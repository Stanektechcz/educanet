<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – studentské UI.
 *
 * Jen zobrazení. Veškerá logika (svět, kontrola úkolů, ukládání) žije v linux_v57_lab.php
 * a běží přes lab_v57_api.php – tady se nic nespouští, jen se staví HTML a formuláře,
 * které to JSON API volají. Terminál je vždy jen simulace v prohlížeči.
 */

// ---------------------------------------------------------------------------
// v58 · pomocníci (LABUI): assety, herní rozcestník, odznaky, opakování
// ---------------------------------------------------------------------------

/** Vloží CSS/JS pro v58 rozšíření terminálu (A11Y, „Vysvětli výstup“). Volej jednou na stránku s pracovní plochou. */
function lab57_render_v58_assets(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $ver = static fn(string $path, string $fallback): string => function_exists('asset_url') ? asset_url($path . '?v=' . $fallback) : $path . '?v=' . $fallback;
    echo '<link rel="stylesheet" href="' . e($ver('assets/lab-a11y-v58.css', '58.0')) . '">';
    echo '<script src="' . e($ver('assets/lab-a11y-v58.js', '58.0')) . '" defer></script>';
    echo '<script src="' . e($ver('assets/lab-explain-v58.js', '58.0')) . '" defer></script>';
}

/**
 * @return list<array{href:string,icon:string,title:string,desc:string}> další herní režimy.
 * Zjišťuje se přes is_file() na vlastnický soubor modulu (integrátorovo upřesnění), ne function_exists –
 * modul se má nabídnout, i když ho router ještě nenačetl v tomto konkrétním requestu.
 */
function lab57_v58_hub_items(): array
{
    $items = [];
    if (is_file(__DIR__ . '/arena_v58_views.php')) $items[] = ['href' => '?view=hadanka', 'icon' => '🧩', 'title' => trm('Týdenní hádanka'), 'desc' => trm('Nová výzva každé pondělí – vyhraje nejkratší řešení.')];
    if (is_file(__DIR__ . '/arena_v58_events_views.php')) {
        $items[] = ['href' => '?view=ctf', 'icon' => '🚩', 'title' => trm('CTF týden'), 'desc' => trm('Sezónní soutěž v kategoriích, body podle obtížnosti.')];
        $items[] = ['href' => '?view=incident', 'icon' => '🚨', 'title' => trm('Incidenty'), 'desc' => trm('Oprav problém na čas a napiš krátký postmortem.')];
    }
    if (is_file(__DIR__ . '/teamgames_v58_views.php')) $items[] = ['href' => '?view=hry', 'icon' => '🤝', 'title' => trm('Týmové hry'), 'desc' => trm('Spolupracujte na síti, webu i v kvízu.')];
    if (is_file(__DIR__ . '/robots_v58_views.php')) $items[] = ['href' => '?view=roboti', 'icon' => '🤖', 'title' => trm('Robotí liga'), 'desc' => trm('Naprogramuj robota a pošli ho do zápasu.')];
    if (is_file(__DIR__ . '/lab-offline.html')) $items[] = ['href' => 'lab-offline.html', 'icon' => '⇩', 'title' => trm('Offline trénink'), 'desc' => trm('Manuál a pískoviště i bez připojení k síti.')];
    return $items;
}

function lab57_render_hub_section(): void
{
    $items = lab57_v58_hub_items();
    if ($items === []) return;
    ?>
    <section class="lab57-hub" aria-label="<?= e(tr('Další výzvy a herní režimy')) ?>">
      <h2><?= e(tr('Další výzvy')) ?></h2>
      <div class="lab57-hub-grid">
        <?php foreach ($items as $item): $hubTitle = $item['title']; $hubDesc = $item['desc']; ?>
          <a class="lab57-hub-card" href="<?= e($item['href']) ?>">
            <span class="lab57-hub-icon" aria-hidden="true"><?= e($item['icon']) ?></span>
            <strong><?= e(tr($hubTitle)) ?></strong>
            <span><?= e(tr($hubDesc)) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
}

function lab57_render_badges_section(string $classId, string $studentKey): void
{
    if (!function_exists('lab58_badges')) return;
    $badges = lab58_badges($classId, $studentKey);
    if ($badges === []) return;
    ?>
    <div class="lab57-badges" aria-label="<?= e(tr('Odznaky za Linux dovednosti')) ?>">
      <?php foreach ($badges as $badge): $earned = !empty($badge['earned']); ?>
        <span class="lab57-badge<?= $earned ? ' is-earned' : '' ?>" title="<?= e((string)($badge['hint'] ?? '')) ?>">
          <span class="lab57-badge-icon" aria-hidden="true"><?= e((string)($badge['icon'] ?? '🏅')) ?></span>
          <?= e((string)($badge['title'] ?? '')) ?><?= $earned ? '' : ' (' . (int)round((float)($badge['progress'] ?? 0) * 100) . ' %)' ?>
        </span>
      <?php endforeach; ?>
    </div>
    <?php
}

function lab57_render_review_card(string $classId, string $studentKey): void
{
    if (!function_exists('lab58_review_today')) return;
    $review = lab58_review_today($classId, $studentKey);
    $items = (array)($review['items'] ?? []);
    if ($items === []) return;
    $left = count(array_filter($items, static fn(array $i): bool => empty($i['done'])));
    ?>
    <a class="lab57-review-card" href="?view=lab&amp;sekce=opakovani">
      <div>
        <strong><?= e(tr('Dnešní opakování')) ?></strong>
        <span><?= $left > 0 ? e(trn(['one' => '{n} úloha ke zopakování', 'few' => '{n} úlohy ke zopakování', 'other' => '{n} úloh ke zopakování'], $left)) : e(tr('Dnešní opakování je hotové')) ?> · <?= e(tr('řada {n} dní', ['n' => (int)($review['streak'] ?? 0)])) ?></span>
      </div>
      <span class="btn secondary small"><?= e(tr('Otevřít')) ?></span>
    </a>
    <?php
}

function lab57_plural_cs(int $n, string $one, string $few, string $many): string
{
    if ($n === 1) return $one;
    if ($n >= 2 && $n <= 4) return $few;
    return $many;
}

// ---------------------------------------------------------------------------
// Domovská stránka Labu
// ---------------------------------------------------------------------------

function lab57_render_home(string $classId, array $module, string $flash): void
{
    $studentKey = adaptive_student_key($classId);
    $solved = lab57_solved($classId, $studentKey, 'practice');
    $summary = lab57_student_summary($classId, $studentKey);
    $race = function_exists('arena57_live_for_class') ? arena57_live_for_class($classId) : null;
    $packs = function_exists('lab58_packs_for_class') ? lab58_packs_for_class($classId) : lab57_packs();

    render_header('Linux Lab', $module);
    ?>
    <div class="lab57 lab57-home">
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>

      <header class="lab57-hero">
        <span class="lab57-hero-badge">›_ Linux Lab</span>
        <h1><?= e(tr('Trénuj Linux terminál bez rizika')) ?></h1>
        <p><?= e(tr('Celý terminál je simulace přímo v prohlížeči – nic tu neběží na skutečném počítači, takže nejde nic pokazit. Zkoušej směle.')) ?></p>
        <div class="lab57-hero-stats">
          <div><b><?= (int)$summary['count'] ?></b><span><?= e(tr('vyřešených úloh')) ?></span></div>
          <div><b><?= (int)$summary['points'] ?></b><span><?= e(tr('bodů v Labu')) ?></span></div>
          <div><b><?= (int)$summary['cmds'] ?></b><span><?= e(tr('napsaných příkazů')) ?></span></div>
        </div>
      </header>

      <?php lab57_render_badges_section($classId, $studentKey); ?>
      <?php lab57_render_review_card($classId, $studentKey); ?>

      <?php if (is_array($race)): ?>
        <a class="lab57-race-card" href="?view=lab&amp;zavod=<?= rawurlencode((string)$race['id']) ?>">
          <div class="lab57-race-pulse" aria-hidden="true">●</div>
          <div>
            <strong><?= tr_html('Právě běží třídní závod: {nazev}', ['nazev' => (string)($race['title'] ?? '') !== '' ? edu_cs((string)$race['title']) : e(tr('Závod'))]) ?></strong>
            <span><?= e(tr('Klikni a zapoj se, dokud běží.')) ?></span>
          </div>
          <span class="btn primary"><?= e(tr('Vstoupit do závodu')) ?></span>
        </a>
      <?php endif; ?>

      <div class="lab57-packs">
        <?php foreach ($packs as $packId => $pack): $levels = lab57_pack_levels($packId); if ($levels === []) continue; $progress = lab57_pack_progress($packId, $solved); ?>
          <article class="lab57-pack lab57-tone-<?= e((string)$pack['tone']) ?>">
            <div class="lab57-pack-head">
              <span class="lab57-pack-icon" aria-hidden="true"><?= e((string)$pack['icon']) ?></span>
              <div>
                <h2><?= edu_cs((string)$pack['title']) ?></h2>
                <p><?= edu_cs((string)$pack['lead']) ?></p>
                <span class="lab57-pack-inspired"><?= edu_cs((string)$pack['inspired']) ?></span>
              </div>
            </div>
            <div class="lab57-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?= (int)$progress['total'] ?>" aria-valuenow="<?= (int)$progress['done'] ?>">
              <div class="lab57-progress-bar" style="width:<?= $progress['total'] > 0 ? round($progress['done'] / $progress['total'] * 100) : 0 ?>%"></div>
            </div>
            <div class="lab57-pack-foot">
              <span><?= e(tr('{done} / {total} hotovo', ['done' => (int)$progress['done'], 'total' => (int)$progress['total']])) ?></span>
              <?php if ($progress['next'] !== null): ?>
                <a class="btn primary small" href="?view=lab&amp;uroven=<?= rawurlencode((string)$progress['next']) ?>"><?= e(tr('Pokračovat')) ?></a>
              <?php else: ?>
                <span class="lab57-done-chip">✔ <?= e(tr('hotovo')) ?></span>
              <?php endif; ?>
            </div>
            <details class="lab57-pack-levels">
              <summary><?= e(tr('Úrovně balíčku ({n})', ['n' => count($levels)])) ?></summary>
              <ol>
                <?php foreach ($levels as $level): $isSolved = isset($solved[$level['id']]); $unlocked = lab57_level_unlocked($level, $solved); ?>
                  <li class="<?= $isSolved ? 'is-solved' : ($unlocked ? 'is-open' : 'is-locked') ?>">
                    <?php if ($unlocked): ?>
                      <a href="?view=lab&amp;uroven=<?= rawurlencode((string)$level['id']) ?>">
                        <span class="lab57-level-mark"><?= $isSolved ? '✔' : (int)$level['no'] ?></span>
                        <span><?= edu_cs((string)$level['title']) ?></span>
                      </a>
                    <?php else: ?>
                      <span class="lab57-level-locked">
                        <span class="lab57-level-mark">🔒</span>
                        <span><?= edu_cs((string)$level['title']) ?></span>
                      </span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            </details>
          </article>
        <?php endforeach; ?>

        <article class="lab57-pack lab57-pack-extra">
          <div class="lab57-pack-head">
            <span class="lab57-pack-icon" aria-hidden="true">›_</span>
            <div>
              <h2><?= e(tr('Volný terminál')) ?></h2>
              <p><?= e(tr('Žádný úkol, jen zkoušení příkazů ve vlastním tempu. Kdykoli jde vrátit do původního stavu.')) ?></p>
            </div>
          </div>
          <div class="lab57-pack-foot"><a class="btn secondary" href="?view=lab&amp;uroven=sandbox"><?= e(tr('Otevřít pískoviště')) ?></a></div>
        </article>

        <article class="lab57-pack lab57-pack-extra">
          <div class="lab57-pack-head">
            <span class="lab57-pack-icon" aria-hidden="true">📖</span>
            <div>
              <h2><?= e(tr('Příručka příkazů')) ?></h2>
              <p><?= e(tr('Vyhledej si libovolný Linux příkaz – co dělá, jaké má přepínače a jak ho vyzkoušet.')) ?></p>
            </div>
          </div>
          <div class="lab57-pack-foot"><a class="btn secondary" href="?view=prikazy"><?= e(tr('Otevřít příručku')) ?></a></div>
        </article>

        <?php if (function_exists('lab58_skill_map')): ?>
        <article class="lab57-pack lab57-pack-extra">
          <div class="lab57-pack-head">
            <span class="lab57-pack-icon" aria-hidden="true">◆</span>
            <div>
              <h2><?= e(tr('Mapa dovedností')) ?></h2>
              <p><?= e(tr('Přehled, které příkazy a pojmy už zvládáš a na čem ještě pracovat.')) ?></p>
            </div>
          </div>
          <div class="lab57-pack-foot"><a class="btn secondary" href="?view=lab&amp;sekce=dovednosti"><?= e(tr('Zobrazit mapu')) ?></a></div>
        </article>
        <?php endif; ?>
      </div>

      <?php lab57_render_hub_section(); ?>
    </div>
    <?php
    render_footer();
}

// ---------------------------------------------------------------------------
// Pracovní plocha (mise + terminál) – použije i stránka závodu
// ---------------------------------------------------------------------------

/** @param array{next_url?:string,compact?:bool} $opts */
function lab57_render_workspace(array $level, string $ctx, array $opts = []): void
{
    $nextUrl = (string)($opts['next_url'] ?? '?view=lab');
    $compact = !empty($opts['compact']);
    $uid = 'l57-' . substr(sha1($level['id'] . '|' . $ctx), 0, 8);
    $difficultyLabel = [trm('Lehká'), trm('Střední'), trm('Těžká')][max(1, min(3, (int)($level['difficulty'] ?? 1))) - 1];
    lab57_render_v58_assets();
    ?>
    <section class="lab57-work<?= $compact ? ' is-compact' : '' ?>" data-lab57 data-level="<?= e((string)$level['id']) ?>" data-ctx="<?= e($ctx) ?>" data-api="lab_v57_api.php" data-next="<?= e($nextUrl) ?>">
      <div class="lab57-mission" data-lab57-mission>
        <button type="button" class="lab57-mission-toggle" data-lab57-mission-toggle aria-expanded="true" aria-controls="<?= e($uid) ?>-mission-body"><?= e(tr('Mise')) ?> <i aria-hidden="true">⌄</i></button>
        <div class="lab57-mission-body" id="<?= e($uid) ?>-mission-body" data-lab57-mission-body>
          <header class="lab57-mission-head">
            <h2><?= edu_cs((string)$level['title']) ?></h2>
            <?php if ($level['type'] !== 'free'): ?>
              <div class="lab57-mission-chips">
                <span class="lab57-chip"><?= e(tr('Obtížnost: {stupen}', ['stupen' => tr($difficultyLabel)])) ?></span>
                <span class="lab57-chip"><?= e(tr('Body: {n}', ['n' => (int)$level['points']])) ?></span>
              </div>
            <?php endif; ?>
          </header>

          <div class="lab57-adaptive-hint" data-lab57-adaptive-hint hidden role="note">
            <strong><?= e(tr('Nápověda')) ?></strong>
            <p data-lab57-adaptive-hint-text></p>
          </div>

          <p class="lab57-story"<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$level['story'])) ?></p>
          <?php if (trim((string)$level['task']) !== ''): ?>
            <div class="lab57-task"><strong><?= e(tr('Úkol')) ?></strong><p<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$level['task'])) ?></p></div>
          <?php endif; ?>

          <?php $commands = array_values((array)($level['commands'] ?? [])); if ($commands !== []): ?>
            <div class="lab57-suggest">
              <span><?= e(tr('Příkazy k použití:')) ?></span>
              <?php foreach ($commands as $cmd): ?><a href="?view=prikazy&amp;c=<?= rawurlencode((string)$cmd) ?>" target="_blank" rel="noopener"><code><?= e((string)$cmd) ?></code></a><?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($level['type'] === 'code'): ?>
            <div class="lab57-turnin" data-lab57-turnin="code">
              <label for="<?= e($uid) ?>-code"><?= e(tr('Kód mise')) ?></label>
              <div class="lab57-turnin-row">
                <input type="text" id="<?= e($uid) ?>-code" data-lab57-code-input placeholder="EDU-XXXX-XXXX" autocomplete="off" autocapitalize="characters" spellcheck="false">
                <button type="button" class="btn primary" data-lab57-submit-code><?= e(tr('Odevzdat')) ?></button>
              </div>
            </div>
          <?php elseif ($level['type'] === 'answer'): ?>
            <div class="lab57-turnin" data-lab57-turnin="answer">
              <label for="<?= e($uid) ?>-answer"><?= e(tr('Odpověď')) ?><?= trim((string)($level['answer_format'] ?? '')) !== '' ? ' (' . e((string)$level['answer_format']) . ')' : '' ?></label>
              <div class="lab57-turnin-row">
                <input type="text" id="<?= e($uid) ?>-answer" data-lab57-answer-input autocomplete="off" spellcheck="false">
                <button type="button" class="btn primary" data-lab57-submit-answer><?= e(tr('Odpovědět')) ?></button>
              </div>
            </div>
          <?php elseif ($level['type'] === 'check'): ?>
            <div class="lab57-turnin" data-lab57-turnin="check">
              <ul class="lab57-checklist" data-lab57-checklist aria-live="polite"></ul>
              <button type="button" class="btn primary" data-lab57-check><?= e(tr('Zkontrolovat')) ?></button>
            </div>
          <?php elseif ($level['type'] === 'golf'): ?>
            <div class="lab57-turnin" data-lab57-turnin="golf">
              <p class="lab57-golf-note"><?= e(tr('Napiš svůj příkaz do terminálu a spusť ho. Až bude výstup sedět, odevzdej ho tímto tlačítkem.')) ?></p>
              <button type="button" class="btn primary" data-lab57-submit-golf><?= e(tr('Odevzdat poslední příkaz')) ?></button>
            </div>
          <?php endif; ?>

          <div class="lab57-mission-actions">
            <button type="button" class="btn secondary" data-lab57-hint><?= e(tr('Nápověda')) ?> <span data-lab57-hint-count>(0/0)</span></button>
            <button type="button" class="btn secondary" data-lab57-mise><?= e(tr('Mise')) ?></button>
            <button type="button" class="btn secondary lab57-danger" data-lab57-reset><?= e(tr('Začít znovu')) ?></button>
          </div>

          <div class="lab57-solved" data-lab57-solved hidden role="status">
            <strong><?= e(tr('Vyřešeno! 🎉')) ?></strong>
            <p data-lab57-learn></p>
            <div class="lab57-solved-meta">
              <span data-lab57-points></span>
              <span data-lab57-xp hidden></span>
              <span class="lab57-first-blood" data-lab57-first-blood hidden><?= e(tr('první krev 🔥')) ?></span>
            </div>
            <a class="btn primary" data-lab57-next href="<?= e($nextUrl) ?>"><?= e(tr('Další úroveň →')) ?></a>
          </div>
        </div>
      </div>

      <div class="lab57-term-col">
        <div class="lab57-term" data-lab57-term>
          <div class="lab57-term-bar">
            <span class="lab57-term-dot"></span><span class="lab57-term-dot"></span><span class="lab57-term-dot"></span>
            <span class="lab57-term-title">terminal</span>
            <span class="lab57-term-context" data-lab57-term-context hidden></span>
          </div>
          <?php /* v58 · A11Y-01: aria-live je vypnuté zde a řízené přes samostatný, nastavitelný živý region níž (data-lab57-live) – jinak by se nedalo ztlumit „jen chyby“/„vypnuto“. role="log" zůstává pro ruční procházení čtečkou. */ ?>
          <div class="lab57-term-out" data-lab57-out role="log" aria-live="off" aria-label="<?= e(tr('Výstup terminálu')) ?>" tabindex="0"></div>
          <form class="lab57-term-line" data-lab57-form>
            <span class="lab57-prompt" data-lab57-prompt>$</span>
            <input type="text" class="lab57-input" data-lab57-input autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" aria-label="<?= e(tr('Příkazová řádka')) ?>" aria-describedby="<?= e($uid) ?>-tabhint">
          </form>
          <p class="lab57-term-hint" id="<?= e($uid) ?>-tabhint"><?= e(tr('Tab doplní příkaz. Esc a pak Tab přesune fokus pryč z terminálu.')) ?></p>
          <div class="lab57-touchbar" data-lab57-touchbar aria-label="<?= e(tr('Klávesy pro dotykové ovládání')) ?>">
            <button type="button" data-lab57-key="Tab">Tab</button>
            <button type="button" data-lab57-key="Up">↑</button>
            <button type="button" data-lab57-key="Down">↓</button>
            <button type="button" data-lab57-key="Ctrl+C">Ctrl+C</button>
            <button type="button" data-lab57-insert="|">|</button>
            <button type="button" data-lab57-insert="~">~</button>
            <button type="button" data-lab57-insert="/">/</button>
            <button type="button" data-lab57-insert="-">-</button>
          </div>
        </div>

        <?php lab57_render_terminal_tools($uid); ?>
        <?php lab57_render_topology_block($level); ?>
      </div>
    </section>
    <?php
}

/** Přepínač „Vysvětli výstup“ (EDU-05), panel Přístupnost a zobrazení (A11Y-01/03) a živý region – řízené assets/lab-a11y-v58.js a assets/lab-explain-v58.js. */
function lab57_render_terminal_tools(string $uid): void
{
    ?>
    <div class="lab57-term-tools">
      <button type="button" class="btn secondary" data-lab57-explain-toggle aria-pressed="false"><?= e(tr('Vysvětli výstup')) ?></button>
      <details class="lab57-a11y-panel" data-lab57-a11y>
        <summary><?= e(tr('Přístupnost a zobrazení')) ?></summary>
        <div class="lab57-a11y-body">
          <fieldset>
            <legend><?= e(tr('Čtečka obrazovky')) ?></legend>
            <label for="<?= e($uid) ?>-announce"><?= e(tr('Oznamovat nový výstup')) ?></label>
            <select id="<?= e($uid) ?>-announce" data-lab57-a11y-announce>
              <option value="all"><?= e(tr('Celý výstup')) ?></option>
              <option value="errors"><?= e(tr('Jen chyby')) ?></option>
              <option value="off"><?= e(tr('Vypnuto')) ?></option>
            </select>
            <div class="lab57-a11y-read-row">
              <button type="button" class="btn secondary" data-lab57-a11y-read-last aria-keyshortcuts="Alt+Shift+P"><?= e(tr('Přečíst poslední výstup')) ?> <kbd>Alt+Shift+P</kbd></button>
            </div>
          </fieldset>
          <fieldset>
            <legend><?= e(tr('Zobrazení')) ?></legend>
            <label><input type="checkbox" data-lab57-a11y-plain> <?= e(tr('Jen text (bez barev a animací)')) ?></label>
            <label for="<?= e($uid) ?>-fontsize"><?= e(tr('Velikost písma')) . "\n" ?>              <select id="<?= e($uid) ?>-fontsize" data-lab57-a11y-fontsize>
                <option value="0"><?= e(tr('Normální')) ?></option>
                <option value="1"><?= e(tr('Větší')) ?></option>
                <option value="2"><?= e(tr('Velká')) ?></option>
                <option value="3"><?= e(tr('Největší')) ?></option>
              </select>
            </label>
            <label><input type="checkbox" data-lab57-a11y-contrast> <?= e(tr('Vysoký kontrast')) ?></label>
            <label><input type="checkbox" data-lab57-a11y-dyslexia> <?= e(tr('Písmo pro snazší čtení (bez patek, větší rozestupy)')) ?></label>
          </fieldset>
        </div>
      </details>
    </div>
    <div class="lab57-sr-only" aria-live="polite" data-lab57-live></div>
    <div class="lab57-explain-panel" data-lab57-explain-panel hidden tabindex="-1" role="region" aria-label="<?= e(tr('Vysvětlení výstupu')) ?>">
      <button type="button" class="lab57-explain-close" data-lab57-explain-close aria-label="<?= e(tr('Zavřít vysvětlení')) ?>">×</button>
      <h4 data-lab57-explain-title></h4>
      <div data-lab57-explain-body></div>
      <a data-lab57-explain-manual class="btn secondary small" href="#" hidden><?= e(tr('Otevřít v příručce →')) ?></a>
    </div>
    <?php
}

/** Topologie sítě: SVG (vizuál) + A11Y-02 textová tabulka poslední cesty, naplňovaná assets/lab-a11y-v58.js. */
function lab57_render_topology_block(array $level): void
{
    if (empty($level['topology'])) return;
    ?>
    <div class="lab57-net" data-lab57-net>
      <h3><?= e(tr('Síťová topologie')) ?></h3>
      <svg viewBox="0 0 760 300" role="img" aria-label="<?= e(tr('Schéma sítě')) ?>" data-lab57-net-svg></svg>
      <div class="lab57-net-textalt">
        <h4><?= e(tr('Cesta posledního příkazu (textově)')) ?></h4>
        <table data-lab57-net-table>
          <caption class="lab57-sr-only"><?= e(tr('Poslední síťová cesta krok po kroku')) ?></caption>
          <thead><tr><th scope="col"><?= e(tr('Krok')) ?></th><th scope="col"><?= e(tr('Zařízení')) ?></th><th scope="col"><?= e(tr('Adresa')) ?></th><th scope="col"><?= e(tr('Stav')) ?></th><th scope="col"><?= e(tr('Zpoždění')) ?></th></tr></thead>
          <tbody data-lab57-net-table-body>
            <tr><td colspan="5"><?= e(tr('Zatím žádný příkaz s trasou (např. ping, traceroute).')) ?></td></tr>
          </tbody>
        </table>
        <p class="lab57-net-note"><?= e(tr('Přesné zpoždění v milisekundách najdeš v textovém výstupu příkazu výše – tabulka ho zatím nedostává jako samostatný údaj.')) ?></p>
      </div>
    </div>
    <?php
}

// ---------------------------------------------------------------------------
// Stránka jedné úrovně (procvičování)
// ---------------------------------------------------------------------------

function lab57_render_level(string $classId, array $module, string $levelId, string $flash): void
{
    $level = lab57_resolve_level($levelId);
    $studentKey = adaptive_student_key($classId);
    $error = $level === null ? tr('Tahle úroveň neexistuje.') : lab57_access_error($level, ['context' => 'practice', 'class' => $classId, 'student' => $studentKey, 'now' => time()]);
    if ($level === null || $error !== null) {
        $_SESSION['flash'] = (string)$error;
        redirect_to('?view=lab');
    }

    $pack = (string)$level['pack'];
    $packInfo = function_exists('lab58_pack') ? lab58_pack($pack) : (lab57_packs()[$pack] ?? null);
    $nextUrl = '?view=lab';
    if ($pack !== 'free') {
        $siblings = lab57_pack_levels($pack);
        foreach ($siblings as $i => $sibling) {
            if ($sibling['id'] !== $level['id']) continue;
            $nextUrl = isset($siblings[$i + 1]) ? '?view=lab&uroven=' . rawurlencode((string)$siblings[$i + 1]['id']) : '?view=lab';
            break;
        }
    }

    render_header('Lab · ' . (string)$level['title'], $module, true);
    ?>
    <div class="lab57 lab57-level-page">
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>
      <nav class="lab57-crumbs" aria-label="<?= e(tr('Cesta')) ?>">
        <a href="?view=lab">Linux Lab</a>
        <?php if ($packInfo !== null): ?><span>›</span><span><?= edu_cs((string)$packInfo['title']) ?></span><?php endif; ?>
        <span>›</span><span><?= edu_cs((string)$level['title']) ?></span>
      </nav>
      <?php
      $cmdPrefill = isset($_GET['cmd']) && is_string($_GET['cmd']) ? $_GET['cmd'] : '';
      lab57_render_workspace($level, 'practice', ['next_url' => $nextUrl]);
      if ($cmdPrefill !== ''): ?>
        <script>document.currentScript.closest('.lab57-level-page').querySelector('[data-lab57]').dataset.cmd = <?= json_encode($cmdPrefill, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;</script>
      <?php endif; ?>
    </div>
    <?php
    render_footer();
}

// ---------------------------------------------------------------------------
// v58 · Sekce Labu naplněné daty LEARN (mapa dovedností, dnešní opakování)
// ---------------------------------------------------------------------------

/**
 * `?view=lab&sekce=dovednosti|opakovani`. Data dodává LEARN (lab58_skill_map/lab58_review_today) –
 * volá se jen přes function_exists, takže stránka funguje (s vysvětlující zprávou), i když LEARN ještě neběží.
 */
function lab58_render_section(string $classId, array $module, string $section): void
{
    $studentKey = adaptive_student_key($classId);
    if ($section === 'dovednosti') { lab58_render_skill_section($classId, $module, $studentKey); return; }
    if ($section === 'opakovani') { lab58_render_review_section($classId, $module, $studentKey); return; }
    $_SESSION['flash'] = 'Tahle část Labu neexistuje.';
    redirect_to('?view=lab');
}

function lab58_render_skill_section(string $classId, array $module, string $studentKey): void
{
    render_header('Lab · ' . tr('Mapa dovedností'), $module);
    ?>
    <div class="lab57 lab57-skills">
      <nav class="lab57-crumbs" aria-label="<?= e(tr('Cesta')) ?>"><a href="?view=lab">Linux Lab</a><span>›</span><span><?= e(tr('Mapa dovedností')) ?></span></nav>
      <h1><?= e(tr('Mapa dovedností')) ?></h1>
      <?php if (!function_exists('lab58_skill_map')): ?>
        <p class="lab57-empty-note"><?= e(tr('Mapa dovedností se připravuje – zkus to prosím později.')) ?></p>
      <?php else: $map = lab58_skill_map($classId, $studentKey); $summary = (array)($map['summary'] ?? []); $states = ['new' => trm('nové'), 'learning' => trm('učíš se'), 'mastered' => trm('zvládnuto')]; ?>
        <p class="lab57-skills-summary"><?= tr_html('{mastered} zvládnuto · {learning} se učíš · z {total} celkem', ['mastered' => '<b>' . (int)($summary['mastered'] ?? 0) . '</b>', 'learning' => '<b>' . (int)($summary['learning'] ?? 0) . '</b>', 'total' => (int)($summary['total'] ?? 0)]) ?></p>

        <?php $commands = (array)($map['commands'] ?? []); if ($commands !== []): ?>
        <section aria-label="<?= e(tr('Příkazy')) ?>">
          <h2><?= e(tr('Příkazy')) ?></h2>
          <div class="lab57-skill-grid">
            <?php foreach ($commands as $name => $info): $state = (string)($info['state'] ?? 'new'); $stateLabel = $states[$state] ?? $state; ?>
              <div class="lab57-skill-chip lab57-skill-<?= e($state) ?>">
                <code><?= e((string)$name) ?></code>
                <span><?= e(tr($stateLabel)) ?></span>
                <small><?= e(tr('{ok} / {uses} úspěšně', ['ok' => (int)($info['ok'] ?? 0), 'uses' => (int)($info['uses'] ?? 0)])) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php $concepts = (array)($map['concepts'] ?? []); if ($concepts !== []): ?>
        <section aria-label="<?= e(tr('Koncepty')) ?>">
          <h2><?= e(tr('Koncepty')) ?></h2>
          <div class="lab57-skill-grid">
            <?php foreach ($concepts as $id => $c): $progress = max(0.0, min(1.0, (float)($c['progress'] ?? 0))); $pct = (int)round($progress * 100); ?>
              <div class="lab57-concept-chip">
                <strong><?= edu_cs((string)($c['title'] ?? $id)) ?></strong>
                <div class="lab57-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $pct ?>"><div class="lab57-progress-bar" style="width:<?= $pct ?>%"></div></div>
                <small><?= e(tr('{pct} % zvládnuto', ['pct' => $pct])) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($commands === [] && $concepts === []): ?>
          <p class="lab57-empty-note"><?= e(tr('Zatím tu není žádná dovednost k zobrazení – vyřeš pár úloh v Labu a vrať se sem.')) ?></p>
        <?php endif; ?>
      <?php endif; ?>
      <p><a class="btn secondary" href="?view=lab"><?= e(tr('← Zpět do Labu')) ?></a></p>
    </div>
    <?php
    render_footer();
}

function lab58_render_review_section(string $classId, array $module, string $studentKey): void
{
    render_header('Lab · ' . tr('Dnešní opakování'), $module);
    ?>
    <div class="lab57 lab57-skills">
      <nav class="lab57-crumbs" aria-label="<?= e(tr('Cesta')) ?>"><a href="?view=lab">Linux Lab</a><span>›</span><span><?= e(tr('Dnešní opakování')) ?></span></nav>
      <h1><?= e(tr('Dnešní opakování')) ?></h1>
      <?php if (!function_exists('lab58_review_today')): ?>
        <p class="lab57-empty-note"><?= e(tr('Opakování se připravuje – zkus to prosím později.')) ?></p>
      <?php else: $review = lab58_review_today($classId, $studentKey); $items = (array)($review['items'] ?? []); ?>
        <p class="lab57-skills-summary"><?= tr_html('Řada {n} dní v kuse.', ['n' => '<b>' . (int)($review['streak'] ?? 0) . '</b>']) ?></p>
        <?php if ($items === []): ?>
          <p class="lab57-empty-note"><?= e(tr('Na dnešek nemáš naplánovanou žádnou úlohu k zopakování.')) ?></p>
        <?php else: ?>
          <ul class="lab57-review-list">
            <?php foreach ($items as $item): $done = !empty($item['done']); $rawUrl = (string)($item['url'] ?? ''); $itemUrl = str_starts_with($rawUrl, '?') ? $rawUrl : ''; ?>
              <li class="<?= $done ? 'is-done' : '' ?>">
                <span><?= $done ? '✔ ' : '' ?><?= edu_cs((string)($item['title'] ?? $item['level'] ?? '')) ?></span>
                <span class="lab57-review-box"><?= e(tr('přihrádka {n}', ['n' => (int)($item['box'] ?? 1)])) ?></span>
                <?php if (!$done && $itemUrl !== ''): ?><a class="btn secondary small" href="<?= e($itemUrl) ?>"><?= e(tr('Otevřít')) ?></a><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endif; ?>
      <p><a class="btn secondary" href="?view=lab"><?= e(tr('← Zpět do Labu')) ?></a></p>
    </div>
    <?php
    render_footer();
}

// ---------------------------------------------------------------------------
// Příručka příkazů
// ---------------------------------------------------------------------------

function lab57_render_manual(string $classId, array $module, ?string $command): void
{
    $manual = v57_manual();
    if ($command !== null && $command !== '') {
        lab57_render_manual_detail($module, $manual, $command);
        return;
    }
    lab57_render_manual_index($module, $manual);
}

function lab57_render_manual_index(array $module, array $manual): void
{
    render_header(tr('Linux příkazy'), $module);
    ?>
    <div class="lab57 lab57-manual">
      <header class="lab57-manual-head">
        <h1><?= e(tr('Příručka Linux příkazů')) ?></h1>
        <p><?= e(tr('Vyhledej si příkaz, přečti si, co dělá, a vyzkoušej ho rovnou v pískovišti.')) ?></p>
        <a class="btn secondary" href="?view=lab"><?= e(tr('← Zpět do Labu')) ?></a>
      </header>
      <div class="lab57-manual-search">
        <input type="search" data-lab57-manual-search placeholder="<?= e(tr('Hledat příkaz, např. grep…')) ?>" aria-label="<?= e(tr('Hledat příkaz')) ?>">
      </div>

      <div class="lab57-manual-cats" data-lab57-manual-list<?= edu_content_lang_attr() ?>>
        <?php foreach ((array)$manual['categories'] as $catId => $cat): $cmds = array_filter((array)$manual['commands'], static fn(array $c): bool => $c['cat'] === $catId); if ($cmds === []) continue; ?>
          <section class="lab57-manual-cat" data-lab57-manual-cat>
            <h2><span aria-hidden="true"><?= e((string)$cat['icon']) ?></span> <?= e((string)$cat['label']) ?></h2>
            <p><?= e((string)$cat['lead']) ?></p>
            <div class="lab57-manual-grid">
              <?php // v61: karta na jednom řádku bez odsazení – index má ~170 příkazů, bílé znaky tvořily čtvrtinu HTML.
              foreach ($cmds as $name => $cmd) { echo '<a class="lab57-manual-card" href="?view=prikazy&amp;c=', rawurlencode((string)$name), '" data-lab57-manual-item><code>', e((string)$name), '</code> <span class="lab57-manual-level">', e(tr('úroveň {n}', ['n' => (int)$cmd['level']])), '</span>', empty($cmd['in_lab']) ? ' <span class="lab57-manual-badge">' . e(tr('jen přehled')) . '</span>' : '', '<p>', e((string)$cmd['summary']), '</p></a>', "\n"; } ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>

      <p class="lab57-manual-empty" data-lab57-manual-empty hidden><?= e(tr('Žádný příkaz s tímhle jménem jsme nenašli.')) ?></p>

      <?php if (!empty($manual['concepts'])): ?>
        <section class="lab57-manual-concepts">
          <h2><?= e(tr('Jak funguje shell')) ?></h2>
          <div class="lab57-manual-grid"<?= edu_content_lang_attr() ?>>
            <?php // v61: kompaktní výpis bez odsazení (úspora HTML).
            foreach ((array)$manual['concepts'] as $key => $c) {
                echo '<article class="lab57-concept"><h3>', e((string)$c['title']), '</h3><p>', e((string)$c['about']), '</p>';
                foreach ((array)($c['examples'] ?? []) as [$cmd, $note]) echo '<div class="lab57-example"><code>', e((string)$cmd), '</code><span>', e((string)$note), '</span></div>';
                echo "</article>\n";
            } ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
    <?php
    render_footer();
}

function lab57_render_manual_detail(array $module, array $manual, string $command): void
{
    $commands = (array)$manual['commands'];
    $entry = $commands[$command] ?? null;

    render_header(tr('Příkaz {prikaz}', ['prikaz' => $command]), $module);
    ?>
    <div class="lab57 lab57-manual lab57-manual-detail">
      <nav class="lab57-crumbs" aria-label="<?= e(tr('Cesta')) ?>">
        <a href="?view=prikazy"><?= e(tr('Příručka příkazů')) ?></a><span>›</span><span><?= e($command) ?></span>
      </nav>
      <?php if ($entry === null): $suggestions = lab57_manual_suggest($commands, $command); ?>
        <div class="lab57-manual-notfound">
          <h1><?= e(tr('Příkaz „{prikaz}“ neznáme', ['prikaz' => $command])) ?></h1>
          <p><?= e(tr('Zkontroluj překlep, nebo zkus některý z podobných příkazů:')) ?></p>
          <?php if ($suggestions !== []): ?>
            <ul class="lab57-manual-suggest-list">
              <?php foreach ($suggestions as $s): ?><li><a href="?view=prikazy&amp;c=<?= rawurlencode($s) ?>"><code><?= e($s) ?></code></a></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <a class="btn secondary" href="?view=prikazy"><?= e(tr('← Zpět na seznam příkazů')) ?></a>
        </div>
      <?php else: $cat = (array)($manual['categories'][$entry['cat']] ?? ['label' => '']); ?>
        <article class="lab57-manual-page"<?= edu_content_lang_attr() ?>>
          <header>
            <span class="lab57-manual-cat-label"><?= e((string)$cat['label']) ?></span>
            <h1><code><?= e($command) ?></code></h1>
            <p class="lab57-manual-synopsis"><?= e((string)$entry['synopsis']) ?></p>
            <p><?= e((string)$entry['summary']) ?></p>
            <?php if (empty($entry['in_lab'])): ?><p class="lab57-manual-badge lab57-manual-badge-block"><?= e(tr('jen přehled – v Labu se aktivně nepoužívá')) ?></p><?php endif; ?>
          </header>

          <section class="lab57-manual-about"><h2><?= e(tr('Jak to funguje')) ?></h2><p><?= e((string)$entry['about']) ?></p></section>

          <?php if (!empty($entry['options'])): ?>
            <section class="lab57-manual-options">
              <h2><?= e(tr('Přepínače')) ?></h2>
              <table>
                <?php foreach ((array)$entry['options'] as [$flag, $text]): ?>
                  <tr><td><code><?= e((string)$flag) ?></code></td><td><?= e((string)$text) ?></td></tr>
                <?php endforeach; ?>
              </table>
            </section>
          <?php endif; ?>

          <?php if (!empty($entry['examples'])): ?>
            <section class="lab57-manual-examples">
              <h2><?= e(tr('Příklady')) ?></h2>
              <?php foreach ((array)$entry['examples'] as [$cmd, $text]): ?>
                <div class="lab57-example">
                  <code><?= e((string)$cmd) ?></code>
                  <span><?= e((string)$text) ?></span>
                  <?php if (!empty($entry['in_lab'])): ?><a class="lab57-try" href="?view=lab&amp;uroven=sandbox&amp;cmd=<?= rawurlencode((string)$cmd) ?>"><?= e(tr('Vyzkoušet →')) ?></a><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </section>
          <?php endif; ?>

          <?php if (trim((string)($entry['tip'] ?? '')) !== ''): ?><div class="lab57-callout lab57-tip-callout"><strong><?= e(tr('Tip')) ?></strong><p><?= e((string)$entry['tip']) ?></p></div><?php endif; ?>
          <?php if (trim((string)($entry['warn'] ?? '')) !== ''): ?><div class="lab57-callout lab57-warn-callout"><strong><?= e(tr('Pozor')) ?></strong><p><?= e((string)$entry['warn']) ?></p></div><?php endif; ?>

          <?php if (!empty($entry['related'])): ?>
            <section class="lab57-manual-related">
              <h2><?= e(tr('Související')) ?></h2>
              <?php foreach ((array)$entry['related'] as $rel): ?><a href="?view=prikazy&amp;c=<?= rawurlencode((string)$rel) ?>"><code><?= e((string)$rel) ?></code></a><?php endforeach; ?>
            </section>
          <?php endif; ?>
        </article>
      <?php endif; ?>
    </div>
    <?php
    render_footer();
}

/** @return list<string> */
function lab57_manual_suggest(array $commands, string $needle): array
{
    $names = array_keys($commands);
    $matches = array_values(array_filter($names, static fn(string $n): bool => str_contains($n, $needle) || str_contains($needle, $n)));
    if ($matches !== []) return array_slice($matches, 0, 6);
    usort($names, static fn(string $a, string $b): int => levenshtein($needle, $a) <=> levenshtein($needle, $b));
    return array_slice($names, 0, 6);
}
