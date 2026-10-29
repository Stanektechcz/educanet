<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Robotí liga – pohledy: stránka žáka (?view=roboti), učitelská záložka (teacher.php?tab=roboti)
 * a projektor. Přehrávač, editor a živé aktualizace obstará assets/robots-v58.js (jen DOM API, žádné innerHTML).
 * Stránky si samy načítají své CSS/JS, takže integrace potřebuje jen routu.
 */

require_once __DIR__ . '/robots_v58.php';

const ROBOTS58_ASSET_V = '58.0';

function robots58_h(string $value): string
{
    return function_exists('e') ? e($value) : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** JSON pro <script type="application/json"> – znaky < > & ' " jsou escapované, takže nejde „utéct“ ze značky. */
function robots58_json_block(string $attr, array $data): string
{
    $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    return '<script type="application/json" ' . $attr . '>' . ($json === false ? '{}' : $json) . '</script>';
}

function robots58_assets(bool $js = true): void
{
    // v68: v cockpitu učitele tokenizovaná kopie (tmavý režim), jinak originál.
    echo '<link rel="stylesheet" href="' . htmlspecialchars(function_exists('edu_css_href') ? edu_css_href('assets/robots-v58.css', 'assets/robots-v58.css?v=' . ROBOTS58_ASSET_V) : 'assets/robots-v58.css?v=' . ROBOTS58_ASSET_V, ENT_QUOTES) . '">';
    if ($js) echo '<script src="assets/robots-v58.js?v=' . ROBOTS58_ASSET_V . '" defer></script>';
}

function robots58_status_label(string $status): string
{
    return match ($status) { 'finished' => tr('Odehráno'), 'closed' => tr('Uzávěrka – čeká na simulaci'), default => tr('Příprava – odevzdávej') };
}

function robots58_names_label(string $mode): string
{
    return match ($mode) { 'full' => tr('celá jména'), 'anon' => tr('anonymně'), default => tr('jméno + iniciála') };
}

function robots58_time_tag(?string $iso, string $format = 'j. n. H:i'): string
{
    $ts = robots58_ts($iso);
    return $ts === null ? '<time>–</time>' : '<time datetime="' . robots58_h(date(DATE_ATOM, $ts)) . '">' . robots58_h(date($format, $ts)) . '</time>';
}

/** Kostra přehrávače; data dodá JS (zkušební replay z API nebo <script type="application/json">). */
function robots58_render_player(string $id, string $extraClass = ''): void
{
    ?>
    <div class="rb58-player <?= robots58_h($extraClass) ?>" data-rb58-player id="<?= robots58_h($id) ?>" hidden>
      <p class="rb58-player-title" data-rb58-title></p>
      <div class="rb58-stage" tabindex="0" role="group" aria-label="<?= robots58_h(tr('Mapa zápasu')) ?>" data-rb58-stage aria-describedby="<?= robots58_h($id) ?>-keys">
        <svg class="rb58-svg" data-rb58-svg viewBox="0 0 480 320" role="img" aria-label="<?= robots58_h(tr('Mapa datacentra s roboty. Stejné informace jsou v tabulce a výpisu tahů pod mapou.')) ?>" focusable="false"></svg>
      </div>
      <div class="rb58-controls" role="group" aria-label="<?= robots58_h(tr('Ovládání přehrávání')) ?>">
        <button type="button" class="rb58-btn is-ghost" data-rb58-ctl="start"><?= robots58_h(tr('Začátek')) ?></button>
        <button type="button" class="rb58-btn is-ghost" data-rb58-ctl="back" aria-label="<?= robots58_h(tr('O tah zpět')) ?>">−1</button>
        <button type="button" class="rb58-btn is-primary" data-rb58-ctl="play" aria-pressed="false"><?= robots58_h(tr('Přehrát')) ?></button>
        <button type="button" class="rb58-btn is-ghost" data-rb58-ctl="fwd" aria-label="<?= robots58_h(tr('O tah vpřed')) ?>">+1</button>
        <button type="button" class="rb58-btn is-ghost" data-rb58-ctl="end"><?= robots58_h(tr('Konec')) ?></button>
        <label class="rb58-slider-label"><span><?= robots58_h(tr('Tah')) ?></span><input type="range" min="0" max="1" value="0" step="1" data-rb58-slider></label>
        <output class="rb58-turn" data-rb58-turn aria-live="off">0</output>
        <label class="rb58-speed"><span><?= robots58_h(tr('Rychlost')) ?></span><select data-rb58-speed><option value="1">1×</option><option value="2" selected>2×</option><option value="4">4×</option><option value="8">8×</option></select></label>
      </div>
      <p class="rb58-muted small" id="<?= robots58_h($id) ?>-keys"><?= robots58_h(tr('Klávesy na mapě: mezerník přehrát/pauza, šipky ← → o tah, Home a End na začátek a konec.')) ?></p>
      <p class="rb58-live" data-rb58-live aria-live="polite"></p>
      <div class="rb58-table-wrap"><table class="rb58-table rb58-score" data-rb58-score><caption><?= robots58_h(tr('Stav robotů v aktuálním tahu')) ?></caption><thead><tr><th scope="col"><?= robots58_h(tr('Č.')) ?></th><th scope="col"><?= robots58_h(tr('Robot')) ?></th><th scope="col"><?= robots58_h(tr('Tým')) ?></th><th scope="col"><?= robots58_h(tr('Body')) ?></th><th scope="col"><?= robots58_h(tr('Energie')) ?></th><th scope="col"><?= robots58_h(tr('Náklad')) ?></th><th scope="col"><?= robots58_h(tr('Poslední akce')) ?></th></tr></thead><tbody></tbody></table></div>
      <ul class="rb58-legend" aria-label="<?= robots58_h(tr('Legenda mapy')) ?>">
        <li><i class="rb58-key is-wall"></i><?= robots58_h(tr('regál (zeď)')) ?></li><li><i class="rb58-key is-base">Z</i><?= robots58_h(tr('základna (roh)')) ?></li><li><i class="rb58-key is-charger">+</i><?= robots58_h(tr('nabíječka')) ?></li>
        <li><i class="rb58-key is-packet"></i><?= robots58_h(tr('datový balíček')) ?></li><li><i class="rb58-key is-node">!</i><?= robots58_h(tr('rozbitý uzel (číslo = zbývající opravy)')) ?></li><li><i class="rb58-key is-robot">1</i><?= robots58_h(tr('robot (tvůj má černý rámeček a nápis TY)')) ?></li>
      </ul>
      <div class="rb58-stats" data-rb58-stats></div>
      <details class="rb58-log" data-rb58-logbox><summary><?= robots58_h(tr('Textový výpis tahů')) ?></summary>
        <label class="rb58-check"><input type="checkbox" data-rb58-logmine> <?= robots58_h(tr('Jen můj robot')) ?></label>
        <ol data-rb58-log></ol>
      </details>
    </div>
    <?php
}

/** $caption je hotové (escapované) HTML – volající si sám ošetří překlad i případný obsahový lang="cs" (edu_cs/tr_html). */
function robots58_render_results_table(array $public, string $caption, bool $teams): void
{
    $rows = $teams ? $public['teams'] : $public['robots'];
    if ($rows === []) { echo '<p class="rb58-muted">' . robots58_h(tr('Zatím bez výsledků.')) . '</p>'; return; }
    ?>
    <div class="rb58-table-wrap"><table class="rb58-table">
      <caption><?= $caption ?></caption>
      <thead><tr><th scope="col"><?= robots58_h(tr('Místo')) ?></th><th scope="col"><?= robots58_h($teams ? tr('Tým') : tr('Robot')) ?></th><?php if (!$teams): ?><th scope="col"><?= robots58_h(tr('Tým')) ?></th><?php endif; ?><th scope="col"><?= robots58_h(tr('Body')) ?></th><th scope="col"><?= robots58_h($teams ? tr('Robotů') : tr('Doručeno / opravy')) ?></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
        <tr<?= !empty($row['me']) ? ' class="is-me"' : '' ?>><td><?= (int)$row['rank'] ?>.</td><th scope="row"><?= edu_cs((string)$row['name']) ?><?= !empty($row['me']) ? ' <small>' . robots58_h(tr('(ty)')) . '</small>' : '' ?></th><?php if (!$teams): ?><td><?= (string)($row['team'] ?? '') !== '' ? edu_cs((string)$row['team']) : '–' ?></td><?php endif; ?><td><?= (int)$row['points'] ?></td><td><?= $teams ? (int)$row['robots'] . ' / ' . (int)$row['size'] : (int)$row['delivered'] . ' / ' . (int)$row['repairs'] ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php
}

function robots58_render_league(array $league, bool $teacher = false): void
{
    ?>
    <section class="rb58-card" aria-labelledby="rb58-league-h" data-rb58-league>
      <h2 id="rb58-league-h"><?= tr_html('Liga · {label}', ['label' => e((string)$league['label'])]) ?></h2>
      <p class="rb58-muted small"><?= robots58_h(tr('Ligové body za každý zápas: 1. místo 10, 2. místo 7, 3. místo 5, každá další účast 3 (v týmech rozhoduje umístění týmu). Při shodě rozhodují body robota.')) ?></p>
      <?php if ($league['rows'] === []): ?><p class="rb58-muted" data-rb58-league-empty><?= robots58_h(tr('V tomhle pololetí se ještě nehrálo.')) ?></p><?php else: ?>
      <div class="rb58-table-wrap"><table class="rb58-table" data-rb58-league-table>
        <caption class="rb58-sr"><?= robots58_h(tr('Ligová tabulka')) ?></caption>
        <thead><tr><th scope="col"><?= robots58_h(tr('Pořadí')) ?></th><th scope="col"><?= robots58_h(tr('Hráč')) ?></th><th scope="col"><?= robots58_h(tr('Zápasy')) ?></th><th scope="col"><?= robots58_h(tr('Výhry')) ?></th><th scope="col"><?= robots58_h(tr('Ligové body')) ?></th><th scope="col"><?= robots58_h(tr('Body robotů')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($league['rows'] as $row): ?>
          <tr<?= !empty($row['me']) ? ' class="is-me"' : '' ?>><td><?= (int)$row['rank'] ?>.</td><th scope="row"><?= edu_cs((string)$row['name']) ?><?= !empty($row['me']) && !$teacher ? ' <small>' . robots58_h(tr('(ty)')) . '</small>' : '' ?></th><td><?= (int)$row['matches'] ?></td><td><?= (int)$row['wins'] ?></td><td><?= (int)$row['league'] ?></td><td><?= (int)$row['points'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </section>
    <?php
}

function robots58_render_cheatsheet(): void
{
    ?>
    <section class="rb58-card rb58-cheat" aria-labelledby="rb58-cheat-h"<?= edu_content_lang_attr() ?>>
      <h2 id="rb58-cheat-h">Tahák RoboScriptu</h2>
      <p>Skript běží <strong>každý tah znovu odshora</strong>, dokud robot neprovede jednu <strong>akci</strong>. Bloky se dělají odsazením pod řádkem s dvojtečkou (jako v Pythonu). Klíčová slova jsou anglicky – stejně jako v Pythonu, JavaScriptu i v Linuxu.</p>
      <div class="rb58-cheat-grid">
        <div><h3>Akce (ukončí tah)</h3><ul>
          <li><code>move up</code> / <code>down</code> / <code>left</code> / <code>right</code> – o políčko (1 energie)</li>
          <li><code>step_to cil</code> – krok nejkratší cestou k pozici</li>
          <li><code>pick</code> – zvedni balíček (náklad max 3)</li>
          <li><code>drop</code> – odevzdej náklad na své základně (+3 b za balíček)</li>
          <li><code>repair</code> – oprav uzel, na kterém stojíš (+2 b, 2 energie, uzel potřebuje 3 opravy)</li>
          <li><code>charge</code> – nabij se na nabíječce (+20)</li>
          <li><code>wait</code> – čekej</li>
        </ul><p><code>say "Ahoj"</code> tah neukončí – bublina max 40 znaků, hlídá ji filtr slušnosti.</p></div>
        <div><h3>Senzory</h3><ul>
          <li><code>pos</code> (<code>pos.x</code>, <code>pos.y</code>), <code>energy</code> 0–100, <code>cargo</code>, <code>max_cargo</code></li>
          <li><code>here</code> – "packet", "node", "charger", "base" nebo "empty"</li>
          <li><code>turn</code>, <code>turns_left</code>, <code>score</code></li>
          <li><code>nearest("packet")</code> – nejbližší pozice, nebo <code>none</code> (také "node", "charger", "base")</li>
          <li><code>distance(cil)</code> – kroků k cíli (−1 = nedosažitelné)</li>
          <li><code>look(up)</code> – soused: "wall", "edge", "robot", "packet", "node", "charger", "base", "other_base", "empty"</li>
          <li><code>count("packet")</code>, <code>random(6)</code>, <code>abs</code>, <code>min</code>, <code>max</code>, <code>point(x, y)</code></li>
        </ul></div>
        <div><h3>Řízení a proměnné</h3><ul>
          <li><code>if</code> / <code>elif</code> / <code>else</code>, <code>while</code>, <code>repeat 3:</code>, <code>break</code>, <code>continue</code>, <code>pass</code></li>
          <li><code>def jmeno(a):</code> … <code>return a + 1</code> (hloubka volání max 8)</li>
          <li><code>and</code>, <code>or</code>, <code>not</code>, <code>== != &lt; &lt;= &gt; &gt;=</code>, <code>+ - * / %</code> (celá čísla), <code># komentář</code></li>
          <li><code>keep cesty = 0</code> – paměť robota, přežije do dalšího tahu (max 16)</li>
        </ul></div>
        <div><h3>Pravidla a limity</h3><ul>
          <li>Skript max 4 KB, 500 kroků na tah. 3× po sobě přes limit = 10 tahů chlazení.</li>
          <li>Robot, který 3 tahy stojí, je průhledný – nikoho neblokuje.</li>
          <li>Do cizí základny se nevjede, roboti se nemůžou poškodit ani krást.</li>
          <li>Pořadí robotů se každý tah férově losuje ze semínka zápasu.</li>
          <li>Pod 5 energie se robot, který v tahu nic nespotřebuje, dobije o 1.</li>
        </ul></div>
      </div>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Žák: ?view=roboti
// ---------------------------------------------------------------------------

function robots58_render_student(string $classId, array $module, string $flash = ''): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    robots58_claim_xp($classId, $studentKey);
    $now = robots58_now();
    $state = robots58_student_state($classId, $studentKey, $now);
    $draft = $studentKey !== '' ? robots58_draft($classId, $studentKey) : null;
    $examples = robots58_examples();
    $code = $draft['code'] ?? $examples['start']['text'];
    $open = array_values(array_filter($state['matches'], static fn(array $m): bool => $m['status'] === 'open'));
    $shell = function_exists('render_header') && function_exists('render_footer');
    if ($shell) render_header(tr('Robotí liga'), $module); else robots58_page_open(tr('Robotí liga'));
    robots58_assets();
    ?>
    <div class="rb58 rb58-student" data-rb58-student data-api="robots_v58_api.php" data-csrf="<?= robots58_h(function_exists('csrf_token') ? csrf_token() : '') ?>" data-version="<?= robots58_h($state['version']) ?>">
      <header class="rb58-head">
        <span class="rb58-kicker"><?= robots58_h(tr('Linux Lab · Robotí liga')) ?></span>
        <h1><?= robots58_h(tr('Robotí liga')) ?></h1>
        <p><?= robots58_h(tr('Naprogramuj robota v datacentru. Každý tah se tvůj skript spustí znovu odshora a robot udělá jednu akci: popojede, zvedne datový balíček, odveze ho na základnu, opraví uzel nebo se nabije. Soutěží se sběrem a opravami – roboti si nemůžou ublížit ani nic ukrást.')) ?></p>
      </header>
      <?php if ($flash !== ''): ?><div class="rb58-notice" role="status"><?= robots58_h($flash) ?></div><?php endif; ?>
      <div class="rb58-layout">
        <section class="rb58-card rb58-editor-card" aria-labelledby="rb58-editor-h">
          <h2 id="rb58-editor-h"><?= robots58_h(tr('Tvůj skript')) ?></h2>
          <div class="rb58-toolbar">
            <label for="rb58-example"><?= robots58_h(tr('Ukázka')) ?></label>
            <select id="rb58-example" data-rb58-example><?php foreach ($examples as $id => $ex): ?><option value="<?= robots58_h($id) ?>"<?= edu_content_lang_attr() ?>><?= robots58_h($ex['title']) ?></option><?php endforeach; ?></select>
            <button type="button" class="rb58-btn is-ghost" data-rb58-load-example><?= robots58_h(tr('Načíst ukázku')) ?></button>
          </div>
          <div class="rb58-editor">
            <pre class="rb58-gutter" aria-hidden="true" data-rb58-gutter>1</pre>
            <label for="rb58-code" class="rb58-sr"><?= robots58_h(tr('Skript v jazyce RoboScript')) ?></label>
            <textarea id="rb58-code" data-rb58-code spellcheck="false" autocapitalize="off" autocomplete="off" wrap="off" rows="18" aria-describedby="rb58-code-help rb58-bytes"><?= robots58_h($code) ?></textarea>
          </div>
          <p id="rb58-code-help" class="rb58-muted small"><?= robots58_h(tr('Tab odsadí o 4 mezery, Shift+Tab odsazení vrátí. Chceš-li editor opustit klávesnicí, stiskni Esc a potom Tab.')) ?></p>
          <p class="rb58-muted small" id="rb58-bytes" data-rb58-bytes><?= strlen($code) ?> / <?= ROBOTS58_MAX_BYTES ?> B</p>
          <?php if ($draft !== null): ?><p class="rb58-muted small"><?= tr_html('Koncept uložen {time}.', ['time' => robots58_time_tag((string)$draft['at'])]) ?></p><?php endif; ?>
          <fieldset class="rb58-options">
            <legend><?= robots58_h(tr('Trénink')) ?></legend>
            <label><?= robots58_h(tr('Mapa')) ?> <select data-rb58-map><?php for ($i = 1; $i <= ROBOTS58_TRAINING_MAPS; $i++): ?><option value="<?= $i ?>"><?= robots58_h(tr('č. {n}', ['n' => $i])) ?></option><?php endfor; ?></select></label>
            <label><?= robots58_h(tr('Tahů')) ?> <select data-rb58-turns><?php foreach (ROBOTS58_TRAINING_TURNS as $t): ?><option value="<?= $t ?>"<?= $t === 150 ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
            <label class="rb58-check"><input type="checkbox" data-rb58-sparring checked> <?= robots58_h(tr('Cvičný soupeř')) ?></label>
          </fieldset>
          <div class="rb58-actions">
            <button type="button" class="rb58-btn" data-rb58-op="validate"><?= robots58_h(tr('Zkontrolovat')) ?></button>
            <button type="button" class="rb58-btn is-primary" data-rb58-op="test"><?= robots58_h(tr('Otestovat')) ?></button>
            <button type="button" class="rb58-btn is-ghost" data-rb58-op="save"><?= robots58_h(tr('Uložit koncept')) ?></button>
          </div>
          <div class="rb58-submit" data-rb58-submit-box<?= $open === [] ? ' hidden' : '' ?>>
            <label for="rb58-match"><?= robots58_h(tr('Zápas')) ?></label>
            <select id="rb58-match" data-rb58-match><?php foreach ($open as $m): ?><option value="<?= robots58_h($m['id']) ?>"<?= edu_content_lang_attr() ?>><?= robots58_h(tr('{title} (do {deadline})', ['title' => $m['title'], 'deadline' => date('j. n. H:i', (int)robots58_ts($m['deadline']))])) ?></option><?php endforeach; ?></select>
            <button type="button" class="rb58-btn is-primary" data-rb58-op="submit"><?= robots58_h(tr('Odevzdat do zápasu')) ?></button>
          </div>
          <p class="rb58-muted small" data-rb58-no-open<?= $open !== [] ? ' hidden' : '' ?>><?= robots58_h(tr('Teď se nepřipravuje žádný zápas – trénuj, učitel ho brzy vypíše.')) ?></p>
          <div class="rb58-out" role="status" aria-live="polite" data-rb58-out></div>
          <ul class="rb58-errors" data-rb58-errors></ul>
        </section>
        <section class="rb58-card rb58-replay-card" aria-labelledby="rb58-replay-h">
          <h2 id="rb58-replay-h"><?= robots58_h(tr('Přehrávání')) ?></h2>
          <p class="rb58-muted" data-rb58-player-empty><?= tr_html('Stiskni {btn} a uvidíš, co tvůj robot udělá. Odehrané zápasy si pustíš níže.', ['btn' => '<strong>' . robots58_h(tr('Otestovat')) . '</strong>']) ?></p>
          <?php robots58_render_player('rb58-player-student'); ?>
        </section>
      </div>
      <section class="rb58-card" aria-labelledby="rb58-matches-h">
        <h2 id="rb58-matches-h"><?= robots58_h(tr('Zápasy třídy')) ?></h2>
        <div data-rb58-matches><?php robots58_render_student_matches($state['matches']); ?></div>
      </section>
      <?php robots58_render_league($state['league']); ?>
      <?php robots58_render_cheatsheet(); ?>
      <?= robots58_json_block('data-rb58-examples', array_map(static fn(array $ex): string => $ex['text'], $examples)) ?>
    </div>
    <?php
    if ($shell) render_footer(); else robots58_page_close();
}

function robots58_render_student_matches(array $matches): void
{
    if ($matches === []) { echo '<p class="rb58-muted">' . robots58_h(tr('Zatím žádný zápas. Mezitím trénuj – učitel zápas vypíše, až bude třída připravená.')) . '</p>'; return; }
    echo '<ul class="rb58-matches">';
    foreach ($matches as $m) {
        ?>
        <li class="rb58-match is-<?= robots58_h($m['status']) ?>">
          <div class="rb58-match-head"><h3><?= edu_cs((string)$m['title']) ?></h3><span class="rb58-badge is-<?= robots58_h($m['status']) ?>"><?= robots58_h(robots58_status_label($m['status'])) ?></span></div>
          <p class="rb58-muted small"><?= robots58_h($m['mode'] === 'teams' ? tr('Týmy') : tr('Každý sám za sebe')) ?> · <?= robots58_h(tr('{n} tahů', ['n' => (int)$m['turns']])) ?> · <?= tr_html('uzávěrka {deadline}', ['deadline' => robots58_time_tag($m['deadline'])]) ?> · <?= robots58_h(tr('odevzdáno {n}', ['n' => (int)$m['submitted']])) ?></p>
          <?php if ($m['team'] !== null): ?><p><?= tr_html('Tvůj tým: {name}', ['name' => '<strong>' . edu_cs((string)$m['team']['name']) . '</strong>']) ?><?= $m['team']['mates'] !== [] ? ' – ' . edu_cs(implode(', ', $m['team']['mates'])) : ' ' . robots58_h(tr('({n} hráčů)', ['n' => (int)$m['team']['size']])) ?></p><?php endif; ?>
          <p><?php if ($m['status'] === 'finished') { echo robots58_h($m['played'] ? tr('Tvůj robot v zápase hrál.') : tr('Do tohohle zápasu jsi neodevzdal(a).')); } elseif ($m['my'] !== null) { echo tr_html('Tvoje odevzdání: {time} (pokus {n})', ['time' => robots58_time_tag($m['my']['at']), 'n' => (int)$m['my']['n']]); } else { echo robots58_h($m['status'] === 'open' ? tr('Zatím jsi neodevzdal(a).') : tr('Do tohohle zápasu jsi neodevzdal(a).')); } ?></p>
          <?php if ($m['status'] === 'finished' && $m['results'] !== null): ?>
            <?php if ($m['place'] !== null): ?><p class="rb58-place"><?= tr_html('Tvoje umístění: {place}', ['place' => '<strong>' . robots58_h(tr('{n}. místo', ['n' => (int)$m['place']])) . '</strong>']) ?></p><?php endif; ?>
            <?php robots58_render_results_table($m['results'], tr_html('Výsledky – {title}', ['title' => edu_cs((string)$m['title'])]), $m['mode'] === 'teams'); ?>
            <button type="button" class="rb58-btn" data-rb58-replay="<?= robots58_h($m['id']) ?>"><?= robots58_h(tr('Přehrát zápas')) ?></button>
          <?php endif; ?>
        </li>
        <?php
    }
    echo '</ul>';
}

function robots58_page_open(string $title): void
{
    echo '<!doctype html><html lang="' . e(edu_html_lang()) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . robots58_h($title) . ' · EDUCANET</title><script src="assets/i18n-v58.js" defer></script>' . edu_tr_json_js() . '</head><body class="rb58-body"><main class="rb58-main">';
}

function robots58_page_close(): void
{
    echo '</main></body></html>';
}

// ---------------------------------------------------------------------------
// Učitel: teacher.php?tab=roboti
// ---------------------------------------------------------------------------

function robots58_hidden(string $csrf, string $action, string $classId, ?string $matchId = null): string
{
    return '<input type="hidden" name="csrf" value="' . $csrf . '"><input type="hidden" name="action" value="' . robots58_h($action) . '"><input type="hidden" name="class_id" value="' . robots58_h($classId) . '"><input type="hidden" name="return_tab" value="roboti">' . ($matchId !== null ? '<input type="hidden" name="match" value="' . robots58_h($matchId) . '">' : '');
}

function robots58_render_teacher_tab(array $modules, string $classId): void
{
    if (!isset($modules[$classId]) && $modules !== []) $classId = (string)array_key_first($modules);
    $now = robots58_now();
    $matches = robots58_matches_for_class($classId);
    $wanted = is_string($_GET['match'] ?? null) ? $_GET['match'] : '';
    $selected = null;
    foreach ($matches as $m) { if ($m['id'] === $wanted) $selected = $m; }
    $selected ??= $matches[0] ?? null;
    $csrf = robots58_h(function_exists('csrf_token') ? csrf_token() : '');
    robots58_assets();
    ?>
    <div class="rb58 rb58-teacher" data-rb58-teacher>
      <header class="rb58-head">
        <span class="rb58-kicker">Linux Lab · Robotí liga</span>
        <h1>Programovatelná aréna</h1>
        <p>Žáci píšou krátké skripty, které po tazích řídí robota v simulovaném datacentru. Zápas vypíšeš s uzávěrkou, žáci odevzdávají (platí poslední platný skript), pak spustíš simulaci a záznam pustíš na projektor. Nic se nespouští doopravdy – skripty interpretuje EDUCANET.</p>
      </header>
      <nav class="rb58-classes" aria-label="Třída">
        <?php foreach (array_keys($modules) as $cid): $cid = (string)$cid; ?>
          <a class="<?= $cid === $classId ? 'active' : '' ?>" href="<?= robots58_h('teacher.php?tab=roboti&class=' . rawurlencode($cid)) ?>"<?= $cid === $classId ? ' aria-current="page"' : '' ?>><?= robots58_h(function_exists('teacher_class_label') ? teacher_class_label($cid) : $cid) ?></a>
        <?php endforeach; ?>
      </nav>
      <?php if ($selected !== null) robots58_render_match_panel($selected, $now, $csrf); ?>
      <div class="rb58-teacher-grid">
        <?php robots58_render_create_form($classId, $csrf, $now); ?>
        <section class="rb58-card" aria-labelledby="rb58-list-h">
          <h2 id="rb58-list-h">Zápasy třídy</h2>
          <?php if ($matches === []): ?><p class="rb58-muted">Zatím žádný zápas.</p><?php else: ?>
          <ul class="rb58-list"><?php foreach ($matches as $m): $st = robots58_status($m, $now); ?>
            <li<?= $selected !== null && $m['id'] === $selected['id'] ? ' class="is-current"' : '' ?>><a href="<?= robots58_h('teacher.php?tab=roboti&class=' . rawurlencode($classId) . '&match=' . rawurlencode($m['id'])) ?>"><?= robots58_h($m['title']) ?></a> <span class="rb58-badge is-<?= robots58_h($st) ?>"><?= robots58_h(robots58_status_label($st)) ?></span> <small class="rb58-muted"><?= robots58_time_tag($m['created_at'], 'j. n.') ?></small></li>
          <?php endforeach; ?></ul>
          <?php endif; ?>
        </section>
      </div>
      <?php robots58_render_league(robots58_public_league($classId, robots58_semester($now), null, true), true); ?>
      <?php robots58_render_cheatsheet(); ?>
    </div>
    <?php
}

function robots58_render_match_panel(array $m, int $now, string $csrf): void
{
    $status = robots58_status($m, $now);
    $subs = robots58_teacher_submissions($m);
    $submitted = count(array_filter($subs, static fn(array $r): bool => $r['at'] !== null));
    $poll = robots58_poll_status($m, $now);
    $classId = (string)$m['class_id'];
    $roster = robots58_roster($classId);
    $labels = array_map(static fn(array $sub): string => (string)($sub['label'] ?? ''), robots58_submissions((string)$m['id']));
    ?>
    <section class="rb58-card rb58-panel" aria-labelledby="rb58-panel-h" data-rb58-poll="<?= robots58_h('teacher.php?tab=roboti&robots_poll=1&match=' . rawurlencode($m['id'])) ?>" data-version="<?= robots58_h($poll['version']) ?>" data-status="<?= robots58_h($status) ?>">
      <div class="rb58-match-head"><h2 id="rb58-panel-h"><?= robots58_h($m['title']) ?></h2><span class="rb58-badge is-<?= robots58_h($status) ?>" data-rb58-status><?= robots58_h(robots58_status_label($status)) ?></span></div>
      <p class="rb58-muted"><?= $m['mode'] === 'teams' ? 'Týmy (' . count($m['teams']) . ')' : 'Sólo' ?> · <?= (int)$m['turns'] ?> tahů · uzávěrka <?= robots58_time_tag($m['deadline']) ?> · jména na projektoru: <?= robots58_h(robots58_names_label((string)$m['names'])) ?> · odevzdáno <strong data-rb58-submitted><?= $submitted ?></strong> / <?= count($subs) ?></p>
      <p class="rb58-live" data-rb58-teacher-live aria-live="polite"></p>
      <div class="rb58-actions">
        <?php if ($status === 'open'): ?><form method="post" action="teacher.php"><?= robots58_hidden($csrf, 'robots58_close', $classId, $m['id']) ?><button class="rb58-btn">Uzavřít odevzdávání</button></form><?php endif; ?>
        <?php if ($status !== 'finished'): ?><form method="post" action="teacher.php"><?= robots58_hidden($csrf, 'robots58_run', $classId, $m['id']) ?><button class="rb58-btn is-primary">Spustit simulaci</button></form><?php endif; ?>
        <a class="rb58-btn is-ghost" href="<?= robots58_h('teacher.php?tab=roboti&projector=1&match=' . rawurlencode($m['id'])) ?>" target="_blank" rel="noopener">Projektor (nové okno)</a>
        <form method="post" action="teacher.php" data-rb58-confirm="Opravdu smazat zápas i se záznamem a odevzdanými skripty?"><?= robots58_hidden($csrf, 'robots58_delete', $classId, $m['id']) ?><button class="rb58-btn is-danger">Smazat</button></form>
      </div>
      <?php if ($m['mode'] === 'teams'): ?>
        <h3>Týmy</h3>
        <ul class="rb58-teams"><?php foreach ($m['teams'] as $t): ?><li><strong><?= robots58_h($t['name']) ?></strong>: <?= robots58_h(implode(', ', array_map(static fn($k): string => robots58_label((string)$k, $labels[(string)$k] ?? '', $roster), $t['members']))) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if ($status === 'finished'): $payload = robots58_replay_payload((string)$m['id'], null, true); ?>
        <h3>Výsledky</h3>
        <?php if ($m['mode'] === 'teams') robots58_render_results_table(robots58_public_results($m, null, true), robots58_h('Týmy'), true); ?>
        <?php robots58_render_results_table(robots58_public_results($m, null, true), robots58_h('Roboti (celá jména vidíš jen ty)'), false); ?>
        <?php if ($payload !== null): ?><?php robots58_render_player('rb58-player-teacher'); ?><?= robots58_json_block('data-rb58-replay-json', $payload) ?><?php endif; ?>
      <?php endif; ?>
      <details class="rb58-subs"><summary>Odevzdání žáků (<?= $submitted ?>)</summary>
        <div class="rb58-table-wrap"><table class="rb58-table"><caption class="rb58-sr">Stav odevzdání</caption>
          <thead><tr><th scope="col">Žák</th><?php if ($m['mode'] === 'teams'): ?><th scope="col">Tým</th><?php endif; ?><th scope="col">Odevzdáno</th><th scope="col">Pokusů</th><th scope="col">Velikost</th><th scope="col">Skript</th></tr></thead>
          <tbody><?php foreach ($subs as $row): ?><tr><th scope="row"><?= robots58_h($row['name']) ?></th><?php if ($m['mode'] === 'teams'): ?><td><?= robots58_h($row['team'] ?: '–') ?></td><?php endif; ?><td><?= $row['at'] !== null ? robots58_time_tag($row['at']) : 'neodevzdáno' ?></td><td><?= (int)$row['n'] ?></td><td><?= $row['at'] !== null ? (int)$row['bytes'] . ' B' : '–' ?></td>
            <td><?php if ($row['code'] !== null): ?><details><summary><?= $row['valid'] ? 'Zobrazit' : 'Zobrazit (chyba)' ?></summary><pre class="rb58-code-view"><?= robots58_h($row['code']) ?></pre></details><?php else: ?>–<?php endif; ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
        <p class="rb58-muted small">Kód vidíš jen ty – žáci cizí skripty nevidí ani po zápase.</p>
      </details>
    </section>
    <?php
}

function robots58_render_create_form(string $classId, string $csrf, int $now): void
{
    ?>
    <section class="rb58-card" aria-labelledby="rb58-create-h">
      <h2 id="rb58-create-h">Nový zápas</h2>
      <form method="post" action="teacher.php" class="rb58-form">
        <?= robots58_hidden($csrf, 'robots58_create', $classId) ?>
        <label>Název <input name="title" maxlength="60" value="Zápas robotů" required></label>
        <fieldset><legend>Režim</legend>
          <label class="rb58-check"><input type="radio" name="mode" value="solo" checked> Každý sám</label>
          <label class="rb58-check"><input type="radio" name="mode" value="teams"> Týmy (hadí draft podle Labu)</label>
        </fieldset>
        <label>Počet týmů <select name="teams"><option>2</option><option>3</option><option>4</option></select></label>
        <label>Tahů <select name="turns"><option>100</option><option>200</option><option selected>300</option><option>500</option></select></label>
        <label>Uzávěrka odevzdání <input type="datetime-local" name="deadline" required value="<?= robots58_h(date('Y-m-d\TH:i', $now + 45 * 60)) ?>"></label>
        <label>Jména na projektoru a u žáků <select name="names"><option value="initials" selected>jméno + iniciála</option><option value="full">celá jména</option><option value="anon">anonymně (Robot 1, 2…)</option></select></label>
        <button class="rb58-btn is-primary">Vypsat zápas</button>
      </form>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Projektor
// ---------------------------------------------------------------------------

function robots58_render_projector(string $matchId): void
{
    $now = robots58_now();
    $m = robots58_match($matchId);
    if (!headers_sent()) {
        if ($m === null) http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
    }
    $status = $m !== null ? robots58_status($m, $now) : 'missing';
    $payload = $status === 'finished' ? robots58_replay_payload($matchId, null) : null;
    $poll = $m !== null ? robots58_poll_status($m, $now) : null;
    ?><!doctype html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><meta name="color-scheme" content="dark">
<title><?= robots58_h($m !== null ? 'Projektor · ' . (string)$m['title'] : 'Projektor · Robotí liga') ?></title>
<?php robots58_assets(); ?></head>
<body class="rb58-projector-body">
<main class="rb58 rb58-projector"<?php if ($poll !== null): ?> data-rb58-poll="<?= robots58_h('teacher.php?tab=roboti&robots_poll=1&match=' . rawurlencode($matchId)) ?>" data-version="<?= robots58_h($poll['version']) ?>" data-status="<?= robots58_h($status) ?>" data-reload="1"<?php endif; ?>>
<?php if ($m === null): ?>
  <h1>Zápas nebyl nalezen</h1><p>Zavři kartu a otevři projektor znovu ze záložky Robotí liga.</p>
<?php else: ?>
  <header class="rb58-proj-head"><div><span class="rb58-kicker">Robotí liga · <?= robots58_h(robots58_status_label($status)) ?></span><h1><?= robots58_h((string)$m['title']) ?></h1></div></header>
  <?php if ($payload === null): ?>
    <section class="rb58-proj-wait" role="status"><p>Připravujeme roboty. Odevzdáno skriptů: <strong data-rb58-submitted><?= (int)$poll['submitted'] ?></strong></p><p>Uzávěrka <?= robots58_time_tag($m['deadline'], 'H:i') ?>. Jakmile učitel spustí simulaci, záznam se tu sám objeví.</p></section>
  <?php else: ?>
    <div class="rb58-proj-grid">
      <div><?php robots58_render_player('rb58-player-projector', 'is-projector'); ?></div>
      <div>
        <?php if ($m['mode'] === 'teams') robots58_render_results_table($payload['results'], robots58_h('Týmy'), true); ?>
        <?php robots58_render_results_table($payload['results'], robots58_h('Top ' . ROBOTS58_BOARD_TOP . ' robotů'), false); ?>
      </div>
    </div>
    <?= robots58_json_block('data-rb58-replay-json', ['replay' => $payload['replay'], 'results' => $payload['results']]) ?>
  <?php endif; ?>
  <footer class="rb58-proj-foot">Skripty interpretuje EDUCANET – nic se nespouští doopravdy. Roboti se nemůžou poškodit ani krást.</footer>
<?php endif; ?>
</main>
</body></html>
<?php
}
