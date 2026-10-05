<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v60 · ARN-07 Výzva spolužákovi (1v1 duel).
 *
 * Opt-in: žák musí sám zapnout „přijímám výzvy“ (arena60_optin_set), výchozí je VYPNUTO.
 * Výzva jde jen spolužákovi ze stejné třídy (roster přes arena57_roster/project_students_for_class),
 * nikdy sám sobě. Limity proti spamu: max ARENA60_MAX_OPEN_SENT rozeslaných čekajících výzev,
 * max jedna výzva na stejného adresáta za ARENA60_COOLDOWN_SECS. Výzva vyprší po ARENA60_TTL_SECS.
 *
 * Úloha souboje: jedna z volných (pack 'free') úrovní Linux Labu – dostupná bez ohledu na postup,
 * takže oba soupeři mají férově stejné podmínky. Nezapisujeme žádný nový herní kontext do simulátoru
 * (bezpečnostní invariant labu i jednoduchost) – vítěze zjišťujeme „kontrolou“ existujících dat:
 * čteme lab57_solved($class,$key,'practice')[level_id]['at'] a bereme v úvahu jen řešení PO přijetí
 * výzvy (accepted_at). Kdo úlohu vyřeší dřív, vyhrává. Nevýhoda tohoto zjednodušení: žák, který úlohu
 * vyřešil už dřív (před výzvou), musí ji vyřešit znovu (level lze v practice módu resetovat/spustit znovu
 * jako 'sandbox' typ ne, ale check-typ levely lze spustit znovu jen pokud nebyly nikdy vyřešené –
 * pro souboj proto vybíráme jen level, který ani jeden ze soupeřů v practice ještě nevyřešil).
 *
 * Body: ZA VÝZVU SE BODY NEDÁVAJÍ (anti-farming) – jen historie výher/proher v profilu žáka.
 *
 * Soukromí: veřejně (na cizím profilu) je vidět jen souhrn (počet soubojů/výher), nikdy detail
 * soubojů s jinými spolužáky. Jméno v samotné výzvě/historii zobrazujeme podle nastavení soukromí
 * arény (výchozí iniciály) přes arena57_public_name()/lab57_display_name().
 */

require_once __DIR__ . '/arena_v57.php';

const ARENA60_MAX_OPEN_SENT = 3;
const ARENA60_COOLDOWN_SECS = 86400; // 1 výzva na stejného adresáta / 24 h
const ARENA60_TTL_SECS = 172800; // vyprší po 48 h
const ARENA60_STATUSES_OPEN = ['pending'];

// ---------------------------------------------------------------------------
// Úložiště
// ---------------------------------------------------------------------------

function arena60_path(): string
{
    return lab57_storage_dir() . '/arena_v60_challenges.json.php';
}

function arena60_normalize(array $d): array
{
    $d['challenges'] = is_array($d['challenges'] ?? null) ? $d['challenges'] : [];
    $d['optin'] = is_array($d['optin'] ?? null) ? $d['optin'] : [];
    return $d;
}

function arena60_data(): array
{
    return arena60_normalize(lab57_store_read(arena60_path()));
}

function arena60_update(callable $mutate): array
{
    return lab57_store_update(arena60_path(), static fn(array $d): array => arena60_normalize($mutate(arena60_normalize($d))));
}

// ---------------------------------------------------------------------------
// Opt-in (výchozí VYPNUTO)
// ---------------------------------------------------------------------------

function arena60_optin_get(string $classId, string $studentKey): bool
{
    $d = arena60_data();
    return !empty($d['optin'][$classId][$studentKey]);
}

function arena60_optin_set(string $classId, string $studentKey, bool $on): void
{
    arena60_update(static function (array $d) use ($classId, $studentKey, $on): array {
        if ($on) { $d['optin'][$classId][$studentKey] = true; } else { unset($d['optin'][$classId][$studentKey]); }
        return $d;
    });
}

// ---------------------------------------------------------------------------
// Pomocné dotazy
// ---------------------------------------------------------------------------

/** @return list<array<string,mixed>> Všechny výzvy dané třídy (nezávisle na roli). */
function arena60_class_challenges(string $classId): array
{
    $out = [];
    foreach (arena60_data()['challenges'] as $row) {
        if (is_array($row) && (string)($row['class_id'] ?? '') === $classId) $out[] = $row;
    }
    return $out;
}

function arena60_find(string $classId, string $id): ?array
{
    foreach (arena60_class_challenges($classId) as $row) {
        if ((string)$row['id'] === $id) return $row;
    }
    return null;
}

/** Přepočte expirované pending výzvy na 'expired' (líné vyhodnocení při čtení – žádný cron). */
function arena60_sweep_expired(string $classId, int $now): void
{
    arena60_update(static function (array $d) use ($classId, $now): array {
        foreach ($d['challenges'] as $i => $row) {
            if ((string)($row['class_id'] ?? '') !== $classId) continue;
            if ((string)($row['status'] ?? '') === 'pending' && $now > (int)($row['expires_at'] ?? 0)) {
                $d['challenges'][$i]['status'] = 'expired';
            }
        }
        return $d;
    });
}

/** Doplní výsledek (kdo vyhrál) u accepted výzev, pokud už ho lze zjistit z lab57_solved(). Líné, žádný zápis mimo tenhle soubor. */
function arena60_sweep_results(string $classId, int $now): void
{
    $finished = [];
    arena60_update(static function (array $d) use ($classId, $now, &$finished): array {
        foreach ($d['challenges'] as $i => $row) {
            if ((string)($row['class_id'] ?? '') !== $classId || (string)($row['status'] ?? '') !== 'accepted') continue;
            $winner = arena60_detect_winner($classId, $row);
            if ($winner !== null) {
                $d['challenges'][$i]['status'] = 'done';
                $d['challenges'][$i]['winner_key'] = $winner;
                $d['challenges'][$i]['finished_at'] = date(DATE_ATOM, $now);
                $finished[] = $d['challenges'][$i];
            } elseif ($now > (int)($row['expires_at'] ?? 0)) {
                $d['challenges'][$i]['status'] = 'expired';
            }
        }
        return $d;
    });
    // v64: ELO a liga (idempotentně podle id výzvy; mimo zámek souboru výzev). Selhání ratingu nesmí shodit souboj.
    foreach ($finished as $row) fair64_record_challenge($classId, $row, $now);
}

/** @return string|null klíč vítěze, nebo null pokud ještě nikdo nevyřešil (po accepted_at). */
function arena60_detect_winner(string $classId, array $challenge): ?string
{
    $acceptedAt = arena57_ts($challenge['accepted_at'] ?? null);
    if ($acceptedAt === null) return null;
    $levelId = (string)$challenge['level_id'];
    $best = null; // ['key'=>..., 'at'=>ts]
    foreach ([(string)$challenge['from_key'], (string)$challenge['to_key']] as $key) {
        $solved = lab57_solved($classId, $key, 'practice');
        $info = $solved[$levelId] ?? null;
        if (!is_array($info)) continue;
        $at = arena57_ts($info['at'] ?? null);
        if ($at === null || $at < $acceptedAt) continue; // řešení musí být AŽ po přijetí výzvy
        if ($best === null || $at < $best['at']) $best = ['key' => $key, 'at' => $at];
    }
    return $best['key'] ?? null;
}

/**
 * Úrovně vhodné pro souboj: PRVNÍ úroveň každého balíčku (sekvenční odemykání znamená, že první
 * úroveň je vždy dostupná úplně každému bez ohledu na dosavadní postup – viz lab57_level_unlocked()),
 * kromě volného terminálu (typ 'free', bez jednoznačného řešení). Vybíráme jen z těch, které ANI
 * JEDEN ze soupeřů dosud v practice nevyřešil (aby oba řešili od nuly, férově).
 */
function arena60_pick_level(string $classId, string $fromKey, string $toKey): ?string
{
    $solvedFrom = array_keys(lab57_solved($classId, $fromKey, 'practice'));
    $solvedTo = array_keys(lab57_solved($classId, $toKey, 'practice'));
    $taken = array_flip(array_merge($solvedFrom, $solvedTo));
    $candidates = [];
    foreach (array_keys(lab57_packs()) as $packId) {
        $levels = lab57_pack_levels((string)$packId);
        $first = $levels[0] ?? null;
        if ($first === null) continue;
        $id = (string)$first['id'];
        if (($first['type'] ?? '') === 'free' || isset($taken[$id])) continue;
        $candidates[] = $id;
    }
    if ($candidates === []) return null;
    return $candidates[random_int(0, count($candidates) - 1)];
}

function arena60_open_sent_count(string $classId, string $fromKey): int
{
    $n = 0;
    foreach (arena60_class_challenges($classId) as $row) {
        if ((string)($row['from_key'] ?? '') === $fromKey && in_array((string)($row['status'] ?? ''), ARENA60_STATUSES_OPEN, true)) $n++;
    }
    return $n;
}

function arena60_recent_to_seconds(string $classId, string $fromKey, string $toKey, int $now): ?int
{
    $latest = null;
    foreach (arena60_class_challenges($classId) as $row) {
        if ((string)($row['from_key'] ?? '') !== $fromKey || (string)($row['to_key'] ?? '') !== $toKey) continue;
        $at = arena57_ts($row['created_at'] ?? null);
        if ($at !== null && ($latest === null || $at > $latest)) $latest = $at;
    }
    return $latest === null ? null : ($now - $latest);
}

// ---------------------------------------------------------------------------
// Akce
// ---------------------------------------------------------------------------

/** @throws RuntimeException srozumitelný důvod pro žáka */
function arena60_challenge_create(string $classId, string $fromKey, string $toKey, int $now): array
{
    arena60_sweep_expired($classId, $now);
    if ($fromKey === '' ) throw new RuntimeException(tr('Nejdřív se přihlas do své třídy.'));
    if ($toKey === $fromKey) throw new RuntimeException(tr('Sám sebe vyzvat nemůžeš.'));
    $roster = arena57_roster($classId);
    if (!isset($roster[$fromKey]) || !isset($roster[$toKey])) throw new RuntimeException(tr('Spolužák musí být ve stejné třídě.'));
    if (!arena60_optin_get($classId, $toKey)) throw new RuntimeException(tr('Tenhle spolužák výzvy zatím nepřijímá.'));
    if (arena60_open_sent_count($classId, $fromKey) >= ARENA60_MAX_OPEN_SENT) throw new RuntimeException(tr('Máš rozeslané už {n} čekající výzvy, počkej na odpověď.', ['n' => ARENA60_MAX_OPEN_SENT]));
    $sinceLast = arena60_recent_to_seconds($classId, $fromKey, $toKey, $now);
    if ($sinceLast !== null && $sinceLast < ARENA60_COOLDOWN_SECS) throw new RuntimeException(tr('Tomuhle spolužákovi jsi výzvu poslal/a nedávno, zkus to znovu za chvíli.'));
    $levelId = arena60_pick_level($classId, $fromKey, $toKey);
    if ($levelId === null) throw new RuntimeException(tr('Momentálně není volná žádná vhodná úloha pro souboj – zkus to jindy.'));
    $row = [
        'id' => bin2hex(random_bytes(8)), 'class_id' => $classId, 'from_key' => $fromKey, 'to_key' => $toKey,
        'level_id' => $levelId, 'status' => 'pending', 'created_at' => date(DATE_ATOM, $now),
        'expires_at' => $now + ARENA60_TTL_SECS, 'accepted_at' => null, 'winner_key' => null, 'finished_at' => null,
    ];
    arena60_update(static function (array $d) use ($row): array {
        $d['challenges'][] = $row;
        return $d;
    });
    return $row;
}

function arena60_challenge_respond(string $classId, string $studentKey, string $id, bool $accept, int $now): void
{
    arena60_sweep_expired($classId, $now);
    $row = arena60_find($classId, $id);
    if ($row === null) throw new RuntimeException(tr('Výzva nebyla nalezena.'));
    if ((string)$row['to_key'] !== $studentKey) throw new RuntimeException(tr('Tahle výzva není určená tobě.'));
    if ((string)$row['status'] !== 'pending') throw new RuntimeException(tr('Na tuhle výzvu už bylo odpovězeno, nebo vypršela.'));
    arena60_update(static function (array $d) use ($id, $accept, $now): array {
        foreach ($d['challenges'] as $i => $r) {
            if ((string)$r['id'] !== $id) continue;
            $d['challenges'][$i]['status'] = $accept ? 'accepted' : 'declined';
            if ($accept) $d['challenges'][$i]['accepted_at'] = date(DATE_ATOM, $now);
        }
        return $d;
    });
}

function arena60_challenge_cancel(string $classId, string $studentKey, string $id, int $now): void
{
    $row = arena60_find($classId, $id);
    if ($row === null) throw new RuntimeException(tr('Výzva nebyla nalezena.'));
    if ((string)$row['from_key'] !== $studentKey) throw new RuntimeException(tr('Zrušit smíš jen svoje vlastní výzvy.'));
    if (!in_array((string)$row['status'], ['pending', 'accepted'], true)) throw new RuntimeException(tr('Tuhle výzvu už nejde zrušit.'));
    arena60_update(static function (array $d) use ($id): array {
        foreach ($d['challenges'] as $i => $r) {
            if ((string)$r['id'] === $id) $d['challenges'][$i]['status'] = 'cancelled';
        }
        return $d;
    });
}

// ---------------------------------------------------------------------------
// Data pro profil (readonly)
// ---------------------------------------------------------------------------

/** Kompletní pohled pro vlastní profil žáka (přehled, příchozí, odchozí, historie). */
function arena60_profile_data(string $classId, string $studentKey, int $now): array
{
    arena60_sweep_expired($classId, $now);
    arena60_sweep_results($classId, $now);
    $all = arena60_class_challenges($classId);
    $roster = arena57_roster($classId);
    $nameOf = static fn(string $key): string => lab57_display_name(arena57_full_label($key, '', $roster));

    $incoming = $outgoing = $history = [];
    $wins = $losses = 0;
    foreach ($all as $row) {
        $level = lab57_level((string)$row['level_id']);
        $decorate = static function (array $r) use ($nameOf, $level): array {
            $r['from_name'] = $nameOf((string)$r['from_key']);
            $r['to_name'] = $nameOf((string)$r['to_key']);
            $r['level_title'] = (string)($level['title'] ?? $r['level_id']);
            return $r;
        };
        if ((string)$row['to_key'] === $studentKey && (string)$row['status'] === 'pending') $incoming[] = $decorate($row);
        if ((string)$row['from_key'] === $studentKey && in_array((string)$row['status'], ['pending', 'accepted'], true)) $outgoing[] = $decorate($row);
        if (in_array($studentKey, [(string)$row['from_key'], (string)$row['to_key']], true) && in_array((string)$row['status'], ['done', 'declined', 'cancelled', 'expired'], true)) {
            $history[] = $decorate($row);
            if ((string)$row['status'] === 'done') {
                if ((string)$row['winner_key'] === $studentKey) $wins++; else $losses++;
            }
        }
    }
    usort($history, static fn($a, $b) => strcmp((string)$b['created_at'], (string)$a['created_at']));
    return [
        'optin' => arena60_optin_get($classId, $studentKey),
        'incoming' => $incoming, 'outgoing' => $outgoing, 'history' => array_slice($history, 0, 20),
        'wins' => $wins, 'losses' => $losses, 'weekly' => function_exists('arena58_weekly_history') ? arena58_weekly_history($classId, $studentKey, $now) : [],
    ];
}

/** Souhrn pro CIZÍ profil – jen čísla, žádný detail konkrétních soubojů. */
function arena60_public_summary(string $classId, string $studentKey, int $now): array
{
    arena60_sweep_expired($classId, $now);
    arena60_sweep_results($classId, $now);
    $wins = $losses = $total = 0;
    foreach (arena60_class_challenges($classId) as $row) {
        if (!in_array($studentKey, [(string)$row['from_key'], (string)$row['to_key']], true)) continue;
        if ((string)$row['status'] !== 'done') continue;
        $total++;
        if ((string)$row['winner_key'] === $studentKey) $wins++; else $losses++;
    }
    return ['optin' => arena60_optin_get($classId, $studentKey), 'total' => $total, 'wins' => $wins, 'losses' => $losses];
}

/** Může $fromKey vyzvat $toKey teď? Vrací [ok, duvod-pokud-ne]. Čistě informativní (create ověřuje znovu, atomicky). */
function arena60_can_challenge(string $classId, string $fromKey, string $toKey, int $now): array
{
    if ($fromKey === $toKey) return [false, tr('Sám sebe vyzvat nemůžeš.')];
    $roster = arena57_roster($classId);
    if (!isset($roster[$toKey])) return [false, tr('Spolužák musí být ve stejné třídě.')];
    if (!arena60_optin_get($classId, $toKey)) return [false, tr('Tenhle spolužák výzvy zatím nepřijímá.')];
    if (arena60_open_sent_count($classId, $fromKey) >= ARENA60_MAX_OPEN_SENT) return [false, tr('Máš rozeslané už {n} čekající výzvy.', ['n' => ARENA60_MAX_OPEN_SENT])];
    $sinceLast = arena60_recent_to_seconds($classId, $fromKey, $toKey, $now);
    if ($sinceLast !== null && $sinceLast < ARENA60_COOLDOWN_SECS) return [false, tr('Tomuhle spolužákovi jsi psal/a nedávno.')];
    return [true, ''];
}

/** Počet příchozích čekajících výzev (pro počítadlo v menu/profilu). */
function arena60_incoming_count(string $classId, string $studentKey): int
{
    $n = 0;
    $now = arena57_now();
    foreach (arena60_class_challenges($classId) as $row) {
        if ((string)($row['to_key'] ?? '') === $studentKey && (string)($row['status'] ?? '') === 'pending' && $now <= (int)($row['expires_at'] ?? 0)) $n++;
    }
    return $n;
}
