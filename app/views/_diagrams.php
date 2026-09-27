<?php

declare(strict_types=1);

/**
 * Diagramy případových studií, terminálové karty a bodování checkpointů (v51).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function render_case_diagram(array $diagram, array $focus = []): void
{
    $nodes = is_array($diagram['nodes'] ?? null) ? $diagram['nodes'] : [];
    $links = is_array($diagram['links'] ?? null) ? $diagram['links'] : [];
    if (!$nodes) {
        return;
    }
    $width = max(640, (int)($diagram['width'] ?? 920));
    $height = max(300, (int)($diagram['height'] ?? 390));
    $byId = [];
    foreach ($nodes as $node) {
        if (!is_array($node) || empty($node['id'])) {
            continue;
        }
        $byId[(string)$node['id']] = $node;
    }
    $focusMap = array_fill_keys(array_map('strval', $focus), true);
    ?>
    <figure class="case-diagram-card">
        <?php if (!empty($diagram['title'])): ?><div class="case-diagram-title"<?= edu_content_lang_attr() ?>><?= e((string)$diagram['title']) ?></div><?php endif; ?>
        <div class="case-diagram-scroll">
            <svg class="case-diagram" viewBox="0 0 <?= $width ?> <?= $height ?>" role="img" aria-label="<?= e((string)($diagram['title'] ?? tr('Síťový diagram'))) ?>"<?= edu_content_lang_attr() ?>>
                <defs>
                    <marker id="arrow-head" markerWidth="8" markerHeight="8" refX="7" refY="3.5" orient="auto"><polygon points="0 0, 7 3.5, 0 7" /></marker>
                </defs>
                <?php foreach ($links as $link):
                    if (!is_array($link)) continue;
                    $from = $byId[(string)($link['from'] ?? '')] ?? null;
                    $to = $byId[(string)($link['to'] ?? '')] ?? null;
                    if (!is_array($from) || !is_array($to)) continue;
                    $x1 = (float)$from['x'] + ((float)($from['w'] ?? 150) / 2);
                    $y1 = (float)$from['y'] + ((float)($from['h'] ?? 62) / 2);
                    $x2 = (float)$to['x'] + ((float)($to['w'] ?? 150) / 2);
                    $y2 = (float)$to['y'] + ((float)($to['h'] ?? 62) / 2);
                    $mx = ($x1 + $x2) / 2;
                    $my = ($y1 + $y2) / 2;
                    $linkFocused = isset($focusMap[(string)($link['from'] ?? '')]) && isset($focusMap[(string)($link['to'] ?? '')]);
                    ?>
                    <line class="case-link<?= $linkFocused ? ' focused' : '' ?>" x1="<?= $x1 ?>" y1="<?= $y1 ?>" x2="<?= $x2 ?>" y2="<?= $y2 ?>" marker-end="url(#arrow-head)" />
                    <?php if (!empty($link['label'])): ?>
                        <text class="case-link-label" x="<?= $mx ?>" y="<?= $my - 7 ?>" text-anchor="middle"><?= e((string)$link['label']) ?></text>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php foreach ($nodes as $node):
                    if (!is_array($node) || empty($node['id'])) continue;
                    $id = (string)$node['id'];
                    $x = (float)($node['x'] ?? 0); $y = (float)($node['y'] ?? 0);
                    $w = (float)($node['w'] ?? 150); $h = (float)($node['h'] ?? 62);
                    $focused = !$focus || isset($focusMap[$id]);
                    ?>
                    <g class="case-node<?= $focused ? ' focused' : ' muted' ?>">
                        <rect x="<?= $x ?>" y="<?= $y ?>" width="<?= $w ?>" height="<?= $h ?>" rx="14" />
                        <text class="case-node-label" x="<?= $x + 14 ?>" y="<?= $y + 25 ?>"><?= e((string)($node['label'] ?? $id)) ?></text>
                        <?php if (!empty($node['sub'])): ?><text class="case-node-sub" x="<?= $x + 14 ?>" y="<?= $y + 46 ?>"><?= e((string)$node['sub']) ?></text><?php endif; ?>
                    </g>
                <?php endforeach; ?>
            </svg>
        </div>
        <?php if (!empty($diagram['caption'])): ?><figcaption<?= edu_content_lang_attr() ?>><?= e((string)$diagram['caption']) ?></figcaption><?php endif; ?>
    </figure>
    <?php
}

function render_terminal_cards(array $items, ?string $taskId = null): void
{
    $visible = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        if ($taskId !== null && (string)($item['task_id'] ?? '') !== $taskId) continue;
        $visible[] = $item;
    }
    if (!$visible) {
        return;
    }
    ?>
    <section class="terminal-lab" data-terminal-lab>
        <div class="terminal-lab-head">
            <div><div class="eyebrow"><?= e(tr('Simulovaný terminál')) ?></div><h2><?= e(tr('Spusť diagnostické příkazy')) ?></h2></div>
            <span><?= e(trn(['one' => '{n} ukázka', 'few' => '{n} ukázky', 'other' => '{n} ukázek'], count($visible))) ?></span>
        </div>
        <p class="terminal-note"><?= e(tr('Příkazy se ve skutečnosti na tvém zařízení nespouštějí. Kliknutím odkryješ realistický výstup a můžeš si zkusit formulovat závěr dřív, než otevřeš interpretaci.')) ?></p>
        <div class="terminal-stack">
            <?php foreach ($visible as $i => $item): ?>
                <article class="terminal-card">
                    <div class="terminal-card-top">
                        <div><strong<?= edu_content_lang_attr() ?>><?= e((string)($item['label'] ?? tr('Příkaz {n}', ['n' => (string)($i + 1)]))) ?></strong><small<?= edu_content_lang_attr() ?>><?= e((string)($item['purpose'] ?? '')) ?></small></div>
                        <button class="btn tertiary terminal-run" type="button" aria-expanded="false"><?= e(tr('Spustit příkaz')) ?></button>
                    </div>
                    <div class="terminal-command"><span><?= e((string)($item['prompt'] ?? '$')) ?></span><code><?= e((string)($item['command'] ?? '')) ?></code></div>
                    <div class="terminal-output" hidden><pre><?= e((string)($item['output'] ?? '')) ?></pre></div>
                    <?php if (!empty($item['meaning'])): ?>
                        <details class="terminal-meaning" hidden>
                            <summary><?= e(tr('Co tento výstup dokazuje?')) ?></summary>
                            <p<?= edu_content_lang_attr() ?>><?= e((string)$item['meaning']) ?></p>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/** v51: bodování checkpointu – 3 body za první pokus bez nápovědy, každý další pokus nebo nápověda −1, minimum 1 bod. */
const U51_PRACTICE_MAX_POINTS = 3;
function u51_practice_points(array $answers): int
{
    $sum = 0;
    foreach ($answers as $answer) {
        if (!is_array($answer)) continue;
        $sum += max(1, U51_PRACTICE_MAX_POINTS - max(0, (int)($answer['attempts'] ?? 1) - 1) - max(0, (int)($answer['hints_used'] ?? 0)));
    }
    return $sum;
}
