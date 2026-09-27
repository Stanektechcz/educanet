<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Aréna – pohledy: stránka závodu pro žáka, učitelská záložka „Aréna“ a projektor.
 * Všechny terminály jsou jen simulace (linux_v57_*). Jména na žebříčcích respektují nastavení soukromí závodu.
 */

require_once __DIR__ . '/arena_v57.php';
if (is_file(__DIR__ . '/arena_v58_views.php')) require_once __DIR__ . '/arena_v58_views.php';
if (is_file(__DIR__ . '/arena_v58_replay.php')) require_once __DIR__ . '/arena_v58_replay.php';

// ---------------------------------------------------------------------------
// Drobné pomocníky
// ---------------------------------------------------------------------------

/** v58: lab58_packs() bez skryté banky Týdenní hádanky (ta se nikdy nenabízí do závodu ani procvičování). */
function arena57_visible_packs(): array
{
    $packs = function_exists('lab58_packs') ? lab58_packs() : lab57_packs();
    if (function_exists('arena58_weekly_pack_id')) unset($packs[arena58_weekly_pack_id()]);
    return $packs;
}

function arena57_h(string $value): string
{
    return function_exists('e') ? e($value) : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function arena57_class_label(string $classId): string
{
    return function_exists('teacher_class_label') ? teacher_class_label($classId) : $classId;
}

function arena57_status_label(string $status): string
{
    return match ($status) { 'live' => tr('Běží'), 'finished' => tr('Skončil'), default => tr('Připravený') };
}

function arena57_names_label(string $mode): string
{
    return match ($mode) { 'full' => tr('celá jména'), 'anon' => tr('anonymně'), default => tr('jméno + iniciála') };
}

/** v58 · ARN-06 */
function arena57_rating_label(string $rating): string
{
    return match ($rating) { 'osobni_rekord' => tr('osobní rekord'), 'kategorie' => tr('kategorie'), default => tr('žebříček') };
}

function arena57_clock_text(int $secs): string
{
    $secs = max(0, $secs);
    return sprintf('%d:%02d', intdiv($secs, 60), $secs % 60);
}

function arena57_time_text(?string $iso): string
{
    $ts = arena57_ts($iso);
    return $ts === null ? '–' : date('H:i', $ts);
}

function arena57_time_tag(?string $iso): string
{
    $ts = arena57_ts($iso);
    return $ts === null ? "<time>–</time>" : "<time datetime=\"" . arena57_h(date(DATE_ATOM, $ts)) . "\">" . date("H:i", $ts) . "</time>";
}

function arena57_secs_text(?int $secs): string
{
    if ($secs === null) return '–';
    return $secs >= 60 ? tr('{min} min {s} s', ['min' => intdiv($secs, 60), 's' => $secs % 60]) : tr('{s} s', ['s' => $secs]);
}

function arena57_render_countdown(array $race, int $now, string $extraClass = ''): void
{
    $remaining = arena57_remaining($race, $now);
    $status = arena57_status($race, $now);
    ?>
    <div class="arena57-clock <?= arena57_h($extraClass) ?><?= $status !== 'live' ? ' is-stopped' : '' ?>" data-arena57-countdown data-ends="<?= arena57_h((string)($race['ends_at'] ?? '')) ?>" data-now="<?= $now ?>" data-status="<?= arena57_h($status) ?>">
      <span class="arena57-clock-label"><?= $status === 'live' ? arena57_h(tr('Zbývá')) : ($status === 'draft' ? arena57_h(tr('Délka')) : arena57_h(tr('Konec'))) ?></span>
      <strong class="arena57-clock-value" data-arena57-clock-value role="timer" aria-live="off"><?= $status === 'draft' ? arena57_h(tr('{n} min', ['n' => (int)$race['duration_min']])) : ($status === 'live' ? arena57_clock_text($remaining) : '0:00') ?></strong>
      <span class="arena57-sr" data-arena57-clock-announce aria-live="polite"></span>
    </div>
    <?php
}

function arena57_render_goal(array $goal, string $extraClass = ''): void
{
    if ((int)$goal['target'] <= 0) return;
    ?>
    <div class="arena57-goal <?= arena57_h($extraClass) ?>" data-arena57-goal>
      <div class="arena57-goal-head"><strong><?= arena57_h(tr('Společný cíl třídy')) ?></strong><span data-arena57-goal-text><?= arena57_h(tr('{done} / {target} vyřešených úloh', ['done' => (int)$goal['done'], 'target' => (int)$goal['target']])) ?></span></div>
      <div class="arena57-bar" role="progressbar" aria-label="<?= arena57_h(tr('Společný cíl třídy')) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)$goal['pct'] ?>" data-arena57-goal-bar><i style="width:<?= (int)$goal['pct'] ?>%"></i></div>
    </div>
    <?php
}

/** Řádky pořadí (stejná struktura vykresluje i arena-v57.js). */
function arena57_render_rows(array $rows, string $empty): void
{
    if ($rows === []) { echo '<li class="arena57-row is-empty">' . arena57_h($empty) . '</li>'; return; }
    foreach ($rows as $row) {
        ?><li class="arena57-row<?= !empty($row['me']) ? ' is-me' : '' ?>"><b class="arena57-rank"><?= $row['rank'] !== null ? (int)$row['rank'] . '.' : '–' ?></b><span class="arena57-name"><?= edu_cs((string)$row['name']) ?><?= !empty($row['me']) ? ' <small>(' . arena57_h(tr('ty')) . ')</small>' : '' ?></span><span class="arena57-pts"><?= arena57_h(tr('{n} b', ['n' => (int)$row['points']])) ?></span><small class="arena57-solved"><?= arena57_h(tr('{n} úl.', ['n' => (int)$row['solved']])) ?></small></li><?php
    }
}

function arena57_render_feed(array $feed, string $empty): void
{
    if ($feed === []) { echo '<li class="is-empty">' . arena57_h($empty) . '</li>'; return; }
    foreach ($feed as $item) {
        ?><li<?= !empty($item['me']) ? ' class="is-me"' : '' ?>><?= tr_html('{cas} {jmeno} první vyřešil(a) {uroven}', ['cas' => arena57_time_tag((string)$item['at']), 'jmeno' => '<strong>' . edu_cs((string)$item['name']) . '</strong>', 'uroven' => '<em>' . edu_cs((string)$item['level']) . '</em>']) ?></li><?php
    }
}

// ---------------------------------------------------------------------------
// Žák: stránka závodu ?view=lab&zavod=<id>[&uroven=<level>]
// ---------------------------------------------------------------------------

function arena57_render_student(string $classId, array $module, string $raceId, string $levelId, string $flash): void
{
    $race = arena57_valid_id($raceId) ? arena57_race($raceId) : null;
    if ($race === null || (string)$race['class_id'] !== $classId) {
        $_SESSION['flash'] = tr('Tenhle závod neexistuje nebo patří jiné třídě.');
        redirect_to('?view=lab');
    }
    $now = arena57_now();
    $status = arena57_status($race, $now);
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    $csrf = csrf_token();
    $board = arena57_board($raceId, $studentKey, $classId);
    $solved = function_exists('lab57_solved') && $studentKey !== '' ? lab57_solved($classId, $studentKey, 'race:' . $raceId) : [];
    $levels = [];
    foreach ((array)$race['levels'] as $id) {
        $level = lab57_level((string)$id);
        if ($level !== null) $levels[(string)$id] = $level;
    }
    $selected = null;
    if ($levelId !== '' && isset($levels[$levelId])) {
        $selected = $levels[$levelId];
    } elseif ($levelId !== '') {
        $flash = tr('Tahle úloha do závodu nepatří.');
    } elseif ($status === 'live') {
        foreach ($levels as $id => $level) { if (!isset($solved[$id])) { $selected = $level; break; } }
    }
    $base = '?view=lab&zavod=' . rawurlencode($raceId);
    render_header(tr('Závod') . ' · ' . (string)$race['title'], $module, true);
    ?>
    <div class="t52 arena57 arena57-student is-<?= arena57_h($status) ?>" data-arena57-student data-race="<?= arena57_h($raceId) ?>" data-status="<?= arena57_h($status) ?>" data-api="lab_v57_api.php">
      <?php if ($flash !== ''): ?><div class="u51-notice" role="status"><?= arena57_h($flash) ?></div><?php endif; ?>
      <?php arena57_render_student_head($race, $board, $now, $status); ?>
      <?php if ($status !== 'draft') arena57_render_rank_toggle($raceId, $csrf, $board); ?>
      <?php if ($status === 'draft'): ?>
        <section class="arena57-card arena57-waiting" role="status">
          <h2><?= arena57_h(tr('Závod ještě nezačal')) ?></h2>
          <p><?= arena57_h(tr('Počkej na signál učitele. Stránka se sama přepne, jakmile závod odstartuje.')) ?></p>
          <p class="arena57-muted"><?= arena57_h(tr('Úlohy: {n} · délka {min} min', ['n' => count($levels), 'min' => (int)$race['duration_min']])) ?><?= $race['mode'] === 'teams' ? ' · ' . arena57_h(tr('týmy se rozdělí při startu')) : '' ?>.</p>
        </section>
      <?php else: ?>
        <?php arena57_render_goal($board['goal']); ?>
        <?php arena57_render_student_levels($levels, $solved, $selected, $base); ?>
        <div class="arena57-layout">
          <section class="arena57-main">
            <?php if ($status === 'finished' && $selected === null): ?>
              <?php arena57_render_student_final($race, $board, $studentKey !== '' && arena57_took_part($race, $studentKey, arena57_race_events($race))); ?>
            <?php elseif ($selected !== null): ?>
              <?php if ($status === 'finished'): ?><p class="arena57-note"><?= arena57_h(tr('Závod skončil – body se už nepočítají, ale úlohu si můžeš v klidu dořešit.')) ?></p><?php endif; ?>
              <?php if (function_exists('lab57_render_workspace')): lab57_render_workspace($selected, 'race:' . $raceId, ['next_url' => $base, 'compact' => true]); else: ?>
                <div class="arena57-card arena57-muted"><?= arena57_h(tr('Terminál se právě připravuje. Zkus stránku obnovit za chvilku.')) ?></div>
              <?php endif; ?>
            <?php else: ?>
              <section class="arena57-card arena57-alldone"><h2><?= arena57_h(tr('Všechno máš vyřešené!')) ?></h2><p><?= arena57_h(tr('Skvělá práce. Počkej na konec závodu – třeba ještě pomůžeš třídě ke společnému cíli tím, že vysvětlíš postup spolužákovi (bez prozrazení kódu).')) ?></p></section>
            <?php endif; ?>
          </section>
          <?php arena57_render_student_board($race, $board); ?>
        </div>
      <?php endif; ?>
      <p class="arena57-foot"><?= arena57_h(tr('Terminál je simulace – nic se nespouští na skutečném počítači.')) ?><?php if (!($status === 'live' && !empty($race['settings']['lock_nav']))): ?> <a href="?view=lab"><?= arena57_h(tr('← Zpět do Labu')) ?></a><?php else: ?> <?= arena57_h(tr('Během závodu je navigace zamčená.')) ?><?php endif; ?></p>
    </div>
    <?php
    render_footer();
}

function arena57_render_student_head(array $race, array $board, int $now, string $status): void
{
    $me = $board['me'];
    ?>
    <header class="arena57-head">
      <div class="arena57-head-title">
        <span class="t52-kicker"><?= arena57_h(tr('Třídní závod')) ?> · Linux Lab · <?= arena57_h(arena57_status_label($status)) ?></span>
        <h1><?= edu_cs((string)$race['title']) ?></h1>
        <p><?= arena57_h(tr('{n} úloh', ['n' => count((array)$race['levels'])])) ?> · <?= $race['mode'] === 'teams' ? arena57_h(tr('týmy')) : arena57_h(tr('každý sám za sebe')) ?> · <?= !empty($race['settings']['hints']) ? arena57_h(tr('nápovědy zapnuté (−{pct} % za kus)', ['pct' => (int)round(100 * (float)$race['settings']['hint_penalty'])])) : arena57_h(tr('nápovědy vypnuté')) ?> · <?= arena57_h(tr('první vyřešení úlohy ×1,25')) ?></p>
      </div>
      <?php arena57_render_countdown($race, $now); ?>
      <div class="arena57-me" aria-label="<?= arena57_h(tr('Moje výsledky')) ?>">
        <div><strong data-arena57-my-rank><?= $me['rank'] !== null ? (int)$me['rank'] . '.' : '–' ?></strong><small><?= arena57_h(tr('místo')) ?></small></div>
        <div><strong data-arena57-my-points><?= (int)$me['points'] ?></strong><small><?= arena57_h(tr('bodů')) ?></small></div>
        <?php if ($board['my_team'] !== null): ?><div><strong data-arena57-my-team><?= edu_cs((string)$board['my_team']['name']) ?></strong><small><?= arena57_h(tr('můj tým')) ?></small></div><?php endif; ?>
      </div>
    </header>
    <?php
}

function arena57_render_student_levels(array $levels, array $solved, ?array $selected, string $base): void
{
    $packs = arena57_visible_packs();
    ?>
    <nav class="arena57-levels" aria-label="<?= arena57_h(tr('Úlohy závodu')) ?>">
      <h2><?= arena57_h(tr('Úlohy')) ?></h2>
      <ol>
        <?php $n = 0; foreach ($levels as $id => $level): $n++; $isSolved = isset($solved[$id]); $isCurrent = $selected !== null && $selected['id'] === $id; ?>
          <li><a class="arena57-level<?= $isSolved ? ' is-solved' : '' ?><?= $isCurrent ? ' is-current' : '' ?>" href="<?= arena57_h($base . '&uroven=' . rawurlencode((string)$id)) ?>" data-level="<?= arena57_h((string)$id) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>>
            <span class="arena57-level-no" aria-hidden="true"><?= $n ?></span>
            <span class="arena57-level-text"><strong><?= edu_cs((string)$level['title']) ?></strong><small><?= edu_cs((string)($packs[$level['pack']]['title'] ?? $level['pack'])) ?> · <?= arena57_h(tr('{n} b', ['n' => (int)$level['points']])) ?></small></span>
            <i class="arena57-level-mark" data-arena57-level-mark aria-label="<?= $isSolved ? arena57_h(tr('vyřešeno')) : arena57_h(tr('zatím nevyřešeno')) ?>"><?= $isSolved ? '✓' : '' ?></i>
          </a></li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <?php
}

/** v58 · ARN-06: přepínač „Nechci vidět pořadí“ – u režimu osobní rekord nedává smysl (skryté je vždy), nezobrazí se. */
function arena57_render_rank_toggle(string $raceId, string $csrf, array $board): void
{
    if ((string)($board['rating'] ?? 'zebricek') === 'osobni_rekord') return;
    $hidden = !empty($board['rank_hidden']);
    ?>
    <form method="post" class="arena57-rank-toggle">
      <input type="hidden" name="csrf" value="<?= arena57_h($csrf) ?>">
      <input type="hidden" name="action" value="arena58_rank_toggle">
      <input type="hidden" name="race" value="<?= arena57_h($raceId) ?>">
      <button class="link-button" type="submit"><?= $hidden ? '👁 ' . arena57_h(tr('Zase chci vidět pořadí')) : '🙈 ' . arena57_h(tr('Nechci vidět pořadí')) ?></button>
    </form>
    <?php
}

function arena57_render_student_board(array $race, array $board): void
{
    $rating = (string)($board['rating'] ?? 'zebricek');
    $hidden = !empty($board['rank_hidden']);
    ?>
    <aside class="arena57-side" aria-label="<?= arena57_h(tr('Průběžné pořadí')) ?>">
      <?php if ($race['mode'] === 'teams' && !$hidden): ?>
        <section class="arena57-card"><h2><?= arena57_h(tr('Týmy')) ?></h2><ol class="arena57-board" data-arena57-teams><?php arena57_render_team_rows($board['teams']); ?></ol></section>
      <?php endif; ?>
      <?php if ($hidden): ?>
        <section class="arena57-card" role="status">
          <h2><?= arena57_h(tr('Pořadí je skryté')) ?></h2>
          <?php if ($rating === 'osobni_rekord' && $board['previous_best'] !== null): $prev = (int)$board['previous_best']['points']; $mine = (int)$board['me']['points']; ?>
            <p><?= tr_html('Teď máš {body}.', ['body' => '<strong>' . arena57_h(tr('{n} bodů', ['n' => $mine])) . '</strong>']) . "\n" ?>              <?php if ($prev > 0): ?><?= tr_html('Tvůj dosavadní rekord je {body} ({zavod}).', ['body' => '<strong>' . arena57_h(tr('{n} bodů', ['n' => $prev])) . '</strong>', 'zavod' => edu_cs((string)$board['previous_best']['race_title'])]) ?> <?= $mine > $prev ? arena57_h(tr('Právě jsi ho překonal(a)! 🎉')) : arena57_h(tr('Ještě to nestihlo, zkoušej dál – vidíš jen svoje výsledky, nikdo tě nesrovnává s ostatními.')) ?>
              <?php else: ?><?= arena57_h(tr('Tohle je tvůj první závod v tomhle hodnocení – příště uvidíš, jestli se zlepšuješ.')) ?><?php endif; ?>
            </p>
          <?php else: ?>
            <p><?= arena57_h(tr('Zvolil(a) jsi, že nechceš vidět pořadí. Vidíš jen svoje body a společný cíl třídy – nikdo jiný tě v pořadí taky neuvidí.')) ?></p>
          <?php endif; ?>
        </section>
      <?php elseif ($board['categories'] !== []): ?>
        <?php foreach ($board['categories'] as $cat): if (!$cat['mine']) continue; ?>
          <section class="arena57-card"><h2><?= edu_cs((string)$cat['label']) ?> <small class="arena57-muted">(<?= arena57_h(tr('tvoje skupina')) ?>)</small></h2>
            <ol class="arena57-board" data-arena57-rows><?php arena57_render_rows($cat['rows'], tr('Zatím nikdo ve tvojí skupině nemá body. Buď první!')); ?></ol>
          </section>
        <?php endforeach; ?>
        <?php foreach ($board['categories'] as $cat): if ($cat['mine']) continue; ?>
          <details class="arena57-card"><summary><?= edu_cs((string)$cat['label']) ?></summary><ol class="arena57-board"><?php arena57_render_rows($cat['rows'], tr('Zatím nikdo nemá body.')); ?></ol></details>
        <?php endforeach; ?>
      <?php else: ?>
        <section class="arena57-card">
          <h2><?= arena57_h(tr('Pořadí')) ?></h2>
          <ol class="arena57-board" data-arena57-rows><?php arena57_render_rows($board['rows'], tr('Zatím nikdo nemá body. Buď první!')); ?></ol>
          <p class="arena57-muted small"><?= arena57_h(tr('V tabulce jsou jen ti, kdo už mají body')) ?><?= ($race['settings']['names'] ?? '') === 'anon' ? arena57_h(tr('; ostatní jména jsou skrytá')) : '' ?> <?= arena57_h(tr('a kdo pořadí neskryl(a).')) ?></p>
        </section>
      <?php endif; ?>
      <?php if (!$hidden): ?>
        <section class="arena57-card"><h2><?= arena57_h(tr('První vyřešení')) ?></h2><ul class="arena57-feed" data-arena57-feed><?php arena57_render_feed($board['feed'], tr('Zatím nic – první vyřešení úlohy dává bonus ×1,25.')); ?></ul></section>
      <?php endif; ?>
      <p class="arena57-net" data-arena57-net role="status"></p>
    </aside>
    <?php
}

function arena57_render_team_rows(array $teams): void
{
    if ($teams === []) { echo '<li class="arena57-row is-empty">' . arena57_h(tr('Týmy se ukážou po startu.')) . '</li>'; return; }
    foreach ($teams as $team) {
        ?><li class="arena57-row<?= !empty($team['me']) ? ' is-me' : '' ?>"><b class="arena57-rank"><?= $team['rank'] !== null ? (int)$team['rank'] . '.' : '–' ?></b><span class="arena57-name"><?= edu_cs((string)$team['name']) ?><?php if (!empty($team['members'])): ?><small class="arena57-members"><?= edu_cs(implode(', ', (array)$team['members'])) ?></small><?php endif; ?></span><span class="arena57-pts"><?= arena57_h(tr('{n} b', ['n' => (int)$team['points']])) ?></span><small class="arena57-solved"><?= arena57_h(tr('{n} úl.', ['n' => (int)$team['solved']])) ?></small></li><?php
    }
}

function arena57_render_student_final(array $race, array $board, bool $took): void
{
    $me = $board['me'];
    $rank = $race['mode'] === 'teams' ? ($board['my_team']['rank'] ?? null) : $me['rank'];
    ?>
    <section class="arena57-card arena57-final" aria-labelledby="arena57-final-title">
      <h2 id="arena57-final-title"><?= arena57_h(tr('Konec závodu – díky, že jsi hrál(a)!')) ?></h2>
      <?php if ($me['solved'] > 0): ?>
        <p class="arena57-final-lead"><?php $solvedLabel = $me['solved'] === 1 ? tr('úlohu') : ($me['solved'] < 5 ? tr('úlohy') : tr('úloh')); ?><?= tr_html('Vyřešil(a) jsi {pocet} {slovo} za {body}', ['pocet' => '<strong>' . (int)$me['solved'] . '</strong>', 'slovo' => arena57_h($solvedLabel), 'body' => '<strong>' . arena57_h(tr('{n} bodů', ['n' => (int)$me['points']])) . '</strong>']) ?><?= $me['rank'] !== null ? ' ' . tr_html('a skončil(a) na {misto}', ['misto' => '<strong>' . tr('{n}. místě', ['n' => (int)$me['rank']]) . '</strong>']) : '' ?>.</p>
      <?php else: ?>
        <p class="arena57-final-lead"><?= arena57_h(tr('Tentokrát bez bodů – to nevadí. Každý příkaz, který jsi zkusil(a), je trénink. Úlohy si teď můžeš projít v klidu.')) ?></p>
      <?php endif; ?>
      <?php if ($took): ?><p class="arena57-xp"><?= tr_html('+{xp} XP za účast', ['xp' => arena57_xp_for($race, $rank)]) ?><?= isset(ARENA57_XP_PODIUM[(int)$rank]) ? ' ' . arena57_h(tr('a umístění na stupních vítězů')) : '' ?>.</p><?php endif; ?>
      <?php if ((int)$board['goal']['target'] > 0): ?><p><?= $board['goal']['done'] >= $board['goal']['target'] ? arena57_h(tr('Třída splnila společný cíl! 🎉')) : arena57_h(tr('Společný cíl třídy: {done} z {target} – příště to dáme.', ['done' => (int)$board['goal']['done'], 'target' => (int)$board['goal']['target']])) ?></p><?php endif; ?>
      <h3><?= arena57_h(tr('Konečné pořadí')) ?></h3>
      <?php if ($race['mode'] === 'teams'): ?><ol class="arena57-board is-final"><?php arena57_render_team_rows($board['teams']); ?></ol><?php endif; ?>
      <ol class="arena57-board is-final"><?php arena57_render_rows($board['rows'], tr('Nikdo nezískal body.')); ?></ol>
      <p class="arena57-muted"><?= arena57_h(tr('Co ti šlo, co ne? Řekni to učiteli – pomůže to připravit další závod. Úlohy nahoře si můžeš otevřít a dořešit bez časového tlaku.')) ?></p>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Učitel: záložka Aréna
// ---------------------------------------------------------------------------

function arena57_render_teacher_tab(array $modules, string $classId): void
{
    if (!isset($modules[$classId]) && $modules !== []) $classId = (string)array_key_first($modules);
    $now = arena57_now();
    $live = arena57_live_for_class($classId);
    $races = arena57_races_for_class($classId);
    $selected = null;
    $wanted = is_string($_GET['race'] ?? null) ? $_GET['race'] : '';
    foreach ($races as $race) { if ((string)$race['id'] === $wanted) $selected = $race; }
    $selected ??= $live ?? ($races[0] ?? null);
    $csrf = arena57_h(csrf_token());
    $rezim = (string)($_GET['rezim'] ?? 'zavody') === 'hadanka' ? 'hadanka' : 'zavody';
    ?>
    <div class="t52 arena57 arena57-teacher">
      <header class="t52-page-head arena57-page-head">
        <div><span class="t52-kicker">Aréna · Linux Lab</span><h1>Třídní závod</h1><p>Krátký časovaný závod v simulovaném terminálu. Žáci řeší úlohy Labu, každý má vlastní data i kódy. Slabší žáci nejsou na tabulce vidět, dokud nemají body; jména můžeš skrýt úplně.</p></div>
      </header>
      <nav class="arena57-classes" aria-label="Třída">
        <?php foreach (array_keys($modules) as $cid): $cid = (string)$cid; ?>
          <a class="<?= $cid === $classId ? 'active' : '' ?>" href="<?= arena57_h('teacher.php?tab=arena&rezim=' . rawurlencode($rezim) . '&class=' . rawurlencode($cid)) ?>"<?= $cid === $classId ? ' aria-current="page"' : '' ?>><?= arena57_h(arena57_class_label($cid)) ?></a>
        <?php endforeach; ?>
      </nav>
      <nav class="arena57-classes arena58w-modenav" aria-label="Režim Arény">
        <a class="<?= $rezim === 'zavody' ? 'active' : '' ?>" href="<?= arena57_h('teacher.php?tab=arena&class=' . rawurlencode($classId)) ?>"<?= $rezim === 'zavody' ? ' aria-current="page"' : '' ?>>Závody</a>
        <a class="<?= $rezim === 'hadanka' ? 'active' : '' ?>" href="<?= arena57_h('teacher.php?tab=arena&rezim=hadanka&class=' . rawurlencode($classId)) ?>"<?= $rezim === 'hadanka' ? ' aria-current="page"' : '' ?>>Týdenní hádanka</a>
      </nav>
      <?php if ($rezim === 'hadanka'): ?>
        <?php if (function_exists('arena58_weekly_render_teacher')) arena58_weekly_render_teacher($modules, $classId, $csrf); else echo '<section class="arena57-card">Týdenní hádanka se připravuje.</section>'; ?>
      <?php else: ?>
        <?php arena57_render_lab_toggle($classId, $csrf, $live); ?>
        <?php if ($selected !== null) arena57_render_race_panel($selected, $now, $csrf); ?>
        <div class="arena57-teacher-grid">
          <?php arena57_render_create_form($classId, $csrf); ?>
          <?php arena57_render_race_list($races, $selected, $now, $csrf); ?>
        </div>
        <?php arena57_render_progress($classId); ?>
      <?php endif; ?>
    </div>
    <?php
}

function arena57_hidden(string $csrf, string $action, string $classId, ?string $raceId = null): string
{
    return '<input type="hidden" name="csrf" value="' . $csrf . '"><input type="hidden" name="action" value="' . arena57_h($action) . '"><input type="hidden" name="class_id" value="' . arena57_h($classId) . '"><input type="hidden" name="return_tab" value="arena">' . ($raceId !== null ? '<input type="hidden" name="race" value="' . arena57_h($raceId) . '">' : '');
}

function arena57_render_lab_toggle(string $classId, string $csrf, ?array $live): void
{
    $enabled = arena57_lab_enabled($classId);
    ?>
    <section class="arena57-card arena57-labswitch">
      <div><strong>Linux Lab pro <?= arena57_h(arena57_class_label($classId)) ?>:</strong> <span class="arena57-badge <?= $enabled ? 'is-on' : 'is-off' ?>"><?= $enabled ? 'zapnutý' : 'vypnutý' ?></span>
        <?php if ($live !== null): ?> · <span class="arena57-badge is-live">Právě běží: <?= arena57_h((string)$live['title']) ?></span><?php endif; ?>
        <p class="arena57-muted small">Vypnutý Lab žáci v menu neuvidí a terminál neodpoví.</p></div>
      <form method="post"><?= arena57_hidden($csrf, 'arena57_lab_toggle', $classId) ?><input type="hidden" name="enabled" value="<?= $enabled ? '0' : '1' ?>"><button class="btn secondary" type="submit"><?= $enabled ? 'Vypnout Lab' : 'Zapnout Lab' ?></button></form>
    </section>
    <?php
}

function arena57_race_actions(array $race, string $status, string $csrf, bool $withDetail): void
{
    $cid = (string)$race['class_id'];
    $rid = (string)$race['id'];
    $projector = 'teacher.php?tab=arena&projector=1&race=' . rawurlencode($rid);
    ?>
    <div class="arena57-actions">
      <?php if ($status === 'draft'): ?>
        <form method="post"><?= arena57_hidden($csrf, 'arena57_start', $cid, $rid) ?><button class="btn primary" type="submit">▶ Start</button></form>
      <?php elseif ($status === 'live'): ?>
        <form method="post" data-arena57-live-only><?= arena57_hidden($csrf, 'arena57_extend', $cid, $rid) ?><button class="btn secondary" type="submit">+<?= ARENA57_EXTEND_MIN ?> min</button></form>
        <form method="post" data-arena57-live-only data-arena57-confirm="Opravdu ukončit závod teď?"><?= arena57_hidden($csrf, 'arena57_stop', $cid, $rid) ?><button class="btn secondary" type="submit">■ Stop</button></form>
      <?php endif; ?>
      <?php if ($status !== 'draft'): ?><a class="btn secondary" href="<?= arena57_h($projector) ?>" target="_blank" rel="noopener">Projektor ↗</a><?php endif; ?>
      <?php if ($status === 'finished'): ?><a class="btn secondary" href="<?= arena57_h('teacher.php?tab=arena&class=' . rawurlencode($cid) . '&race=' . rawurlencode($rid) . '&zaznam=1') ?>" target="_blank" rel="noopener">Záznam ↗</a><?php endif; ?>
      <?php if ($withDetail): ?><a class="btn secondary" href="<?= arena57_h('teacher.php?tab=arena&class=' . rawurlencode($cid) . '&race=' . rawurlencode($rid)) ?>">Detail</a><?php endif; ?>
      <?php if ($status !== 'live'): ?>
        <form method="post" data-arena57-confirm="Smazat závod „<?= arena57_h((string)$race['title']) ?>“?"><?= arena57_hidden($csrf, 'arena57_delete', $cid, $rid) ?><button class="link-button danger" type="submit">Smazat</button></form>
      <?php endif; ?>
    </div>
    <?php
}

function arena57_render_race_panel(array $race, int $now, string $csrf): void
{
    $status = arena57_status($race, $now);
    $data = $status === 'draft' ? null : arena57_teacher_data((string)$race['id'], $now);
    $poll = 'teacher.php?tab=arena&arena_poll=1&race=' . rawurlencode((string)$race['id']);
    ?>
    <section class="arena57-card arena57-panel is-<?= arena57_h($status) ?>" data-arena57-teacher data-poll="<?= arena57_h($poll) ?>" data-status="<?= arena57_h($status) ?>" aria-labelledby="arena57-panel-title">
      <header class="arena57-panel-head">
        <div>
          <span class="arena57-badge is-<?= arena57_h($status) ?>" data-arena57-status-badge><?= arena57_h(arena57_status_label($status)) ?></span>
          <h2 id="arena57-panel-title"><?= arena57_h((string)$race['title']) ?></h2>
          <p class="arena57-muted small"><?= count((array)$race['levels']) ?> úloh · <?= (int)$race['duration_min'] ?> min · <?= $race['mode'] === 'teams' ? 'týmy po ' . (int)$race['team_size'] : 'jednotlivci' ?> · nápovědy <?= !empty($race['settings']['hints']) ? '−' . (int)round(100 * (float)$race['settings']['hint_penalty']) . ' %' : 'vypnuté' ?> · jména: <?= arena57_h(arena57_names_label((string)$race['settings']['names'])) ?> · hodnocení: <?= arena57_h(arena57_rating_label((string)($race['settings']['rating'] ?? 'zebricek'))) ?><?= !empty($race['settings']['lock_nav']) ? ' · zamčená navigace' : '' ?></p>
        </div>
        <?php arena57_render_countdown($race, $now); ?>
        <?php arena57_race_actions($race, $status, $csrf, false); ?>
      </header>
      <?php if ($data === null): ?>
        <div class="arena57-draft">
          <p>Závod je připravený. Po stisku <strong>Start</strong> se žákům v menu objeví „Závod!“ a začne odpočet.<?= $race['mode'] === 'teams' ? ' Týmy se sestaví automaticky – vyrovnaně podle bodů z Labu (hadí draft).' : '' ?></p>
          <ol class="arena57-chips"><?php foreach ((array)$race['levels'] as $lvl): ?><li><?= arena57_h(arena57_level_title((string)$lvl)) ?></li><?php endforeach; ?></ol>
        </div>
      <?php else: ?>
        <?php arena57_render_goal($data['goal']); ?>
        <div class="arena57-panel-grid">
          <div class="arena57-block arena57-span2">
            <h3>Pořadí <small class="arena57-muted">(celá jména vidíš jen ty)</small></h3>
            <div class="arena57-table-wrap"><table class="arena57-table"><thead><tr><th scope="col">#</th><th scope="col">Žák</th><th scope="col">Body</th><th scope="col">Úlohy</th><th scope="col">Příkazy</th><th scope="col">Poslední úloha</th></tr></thead><tbody data-arena57-t-board><?php arena57_render_teacher_board_rows($data['board']); ?></tbody></table></div>
          </div>
          <div class="arena57-block">
            <h3>Zasekl(a) se <small class="arena57-muted">(≥ 8 min bez řešení, ale píše)</small></h3>
            <ul class="arena57-list" data-arena57-t-stuck><?php arena57_render_teacher_stuck($data['stuck']); ?></ul>
            <h3>Cizí kódy</h3>
            <ul class="arena57-list is-alert" data-arena57-t-alerts><?php arena57_render_teacher_alerts($data['alerts']); ?></ul>
          </div>
          <?php if ($race['mode'] === 'teams'): ?>
          <div class="arena57-block"><h3>Týmy</h3><ol class="arena57-list" data-arena57-t-teams><?php foreach ($data['teams'] as $team): ?><li><strong><?= $team['rank'] !== null ? (int)$team['rank'] . '. ' : '' ?><?= arena57_h((string)$team['name']) ?></strong> – <?= (int)$team['points'] ?> b, <?= (int)$team['solved'] ?> úl.<br><small><?= arena57_h(implode(', ', $team['members'])) ?></small></li><?php endforeach; ?></ol></div>
          <?php endif; ?>
          <?php if (($data['categories'] ?? []) !== []): ?>
          <div class="arena57-block arena57-span2"><h3>Kategorie <small class="arena57-muted">(žák soutěží jen ve své skupině)</small></h3>
            <?php foreach ($data['categories'] as $cat): ?>
              <p class="arena57-muted small"><strong><?= arena57_h((string)$cat['label']) ?></strong> (<?= (int)$cat['size'] ?> žáků)</p>
              <ol class="arena57-list"><?php foreach ($cat['rows'] as $row): ?><li><?= (int)$row['rank'] ?>. <?= arena57_h((string)$row['name']) ?> – <?= (int)$row['points'] ?> b, <?= (int)$row['solved'] ?> úl.</li><?php endforeach; ?></ol>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="arena57-block arena57-span2">
            <h3>Úlohy</h3>
            <div class="arena57-table-wrap"><table class="arena57-table"><thead><tr><th scope="col">Úloha</th><th scope="col">Vyřešilo</th><th scope="col">Medián času</th><th scope="col">Nápovědy</th><th scope="col">První</th></tr></thead><tbody data-arena57-t-levels><?php arena57_render_teacher_levels($data['levels']); ?></tbody></table></div>
          </div>
          <div class="arena57-block">
            <h3>Dění</h3>
            <ul class="arena57-list arena57-feed" data-arena57-t-feed><?php arena57_render_teacher_feed($data['feed']); ?></ul>
          </div>
        </div>
        <p class="arena57-net" data-arena57-net role="status"></p>
      <?php endif; ?>
    </section>
    <?php
}

function arena57_render_teacher_board_rows(array $board): void
{
    if ($board === []) { echo '<tr><td colspan="6" class="arena57-muted">Na soupisce třídy zatím nikdo není.</td></tr>'; return; }
    foreach ($board as $row) {
        ?><tr class="<?= empty($row['active']) ? 'is-idle' : '' ?>"><td><?= $row['rank'] !== null ? (int)$row['rank'] . '.' : '–' ?></td><td><?= arena57_h((string)$row['name']) ?></td><td><?= (int)$row['points'] ?></td><td><?= (int)$row['solved'] ?></td><td><?= (int)$row['cmds'] ?></td><td><?= arena57_h((string)($row['level'] ?? ($row['active'] ? '' : 'nezapojen(a)'))) ?></td></tr><?php
    }
}

function arena57_render_teacher_stuck(array $stuck): void
{
    if ($stuck === []) { echo '<li class="is-empty">Nikdo – zatím všichni postupují.</li>'; return; }
    foreach ($stuck as $s) {
        ?><li><strong><?= arena57_h((string)$s['name']) ?></strong> · <?= arena57_h((string)$s['level']) ?> · <?= (int)$s['minutes'] ?> min, <?= (int)$s['cmds'] ?> příkazů (<?= (int)$s['failed'] ?> s chybou)<br><code><?= arena57_h(implode(' ⏎ ', (array)$s['recent'])) ?></code></li><?php
    }
}

function arena57_render_teacher_alerts(array $alerts): void
{
    if ($alerts === []) { echo '<li class="is-empty">Žádné – nikdo nezkoušel cizí kód.</li>'; return; }
    foreach ($alerts as $a) {
        ?><li><?= arena57_time_tag((string)$a['at']) ?> <strong><?= arena57_h((string)$a['name']) ?></strong> zadal(a) kód spolužáka v úloze <em><?= arena57_h((string)$a['level']) ?></em></li><?php
    }
}

function arena57_render_teacher_levels(array $levels): void
{
    foreach ($levels as $l) {
        ?><tr><td><?= arena57_h((string)$l['title']) ?></td><td><?= (int)$l['solves'] ?></td><td><?= arena57_h(arena57_secs_text($l['median_secs'])) ?></td><td><?= (int)$l['hints'] ?></td><td><?= arena57_h((string)($l['first'] ?? '–')) ?></td></tr><?php
    }
}

function arena57_render_teacher_feed(array $feed): void
{
    if ($feed === []) { echo '<li class="is-empty">Zatím se nic nestalo.</li>'; return; }
    $kinds = ['solve' => 'vyřešil(a)', 'hint' => 'si vzal(a) nápovědu v', 'foreign_code' => 'zkusil(a) cizí kód v'];
    foreach ($feed as $f) {
        ?><li class="is-<?= arena57_h((string)$f['kind']) ?>"><?= arena57_time_tag((string)$f['at']) ?> <strong><?= arena57_h((string)$f['name']) ?></strong> <?= arena57_h($kinds[$f['kind']] ?? $f['kind']) ?> <em><?= arena57_h((string)$f['level']) ?></em><?= $f['kind'] === 'solve' ? ' (+' . (int)$f['points'] . ' b' . (!empty($f['first']) ? ', první!' : '') . ')' : '' ?></li><?php
    }
}

function arena57_render_create_form(string $classId, string $csrf): void
{
    $presets = arena57_presets();
    $packs = arena57_visible_packs();
    ?>
    <section class="arena57-card arena57-create" aria-labelledby="arena57-create-title">
      <h2 id="arena57-create-title">Nový závod</h2>
      <form method="post" class="arena57-form" data-arena57-create>
        <?= arena57_hidden($csrf, 'arena57_create', $classId) ?>
        <fieldset class="arena57-presets"><legend>Předvolba</legend>
          <?php foreach ($presets as $id => $p): ?>
            <label class="arena57-preset"><input type="radio" name="preset" value="<?= arena57_h($id) ?>" data-minutes="<?= (int)$p['minutes'] ?>" data-title="<?= arena57_h((string)$p['title']) ?>"<?= $id === 'rozcvicka' ? ' checked' : '' ?>><span><strong><?= arena57_h((string)$p['title']) ?></strong><small><?= arena57_h((string)$p['lead']) ?><?= $id !== 'custom' ? ' · ' . count($p['levels']) . ' úloh · ' . (int)$p['minutes'] . ' min' : '' ?></small></span></label>
          <?php endforeach; ?>
        </fieldset>
        <details class="arena57-custom" data-arena57-custom><summary>Vlastní výběr úloh (pro předvolbu „Vlastní výběr“)</summary>
          <?php foreach ($packs as $packId => $pack): $levels = lab57_pack_levels((string)$packId); if ($levels === []) continue; ?>
            <fieldset><legend><?= arena57_h((string)$pack['title']) ?></legend>
              <?php foreach ($levels as $level): ?><label class="arena57-check"><input type="checkbox" name="levels[]" value="<?= arena57_h((string)$level['id']) ?>"> <?= (int)$level['no'] ?>. <?= arena57_h((string)$level['title']) ?> <small>(<?= (int)$level['points'] ?> b)</small></label><?php endforeach; ?>
            </fieldset>
          <?php endforeach; ?>
        </details>
        <div class="arena57-fields">
          <label>Název<input name="title" maxlength="80" placeholder="např. Páteční rozcvička" data-arena57-title></label>
          <label>Délka (min)<input type="number" name="duration_min" min="<?= ARENA57_DURATION_MIN ?>" max="<?= ARENA57_DURATION_MAX ?>" value="10" required data-arena57-duration></label>
          <label>Režim<select name="mode"><option value="solo">Každý sám</option><option value="teams">Týmy (vyrovnané)</option></select></label>
          <label>Velikost týmu<select name="team_size"><option value="2">2</option><option value="3" selected>3</option><option value="4">4</option></select></label>
          <label>Srážka za nápovědu<select name="hint_penalty"><?php foreach ([0.1, 0.2, 0.3, 0.4, 0.5] as $p): ?><option value="<?= $p ?>"<?= $p === 0.2 ? ' selected' : '' ?>><?= (int)round($p * 100) ?> %</option><?php endforeach; ?></select></label>
          <label>Jména na tabulce<select name="names"><option value="initials">Adam K. (doporučeno)</option><option value="full">Celá jména</option><option value="anon">Anonymně (Hráč 3)</option></select></label>
          <label>Společný cíl třídy<input type="number" name="class_goal" min="0" max="<?= ARENA57_GOAL_MAX ?>" placeholder="počet vyřešení, 0 = bez cíle"></label>
          <label>Hodnocení<select name="rating" data-arena57-rating>
            <option value="zebricek">Žebříček (doporučeno)</option>
            <option value="osobni_rekord">Osobní rekord (bez pořadí, jen zlepšení)</option>
            <option value="kategorie">Kategorie podle úrovně (jen „Každý sám“)</option>
          </select></label>
          <label data-arena57-category-count hidden>Počet kategorií<select name="category_count"><option value="2">2</option><option value="3" selected>3</option></select></label>
        </div>
        <p class="arena57-muted small">Osobní rekord: žák vidí jen svůj výsledek a zlepšení proti svým minulým závodům – nikdo cizí pořadí neuvidí, ani přes API. Kategorie: třída se podle dřívějších bodů z Labu rozdělí na 2–3 skupiny, každá se svým žebříčkem.</p>
        <div class="arena57-toggles">
          <label class="arena57-check"><input type="checkbox" name="hints" value="1" checked> Povolit nápovědy</label>
          <label class="arena57-check"><input type="checkbox" name="lock_nav" value="1"> Zamknout navigaci (žák neodejde z Labu)</label>
        </div>
        <div class="arena57-actions"><button class="btn secondary" type="submit">Připravit závod</button><button class="btn primary" type="submit" name="start_now" value="1">Vytvořit a hned spustit ▶</button></div>
      </form>
    </section>
    <?php
}

function arena57_render_race_list(array $races, ?array $selected, int $now, string $csrf): void
{
    ?>
    <section class="arena57-card arena57-races" aria-labelledby="arena57-races-title">
      <h2 id="arena57-races-title">Závody třídy</h2>
      <?php if ($races === []): ?><p class="arena57-muted">Zatím žádný závod. Začni předvolbou Rozcvička – 10 minut stačí.</p><?php endif; ?>
      <ul class="arena57-race-list">
        <?php foreach (array_slice($races, 0, 12) as $race): $status = arena57_status($race, $now); ?>
          <li class="<?= $selected !== null && $selected['id'] === $race['id'] ? 'is-selected' : '' ?>">
            <div><span class="arena57-badge is-<?= arena57_h($status) ?>"><?= arena57_h(arena57_status_label($status)) ?></span> <strong><?= arena57_h((string)$race['title']) ?></strong>
              <small class="arena57-muted"><?= count((array)$race['levels']) ?> úloh · <?= (int)$race['duration_min'] ?> min · <?= $race['mode'] === 'teams' ? 'týmy' : 'sólo' ?> · <?= arena57_h(date('j. n. H:i', (int)(arena57_ts($race['started_at'] ?? null) ?? arena57_ts($race['created_at'] ?? null) ?? $now))) ?></small></div>
            <?php arena57_race_actions($race, $status, $csrf, true); ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php
}

/** Postup třídy v Labu: žáci × balíčky, body a posledních 30 příkazů (kde se žáci zasekávají). */
function arena57_render_progress(string $classId): void
{
    $roster = arena57_roster($classId);
    $packs = arena57_visible_packs();
    $totals = [];
    foreach (array_keys($packs) as $packId) $totals[$packId] = count(lab57_pack_levels((string)$packId));
    ?>
    <section class="arena57-card arena57-progress" aria-labelledby="arena57-progress-title">
      <h2 id="arena57-progress-title">Postup třídy v Labu</h2>
      <p class="arena57-muted small">Vyřešené úlohy v procvičování podle balíčků. Rozbal žáka a uvidíš jeho posledních 30 příkazů (i ze závodů) – rychle poznáš, kde tápe.</p>
      <?php if ($roster === []): ?><p class="arena57-muted">Soupiska třídy je zatím prázdná.</p><?php else: ?>
      <div class="arena57-table-wrap"><table class="arena57-table">
        <thead><tr><th scope="col">Žák</th><?php foreach ($packs as $pack): ?><th scope="col" title="<?= arena57_h((string)$pack['title']) ?>"><?= arena57_h((string)$pack['title']) ?></th><?php endforeach; ?><th scope="col">Body</th><th scope="col">Příkazy</th></tr></thead>
        <tbody>
        <?php foreach ($roster as $key => $student): $sum = lab57_student_summary($classId, (string)$key); $byPack = []; foreach (array_keys((array)$sum['solved']) as $lvl) { $l = lab57_level((string)$lvl); if ($l !== null) $byPack[$l['pack']] = ($byPack[$l['pack']] ?? 0) + 1; } $log = array_slice((array)$sum['log'], -30); ?>
          <tr>
            <td><details class="arena57-log"><summary><?= arena57_h((string)($student['label'] ?? 'Žák')) ?></summary>
              <?php if ($log === []): ?><p class="arena57-muted small">Zatím žádné příkazy.</p><?php else: ?>
              <ol><?php foreach (array_reverse($log) as $row): ?><li class="<?= (int)($row['exit'] ?? 0) !== 0 ? 'is-fail' : '' ?>"><?= arena57_time_tag((string)($row['t'] ?? '')) ?> <small><?= str_starts_with((string)($row['ctx'] ?? ''), 'race:') ? 'závod' : 'cvičení' ?> · <?= arena57_h(arena57_level_title((string)($row['lvl'] ?? ''))) ?></small> <code><?= arena57_h((string)($row['cmd'] ?? '')) ?></code></li><?php endforeach; ?></ol>
              <?php endif; ?></details></td>
            <?php foreach (array_keys($packs) as $packId): $done = (int)($byPack[$packId] ?? 0); ?><td class="<?= $done > 0 && $done >= $totals[$packId] ? 'is-full' : ($done > 0 ? 'is-some' : '') ?>"><?= $done ?>/<?= (int)$totals[$packId] ?></td><?php endforeach; ?>
            <td><?= (int)$sum['points'] ?></td><td><?= (int)$sum['cmds'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Projektor (samostatná stránka pro promítání třídě)
// ---------------------------------------------------------------------------

function arena57_render_projector(string $raceId): void
{
    $now = arena57_now();
    $data = arena57_valid_id($raceId) ? arena57_teacher_data($raceId, $now) : null;
    $race = $data !== null ? arena57_race($raceId) : null;
    if (!headers_sent()) {
        if ($data === null) http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
    }
    ?><!doctype html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><meta name="color-scheme" content="dark">
<title><?= arena57_h($race !== null ? 'Projektor · ' . (string)$race['title'] : 'Projektor · Aréna') ?></title>
<link rel="stylesheet" href="assets/arena-v57.css?v=57.0"></head>
<body class="arena57-projector-body">
<?php if ($race === null || $data === null): ?>
  <main class="arena57-projector"><h1>Závod nebyl nalezen</h1><p>Zavři tuto kartu a otevři projektor znovu ze záložky Aréna.</p></main>
<?php else: $public = $data['public']; ?>
  <main class="arena57-projector" data-arena57-projector data-poll="<?= arena57_h('teacher.php?tab=arena&arena_poll=1&race=' . rawurlencode($raceId)) ?>" data-status="<?= arena57_h((string)$data['race']['status']) ?>">
    <header class="arena57-proj-head">
      <div><span class="arena57-proj-kicker"><?= arena57_h(arena57_class_label((string)$race['class_id'])) ?> · Linux Lab · <span data-arena57-status-badge><?= arena57_h(arena57_status_label((string)$data['race']['status'])) ?></span></span><h1><?= arena57_h((string)$race['title']) ?></h1></div>
      <?php arena57_render_countdown($race, $now, 'is-projector'); ?>
    </header>
    <?php arena57_render_goal($public['goal'], 'is-projector'); ?>
    <?php $rating = (string)($public['rating'] ?? 'zebricek'); ?>
    <?php if ($rating === 'osobni_rekord'): ?>
      <section class="arena57-proj-inclusive"><h2>Osobní rekordy</h2><p>Tenhle závod je bez žebříčku – každý soutěží sám se sebou a zlepšuje si vlastní výsledky. Společný cíl třídy vidíš výše.</p></section>
    <?php elseif ($rating === 'kategorie' && $public['categories'] !== []): ?>
      <div class="arena57-proj-grid">
        <?php foreach ($public['categories'] as $cat): ?>
          <section><h2><?= arena57_h((string)$cat['label']) ?></h2><ol class="arena57-board is-projector" data-arena57-rows><?php arena57_render_rows($cat['rows'], 'Kdo bude první?'); ?></ol></section>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="arena57-proj-grid">
        <section><h2><?= $race['mode'] === 'teams' ? 'Týmy' : 'Top 10' ?></h2>
          <?php if ($race['mode'] === 'teams'): ?><ol class="arena57-board is-projector" data-arena57-teams><?php arena57_render_team_rows($public['teams']); ?></ol><?php endif; ?>
          <ol class="arena57-board is-projector" data-arena57-rows<?= $race['mode'] === 'teams' ? ' hidden' : '' ?>><?php arena57_render_rows($public['rows'], 'Kdo vyřeší první úlohu?'); ?></ol>
        </section>
        <section><h2>První vyřešení</h2><ul class="arena57-feed is-projector" data-arena57-feed><?php arena57_render_feed($public['feed'], 'Zatím nic – bonus ×1,25 čeká.'); ?></ul></section>
      </div>
    <?php endif; ?>
    <p class="arena57-net" data-arena57-net role="status"></p>
    <footer class="arena57-proj-foot">Terminál je simulace – nic se nespouští doopravdy. Hrajeme fér: každý má vlastní kódy.</footer>
  </main>
  <script src="assets/arena-v57.js?v=57.0" defer></script>
<?php endif; ?>
</body></html>
<?php
}
