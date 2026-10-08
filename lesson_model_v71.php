<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v71 · jednotný model lekce `lm71` (jen čtení, bez výstupu).
 *
 * Jediný čtecí normalizátor obsahu lekcí: 8 zdrojů z lesson_model_v71_sources.php + overlay obsahové stopy.
 *   surová vrstva  lm71_raw()            – řádky zdrojů podle precedence, cache v cache/lesson_model/<třída>.php
 *                                          (klíč = podpis souborů, záloha = obsahový SHA-256; nikdy ne ve storage/)
 *   kurikulum      lm71_curriculum_rows() – stejný tvar jako dřív teacher_curriculum_lessons() (Plán, Režim hodiny, kalendář)
 *   žákovská cesta lm71_bundle()          – v56_lesson_bundle() nad stejnými zdroji (žák vidí totéž, jen z runtime cache)
 *   model          lm71_lesson()          – normalizovaný tvar (docs: sekce 2 PROMPT_V71_V75) + skóre úplnosti
 *   den bez lekce  lm71_day()             – karta z calendar_days_v71.php (+ overlay dnů)
 * Original zdrojových souborů se nikdy nepřepisuje. Texty jsou česky (cockpit).
 */

require_once __DIR__ . '/lesson_model_v71_sources.php';

const LM71_TITLE_MAX = 70;
const LM71_GOAL_MIN = 20;
const LM71_TEMPLATE_MIN_LESSONS = 3;
const LM71_EXIT_VARIANTS_MIN = 3;
const LM71_LESSON_MINUTES = 90;
/** Pole, která overlay smí nastavit (cokoli jiného se ignoruje). */
const LM71_OVERLAY_FIELDS = ['title', 'goal', 'success_criteria', 'curriculum', 'competencies', 'prerequisites', 'timeline', 'materials', 'tools',
    'differentiation', 'tasks', 'assessment', 'exit_ticket', 'homework', 'safety', 'teacher_notes', 'substitution', 'worksheet', 'status', 'reviewed_at', 'version',
    'glossary'];   // v72: id termínů z lesson_glossary_v72.php

// ---------------------------------------------------------------------------------------------------------------
// Surová vrstva a cache
// ---------------------------------------------------------------------------------------------------------------

function lm71_class_ok(string $classId): bool
{
    return preg_match('/^class_[a-z0-9]{1,8}$/D', $classId) === 1;
}

function lm71_cache_dir(): string
{
    $env = getenv('EDUCANET_LM71_CACHE_DIR');
    if (is_string($env) && $env !== '' && (str_starts_with($env, '/') || preg_match('~^[A-Za-z]:[\\\\/]~', $env) === 1)) return rtrim(str_replace('\\', '/', $env), '/');
    return __DIR__ . '/cache/lesson_model';
}

function lm71_cache_path(string $classId): string
{
    return lm71_cache_dir() . '/' . (lm71_class_ok($classId) ? $classId : 'unknown') . '.php';
}

/** Data všech zdrojových souborů (jednou za požadavek). @return array<string,array> */
function lm71_source_data(): array
{
    static $data = null;
    if ($data !== null) return $data;
    $data = [];
    foreach (lm71_sources() as $id => $src) {
        if ($src['shape'] === 'primary') continue;
        $path = __DIR__ . '/' . $src['file'];
        $loaded = is_file($path) ? require $path : [];
        $data[$id] = is_array($loaded) ? $loaded : [];
    }
    return $data;
}

/** Overlay obsahové stopy: ['lessons' => [třída => [číslo => pole]], 'days' => [třída => [slot => pole]], 'files' => list]. */
function lm71_overlay_data(): array
{
    static $overlay = null;
    if ($overlay !== null) return $overlay;
    $overlay = ['lessons' => [], 'days' => [], 'files' => []];
    foreach (lm71_overlay_files() as $file) {
        $loaded = require __DIR__ . '/' . $file;
        if (is_array($loaded)) $overlay = lm71_overlay_merge($overlay, $file, $loaded);
    }
    return $overlay;
}

/**
 * Přidá jeden soubor overlaye (čistá funkce): pole po poli, pozdější soubor vyhrává; jen známá pole
 * (LM71_OVERLAY_FIELDS), jen platná třída, lekce 1–28 a slot dne 0–99. Tvar souboru:
 *   ['class_3a' => ['lessons' => [5 => [...pole...]], 'days' => [29 => [...pole...]]], ...]
 */
function lm71_overlay_merge(array $overlay, string $file, array $loaded): array
{
    $overlay['files'][] = $file;
    foreach ($loaded as $classId => $parts) {
        if (!is_string($classId) || !lm71_class_ok($classId) || !is_array($parts)) continue;
        foreach (['lessons' => [1, LM71_LESSONS], 'days' => [0, 99]] as $kind => [$min, $max]) {
            foreach ((array)($parts[$kind] ?? []) as $n => $fields) {
                if (!is_array($fields) || (int)$n < $min || (int)$n > $max) continue;
                $clean = array_intersect_key($fields, array_flip(LM71_OVERLAY_FIELDS));
                $current = $overlay[$kind][$classId][(int)$n] ?? [];
                $overlay[$kind][$classId][(int)$n] = array_replace($current, $clean, ['_file' => $file]);
            }
        }
    }
    return $overlay;
}

function lm71_norm_text(string $text): string
{
    return mb_strtolower(trim((string)preg_replace('/\s+/u', ' ', $text)));
}

/** Sestaví surovou vrstvu třídy přímo ze zdrojů (bez cache). */
function lm71_build_raw(string $classId): array
{
    $sources = lm71_sources();
    $lessons = [1 => ['number' => 1, 'lm71_source' => 'primary']];
    $conflicts = [];
    foreach (lm71_source_data() as $id => $data) {
        $classRows = $data[$classId] ?? null;
        if (!is_array($classRows)) continue;
        $list = $sources[$id]['shape'] === 'single' ? [array_replace($classRows, ['number' => $sources[$id]['lessons'][0]])] : $classRows;
        foreach ($list as $row) {
            if (!is_array($row)) continue;
            $n = (int)($row['number'] ?? 0);
            if ($n < 1 || $n > LM71_LESSONS) { $conflicts[] = ['type' => 'neplatné číslo', 'number' => $n, 'source' => $id]; continue; }
            if (isset($lessons[$n])) { $conflicts[] = ['type' => 'duplicitní lekce', 'number' => $n, 'source' => $id, 'kept' => $lessons[$n]['lm71_source']]; continue; }
            $row['number'] = $n;
            $row['lm71_source'] = $id;
            $lessons[$n] = $row;
        }
    }
    ksort($lessons);
    $taskLessons = [];
    foreach ($lessons as $n => $row) {
        $seen = [];
        foreach ((array)($row['steps'] ?? []) as $step) {
            foreach (is_array($step) ? (array)($step['tasks'] ?? []) : [] as $task) {
                $key = substr(sha1(lm71_norm_text((string)$task)), 0, 12);
                if (!isset($seen[$key])) { $seen[$key] = true; $taskLessons[$key] = ($taskLessons[$key] ?? 0) + 1; }
            }
        }
    }
    $overlay = lm71_overlay_data();
    return [
        'version' => LM71_MODEL_VERSION, 'class' => $classId, 'lessons' => $lessons, 'conflicts' => $conflicts,
        'template_tasks' => array_filter($taskLessons, static fn(int $c): bool => $c >= LM71_TEMPLATE_MIN_LESSONS),
        'overlay' => ['lessons' => $overlay['lessons'][$classId] ?? [], 'days' => $overlay['days'][$classId] ?? [], 'files' => $overlay['files']],
    ];
}

function lm71_cache_read(string $path): ?array
{
    if (!is_file($path)) return null;
    try {
        $data = include $path;
    } catch (Throwable $e) {
        return null;
    }
    return is_array($data) && (int)($data['version'] ?? 0) === LM71_MODEL_VERSION ? $data : null;
}

function lm71_cache_write(string $path, array $payload): bool
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) return false;
    if (!is_file($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess', "# EDUCANET v71 · odvozená cache modelu lekce – nikdy přes web.\n<IfModule mod_authz_core.c>\n    Require all denied\n"
            . "    <FilesMatch \".\">\n        Require all denied\n    </FilesMatch>\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n"
            . "    <FilesMatch \".\">\n        Order allow,deny\n        Deny from all\n    </FilesMatch>\n</IfModule>\n");
    }
    $php = "<?php\n\ndeclare(strict_types=1);\n\n// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.\nreturn "
        . var_export($payload, true) . ";\n";
    $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $php, LOCK_EX) === false) return false;
    if (!@rename($tmp, $path)) { @unlink($tmp); return false; }
    if (function_exists('opcache_invalidate')) @opcache_invalidate($path, true);
    return true;
}

/** Surová vrstva třídy z cache (čerstvost: podpis souborů, pak obsahový hash), jinak ze zdrojů. */
function lm71_raw(string $classId): array
{
    static $memo = [];
    if (!lm71_class_ok($classId)) return ['version' => LM71_MODEL_VERSION, 'class' => $classId, 'lessons' => [], 'conflicts' => [], 'template_tasks' => [], 'overlay' => ['lessons' => [], 'days' => [], 'files' => []]];
    if (isset($memo[$classId])) return $memo[$classId];
    $files = lm71_model_files();
    $sig = lm71_files_signature($files);
    $path = lm71_cache_path($classId);
    $cached = lm71_cache_read($path);
    if ($cached !== null && (string)($cached['sig'] ?? '') === $sig) return $memo[$classId] = $cached;
    $hash = lm71_files_hash($files);
    if ($cached !== null && (string)($cached['hash'] ?? '') === $hash) {
        $cached['sig'] = $sig;
        lm71_cache_write($path, $cached);
        return $memo[$classId] = $cached;
    }
    $built = lm71_build_raw($classId) + ['sig' => $sig, 'hash' => $hash, 'built_at' => date(DATE_ATOM)];
    if (!lm71_cache_write($path, $built)) {
        static $logged = false;
        if (!$logged) { $logged = true; error_log('EDUCANET v71: cache modelu lekce nelze zapsat (' . lm71_cache_dir() . ') – model se skládá při každém požadavku.'); }
    }
    return $memo[$classId] = $built;
}

/**
 * Stav cache modelu i žákovské runtime cache (jen čtení, nic nepřestavuje).
 * @return array{model:array<string,string>,runtime:array<string,string>,runtime_hash:string}
 */
function lm71_cache_status(?string $modelDir = null): array
{
    $modelHash = lm71_files_hash(lm71_model_files());
    $runtimeHash = lm71_files_hash(lm71_runtime_cache_files());
    $out = ['model' => [], 'runtime' => [], 'runtime_hash' => $runtimeHash];
    foreach (LM71_CLASSES as $classId) {
        $cached = lm71_cache_read($modelDir !== null ? rtrim($modelDir, '/') . '/' . $classId . '.php' : lm71_cache_path($classId));
        $out['model'][$classId] = $cached === null ? 'chybí' : ((string)($cached['hash'] ?? '') === $modelHash ? 'aktuální' : 'zastaralá');
        $runtimePath = __DIR__ . '/cache/runtime/' . $classId . '.php';
        $runtime = null;
        if (is_file($runtimePath)) {
            try { $runtime = include $runtimePath; } catch (Throwable $e) { $runtime = null; }
        }
        $out['runtime'][$classId] = !is_array($runtime) ? 'chybí' : (!isset($runtime['_sources_hash']) ? 'bez otisku' : ((string)$runtime['_sources_hash'] === $runtimeHash ? 'aktuální' : 'zastaralá'));
    }
    return $out;
}

// ---------------------------------------------------------------------------------------------------------------
// Kurikulum (původní tvar teacher_curriculum_lessons) a žákovská cesta
// ---------------------------------------------------------------------------------------------------------------

/** Řádky lekcí 1–28 ve tvaru, který znají Plán a kurikulum, Režim hodiny a kalendář. */
function lm71_curriculum_rows(string $classId): array
{
    $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
    $rows = [];
    foreach (lm71_raw($classId)['lessons'] as $n => $row) {
        if ((int)$n === 1) {
            $rows[] = [
                'id' => 'lesson_1_primary', 'number' => 1, 'title' => 'Lekce 1 · Základní dvouhodinový blok',
                'goal' => (string)($module['lesson_note'] ?? 'Úvodní dvouhodinový blok.'),
                'knowledge' => array_slice(array_keys((array)($module['knowledgebase'] ?? [])), 0, 5),
                'steps' => [['id' => 'primary', 'title' => 'Primární blok']], 'worksheet' => [],
                'teacher_notes' => [(string)($module['intro'] ?? '')], 'source' => 'module', 'lm71_source' => 'primary',
            ];
            continue;
        }
        $row['source'] = (string)$row['lm71_source'] === 'next' ? 'next' : 'extended';
        $rows[] = $row;
    }
    return $rows;
}

/** Žákovská posloupnost lekce (témata, test, kroky) – v56_lesson_bundle nad zdroji lm71. */
function lm71_bundle(string $classId, array $module, int $number): array
{
    if (!function_exists('tut52_family')) require_once __DIR__ . '/tutorial_v52.php';
    if (!function_exists('v56_lesson_bundle')) require_once __DIR__ . '/learning_v56.php';
    $raw = lm71_raw($classId)['lessons'];
    $next = isset($raw[2]) ? [$classId => $raw[2]] : [];
    $extended = [$classId => array_values(array_filter($raw, static fn($row, $n): bool => (int)$n >= 3 && is_array($row), ARRAY_FILTER_USE_BOTH))];
    return v56_lesson_bundle($classId, $module, $number, $next, $extended);
}

// ---------------------------------------------------------------------------------------------------------------
// Normalizovaný model lekce
// ---------------------------------------------------------------------------------------------------------------

/** @return list<string> */
function lm71_text_list(mixed $value): array
{
    $out = [];
    foreach (is_array($value) ? $value : ($value === null || $value === '' ? [] : [$value]) as $item) {
        $text = trim(is_array($item) ? (string)($item['text'] ?? ($item['question'] ?? '')) : (string)$item);
        if ($text !== '') $out[] = $text;
    }
    return $out;
}

/** „0–10“ / „10-25“ → [od, do], jinak null. */
function lm71_parse_range(string $time): ?array
{
    return preg_match('/^\s*(\d{1,3})\s*[–—-]\s*(\d{1,3})\s*$/u', $time, $m) === 1 && (int)$m[2] > (int)$m[1] ? [(int)$m[1], (int)$m[2]] : null;
}

/**
 * Plán po minutách: overlay > `schedule` zdroje > odvozeně z kroků (součet minut kroků, příznak derived).
 * @return list<array{from:int,to:int,minutes:int,phase:string,teacher:string,student:string,form:string,materials:list<string>,derived:bool}>
 */
function lm71_timeline(array $raw, array $steps, ?array $overlay = null): array
{
    $segment = static fn(int $from, int $to, string $phase, string $teacher, string $student, string $form, array $materials, bool $derived): array => [
        'from' => $from, 'to' => $to, 'minutes' => $to - $from, 'phase' => $phase, 'teacher' => $teacher, 'student' => $student, 'form' => $form, 'materials' => $materials, 'derived' => $derived];
    $out = [];
    $schedule = is_array($overlay) ? $overlay : (array)($raw['schedule'] ?? []);
    foreach ($schedule as $row) {
        if (!is_array($row)) continue;
        $range = isset($row['from'], $row['to']) ? [(int)$row['from'], (int)$row['to']] : lm71_parse_range((string)($row['time'] ?? ''));
        if ($range === null || $range[1] <= $range[0]) continue;
        $out[] = $segment($range[0], $range[1], trim((string)($row['phase'] ?? ($row['title'] ?? ''))), trim((string)($row['teacher'] ?? ($row['text'] ?? ''))),
            trim((string)($row['student'] ?? '')), trim((string)($row['form'] ?? '')), lm71_text_list($row['materials'] ?? []), false);
    }
    if ($out !== []) return $out;
    $cursor = 0;
    foreach ($steps as $step) {
        $minutes = preg_match('/(\d{1,3})/', (string)($step['time'] ?? ''), $m) === 1 ? (int)$m[1] : 0;
        if ($minutes <= 0) continue;
        $out[] = $segment($cursor, $cursor + $minutes, (string)($step['title'] ?? ''), implode(' · ', array_slice(lm71_text_list($step['tasks'] ?? []), 0, 2)), '', '', [], true);
        $cursor += $minutes;
    }
    return $out;
}

/** Plán je souvislý od 0 do 90 minut a nevznikl dopočtem z kroků. */
function lm71_timeline_ok(array $timeline): bool
{
    if (count($timeline) < 3) return false;
    $cursor = 0;
    foreach ($timeline as $seg) {
        if (!empty($seg['derived']) || (int)$seg['from'] !== $cursor) return false;
        $cursor = (int)$seg['to'];
    }
    return $cursor === LM71_LESSON_MINUTES;
}

/** Kvízové položky kroků lekce. @return list<array{step:string,question:string,options:int,correct:int}> */
function lm71_quiz_items(array $raw): array
{
    $out = [];
    foreach ((array)($raw['steps'] ?? []) as $step) {
        if (!is_array($step) || trim((string)($step['question'] ?? '')) === '' || !is_array($step['options'] ?? null)) continue;
        $out[] = ['step' => (string)($step['id'] ?? ''), 'question' => trim((string)$step['question']), 'options' => count($step['options']), 'correct' => (int)($step['correct'] ?? -1)];
    }
    return $out;
}

/** Exit ticket: overlay (varianty) > krok „exit“ / „Exit ticket“ ve zdroji (jedna varianta). @return list<string> */
function lm71_exit_ticket(array $raw, ?array $overlay): array
{
    if (is_array($overlay)) return lm71_text_list($overlay['variants'] ?? $overlay);
    foreach ((array)($raw['steps'] ?? []) as $step) {
        if (!is_array($step)) continue;
        $isExit = (string)($step['id'] ?? '') === 'exit' || stripos((string)($step['title'] ?? ''), 'exit ticket') !== false;
        if ($isExit && trim((string)($step['question'] ?? '')) !== '') return [trim((string)$step['question'])];
    }
    return [];
}

/** Titulek bez českých znaků s diakritikou i bez českých spojek/předložek = anglický (heuristika reportu). */
function lm71_is_english_title(string $title): bool
{
    $title = trim((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', $title));
    if ($title === '' || preg_match('/[áčďéěíňóřšťúůýž]/iu', $title) === 1) return false;
    $words = preg_split('/[^\p{L}]+/u', mb_strtolower($title), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $czech = ['a', 'i', 'v', 've', 'na', 'pro', 's', 'se', 'z', 'ze', 'do', 'od', 'k', 'ke', 'o', 'po', 'za', 'bez', 'jak', 'co', 'je', 'nebo', 'jako', 'mezi', 'nad', 'pod', 'webu', 'webem', 'sit', 'site'];
    return array_intersect($words, $czech) === [];
}

/** Materiály k tématům lekce z learning_resources.php (bez obecných `_default`). @return list<array<string,mixed>> */
function lm71_materials(string $classId, array $topicKeys): array
{
    static $resources = null;
    if ($resources === null) {
        $loaded = is_file(__DIR__ . '/learning_resources.php') ? require __DIR__ . '/learning_resources.php' : [];
        $resources = is_array($loaded) ? $loaded : [];
    }
    $out = [];
    foreach ($topicKeys as $topic) {
        foreach ((array)($resources[$classId][$topic] ?? []) as $r) {
            if (!is_array($r) || trim((string)($r['url'] ?? '')) === '') continue;
            $id = 'res_' . substr(sha1((string)$r['url']), 0, 10);
            $out[$id] ??= ['id' => $id, 'topic' => (string)$topic, 'title' => (string)($r['title'] ?? ''), 'type' => (string)($r['type'] ?? 'link'), 'role' => 'doporučený',
                'required' => false, 'metadata' => isset($r['checked_at'], $r['license']) && (string)$r['checked_at'] !== '' && (string)$r['license'] !== ''];
        }
    }
    return array_values($out);
}

/** Pole modelu, která tvoří skóre úplnosti (12), s českým popisem pro odznak. */
function lm71_completeness_fields(): array
{
    return [
        'title' => 'český název do 70 znaků', 'goal' => 'cíl pro žáka', 'success_criteria' => 'kritéria úspěchu (2–4)', 'timeline' => 'plán po minutách (90 min)',
        'materials' => 'materiály k tématům', 'tasks' => 'konkrétní úkoly', 'differentiation' => 'diferenciace (podpora / standard / výzva)',
        'assessment' => 'formativní kontrola a rubrika', 'exit_ticket' => 'exit ticket (≥ 3 varianty, jiný než kvíz)', 'safety' => 'bezpečnost a licence',
        'teacher_notes' => 'poznámky pro učitele', 'substitution' => 'plán pro zástup',
    ];
}

/** Skóre úplnosti modelu (čistá funkce). @return array{score:int,total:int,complete:bool,checks:array<string,bool>,missing:list<string>} */
function lm71_completeness(array $m): array
{
    $quizQuestions = array_map('lm71_norm_text', array_column((array)($m['assessment']['checks'] ?? []), 'question'));
    $exit = (array)($m['exit_ticket'] ?? []);
    $concreteTasks = array_filter((array)($m['tasks'] ?? []), static fn(array $t): bool => empty($t['template']));
    $diff = (array)($m['differentiation'] ?? []);
    $rubric = (array)($m['assessment']['rubric'] ?? []);
    $criteria = (array)($m['goal']['success_criteria'] ?? []);
    $checks = [
        'title' => (string)$m['title'] !== '' && mb_strlen((string)$m['title']) <= LM71_TITLE_MAX && !lm71_is_english_title((string)$m['title']),
        'goal' => mb_strlen(trim((string)($m['goal']['student'] ?? ''))) >= LM71_GOAL_MIN,
        'success_criteria' => count($criteria) >= 2 && count($criteria) <= 4,
        'timeline' => lm71_timeline_ok((array)($m['timeline'] ?? [])),
        'materials' => (array)($m['materials'] ?? []) !== [],
        'tasks' => count($concreteTasks) >= 3,
        'differentiation' => trim((string)($diff['support'] ?? '')) !== '' && trim((string)($diff['standard'] ?? '')) !== '' && trim((string)($diff['challenge'] ?? '')) !== '',
        'assessment' => ((array)($m['assessment']['checks'] ?? []) !== [] || (array)($m['assessment']['formative'] ?? []) !== []) && count($rubric) >= 3 && count($rubric) <= 5,   // v72: kvíz nebo formativní kontrola
        'exit_ticket' => count($exit) >= LM71_EXIT_VARIANTS_MIN && array_intersect(array_map('lm71_norm_text', $exit), $quizQuestions) === [],
        'safety' => (array)($m['safety'] ?? []) !== [],
        'teacher_notes' => (array)($m['teacher_notes'] ?? []) !== [],
        'substitution' => (array)($m['substitution'] ?? []) !== [],
    ];
    $labels = lm71_completeness_fields();
    $missing = [];
    foreach ($checks as $key => $ok) if (!$ok) $missing[] = $labels[$key];
    $score = count(array_filter($checks));
    return ['score' => $score, 'total' => count($checks), 'complete' => $score === count($checks), 'checks' => $checks, 'missing' => $missing];
}

/**
 * Normalizovaný model lekce 1–28 třídy (overlay má přednost u polí pro učitele; žákovská cesta zůstává ze zdrojů).
 * @return array<string,mixed>
 */
function lm71_lesson(string $classId, int $number, ?array $module = null): array
{
    $number = max(1, min(LM71_LESSONS, $number));
    $module ??= is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
    $rawLayer = lm71_raw($classId);
    $raw = is_array($rawLayer['lessons'][$number] ?? null) ? $rawLayer['lessons'][$number] : ['lm71_source' => ''];
    $ov = (array)($rawLayer['overlay']['lessons'][$number] ?? []);
    $bundle = lm71_bundle($classId, $module, $number);
    $topics = [];
    foreach ((array)$bundle['topics'] as $key => $topic) $topics[] = ['key' => (string)$key, 'title' => (string)($topic['title'] ?? $key), 'summary' => (string)($topic['summary'] ?? '')];
    $steps = [];
    foreach ((array)$bundle['steps'] as $step) $steps[] = ['title' => (string)($step['title'] ?? ''), 'time' => (string)($step['time'] ?? ''), 'tasks' => array_values(array_map('strval', (array)($step['tasks'] ?? [])))];
    $tasks = [];
    $source = (string)$raw['lm71_source'];
    foreach ($source === 'primary' ? $steps : (array)($raw['steps'] ?? []) as $step) {
        if (!is_array($step)) continue;
        foreach (lm71_text_list($step['tasks'] ?? []) as $text) {
            $tasks[] = ['text' => $text, 'step' => trim((string)preg_replace('/^\d+\s*·\s*/u', '', (string)($step['title'] ?? ''))), 'time' => (string)($step['time'] ?? ''),
                'template' => isset($rawLayer['template_tasks'][substr(sha1(lm71_norm_text($text)), 0, 12)])];
        }
    }
    $finisher = is_array($raw['finisher'] ?? null) ? trim((string)($raw['finisher']['title'] ?? '') . ': ' . (string)($raw['finisher']['text'] ?? ''), ': ') : '';
    $model = [
        'id' => $classId . '_l' . str_pad((string)$number, 2, '0', STR_PAD_LEFT), 'class_id' => $classId, 'number' => $number, 'day_kind' => null,
        'title' => (string)$bundle['title'],
        'goal' => ['student' => (string)$bundle['goal'], 'success_criteria' => lm71_text_list($raw['success_criteria'] ?? [])],
        'curriculum' => ['svp' => is_array($raw['curriculum'] ?? null) ? ($raw['curriculum']['svp'] ?? null) : null],
        'competencies' => array_values((array)($raw['competencies'] ?? [])), 'prerequisites' => lm71_text_list($raw['prerequisites'] ?? []),
        'timeline' => lm71_timeline($raw, $source === 'primary' ? $steps : (array)($raw['steps'] ?? [])),
        'materials' => lm71_materials($classId, array_column($topics, 'key')),
        'tools' => array_values(array_filter(array_map(static fn($t): string => is_array($t) ? (string)($t['name'] ?? '') : '', (array)$bundle['tools']))),
        'differentiation' => array_filter(['support' => '', 'standard' => '', 'challenge' => $finisher], static fn(string $v): bool => $v !== ''),
        'tasks' => $tasks,
        'assessment' => ['checks' => lm71_quiz_items($raw), 'rubric' => array_values((array)($raw['rubric'] ?? [])), 'formative' => []],
        'exit_ticket' => lm71_exit_ticket($raw, null), 'homework' => lm71_text_list($raw['homework'] ?? []), 'safety' => lm71_text_list($raw['safety'] ?? []),
        'teacher_notes' => $source === 'primary' ? [] : lm71_text_list($raw['teacher_notes'] ?? []), 'substitution' => lm71_text_list($raw['substitution'] ?? []),
        'worksheet' => lm71_text_list($raw['worksheet'] ?? []), 'glossary' => [],
        'topics' => $topics, 'steps' => $steps, 'questions' => count((array)$bundle['questions']), 'family' => (string)($bundle['family'] ?? ''),
        'meta' => ['source' => $source, 'file' => (string)(lm71_sources()[$source]['file'] ?? ''), 'template' => !empty(lm71_sources()[$source]['template']),
            'version' => LM71_MODEL_VERSION, 'status' => 'puvodni', 'reviewed_at' => null, 'overlay' => '', 'content_hash' => ''],
    ];
    if ($ov !== []) $model = lm71_apply_overlay($model, $ov, $raw);
    $model['meta']['content_hash'] = substr(hash('sha256', (string)json_encode([$model['title'], $model['goal'], $model['timeline'], $model['tasks'], $model['exit_ticket'], $model['teacher_notes']])), 0, 16);
    $model['completeness'] = lm71_completeness($model);
    return $model;
}

/** Overlay přepíše pole pro učitele (čistá funkce). Stav overlaye je „navrh“, dokud ho neschválí učitel (v72). */
function lm71_apply_overlay(array $model, array $ov, array $raw = []): array
{
    if (isset($ov['title']) && trim((string)$ov['title']) !== '') $model['title'] = trim((string)$ov['title']);
    if (isset($ov['goal'])) $model['goal']['student'] = is_array($ov['goal']) ? (string)($ov['goal']['student'] ?? $model['goal']['student']) : (string)$ov['goal'];
    if (is_array($ov['goal'] ?? null) && isset($ov['goal']['success_criteria'])) $model['goal']['success_criteria'] = lm71_text_list($ov['goal']['success_criteria']);
    if (isset($ov['success_criteria'])) $model['goal']['success_criteria'] = lm71_text_list($ov['success_criteria']);
    if (is_array($ov['timeline'] ?? null)) $model['timeline'] = lm71_timeline($raw, [], $ov['timeline']);
    if (is_array($ov['exit_ticket'] ?? null)) $model['exit_ticket'] = lm71_exit_ticket($raw, $ov['exit_ticket']);
    if (is_array($ov['differentiation'] ?? null)) $model['differentiation'] = array_filter(array_map(static fn($v): string => trim(is_array($v) ? implode(' ', lm71_text_list($v)) : (string)$v), array_intersect_key($ov['differentiation'], array_flip(['support', 'standard', 'challenge']))), static fn(string $v): bool => $v !== '');
    if (is_array($ov['assessment'] ?? null)) $model['assessment'] = ['checks' => (array)($ov['assessment']['checks'] ?? $model['assessment']['checks']), 'rubric' => array_values((array)($ov['assessment']['rubric'] ?? [])),
        'formative' => lm71_text_list($ov['assessment']['formative'] ?? [])];   // v72: formativní kontroly (text)
    if (isset($ov['glossary'])) $model['glossary'] = array_values(array_filter((array)$ov['glossary'], 'is_string'));
    if (is_array($ov['tasks'] ?? null)) {
        $model['tasks'] = [];
        foreach ($ov['tasks'] as $t) {
            $text = is_array($t) ? trim((string)($t['text'] ?? '')) : trim((string)$t);
            if ($text !== '') $model['tasks'][] = ['text' => $text, 'step' => is_array($t) ? (string)($t['output'] ?? '') : '', 'time' => is_array($t) ? (string)($t['time'] ?? '') : '', 'template' => false];
        }
    }
    foreach (['teacher_notes', 'safety', 'substitution', 'homework', 'worksheet', 'prerequisites'] as $key) if (isset($ov[$key])) $model[$key] = lm71_text_list($ov[$key]);
    if (isset($ov['competencies'])) $model['competencies'] = array_values((array)$ov['competencies']);
    if (is_array($ov['curriculum'] ?? null)) $model['curriculum'] = ['svp' => $ov['curriculum']['svp'] ?? null];
    $model['meta']['status'] = in_array((string)($ov['status'] ?? ''), ['navrh', 'schvaleno'], true) ? (string)$ov['status'] : 'navrh';
    $model['meta']['reviewed_at'] = isset($ov['reviewed_at']) ? (string)$ov['reviewed_at'] : null;
    $model['meta']['overlay'] = (string)($ov['_file'] ?? '');
    return $model;
}

/** Všech 28 lekcí třídy. @return array<int,array<string,mixed>> */
function lm71_lessons(string $classId, ?array $module = null): array
{
    $out = [];
    for ($n = 1; $n <= LM71_LESSONS; $n++) $out[$n] = lm71_lesson($classId, $n, $module);
    return $out;
}

/** Karta dne bez lekce (intro, projekt, mastery, obhajoby, retrospektiva, portfolio, uzavření) – viz calendar_days_v71.php. */
function lm71_day(string $classId, array $calendarRow): array
{
    if (!function_exists('cd71_day_card')) require_once __DIR__ . '/calendar_days_v71.php';
    $card = cd71_day_card($classId, $calendarRow);
    $ov = (array)(lm71_raw($classId)['overlay']['days'][(int)$card['slot']] ?? []);
    if ($ov !== []) $card = cd71_apply_overlay($card, $ov);
    return $card;
}
