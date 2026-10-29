<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v66 · metadata otázek (sidecar nad bankami; zdrojové bance se nic nemění).
 *
 * Klíč položky "<zdroj>:<id>":
 *   mod:<id>  otázka modulu třídy ($module['questions'], startovní test a testy lekcí v56)
 *   tg:<id>   banka týmových her (teamgames_v58_bank_*.php)
 *   p63:<id>  otázka z banky použitá v kroku cesty v63 (stejná banka jako tg:)
 *   sa:<id>   prestižní zkouška (special_assessments.php)
 * Metadata: competency (id z katalogu v62 nebo null), difficulty 1–3 (nebo null), type (single|bool|numeric|mcq),
 * variant_group (id skupiny rovnocenných variant nebo null), summative (zda otázka smí do sumativního testu).
 * Výchozí hodnoty se odvozují ze zdroje; ruční úpravy (přepsání) jsou v storage/assessment_v66/question_meta.json.php
 * a zapisují se jen přes storage_update. Metadata slouží jen analýze a hodnocení, žádné odměny.
 */

const Q66_SOURCES = ['mod', 'tg', 'p63', 'sa'];
const Q66_TYPES = ['single', 'bool', 'numeric', 'mcq'];
const Q66_REF_RE = '/^(mod|tg|p63|sa):[A-Za-z0-9_.\-]{1,80}$/';
const Q66_KEYS = ['competency', 'difficulty', 'type', 'variant_group', 'summative'];

function q66_path(): string
{
    return STORAGE_DIR . '/assessment_v66/question_meta.json.php';
}

function q66_ref_valid(string $ref): bool
{
    return preg_match(Q66_REF_RE, $ref) === 1;
}

/** Rozdělí klíč na [zdroj, id]; neplatný klíč = null. @return array{0:string,1:string}|null */
function q66_split(string $ref): ?array
{
    if (!q66_ref_valid($ref)) return null;
    [$source, $id] = explode(':', $ref, 2);
    return [$source, $id];
}

/** Normalizuje metadata; neplatná pole jsou zahozena (nikdy se neukládají). @return array<string,mixed> */
function q66_normalize(array $meta): array
{
    $out = ['competency' => null, 'difficulty' => null, 'type' => null, 'variant_group' => null, 'summative' => true];
    if (is_string($meta['competency'] ?? null) && preg_match('/^[a-z][a-z0-9_]{2,39}$/', $meta['competency']) === 1) $out['competency'] = $meta['competency'];
    if (is_int($meta['difficulty'] ?? null) && $meta['difficulty'] >= 1 && $meta['difficulty'] <= 3) $out['difficulty'] = $meta['difficulty'];
    if (is_string($meta['type'] ?? null) && in_array($meta['type'], Q66_TYPES, true)) $out['type'] = $meta['type'];
    if (is_string($meta['variant_group'] ?? null) && preg_match('/^[A-Za-z0-9_.\-]{1,60}$/', $meta['variant_group']) === 1) $out['variant_group'] = $meta['variant_group'];
    if (is_bool($meta['summative'] ?? null)) $out['summative'] = $meta['summative'];
    return $out;
}

/** Surové řádky banky týmových her (včetně obtížnosti), jednou za požadavek. @return array<string,array<string,mixed>> */
function q66_bank_rows(): array
{
    static $rows = null;
    if ($rows !== null) return $rows;
    $rows = [];
    foreach (['teamgames_v58_bank_net.php', 'teamgames_v58_bank_gfx.php'] as $file) {
        $path = __DIR__ . '/' . $file;
        $data = is_file($path) ? include $path : [];
        foreach (is_array($data) ? $data : [] as $row) {
            if (is_array($row) && is_string($row['id'] ?? null) && $row['id'] !== '') $rows[$row['id']] = $row;
        }
    }
    return $rows;
}

/** Id kompetence podle tagu tématu, nebo null. */
function q66_competency_for_topic(string $classId, string $topic): ?string
{
    if ($topic === '' || !function_exists('comp62_subject_for_class')) return null;
    $subject = comp62_subject_for_class($classId);
    if ($subject === null) return null;
    return comp62_match($subject, ['topic:' . $topic])[0] ?? null;
}

/** Výchozí metadata odvozená ze zdroje otázky. @return array<string,mixed> */
function q66_default_meta(string $classId, string $ref): array
{
    $parts = q66_split($ref);
    if ($parts === null) return q66_normalize([]);
    [$source, $id] = $parts;
    $meta = [];
    if ($source === 'mod') {
        $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
        foreach ((array)($module['questions'] ?? []) as $q) {
            if (!is_array($q) || (string)($q['id'] ?? '') !== $id) continue;
            $meta = ['competency' => q66_competency_for_topic($classId, (string)($q['kb'] ?? '')), 'type' => 'mcq', 'difficulty' => 2];
            break;
        }
    } elseif ($source === 'tg' || $source === 'p63') {
        $row = q66_bank_rows()[$id] ?? null;
        if (is_array($row)) {
            $category = (string)($row['category'] ?? '');
            $comps = defined('P63_BANK_COMPETENCY') ? (P63_BANK_COMPETENCY[$category] ?? []) : [];
            $diff = $row['difficulty'] ?? null;
            $meta = ['competency' => $comps[0] ?? null, 'type' => in_array($row['type'] ?? '', ['single', 'bool', 'numeric'], true) ? $row['type'] : null,
                'difficulty' => is_numeric($diff) ? max(1, min(3, (int)$diff)) : null];
        }
    }
    return q66_normalize($meta);
}

/** Metadata položky: výchozí odvozená, přepsaná ruční úpravou (jen platná pole). @return array<string,mixed> */
function q66_meta(string $classId, string $ref): array
{
    $meta = q66_default_meta($classId, $ref);
    $override = storage_read(q66_path(), false)[$ref] ?? null;
    if (is_array($override)) {
        foreach (Q66_KEYS as $key) {
            if (array_key_exists($key, $override)) $meta = q66_normalize([$key => $override[$key]] + $meta);
        }
    }
    return $meta;
}

/** Uloží ruční úpravu metadat jedné položky (jen platná pole, jinak false). */
function q66_set_override(string $ref, array $changes): bool
{
    if (!q66_ref_valid($ref)) return false;
    $clean = array_intersect_key($changes, array_flip(Q66_KEYS));
    if ($clean === []) return false;
    storage_update(q66_path(), static function (array $data) use ($ref, $clean): array {
        $data[$ref] = array_replace(is_array($data[$ref] ?? null) ? $data[$ref] : [], $clean);
        return $data;
    });
    return true;
}

/**
 * Náhodné pořadí otázek se seedem (stejný žák a test = stejné pořadí, jiný žák = jiné); bez rozdílu při shodě
 * hashe rozhoduje původní pořadí. @param list<array<string,mixed>> $questions
 * @return list<array<string,mixed>>
 */
function q66_seeded_order(array $questions, string $seed): array
{
    $keyed = [];
    foreach (array_values($questions) as $i => $q) {
        $keyed[] = [sha1($seed . '|' . (string)(is_array($q) ? ($q['id'] ?? $i) : $i)), $i, $q];
    }
    usort($keyed, static fn(array $a, array $b): int => strcmp($a[0], $b[0]) ?: $a[1] <=> $b[1]);
    return array_column($keyed, 2);
}
