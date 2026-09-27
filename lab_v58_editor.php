<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Editor úrovní (TCH-01) – logika, úložiště, kontrola řešitelnosti a obsahu.
 *
 * Vlastní úlohy učitele jsou čistá DATA (žádné closures) uložená v
 * storage/lab_v58_custom_levels.json.php (přes lab57_store_update – zámek přes čtení i zápis;
 * použije F2 storage_update(), pokud je k dispozici, jinak vlastní LOCK_EX – funguje i ve
 * standalone kontextu, kdy je načtený jen linux_v57_lab.php bez bootstrap.php, např. v auditu).
 * Zveřejněná
 * úloha se objeví žákům dané třídy v balíčku „Úlohy od učitele“ (jeden balíček na třídu, aby šlo
 * použít existující `classes` filtr balíčku – lab58_register_pack() na konci tohoto souboru).
 *
 * DŮLEŽITÉ pro studentskou stranu: tento soubor NEODPOVÍDÁ vzoru linux_v58_(cmd|levels)_*.php,
 * takže ho `lab58_load_extensions()` nenačte automaticky. Proto ho vždy require_once-uje
 * linux_v58_levels_generators.php (soubor, který jádro NAČÍTÁ automaticky pro každý požadavek,
 * žákovský i učitelský) – tím se balíčky „Úlohy od učitele“ zaregistrují i na straně žáka.
 * Views (lab_v58_editor_views.php) require_once-uje tenhle soubor taky, pro samostatnou použitelnost.
 *
 * Bezpečnostní invariant: vlastní úrovně jsou jen data (šablony, jména generátorů, parametry) –
 * žádný kód učitele se nikdy nevykonává (žádné eval/serialize objektů, jen JSON pole).
 */

// Vlastní kontrola (bez závislosti na editoru z jejího pohledu, ale editor ji potřebuje) – ať
// lab58e_check_row() funguje i tehdy, když tento soubor někdo požaduje samostatně.
if (is_file(__DIR__ . '/lab_v58_content.php')) require_once __DIR__ . '/lab_v58_content.php';

const LAB58E_CLASS_IDS = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
const LAB58E_MAX_FILES = 30;
const LAB58E_MAX_FILE_BYTES = 65536;
const LAB58E_MAX_GENERATORS = 10;
const LAB58E_MAX_CHECKS = 8;
const LAB58E_MAX_TITLE = 120;
const LAB58E_MAX_STORY = 4000;
const LAB58E_MAX_TASK = 2000;
const LAB58E_MAX_LEARN = 2000;
const LAB58E_MAX_HINT = 500;
const LAB58E_MAX_ANSWER = 300;
const LAB58E_PREVIEW_SEEDS = 5;
const LAB58E_ID_RE = '/^u-[0-9a-f]{8}$/';

/** Katalog generátorů/kontrol nabízený v editoru – jen naše vlastní core.* + základní ukázkové (stabilní, dokumentované v58 API). Šablonové tokeny bez rng ({TOKEN}/{DECOY}/{NUM}/{WORD}/{NAME}) nejsou v answer/solution povolené – viz lab58e_forbidden_template_tokens(). */
function lab58e_generator_catalog(): array
{
    return [
        'file' => 'Jeden soubor s obsahem',
        'code_file' => 'Kód na náhodné cestě z několika možností',
        'decoys' => 'Sada návnadových souborů s falešnými kódy',
        'core_series' => 'Řada podobně pojmenovaných souborů',
        'core_mkdir' => 'Prázdná složka',
        'core_access_log' => 'Webový přístupový log',
        'core_level_log' => 'Aplikační log s úrovněmi (INFO/WARN/ERROR…)',
        'core_auth_log' => 'Log přihlášení (auth.log)',
        'core_syslog' => 'Obecný systémový log (syslog)',
        'core_filler_lines' => 'Vycpávkové věty s volitelnou „jehličkou“',
        'core_unique_row' => 'Opakující se řádky + jeden jedinečný',
        'core_diff_pair' => 'Dva soubory lišící se jedním řádkem',
        'core_wrapped_message' => 'Text zabalený do base64/hex/rot13',
        'core_number_base' => 'Číslo v jiné číselné soustavě',
        'core_env_var' => 'Proměnná prostředí',
    ];
}

function lab58e_check_catalog(): array
{
    return [
        'core_file_exists' => 'Soubor existuje',
        'core_dir_exists' => 'Složka existuje',
        'core_not_exists' => 'Cesta už neexistuje (smazáno)',
        'core_files_exist' => 'Víc souborů existuje (paths = seznam)',
        'core_children_count' => 'Počet položek ve složce (dir, suffix, count/min/max)',
        'file_contains' => 'Soubor obsahuje/rovná se textu (path, contains|equals, ci)',
        'core_executable' => 'Soubor je spustitelný',
        'service_running' => 'Služba běží (service)',
    ];
}

/** @return list<string> šablonové tokeny, které v answer/solution/checks nejdou spolehlivě použít (potřebují živé rng, které tam není). */
function lab58e_forbidden_template_tokens(): array
{
    return ['{TOKEN}', '{DECOY}', '{NUM}', '{WORD}', '{NAME}'];
}

// ---------------------------------------------------------------------------
// Úložiště
// ---------------------------------------------------------------------------

function lab58e_storage_path(): string
{
    return lab57_storage_dir() . '/lab_v58_custom_levels.json.php';
}

/** @return array<string,array> id => záznam */
function lab58e_all(): array
{
    $data = lab57_store_read(lab58e_storage_path());
    return is_array($data['levels'] ?? null) ? $data['levels'] : [];
}

function lab58e_get(string $id): ?array
{
    $row = lab58e_all()[$id] ?? null;
    return is_array($row) ? $row : null;
}

/** @return list<array> pro danou třídu (všechny stavy, pro učitelský seznam) */
function lab58e_for_class(string $classId): array
{
    return array_values(array_filter(lab58e_all(), static fn(array $r): bool => in_array($classId, (array)($r['classes'] ?? []), true)));
}

/** @return list<array> jen zveřejněné pro danou třídu (pro registraci balíčku) */
function lab58e_published_for_class(string $classId): array
{
    return array_values(array_filter(lab58e_for_class($classId), static fn(array $r): bool => (string)($r['status'] ?? '') === 'published'));
}

function lab58e_new_id(array $existing): string
{
    do { $id = 'u-' . bin2hex(random_bytes(4)); } while (isset($existing[$id]));
    return $id;
}

function lab58e_save(array $row): array
{
    $stored = lab57_store_update(lab58e_storage_path(), static function (array $data) use ($row): array {
        $levels = is_array($data['levels'] ?? null) ? $data['levels'] : [];
        $id = (string)($row['id'] ?? '');
        if ($id === '' || !isset($levels[$id])) { $id = lab58e_new_id($levels); $row['id'] = $id; $row['created_at'] = date(DATE_ATOM); }
        $row['updated_at'] = date(DATE_ATOM);
        $levels[$id] = $row;
        $data['levels'] = $levels;
        $data['saved_row_id'] = $id;
        return $data;
    });
    return $stored['levels'][(string)$stored['saved_row_id']];
}

function lab58e_delete(string $id): void
{
    lab57_store_update(lab58e_storage_path(), static function (array $data) use ($id): array {
        unset($data['levels'][$id]);
        return $data;
    });
}

function lab58e_set_fields(string $id, array $fields): ?array
{
    $result = null;
    lab57_store_update(lab58e_storage_path(), static function (array $data) use ($id, $fields, &$result): array {
        if (!isset($data['levels'][$id]) || !is_array($data['levels'][$id])) return $data;
        $data['levels'][$id] = array_merge($data['levels'][$id], $fields, ['updated_at' => date(DATE_ATOM)]);
        $result = $data['levels'][$id];
        return $data;
    });
    return $result;
}

// ---------------------------------------------------------------------------
// Validace vstupu z formuláře (tvar, limity, katalog) – odděleno od CNT-03 (obsah)
// ---------------------------------------------------------------------------

/** Rozparsuje JSON parametry z textarea; prázdné/neplatné → []. */
function lab58e_parse_params(string $json): array
{
    $json = trim($json);
    if ($json === '') return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/**
 * @param array $input syrový $_POST (už s poli files[]/hints[]/… jako pole)
 * @return array{errors:list<string>, row:array} row = normalizovaný záznam (bez id/status/timestamps)
 */
function lab58e_validate_input(array $input): array
{
    $errors = [];
    $title = trim((string)($input['title'] ?? ''));
    $story = trim((string)($input['story'] ?? ''));
    $task = trim((string)($input['task'] ?? ''));
    $learn = trim((string)($input['learn'] ?? ''));
    $type = (string)($input['type'] ?? '');
    $difficulty = max(1, min(3, (int)($input['difficulty'] ?? 1)));
    $minutes = max(1, min(30, (int)($input['minutes'] ?? 5)));
    $classes = array_values(array_intersect((array)($input['classes'] ?? []), LAB58E_CLASS_IDS));

    if ($title === '' || mb_strlen($title) > LAB58E_MAX_TITLE) $errors[] = 'Název je povinný (max ' . LAB58E_MAX_TITLE . ' znaků).';
    if ($story === '' || mb_strlen($story) > LAB58E_MAX_STORY) $errors[] = 'Příběh je povinný (max ' . LAB58E_MAX_STORY . ' znaků).';
    if ($task === '' || mb_strlen($task) > LAB58E_MAX_TASK) $errors[] = 'Úkol je povinný (max ' . LAB58E_MAX_TASK . ' znaků).';
    if (mb_strlen($learn) > LAB58E_MAX_LEARN) $errors[] = 'Ponaučení je moc dlouhé (max ' . LAB58E_MAX_LEARN . ' znaků).';
    if (!in_array($type, ['code', 'answer', 'check', 'golf'], true)) $errors[] = 'Vyber typ úlohy (code/answer/check/golf).';
    if ($classes === []) $errors[] = 'Vyber aspoň jednu třídu.';

    $hints = [];
    foreach ((array)($input['hints'] ?? []) as $hint) {
        $hint = trim((string)$hint);
        if ($hint === '') continue;
        if (mb_strlen($hint) > LAB58E_MAX_HINT) { $errors[] = 'Nápověda je moc dlouhá (max ' . LAB58E_MAX_HINT . ' znaků).'; continue; }
        $hints[] = $hint;
    }
    $hints = array_slice($hints, 0, 3);
    if ($hints === []) $errors[] = 'Doplň aspoň jednu nápovědu (1.–3. stupeň).';

    $files = [];
    foreach ((array)($input['files'] ?? []) as $file) {
        $path = trim((string)($file['path'] ?? ''));
        $content = (string)($file['content'] ?? '');
        if ($path === '' && $content === '') continue;
        if ($path === '') { $errors[] = 'Soubor bez cesty.'; continue; }
        if (!preg_match('#^[~/]?[\p{L}0-9 ._/~+\-]{1,180}$#u', $path)) { $errors[] = 'Neplatná cesta souboru: ' . $path; continue; }
        if (strlen($content) > LAB58E_MAX_FILE_BYTES) { $errors[] = 'Soubor „' . $path . '“ je moc velký (max 64 KB).'; continue; }
        $files[] = ['path' => $path, 'content' => $content];
    }
    if (count($files) > LAB58E_MAX_FILES) $errors[] = 'Moc souborů (max ' . LAB58E_MAX_FILES . ').';
    $files = array_slice($files, 0, LAB58E_MAX_FILES);

    $catalog = lab58e_generator_catalog();
    $generators = [];
    foreach ((array)($input['generators'] ?? []) as $gen) {
        $name = (string)($gen['name'] ?? '');
        if ($name === '') continue;
        if (!isset($catalog[$name])) { $errors[] = 'Neznámý generátor: ' . $name; continue; }
        $generators[] = [$name, lab58e_parse_params((string)($gen['params'] ?? ''))];
    }
    $generators = array_slice($generators, 0, LAB58E_MAX_GENERATORS);

    $checks = [];
    if ($type === 'check') {
        $checkCatalog = lab58e_check_catalog();
        foreach ((array)($input['checks'] ?? []) as $chk) {
            $name = (string)($chk['name'] ?? '');
            if ($name === '') continue;
            if (!isset($checkCatalog[$name])) { $errors[] = 'Neznámá kontrola: ' . $name; continue; }
            $label = trim((string)($chk['label'] ?? $checkCatalog[$name]));
            $params = lab58e_parse_params((string)($chk['params'] ?? ''));
            $params['label'] = $label;
            $checks[] = [$name, $params];
        }
        $checks = array_slice($checks, 0, LAB58E_MAX_CHECKS);
        if ($checks === []) $errors[] = 'Typ „oprava“ potřebuje aspoň jednu kontrolu.';
    }

    $answer = '';
    $answerFormat = '';
    if ($type === 'answer') {
        $answer = trim((string)($input['answer'] ?? ''));
        $answerFormat = trim((string)($input['answer_format'] ?? ''));
        if ($answer === '' || mb_strlen($answer) > LAB58E_MAX_ANSWER) $errors[] = 'Doplň očekávanou odpověď (max ' . LAB58E_MAX_ANSWER . ' znaků).';
        foreach (lab58e_forbidden_template_tokens() as $tok) if (str_contains($answer, $tok)) $errors[] = 'Odpověď nesmí obsahovat ' . $tok . ' (funguje jen v souborech).';
    }

    $golfReference = '';
    if ($type === 'golf') {
        $golfReference = trim((string)($input['golf_reference'] ?? ''));
        if ($golfReference === '') $errors[] = 'Doplň referenční příkaz pro golf.';
    }

    $solution = [];
    foreach (preg_split('/\r?\n/', (string)($input['solution'] ?? '')) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') continue;
        foreach (lab58e_forbidden_template_tokens() as $tok) if (str_contains($line, $tok)) $errors[] = 'Řešení nesmí obsahovat ' . $tok . ' (funguje jen v souborech): ' . $line;
        $solution[] = $line;
    }
    if ($solution === []) $errors[] = 'Doplň referenční řešení (aspoň jeden příkaz na řádek) – bez něj nejde ověřit řešitelnost.';
    if ($type === 'code' && !in_array(true, array_map(static fn(string $l): bool => str_starts_with(trim($l), 'submit'), $solution), true)) {
        $errors[] = 'Řešení pro typ „kód“ musí končit příkazem submit {CODE}.';
    }
    if ($type === 'golf' && $golfReference !== '') $solution = [$golfReference, 'submit'];

    $row = [
        'type' => $type, 'title' => $title, 'story' => $story, 'task' => $task, 'learn' => $learn,
        'difficulty' => $difficulty, 'minutes' => $minutes, 'classes' => $classes, 'hints' => $hints,
        'files' => $files, 'generators' => $generators, 'checks' => $checks,
        'answer' => $answer, 'answer_format' => $answerFormat, 'golf_reference' => $golfReference, 'solution' => $solution,
    ];
    return ['errors' => $errors, 'row' => $row];
}

// ---------------------------------------------------------------------------
// Převod uloženého záznamu na deklarativní úroveň (žádné closures z dat)
// ---------------------------------------------------------------------------

function lab58e_points_for(int $difficulty): int
{
    return [1 => 100, 2 => 150, 3 => 200][max(1, min(3, $difficulty))] ?? 100;
}

/** @return array deklarativní úroveň (generate/checks/answer/solution jsou pole/řetězce, ne closures) */
function lab58e_to_level(array $row, string $packId): array
{
    $id = (string)($row['id'] ?? '');
    $generate = [];
    if ((array)($row['files'] ?? []) !== []) $generate[] = ['core_file_set', ['files' => (array)$row['files']]];
    foreach ((array)($row['generators'] ?? []) as $gen) {
        if (is_array($gen) && isset($gen[0])) $generate[] = [(string)$gen[0], (array)($gen[1] ?? [])];
    }
    $level = [
        'id' => $id, 'pack' => $packId, 'type' => (string)($row['type'] ?? 'code'), 'title' => (string)($row['title'] ?? ''),
        'story' => (string)($row['story'] ?? ''), 'task' => (string)($row['task'] ?? ''), 'learn' => (string)($row['learn'] ?? ''),
        'difficulty' => (int)($row['difficulty'] ?? 1), 'points' => lab58e_points_for((int)($row['difficulty'] ?? 1)),
        'minutes' => (int)($row['minutes'] ?? 5), 'hints' => array_values((array)($row['hints'] ?? [])),
        'commands' => [], 'v' => (int)($row['v'] ?? 1), 'generate' => $generate,
        'solution' => array_values((array)($row['solution'] ?? [])),
    ];
    if ($level['type'] === 'check') $level['checks'] = (array)($row['checks'] ?? []);
    if ($level['type'] === 'answer') { $level['answer'] = (string)($row['answer'] ?? ''); $level['answer_format'] = (string)($row['answer_format'] ?? ''); }
    if ($level['type'] === 'golf') $level['golf'] = ['tests' => 3, 'reference' => (string)($row['golf_reference'] ?? '')];
    return $level;
}

// ---------------------------------------------------------------------------
// Kontrola řešitelnosti (N semínek) + obsahu (CNT-03) – volá se z náhledu i před zveřejněním
// ---------------------------------------------------------------------------

/** @return array{ok:bool,solved:int,total:int,detail:list<string>,content_ok:bool,issues:list<array>} */
function lab58e_check_row(array $row, int $seeds = LAB58E_PREVIEW_SEEDS): array
{
    $level = lab58e_to_level($row, 'x-preview');
    $shapeErrors = function_exists('lab58_validate_level') ? lab58_validate_level($level) : [];
    // lab58_try_solution() očekává už připravenou úroveň (generate/checks/answer/solution
    // převedené na closures) – stejně jako lab57_levels() dělá pro každou úroveň při načtení.
    if ($shapeErrors === [] && function_exists('lab58_level_prepare')) {
        $prepared = lab58_level_prepare($level, 'x-preview', 1);
        if (is_string($prepared)) $shapeErrors[] = $prepared; else $level = $prepared;
    }
    $now = time();
    $solved = 0;
    $detail = [];
    if ($shapeErrors === []) {
        for ($i = 0; $i < $seeds; $i++) {
            $seed = 'lab58e-preview|' . (string)($row['id'] ?? 'draft') . '|' . $i;
            try {
                $result = lab58_try_solution($level, $seed, $now, 'EDU-TEST-0000');
                if (!empty($result['solved'])) { $solved++; continue; }
                $detail[] = 'Semínko ' . ($i + 1) . ': referenční řešení úlohu nevyřešilo.';
            } catch (Throwable $e) {
                $detail[] = 'Semínko ' . ($i + 1) . ': ' . $e->getMessage();
            }
        }
    } else {
        $detail = $shapeErrors;
    }
    $content = function_exists('cnt58_check_fields')
        ? cnt58_check_fields(['název' => $row['title'] ?? '', 'příběh' => $row['story'] ?? '', 'úkol' => $row['task'] ?? '', 'ponaučení' => $row['learn'] ?? '', 'nápovědy' => implode("\n", (array)($row['hints'] ?? []))])
        : ['ok' => true, 'issues' => []];
    return ['ok' => $shapeErrors === [] && $solved === $seeds, 'solved' => $solved, 'total' => $seeds, 'detail' => $detail, 'content_ok' => (bool)$content['ok'], 'issues' => $content['issues']];
}

// ---------------------------------------------------------------------------
// Čisté brány (bez vedlejších účinků, bez exit) – používá je POST handler i audit/testy.
// ---------------------------------------------------------------------------

/** @return array{ok:bool,errors:list<string>,row:array} */
function lab58e_can_save(array $input): array
{
    $v = lab58e_validate_input($input);
    $errors = $v['errors'];
    $row = $v['row'];
    if ($errors === [] && function_exists('cnt58_check_fields')) {
        $content = cnt58_check_fields(['název' => $row['title'], 'příběh' => $row['story'], 'úkol' => $row['task'], 'ponaučení' => $row['learn'], 'nápovědy' => implode("\n", $row['hints'])]);
        if (!$content['ok']) {
            foreach ($content['issues'] as $issue) if (($issue['severity'] ?? 'warn') === 'block') $errors[] = ($issue['field'] ?? '') . ': ' . $issue['message'];
        }
    }
    return ['ok' => $errors === [], 'errors' => $errors, 'row' => $row];
}

/** @return array{ok:bool,reason:string,check:?array} reason: ''|'checklist'|'unsolvable'|'content' */
function lab58e_can_publish(array $row, array $checklist): array
{
    foreach (function_exists('cnt58_checklist_items') ? cnt58_checklist_items() : [] as $item) {
        if (empty($checklist[$item['id']])) return ['ok' => false, 'reason' => 'checklist', 'check' => null];
    }
    $result = lab58e_check_row($row, LAB58E_PREVIEW_SEEDS);
    if (!$result['ok']) return ['ok' => false, 'reason' => 'unsolvable', 'check' => $result];
    if (!$result['content_ok']) return ['ok' => false, 'reason' => 'content', 'check' => $result];
    return ['ok' => true, 'reason' => '', 'check' => $result];
}

// ---------------------------------------------------------------------------
// Učitel: POST akce (CSRF a oprávnění content.manage ověřil teacher.php)
// ---------------------------------------------------------------------------

/** v59 · AUTHZ58-07: úloha (i uložená) smí mít jen třídy v rozsahu učitele; prázdný seznam = jen admin; legacy/CLI = vždy true. */
function lab58e_classes_in_scope(array $classes): bool
{
    if (!function_exists('teacher59_can_classes')) return true;
    return teacher59_can_classes(array_values(array_map('strval', $classes)));
}

function lab58e_teacher_handle_post(string $action, array $modules): void
{
    $classId = (string)($_POST['class_id'] ?? '');
    if (!in_array($classId, LAB58E_CLASS_IDS, true)) $classId = (string)array_key_first($modules) ?: LAB58E_CLASS_IDS[0];
    $done = static function (string $message, string $type = 'ok', string $id = '') use ($classId): never {
        if (function_exists('teacher_flash')) teacher_flash($message, $type);
        $params = ['tab' => 'editor', 'class' => $classId];
        if ($id !== '') $params['level'] = $id;
        if (function_exists('teacher_redirect')) teacher_redirect($params);
        exit;
    };

    switch ($action) {
        case 'lab58e_save': {
            $gate = lab58e_can_save($_POST);
            if (!$gate['ok']) $done('Nejde uložit: ' . implode(' ', $gate['errors']), 'error', (string)($_POST['id'] ?? ''));
            $row = $gate['row'];
            $id = (string)($_POST['id'] ?? '');
            $existing = $id !== '' ? lab58e_get($id) : null;
            if ($existing !== null && !lab58e_classes_in_scope((array)($existing['classes'] ?? []))) $done('Úloha nebyla nalezena.', 'error');
            if (!lab58e_classes_in_scope((array)($row['classes'] ?? []))) $done('Úlohu můžeš uložit jen pro své třídy.', 'error', $id);
            $row['id'] = $existing['id'] ?? '';
            $row['status'] = $existing['status'] ?? 'draft';
            // Úprava zveřejněné úlohy ji vrátí do konceptu – po každé změně obsahu je potřeba nový úspěšný test.
            if (($existing['status'] ?? '') === 'published') $row['status'] = 'draft';
            $row['created_by'] = $existing['created_by'] ?? (function_exists('teacher_display_name') ? teacher_display_name() : 'učitel');
            $row['checklist'] = $existing['checklist'] ?? [];
            $row['last_check'] = null;
            $saved = lab58e_save($row);
            $done('Úloha „' . $saved['title'] . '“ uložena jako koncept.', 'ok', (string)$saved['id']);
        }
        case 'lab58e_check': {
            $id = (string)($_POST['id'] ?? '');
            $row = lab58e_get($id);
            if ($row === null || !lab58e_classes_in_scope((array)($row['classes'] ?? []))) $done('Úloha nebyla nalezena.', 'error');
            $result = lab58e_check_row($row, LAB58E_PREVIEW_SEEDS);
            lab58e_set_fields($id, ['last_check' => ['at' => date(DATE_ATOM), 'ok' => $result['ok'], 'solved' => $result['solved'], 'total' => $result['total'], 'content_ok' => $result['content_ok'], 'detail' => array_slice($result['detail'], 0, 10), 'issues' => $result['issues']]]);
            $done($result['ok'] ? 'Test proběhl: řešitelné (' . $result['solved'] . '/' . $result['total'] . '), obsah ' . ($result['content_ok'] ? 'v pořádku.' : 'má upozornění – zkontroluj náhled.') : 'Test selhal: úloha není spolehlivě řešitelná (' . $result['solved'] . '/' . $result['total'] . '). Detaily v náhledu.', $result['ok'] ? 'ok' : 'error', $id);
        }
        case 'lab58e_publish': {
            $id = (string)($_POST['id'] ?? '');
            $row = lab58e_get($id);
            if ($row === null || !lab58e_classes_in_scope((array)($row['classes'] ?? []))) $done('Úloha nebyla nalezena.', 'error');
            $checklist = [];
            foreach (function_exists('cnt58_checklist_items') ? cnt58_checklist_items() : [] as $item) $checklist[$item['id']] = !empty($_POST['checklist'][$item['id']]);
            $gate = lab58e_can_publish($row, $checklist);
            if ($gate['check'] !== null) {
                $result = $gate['check'];
                lab58e_set_fields($id, ['last_check' => ['at' => date(DATE_ATOM), 'ok' => $result['ok'], 'solved' => $result['solved'], 'total' => $result['total'], 'content_ok' => $result['content_ok'], 'detail' => array_slice($result['detail'], 0, 10), 'issues' => $result['issues']], 'checklist' => $checklist]);
            }
            if ($gate['reason'] === 'checklist') $done('Před zveřejněním potvrď celý checklist.', 'error', $id);
            if ($gate['reason'] === 'unsolvable') $done('Nejde zveřejnit: úloha není spolehlivě řešitelná (' . $gate['check']['solved'] . '/' . $gate['check']['total'] . ' semínek). Uprav ji a zkus to znovu.', 'error', $id);
            if ($gate['reason'] === 'content') $done('Nejde zveřejnit: kontrola obsahu našla problém (zakázaný výraz nebo osobní údaj). Uprav text.', 'error', $id);
            lab58e_set_fields($id, ['status' => 'published']);
            $done('Úloha „' . $row['title'] . '“ je zveřejněná pro vybrané třídy.', 'ok', $id);
        }
        case 'lab58e_unpublish': {
            $id = (string)($_POST['id'] ?? '');
            $row = lab58e_get($id);
            if ($row === null || !lab58e_classes_in_scope((array)($row['classes'] ?? []))) $done('Úloha nebyla nalezena.', 'error');
            lab58e_set_fields($id, ['status' => 'draft']);
            $done('Úloha je zpátky v konceptu – žáci ji dočasně nevidí.', 'ok', $id);
        }
        case 'lab58e_delete': {
            $id = (string)($_POST['id'] ?? '');
            $row = lab58e_get($id);
            if ($row === null || !lab58e_classes_in_scope((array)($row['classes'] ?? []))) $done('Úloha nebyla nalezena.', 'error');
            lab58e_delete($id);
            $done('Úloha „' . $row['title'] . '“ byla smazána.');
        }
        default:
            throw new RuntimeException('Neznámá akce editoru úrovní.');
    }
}

// ---------------------------------------------------------------------------
// Registrace balíčků „Úlohy od učitele“ (jeden na třídu) – běží na straně žáka i učitele,
// protože tento soubor require_once-uje linux_v58_levels_generators.php (auto-load jádra).
// ---------------------------------------------------------------------------

function lab58e_class_suffix(string $classId): string
{
    $known = ['class_1a' => '1a', 'class_2a' => '2a', 'class_3a' => '3a', 'class_4a' => '4a'];
    return $known[$classId] ?? substr((string)preg_replace('/[^a-z0-9]/', '', strtolower($classId)), 0, 12);
}

if (function_exists('lab58_register_pack')) {
    foreach (LAB58E_CLASS_IDS as $lab58eClassId) {
        $lab58ePackId = 'ucitel-' . lab58e_class_suffix($lab58eClassId);
        lab58_register_pack(
            ['id' => $lab58ePackId, 'title' => 'Úlohy od učitele', 'description' => 'Vlastní úlohy, které pro tvou třídu připravil učitel.', 'order' => 90, 'classes' => [$lab58eClassId], 'unlock' => 'free', 'icon' => '✎', 'tone' => 'teal', 'source' => 'store'],
            static function () use ($lab58eClassId, $lab58ePackId): array {
                $out = [];
                foreach (lab58e_published_for_class($lab58eClassId) as $row) $out[] = lab58e_to_level($row, $lab58ePackId);
                return $out;
            }
        );
    }
}
