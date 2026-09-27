<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček „Opakování“ (LAB-09) a adaptivní nápověda (EDU-03).
 *
 * Auto-načítaný soubor jádra (linux_v58_ext.php): registruje kontext `review:<RRRRMMDD>`,
 * fond krátkých úloh, dva vlastní generátory/kontroly a filtr `state_payload` (adaptive_hint).
 * Musí být tady, ne v lab_v58_review.php – jen soubory `linux_v58_(cmd|levels)_*.php` se
 * načítají automaticky i uvnitř lab_v57_api.php (žádné volání ze studentských/učitelských stránek).
 *
 * Leitnerovy přihrádky (1–5, intervaly 1/2/4/7/14 dní) se vedou per „dovednost“ = jméno
 * fondové úrovně (skill), ne per konkrétní instance úlohy. Úložiště:
 * storage/linux_v58/review/<třída>__<sha1 žáka>.json.php (jen přes lab57_store_update()).
 */

const LAB58_REVIEW_SKILLS = ['ls', 'find', 'grep', 'chmod', 'ip', 'redirect', 'cd', 'sudo'];
const LAB58_REVIEW_INTERVALS = [1 => 1, 2 => 2, 3 => 4, 4 => 7, 5 => 14];
const LAB58_REVIEW_DAY_RE = '/^\d{8}$/';
const LAB58_REVIEW_GRACE_SECS = 3 * 86400; // student může dohnat i den zpátky

// ---------------------------------------------------------------------------
// Úložiště: Leitnerovy přihrádky, denní výběr, série
// ---------------------------------------------------------------------------

function lab58_review_store_path(string $classId, string $studentKey): string
{
    $safeClass = preg_replace('/[^a-z0-9_]/i', '', $classId);
    return lab57_storage_dir() . '/linux_v58/review/' . $safeClass . '__' . sha1($studentKey) . '.json.php';
}

/** Skill fondové úrovně (jen úrovně balíčku review), nebo null. */
function lab58_review_skill_of(string $levelId): ?string
{
    $level = function_exists('lab57_level') ? lab57_level($levelId) : null;
    if (!is_array($level) || (string)($level['pack'] ?? '') !== 'review') return null;
    $skill = $level['skill'] ?? null;
    return is_string($skill) && $skill !== '' ? $skill : null;
}

/** Leitner: úspěch = +1 přihrádka (max 5), neúspěch = zpět na 1. Volá se z posluchačů události. */
function lab58_review_leitner_advance(string $classId, string $studentKey, string $levelId, bool $success, int $now): void
{
    $skill = lab58_review_skill_of($levelId);
    if ($skill === null) return;
    lab57_store_update(lab58_review_store_path($classId, $studentKey), static function (array $d) use ($skill, $success, $now): array {
        $box = (array)($d['boxes'][$skill] ?? []);
        $newBox = $success ? min(5, (int)($box['box'] ?? 1) + 1) : 1;
        $days = LAB58_REVIEW_INTERVALS[$newBox] ?? 1;
        $d['boxes'][$skill] = ['box' => $newBox, 'last' => date('Y-m-d', $now), 'due' => date('Y-m-d', $now + $days * 86400)];
        return $d;
    });
}

/** @return list<array> položky uložené pro daný den (RRRR-MM-DD), nebo null když den ještě neexistuje */
function lab58_review_day_items(string $classId, string $studentKey, string $dayId): ?array
{
    if (preg_match(LAB58_REVIEW_DAY_RE, $dayId) !== 1) return null;
    $date = substr($dayId, 0, 4) . '-' . substr($dayId, 4, 2) . '-' . substr($dayId, 6, 2);
    $data = lab57_store_read(lab58_review_store_path($classId, $studentKey));
    $day = $data['days'][$date] ?? null;
    return is_array($day) && is_array($day['items'] ?? null) ? $day['items'] : null;
}

/** Vybere 3 dovednosti na dnešek: nejdřív nejvíc opožděné přihrádky, pak deterministická rotace fondu. */
function lab58_review_pick_skills(array $boxes, string $today, int $now): array
{
    $due = [];
    foreach ($boxes as $skill => $box) {
        if ((string)($box['due'] ?? '1970-01-01') <= $today) $due[] = [(string)$skill, (string)($box['due'] ?? '')];
    }
    usort($due, static fn(array $a, array $b): int => $a[1] <=> $b[1]);
    $skills = array_column($due, 0);
    $pool = LAB58_REVIEW_SKILLS;
    $dayIndex = (int)date('z', $now);
    for ($i = 0; count($skills) < 3 && $i < count($pool); $i++) {
        $candidate = $pool[($dayIndex + $i) % count($pool)];
        if (!in_array($candidate, $skills, true)) $skills[] = $candidate;
    }
    return array_slice($skills, 0, 3);
}

/** Deterministický výběr jedné úrovně fondu pro dovednost (různý žák/den = jiná úroha ze stejné dovednosti). */
function lab58_review_pick_level(string $skill, string $studentKey, string $today): ?string
{
    $candidates = [];
    foreach (function_exists('lab57_pack_levels') ? lab57_pack_levels('review') : [] as $level) {
        if ((string)($level['skill'] ?? '') === $skill) $candidates[] = (string)$level['id'];
    }
    if ($candidates === []) return null;
    sort($candidates);
    $idx = crc32($studentKey . '|' . $today . '|' . $skill) % count($candidates);
    return $candidates[$idx];
}

/** Zajistí, že pro dnešek existuje výběr 3 úloh (jednou vygenerovaný, dál se neměnní). */
function lab58_review_ensure_day(string $classId, string $studentKey, int $now): array
{
    $today = date('Y-m-d', $now);
    return lab57_store_update(lab58_review_store_path($classId, $studentKey), static function (array $d) use ($today, $now, $studentKey): array {
        if (is_array($d['days'][$today] ?? null)) return $d;
        $skills = lab58_review_pick_skills((array)($d['boxes'] ?? []), $today, $now);
        $items = [];
        foreach ($skills as $skill) {
            $levelId = lab58_review_pick_level($skill, $studentKey, $today);
            if ($levelId === null) continue;
            $box = (array)($d['boxes'][$skill] ?? []);
            $items[] = ['level' => $levelId, 'skill' => $skill, 'box' => (int)($box['box'] ?? 1)];
        }
        $d['days'][$today] = ['items' => $items, 'generated_at' => $now];
        if (count($d['days']) > 30) { uksort($d['days'], 'strcmp'); $d['days'] = array_slice($d['days'], -30, null, true); }
        return $d;
    });
}

/** Aktuální i nejdelší dosažená série dní (max se používá pro odznak Pilný – nemizí při přerušení). */
function lab58_review_streak(string $classId, string $studentKey): int
{
    $d = lab57_store_read(lab58_review_store_path($classId, $studentKey));
    return (int)($d['streak']['count'] ?? 0);
}

function lab58_review_streak_max(string $classId, string $studentKey): int
{
    $d = lab57_store_read(lab58_review_store_path($classId, $studentKey));
    return (int)($d['streak']['max'] ?? 0);
}

/** Když jsou všechny dnešní úlohy hotové, prodlouží (nebo založí) sérii dní – nejvýš jednou za den. */
function lab58_review_maybe_bump_streak(string $classId, string $studentKey, int $now): void
{
    $today = date('Y-m-d', $now);
    $data = lab57_store_read(lab58_review_store_path($classId, $studentKey));
    $items = (array)($data['days'][$today]['items'] ?? []);
    if ($items === [] || !function_exists('lab57_solved')) return;
    $solved = lab57_solved($classId, $studentKey, 'review:' . date('Ymd', $now));
    foreach ($items as $it) if (!isset($solved[(string)($it['level'] ?? '')])) return;
    lab57_store_update(lab58_review_store_path($classId, $studentKey), static function (array $d) use ($today): array {
        $streak = (array)($d['streak'] ?? ['count' => 0, 'last' => '', 'max' => 0]);
        if ((string)($streak['last'] ?? '') === $today) return $d;
        $yesterday = date('Y-m-d', strtotime($today . ' -1 day'));
        $streak['count'] = (string)($streak['last'] ?? '') === $yesterday ? (int)$streak['count'] + 1 : 1;
        $streak['last'] = $today;
        $streak['max'] = max((int)($streak['max'] ?? 0), (int)$streak['count']);
        $d['streak'] = $streak;
        return $d;
    });
}

// ---------------------------------------------------------------------------
// Vlastní generátory a kontroly fondu (kromě file/code_file/decoys/file_contains z jádra)
// ---------------------------------------------------------------------------

/** Krátký log s náhodným počtem řádků „CHYBA“ – fakt review_log_count drží správný počet. */
function lab58_review_gen_log(Lab57World $w, Lab57Rng $r, array $p): void
{
    $total = $r->int(6, 10);
    $matches = $r->int(1, 3);
    $positions = [];
    while (count($positions) < min($matches, $total)) $positions[$r->int(0, $total - 1)] = true;
    $lines = [];
    for ($i = 0; $i < $total; $i++) $lines[] = isset($positions[$i]) ? 'CHYBA: spojeni se serverem selhalo' : 'OK: sluzba bezi normalne';
    $w->facts['review_log_count'] = (string)count($positions);
    lab58_gen_write($w, lab58_gen_path($w, '~/log.txt', $r), implode("\n", $lines) . "\n", $p);
}

/** Kontrola: soubor existuje a má nastavené spouštěcí právo pro vlastníka (chmod u+x). */
function lab58_review_check_executable(Lab57World $w, array $p): bool
{
    $node = $w->fs->get(lab58_gen_path($w, (string)($p['path'] ?? '')));
    return $node !== null && ($node['t'] ?? '') === 'f' && ((int)($node['m'] ?? 0) & 0100) !== 0;
}

// ---------------------------------------------------------------------------
// Fond krátkých úloh (2–3 minuty), 1 dvojice úrovní na dovednost
// ---------------------------------------------------------------------------

function lab58_review_pool_files(): array
{
    return [
        [
            'id' => 'review-ls-1', 'pack' => 'review', 'type' => 'code', 'skill' => 'ls', 'title' => 'Kde je ten kód?',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'Krátké opakování: v domovské složce je schovaný soubor s kódem.',
            'task' => 'Najdi soubor s kódem (zkus ls -R nebo find ~ -name "*.txt") a odevzdej: submit EDU-XXXX-XXXX',
            'commands' => ['ls', 'find', 'cat', 'submit'],
            'hints' => ['ls -R ~ vypíše obsah všech podsložek najednou.', 'cat vypíše obsah souboru na obrazovku.'],
            'generate' => [
                ['decoys', ['dir' => '~/soubory', 'count' => 4, 'name' => 'info-{N}.txt']],
                ['code_file', ['dirs' => ['~', '~/soubory'], 'names' => ['kod.txt', 'poznamka.txt']]],
            ],
            'solution' => ['cat {f:code_path}', 'submit {CODE}'],
        ],
        [
            'id' => 'review-find-1', 'pack' => 'review', 'type' => 'code', 'skill' => 'find', 'title' => 'Hledej podle jména',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'V archivu je desítky podobných souborů, jen jeden má správné jméno.',
            'task' => 'Najdi soubor tajenka.txt (zkus find ~ -name "tajenka.txt") a odevzdej jeho kód: submit EDU-XXXX-XXXX',
            'commands' => ['find', 'cat', 'submit'],
            'hints' => ['find ~ -name "tajenka.txt" najde soubor přesně podle jména.', 'find umí hledat i podle typu: find ~ -type f.'],
            'generate' => [
                ['decoys', ['dir' => '~/data/archiv', 'count' => 6, 'name' => 'zaznam-{N}.txt']],
                ['code_file', ['dirs' => ['~/data/archiv', '~/data'], 'names' => ['tajenka.txt']]],
            ],
            'solution' => ['find ~ -name tajenka.txt', 'cat {f:code_path}', 'submit {CODE}'],
        ],
    ];
}

function lab58_review_pool_files2(): array
{
    return [
        [
            'id' => 'review-grep-1', 'pack' => 'review', 'type' => 'answer', 'skill' => 'grep', 'title' => 'Kolik chyb v logu?',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'Server zapisuje stav do log.txt. Někde v něm jsou i chybové řádky.',
            'task' => 'Kolikrát se v ~/log.txt objevuje slovo CHYBA? Odpověz číslem.',
            'commands' => ['grep', 'cat', 'answer'],
            'hints' => ['grep CHYBA ~/log.txt vypíše jen odpovídající řádky.', 'grep -c CHYBA ~/log.txt rovnou spočítá výskyty.'],
            'generate' => [['review_log', []]],
            'answer' => static fn(Lab57World $w): string => (string)($w->facts['review_log_count'] ?? '0'),
            'answer_format' => 'celé číslo',
            'solution' => static fn(Lab57World $w): array => ['grep -c CHYBA ~/log.txt', 'answer ' . (string)($w->facts['review_log_count'] ?? '0')],
        ],
        [
            'id' => 'review-chmod-1', 'pack' => 'review', 'type' => 'check', 'skill' => 'chmod', 'title' => 'Spustitelný skript',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'Skript zálohy by měl jít spustit, ale někdo mu vzal spouštěcí právo.',
            'task' => 'Nastav souboru ~/zaloha.sh spouštěcí právo pro vlastníka (chmod u+x ~/zaloha.sh) a ověř.',
            'commands' => ['chmod', 'ls'],
            'hints' => ['ls -l ~/zaloha.sh ukáže aktuální práva.', 'chmod u+x ~/zaloha.sh přidá spouštěcí právo jen vlastníkovi.'],
            'generate' => [['file', ['path' => '~/zaloha.sh', 'content' => "#!/bin/bash\necho zaloha hotova\n", 'mode' => '644']]],
            'checks' => [['review_executable', ['path' => '~/zaloha.sh', 'label' => 'zaloha.sh je spustitelný pro vlastníka']]],
            'solution' => ['chmod u+x ~/zaloha.sh'],
        ],
    ];
}

function lab58_review_pool_files3(): array
{
    $netBuild = static function (Lab57World $w, Lab57Rng $r): void {
        lab57_net_renumber($w, $r->int(1, 250));
        $w->net['ifaces']['eth0']['ip'] = lab57_lan($w, $r->int(20, 240));
        $w->net['nodes']['pc']['ip'] = $w->net['ifaces']['eth0']['ip'];
    };
    return [
        [
            'id' => 'review-ip-1', 'pack' => 'review', 'type' => 'answer', 'skill' => 'ip', 'title' => 'Adresa počítače',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2, 'topology' => true,
            'story' => 'Krátké opakování: každý počítač v síti má svou IPv4 adresu.',
            'task' => 'Zjisti IPv4 adresu rozhraní eth0 (bez /24) a odpověz: answer <adresa>',
            'commands' => ['ip', 'answer'],
            'hints' => ['ip a vypíše síťová rozhraní a jejich adresy.', 'Hledej řádek inet u eth0.'],
            'build' => $netBuild,
            'answer' => static fn(Lab57World $w): string => (string)$w->net['ifaces']['eth0']['ip'],
            'answer_format' => 'IPv4 adresa',
            'solution' => static fn(Lab57World $w): array => ['ip a', 'answer ' . $w->net['ifaces']['eth0']['ip']],
        ],
        [
            'id' => 'review-redirect-1', 'pack' => 'review', 'type' => 'check', 'skill' => 'redirect', 'title' => 'Přepsat stav',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'Soubor se stavem má zastaralý obsah.',
            'task' => 'Přepiš obsah ~/stav.txt na jediné slovo hotovo (echo hotovo > ~/stav.txt) a ověř to.',
            'commands' => ['echo', 'cat'],
            'hints' => ['Znak > přesměruje výstup příkazu do souboru a přitom ho přepíše.', 'echo hotovo > ~/stav.txt'],
            'generate' => [['file', ['path' => '~/stav.txt', 'content' => "necekany obsah\n"]]],
            'checks' => [['file_contains', ['path' => '~/stav.txt', 'equals' => 'hotovo', 'label' => 'stav.txt obsahuje hotovo']]],
            'solution' => ['echo hotovo > ~/stav.txt'],
        ],
    ];
}

function lab58_review_pool_files4(): array
{
    return [
        [
            'id' => 'review-cd-1', 'pack' => 'review', 'type' => 'code', 'skill' => 'cd', 'title' => 'Starý projekt',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'Ve starém projektu zůstal soubor s kódem pár složek hluboko.',
            'task' => 'Přepni se do složky projekt/stary (cd) a najdi tam kód: submit EDU-XXXX-XXXX',
            'commands' => ['cd', 'ls', 'cat', 'submit'],
            'hints' => ['cd projekt/stary tě přepne rovnou do vnořené složky.', 'Jsi-li uvnitř, ls a cat stačí bez celé cesty.'],
            'generate' => [
                ['decoys', ['dir' => '~/projekt/stary', 'count' => 3, 'name' => 'draft-{N}.txt']],
                ['code_file', ['dirs' => ['~/projekt/stary'], 'names' => ['finale.txt']]],
            ],
            'solution' => ['cd ~/projekt/stary', 'cat {f:code_path}', 'submit {CODE}'],
        ],
        [
            'id' => 'review-sudo-1', 'pack' => 'review', 'type' => 'check', 'skill' => 'sudo', 'title' => 'Soubor správce',
            'difficulty' => 1, 'points' => 20, 'minutes' => 2,
            'story' => 'Soubor patří správci systému – běžný uživatel do něj nemůže zapisovat.',
            'task' => 'Přepiš /etc/notice.txt na text vitej pomocí sudo (echo vitej | sudo tee /etc/notice.txt).',
            'commands' => ['sudo', 'tee', 'cat'],
            'hints' => ['Zápis bez sudo skončí chybou Permission denied.', 'echo vitej | sudo tee /etc/notice.txt zapíše soubor jako správce.'],
            'generate' => [['file', ['path' => '/etc/notice.txt', 'content' => "stary text\n", 'owner' => 'root', 'mode' => '644']]],
            'checks' => [['file_contains', ['path' => '/etc/notice.txt', 'equals' => 'vitej', 'label' => 'notice.txt obsahuje vitej']]],
            'solution' => ['echo vitej | sudo tee /etc/notice.txt'],
        ],
    ];
}

function lab58_review_pool_levels(): array
{
    return array_merge(lab58_review_pool_files(), lab58_review_pool_files2(), lab58_review_pool_files3(), lab58_review_pool_files4());
}

// ---------------------------------------------------------------------------
// EDU-03: adaptivní nápověda po 3 stejných chybách (filtr state_payload → adaptive_hint)
// ---------------------------------------------------------------------------

const LAB58_HINT_MIN_REPEATS = 3;
const LAB58_HINT_NOT_FOUND_MAP = [
    'ipconfig' => 'ip a', 'ifconfig' => 'ip a', 'dir' => 'ls', 'cls' => 'clear', 'copy' => 'cp',
    'move' => 'mv', 'del' => 'rm', 'ren' => 'mv', 'type' => 'cat', 'more' => 'less', 'findstr' => 'grep',
    'tasklist' => 'ps', 'taskkill' => 'kill', 'netstat' => 'ss', 'md' => 'mkdir', 'rd' => 'rmdir', 'edit' => 'nano',
];

/** Text nápovědy pro danou třídu chyby + příkaz, nebo null (chyba bez pravidla). */
function lab58_adaptive_hint_text(string $errorClass, string $cmd): ?array
{
    if ($errorClass === 'not_found' && isset(LAB58_HINT_NOT_FOUND_MAP[$cmd])) {
        $suggest = LAB58_HINT_NOT_FOUND_MAP[$cmd];
        return ['text' => 'Příkaz „' . $cmd . '“ v Linuxu neexistuje. Zkus „' . $suggest . '“.', 'reason' => 'not_found:' . $cmd];
    }
    return match ($errorClass) {
        'permission' => ['text' => 'Zkontroluj práva příkazem „ls -l“, případně použij „sudo <příkaz>“.', 'reason' => 'permission'],
        'no_such_file' => ['text' => 'Cesta asi nesedí. „pwd“ ukáže, kde jsi, „ls“ co je v okolí.', 'reason' => 'no_such_file'],
        'bad_option' => ['text' => 'Přepínač nesedí. Zkus „man ' . $cmd . '“ pro přehled voleb.', 'reason' => 'bad_option:' . $cmd],
        'usage', 'syntax' => ['text' => 'Zkontroluj zápis příkazu – „' . $cmd . ' --help“ nebo „man ' . $cmd . '“ ukáže správné použití.', 'reason' => 'usage:' . $cmd],
        'not_found' => ['text' => 'Příkaz „' . $cmd . '“ nebyl nalezen. Zkontroluj překlep, nebo zkus „help“.', 'reason' => 'not_found:' . $cmd],
        default => null,
    };
}

/** Poslední vzor chyby (třída+příkaz), který se v úrovni zopakoval aspoň LAB58_HINT_MIN_REPEATS×. */
function lab58_adaptive_hint_scan(string $classId, string $studentKey, string $levelId, int $now): ?array
{
    if (!function_exists('lab58_log_read')) return null;
    $counts = [];
    $lastByPattern = [];
    foreach (lab58_log_read($classId, $studentKey, $levelId, $now) as $r) {
        if (($r['kind'] ?? '') !== 'cmd' || ($r['error_class'] ?? 'ok') === 'ok') continue;
        $cmd = function_exists('lab58_log_command_name') ? lab58_log_command_name((string)$r['line']) : '?';
        $pattern = (string)$r['error_class'] . '|' . $cmd;
        $counts[$pattern] = ($counts[$pattern] ?? 0) + 1;
        $lastByPattern[$pattern] = ['error_class' => (string)$r['error_class'], 'cmd' => $cmd, 'ts' => (int)$r['ts']];
    }
    $best = null;
    foreach ($counts as $pattern => $n) {
        if ($n < LAB58_HINT_MIN_REPEATS) continue;
        if ($best === null || $lastByPattern[$pattern]['ts'] > $lastByPattern[$best]['ts']) $best = $pattern;
    }
    return $best === null ? null : lab58_adaptive_hint_text($lastByPattern[$best]['error_class'], $lastByPattern[$best]['cmd']);
}

function lab58_review_state_payload_filter(array $payload, array $args): array
{
    $level = (array)($args['level'] ?? []);
    $ctx = (array)($args['ctx'] ?? []);
    $levelId = (string)($level['id'] ?? '');
    $classId = (string)($ctx['class'] ?? '');
    $studentKey = (string)($ctx['student'] ?? '');
    if ($levelId === '' || $levelId === 'sandbox' || $classId === '' || $studentKey === '' || !empty($payload['solved'])) {
        $payload['adaptive_hint'] = null;
        return $payload;
    }
    $payload['adaptive_hint'] = lab58_adaptive_hint_scan($classId, $studentKey, $levelId, (int)($ctx['now'] ?? time()));
    return $payload;
}

// ---------------------------------------------------------------------------
// Kontext review:<RRRRMMDD> – jen dnešní (± pár dní) vybrané úlohy studenta
// ---------------------------------------------------------------------------

function lab58_review_ctx_access(array $level, array $ctx): ?string
{
    $id = (string)($ctx['id'] ?? '');
    if (preg_match(LAB58_REVIEW_DAY_RE, $id) !== 1) return 'Neplatný den opakování.';
    $dayTs = strtotime(substr($id, 0, 4) . '-' . substr($id, 4, 2) . '-' . substr($id, 6, 2));
    $now = (int)($ctx['now'] ?? time());
    if ($dayTs === false) return 'Neplatný den opakování.';
    if ($dayTs > $now) return 'Tenhle den opakování ještě nezačal.';
    if ($now - $dayTs > LAB58_REVIEW_GRACE_SECS) return 'Tenhle den opakování je už uzavřený.';
    return null;
}

function lab58_review_ctx_levels(array $ctx): array
{
    $items = lab58_review_day_items((string)$ctx['class'], (string)$ctx['student'], (string)($ctx['id'] ?? ''));
    return $items === null ? [] : array_map(static fn(array $it): string => (string)($it['level'] ?? ''), $items);
}

// ---------------------------------------------------------------------------
// LAB-09 · Vykreslení denní úlohy opakování (živá trasa z lab58_review_today())
// ---------------------------------------------------------------------------

/**
 * `?view=lab&uroven=<id>&opakovani=<RRRRMMDD>` – obdoba lab57_render_level(), ale s kontextem
 * `review:<den>` místo `practice` (úroveň musí být v dnešním výběru žáka, viz lab58_review_ctx_access).
 * Volá se z app/views/linux_lab.php jen když je nastavený GET parametr `opakovani` (patch v INTEGRATION.md).
 */
function lab58_review_render_student(string $classId, array $module, string $day, string $levelId, string $flash): void
{
    $ctx = 'review:' . $day;
    $level = lab57_resolve_level($levelId);
    $studentKey = adaptive_student_key($classId);
    $error = $level === null ? 'Tahle úloha neexistuje.' : lab57_access_error($level, ['context' => $ctx, 'class' => $classId, 'student' => $studentKey, 'now' => time()]);
    if ($level === null || $error !== null) {
        $_SESSION['flash'] = (string)$error;
        redirect_to('?view=lab&sekce=opakovani');
    }

    render_header('Lab · ' . (string)$level['title'], $module);
    ?>
    <div class="lab57 lab57-level-page">
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>
      <nav class="lab57-crumbs" aria-label="Cesta">
        <a href="?view=lab">Linux Lab</a><span>›</span>
        <a href="?view=lab&amp;sekce=opakovani">Dnešní opakování</a><span>›</span>
        <span><?= e((string)$level['title']) ?></span>
      </nav>
      <?php lab57_render_workspace($level, $ctx, ['next_url' => '?view=lab&sekce=opakovani']); ?>
    </div>
    <?php
    render_footer();
}

// ---------------------------------------------------------------------------
// Registrace (při načtení souboru)
// ---------------------------------------------------------------------------

lab58_register_generator('review_log', 'lab58_review_gen_log');
lab58_register_check('review_executable', 'lab58_review_check_executable');
lab58_register_pack(
    ['id' => 'review', 'title' => 'Opakování', 'description' => 'Krátké 2minutové úlohy na už probrané příkazy.', 'order' => 65, 'classes' => null, 'unlock' => 'free', 'icon' => '↻', 'tone' => 'teal'],
    'lab58_review_pool_levels'
);
lab58_register_context('review', ['label' => 'Denní opakování', 'access' => 'lab58_review_ctx_access', 'levels' => 'lab58_review_ctx_levels']);
lab58_add_filter('state_payload', 'lab58_review_state_payload_filter');
lab58_on('submit_fail', static function (array $e): void {
    if ((string)($e['prefix'] ?? '') !== 'review') return;
    lab58_review_leitner_advance((string)$e['class'], (string)$e['student'], (string)$e['level'], false, (int)$e['ts']);
});
lab58_on('complete', static function (array $e): void {
    if ((string)($e['prefix'] ?? '') !== 'review') return;
    lab58_review_leitner_advance((string)$e['class'], (string)$e['student'], (string)$e['level'], true, (int)$e['ts']);
    lab58_review_maybe_bump_streak((string)$e['class'], (string)$e['student'], (int)$e['ts']);
});
