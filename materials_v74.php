<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · knihovna materiálů s verzemi (fáze D, docs/PROMPT_V71_V75.md).
 *
 * Materiál = odkaz https (video, článek, kurz, reference, nástroj, pracovní list) vázaný na třídu, s metadaty (jazyk, délka,
 * úroveň, publikum, licence, témata, kompetence), stavem (aktivní / neodpovídá tématu / vyřazený), datem ověření a připojením
 * k lekcím (role a povinnost). Každá změna = nová verze; historie je append-only (starší verze se jen odřezávají stropem
 * MAT74_HISTORY_MAX a retencí MAT74_HISTORY_DAYS, nejnovějších MAT74_HISTORY_KEEP_MIN zůstává vždy). Obnovení starší verze
 * zapíše novou verzi se stejným obsahem – nic se nepřepisuje. U každé verze kdo (jméno učitele, max 60 znaků) a kdy.
 *
 * Úložiště storage/materials_v74.json.php = {"v":1,"items":{id:aktuální verze}}; od v75 je historie ve vlastním souboru
 * storage/materials_v74_history.json.php = {"v":1,"history":{id:[verze…]}} (Dnešní hodina čte jen aktuální verze). Starší klíč
 * "history" v hlavním souboru se dál čte a přesune ho migrace 0005 nebo první zápis. Zápis jen přes storage_update_many. Neobsahuje data žáků. Nahrávání souborů není součástí MVP (rozhodnutí školy č. 7 je otevřené): stejně jako
 * video k lekci v42 se ukládá jen odkaz, ale v74 přijme výhradně https (http:, javascript:, data: a odkazy s přihlašovacími
 * údaji se odmítnou) a vykresluje se jen jako odkaz s rel="noopener noreferrer" (žádné vložené přehrávače).
 *
 * POST akce (modul `materialy74`): mat74_add, mat74_update, mat74_verify, mat74_retire, mat74_attach, mat74_restore –
 * CSRF a politika teacher59 (třída povinná a v rozsahu) v teacher.php, oprávnění content.manage (asistent jen čte);
 * handler navíc ověří, že materiál patří do POSTnuté třídy. Neznámá mat74_* akce je zakázaná (deny-by-default).
 * Import: migrace migrations/0004_materials_v74.php (videa učitelů v42 + zdroje learning_resources.php, dry-run, rollback).
 */

const MAT74_CLASSES = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
const MAT74_TYPES = ['video' => 'Video', 'article' => 'Článek', 'course' => 'Kurz', 'reference' => 'Reference', 'tool' => 'Nástroj', 'worksheet' => 'Pracovní list'];
const MAT74_STATES = ['aktivni' => 'aktivní', 'neodpovida' => 'neodpovídá tématu', 'vyrazeny' => 'vyřazený'];
const MAT74_ROLES = ['hlavni' => 'hlavní materiál', 'doplnek' => 'doplněk', 'ucitel' => 'jen pro učitele'];
const MAT74_LANGS = ['CZ' => 'čeština', 'SK' => 'slovenština', 'EN' => 'angličtina', 'jiny' => 'jiný jazyk'];
const MAT74_AUDIENCES = ['zak' => 'žáci', 'ucitel' => 'učitel'];
const MAT74_TITLE_MAX = 180;
const MAT74_URL_MAX = 1500;
const MAT74_NOTE_MAX = 600;
const MAT74_SHORT_MAX = 60;
const MAT74_LICENSE_MAX = 120;
const MAT74_REASON_MAX = 300;
const MAT74_TOPICS_MAX = 20;
const MAT74_COMPETENCIES_MAX = 10;
const MAT74_LESSONS = 28;
const MAT74_ITEMS_MAX = 2000;
const MAT74_HISTORY_MAX = 30;
const MAT74_HISTORY_DAYS = 730;
const MAT74_HISTORY_KEEP_MIN = 3;
/** Ověření platí 180 dní (metrika fáze D: ověřené materiály za 180 dní ≥ 90 %). */
const MAT74_VERIFY_DAYS = 180;
const MAT74_ACTIONS = ['mat74_add', 'mat74_update', 'mat74_verify', 'mat74_retire', 'mat74_attach', 'mat74_restore'];
/** Pole verze (obsah materiálu); id, třída a původ se mezi verzemi nemění. */
const MAT74_FIELDS = ['type', 'title', 'url', 'lang', 'duration', 'level', 'audience', 'license', 'topics', 'competencies', 'lessons', 'state', 'checked_at', 'checked_by', 'retired_reason', 'note'];
const MAT74_FIELD_LABELS = ['type' => 'Typ', 'title' => 'Název', 'url' => 'Odkaz', 'lang' => 'Jazyk', 'duration' => 'Délka', 'level' => 'Úroveň', 'audience' => 'Publikum',
    'license' => 'Licence', 'topics' => 'Témata', 'competencies' => 'Kompetence', 'lessons' => 'Lekce', 'state' => 'Stav', 'checked_at' => 'Ověřeno', 'checked_by' => 'Ověřil/a',
    'retired_reason' => 'Důvod vyřazení', 'note' => 'Poznámka'];

function mat74_path(): string
{
    return STORAGE_DIR . '/materials_v74.json.php';
}

/** v75 · historie verzí ve vlastním souboru (Dnešní hodina ji nečte – hlavní soubor je menší a rychlejší). */
function mat74_history_path(): string
{
    return STORAGE_DIR . '/materials_v74_history.json.php';
}

function mat74_class_ok(string $classId): bool
{
    return in_array($classId, MAT74_CLASSES, true);
}

function mat74_data(): array
{
    $d = storage_read(mat74_path(), false);
    return is_array($d) ? $d : [];
}

/** Aktuální verze materiálů (volitelně jen jedné třídy). @return array<string,array> */
function mat74_items(?string $classId = null, ?array $data = null): array
{
    $items = array_filter((array)(($data ?? mat74_data())['items'] ?? []), 'is_array');
    if ($classId === null) return $items;
    return array_filter($items, static fn(array $i): bool => (string)($i['class_id'] ?? '') === $classId);
}

function mat74_item(string $id, ?array $data = null): ?array
{
    $item = (($data ?? mat74_data())['items'] ?? [])[$id] ?? null;
    return is_array($item) ? $item : null;
}

/** Historie verzí materiálu, od nejstarší. @return list<array> */
function mat74_history(string $id, ?array $data = null): array
{
    return array_values(array_filter((array)(mat74_history_all($data)[$id] ?? []), 'is_array'));
}

/** v75 · celá historie: soubor historie má přednost; starší klíč „history“ v hlavním souboru (před migrací 0005) se jen dočte. */
function mat74_history_all(?array $data = null, ?array $historyFile = null): array
{
    $legacy = array_filter((array)(($data ?? mat74_data())['history'] ?? []), 'is_array');
    $separate = array_filter((array)(($historyFile ?? storage_read(mat74_history_path(), false))['history'] ?? []), 'is_array');
    return array_replace($legacy, $separate);
}

/** Text bez řídicích znaků a zdvojených mezer, oříznutý na délku. */
function mat74_text(mixed $value, int $max): string
{
    $raw = is_scalar($value) ? (string)$value : '';
    if (!mb_check_encoding($raw, 'UTF-8')) $raw = (string)mb_convert_encoding($raw, 'UTF-8', 'UTF-8');   // neplatné bajty se zahodí, text zůstane
    $text = trim((string)preg_replace('/\s+/u', ' ', (string)preg_replace('/[\x00-\x1F\x7F]/u', ' ', $raw)));
    return mb_substr($text, 0, $max);
}

/**
 * Odkaz materiálu: jen https s hostitelem, bez mezer, řídicích znaků a přihlašovacích údajů, nejvýš MAT74_URL_MAX znaků.
 * Vyhazuje RuntimeException s českou hláškou (http:, javascript:, data: … se odmítnou).
 */
function mat74_clean_url(mixed $value): string
{
    $url = is_string($value) ? trim($value) : '';
    if ($url === '') throw new RuntimeException('Doplňte odkaz na materiál (začíná https://).');
    if (strlen($url) > MAT74_URL_MAX) throw new RuntimeException('Odkaz je delší než ' . MAT74_URL_MAX . ' znaků.');
    if (preg_match('/[\x00-\x20\x7F]/', $url) === 1) throw new RuntimeException('Odkaz nesmí obsahovat mezery ani řídicí znaky.');
    $parts = parse_url($url);
    if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https' || preg_match('/^[a-z0-9.-]+\.[a-z0-9-]{2,}$/i', (string)($parts['host'] ?? '')) !== 1) {
        throw new RuntimeException('Knihovna přijímá jen odkazy https:// na veřejnou stránku (http:, javascript: ani data: nejdou).');
    }
    if (isset($parts['user']) || isset($parts['pass'])) throw new RuntimeException('Odkaz nesmí obsahovat přihlašovací údaje.');
    if (safe_url($url) !== $url) throw new RuntimeException('Odkaz se nepodařilo ověřit.');
    return $url;
}

/** Seznam klíčů (témata, kompetence) z textu „a, b c“ nebo pole; jen [a-z0-9_.-], bez duplicit. @return list<string> */
function mat74_keys(mixed $value, int $max): array
{
    $raw = is_array($value) ? $value : preg_split('/[\s,;]+/u', is_string($value) ? $value : '');
    $out = [];
    foreach ((array)$raw as $v) {
        $k = strtolower(trim(is_scalar($v) ? (string)$v : ''));
        if ($k !== '' && preg_match('/^[a-z0-9][a-z0-9_.-]{0,59}$/', $k) === 1) $out[$k] = true;
    }
    return array_slice(array_keys($out), 0, $max);
}

/** Připojení k lekcím: {"<číslo>":{"role":…, "required":bool}} seřazené podle čísla. */
function mat74_lessons_norm(mixed $value): array
{
    $out = [];
    foreach ((array)$value as $n => $row) {
        $n = (int)$n;
        if ($n < 1 || $n > MAT74_LESSONS || !is_array($row)) continue;
        $role = (string)($row['role'] ?? 'hlavni');
        $out[(string)$n] = ['role' => isset(MAT74_ROLES[$role]) ? $role : 'doplnek', 'required' => !empty($row['required'])];
    }
    uksort($out, static fn($a, $b): int => (int)$a <=> (int)$b);
    return $out;
}

/** Prázdná pole verze (výchozí hodnoty). */
function mat74_blank(): array
{
    return ['type' => 'video', 'title' => '', 'url' => '', 'lang' => 'CZ', 'duration' => '', 'level' => '', 'audience' => 'zak', 'license' => '', 'topics' => [], 'competencies' => [],
        'lessons' => [], 'state' => 'aktivni', 'checked_at' => '', 'checked_by' => '', 'retired_reason' => '', 'note' => ''];
}

/** Jen pole verze z libovolného záznamu (v pořadí MAT74_FIELDS). */
function mat74_fields(array $row): array
{
    $out = [];
    foreach (MAT74_FIELDS as $f) $out[$f] = $row[$f] ?? mat74_blank()[$f];
    $out['lessons'] = mat74_lessons_norm($out['lessons']);
    return $out;
}

/**
 * Validace formuláře (přidání / úprava) nad základem $base. Popisná pole; stav, ověření a lekce mění jiné akce.
 * @return array<string,mixed> pole verze
 */
function mat74_validate(array $in, array $base): array
{
    $f = mat74_fields($base);
    $type = is_string($in['type'] ?? null) ? (string)$in['type'] : '';
    if (!isset(MAT74_TYPES[$type])) throw new RuntimeException('Vyberte typ materiálu.');
    $title = mat74_text($in['title'] ?? '', MAT74_TITLE_MAX + 1);
    if (mb_strlen($title) < 3) throw new RuntimeException('Napište název materiálu (aspoň 3 znaky).');
    if (mb_strlen($title) > MAT74_TITLE_MAX) throw new RuntimeException('Název může mít nejvýš ' . MAT74_TITLE_MAX . ' znaků.');
    $lang = is_string($in['lang'] ?? null) && isset(MAT74_LANGS[(string)$in['lang']]) ? (string)$in['lang'] : 'CZ';
    $audience = is_string($in['audience'] ?? null) && isset(MAT74_AUDIENCES[(string)$in['audience']]) ? (string)$in['audience'] : 'zak';
    $note = mat74_text($in['note'] ?? '', MAT74_NOTE_MAX + 1);
    if (mb_strlen($note) > MAT74_NOTE_MAX) throw new RuntimeException('Poznámka může mít nejvýš ' . MAT74_NOTE_MAX . ' znaků.');
    $url = mat74_clean_url($in['url'] ?? '');
    if ($url !== (string)$f['url'] && (string)$f['checked_at'] !== '') { $f['checked_at'] = ''; $f['checked_by'] = ''; }   // jiný odkaz = znovu ověřit
    return array_merge($f, [
        'type' => $type, 'title' => $title, 'url' => $url, 'lang' => $lang, 'audience' => $audience, 'note' => $note,
        'duration' => mat74_text($in['duration'] ?? '', MAT74_SHORT_MAX), 'level' => mat74_text($in['level'] ?? '', MAT74_SHORT_MAX),
        'license' => mat74_text($in['license'] ?? '', MAT74_LICENSE_MAX),
        'topics' => mat74_keys($in['topics'] ?? '', MAT74_TOPICS_MAX), 'competencies' => mat74_keys($in['competencies'] ?? '', MAT74_COMPETENCIES_MAX),
    ]);
}

/** Odřízne nejstarší verze nad strop a starší než retence (nejnovějších MAT74_HISTORY_KEEP_MIN zůstává). Existující verze nemění. */
function mat74_trim_history(array $list, int $now): array
{
    $list = array_values($list);
    if (count($list) > MAT74_HISTORY_MAX) $list = array_slice($list, -MAT74_HISTORY_MAX);
    $limit = $now - MAT74_HISTORY_DAYS * 86400;
    while (count($list) > MAT74_HISTORY_KEEP_MIN && (int)strtotime((string)($list[0]['at'] ?? '')) < $limit) array_shift($list);
    return $list;
}

/**
 * Jediný zápis knihovny: nová verze materiálu (nebo nový materiál pro $id = null). $mutate(array pole, ?array aktuální): array pole.
 * $expectedVersion > 0 = optimistická kontrola (formulář viděl tuto verzi). Vrací aktuální záznam.
 */
function mat74_write(string $classId, ?string $id, callable $mutate, string $change, string $by, int $expectedVersion = 0, ?int $now = null, string $origin = 'ucitel'): array
{
    if (!mat74_class_ok($classId)) throw new RuntimeException('Neplatná třída.');
    $now ??= time();
    $by = mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, MAT74_SHORT_MAX);
    $saved = [];
    storage_update_many([mat74_path(), mat74_history_path()], static function (array $all) use ($classId, $id, $mutate, $change, $by, $expectedVersion, $now, $origin, &$saved): array {
        $d = $all[mat74_path()];
        $items = array_filter((array)($d['items'] ?? []), 'is_array');
        $history = mat74_history_all($d, $all[mat74_history_path()]);
        $current = $id !== null ? ($items[$id] ?? null) : null;
        if ($id !== null && !is_array($current)) throw new RuntimeException('Materiál nebyl nalezen.');
        if (is_array($current) && (string)($current['class_id'] ?? '') !== $classId) throw new RuntimeException('Materiál patří jiné třídě.');
        if (is_array($current) && $expectedVersion > 0 && (int)($current['version'] ?? 0) !== $expectedVersion) {
            throw new RuntimeException('Materiál mezitím někdo upravil (verze ' . (int)$current['version'] . '). Načtěte stránku a změnu zopakujte.');
        }
        if ($id === null && count($items) >= MAT74_ITEMS_MAX) throw new RuntimeException('Knihovna je plná (' . MAT74_ITEMS_MAX . ' materiálů). Vyřaďte nepoužívané.');
        $fields = mat74_fields((array)$mutate(is_array($current) ? mat74_fields($current) : mat74_blank(), $current));
        if (is_array($current) && $fields === mat74_fields($current)) throw new RuntimeException('Nic se nezměnilo – nová verze se nevytvořila.');
        $newId = $id ?? 'm74_' . bin2hex(random_bytes(6));
        $version = is_array($current) ? (int)($current['version'] ?? 0) + 1 : 1;
        $snapshot = $fields + ['version' => $version, 'at' => date(DATE_ATOM, $now), 'by' => $by, 'change' => mb_substr($change, 0, 80)];
        $history[$newId] = mat74_trim_history(array_merge(array_values((array)($history[$newId] ?? [])), [$snapshot]), $now);
        $saved = $snapshot + ['id' => $newId, 'class_id' => $classId, 'origin' => is_array($current) ? (string)($current['origin'] ?? 'ucitel') : $origin,
            'created_at' => is_array($current) ? (string)($current['created_at'] ?? $snapshot['at']) : $snapshot['at'], 'created_by' => is_array($current) ? (string)($current['created_by'] ?? $by) : $by];
        $items[$newId] = $saved;
        return [mat74_path() => ['v' => 1, 'items' => $items], mat74_history_path() => ['v' => 1, 'history' => $history]];
    });
    return $saved;
}

function mat74_add(string $classId, array $in, string $by, ?int $now = null): array
{
    $fields = mat74_validate($in, mat74_blank());
    return mat74_write($classId, null, static fn(): array => $fields, 'vytvoření', $by, 0, $now);
}

function mat74_update(string $classId, string $id, array $in, int $expected, string $by, ?int $now = null): array
{
    return mat74_write($classId, $id, static fn(array $f): array => mat74_validate($in, $f), 'úprava popisu', $by, $expected, $now);
}

/** Ověření: ok = odkaz funguje a odpovídá tématu (stav aktivní), mismatch = neodpovídá tématu (v Dnešní hodině se neukáže). */
function mat74_verify(string $classId, string $id, string $result, string $by, ?int $now = null): array
{
    if (!in_array($result, ['ok', 'mismatch'], true)) throw new RuntimeException('Vyberte výsledek ověření.');
    $now ??= time();
    return mat74_write($classId, $id, static function (array $f) use ($result, $by, $now): array {
        if ($f['state'] === 'vyrazeny') throw new RuntimeException('Vyřazený materiál nejde ověřit. Nejdřív ho obnovte ze starší verze.');
        return array_merge($f, ['state' => $result === 'ok' ? 'aktivni' : 'neodpovida', 'checked_at' => date('Y-m-d', $now), 'checked_by' => mb_substr(trim($by) ?: 'učitel', 0, MAT74_SHORT_MAX)]);
    }, $result === 'ok' ? 'ověření' : 'neodpovídá tématu', $by, 0, $now);
}

function mat74_retire(string $classId, string $id, mixed $reason, string $by, ?int $now = null): array
{
    $reason = mat74_text($reason, MAT74_REASON_MAX + 1);
    if ($reason === '') throw new RuntimeException('Napište krátce, proč materiál vyřazujete.');
    if (mb_strlen($reason) > MAT74_REASON_MAX) throw new RuntimeException('Důvod vyřazení může mít nejvýš ' . MAT74_REASON_MAX . ' znaků.');
    return mat74_write($classId, $id, static function (array $f) use ($reason): array {
        if ($f['state'] === 'vyrazeny') throw new RuntimeException('Materiál už je vyřazený.');
        return array_merge($f, ['state' => 'vyrazeny', 'retired_reason' => $reason]);
    }, 'vyřazení', $by, 0, $now);
}

/** Připojení k lekci (role, povinný) nebo odpojení ($detach). */
function mat74_attach(string $classId, string $id, int $lesson, string $role, bool $required, bool $detach, string $by, ?int $now = null): array
{
    if ($lesson < 1 || $lesson > MAT74_LESSONS) throw new RuntimeException('Vyberte lekci 1–' . MAT74_LESSONS . '.');
    if (!$detach && !isset(MAT74_ROLES[$role])) throw new RuntimeException('Vyberte roli materiálu v lekci.');
    return mat74_write($classId, $id, static function (array $f) use ($lesson, $role, $required, $detach): array {
        $lessons = $f['lessons'];
        if ($detach) {
            if (!isset($lessons[(string)$lesson])) throw new RuntimeException('Materiál k lekci ' . $lesson . ' připojený není.');
            unset($lessons[(string)$lesson]);
        } else {
            $lessons[(string)$lesson] = ['role' => $role, 'required' => $required];
        }
        return array_merge($f, ['lessons' => $lessons]);
    }, $detach ? 'odpojení od lekce ' . $lesson : 'připojení k lekci ' . $lesson, $by, 0, $now);
}

/** Obnovení starší verze = nová verze se stejným obsahem (append-only). */
function mat74_restore(string $classId, string $id, int $version, string $by, ?int $now = null): array
{
    $old = null;
    foreach (mat74_history($id) as $row) if ((int)($row['version'] ?? 0) === $version) $old = $row;
    if ($old === null) throw new RuntimeException('Verze ' . $version . ' už v historii není (strop ' . MAT74_HISTORY_MAX . ' verzí, retence ' . MAT74_HISTORY_DAYS . ' dní).');
    return mat74_write($classId, $id, static fn(): array => mat74_fields($old), 'obnovení verze ' . $version, $by, 0, $now);
}

/** Ověřeno v posledních MAT74_VERIFY_DAYS dnech. */
function mat74_verified(array $item, ?int $now = null): bool
{
    $at = (string)($item['checked_at'] ?? '');
    if ($at === '' || (string)($item['state'] ?? '') !== 'aktivni') return false;
    return (int)strtotime($at) >= ($now ?? time()) - MAT74_VERIFY_DAYS * 86400;
}

/** Třída má v knihovně aspoň jeden nevyřazený materiál (jinak Dnešní hodina ukáže zdroje v70). */
function mat74_has_library(string $classId, ?array $data = null): bool
{
    foreach (mat74_items($classId, $data) as $i) if ((string)($i['state'] ?? '') !== 'vyrazeny') return true;
    return false;
}

/**
 * Materiály pro lekci (Dnešní hodina): připojené k lekci nebo k jejím tématům, nikdy vyřazené ani „neodpovídá tématu“.
 * Pořadí: povinné → ověřené → připojené k lekci → podle názvu.
 * @return list<array<string,mixed>>
 */
function mat74_for_lesson(string $classId, int $lesson, array $topicKeys, ?int $now = null, ?array $data = null): array
{
    $out = [];
    foreach (mat74_items($classId, $data) as $item) {
        if ((string)($item['state'] ?? '') !== 'aktivni') continue;
        $att = mat74_lessons_norm($item['lessons'] ?? [])[(string)$lesson] ?? null;
        $topics = array_values(array_intersect(array_map('strval', (array)($item['topics'] ?? [])), $topicKeys));
        if ($att === null && $topics === []) continue;
        $out[] = $item + ['attached' => $att !== null, 'required' => is_array($att) && $att['required'], 'role' => is_array($att) ? $att['role'] : 'doplnek',
            'verified' => mat74_verified($item, $now), 'matched_topics' => $topics];
    }
    usort($out, static fn(array $a, array $b): int => ((int)$b['required'] <=> (int)$a['required']) ?: ((int)$b['verified'] <=> (int)$a['verified'])
        ?: ((int)$b['attached'] <=> (int)$a['attached']) ?: strnatcasecmp((string)$a['title'], (string)$b['title']));
    return $out;
}

/** Souhrn třídy pro ukazatele knihovny. @return array{total:int,active:int,verified:int,unverified:int,mismatch:int,retired:int,required:int,pct:int} */
function mat74_stats(string $classId, ?int $now = null, ?array $data = null): array
{
    $s = ['total' => 0, 'active' => 0, 'verified' => 0, 'unverified' => 0, 'mismatch' => 0, 'retired' => 0, 'required' => 0, 'pct' => 0];
    foreach (mat74_items($classId, $data) as $i) {
        $s['total']++;
        $state = (string)($i['state'] ?? '');
        if ($state === 'vyrazeny') { $s['retired']++; continue; }
        if ($state === 'neodpovida') { $s['mismatch']++; continue; }
        $s['active']++;
        mat74_verified($i, $now) ? $s['verified']++ : $s['unverified']++;
        foreach (mat74_lessons_norm($i['lessons'] ?? []) as $row) if ($row['required']) { $s['required']++; break; }
    }
    $s['pct'] = $s['active'] > 0 ? (int)round($s['verified'] / $s['active'] * 100) : 0;
    return $s;
}

/** Hodnota pole jako čitelný text (náhled verze a rozdíl). */
function mat74_value_text(string $field, mixed $value): string
{
    if ($field === 'type') return MAT74_TYPES[(string)$value] ?? (string)$value;
    if ($field === 'lang') return MAT74_LANGS[(string)$value] ?? (string)$value;
    if ($field === 'audience') return MAT74_AUDIENCES[(string)$value] ?? (string)$value;
    if ($field === 'state') return MAT74_STATES[(string)$value] ?? (string)$value;
    if ($field === 'checked_at') return (string)$value === '' ? 'neověřeno' : date('j. n. Y', (int)strtotime((string)$value));
    if ($field === 'lessons') {
        $parts = [];
        foreach (mat74_lessons_norm($value) as $n => $row) $parts[] = 'L' . $n . ' (' . (MAT74_ROLES[$row['role']] ?? $row['role']) . ($row['required'] ? ', povinný' : '') . ')';
        return $parts === [] ? '—' : implode(', ', $parts);
    }
    if (is_array($value)) return $value === [] ? '—' : implode(', ', array_map('strval', $value));
    return trim((string)$value) === '' ? '—' : (string)$value;
}

/** Rozdíl dvou verzí: [pole, popisek, před, po]. @return list<array{0:string,1:string,2:string,3:string}> */
function mat74_diff(array $from, array $to): array
{
    $a = mat74_fields($from);
    $b = mat74_fields($to);
    $out = [];
    foreach (MAT74_FIELDS as $f) {
        if ($a[$f] === $b[$f]) continue;
        $out[] = [$f, MAT74_FIELD_LABELS[$f], mat74_value_text($f, $a[$f]), mat74_value_text($f, $b[$f])];
    }
    return $out;
}

/**
 * Kandidáti importu (čistá funkce pro migraci 0004): videa učitelů v42 (adaptive_lesson_resources) a zdroje learning_resources.php.
 * Id je deterministické (opakované spuštění nic nezdvojí). Jen https; ostatní se vrátí v 'skipped'.
 * @return array{rows:list<array{id:string,class_id:string,origin:string,fields:array}>,skipped:list<string>}
 */
function mat74_import_candidates(array $learningResources, array $v42Rows): array
{
    $rows = [];
    $skipped = [];
    $types = ['video' => 'video', 'course' => 'course', 'reference' => 'reference', 'article' => 'article', 'tool' => 'tool'];
    foreach ($v42Rows as $key => $r) {
        if (!is_array($r)) continue;
        $class = (string)($r['class_id'] ?? '');
        $n = (int)($r['lesson_number'] ?? 0);
        if (!mat74_class_ok($class) || $n < 1 || $n > MAT74_LESSONS) { $skipped[] = 'video v42 ' . (string)$key . ': neplatná třída nebo lekce'; continue; }
        try { $url = mat74_clean_url((string)($r['url'] ?? '')); } catch (RuntimeException $e) { $skipped[] = 'video v42 ' . $class . ' L' . $n . ': ' . $e->getMessage(); continue; }
        $id = 'm74_' . substr(hash('sha256', 'v42|' . $class . '|' . $n), 0, 12);
        $rows[$id] = ['id' => $id, 'class_id' => $class, 'origin' => 'import_v42', 'fields' => array_merge(mat74_blank(), [
            'type' => 'video', 'title' => mat74_text($r['title'] ?? 'Video k lekci', MAT74_TITLE_MAX) ?: 'Video k lekci', 'url' => $url, 'audience' => 'zak',
            'note' => mat74_text($r['note'] ?? '', MAT74_NOTE_MAX), 'lessons' => [(string)$n => ['role' => 'hlavni', 'required' => false]]])];
    }
    foreach ($learningResources as $class => $topics) {
        if (!mat74_class_ok((string)$class) || !is_array($topics)) continue;
        foreach ($topics as $topic => $list) {
            foreach ((array)$list as $r) {
                if (!is_array($r) || trim((string)($r['url'] ?? '')) === '') continue;
                try { $url = mat74_clean_url((string)$r['url']); } catch (RuntimeException $e) { $skipped[] = 'zdroj ' . $class . '/' . $topic . ': ' . $e->getMessage(); continue; }
                $id = 'm74_' . substr(hash('sha256', 'lr|' . $class . '|' . $url), 0, 12);
                $topicKey = (string)$topic === '_default' ? [] : mat74_keys([(string)$topic], 1);
                if (isset($rows[$id])) { $rows[$id]['fields']['topics'] = array_slice(array_values(array_unique(array_merge($rows[$id]['fields']['topics'], $topicKey))), 0, MAT74_TOPICS_MAX); continue; }
                $lang = strtoupper((string)($r['lang'] ?? ''));
                $rows[$id] = ['id' => $id, 'class_id' => (string)$class, 'origin' => 'import_lr', 'fields' => array_merge(mat74_blank(), [
                    'type' => $types[(string)($r['type'] ?? '')] ?? 'reference', 'title' => mat74_text($r['title'] ?? 'Materiál', MAT74_TITLE_MAX) ?: 'Materiál', 'url' => $url,
                    'lang' => isset(MAT74_LANGS[$lang]) ? $lang : ($lang === '' ? 'EN' : 'jiny'), 'duration' => mat74_text($r['duration'] ?? '', MAT74_SHORT_MAX),
                    'level' => mat74_text($r['level'] ?? '', MAT74_SHORT_MAX), 'topics' => $topicKey,
                    'note' => mat74_text((string)($r['description'] ?? ($r['meta'] ?? '')), MAT74_NOTE_MAX)])];
            }
        }
    }
    return ['rows' => array_values($rows), 'skipped' => $skipped];
}

/** Uloží kandidáty importu (jen chybějící id; existující materiál se nemění). @return array{added:int,existing:int} */
function mat74_import_apply(array $rows, string $by, ?int $now = null): array
{
    $now ??= time();
    $res = ['added' => 0, 'existing' => 0];
    storage_update_many([mat74_path(), mat74_history_path()], static function (array $all) use ($rows, $by, $now, &$res): array {
        $d = $all[mat74_path()];
        $items = array_filter((array)($d['items'] ?? []), 'is_array');
        $history = mat74_history_all($d, $all[mat74_history_path()]);
        foreach ($rows as $r) {
            if (isset($items[$r['id']])) { $res['existing']++; continue; }
            $at = date(DATE_ATOM, $now);
            $snap = mat74_fields($r['fields']) + ['version' => 1, 'at' => $at, 'by' => $by, 'change' => 'import (migrace 0004)'];
            $history[$r['id']] = [$snap];
            $items[$r['id']] = $snap + ['id' => $r['id'], 'class_id' => $r['class_id'], 'origin' => $r['origin'], 'created_at' => $at, 'created_by' => $by];
            $res['added']++;
        }
        return [mat74_path() => ['v' => 1, 'items' => $items], mat74_history_path() => ['v' => 1, 'history' => $history]];
    });
    return $res;
}

/** POST mat74_* (CSRF, politiku a oprávnění content.manage ověřil teacher.php). */
function mat74_teacher_handle_post(string $action): void
{
    if (!in_array($action, MAT74_ACTIONS, true)) throw new RuntimeException('Neznámá akce knihovny materiálů.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!mat74_class_ok($classId) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    $id = is_string($_POST['material_id'] ?? null) && preg_match('/^m74_[a-f0-9]{12}$/', (string)$_POST['material_id']) === 1 ? (string)$_POST['material_id'] : '';
    if ($action !== 'mat74_add' && $id === '') throw new RuntimeException('Materiál nebyl nalezen.');
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $int = static fn(string $k): int => is_string($_POST[$k] ?? null) && preg_match('/^\d{1,4}$/', (string)$_POST[$k]) === 1 ? (int)$_POST[$k] : 0;
    $item = match ($action) {
        'mat74_add' => mat74_add($classId, $_POST, $by),
        'mat74_update' => mat74_update($classId, $id, $_POST, $int('version'), $by),
        'mat74_verify' => mat74_verify($classId, $id, is_string($_POST['result'] ?? null) ? (string)$_POST['result'] : '', $by),
        'mat74_retire' => mat74_retire($classId, $id, $_POST['reason'] ?? '', $by),
        'mat74_attach' => mat74_attach($classId, $id, $int('lesson'), is_string($_POST['role'] ?? null) ? (string)$_POST['role'] : '', !empty($_POST['required']), !empty($_POST['detach']), $by),
        'mat74_restore' => mat74_restore($classId, $id, $int('restore_version'), $by),
    };
    $messages = ['mat74_add' => 'Materiál „%s“ je v knihovně (verze 1).', 'mat74_update' => 'Úprava uložena jako verze %2$d materiálu „%1$s“.',
        'mat74_verify' => 'Ověření zapsáno (verze %2$d): „%1$s“.', 'mat74_retire' => 'Materiál „%s“ je vyřazený – v Dnešní hodině se už neukáže. Vrátit ho jde obnovením starší verze.',
        'mat74_attach' => 'Připojení k lekci uloženo (verze %2$d): „%1$s“.', 'mat74_restore' => 'Obnoveno jako nová verze %2$d: „%1$s“.'];
    $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => sprintf($messages[$action], (string)$item['title'], (int)$item['version'])];
    $query = ['tab' => 'materialy74', 'class' => $classId, 'mat' => (string)$item['id']];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
