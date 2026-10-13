<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v79 · krokové postupy (stepper) – společná logika pro 7 toků (učitel i žák).
 *
 * Princip: každý tok má JEDEN formulář se všemi kroky (panely, neaktivní mají atribut `hidden`). Kterým krokem se začne, rozhoduje
 * server (GET ?krok= nebo rozpracovaný stav v relaci). Tlačítka Zpět/Další posílají POST sp79_next / sp79_back (žák sp79_s_next /
 * sp79_s_back), které jen uloží koncept do relace ($_SESSION['sp79']) a přesměrují na další krok – do úložiště se nic nezapisuje.
 * Poslední krok odešle STEJNOU akci jako před v79 (lr73_save, ba74_approve, identity58_plan, pf75_s_pin …) se stejnými poli.
 * Koncept se při chybě neztratí (je v relaci), chyby jsou u pole i v souhrnu nahoře (aria-live). JS jen vylepšuje (kontrola polí, fokus).
 *
 * Tok s konceptem = záznam v SP79_FLOWS (draft => true) + funkce sp79_def_<tok>() v souboru toku, která vrací pole:
 *   fields   – specifikace polí konceptu (typ, rozsah), viz sp79_sanitize()
 *   scope    – callable(array $data): string – rozsah konceptu (třída + lekce …); jiný rozsah = nový koncept
 *   validate – callable(int $step, array $data): array<string,string> – chyby kroku (id pole → zpráva)
 *   query    – callable(array $data, int $step): array – GET parametry stránky toku pro přesměrování
 */

const SP79_TTL = 8 * 3600;              // rozpracovaný koncept platí 8 h (pak se zahodí)
const SP79_TEXT_STORE_MAX = 2000;       // delší text se do konceptu neuloží celý (validace kroku ohlásí limit pole)
const SP79_LIST_MAX = 300;              // nejvýš položek v poli seznamu (hromadné schválení má strop 250)

/** Toky: id → strana, záložka / pohled, zda má koncept v relaci (false = výběr jen v URL) a počet kroků. Názvy kroků vykresluje soubor toku (žák přes tr()). */
const SP79_FLOWS = [
    'priprava' => ['side' => 'teacher', 'tab' => 'priprava79', 'draft' => false, 'steps' => 4],
    'hromadne' => ['side' => 'teacher', 'tab' => 'hromadne74', 'draft' => true, 'steps' => 4],
    'reflexe' => ['side' => 'teacher', 'tab' => 'reflexe73', 'draft' => true, 'steps' => 4],
    'opakovani' => ['side' => 'teacher', 'tab' => 'opakovani75', 'draft' => false, 'steps' => 3],
    'identita' => ['side' => 'teacher', 'tab' => 'identita', 'draft' => true, 'steps' => 4],
    'uloha' => ['side' => 'student', 'view' => 'ulohy', 'draft' => false, 'steps' => 4],
    'portfolio' => ['side' => 'student', 'view' => 'portfolio', 'draft' => true, 'steps' => 3],
];

/** Akce konceptu (učitel / žák). Poslední krok každého toku používá původní akci modulu. */
const SP79_TEACHER_ACTIONS = ['sp79_next', 'sp79_back', 'sp79_reset'];
const SP79_STUDENT_ACTIONS = ['sp79_s_next', 'sp79_s_back', 'sp79_s_reset'];

function sp79_flow_ok(string $flow, string $side): bool
{
    return isset(SP79_FLOWS[$flow]) && SP79_FLOWS[$flow]['side'] === $side;
}

function sp79_steps(string $flow): int
{
    return (int)(SP79_FLOWS[$flow]['steps'] ?? 1);
}

/** Číslo kroku z GET (?krok=1..n); 0 = nezadáno nebo neplatné. */
function sp79_get_step(string $flow, ?array $get = null): int
{
    $get ??= $_GET;
    $raw = $get['krok'] ?? null;
    if (!is_string($raw) || preg_match('/^[1-9]$/', $raw) !== 1) return 0;
    $n = (int)$raw;
    return $n <= sp79_steps($flow) ? $n : 0;
}

/** Prázdný koncept. */
function sp79_blank(): array
{
    return ['scope' => '', 'step' => 1, 'max' => 1, 'data' => [], 'errors' => [], 'at' => 0, 'final' => 0];
}

/** Rozpracovaný koncept toku (prošlý = prázdný). */
function sp79_draft(string $flow, ?int $now = null): array
{
    $now ??= time();
    $row = $_SESSION['sp79'][$flow] ?? null;
    if (!is_array($row) || (int)($row['at'] ?? 0) < $now - SP79_TTL) return sp79_blank();
    return array_replace(sp79_blank(), array_intersect_key($row, sp79_blank()));
}

/** Koncept pro daný rozsah; jiný rozsah = prázdný koncept (nepřenáší se data jiné třídy/lekce). */
function sp79_draft_for(string $flow, string $scope, ?int $now = null): array
{
    $draft = sp79_draft($flow, $now);
    return $draft['scope'] === $scope ? $draft : array_replace(sp79_blank(), ['scope' => $scope]);
}

function sp79_draft_put(string $flow, array $draft, ?int $now = null): void
{
    if (!isset(SP79_FLOWS[$flow])) return;
    $draft = array_replace(sp79_blank(), array_intersect_key($draft, sp79_blank()));
    $draft['at'] = $now ?? time();
    $n = sp79_steps($flow);
    $draft['step'] = max(1, min($n, (int)$draft['step']));
    $draft['max'] = max($draft['step'], min($n, (int)$draft['max']));
    $_SESSION['sp79'][$flow] = $draft;
}

function sp79_draft_clear(string $flow): void
{
    unset($_SESSION['sp79'][$flow]);
}

/** Text: jen řetězec, bez řídicích znaků, oříznutý na $max znaků. */
function sp79_text(mixed $value, int $max): string
{
    if (!is_string($value)) return '';
    $value = (string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    return mb_substr(trim(str_replace("\r\n", "\n", $value)), 0, $max);
}

/**
 * Pole konceptu podle specifikace. Typy: enum (jedna z hodnot), enums (seznam hodnot), int [min,max] (prázdné = ''),
 * text (max znaků), list (seznam řetězců podle regulárního výrazu), flag ('1' nebo '').
 * @param array<string,array<string,mixed>> $spec
 */
function sp79_sanitize(array $spec, array $input): array
{
    $out = [];
    foreach ($spec as $name => $rule) {
        $value = $input[$name] ?? null;
        $type = (string)($rule['type'] ?? 'text');
        if ($type === 'enum') {
            $out[$name] = is_string($value) && in_array($value, (array)$rule['values'], true) ? $value : '';
        } elseif ($type === 'enums' || $type === 'list') {
            $items = [];
            foreach (is_array($value) ? $value : [] as $v) {
                if (!is_string($v)) continue;
                $ok = $type === 'enums' ? in_array($v, (array)$rule['values'], true) : preg_match((string)$rule['re'], $v) === 1;
                if ($ok) $items[] = $v;
            }
            $out[$name] = array_slice(array_values(array_unique($items)), 0, (int)($rule['max'] ?? SP79_LIST_MAX));
        } elseif ($type === 'int') {
            [$min, $max] = $rule['range'];
            $out[$name] = is_string($value) && preg_match('/^\d{1,6}$/', trim($value)) === 1 && (int)$value >= $min && (int)$value <= $max ? (string)(int)$value : (is_string($value) && trim($value) !== '' ? 'x' : '');
        } elseif ($type === 'flag') {
            $out[$name] = $value === '1' ? '1' : '';
        } else {
            $out[$name] = sp79_text($value, min(SP79_TEXT_STORE_MAX, (int)($rule['max'] ?? 300) + 200));
        }
    }
    return $out;
}

/** Definice toku (funkce v souboru toku musí být načtená). */
function sp79_def(string $flow): ?array
{
    if (empty(SP79_FLOWS[$flow]['draft'])) return null;
    $fn = 'sp79_def_' . $flow;   // definice v souboru toku (sp79_def_reflexe, sp79_def_hromadne, sp79_def_identita, sp79_def_portfolio)
    if (!function_exists($fn)) return null;
    $def = $fn();
    return is_array($def) ? $def : null;
}

/**
 * Zpracování Zpět / Další / Zahodit (čistá logika nad konceptem; bez přesměrování).
 * @return array{draft:array,step:int} nový koncept a krok, na který se přesměruje
 */
function sp79_apply(string $flow, array $def, string $move, int $from, array $input, ?int $now = null): array
{
    $n = sp79_steps($flow);
    $from = max(1, min($n, $from));
    $data = sp79_sanitize((array)$def['fields'], $input);
    $scope = (string)($def['scope'])($data);
    $draft = sp79_draft_for($flow, $scope, $now);
    if ($move === 'reset') return ['draft' => array_replace(sp79_blank(), ['scope' => $scope]), 'step' => 1];
    $draft['data'] = $data;
    $draft['errors'] = [];
    if ($move === 'back') {
        $draft['step'] = max(1, $from - 1);
        return ['draft' => $draft, 'step' => $draft['step']];
    }
    $errors = (array)($def['validate'])($from, $data);
    if ($errors !== []) {
        $draft['errors'] = array_map('strval', $errors);
        $draft['step'] = $from;
    } else {
        $draft['step'] = min($n, $from + 1);
        $draft['max'] = max((int)$draft['max'], $draft['step']);
    }
    return ['draft' => $draft, 'step' => $draft['step']];
}

/** Pohyb z názvu akce. */
function sp79_move(string $action): string
{
    return match ($action) { 'sp79_back', 'sp79_s_back' => 'back', 'sp79_reset', 'sp79_s_reset' => 'reset', default => 'next' };
}

/** POST sp79_* v cockpitu (CSRF, rozsah tříd a oprávnění „view“ už ověřil teacher.php; tady oprávnění toku). */
function sp79_teacher_handle_post(string $action, array $modules = []): void
{
    $flow = is_string($_POST['sp79_flow'] ?? null) ? (string)$_POST['sp79_flow'] : '';
    if (!in_array($action, SP79_TEACHER_ACTIONS, true) || !sp79_flow_ok($flow, 'teacher')) throw new RuntimeException('Neznámý krokový postup.');
    $tab = (string)SP79_FLOWS[$flow]['tab'];
    if (function_exists('teacher58_is_admin_denied') && teacher58_is_admin_denied($tab)) throw new RuntimeException('Tento postup je jen pro administrátora.');
    if (function_exists('teacher58_load') && function_exists('teacher58_is_tab') && teacher58_is_tab($tab)) teacher58_load($tab);
    $def = sp79_def($flow);
    if ($def === null) throw new RuntimeException('Krokový postup teď není dostupný.');
    $perm = (string)($def['perm'] ?? '');
    if ($perm !== '' && function_exists('teacher_permission') && !teacher_permission($perm)) throw new RuntimeException('Máte jen čtení – tento postup dokončí učitel s oprávněním.');
    $from = is_string($_POST['sp79_step'] ?? null) ? (int)$_POST['sp79_step'] : 1;
    $res = sp79_apply($flow, $def, sp79_move($action), $from, $_POST);
    sp79_draft_put($flow, $res['draft']);
    $query = ['tab' => $tab] + (array)($def['query'])($res['draft']['data'], $res['step']) + ['krok' => (string)$res['step']];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    header('Location: teacher.php?' . http_build_query($query), true, 303);
    exit;
}

/** POST sp79_s_* žáka (CSRF a třídu ověřil index.php; identita jen ze session). */
function sp79_student_handle_post(string $action, string $classId): void
{
    $flow = is_string($_POST['sp79_flow'] ?? null) ? (string)$_POST['sp79_flow'] : '';
    $def = in_array($action, SP79_STUDENT_ACTIONS, true) && sp79_flow_ok($flow, 'student') ? sp79_def($flow) : null;
    if ($def === null || $classId === '') {
        $_SESSION['flash'] = function_exists('tr') ? tr('Postup se nepodařilo otevřít. Zkus to znovu.') : 'Postup se nepodařilo otevřít.';
        redirect_to('?view=dashboard');
    }
    $from = is_string($_POST['sp79_step'] ?? null) ? (int)$_POST['sp79_step'] : 1;
    $res = sp79_apply($flow, $def, sp79_move($action), $from, $_POST);
    sp79_draft_put($flow, $res['draft']);
    $query = ['view' => (string)SP79_FLOWS[$flow]['view']] + (array)($def['query'])($res['draft']['data'], $res['step']) + ['krok' => (string)$res['step']];
    redirect_to('?' . http_build_query($query) . '#sp79-top');
}

/**
 * Krok k zobrazení: požadovaný ?krok= omezený na nejdál dosažený krok konceptu ($reach); bez ?krok= krok konceptu nebo $default.
 */
function sp79_current_step(string $flow, int $reach, int $default, ?array $get = null): int
{
    $n = sp79_steps($flow);
    $reach = max(1, min($n, $reach));
    $want = sp79_get_step($flow, $get);
    $step = $want > 0 ? $want : $default;
    return max(1, min($reach, $step));
}

/** Zpráva v relaci učitele (teacher.php ji vyzvedne do $flash před vykreslením záložky). @return array{type:string,message:string}|null */
function sp79_teacher_flash(): ?array
{
    $flash = $GLOBALS['flash'] ?? null;
    return is_array($flash) ? ['type' => (string)($flash['type'] ?? ''), 'message' => (string)($flash['message'] ?? '')] : null;
}

/** Zobrazený krok konceptu si koncept pamatuje (návrat do záložky, poznání odeslání z posledního kroku). */
function sp79_track(string $flow, array $draft, int $cur): void
{
    if ($draft['data'] !== [] && (int)$draft['step'] !== $cur) sp79_draft_put($flow, ['step' => $cur, 'errors' => []] + $draft);
}
