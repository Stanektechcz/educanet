<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Aréna – Záznam závodu pro projektor (ARN-05).
 *
 * Z událostí dokončeného závodu (arena57_race_events) postaví časovou osu: start, každé vyřešení (a „první krev“),
 * milníky společného cíle třídy (25/50/75/100 %) a konec. Jména respektují nastavení soukromí závodu stejně
 * jako živý projektor (arena57_public_name, viewer = null). PRIV58-06: žák s „Nechci vidět pořadí“ (hide_rank) je
 * v ose jen „Spolužák“ bez bodů, v režimu „osobní rekord“ osa ukazuje jen souhrn třídy (žádná jména ani body)
 * a anonymní „Hráč N“ se čísluje nezávisle na pořadí i na tom, kdo řešil první. Stránka je vždy nejdřív vykreslená jako obyčejný
 * seznam kroků (funguje bez JS i s `prefers-reduced-motion`); JS (assets/arena-v58.js) nad ní jen přidá
 * přehrávání (play/pause/rychlost/krok, klávesnice).
 */

require_once __DIR__ . '/arena_v57_views.php';
require_once __DIR__ . '/arena_v58_weekly.php';

/** @return list<array{t:int,kind:string,text:string}> časová osa v sekundách od startu závodu */
function arena58_replay_timeline(array $race): array
{
    $calc = arena57_compute($race);
    $roster = arena57_roster((string)$race['class_id']);
    $mode = (string)($race['settings']['names'] ?? 'initials');
    $goalTarget = (int)($race['settings']['class_goal'] ?? 0);
    $start = arena57_ts($race['started_at'] ?? null) ?? 0;
    $end = arena57_ts($race['ends_at'] ?? null) ?? $start;
    $hidden = (array)($race['hide_rank'] ?? []);
    $classOnly = (string)($race['settings']['rating'] ?? 'zebricek') === 'osobni_rekord';
    $anonNo = arena58_replay_anon_numbers((string)$race['id'], $calc['solves'], $hidden);

    $frames = [];
    $frames[] = ['t' => 0, 'kind' => 'start', 'text' => 'Závod „' . (string)$race['title'] . '“ začíná – ' . count((array)$race['levels']) . ' ' . arena58_plural(count((array)$race['levels']), 'úloha', 'úlohy', 'úloh') . ', ' . ($race['mode'] === 'teams' ? count((array)$race['teams']) . ' týmy' : 'každý sám za sebe') . '.'];

    $nextMilestone = $goalTarget > 0 ? 1 : 5; // 1..4 = 25/50/75/100 %; 5+ = bez cíle (nic se nehlásí)
    $solvedSoFar = 0;
    foreach ($calc['solves'] as $s) {
        $solvedSoFar++;
        $key = (string)$s['student_key'];
        $t = max(0, (int)$s['ts'] - $start);
        $level = arena57_level_title((string)$s['level']);
        if ($classOnly) {
            $frames[] = ['t' => $t, 'kind' => 'solve', 'text' => 'Třída vyřešila „' . $level . '“ – celkem ' . $solvedSoFar . ' ' . arena58_plural($solvedSoFar, 'úloha', 'úlohy', 'úloh') . '.'];
        } elseif (!empty($hidden[$key])) {
            $frames[] = ['t' => $t, 'kind' => 'solve', 'text' => 'Spolužák vyřešil(a) „' . $level . '“.'];
        } else {
            $name = $mode === 'anon' ? 'Hráč ' . ($anonNo[$key] ?? 0) : arena57_public_name($mode, $key, (string)($s['label'] ?? ''), $roster, null, null);
            $isFirst = !empty($s['first']);
            $text = $name . ' vyřešil(a) „' . $level . '“ (+' . (int)($s['points'] ?? 0) . ' b)' . ($isFirst ? ' – první krev!' : '');
            $frames[] = ['t' => $t, 'kind' => $isFirst ? 'first_blood' : 'solve', 'text' => $text];
        }
        while ($goalTarget > 0 && $nextMilestone <= 4 && $solvedSoFar >= (int)ceil($goalTarget * $nextMilestone / 4)) {
            $pct = $nextMilestone * 25;
            $frames[] = ['t' => $t, 'kind' => 'goal', 'text' => $pct === 100 ? 'Třída splnila společný cíl – ' . $goalTarget . ' vyřešených úloh! 🎉' : 'Společný cíl třídy: ' . $pct . ' % hotovo.'];
            $nextMilestone++;
        }
    }
    $endText = $calc['solves'] === [] ? ' Tentokrát nikdo nezískal body – příště to půjde líp.'
        : ($classOnly ? ' Třída vyřešila celkem ' . count($calc['solves']) . ' ' . arena58_plural(count($calc['solves']), 'úlohu', 'úlohy', 'úloh') . ' – každý si porovná výsledek se svým osobním rekordem.' : ' Konečné pořadí je připravené níž.');
    $frames[] = ['t' => max(0, $end - $start), 'kind' => 'end', 'text' => 'Závod skončil.' . $endText];
    return $frames;
}

/**
 * PRIV58-06: anonymní čísla „Hráč N“ – pořadí podle sha256(závod|klíč), tedy nezávislé na konečném pořadí
 * i na tom, kdo řešil první. Žáci se skrytým pořadím (hide_rank) číslo nedostanou (v ose jsou „Spolužák“).
 */
function arena58_replay_anon_numbers(string $raceId, array $solves, array $hidden): array
{
    $keys = [];
    foreach ($solves as $s) {
        $key = (string)($s['student_key'] ?? '');
        if ($key !== '' && empty($hidden[$key])) $keys[$key] = hash('sha256', $raceId . '|replay|' . $key);
    }
    asort($keys, SORT_STRING);
    $out = [];
    $n = 0;
    foreach (array_keys($keys) as $key) $out[(string)$key] = ++$n;
    return $out;
}

function arena58_replay_time_text(int $secs): string
{
    $secs = max(0, $secs);
    return sprintf('%d:%02d', intdiv($secs, 60), $secs % 60);
}

/** GET teacher.php?tab=arena&race=<id>&zaznam=1 (přihlášení a oprávnění ověřuje teacher.php). */
function arena58_replay_render(string $raceId): void
{
    $now = arena57_now();
    $race = arena57_valid_id($raceId) ? arena57_race($raceId) : null;
    $status = $race !== null ? arena57_status($race, $now) : 'draft';
    if (!headers_sent()) {
        if ($race === null) http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
    ?><!doctype html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title><?= arena57_h($race !== null ? 'Záznam · ' . (string)$race['title'] : 'Záznam závodu') ?></title>
<link rel="stylesheet" href="assets/arena-v57.css?v=57.0"><link rel="stylesheet" href="assets/arena-v58.css?v=58.0"></head>
<body class="arena57-projector-body">
<?php if ($race === null): ?>
  <main class="arena57-projector arena58-replay"><h1>Závod nebyl nalezen</h1><p>Zavři tuhle kartu a otevři záznam znovu ze záložky Aréna.</p></main>
<?php elseif ($status !== 'finished'): ?>
  <main class="arena57-projector arena58-replay"><h1>Záznam ještě není hotový</h1><p>Záznam se dá přehrát, až závod „<?= arena57_h((string)$race['title']) ?>“ skončí. Zatím ho můžeš sledovat živě přes Projektor.</p></main>
<?php else:
    $frames = arena58_replay_timeline($race);
    $json = json_encode($frames, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]';
?>
  <main class="arena57-projector arena58-replay" data-arena58-replay data-frames="<?= arena57_h($json) ?>">
    <header class="arena57-proj-head">
      <div><span class="arena57-proj-kicker"><?= arena57_h(arena57_class_label((string)$race['class_id'])) ?> · Záznam závodu</span><h1><?= arena57_h((string)$race['title']) ?></h1></div>
      <div class="arena58-replay-clock" role="timer" aria-live="off" data-replay-clock>0:00</div>
    </header>
    <div class="arena58-replay-controls" role="group" aria-label="Ovládání přehrávání záznamu">
      <button type="button" class="btn primary" data-replay-play>▶ Přehrát</button>
      <button type="button" class="btn secondary" data-replay-pause hidden>⏸ Pozastavit</button>
      <button type="button" class="btn secondary" data-replay-back aria-label="Krok zpět">◀</button>
      <button type="button" class="btn secondary" data-replay-fwd aria-label="Krok vpřed">▶</button>
      <label class="arena58-replay-speed">Rychlost
        <select data-replay-speed>
          <option value="0.5">0,5×</option><option value="1" selected>1×</option><option value="2">2×</option><option value="4">4×</option>
        </select>
      </label>
      <a class="btn secondary" href="<?= arena57_h('teacher.php?tab=arena&class=' . rawurlencode((string)$race['class_id']) . '&race=' . rawurlencode((string)$race['id'])) ?>">← Zpět na Arénu</a>
    </div>
    <p class="arena57-sr" aria-live="polite" data-replay-announce></p>
    <ol class="arena58-replay-list" data-replay-list aria-label="Průběh závodu (čas od startu)">
      <?php foreach ($frames as $i => $f): ?>
        <li data-replay-frame="<?= (int)$i ?>" data-t="<?= (int)$f['t'] ?>" class="arena58-replay-item is-<?= arena57_h((string)$f['kind']) ?>">
          <time><?= arena57_h(arena58_replay_time_text((int)$f['t'])) ?></time> <span><?= arena57_h((string)$f['text']) ?></span>
        </li>
      <?php endforeach; ?>
    </ol>
    <footer class="arena57-proj-foot">Bez JavaScriptu nebo s omezeným pohybem (prefers-reduced-motion) vidíš rovnou celý seznam kroků výše – přehrávání je jen doplněk.</footer>
  </main>
  <script src="assets/arena-v57.js?v=57.0" defer></script>
  <script src="assets/arena-v58.js?v=58.0" defer></script>
<?php endif; ?>
</body></html>
<?php
}
