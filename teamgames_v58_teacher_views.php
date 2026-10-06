<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Týmové hry – učitelská záložka `?tab=hry` (teacher_v58.php ji už zaregistroval).
 * Založení hry, řízení (start/pauza/pokračování/konec/archiv/smazání), živý panel týmů, banka otázek
 * učitele. POST akce `tg58_*`: CSRF a oprávnění (students.manage) už ověřil teacher.php.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';
require_once __DIR__ . '/teamgames_v58_game_relay.php';
require_once __DIR__ . '/teamgames_v58_game_bingo.php';
require_once __DIR__ . '/teamgames_v58_game_jeopardy.php';
require_once __DIR__ . '/teamgames_v58_game_tug.php';
require_once __DIR__ . '/teamgames_v58_game_netadmin.php';
require_once __DIR__ . '/teamgames_v58_game_escape.php';

// ---------------------------------------------------------------------------
// Pomocníci
// ---------------------------------------------------------------------------

function tg58_hidden(string $csrf, string $action, string $classId, string $gameId = ''): string
{
    $out = '<input type="hidden" name="csrf" value="' . tg58_h($csrf) . '"><input type="hidden" name="action" value="' . tg58_h($action) . '">'
        . '<input type="hidden" name="class_id" value="' . tg58_h($classId) . '"><input type="hidden" name="return_tab" value="hry">';
    if ($gameId !== '') $out .= '<input type="hidden" name="game" value="' . tg58_h($gameId) . '">';
    return $out;
}

/** v59 · AUTHZ58-07: předmět otázky banky (graphics | networks | both) smí spravovat jen učitel s tímto předmětem; legacy = vždy. */
function tg58_bank_line_in_scope(string $line): bool
{
    if (!function_exists('teacher59_can_subject')) return true;
    return teacher59_can_subject(in_array($line, ['graphics', 'networks'], true) ? $line : 'both');
}

/** v59: hra jen z třídy v rozsahu učitele (legacy = vždy). */
function tg58_session_in_scope(array $session): bool
{
    return !function_exists('teacher59_can_class') || teacher59_can_class((string)($session['class_id'] ?? ''));
}

function tg58_owned_session(string $gameId, string $classId): array
{
    $session = tg58_get($gameId);
    if ($session === null || (string)$session['class_id'] !== $classId || !tg58_session_in_scope($session)) throw new RuntimeException('Hra nebyla nalezena.');
    return $session;
}

// ---------------------------------------------------------------------------
// Záložka učitele
// ---------------------------------------------------------------------------

function tg58_render_teacher_tab(string $classId, string $csrf): void
{
    $classes = function_exists('project_catalog') ? array_keys((array)project_catalog()) : [$classId];
    // v59 · AUTHZ58-07: navigace jen přes třídy v rozsahu učitele (legacy = všechny).
    if (function_exists('teacher59_can_class')) $classes = array_values(array_filter($classes, static fn($c): bool => teacher59_can_class((string)$c)));
    if (!in_array($classId, $classes, true) && $classes !== []) $classId = (string)$classes[0];
    $now = tg58_now();
    $live = tg58_live_for_class($classId);
    $sessions = tg58_sessions_for_class($classId);
    $wanted = is_string($_GET['game'] ?? null) ? $_GET['game'] : '';
    $selected = null;
    foreach ($sessions as $s) { if ((string)$s['id'] === $wanted) $selected = $s; }
    $selected ??= $live ?? ($sessions[0] ?? null);
    ?>
    <div class="t52 tg58 tg58-teacher">
      <link rel="stylesheet" href="<?= e(edu_css_href('assets/teamgames-v58.css', 'assets/teamgames-v58.css?v=58.0')) ?>">
      <header class="t52-page-head"><span class="t52-kicker">Týmové hry</span><h1>Hry třídy</h1><p>Štafeta, bingo, Riskuj!, přetahovaná, správci sítě/webu a úniková místnost. Týmy se sestaví vyrovnaně při startu; jména na projektoru respektují nastavení soukromí.</p></header>
      <nav class="tg58-classes" aria-label="Třída">
        <?php foreach ($classes as $cid): $cid = (string)$cid; ?>
          <a class="<?= $cid === $classId ? 'active' : '' ?>" href="<?= tg58_h('teacher.php?tab=hry&class=' . rawurlencode($cid)) ?>"<?= $cid === $classId ? ' aria-current="page"' : '' ?>><?= tg58_h(function_exists('teacher_class_label') ? teacher_class_label($cid) : $cid) ?></a>
        <?php endforeach; ?>
      </nav>
      <?php if ($selected !== null) tg58_render_game_panel($selected, $now, $csrf); ?>
      <div class="tg58-teacher-grid">
        <?php tg58_render_create_form($classId, $csrf, $live !== null); ?>
        <?php tg58_render_session_list($sessions, $selected, $now, $csrf); ?>
      </div>
      <?php tg58_render_bank_admin($classId, $csrf); ?>
    </div>
    <?php
}

function tg58_render_status_badge(string $status): void
{
    echo '<span class="tg58-badge is-' . tg58_h($status) . '">' . tg58_h(tg58_status_label($status)) . '</span>';
}

function tg58_render_actions(array $session, string $status, string $csrf): void
{
    $classId = (string)$session['class_id'];
    $id = (string)$session['id'];
    $projector = 'teacher.php?tab=hry&projektor=' . rawurlencode($id);
    ?>
    <div class="tg58-actions">
      <?php if ($status === 'lobby'): ?><form method="post"><?= tg58_hidden($csrf, 'tg58_start', $classId, $id) ?><button class="btn primary" type="submit">▶ Start</button></form><?php endif; ?>
      <?php if ($status === 'running'): ?><form method="post"><?= tg58_hidden($csrf, 'tg58_pause', $classId, $id) ?><button class="btn secondary" type="submit">⏸ Pauza</button></form><?php endif; ?>
      <?php if ($status === 'paused'): ?><form method="post"><?= tg58_hidden($csrf, 'tg58_resume', $classId, $id) ?><button class="btn primary" type="submit">▶ Pokračovat</button></form><?php endif; ?>
      <?php if (in_array($status, ['running', 'paused'], true)): ?><form method="post" data-tg58-confirm="Opravdu ukončit hru teď?"><?= tg58_hidden($csrf, 'tg58_end', $classId, $id) ?><button class="btn secondary" type="submit">■ Konec</button></form><?php endif; ?>
      <?php if ($status !== 'lobby'): ?><a class="btn secondary" href="<?= tg58_h($projector) ?>" target="_blank" rel="noopener">Projektor ↗</a><?php endif; ?>
      <?php if ($status === 'finished'): ?><form method="post"><?= tg58_hidden($csrf, 'tg58_archive', $classId, $id) ?><button class="btn secondary" type="submit">Archivovat</button></form><?php endif; ?>
      <?php if (!in_array($status, ['running', 'paused'], true)): ?><form method="post" data-tg58-confirm="Smazat hru „<?= tg58_h((string)$session['title']) ?>“?"><?= tg58_hidden($csrf, 'tg58_delete', $classId, $id) ?><button class="link-button danger" type="submit">Smazat</button></form><?php endif; ?>
    </div>
    <?php
}

function tg58_render_game_panel(array $session, int $now, string $csrf): void
{
    $status = tg58_status($session, $now);
    $poll = 'teacher.php?tab=hry&tg_poll=1&game=' . rawurlencode((string)$session['id']);
    ?>
    <section class="tg58-card tg58-panel is-<?= tg58_h($status) ?>" data-tg58-teacher data-poll="<?= tg58_h($poll) ?>" data-status="<?= tg58_h($status) ?>">
      <header class="tg58-panel-head">
        <div><?php tg58_render_status_badge($status); ?><h2><?= tg58_h(tg58_type_label((string)$session['type'])) ?> · <?= tg58_h((string)$session['title']) ?></h2>
        <p class="tg58-muted small">Linie: <?= tg58_h($session['line'] === 'graphics' ? 'grafika a webdesign' : 'sítě') ?> · jména: <?= tg58_h((string)$session['names']) ?><?= (int)$session['duration_s'] > 0 ? ' · délka ' . (int)round($session['duration_s'] / 60) . ' min' : ' · bez časového limitu' ?></p></div>
        <?php tg58_render_actions($session, $status, $csrf); ?>
      </header>
      <?php if ($status === 'lobby'): ?>
        <p>Hra čeká na start. Týmy (<?= (int)$session['team_count'] ?>, režim „<?= tg58_h((string)$session['team_mode']) ?>“) se sestaví vyrovnaně hned po stisku Start.</p>
      <?php else: ?>
        <div data-tg58-t-body><?php tg58_render_teacher_body($session, $now); ?></div>
        <p class="tg58-net" data-tg58-net role="status"></p>
      <?php endif; ?>
    </section>
    <?php
}

function tg58_render_teacher_body(array $session, int $now): void
{
    $view = tg58_teacher_view($session, $now);
    ?>
    <div class="tg58-block"><h3>Týmy</h3><ul class="tg58-list" data-tg58-t-teams>
      <?php foreach ((array)$view['teams'] as $t): ?><li><?= tg58_h((string)$t['name']) ?> <small>(<?= (int)$t['size'] ?> žáků)</small></li><?php endforeach; ?>
    </ul></div>
    <?php if (isset($view['game']['rows'])): ?>
      <div class="tg58-block tg58-span2"><h3>Výsledky</h3><div class="tg58-table-wrap"><table class="tg58-table" data-tg58-t-rows><thead><tr><?php foreach (array_keys($view['game']['rows'][0] ?? ['team' => '']) as $col): ?><th scope="col"><?= tg58_h(tg58_col_label((string)$col)) ?></th><?php endforeach; ?></tr></thead><tbody>
        <?php foreach ((array)$view['game']['rows'] as $row): ?><tr><?php foreach ($row as $col => $v): ?><td><?= tg58_h(str_ends_with((string)$col, '_s') && ($v === null || is_int($v)) ? tg58_secs_text($v) : tg58_cell_text($v)) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
      </tbody></table></div></div>
    <?php elseif (isset($view['game']['scores'])): ?>
      <div class="tg58-block"><h3>Skóre</h3><ol class="tg58-list" data-tg58-t-scores><?php foreach ((array)$view['game']['scores'] as $s): ?><li><?= tg58_h((string)$s['team']) ?> – <?= (int)$s['points'] ?> b.</li><?php endforeach; ?></ol></div>
    <?php endif; ?>
    <div class="tg58-block"><h3>Signály</h3><ul class="tg58-list" data-tg58-t-signals>
      <?php foreach ((array)$view['signals'] as $sig): ?><li><?= tg58_h((string)$sig['team']) ?>: <?= tg58_h((string)$sig['label']) ?></li><?php endforeach; ?>
      <?php if ($view['signals'] === []): ?><li class="is-empty">Zatím žádné.</li><?php endif; ?>
    </ul></div>
    <?php
}

function tg58_render_create_form(string $classId, string $csrf, bool $liveExists): void
{
    ?>
    <section class="tg58-card tg58-create">
      <h2>Nová hra</h2>
      <?php if ($liveExists): ?><p class="tg58-muted small">Třída už má rozehranou hru – novou půjde spustit až po jejím konci (připravit ji ale můžeš už teď).</p><?php endif; ?>
      <form method="post" class="tg58-form" data-tg58-create>
        <?= tg58_hidden($csrf, 'tg58_create', $classId) ?>
        <label>Typ hry<select name="type" data-tg58-type-select>
          <?php foreach (tg58_registered_types() as $t): ?><option value="<?= tg58_h($t) ?>"><?= tg58_h(tg58_type_label($t)) ?></option><?php endforeach; ?>
        </select></label>
        <label>Název (nepovinné)<input name="title" maxlength="80" placeholder="např. Páteční štafeta"></label>
        <label>Linie<select name="line"><option value="networks">Sítě (3.A/4.A)</option><option value="graphics">Grafika a web (1.A/2.A)</option></select></label>
        <label>Sestavení týmů<select name="team_mode"><option value="snake">Vyrovnaně podle bodů</option><option value="random">Náhodně</option></select></label>
        <label>Počet týmů<input type="number" name="team_count" min="2" max="8" value="4"></label>
        <label>Jména na projektoru<select name="names"><option value="initials">Jméno + iniciála</option><option value="full">Celá jména</option><option value="anon">Anonymně</option></select></label>
        <label>Délka (min, 0 = bez limitu)<input type="number" name="duration_min" min="0" max="180" value="25"></label>
        <fieldset data-tg58-type-field="relay"><legend>Štafeta</legend><label>Počet úseků (3–5)<input type="number" name="legs" min="3" max="5" value="4"></label></fieldset>
        <fieldset data-tg58-type-field="bingo"><legend>Bingo</legend><label>Velikost karty<select name="size"><option value="4">4×4</option><option value="5">5×5</option></select></label></fieldset>
        <fieldset data-tg58-type-field="jeopardy"><legend>Riskuj!</legend><label>Velikost tabule<select name="size"><option value="5">5×5</option><option value="4">4×4</option></select></label><label>Okno na odpověď (s)<input type="number" name="window_s" min="20" max="60" value="35"></label></fieldset>
        <fieldset data-tg58-type-field="tug"><legend>Přetahovaná</legend><label>Počet kol<input type="number" name="rounds" min="1" max="5" value="3"></label><p class="tg58-muted small">Vždy přesně 2 týmy (celá třída).</p></fieldset>
        <fieldset data-tg58-type-field="netadmin"><legend>Správci sítě/webu</legend><label>Počet uzlů (16–24)<input type="number" name="node_count" min="16" max="24" value="20"></label><label>Vlna nových závad (min)<input type="number" name="wave_minutes" min="3" max="15" value="5"></label></fieldset>
        <div class="tg58-actions"><button class="btn primary" type="submit">Vytvořit hru</button></div>
      </form>
    </section>
    <?php
}

function tg58_render_session_list(array $sessions, ?array $selected, int $now, string $csrf): void
{
    ?>
    <section class="tg58-card tg58-sessions">
      <h2>Hry třídy</h2>
      <?php if ($sessions === []): ?><p class="tg58-muted">Zatím žádná hra – založ první vpravo.</p><?php endif; ?>
      <ul class="tg58-session-list">
        <?php foreach (array_slice($sessions, 0, 15) as $s): $status = tg58_status($s, $now); ?>
          <li class="<?= $selected !== null && $selected['id'] === $s['id'] ? 'is-selected' : '' ?>">
            <div><?php tg58_render_status_badge($status); ?> <strong><?= tg58_h(tg58_type_label((string)$s['type'])) ?></strong> <?= tg58_h((string)$s['title']) ?>
              <small class="tg58-muted"><?= tg58_h(date('j. n. H:i', (int)(tg58_ts($s['started_at'] ?? null) ?? tg58_ts($s['created_at'] ?? null) ?? $now))) ?></small></div>
            <?php tg58_render_actions($s, $status, $csrf); ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Banka otázek učitele
// ---------------------------------------------------------------------------

function tg58_render_bank_admin(string $classId, string $csrf): void
{
    $items = array_values(array_filter(tg58_quiz_teacher_bank(), static fn(array $it): bool => tg58_bank_line_in_scope((string)($it['line'] ?? '')))); // v59: jen předměty v rozsahu
    $bankLines = array_filter(['both' => 'Obě', 'networks' => 'Sítě', 'graphics' => 'Grafika'], static fn(string $l): bool => tg58_bank_line_in_scope($l), ARRAY_FILTER_USE_KEY);
    ?>
    <section class="tg58-card tg58-bank" aria-labelledby="tg58-bank-title">
      <h2 id="tg58-bank-title">Vlastní otázky do kvízové banky</h2>
      <p class="tg58-muted small">Používají je Bingo, Riskuj! a Přetahovaná (podle zvolené linie a třídy).</p>
      <form method="post" class="tg58-form">
        <?= tg58_hidden($csrf, 'tg58_bank_add', $classId) ?>
        <label>Otázka<input name="prompt" maxlength="300" required></label>
        <label>Kategorie<input name="category" maxlength="40" placeholder="např. HTML"></label>
        <label>Linie<select name="line"><?php foreach ($bankLines as $lineId => $lineLabel): ?><option value="<?= tg58_h($lineId) ?>"><?= tg58_h($lineLabel) ?></option><?php endforeach; ?></select></label>
        <label>Obtížnost<select name="difficulty"><option value="1">Lehká</option><option value="2">Střední</option><option value="3">Těžká</option></select></label>
        <label>Typ<select name="type" data-tg58-bank-type><option value="single">Výběr z možností</option><option value="bool">Ano/Ne</option><option value="text">Krátká odpověď</option></select></label>
        <label>Možnosti (výběr, po jedné na řádek)<textarea name="options_text" rows="3" placeholder="ls&#10;cd&#10;pwd"></textarea></label>
        <label>Správná odpověď<input name="answer" maxlength="120" required></label>
        <label>Vysvětlení (nepovinné)<input name="explain" maxlength="300"></label>
        <div class="tg58-actions"><button class="btn secondary" type="submit">Přidat otázku</button></div>
      </form>
      <?php if ($items !== []): ?>
      <div class="tg58-table-wrap"><table class="tg58-table"><thead><tr><th scope="col">Otázka</th><th scope="col">Kategorie</th><th scope="col">Linie</th><th scope="col"></th></tr></thead><tbody>
        <?php foreach ($items as $it): ?><tr><td><?= tg58_h((string)$it['prompt']) ?></td><td><?= tg58_h((string)$it['category']) ?></td><td><?= tg58_h((string)$it['line']) ?></td><td><form method="post"><?= tg58_hidden($csrf, 'tg58_bank_delete', $classId) ?><input type="hidden" name="bank_id" value="<?= tg58_h((string)$it['id']) ?>"><button class="link-button danger" type="submit">Smazat</button></form></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <?php endif; ?>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// POST akce a GET polling
// ---------------------------------------------------------------------------

function tg58_options_from_text(string $text): array
{
    return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', $text))), static fn(string $l): bool => $l !== ''));
}

function tg58_teacher_handle_post(string $action, array $modules): void
{
    $classIds = array_map('strval', array_keys($modules));
    $classId = is_string($_POST['class_id'] ?? null) ? $_POST['class_id'] : '';
    $gameId = is_string($_POST['game'] ?? null) ? $_POST['game'] : '';
    $now = tg58_now();
    $done = static function (string $message, string $cid, string $gid = ''): never {
        if (function_exists('teacher_flash')) teacher_flash($message);
        $params = ['tab' => 'hry', 'class' => $cid];
        if ($gid !== '') $params['game'] = $gid;
        if (function_exists('teacher_redirect')) teacher_redirect($params);
        exit;
    };
    if (!in_array($classId, $classIds, true) && $action !== 'tg58_bank_delete') throw new RuntimeException('Neplatná třída.');

    switch ($action) {
        case 'tg58_create':
            $session = tg58_create_session($_POST, $classIds, $now);
            $done('Hra „' . $session['title'] . '“ je připravená v lobby. Spusť ji, až bude třída připravená.', $classId, (string)$session['id']);
        case 'tg58_start':
            $session = tg58_start_session((string)tg58_owned_session($gameId, $classId)['id'], $now);
            $done('Hra „' . $session['title'] . '“ běží! Týmy: ' . count($session['teams']) . '.', $classId, (string)$session['id']);
        case 'tg58_pause':
            $session = tg58_pause_session((string)tg58_owned_session($gameId, $classId)['id'], $now);
            $done('Hra je pozastavená.', $classId, (string)$session['id']);
        case 'tg58_resume':
            $session = tg58_resume_session((string)tg58_owned_session($gameId, $classId)['id'], $now);
            $done('Hra pokračuje.', $classId, (string)$session['id']);
        case 'tg58_end':
            $session = tg58_end_session((string)tg58_owned_session($gameId, $classId)['id'], $now);
            $done('Hra „' . $session['title'] . '“ je ukončená. Výsledky vidí žáci hned.', $classId, (string)$session['id']);
        case 'tg58_archive':
            $session = tg58_archive_session((string)tg58_owned_session($gameId, $classId)['id']);
            $done('Hra byla archivována.', $classId);
        case 'tg58_delete':
            $title = (string)tg58_owned_session($gameId, $classId)['title'];
            tg58_delete_session($gameId);
            $done('Hra „' . $title . '“ byla smazána.', $classId);
        case 'tg58_bank_add':
            if (!tg58_bank_line_in_scope(is_string($_POST['line'] ?? null) ? $_POST['line'] : '')) throw new RuntimeException('Otázky tohoto předmětu nemůžete přidávat.');
            $in = $_POST;
            $in['options'] = tg58_options_from_text((string)($_POST['options_text'] ?? ''));
            tg58_bank_add_question($in, $now);
            $done('Otázka byla přidána do banky.', $classId);
        case 'tg58_bank_delete':
            $bankItem = null;
            foreach (tg58_quiz_teacher_bank() as $it) { if ((string)($it['id'] ?? '') === (string)($_POST['bank_id'] ?? '')) { $bankItem = $it; break; } }
            if ($bankItem !== null && !tg58_bank_line_in_scope((string)($bankItem['line'] ?? ''))) throw new RuntimeException('Otázka nebyla nalezena.');
            tg58_bank_delete_question((string)($_POST['bank_id'] ?? ''));
            $done('Otázka byla smazána z banky.', $classId !== '' ? $classId : (string)array_key_first($modules));
        default:
            throw new RuntimeException('Neznámá akce týmových her.');
    }
}

/**
 * GET teacher.php?tab=hry&tg_poll=1&game=<id>&v=<verze>[&projektor=1].
 * `&projektor=1` (nastavuje jen tg58_render_projector) přepne na redigovaná data projektoru
 * (tg58_projector_view – bez jednotlivých jmen a bez detailů typu penalizace/nápovědy/role) – panel
 * učitele a promítání sdílejí stejný GET podle teacher_v58.php, ale nikdy stejná data.
 */
function tg58_teacher_poll(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    $gameId = is_string($_GET['game'] ?? null) ? $_GET['game'] : '';
    $since = is_string($_GET['v'] ?? null) ? $_GET['v'] : '';
    $isProjector = ($_GET['projektor'] ?? '') !== '';
    $session = tg58_get($gameId);
    if ($session === null) { tg58_json_out(['ok' => false, 'error' => 'Hra nebyla nalezena.'], 404); return; }
    if (!tg58_session_in_scope($session)) { tg58_json_out(['ok' => false, 'error' => 'K této hře nemáte přístup.'], 403); return; } // v59
    $now = tg58_now();
    $view = $isProjector ? tg58_projector_view($session, $now) : tg58_teacher_view($session, $now);
    $version = tg58_version_of($view);
    tg58_json_out($since === $version ? ['ok' => true, 'changed' => false, 'version' => $version] : ['ok' => true, 'changed' => true, 'version' => $version] + $view);
}
