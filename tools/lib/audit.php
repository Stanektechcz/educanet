<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET · tools/lib/audit.php (F3 – behaviorální kontroly v audit vrstvách v51+)
 *
 * Sdílené počítadlo kontrol (nahrazuje ručně kopírovanou dvojici $checks/$failed v každém
 * auditu) a pomocné funkce pro HTTP kontroly přes tools/lib/http_harness.php a pro CSS
 * vlastní vlastnosti (parsování hodnot + kontrast podle WCAG), aby audity mohly ověřovat
 * chování aplikace místo pouhého výskytu řetězce ve zdrojovém textu.
 *
 * Počítadlo kontrol
 *   $state = audit_counter();
 *   $check = audit_checker($state);
 *   $check('popis kontroly', $ok);           // chování (výchozí) – funkční volání, HTTP
 *                                             // odpověď, stav v dočasném úložišti…
 *   $check('popis kontroly', $ok, false);     // čistě textová kontrola zdroje – použít jen
 *                                             // tam, kde chování nejde ověřit jinak (např.
 *                                             // „knihovna nikde neodkazuje na CDN“).
 *   exit(audit_summary($state, 'V51_LIGHT_CLARITY'));
 *      → vypíše "BEHAVIORAL x/y" a "V51_LIGHT_CLARITY_AUDIT_OK|FAIL checks=N failed=M".
 *
 * HTTP (vyžaduje require_once __DIR__ . '/http_harness.php' před voláním)
 *   audit_login_student($harness, $classId, $student) – dev-bypass přihlášení (vyžaduje
 *      EDUCANET_DEV_BYPASS=1 v prostředí serveru), vrátí GET ?view=dashboard a CSRF token.
 *   audit_response_clean($response) – odpověď neobsahuje hlášku PHP chyby/varování.
 *
 * Zachycení výstupu
 *   audit_capture($fn) – zavolá $fn() a vrátí, co po cestě vypsala (echo/print) – pro funkce
 *      render_teacher_tab(...): void, které HTML rovnou vypisují místo aby ho vracely. Skutečně
 *      spustí vykreslovací kód nad kontrolovaným vstupem, takže výsledek lze ověřit jako chování,
 *      ne jen jako řetězec ve zdrojovém souboru.
 *
 * CSS vlastní vlastnosti
 *   audit_css_vars($css) – pole ['jmeno' => 'hodnota'] z prvního výskytu každé --jmeno: …;
 *   audit_css_resolve($vars, $value) – rozřeší var(--jmeno) na skutečnou hodnotu.
 *   audit_contrast_ratio($hexA, $hexB) – kontrastní poměr 1..21 podle WCAG 2.x, nebo null.
 */

function audit_counter(): stdClass
{
    $state = new stdClass();
    $state->checks = 0;
    $state->failed = 0;
    $state->behavioral = 0;
    return $state;
}

/** Closure se stejným rozhraním jako dosavadní $check(label, ok); 3. parametr je nepovinný. */
function audit_checker(stdClass $state): Closure
{
    return static function (string $label, bool $ok, bool $behavioral = true) use ($state): bool {
        $state->checks++;
        if ($behavioral) {
            $state->behavioral++;
        }
        if (!$ok) {
            $state->failed++;
        }
        echo ($ok ? 'PASS  ' : 'FAIL  ') . $label . PHP_EOL;
        return $ok;
    };
}

/** Vypíše "BEHAVIORAL x/y" a závěrečný řádek "<PREFIX>_AUDIT_OK|FAIL checks=N failed=M"; vrací exit kód. */
function audit_summary(stdClass $state, string $prefix): int
{
    echo 'BEHAVIORAL ' . $state->behavioral . '/' . $state->checks . PHP_EOL;
    $ok = $state->failed === 0;
    echo $prefix . '_AUDIT_' . ($ok ? 'OK' : 'FAIL') . ' checks=' . $state->checks . ' failed=' . $state->failed . PHP_EOL;
    return $ok ? 0 : 1;
}

/**
 * Předehřeje dočasné úložiště PŘED startem Harness serveru: založení účtů pro všechny žáky
 * (`acc53_provision_all`) haší heslo pro každého žáka (bcrypt, cca 60-100 ms/žák) a na
 * prázdném úložišti (edu_audit_temp_storage) tak první reálný HTTP požadavek v novém PHP
 * procesu trvá desítky sekund – Harness::start() ho pak vyhodnotí jako "server nenaběhl
 * včas" (viz tools/lib/http_harness.php, limit 15 s). Zavolej jednou před Harness::start().
 * Bez parametru $modules nic nedělá (audit, který účty nepoužívá, tuto režii nepotřebuje).
 */
function audit_prewarm_accounts(array $modules): void
{
    if (function_exists('acc53_provision_all')) {
        acc53_provision_all($modules);
    }
    if (function_exists('intake_v51_sync_v1')) {
        intake_v51_sync_v1($modules);
    }
}

// --- HTTP (tools/lib/http_harness.php) ------------------------------------------------------

/**
 * Dev-bypass přihlášení žáka (?class=&student=), stejné jako v CLAUDE.md pro ruční testy.
 * Vrací GET odpověď na ?view=dashboard a z ní vytažený CSRF token (pro následné POST akce).
 * @return array{response: array, csrf: ?string}
 */
function audit_login_student(object $harness, string $classId, string $student): array
{
    $harness->request('GET', '/', ['class' => $classId, 'student' => $student]);
    $dashboard = $harness->request('GET', '/?view=dashboard');
    return ['response' => $dashboard, 'csrf' => $harness->csrfToken((string)$dashboard['body'])];
}

/**
 * Přihlásí se do teacher.php učitelským klíčem (nutno předat stejný $key i v prostředí
 * harness serveru jako EDUCANET_TEACHER_EXPORT_KEY). Vrátí GET odpověď po přihlášení a CSRF token.
 * @return array{response: array, csrf: ?string}
 */
function audit_login_teacher(object $harness, string $key, string $name = 'Audit Teacher'): array
{
    $login = $harness->request('GET', '/teacher.php');
    $csrf = $harness->csrfToken((string)$login['body']);
    $after = $harness->request('POST', '/teacher.php', [
        'action' => 'teacher_login', 'teacher_key' => $key, 'teacher_name' => $name, 'csrf' => (string)$csrf,
    ]);
    return ['response' => $after, 'csrf' => $harness->csrfToken((string)$after['body'])];
}

/** true, když odpověď 200 neobsahuje typickou hlášku běhové PHP chyby/varování/upozornění. */
function audit_response_clean(array $response): bool
{
    return (int)($response['status'] ?? 0) === 200
        && !preg_match('/Fatal error|Uncaught (Error|Exception)|<b>Warning<\/b>|<b>Deprecated<\/b>|<b>Notice<\/b>/', (string)($response['body'] ?? ''));
}

/** Zavolá $fn() a vrátí vše, co po cestě vypsala (echo/print) – viz poznámka nahoře. */
function audit_capture(callable $fn): string
{
    ob_start();
    try {
        $fn();
        return (string)ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        return '';
    }
}

// --- CSS vlastní vlastnosti a kontrast (WCAG 2.x) -------------------------------------------

/** Rozparsuje deklarace "--jmeno: hodnota;" z CSS; vrací první definici každého jména (typicky z :root). */
function audit_css_vars(string $css): array
{
    if (!preg_match_all('/--([a-zA-Z0-9_-]+)\s*:\s*([^;]+);/', $css, $matches, PREG_SET_ORDER)) {
        return [];
    }
    $vars = [];
    foreach ($matches as $row) {
        if (!isset($vars[$row[1]])) {
            $vars[$row[1]] = trim($row[2]);
        }
    }
    return $vars;
}

/** Rozřeší "var(--jmeno[, náhrada])" na skutečnou hodnotu (max. 8 kroků proti cyklu). */
function audit_css_resolve(array $vars, string $value, int $depth = 0): string
{
    if ($depth > 8 || !preg_match('/^var\(\s*--([a-zA-Z0-9_-]+)\s*(?:,\s*(.+))?\)$/', trim($value), $m)) {
        return $value;
    }
    if (isset($vars[$m[1]])) {
        return audit_css_resolve($vars, $vars[$m[1]], $depth + 1);
    }
    return isset($m[2]) ? audit_css_resolve($vars, $m[2], $depth + 1) : $value;
}

/** "#rgb" / "#rrggbb" → [r, g, b] (0-255), nebo null, když hodnota není hex barva. */
function audit_hex_to_rgb(string $hex): ?array
{
    $hex = ltrim(trim($hex), '#');
    if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

/** Relativní jas barvy [r,g,b] podle WCAG 2.x. */
function audit_relative_luminance(array $rgb): float
{
    $channel = static function (int $c): float {
        $v = $c / 255;
        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    };
    return 0.2126 * $channel($rgb[0]) + 0.7152 * $channel($rgb[1]) + 0.0722 * $channel($rgb[2]);
}

/** Kontrastní poměr dvou barev (1..21), nebo null, když některá hodnota není platná hex barva. */
function audit_contrast_ratio(string $hexA, string $hexB): ?float
{
    $a = audit_hex_to_rgb($hexA);
    $b = audit_hex_to_rgb($hexB);
    if ($a === null || $b === null) {
        return null;
    }
    $la = audit_relative_luminance($a) + 0.05;
    $lb = audit_relative_luminance($b) + 0.05;
    return $la > $lb ? $la / $lb : $lb / $la;
}

/**
 * v59 · AUTHZ58-07: přihlásí se do teacher.php vlastním učitelským účtem (režim účtů, pole login + password).
 * Nový učitel s OTP skončí na stránce vynucené změny hesla. Vrátí odpověď po přihlášení a CSRF token.
 * @return array{response: array, csrf: ?string}
 */
function audit_login_teacher_account(object $harness, string $login, string $password): array
{
    $page = $harness->request('GET', '/teacher.php');
    $csrf = $harness->csrfToken((string)$page['body']);
    $after = $harness->request('POST', '/teacher.php', [
        'action' => 'teacher_login', 'login' => $login, 'password' => $password, 'csrf' => (string)$csrf,
    ]);
    return ['response' => $after, 'csrf' => $harness->csrfToken((string)$after['body'])];
}
