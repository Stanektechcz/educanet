<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · jádro úložiště (F2, DAT-01/03/07, ARC-05).
 *
 * Jediné API nad soubory storage/:
 *   storage_read($path, $strict)          – čtení pod LOCK_SH, cache v rámci requestu (a APCu) podle podpisu souboru
 *   storage_update($path, $mutate)        – read-modify-write pod jedním LOCK_EX (jediný povolený způsob RMW)
 *   storage_update_many($paths, $mutate)  – totéž pro více souborů; zámky v seřazeném pořadí, bez vnořování
 *   storage_map_update($path, $key, $fn)  – RMW jednoho klíče mapy (zkratka nad storage_update)
 *   storage_list_push($path, $row, $cap)  – přidání do seznamu s volitelným stropem (zkratka nad storage_update)
 *   storage_write($path, $data)           – jen cache/snapshoty/fixtures (celý obsah, žádné RMW)
 *   storage_append($stream, $record)      – měsíční JSONL storage/<stream>/<YYYY-MM>.jsonl.php (připojení pod LOCK_EX na <soubor>.lock)
 *   storage_scan($stream, ...)            – generátor záznamů proudu (včetně dosud nemigrovaného legacy souboru)
 *
 * Soubor = ochranný 1. řádek + JSON (mapy), resp. + 1 JSON záznam na řádek (proudy).
 * Zápis mapy (DAT58-04): zámek na vedlejším <soubor>.lock (LOCK_EX zápis, LOCK_SH čtení), zápis do <soubor>.<rand>.tmp
 * + fflush + fsync + rename na cíl – při chybě (plný disk, pád) zůstane původní soubor celý.
 * Poškozený JSON = výjimka (nikdy [] → následný zápis by data smazal).
 * Vnoření: uvnitř $mutate se nesmí zapisovat do souboru, který je právě zamčený (výjimka místo zamrznutí);
 * čtení zamčeného souboru uvnitř $mutate vrátí poslední potvrzený stav (čte se přímo, zámek už drží tento proces).
 */

if (!defined('STORAGE_GUARD_LINE')) define('STORAGE_GUARD_LINE', "<?php http_response_code(403); exit; ?>\n");
const STORAGE_STREAM_NAME_RE = '/^[a-z0-9][a-z0-9_]{0,63}$/';
const STORAGE_MONTH_RE = '/^\d{4}-(0[1-9]|1[0-2])$/';
const STORAGE_STREAM_MAX_RECORD_BYTES = 1048576;

// ---------------------------------------------------------------- kódování

/** Celý obsah úložného souboru (guard + JSON). Kóduje se PŘED zkrácením souboru. */
function storage_encode_payload(array $data): string
{
    return STORAGE_GUARD_LINE . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR) . "\n";
}

/** Přečte obsah úložného souboru. Poškozený (nečitelný) JSON se nepřepíše – raději výjimka. */
function storage_decode_raw(string $raw): array
{
    $clean = trim(preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw);
    if ($clean === '') return [];
    $decoded = json_decode($clean, true);
    if (!is_array($decoded)) throw new RuntimeException('Úložiště je poškozené, zápis byl zastaven.');
    return $decoded;
}

// ---------------------------------------------------------------- zámky (DAT58-04)

const STORAGE_LOCK_SUFFIX = '.lock';
const STORAGE_RENAME_ATTEMPTS = 60;
const STORAGE_RENAME_WAIT_US = 5000;

/** Zamčené soubory tohoto procesu (deskriptor vedlejšího zámku <soubor>.lock pod LOCK_EX) – ochrana proti vnoření. */
function storage_held_handle(string $path)
{
    return $GLOBALS['educanet_storage_held'][storage_path_key($path)] ?? null;
}

function storage_path_key(string $path): string
{
    return str_replace('\\', '/', $path);
}

/** Vedlejší zámkový soubor. Datový soubor se při zápisu nahrazuje (rename), proto se nezamyká on sám. */
function storage_lock_path(string $path): string
{
    return $path . STORAGE_LOCK_SUFFIX;
}

/** Otevře <soubor>.lock a získá LOCK_EX. Vnořený zámek téhož souboru ve stejném procesu = výjimka (jinak by zamrzl). */
function storage_lock_exclusive(string $path)
{
    if (storage_held_handle($path) !== null) {
        throw new RuntimeException('Vnořený zápis do stejného úložiště (' . basename($path) . ') není dovolen.');
    }
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) throw new RuntimeException('Nelze vytvořit adresář úložiště.');
    $fp = @fopen(storage_lock_path($path), 'c');
    if ($fp === false) throw new RuntimeException('Nelze otevřít zámek úložiště.');
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException('Nelze uzamknout úložiště.');
    }
    $GLOBALS['educanet_storage_held'][storage_path_key($path)] = $fp;
    return $fp;
}

function storage_unlock($fp, string $path): void
{
    unset($GLOBALS['educanet_storage_held'][storage_path_key($path)]);
    flock($fp, LOCK_UN);
    fclose($fp);
    php_json_cache_forget($path);
}

/** Obsah datového souboru (bez zamykání – volající drží zámek). null = soubor neexistuje / nelze číst. */
function storage_read_file_raw(string $path): ?string
{
    if (!is_file($path)) return null;
    $raw = @file_get_contents($path);
    return is_string($raw) ? $raw : null;
}

/** Aktuální data souboru, jehož zámek tento proces drží. */
function storage_read_current(string $path): array
{
    return storage_decode_raw(storage_read_file_raw($path) ?? '');
}

/** Test hook (jen CLI audit): simulace selhání zápisu v dané fázi ('write' | 'rename'). */
function storage_fault(string $stage): bool
{
    return PHP_SAPI === 'cli' && ($GLOBALS['educanet_storage_fault'] ?? null) === $stage;
}

/**
 * Atomické nahrazení obsahu (volající drží LOCK_EX na <soubor>.lock): zápis do <soubor>.<rand>.tmp → fflush → fsync
 * → rename na cíl. Při jakékoli chybě se tmp smaže a původní soubor zůstane nedotčený (žádné zkrácení při plném disku).
 * Windows: rename selže, drží-li cíl otevřený cizí čtenář bez zámku – proto krátké opakování.
 */
function storage_atomic_replace(string $path, string $payload): void
{
    $tmp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
    $fp = @fopen($tmp, 'xb');
    if ($fp === false) throw new RuntimeException('Nelze vytvořit dočasný soubor úložiště.');
    try {
        $written = storage_fault('write') ? false : fwrite($fp, $payload);
        if ($written === false || $written !== strlen($payload) || !fflush($fp)) throw new RuntimeException('Úložiště se nepodařilo celé zapsat.');
        if (function_exists('fsync') && !fsync($fp)) throw new RuntimeException('Úložiště se nepodařilo uložit na disk.');
        fclose($fp);
        $fp = null;
        for ($i = 0; ; $i++) {
            if (!storage_fault('rename') && @rename($tmp, $path)) break;
            if ($i >= STORAGE_RENAME_ATTEMPTS) throw new RuntimeException('Úložiště se nepodařilo nahradit.');
            usleep(STORAGE_RENAME_WAIT_US * min(10, $i + 1));
        }
        $tmp = null;
    } finally {
        if (is_resource($fp)) fclose($fp);
        if ($tmp !== null) @unlink($tmp);
        clearstatcache(true, $path);
    }
}

/**
 * Z1: přečte celý soubor pod sdíleným zámkem <soubor>.lock (datový soubor je otevřený jen po dobu čtení).
 * Drží-li tento proces zámek, čte se přímo. Zámkový soubor čtení nevytváří (čtení nemění storage/). null = nelze číst.
 */
function storage_read_locked_raw(string $path): ?string
{
    if (storage_held_handle($path) !== null) return storage_read_file_raw($path);
    $lk = @fopen(storage_lock_path($path), 'rb');
    if ($lk === false) return storage_read_file_raw($path);
    try {
        if (!storage_lock_shared($lk)) return storage_read_file_raw($path);
        try {
            return storage_read_file_raw($path);
        } finally {
            flock($lk, LOCK_UN);
        }
    } finally {
        fclose($lk);
    }
}

/**
 * LOCK_SH na zámkovém souboru. Drží-li proces jiný LOCK_EX (čtení uvnitř $mutate), čeká se jen omezeně (LOCK_NB)
 * – dva zapisovatelé čtoucí si navzájem soubory tak nikdy nezamrznou. false = čte se bez zámku (bezpečné: zápis je
 * vždy celý přes rename, zámek čtenáře jen chrání rename na Windows).
 */
function storage_lock_shared($lk): bool
{
    if (empty($GLOBALS['educanet_storage_held'])) return flock($lk, LOCK_SH);
    for ($i = 0; $i < STORAGE_RENAME_ATTEMPTS; $i++) {
        if (flock($lk, LOCK_SH | LOCK_NB)) return true;
        usleep(STORAGE_RENAME_WAIT_US * min(10, $i + 1));
    }
    return false;
}

// ---------------------------------------------------------------- mapy (JSON soubory)

/** Podpis souboru pro cache (mtime:ctime:size). Stat cache se čistí – jinak by dlouhý proces neviděl cizí zápisy. */
function storage_signature(string $path): ?string
{
    clearstatcache(true, $path);
    $stat = @stat($path);
    if (!is_array($stat)) return null;
    return (int)($stat['mtime'] ?? 0) . ':' . (int)($stat['ctime'] ?? 0) . ':' . (int)($stat['size'] ?? 0);
}

/**
 * Čtení JSON úložiště pod LOCK_SH s cache podle podpisu souboru.
 * $strict = true: poškozený soubor → výjimka (výchozí, bezpečné pro následný zápis).
 * $strict = false: poškozený soubor → [] + záznam do error_logu (jen pro čistě zobrazovací místa).
 */
function storage_read(string $path, bool $strict = true): array
{
    $signature = storage_signature($path);
    if ($signature === null) return [];
    $cache = &$GLOBALS['educanet_json_request_cache'];
    if (!is_array($cache)) $cache = [];
    $inLock = storage_held_handle($path) !== null;
    $cached = $cache[$path] ?? null;
    if (!$inLock && is_array($cached) && (string)($cached['signature'] ?? '') === $signature && is_array($cached['data'] ?? null)) {
        return $cached['data'];
    }
    $apcuKey = '';
    if (!$inLock && educanet_apcu_enabled()) {
        $apcuKey = 'educanet:json:data:' . hash('sha256', $path . '|' . $signature);
        $data = apcu_fetch($apcuKey, $ok);
        if ($ok && is_array($data)) {
            $cache[$path] = ['signature' => $signature, 'data' => $data];
            return $data;
        }
    }
    $raw = storage_read_locked_raw($path);
    if ($raw === null) return [];
    try {
        $data = storage_decode_raw($raw);
    } catch (RuntimeException $e) {
        error_log('EDUCANET: poškozené úložiště ' . basename($path));
        if (!$strict) return [];
        throw new RuntimeException('Úložiště ' . basename($path) . ' je poškozené, čtení bylo zastaveno.', 0, $e);
    }
    if ($inLock) return $data;
    $cache[$path] = ['signature' => $signature, 'data' => $data];
    if ($apcuKey !== '') {
        apcu_store($apcuKey, $data, 1800);
        apcu_store('educanet:json:pointer:' . hash('sha256', $path), $apcuKey, 1800);
    }
    return $data;
}

/**
 * Atomická úprava JSON úložiště: jeden LOCK_EX drží čtení → $mutate($data) → zápis.
 * $mutate dostane aktuální data a vrací nová data; výjimka z $mutate zápis zruší.
 * Vrátí-li $mutate beze změny stejná data, soubor se nepřepisuje.
 */
function storage_update(string $path, callable $mutate): array
{
    $fp = storage_lock_exclusive($path);
    try {
        $raw = storage_read_file_raw($path);
        $data = storage_decode_raw($raw ?? '');
        $new = $mutate($data);
        if (!is_array($new)) throw new RuntimeException('Úprava úložiště nevrátila platná data.');
        if ($new !== $data || $raw === null || $raw === '') storage_atomic_replace($path, storage_encode_payload($new));
    } finally {
        storage_unlock($fp, $path);
    }
    return $new;
}

/**
 * Atomická úprava více souborů najednou. Zámky se berou v seřazeném pořadí cest (žádný deadlock mezi procesy),
 * $mutate(array $dataByPath): array $newDataByPath – chybějící klíč = soubor beze změny.
 * Vše se zakóduje dřív, než se cokoli zapíše.
 */
function storage_update_many(array $paths, callable $mutate): array
{
    $paths = array_values(array_unique(array_map('strval', $paths)));
    usort($paths, static fn(string $a, string $b): int => strcmp(storage_path_key($a), storage_path_key($b)));
    foreach ($paths as $path) {
        if (storage_held_handle($path) !== null) throw new RuntimeException('Vnořený zápis do stejného úložiště (' . basename($path) . ') není dovolen.');
    }
    $handles = [];
    try {
        $data = [];
        foreach ($paths as $path) {
            $handles[$path] = storage_lock_exclusive($path);
            $data[$path] = storage_read_current($path);
        }
        $new = $mutate($data);
        if (!is_array($new)) throw new RuntimeException('Úprava úložiště nevrátila platná data.');
        $payloads = [];
        foreach ($new as $path => $rows) {
            if (!array_key_exists($path, $data)) throw new RuntimeException('Úprava úložiště vrátila neočekávaný soubor.');
            if (!is_array($rows)) throw new RuntimeException('Úprava úložiště nevrátila platná data.');
            if ($rows !== $data[$path]) $payloads[$path] = storage_encode_payload($rows);
        }
        foreach ($payloads as $path => $payload) storage_atomic_replace((string)$path, $payload);
        return array_replace($data, $new);
    } finally {
        foreach (array_reverse($handles, true) as $path => $fp) storage_unlock($fp, (string)$path);
    }
}

/**
 * RMW jednoho klíče mapy: $fn(?array $current): ?array – null smaže klíč. Vrací novou hodnotu (nebo null).
 */
function storage_map_update(string $path, string $key, callable $fn): ?array
{
    $result = null;
    storage_update($path, static function (array $all) use ($key, $fn, &$result): array {
        $current = is_array($all[$key] ?? null) ? $all[$key] : null;
        $result = $fn($current);
        if ($result === null) {
            unset($all[$key]);
        } else {
            if (!is_array($result)) throw new RuntimeException('Úprava záznamu nevrátila platná data.');
            $all[$key] = $result;
        }
        return $all;
    });
    return $result;
}

/** Přidá řádek do seznamu (JSON pole) pod zámkem; $cap > 0 ponechá jen posledních $cap řádků. */
function storage_list_push(string $path, array $row, int $cap = 0): void
{
    storage_update($path, static function (array $rows) use ($row, $cap): array {
        $rows[] = $row;
        if ($cap > 0 && count($rows) > $cap) $rows = array_slice($rows, -$cap);
        return $rows;
    });
}

/** Zápis celého obsahu – jen pro cache, snapshoty a testovací fixtures (NE pro read-modify-write). */
function storage_write(string $path, array $data): void
{
    $payload = storage_encode_payload($data);
    $fp = storage_lock_exclusive($path);
    try {
        storage_atomic_replace($path, $payload);
    } finally {
        storage_unlock($fp, $path);
    }
}

/**
 * Tříbodové sloučení: změny, které volající udělal proti své výchozí kopii ($base → $new), se přenesou do čerstvých
 * dat z úložiště ($fresh) – do hloubky 2 (klíč → podklíč). Klíče v $counters se sčítají rozdílem (např. XP).
 * Klíče v $ignore se nepřenášejí. Nesouvisející změny jiných zapisovatelů ve $fresh zůstanou zachovány.
 */
function storage_merge_changes(array $fresh, array $base, array $new, array $counters = [], array $ignore = []): array
{
    foreach ($new as $k => $v) {
        if (in_array($k, $ignore, true)) continue;
        $had = array_key_exists($k, $base);
        $b = $had ? $base[$k] : null;
        if ($had && $b === $v) continue;
        if (in_array($k, $counters, true) && is_numeric($v)) {
            $fresh[$k] = max(0, (int)($fresh[$k] ?? 0) + (int)$v - (int)($b ?? 0));
            continue;
        }
        if (is_array($v) && is_array($b) && !storage_is_nonempty_list($v) && !storage_is_nonempty_list($b)) {
            $f = is_array($fresh[$k] ?? null) ? $fresh[$k] : [];
            foreach ($v as $sk => $sv) {
                if (!array_key_exists($sk, $b) || $b[$sk] !== $sv) $f[$sk] = $sv;
            }
            foreach ($b as $sk => $_) {
                if (!array_key_exists($sk, $v)) unset($f[$sk]);
            }
            $fresh[$k] = $f;
            continue;
        }
        $fresh[$k] = $v;
    }
    foreach ($base as $k => $_) {
        if (!array_key_exists($k, $new) && !in_array($k, $ignore, true)) unset($fresh[$k]);
    }
    return $fresh;
}

/** true, když se $new od $base liší jen v ignorovaných klíčích. */
function storage_changes_empty(array $base, array $new, array $ignore = []): bool
{
    foreach ($ignore as $k) unset($base[$k], $new[$k]);
    return $base === $new;
}

function storage_is_nonempty_list(array $value): bool
{
    return $value !== [] && array_is_list($value);
}

// ---------------------------------------------------------------- proudy (měsíční JSONL)

/**
 * Registrovaná „pouze přidávaná“ data. Klíč = název proudu (adresář storage/<proud>/), 'legacy' = původní JSON
 * soubor (čte se do doby, než ho migrace převede), 'time' = pole s časem záznamu (pro zařazení do měsíce).
 */
function storage_streams(): array
{
    return [
        'practice_results' => ['legacy' => 'practice_results.json.php', 'time' => ['finished_at', 'submitted_at', 'created_at'], 'label' => 'Výsledky testů'],
        'lab_results' => ['legacy' => 'lab_results.json.php', 'time' => ['finished_at', 'created_at'], 'label' => 'Výsledky laboratoří'],
        'extra_submissions' => ['legacy' => 'extra_submissions.json.php', 'time' => ['submitted_at', 'created_at'], 'label' => 'Odevzdané extra úkoly'],
        'graphics_submissions' => ['legacy' => 'graphics_submissions.json.php', 'time' => ['submitted_at', 'created_at'], 'label' => 'Odevzdané grafické práce'],
        'special_exam_results' => ['legacy' => 'special_exam_results.json.php', 'time' => ['attempted_at', 'created_at'], 'label' => 'Prestižní zkoušky'],
        'skill_attempts' => ['legacy' => 'skill_attempts.json.php', 'time' => ['created_at'], 'label' => 'Pokusy mastery challenge'],
        'skill_audit' => ['legacy' => 'skill_audit.json.php', 'time' => ['at'], 'label' => 'Audit stromu dovedností'],
        'adaptive_help_events' => ['legacy' => 'adaptive_help_events.json.php', 'time' => ['at'], 'label' => 'Události nápovědy'],
        'adaptive_retrieval_attempts' => ['legacy' => 'adaptive_retrieval_attempts.json.php', 'time' => ['at'], 'label' => 'Pokusy opakování'],
        'adaptive_v50_hands_on_events' => ['legacy' => 'adaptive_v50_hands_on_events.json.php', 'time' => ['created_at', 'at'], 'label' => 'Události v50 (praktické laby)'],
        'adaptive_ml_events' => ['legacy' => 'adaptive_ml_events.json.php', 'time' => ['created_at'], 'label' => 'Učební události mastery (v41)'],
        'adaptive_v505_events' => ['legacy' => 'adaptive_v505_events.json.php', 'time' => ['created_at', 'at'], 'label' => 'Události v50.5 (jeden úkol)'],
    ];
}

function storage_stream_dir(string $stream): string
{
    if (preg_match(STORAGE_STREAM_NAME_RE, $stream) !== 1) throw new InvalidArgumentException('Neplatný název proudu úložiště.');
    return STORAGE_DIR . '/' . $stream;
}

function storage_stream_file(string $stream, string $month): string
{
    if (preg_match(STORAGE_MONTH_RE, $month) !== 1) throw new InvalidArgumentException('Neplatný měsíc proudu úložiště.');
    return storage_stream_dir($stream) . '/' . $month . '.jsonl.php';
}

/** Původní JSON soubor proudu (před migrací), nebo null. */
function storage_stream_legacy_path(string $stream): ?string
{
    $def = storage_streams()[$stream] ?? null;
    return is_array($def) && !empty($def['legacy']) ? STORAGE_DIR . '/' . $def['legacy'] : null;
}

/** Proud, kterému odpovídá cesta k legacy souboru (např. storage/practice_results.json.php), nebo null. */
function storage_stream_for_path(string $path): ?string
{
    $norm = storage_path_key($path);
    foreach (storage_streams() as $stream => $def) {
        if (!empty($def['legacy']) && $norm === storage_path_key(STORAGE_DIR . '/' . $def['legacy'])) return $stream;
    }
    return null;
}

function storage_encode_line(array $record): string
{
    $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    if (strlen($line) > STORAGE_STREAM_MAX_RECORD_BYTES) throw new RuntimeException('Záznam je příliš velký pro uložení.');
    return $line . "\n";
}

/** Připojí záznam do měsíčního JSONL souboru proudu (jeden řádek = jeden JSON, připojení pod LOCK_EX na <soubor>.lock). */
function storage_append(string $stream, array $record): void
{
    storage_append_many($stream, [$record], date('Y-m'));
}

/**
 * Připojí více záznamů do daného měsíce jedním zápisem (migrace, dávky). Vrací počet připojených záznamů.
 * Ochranný řádek zapíše ten, kdo pod zámkem (<soubor>.lock) najde prázdný soubor – nikdy nevznikne soubor bez guardu.
 * Neúplný zápis (plný disk) se zkrátí zpět na původní délku.
 */
function storage_append_many(string $stream, array $records, ?string $month = null): int
{
    if ($records === []) return 0;
    $file = storage_stream_file($stream, $month ?? date('Y-m'));
    $payload = '';
    foreach ($records as $record) {
        if (!is_array($record)) throw new InvalidArgumentException('Záznam proudu musí být pole.');
        $payload .= storage_encode_line($record);
    }
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) throw new RuntimeException('Nelze vytvořit adresář proudu.');
    $lk = storage_lock_exclusive($file);
    try {
        $fp = @fopen($file, 'ab');
        if ($fp === false) throw new RuntimeException('Nelze otevřít proud úložiště.');
        try {
            $size = (int)(fstat($fp)['size'] ?? 0);
            if ($size === 0) $payload = STORAGE_GUARD_LINE . $payload;
            $written = storage_fault('write') ? false : fwrite($fp, $payload);
            if ($written === false || $written !== strlen($payload) || !fflush($fp)) {
                ftruncate($fp, $size); // neúplný řádek pryč – předchozí záznamy zůstanou beze změny
                throw new RuntimeException('Záznam se nepodařilo celý zapsat.');
            }
            if (function_exists('fsync')) fsync($fp);
        } finally {
            fclose($fp);
        }
    } finally {
        storage_unlock($lk, $file);
    }
    unset($GLOBALS['educanet_stream_cache'][storage_path_key($file)]);
    return count($records);
}

/** Měsíce proudu (YYYY-MM) vzestupně. */
function storage_stream_months(string $stream): array
{
    $dir = storage_stream_dir($stream);
    if (!is_dir($dir)) return [];
    $months = [];
    foreach (scandir($dir) ?: [] as $name) {
        if (preg_match('/^(\d{4}-\d{2})\.jsonl\.php$/', $name, $m) === 1 && preg_match(STORAGE_MONTH_RE, $m[1]) === 1) $months[] = $m[1];
    }
    sort($months, SORT_STRING);
    return $months;
}

/** Rozparsuje obsah JSONL souboru – bere jen celé řádky (neukončený poslední řádek se ignoruje). */
function storage_parse_lines(string $raw, string $label): array
{
    $rows = [];
    $lines = explode("\n", $raw);
    array_pop($lines); // za posledním "\n" je buď prázdno, nebo neukončený (rozepsaný) řádek
    foreach ($lines as $i => $line) {
        if ($i === 0 && str_starts_with($line, '<?php')) continue;
        $line = rtrim($line, "\r");
        if ($line === '') continue;
        $row = json_decode($line, true);
        if (!is_array($row)) {
            error_log('EDUCANET: poškozený řádek v proudu ' . $label);
            continue;
        }
        $rows[] = $row;
    }
    return $rows;
}

/** Záznamy jednoho měsíce (LOCK_SH, cache podle podpisu souboru). */
function storage_stream_month_rows(string $stream, string $month): array
{
    $file = storage_stream_file($stream, $month);
    $signature = storage_signature($file);
    if ($signature === null) return [];
    $key = storage_path_key($file);
    $cached = $GLOBALS['educanet_stream_cache'][$key] ?? null;
    if (is_array($cached) && ($cached['signature'] ?? '') === $signature) return $cached['rows'];
    $raw = storage_read_locked_raw($file);
    $rows = $raw === null ? [] : storage_parse_lines($raw, $stream . '/' . $month);
    $GLOBALS['educanet_stream_cache'][$key] = ['signature' => $signature, 'rows' => $rows];
    return $rows;
}

/** Měsíc záznamu podle časových polí proudu (nebo null). */
function storage_record_month(array $record, array $fields): ?string
{
    foreach ($fields as $field) {
        $value = $record[$field] ?? null;
        if ($value === null || $value === '') continue;
        $ts = is_numeric($value) ? (int)$value : strtotime((string)$value);
        if ($ts !== false && $ts > 0) return date('Y-m', $ts);
    }
    return null;
}

/**
 * Generátor záznamů proudu v časovém pořadí (nejdřív nemigrovaný legacy soubor, pak měsíce vzestupně).
 * $fromMonth/$toMonth = 'YYYY-MM' včetně (null = bez omezení), $filter(array $record): bool, $limit 0 = bez limitu.
 */
function storage_scan(string $stream, ?string $fromMonth = null, ?string $toMonth = null, ?callable $filter = null, int $limit = 0): Generator
{
    foreach ([$fromMonth, $toMonth] as $m) {
        if ($m !== null && preg_match(STORAGE_MONTH_RE, $m) !== 1) throw new InvalidArgumentException('Neplatný měsíc proudu úložiště.');
    }
    $count = 0;
    $inRange = static fn(?string $month): bool => $month === null
        ? ($fromMonth === null && $toMonth === null)
        : (($fromMonth === null || strcmp($month, $fromMonth) >= 0) && ($toMonth === null || strcmp($month, $toMonth) <= 0));
    $legacy = storage_stream_legacy_path($stream);
    if ($legacy !== null && is_file($legacy)) {
        $fields = (array)(storage_streams()[$stream]['time'] ?? []);
        foreach (storage_read($legacy) as $record) {
            if (!is_array($record) || !$inRange(storage_record_month($record, $fields))) continue;
            if ($filter !== null && !$filter($record)) continue;
            yield $record;
            if ($limit > 0 && ++$count >= $limit) return;
        }
    }
    foreach (storage_stream_months($stream) as $month) {
        if (!$inRange($month)) continue;
        foreach (storage_stream_month_rows($stream, $month) as $record) {
            if ($filter !== null && !$filter($record)) continue;
            yield $record;
            if ($limit > 0 && ++$count >= $limit) return;
        }
    }
}

/** Všechny (filtrované) záznamy proudu jako pole. */
function storage_stream_rows(string $stream, ?callable $filter = null, int $limit = 0): array
{
    return iterator_to_array(storage_scan($stream, null, null, $filter, $limit), false);
}

/** Kompatibilní čtení podle cesty: registrovaný proud → storage_stream_rows, jinak storage_read. */
function storage_rows(string $path): array
{
    $stream = storage_stream_for_path($path);
    return $stream !== null ? storage_stream_rows($stream) : storage_read($path);
}

// ---------------------------------------------------------------- schéma (DAT-07)

function storage_schema_path(): string
{
    return STORAGE_DIR . '/_schema_v58.json.php';
}

/** Manifest verzí schémat {soubor|proud: verze}. Chybějící položka = verze 1 (stav před v58). */
function storage_schema_versions(): array
{
    return storage_read(storage_schema_path());
}

function storage_schema_version(string $name): int
{
    return max(1, (int)(storage_schema_versions()[$name] ?? 1));
}

/** Nastaví verze schémat (jen zvyšuje – starší migrace nikdy nesníží verzi). */
function storage_schema_bump(array $versions): array
{
    return storage_update(storage_schema_path(), static function (array $all) use ($versions): array {
        foreach ($versions as $name => $version) {
            $all[(string)$name] = max((int)($all[(string)$name] ?? 1), (int)$version);
        }
        ksort($all, SORT_STRING);
        return $all;
    });
}
