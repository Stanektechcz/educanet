<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · hromadné schválení lekcí a vazeb na ŠVP (modul `hromadne74`, akce ba74_approve).
 *
 * Výběr: týden výuky (datum lekce podle kalendáře třídy), jedna třída, nebo celý školní rok (všechny třídy v rozsahu), volitelně
 * jen lekce / jen vazby ŠVP. Do výběru patří jen položky, které čekají na rozhodnutí (návrh, změněno po schválení); vrácené
 * k úpravě se hromadně neschvalují (učitel je vrátil vědomě). Pořadí: T-14 (výuka do 14 dní) první, pak podle data výuky.
 * Postup: náhled (počet, seznam, stav nyní → schváleno, otisk) → potvrzení (zaškrtnutí „zkontroloval/a jsem“) → POST ba74_approve
 * → výsledek po položkách (session, bez dat žáků) → vrácení jednotlivé položky existující akcí lc72_return / lc72_svp_return.
 * Zápis: jeden storage_update do storage/lesson_approvals_v72.json.php (stejné záznamy jako lc72_decide / svp72_decide, log append
 * se stropem LC72_LOG_MAX). Každá položka nese otisk, který učitel viděl v náhledu – změněný obsah se neschválí.
 * Politika ba74_approve: třída povinná a v rozsahu (class_ids[] všech tříd v potvrzení; handler ověří každou položku znovu),
 * oprávnění content.manage (asistent jen čte). Neznámá ba74_* akce je zakázaná.
 */

const BA74_MAX = 250;
const BA74_SCOPES = ['tyden' => 'týden výuky', 'trida' => 'jedna třída (celý rok)', 'rok' => 'celý školní rok – všechny moje třídy'];
const BA74_PENDING = ['navrh', 'zmeneno'];

/** Model lekce jednou za požadavek (náhled roku sahá na desítky položek). */
function ba74_lesson(string $class, int $n): array
{
    static $memo = [];
    return $memo[$class . '|' . $n] ??= lm71_lesson($class, $n);
}

/** Pondělí týdne daného data (Y-m-d). */
function ba74_monday(string $date): string
{
    $ts = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && strtotime($date) !== false ? (int)strtotime($date) : time();
    return date('Y-m-d', (int)strtotime('monday this week', $ts));
}

/**
 * Položky k rozhodnutí pro výběr. $classes = třídy v rozsahu učitele (už ověřené).
 * @return array{items:list<array<string,mixed>>,returned:int,approved:int}
 */
function ba74_candidates(array $classes, string $scope, string $weekStart, bool $lessons, bool $svp, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $weekEnd = date('Y-m-d', (int)strtotime($weekStart . ' +6 days'));
    $items = [];
    $returned = 0;
    $approved = 0;
    $records = lc72_records();
    $svpRecords = function_exists('svp72_records') ? svp72_records() : [];
    foreach ($classes as $class) {
        $dates = lc72_lesson_dates($class);
        $numbers = [];
        if ($lessons) $numbers = array_merge($numbers, lc72_overlay_lessons($class));
        if ($svp && function_exists('svp72_lesson_ids')) for ($n = 1; $n <= LM71_LESSONS; $n++) if (svp72_lesson_ids($class, $n) !== []) $numbers[] = $n;
        foreach (array_values(array_unique($numbers)) as $n) {
            $date = (string)($dates[$n] ?? '');
            if ($scope === 'tyden' && ($date === '' || $date < $weekStart || $date > $weekEnd)) continue;
            $days = $date !== '' ? (int)floor(((int)strtotime($date) - (int)strtotime($today)) / 86400) : null;
            $kinds = [];
            if ($lessons && lc72_overlay($class, $n) !== []) $kinds['lesson'] = lc72_status($class, $n, $records);
            if ($svp && function_exists('svp72_status') && svp72_lesson_ids($class, $n) !== []) $kinds['svp'] = svp72_status($class, $n, $svpRecords);
            foreach ($kinds as $kind => $st) {
                if ($st['status'] === 'schvaleno') { $approved++; continue; }
                if ($st['status'] === 'vraceno') { $returned++; continue; }
                if (!in_array($st['status'], BA74_PENDING, true)) continue;
                $items[] = ['class' => $class, 'lesson' => $n, 'kind' => $kind, 'title' => (string)ba74_lesson($class, $n)['title'], 'date' => $date, 'days' => $days,
                    't14' => $days !== null && $days >= 0 && $days <= LC72_T14_DAYS, 'status' => $st['status'], 'label' => $st['label'], 'hash' => $st['hash'],
                    'detail' => ba74_detail($class, $n, $kind, $st, $records)];
            }
        }
    }
    usort($items, static fn(array $a, array $b): int => ((int)$b['t14'] <=> (int)$a['t14']) ?: strcmp($a['date'] === '' ? '9999' : $a['date'], $b['date'] === '' ? '9999' : $b['date'])
        ?: strcmp($a['class'], $b['class']) ?: ($a['lesson'] <=> $b['lesson']) ?: strcmp($a['kind'], $b['kind']));
    return ['items' => $items, 'returned' => $returned, 'approved' => $approved];
}

/** Rozdíl proti současnému stavu: co žák uvidí po schválení (lekce) nebo jaké výsledky ŠVP se potvrdí. */
function ba74_detail(string $class, int $n, string $kind, array $st, array $records): string
{
    if ($kind === 'svp') {
        $cur = svp72_curriculum($class, $n);
        $ids = $cur !== null ? array_column($cur['outcomes'], 'id') : [];
        return ($st['status'] === 'zmeneno' ? 'Vazba se od schválení změnila; ' : '') . 'potvrdí se ' . count($ids) . ' ' . (count($ids) === 1 ? 'výsledek' : 'výsledky') . ' ŠVP: ' . implode(', ', array_slice($ids, 0, 4)) . (count($ids) > 4 ? ' …' : '');
    }
    $model = ba74_lesson($class, $n);
    $old = (string)($records[$class][(string)$n]['hash'] ?? '');
    $base = 'Žáci místo původní lekce uvidí nový cíl, exit ticket a domácí přípravu (úplnost ' . (int)$model['completeness']['score'] . '/' . (int)$model['completeness']['total'] . ').';
    return $st['status'] === 'zmeneno' ? 'Obsah se od schválení změnil (otisk ' . $old . ' → ' . $st['hash'] . '). ' . $base : $base;
}

/** Položka formuláře „třída|lekce|druh|otisk“ → pole, nebo null. */
function ba74_parse_item(mixed $raw): ?array
{
    if (!is_string($raw) || preg_match('/^(class_[a-z0-9]{1,8})\|(\d{1,2})\|(lesson|svp)\|([0-9a-f]{16})$/D', $raw, $m) !== 1) return null;
    $n = (int)$m[2];
    return $n >= 1 && $n <= LM71_LESSONS ? ['class' => $m[1], 'lesson' => $n, 'kind' => $m[3], 'hash' => $m[4]] : null;
}

/**
 * Hromadné schválení (validace po položkách + jeden zápis). $allowed = třídy, které smí učitel měnit.
 * @return list<array{class:string,lesson:int,kind:string,hash:string,ok:bool,message:string}>
 */
function ba74_apply(array $rawItems, array $allowed, string $by, ?int $now = null): array
{
    if ($rawItems === []) throw new RuntimeException('Vyberte aspoň jednu položku ke schválení.');
    if (count($rawItems) > BA74_MAX) throw new RuntimeException('Najednou jde schválit nejvýš ' . BA74_MAX . ' položek. Zužte výběr (týden nebo třída).');
    $at = date(DATE_ATOM, $now ?? time());
    $by = mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, 60);
    $results = [];
    $write = [];
    $records = lc72_records();
    $svpRecords = function_exists('svp72_records') ? svp72_records() : [];
    foreach ($rawItems as $raw) {
        $it = ba74_parse_item($raw);
        if ($it === null) { $results[] = ['class' => '', 'lesson' => 0, 'kind' => '', 'hash' => '', 'ok' => false, 'message' => 'Neplatná položka – přeskočeno.']; continue; }
        $key = $it['class'] . '|' . $it['lesson'] . '|' . $it['kind'];
        if (isset($write[$key])) continue;
        $fail = static function (string $msg) use (&$results, $it): void { $results[] = $it + ['ok' => false, 'message' => $msg]; };
        if (!in_array($it['class'], $allowed, true)) { $fail('Třída není ve vašem rozsahu – přeskočeno.'); continue; }
        $st = $it['kind'] === 'lesson' ? lc72_status($it['class'], $it['lesson'], $records) : (function_exists('svp72_status') ? svp72_status($it['class'], $it['lesson'], $svpRecords) : ['status' => 'chybi', 'hash' => '']);
        if (in_array($st['status'], ['puvodni', 'chybi'], true)) { $fail($it['kind'] === 'lesson' ? 'Lekce nemá návrh obsahu.' : 'Lekce nemá vazbu na ŠVP.'); continue; }
        if (!hash_equals((string)$st['hash'], $it['hash'])) { $fail('Obsah se od náhledu změnil – zkontrolujte novou verzi a schvalte jednotlivě.'); continue; }
        if ($st['status'] === 'schvaleno') { $results[] = $it + ['ok' => true, 'message' => 'Už bylo schváleno – beze změny.']; $write[$key] = null; continue; }
        if ($st['status'] === 'vraceno') { $fail('Vráceno k úpravě – schvalte jednotlivě po kontrole.'); continue; }
        $write[$key] = $it;
        $results[] = $it + ['ok' => true, 'message' => $it['kind'] === 'lesson' ? 'Schváleno – žáci uvidí nový obsah.' : 'Vazba na ŠVP schválena.'];
    }
    $write = array_filter($write);
    if ($write !== []) {
        storage_update(lc72_path(), static function (array $d) use ($write, $at, $by): array {
            $d['v'] = 1;
            $d['lessons'] = is_array($d['lessons'] ?? null) ? $d['lessons'] : [];
            $d['svp'] = is_array($d['svp'] ?? null) ? $d['svp'] : [];
            $log = array_values(array_filter((array)($d['log'] ?? []), 'is_array'));
            foreach ($write as $it) {
                $rec = ['status' => 'schvaleno', 'hash' => $it['hash'], 'at' => $at, 'by' => $by, 'note' => ''];
                if ($it['kind'] === 'lesson') $d['lessons'][$it['class']][(string)$it['lesson']] = $rec; else $d['svp'][$it['class']][(string)$it['lesson']] = $rec;
                $log[] = ['class' => $it['class'], 'lesson' => $it['lesson']] + ($it['kind'] === 'svp' ? ['kind' => 'svp'] : []) + ['status' => 'schvaleno', 'hash' => $it['hash'], 'at' => $at, 'by' => $by, 'bulk' => true];
            }
            $d['log'] = array_slice($log, -LC72_LOG_MAX);
            return $d;
        });
    }
    return $results;
}

/** POST ba74_approve (CSRF, politiku třídy a oprávnění content.manage ověřil teacher.php). */
function ba74_teacher_handle_post(string $action): void
{
    if ($action !== 'ba74_approve') throw new RuntimeException('Neznámá akce hromadného schválení.');
    if (($_POST['confirm'] ?? '') !== '1') throw new RuntimeException('Potvrďte zaškrtnutím, že jste náhled zkontrolovali – nic se zatím neschválilo.');
    $classes = array_values(array_filter((array)($_POST['class_ids'] ?? []), static fn($c): bool => is_string($c) && lm71_class_ok($c) && (!function_exists('teacher59_can_class') || teacher59_can_class($c))));
    $items = array_values(array_filter((array)($_POST['items'] ?? []), 'is_string'));
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $results = ba74_apply($items, $classes, $by);
    $ok = count(array_filter($results, static fn(array $r): bool => $r['ok']));
    $_SESSION['ba74_result'] = ['at' => date(DATE_ATOM), 'rows' => array_slice($results, 0, BA74_MAX)];
    $_SESSION['teacher_export_flash'] = ['type' => $ok === count($results) ? 'ok' : 'error',
        'message' => 'Hromadné schválení: ' . $ok . ' z ' . count($results) . ' položek v pořádku.' . ($ok < count($results) ? ' Neschválené položky a důvody najdete ve výsledku.' : '')];
    $query = ['tab' => 'hromadne74', 'vysledek' => '1'];
    $first = (string)($_POST['return_class'] ?? '');
    if (lm71_class_ok($first) && in_array($first, $classes, true)) $query['class'] = $first;
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
