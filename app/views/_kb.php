<?php

declare(strict_types=1);

/**
 * Vizualizace, simulace a průvodce tématy knowledgebase.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function render_kb_visual(array $visual): void
{
    static $counter = 0;
    $counter++;
    $type = (string)($visual['type'] ?? 'flow');
    $uid = 'kbv-' . $counter;
    $interactiveTypes = ['poster','grid','color','pixels','export'];
    $isInteractive = in_array($type, $interactiveTypes, true);
    if ($type === 'color' && (string)($visual['mode'] ?? 'contrast') === 'rgb-cmyk') { $isInteractive = false; }

    echo '<div class="kb-demo-stage" data-kb-demo-stage data-kb-visual-type="' . e($type) . '" data-kb-visual-id="' . e($uid) . '">';
    echo '<div class="kb-demo-toolbar">';
    echo '<div class="kb-demo-live"><i></i><span>' . e(tr('Interaktivní model')) . '</span><b data-kb-demo-state>' . e(tr('připraveno')) . '</b></div>';
    echo '<div class="kb-demo-toolbar-actions">';
    if (!$isInteractive) {
        echo '<button type="button" class="kb-demo-next" data-kb-demo-next>' . e(tr('Další krok →')) . '</button>';
    }
    echo '<button type="button" class="kb-demo-reset" data-kb-demo-reset>' . e(tr('Znovu')) . '</button>';
    echo '</div></div>';

    if ($type === 'subnet') {
        ?>
        <div class="kb-demo-generic-canvas kb-subnet-visual"<?=edu_content_lang_attr()?>>
            <div class="kb-subnet-ip" data-demo-step="0"><span>IP / prefix</span><strong><?= e((string)($visual['address'] ?? '192.168.1.42/24')) ?></strong></div>
            <div class="kb-address-track" aria-label="Rozdělení adresního prostoru">
                <div class="network" data-demo-step="1"><span>síť</span><b><?= e((string)($visual['network'] ?? 'síťová adresa')) ?></b></div>
                <div class="hosts" data-demo-step="2"><span>hosté</span><b><?= e((string)($visual['hosts'] ?? 'použitelné adresy')) ?></b></div>
                <div class="broadcast" data-demo-step="3"><span>broadcast</span><b><?= e((string)($visual['broadcast'] ?? 'broadcast')) ?></b></div>
            </div>
            <div class="kb-demo-caption">Prefix určuje hranici mezi částí sítě a částí hosta. Procházej model krok po kroku a sleduj, co se při rozhodování skutečně používá.</div>
        </div>
        <?php
    } elseif ($type === 'sequence' || $type === 'flow') {
        $items = is_array($visual['items'] ?? null) ? $visual['items'] : [];
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-flow <?= $type === 'sequence' ? 'sequence' : '' ?>">
            <?php foreach ($items as $i => $item): if (!is_array($item)) continue; ?>
                <div class="kb-flow-node" data-demo-step="<?= $i ?>"><span><?= e((string)($item[0] ?? ($i + 1))) ?></span><strong><?= e((string)($item[1] ?? '')) ?></strong></div>
                <?php if ($i < count($items)-1): ?><div class="kb-flow-arrow" aria-hidden="true">→</div><?php endif; ?>
            <?php endforeach; ?>
        </div></div><?php
    } elseif ($type === 'layers') {
        $items = is_array($visual['items'] ?? null) ? $visual['items'] : [];
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-layer-stack">
            <?php foreach ($items as $i => $item): if (!is_array($item)) continue; ?>
                <div class="kb-layer" data-demo-step="<?= $i ?>"><span><?= e((string)($item[0] ?? ($i + 1))) ?></span><div><strong><?= e((string)($item[1] ?? 'Vrstva')) ?></strong><small><?= e((string)($item[2] ?? '')) ?></small></div></div>
            <?php endforeach; ?>
        </div></div><?php
    } elseif ($type === 'ports') {
        $items = is_array($visual['items'] ?? null) ? $visual['items'] : [];
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-server-visual">
            <div class="kb-server-box" data-demo-step="0"><span class="server-light"></span><strong><?= e((string)($visual['host'] ?? 'server')) ?></strong><small>jeden host · více služeb</small></div>
            <div class="kb-port-list">
                <?php foreach ($items as $i => $item): if (!is_array($item)) continue; $state=(string)($item[2]??'open'); ?>
                    <div class="kb-port-row state-<?= e($state) ?>" data-demo-step="<?= $i+1 ?>"><code>:<?= e((string)($item[0]??'')) ?></code><strong><?= e((string)($item[1]??'')) ?></strong><span><?= e($state==='open'?'naslouchá':($state==='local'?'jen lokálně':'zavřeno')) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div></div><?php
    } elseif ($type === 'permissions') {
        $rows = is_array($visual['rows'] ?? null) ? $visual['rows'] : [];
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-permission-visual"><div class="kb-permission-mode" data-demo-step="0">chmod <strong><?= e((string)($visual['mode'] ?? '600')) ?></strong></div><div class="kb-permission-grid">
            <?php foreach ($rows as $i=>$row): if(!is_array($row)) continue; ?><div class="kb-permission-row" data-demo-step="<?= $i+1 ?>"><strong><?= e((string)($row[0]??'')) ?></strong><code><?= e((string)($row[1]??'')) ?></code><span><?= e((string)($row[2]??'')) ?></span></div><?php endforeach; ?>
        </div></div></div><?php
    } elseif ($type === 'compare') {
        $left=is_array($visual['left']??null)?$visual['left']:[]; $right=is_array($visual['right']??null)?$visual['right']:[];
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-compare-visual"><div class="kb-compare-card badish" data-demo-step="0"><span>Varianta A</span><strong><?= e((string)($left[0]??'')) ?></strong><p><?= e((string)($left[1]??'')) ?></p><small><?= e((string)($left[2]??'')) ?></small></div><div class="kb-compare-vs">VS</div><div class="kb-compare-card goodish" data-demo-step="1"><span>Varianta B</span><strong><?= e((string)($right[0]??'')) ?></strong><p><?= e((string)($right[1]??'')) ?></p><small><?= e((string)($right[2]??'')) ?></small></div></div></div><?php
    } elseif ($type === 'poster') {
        $mode=(string)($visual['mode']??'hierarchy');
        ?>
        <div class="kb-interactive-demo kb-hierarchy-lab" data-kb-hierarchy-lab data-mode="<?= e($mode) ?>"<?=edu_content_lang_attr()?>>
            <div class="kb-demo-canvas hierarchy-canvas">
                <div class="hierarchy-poster" data-hierarchy-poster style="--h-title:36px;--h-meta:22px;--h-cta:15px;--h-space:8px;">
                    <span class="hierarchy-kicker">EDUCANET · DESIGN LAB</span>
                    <strong class="hierarchy-title">STUDENT<br>NIGHT</strong>
                    <p class="hierarchy-meta">PÁTEK · 18:00 · BRNO</p>
                    <button class="hierarchy-cta" type="button">PŘIJĎ TAKY →</button>
                    <small class="hierarchy-note">workshop · hudba · portfolio review</small>
                    <svg class="hierarchy-eye-path" viewBox="0 0 100 100" aria-hidden="true"><path d="M34 23 C62 20 68 44 46 52 C30 58 42 75 64 78"/><circle cx="34" cy="23" r="3"/><circle cx="46" cy="52" r="3"/><circle cx="64" cy="78" r="3"/></svg>
                </div>
                <div class="hierarchy-insight" data-hierarchy-insight>
                    <span>Čitelnost z dálky</span><strong>Plochá hierarchie</strong><p>Uprav ovladače. Cílem je, aby oko našlo název → datum → CTA bez hledání.</p>
                </div>
            </div>
            <aside class="kb-demo-controls" aria-label="Ovládání vizuální hierarchie">
                <div class="demo-control-head"><span>Experiment</span><strong>Měň jeden parametr a sleduj efekt</strong></div>
                <div class="demo-presets" role="group" aria-label="Předvolby"><button type="button" data-hierarchy-preset="flat" class="active">Plochá</button><button type="button" data-hierarchy-preset="balanced">Vyvážená</button><button type="button" data-hierarchy-preset="strong">Silná</button></div>
                <label class="demo-range"><span>Titulek <b data-hierarchy-value="title">36 px</b></span><input type="range" min="28" max="76" step="2" value="36" data-hierarchy-input="title"></label>
                <label class="demo-range"><span>Datum + místo <b data-hierarchy-value="meta">22 px</b></span><input type="range" min="12" max="30" step="1" value="22" data-hierarchy-input="meta"></label>
                <label class="demo-range"><span>CTA <b data-hierarchy-value="cta">15 px</b></span><input type="range" min="12" max="26" step="1" value="15" data-hierarchy-input="cta"></label>
                <label class="demo-range"><span>Spacing <b data-hierarchy-value="space">8 px</b></span><input type="range" min="6" max="30" step="2" value="8" data-hierarchy-input="space"></label>
                <label class="demo-switch"><input type="checkbox" data-hierarchy-thumbnail><span></span><b>Testovat jako malý náhled</b></label>
                <div class="demo-rule"><span>Princip</span><p>Hierarchie není „velký nadpis“. Je to rozdíl důrazu mezi rolemi: velikost + váha + pozice + prostor.</p></div>
            </aside>
        </div>
        <?php
    } elseif ($type === 'grid') {
        ?>
        <div class="kb-interactive-demo kb-grid-lab" data-kb-grid-lab<?=edu_content_lang_attr()?>>
            <div class="kb-demo-canvas">
                <div class="grid-live-canvas" data-grid-canvas style="--grid-cols:4;--grid-margin:24px;--grid-gap:16px">
                    <div class="grid-live-lines" data-grid-lines></div>
                    <div class="grid-live-content" data-grid-content><strong>DESIGN WEEK</strong><div class="grid-live-image">IMAGE</div><p>Workshopy · přednášky<br>12.–14. října</p><b>PROGRAM →</b></div>
                </div>
                <div class="grid-insight"><span>Systém zarovnání</span><strong data-grid-status>4 sloupce · volné hrany</strong></div>
            </div>
            <aside class="kb-demo-controls"><div class="demo-control-head"><span>Grid lab</span><strong>Sleduj společné hrany a rytmus</strong></div><label class="demo-range"><span>Sloupce <b data-grid-value="cols">4</b></span><input type="range" min="2" max="12" step="2" value="4" data-grid-input="cols"></label><label class="demo-range"><span>Margin <b data-grid-value="margin">24 px</b></span><input type="range" min="8" max="48" step="4" value="24" data-grid-input="margin"></label><label class="demo-range"><span>Gutter <b data-grid-value="gap">16 px</b></span><input type="range" min="4" max="32" step="4" value="16" data-grid-input="gap"></label><label class="demo-switch"><input type="checkbox" data-grid-align><span></span><b>Přichytit obsah ke gridu</b></label><div class="demo-rule"><span>Princip</span><p>Grid je skrytá konstrukce. Dobře funguje, když prvky sdílejí několik jasných zarovnávacích linií.</p></div></aside>
        </div>
        <?php
    } elseif ($type === 'color') {
        $mode=(string)($visual['mode']??'contrast');
        if ($mode === 'rgb-cmyk') { ?>
            <div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-color-space"><div class="rgb-orb" data-demo-step="0"><span>RGB</span><b>světlo</b><small>display / web</small></div><div class="color-arrow">→</div><div class="cmyk-stack" data-demo-step="1"><span>CMYK</span><b>inkoust</b><small>běžný tisk</small></div></div></div>
        <?php } else { ?>
            <div class="kb-interactive-demo kb-contrast-lab" data-kb-contrast-lab<?=edu_content_lang_attr()?>>
                <div class="kb-demo-canvas"><div class="contrast-live-card" data-contrast-card style="--contrast-bg:#7b6fa8;--contrast-text:#a99fd0"><span>STUDENT DESIGN LAB</span><strong>ČTEŠ MĚ?</strong><p>Důležitá informace musí zůstat čitelná.</p><button type="button">REGISTRUJ SE →</button></div><div class="contrast-readout" data-contrast-readout><span>Kontrastní poměr</span><strong>1.68 : 1</strong><p>Nedostatečné pro běžný text.</p></div></div>
                <aside class="kb-demo-controls"><div class="demo-control-head"><span>Contrast lab</span><strong>Měň barvy a sleduj měřitelný rozdíl</strong></div><label class="demo-color"><span>Pozadí</span><input type="color" value="#7b6fa8" data-contrast-input="bg"></label><label class="demo-color"><span>Text</span><input type="color" value="#a99fd0" data-contrast-input="text"></label><div class="demo-presets"><button type="button" data-contrast-preset="low" class="active">Nízký</button><button type="button" data-contrast-preset="medium">Střední</button><button type="button" data-contrast-preset="high">Vysoký</button></div><div class="demo-rule"><span>Princip</span><p>Barva může být krásná a přesto nečitelná. Nejprve ověř čitelnost, potom estetiku.</p></div></aside>
            </div>
        <?php }
    } elseif ($type === 'pixels') {
        ?><div class="kb-interactive-demo kb-scale-lab" data-kb-scale-lab<?=edu_content_lang_attr()?>><div class="kb-demo-canvas"><div class="scale-compare"><div class="scale-card raster" data-scale-raster><div class="scale-logo">A</div><strong>RASTER</strong><small>PNG · konečný počet pixelů</small></div><div class="scale-card vector" data-scale-vector><svg viewBox="0 0 100 100"><path d="M18 82 L50 16 L82 82 Z"/><circle cx="50" cy="60" r="12"/></svg><strong>VEKTOR</strong><small>SVG · geometrický popis</small></div></div><div class="scale-readout"><span>Zvětšení</span><strong data-scale-readout>100 %</strong></div></div><aside class="kb-demo-controls"><div class="demo-control-head"><span>Scale lab</span><strong>Zvětšuj oba zdroje současně</strong></div><label class="demo-range"><span>Zoom <b data-scale-value>100 %</b></span><input type="range" min="100" max="1000" step="100" value="100" data-scale-input></label><div class="demo-rule"><span>Princip</span><p>Raster při zvětšení interpoluje pixely. Vektor znovu vykreslí křivky v cílové velikosti.</p></div></aside></div><?php
    } elseif ($type === 'export') {
        ?><div class="kb-interactive-demo kb-export-lab" data-kb-export-lab<?=edu_content_lang_attr()?>><div class="kb-demo-canvas"><div class="export-live-preview" data-export-preview><span>WEB HERO</span><strong>BUILD<br>BETTER.</strong><i data-export-artifacts></i></div><div class="export-live-metrics"><div><span>Formát</span><b data-export-format>WEBP</b></div><div><span>Šířka</span><b data-export-width>1920 px</b></div><div><span>Odhad</span><b data-export-size>~348 kB</b></div></div></div><aside class="kb-demo-controls"><div class="demo-control-head"><span>Export lab</span><strong>Vyvaž kvalitu, rozměr a datovou velikost</strong></div><label class="demo-select"><span>Formát</span><select data-export-input="format"><option value="webp">WebP</option><option value="jpg">JPG</option><option value="png">PNG</option></select></label><label class="demo-select"><span>Šířka</span><select data-export-input="width"><option>1080</option><option selected>1920</option><option>2560</option><option>4000</option></select></label><label class="demo-range"><span>Kvalita <b data-export-value>82 %</b></span><input type="range" min="40" max="100" step="2" value="82" data-export-input="quality"></label><div class="demo-rule"><span>Princip</span><p>Správný export není největší soubor. Je to nejmenší soubor, který stále splní vizuální cíl.</p></div></aside></div><?php
    } elseif ($type === 'assets') {
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-assets-demo"><div class="asset-card unknown" data-demo-step="0"><div class="asset-thumb">?</div><strong>Náhodný obrázek</strong><small>neznámý autor · neznámá licence</small></div><div class="asset-arrow">→ ověř →</div><div class="asset-card verified" data-demo-step="1"><div class="asset-thumb">✓</div><strong>Ověřený zdroj</strong><small>autor · licence · povolené použití</small></div></div></div><?php
    } else {
        ?><div class="kb-demo-generic-canvas"<?=edu_content_lang_attr()?>><div class="kb-flow"><div class="kb-flow-node" data-demo-step="0"><span>1</span><strong>Vstup</strong></div><div class="kb-flow-arrow">→</div><div class="kb-flow-node" data-demo-step="1"><span>2</span><strong>Pozoruj</strong></div><div class="kb-flow-arrow">→</div><div class="kb-flow-node" data-demo-step="2"><span>3</span><strong>Rozhodni</strong></div></div></div><?php
    }
    echo '</div>';
}

function render_kb_simulation(array $sim): void
{
    $config = json_encode($sim, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if (!is_string($config)) {
        return;
    }
    $prediction = is_array($sim['prediction'] ?? null) ? $sim['prediction'] : [];
    $conclusion = is_array($sim['conclusion'] ?? null) ? $sim['conclusion'] : [];
    $hints = is_array($sim['hints'] ?? null) ? $sim['hints'] : [];
    ?>
    <section class="kb-sim" data-kb-sim data-sim-id="<?= e((string)($sim['id'] ?? 'simulation')) ?>">
        <script type="application/json" data-sim-config><?= $config ?></script>
        <div class="sim-head">
            <div>
                <div class="eyebrow"><?= tr_html('Interaktivní simulace · {n} bodů nanečisto', ['n' => e((string)($sim['points'] ?? 5))]) ?></div>
                <h3<?=edu_content_lang_attr()?>><?= e((string)($sim['title'] ?? tr('Simulace'))) ?></h3>
                <p<?=edu_content_lang_attr()?>><?= e((string)($sim['lead'] ?? tr('Změň parametry a sleduj důsledky.'))) ?></p>
            </div>
            <div class="sim-score" data-sim-score><span><?= e(tr('Skóre')) ?></span><strong>0 / <?= (int)($sim['points'] ?? 5) ?></strong><small><?= e(tr('jen pro trénink')) ?></small></div>
        </div>

        <div class="sim-learning-loop" aria-label="<?= e(tr('Postup simulace')) ?>">
            <span class="active" data-sim-phase="predict"><?= e(tr('1 · Předpověz')) ?></span>
            <span data-sim-phase="experiment"><?= e(tr('2 · Experimentuj')) ?></span>
            <span data-sim-phase="evidence"><?= e(tr('3 · Čti důkaz')) ?></span>
            <span data-sim-phase="conclude"><?= e(tr('4 · Udělej závěr')) ?></span>
        </div>

        <div class="sim-prediction" data-sim-prediction data-correct="<?= (int)($prediction['correct'] ?? 0) ?>">
            <div><span><?= e(tr('Před spuštěním')) ?></span><strong<?=edu_content_lang_attr()?>><?= e((string)($prediction['q'] ?? tr('Co očekáváš?'))) ?></strong></div>
            <div class="sim-answer-row">
                <?php foreach (($prediction['options'] ?? []) as $i => $option): ?>
                    <button type="button" data-sim-predict="<?= (int)$i ?>"<?=edu_content_lang_attr()?>><b><?= chr(65 + (int)$i) ?></b><?= e((string)$option) ?></button>
                <?php endforeach; ?>
            </div>
            <p class="sim-inline-feedback" data-sim-predict-feedback hidden></p>
        </div>

        <div class="sim-workbench">
            <aside class="sim-controls-panel">
                <div class="sim-panel-kicker"><?= e(tr('Ovládání')) ?></div>
                <h4><?= e(tr('Změň podmínky')) ?></h4>
                <p<?=edu_content_lang_attr()?>><?= e((string)($sim['task'] ?? tr('Najdi správné nastavení.'))) ?></p>
                <div data-sim-controls></div>
                <div class="sim-control-actions"><button type="button" class="btn secondary" data-sim-reset><?= e(tr('Reset')) ?></button><button type="button" class="btn primary" data-sim-check-state><?= e(tr('Ověřit stav')) ?></button></div>
            </aside>

            <div class="sim-stage-panel">
                <div class="sim-stage-toolbar"><span><?= e(tr('Živá ukázka')) ?></span><strong data-sim-status><?= e(tr('Experimentuj')) ?></strong></div>
                <div class="sim-stage" data-sim-stage></div>
                <div class="sim-terminal" data-sim-terminal hidden></div>
            </div>

            <aside class="sim-evidence-panel">
                <div class="sim-panel-kicker"><?= e(tr('Evidence')) ?></div>
                <h4><?= e(tr('Co víme?')) ?></h4>
                <div class="sim-evidence-list known" data-sim-known><p class="empty"><?= e(tr('Zatím nic. Změň parametr nebo spusť test.')) ?></p></div>
                <h4><?= e(tr('Co ještě nevíme?')) ?></h4>
                <div class="sim-evidence-list unknown" data-sim-unknown><p><?= e(tr('Proveď experiment a odděluj důkaz od domněnky.')) ?></p></div>
            </aside>
        </div>

        <div class="sim-help-row">
            <div><strong><?= e(tr('Zasekl ses?')) ?></strong><span><?= e(tr('Nápovědy se odkrývají postupně. První je zdarma.')) ?></span></div>
            <button type="button" class="btn secondary" data-sim-hint><?= e(tr('Zobrazit nápovědu 1')) ?></button>
        </div>
        <div class="sim-hint-box" data-sim-hint-box hidden>
            <?php foreach ($hints as $i => $hint): ?><p data-sim-hint-item="<?= (int)$i ?>" hidden<?=edu_content_lang_attr()?>><span><?= (int)$i + 1 ?></span><?= e((string)$hint) ?></p><?php endforeach; ?>
        </div>

        <div class="sim-conclusion" data-sim-conclusion data-correct="<?= (int)($conclusion['correct'] ?? 0) ?>">
            <div class="sim-panel-kicker">Závěr</div>
            <h4<?=edu_content_lang_attr()?>><?= e((string)($conclusion['q'] ?? tr('Co z experimentu plyne?'))) ?></h4>
            <div class="sim-conclusion-options">
                <?php foreach (($conclusion['options'] ?? []) as $i => $option): ?>
                    <button type="button" data-sim-conclude="<?= (int)$i ?>"><b><?= chr(65 + (int)$i) ?></b><span<?=edu_content_lang_attr()?>><?= e((string)$option) ?></span></button>
                <?php endforeach; ?>
            </div>
            <div class="sim-final-feedback" data-sim-final-feedback hidden></div>
        </div>
    </section>
    <?php
}

function render_assessment_visual(string $classId, string $topic, string $questionText = ''): void
{
    $s = assessment_visual_spec($classId, $topic, $questionText);
    $kind = (string)$s['kind'];
    $uid = 'av-' . substr(hash('sha256', $classId . '|' . $topic . '|' . $questionText), 0, 10);
    $avTitle = (string)$s['title'];
    $avConcept = (string)$s['concept'];
    $avPrompt = (string)$s['prompt'];
    $avStateLabel = (string)($s['stateLabel'] ?? 'Změnit stav');
    $avEvidence = (string)$s['evidence'];
    ?>
    <section class="assessment-visual assessment-svg-visual" data-assessment-demo data-av-kind="<?= e($kind) ?>" id="<?= e($uid) ?>">
        <header class="visual-demo-head">
            <div><span><?= e(tr('Interaktivní model')) ?></span><strong><?= e(tr($avTitle)) ?></strong></div>
            <div class="visual-concept-chip"><?= e(tr($avConcept)) ?></div>
        </header>
        <div class="assessment-what"><span><?= e(tr('Co právě sleduješ')) ?></span><p><?= e(tr($avPrompt)) ?></p></div>
        <div class="assessment-svg-stage" data-av-stage>
            <?php if ($kind === 'dns'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t <?= e($uid) ?>-d">
                    <title id="<?= e($uid) ?>-t">Animace DNS překladu</title><desc id="<?= e($uid) ?>-d">Klient odešle DNS dotaz resolveru, který vrátí IP adresu cílového serveru.</desc>
                    <defs><marker id="<?= e($uid) ?>-arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 z" class="av-marker"/></marker></defs>
                    <path class="av-link" d="M145 110 H300" marker-end="url(#<?= e($uid) ?>-arrow)"/><path class="av-link" d="M385 110 H530" marker-end="url(#<?= e($uid) ?>-arrow)"/>
                    <g class="av-node"><rect x="35" y="70" width="110" height="80" rx="16"/><text x="90" y="104">KLIENT</text><text class="sub" x="90" y="128">portal.cz</text></g>
                    <g class="av-node"><rect x="300" y="70" width="90" height="80" rx="16"/><text x="345" y="104">DNS</text><text class="sub" x="345" y="128">resolver</text></g>
                    <g class="av-node"><rect x="530" y="70" width="80" height="80" rx="16"/><text x="570" y="104">WEB</text><text class="sub" x="570" y="128">10.20.0.30</text></g>
                    <circle class="av-packet" cx="145" cy="110" r="8"><animate attributeName="cx" values="145;300" dur="1.1s" begin="0.15s" fill="freeze"/></circle>
                    <circle class="av-packet alt" cx="390" cy="110" r="8"><animate attributeName="cx" values="390;530" dur="1.1s" begin="1.35s" fill="freeze"/></circle>
                    <text class="av-label" x="205" y="92">1 · dotaz</text><text class="av-label" x="438" y="92">2 · IP odpověď</text>
                </svg>
            <?php elseif ($kind === 'dhcp'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Animace DHCP DORA</title>
                    <line class="av-link" x1="110" y1="130" x2="530" y2="130"/>
                    <g class="av-node"><rect x="35" y="85" width="120" height="90" rx="16"/><text x="95" y="118">KLIENT</text><text class="sub" x="95" y="143">bez IP</text></g>
                    <g class="av-node"><rect x="485" y="85" width="120" height="90" rx="16"/><text x="545" y="118">DHCP</text><text class="sub" x="545" y="143">192.168.10.1</text></g>
                    <g class="av-dora"><circle cx="155" cy="105" r="7"><animate attributeName="cx" values="155;485" dur=".65s" begin="0s" fill="freeze"/></circle><text x="300" y="92">DISCOVER</text></g>
                    <g class="av-dora alt"><circle cx="485" cy="122" r="7"><animate attributeName="cx" values="485;155" dur=".65s" begin=".8s" fill="freeze"/></circle><text x="300" y="115">OFFER</text></g>
                    <g class="av-dora"><circle cx="155" cy="140" r="7"><animate attributeName="cx" values="155;485" dur=".65s" begin="1.6s" fill="freeze"/></circle><text x="300" y="154">REQUEST</text></g>
                    <g class="av-dora alt"><circle cx="485" cy="158" r="7"><animate attributeName="cx" values="485;155" dur=".65s" begin="2.4s" fill="freeze"/></circle><text x="300" y="178">ACK</text></g>
                </svg>
            <?php elseif ($kind === 'route'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Simulace rozhodnutí o routingu</title>
                    <rect class="av-zone" x="20" y="35" width="280" height="190" rx="22"/><rect class="av-zone alt-zone" x="340" y="35" width="280" height="190" rx="22"/>
                    <text class="av-zone-label" x="40" y="62">LAN A · 192.168.10.0/24</text><text class="av-zone-label" x="360" y="62">LAN B · 10.20.0.0/24</text>
                    <g class="av-node"><rect x="45" y="95" width="115" height="75" rx="14"/><text x="102" y="124">HOST</text><text class="sub" x="102" y="147">.42/24</text></g>
                    <g class="av-node"><rect x="255" y="95" width="130" height="75" rx="14"/><text x="320" y="124">GATEWAY</text><text class="sub" x="320" y="147">.1</text></g>
                    <g class="av-node"><rect x="475" y="95" width="120" height="75" rx="14"/><text x="535" y="124">CÍL</text><text class="sub" x="535" y="147">10.20.0.15</text></g>
                    <path class="av-link route-main" d="M160 132 H255 M385 132 H475"/><circle class="av-packet" cx="160" cy="132" r="9"><animate attributeName="cx" values="160;320;535" keyTimes="0;.45;1" dur="2s" begin=".15s" fill="freeze"/></circle>
                    <text class="av-label" x="190" y="112">mimo /24 → GW</text>
                </svg>
            <?php elseif ($kind === 'service'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Animace service stacku</title>
                    <?php $xs=[30,150,270,390,510]; $labs=['HOST','TCP','TLS','HTTP','APP']; foreach($xs as $i=>$x): ?>
                        <g class="av-layer layer-<?= $i ?>"><rect x="<?= $x ?>" y="90" width="100" height="80" rx="16"/><text x="<?= $x+50 ?>" y="126"><?= $labs[$i] ?></text><text class="sub" x="<?= $x+50 ?>" y="149"><?= ['10.20.0.15',':443','cert','GET /','200 OK'][$i] ?></text></g>
                        <?php if($i<4):?><path class="av-link" d="M<?= $x+100 ?> 130 H<?= $x+120 ?>"/><?php endif; ?>
                    <?php endforeach; ?>
                    <circle class="av-packet" cx="80" cy="185" r="8"><animate attributeName="cx" values="80;200;320;440;560" keyTimes="0;.25;.5;.75;1" dur="2.4s" begin=".15s" fill="freeze"/></circle>
                </svg>
            <?php elseif ($kind === 'ssh'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Animace SSH přihlášení</title>
                    <?php $xs=[35,180,325,470]; $labs=['TCP :22','sshd','KEY / AUTH','PRÁVA']; foreach($xs as $i=>$x): ?><g class="av-layer layer-<?= $i ?>"><rect x="<?= $x ?>" y="82" width="125" height="92" rx="16"/><text x="<?= $x+62 ?>" y="118"><?= $labs[$i] ?></text><text class="sub" x="<?= $x+62 ?>" y="145"><?= ['spojení','služba','identita','filesystem'][$i] ?></text></g><?php endforeach; ?>
                    <path class="av-link" d="M160 128 H180 M305 128 H325 M450 128 H470"/><circle class="av-packet" cx="96" cy="195" r="8"><animate attributeName="cx" values="96;242;388;532" dur="2.3s" begin=".15s" fill="freeze"/></circle>
                    <text class="av-error-label" x="388" y="66">chyba může vzniknout až zde</text>
                </svg>
            <?php elseif ($kind === 'probe'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Simulace diagnostické sondy</title>
                    <g class="av-node"><rect x="35" y="95" width="120" height="75" rx="15"/><text x="95" y="126">KLIENT</text><text class="sub" x="95" y="148">test</text></g>
                    <g class="av-node"><rect x="485" y="95" width="120" height="75" rx="15"/><text x="545" y="126">SERVER</text><text class="sub" x="545" y="148">host + služba</text></g>
                    <path class="av-link" d="M155 118 H485"/><path class="av-link muted" d="M485 150 H155"/>
                    <circle class="av-packet" cx="155" cy="118" r="8"><animate attributeName="cx" values="155;485" dur="1s" begin=".1s" fill="freeze"/></circle>
                    <circle class="av-packet alt" cx="485" cy="150" r="8"><animate attributeName="cx" values="485;155" dur="1s" begin="1.15s" fill="freeze"/></circle>
                    <g class="av-meter"><rect x="235" y="196" width="170" height="18" rx="9"/><rect class="fill" x="235" y="196" width="118" height="18" rx="9"><animate attributeName="width" values="0;118" dur="1.4s" begin="1.2s" fill="freeze"/></rect><text x="320" y="236">měření ≠ domněnka</text></g>
                </svg>
            <?php elseif ($kind === 'packet'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Animovaný packet capture</title>
                    <line class="av-time-axis" x1="70" y1="55" x2="70" y2="220"/>
                    <?php $ys=[75,110,145,180]; $labels=['ARP who-has?','DNS query A','TCP SYN','TCP SYN/ACK']; foreach($ys as $i=>$y): ?><g class="av-capture-row row-<?= $i ?>"><circle cx="70" cy="<?= $y ?>" r="7"/><rect x="95" y="<?= $y-14 ?>" width="390" height="28" rx="9"/><text x="112" y="<?= $y+5 ?>"><?= $labels[$i] ?></text><animate attributeName="opacity" values="0;1" dur=".25s" begin="<?= $i*.55 ?>s" fill="freeze"/></g><?php endforeach; ?>
                    <text class="av-label" x="510" y="82">L2</text><text class="av-label" x="510" y="117">DNS</text><text class="av-label" x="510" y="152">TCP</text><text class="av-label" x="510" y="187">TCP</text>
                </svg>
            <?php elseif ($kind === 'decision'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Rozhodovací smyčka při incidentu</title>
                    <?php $pts=[[95,80,'SYMPTOM'],[275,55,'HYPOTÉZA'],[455,80,'TEST'],[455,180,'DŮKAZ'],[275,205,'ZMĚNA'],[95,180,'VALIDACE']]; foreach($pts as $i=>$p): ?><g class="av-node decision-node n<?= $i ?>"><circle cx="<?= $p[0] ?>" cy="<?= $p[1] ?>" r="48"/><text x="<?= $p[0] ?>" y="<?= $p[1]+4 ?>"><?= $p[2] ?></text></g><?php endforeach; ?>
                    <path class="av-loop" d="M138 63 C185 35 220 32 230 42 M322 42 C370 35 405 45 415 62 M490 122 C508 145 505 165 490 176 M412 198 C370 218 325 219 320 214 M228 214 C180 220 140 205 130 192 M75 135 C64 115 68 98 80 91"/>
                    <circle class="av-packet" r="8"><animateMotion dur="3s" begin=".1s" fill="freeze" path="M95,80 C180,20 360,20 455,80 C520,130 500,190 455,180 C360,235 180,235 95,180 C55,145 55,105 95,80"/></circle>
                </svg>
            <?php elseif ($kind === 'contrast'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Porovnání nízkého a vysokého kontrastu</title>
                    <rect class="av-card-bg low" x="45" y="45" width="245" height="170" rx="22"/><text class="av-card-title low" x="168" y="112">EVENT</text><text class="av-card-copy low" x="168" y="145">nízký kontrast</text>
                    <rect class="av-card-bg high" x="350" y="45" width="245" height="170" rx="22"/><text class="av-card-title high" x="472" y="112">EVENT</text><text class="av-card-copy high" x="472" y="145">čitelný kontrast</text>
                    <g class="av-eye"><path d="M260 235 Q320 195 380 235 Q320 255 260 235Z"/><circle cx="320" cy="234" r="10"/></g>
                </svg>
            <?php elseif ($kind === 'hierarchy'): ?>
                <svg class="av-svg av-poster" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Animace cesty oka po plakátu</title>
                    <rect class="poster-bg" x="125" y="20" width="390" height="220" rx="18"/><text class="poster-kicker-svg" x="160" y="62">15 / 10 · BRNO</text><text class="poster-title-svg" x="160" y="120">NIGHT SHIFT</text><text class="poster-copy-svg" x="160" y="151">studentský vizuální večer</text><rect class="poster-cta-svg" x="160" y="178" width="150" height="35" rx="17"/><text class="poster-cta-text-svg" x="235" y="201">REGISTRACE →</text>
                    <circle class="av-focus-ring" cx="265" cy="108" r="54"><animate attributeName="cy" values="108;146;195" keyTimes="0;.55;1" dur="2.4s" begin=".15s" fill="freeze"/><animate attributeName="cx" values="265;260;235" keyTimes="0;.55;1" dur="2.4s" begin=".15s" fill="freeze"/><animate attributeName="r" values="54;34;27" keyTimes="0;.55;1" dur="2.4s" begin=".15s" fill="freeze"/></circle>
                </svg>
            <?php elseif ($kind === 'typography'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Porovnání typografické čitelnosti</title>
                    <g class="type-bad"><rect x="35" y="45" width="260" height="170" rx="18"/><text x="60" y="92">NADPIS PLAKÁTU</text><text x="60" y="117">datum místo registrace</text><text x="60" y="139">vše téměř stejně výrazné</text></g>
                    <g class="type-good"><rect x="345" y="45" width="260" height="170" rx="18"/><text class="hero" x="370" y="105">NADPIS</text><text class="meta" x="370" y="138">datum · místo</text><text class="cta" x="370" y="183">REGISTRACE →</text></g>
                </svg>
            <?php elseif ($kind === 'layout'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Mřížka a zarovnání v layoutu</title>
                    <rect class="layout-board" x="100" y="25" width="440" height="210" rx="18"/><?php foreach([160,230,300,370,440,510] as $x):?><line class="layout-grid-line" x1="<?= $x ?>" y1="25" x2="<?= $x ?>" y2="235"/><?php endforeach;?>
                    <rect class="layout-block b1" x="160" y="55" width="210" height="45" rx="8"/><rect class="layout-block b2" x="160" y="115" width="280" height="65" rx="8"/><rect class="layout-block b3" x="370" y="195" width="140" height="25" rx="8"/>
                    <path class="layout-guide" d="M160 42 V225"><animate attributeName="opacity" values=".2;1;.2" dur="2s" repeatCount="indefinite"/></path>
                </svg>
            <?php elseif ($kind === 'raster'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Raster versus vektor při zvětšení</title>
                    <g class="raster-side-svg"><rect x="50" y="45" width="235" height="170" rx="18"/><text x="167" y="77">RASTER</text><?php for($r=0;$r<5;$r++):for($c=0;$c<6;$c++):?><rect class="px p<?= ($r+$c)%3 ?>" x="<?= 95+$c*24 ?>" y="<?= 95+$r*22 ?>" width="22" height="20"/><?php endfor;endfor;?></g>
                    <g class="vector-side-svg"><rect x="355" y="45" width="235" height="170" rx="18"/><text x="472" y="77">VEKTOR</text><path d="M415 185 L475 95 L535 185 Z"/><circle cx="475" cy="155" r="24"/></g>
                </svg>
            <?php elseif ($kind === 'assets'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Ověření licence vizuálního zdroje</title>
                    <g class="asset-state bad"><rect x="70" y="55" width="210" height="150" rx="18"/><text class="big" x="175" y="125">?</text><text x="175" y="164">neznámý zdroj</text></g><path class="av-link" d="M300 130 H340"/>
                    <g class="asset-state good"><rect x="360" y="55" width="210" height="150" rx="18"/><text class="big" x="465" y="125">✓</text><text x="465" y="156">autor · licence</text><text class="sub" x="465" y="178">podmínky použití</text></g>
                </svg>
            <?php elseif ($kind === 'crop'): ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?> aria-labelledby="<?= e($uid) ?>-t"><title id="<?= e($uid) ?>-t">Simulace výřezu fotografie</title>
                    <defs><linearGradient id="<?= e($uid) ?>-photo" x1="0" x2="1"><stop offset="0" stop-color="#172554"/><stop offset="1" stop-color="#0f766e"/></linearGradient></defs>
                    <rect x="70" y="35" width="500" height="190" rx="18" fill="url(#<?= e($uid) ?>-photo)"/><circle class="crop-subject" cx="390" cy="122" r="52"/><rect class="crop-frame" x="105" y="60" width="380" height="140" rx="9"><animate attributeName="x" values="105;185;105" dur="3s" repeatCount="indefinite"/></rect><text class="crop-copy" x="130" y="110">HEADLINE</text><text class="crop-copy small" x="130" y="140">prostor pro text</text>
                </svg>
            <?php else: ?>
                <svg class="av-svg" viewBox="0 0 640 260" role="img"<?=edu_content_lang_attr()?>><title>Obecný model procesu</title><path class="av-link" d="M90 130 H550"/><?php foreach([[90,'VSTUP'],[245,'TEST'],[400,'DŮKAZ'],[550,'ZÁVĚR']] as $p):?><g class="av-node"><circle cx="<?= $p[0] ?>" cy="130" r="45"/><text x="<?= $p[0] ?>" y="135"><?= $p[1] ?></text></g><?php endforeach;?><circle class="av-packet" cx="90" cy="130" r="7"><animate attributeName="cx" values="90;245;400;550" dur="2.2s" fill="freeze"/></circle></svg>
            <?php endif; ?>
        </div>
        <div class="assessment-visual-controls">
            <button type="button" class="demo-toggle primary-demo" data-av-action="play"><?= e(tr('▶ Přehrát ukázku')) ?></button>
            <button type="button" class="demo-toggle" data-av-action="state"><?= e(tr($avStateLabel)) ?></button>
            <button type="button" class="demo-toggle" data-av-action="evidence"><?= e(tr('Co z toho můžu tvrdit?')) ?></button>
        </div>
        <div class="assessment-evidence" data-av-evidence hidden><span><?= e(tr('Důkaz / vysvětlení')) ?></span><p><?= e(tr($avEvidence)) ?></p></div>
    </section>
    <?php
}


function kb_support_model(string $classId, string $key, array $article, array $tour): array
{
    $graphics = in_array($classId, ['class_1a','class_2a'], true);
    $summary = trim((string)($article['summary'] ?? $tour['mental'] ?? ''));
    $example = trim((string)($article['example'] ?? ''));
    $steps = array_values(array_filter(array_map('strval', is_array($tour['steps'] ?? null) ? $tour['steps'] : [])));
    $mistakes = array_values(array_filter(array_map('strval', is_array($tour['mistakes'] ?? null) ? $tour['mistakes'] : [])));

    if ($graphics) {
        $analogy = 'Představ si návrh jako dopravní značení v budově: nejdůležitější informace musí být vidět první, další prvky ji podporují a uživatel nesmí hádat, kam pokračovat.';
        $try = 'Zakryj všechny dekorace a nech jen obsah. Dokážeš za 3 sekundy říct, co je nejdůležitější a co má člověk udělat dál? Potom vrať vizuální styl a ověř, že tuto cestu neposunul.';
        $loop = [
            ['Cíl','Co má člověk pochopit nebo udělat?'],
            ['Rozhodnutí','Změň jednu konkrétní věc.'],
            ['Test','Miniatura, viewport, kontrast nebo krátký user test.'],
            ['Iterace','Uprav podle evidence, ne podle náhody.'],
        ];
    } else {
        $analogy = 'Představ si diagnostiku jako hledání poruchy světla v domě: nejdřív zjistíš, kde elektřina ještě je, a teprve potom rozebíráš konkrétní vypínač. Každý test má zmenšit prostor možných příčin.';
        $try = 'Napiš si jen symptom. Vymysli dvě možné příčiny a jeden nejmenší test, jehož dva možné výsledky je od sebe odliší. Teprve po výsledku navrhni změnu.';
        $loop = [
            ['Symptom','Co přesně nefunguje bez domněnky proč?'],
            ['Test','Které nejmenší měření rozliší hypotézy?'],
            ['Důkaz','Co výsledek potvrzuje a co ještě ne?'],
            ['Validace','Oprav minimum a ověř celý řetězec.'],
        ];
    }

    return [
        'simple' => $summary !== '' ? $summary : 'Rozděl si problém na menší části a vždy ověř jednu věc.',
        'analogy' => $analogy,
        'steps' => $steps ?: ['Pojmenuj cíl nebo symptom.','Vyber jeden test.','Podívej se na výsledek.','Vysvětli, co z něj plyne.'],
        'example' => $example !== '' ? $example : ($graphics ? 'Vezmi vlastní návrh a změň jediný parametr. Porovnej před/po v miniatuře.' : 'Vezmi jeden symptom a zvol jeden příkaz, který otestuje konkrétní hypotézu.'),
        'mistake' => $mistakes[0] ?? ($graphics ? 'Typická chyba: měnit několik prvků současně a pak nevědět, proč návrh funguje lépe.' : 'Typická chyba: provést restart nebo konfigurační změnu dřív, než existuje důkaz, kterou hypotézu řeší.'),
        'try' => $try,
        'loop' => $loop,
    ];
}

function render_kb_help_ladder(string $classId, string $key, array $article, array $tour): void
{
    $m = kb_support_model($classId, $key, $article, $tour);
    ?>
    <section class="kb-help-ladder" data-explain-ladder data-adaptive-topic="<?= e($key) ?>">
      <div class="kb-help-head">
        <div><span class="eyebrow"><?= e(tr('Když tomu pořád nerozumíš')) ?></span><h3><?= e(tr('Vyber jiný způsob vysvětlení')) ?></h3><p><?= e(tr('Není problém potřebovat jiný příklad. Zkus cestu, která ti sedí; nápovědy se neznámkují.')) ?></p></div>
        <button type="button" class="btn secondary" data-explain-toggle aria-expanded="false"><?= e(tr('Vysvětli jinak')) ?></button>
      </div>
      <div class="kb-help-body" data-explain-body hidden>
        <div class="kb-help-tabs" role="tablist" aria-label="<?= e(tr('Alternativní způsoby vysvětlení')) ?>">
          <button type="button" class="active" data-explain-mode="simple"><?= e(tr('Jednoduše')) ?></button>
          <button type="button" data-explain-mode="analogy"><?= e(tr('Přirovnání')) ?></button>
          <button type="button" data-explain-mode="steps"><?= e(tr('Krok za krokem')) ?></button>
          <button type="button" data-explain-mode="example"><?= e(tr('Příklad')) ?></button>
          <button type="button" data-explain-mode="mistake"><?= e(tr('Typická chyba')) ?></button>
          <button type="button" data-explain-mode="try"><?= e(tr('Zkus si to')) ?></button>
          <button type="button" data-explain-mode="other"><?= e(tr('Video / jiný zdroj')) ?></button>
        </div>
        <div class="kb-help-panels">
          <article class="active" data-explain-panel="simple"><span><?= e(tr('Bez odborného balastu')) ?></span><p<?=edu_content_lang_attr()?>><?= e((string)$m['simple']) ?></p></article>
          <article data-explain-panel="analogy" hidden><span><?= e(tr('Přirovnání z běžného života')) ?></span><p<?=edu_content_lang_attr()?>><?= e((string)$m['analogy']) ?></p></article>
          <article data-explain-panel="steps" hidden><span><?= e(tr('Malé kroky')) ?></span><ol<?=edu_content_lang_attr()?>><?php foreach($m['steps'] as $step): ?><li><?= e((string)$step) ?></li><?php endforeach; ?></ol></article>
          <article data-explain-panel="example" hidden><span><?= e(tr('Konkrétní příklad')) ?></span><p<?=edu_content_lang_attr()?>><?= e((string)$m['example']) ?></p></article>
          <article data-explain-panel="mistake" hidden><span><?= e(tr('Co bývá špatně')) ?></span><p<?=edu_content_lang_attr()?>><?= e((string)$m['mistake']) ?></p></article>
          <article data-explain-panel="try" hidden><span><?= e(tr('30–90sekundový mini pokus')) ?></span><p<?=edu_content_lang_attr()?>><?= e((string)$m['try']) ?></p></article>
          <article data-explain-panel="other" hidden><span><?= e(tr('Ještě jiná cesta')) ?></span><p><?= tr_html('Otevři v horní liště {strong}, podívej se na alternativní české vysvětlení a potom se vrať. Než pokračuješ, zkus princip znovu vlastními slovy nebo na mini-příkladu.', ['strong' => '<strong>' . e(tr('Video a zdroje')) . '</strong>']) ?></p></article>
        </div>
        <div class="kb-tutor-lite" data-edu-tutor>
          <div><span><?= e(tr('EDU Tutor')) ?></span><strong><?= e(tr('Zeptej se na jednu konkrétní věc')) ?></strong><p><?= e(tr('Tutor pracuje jen s obsahem této lekce. U hodnocených checků tě navede, ale neprozradí hotovou odpověď.')) ?></p></div>
          <div class="kb-tutor-modes" role="group" aria-label="<?= e(tr('Režim tutora')) ?>"><button type="button" class="active" data-tutor-mode="guide"><?= e(tr('Naváděj mě')) ?></button><button type="button" data-tutor-mode="explain"><?= e(tr('Vysvětli')) ?></button><button type="button" data-tutor-mode="question"><?= e(tr('Ptej se mě')) ?></button><button type="button" data-tutor-mode="check"><?= e(tr('Zkontroluj vysvětlení')) ?></button><button type="button" data-tutor-mode="gap"><?= e(tr('Najdi díru v postupu')) ?></button></div><form data-edu-tutor-form><input name="question" maxlength="900" placeholder="<?= e(tr('Např. Proč tady nestačí restart?')) ?>" autocomplete="off"><button type="submit" class="btn secondary small"><?= e(tr('Zeptat se')) ?></button></form>
          <div class="kb-tutor-answer" data-edu-tutor-answer hidden></div>
        </div>
      </div>
    </section>
    <?php
}

function render_kb_concept_loop(string $classId, string $key, array $article, array $tour): void
{
    $m = kb_support_model($classId, $key, $article, $tour);
    ?>
    <section class="kb-concept-loop is-running" data-concept-loop aria-label="Animovaná ukázka principu">
      <div class="kb-loop-head"><div><span><?= e(tr('Animovaná ukázka')) ?></span><strong><?= e(tr('Sleduj jednu cestu od začátku k ověření')) ?></strong></div><button type="button" data-loop-replay><?= e(tr('↻ Přehrát znovu')) ?></button></div>
      <div class="kb-loop-track">
        <?php foreach($m['loop'] as $i=>$row): ?><div class="kb-loop-node" style="--loop-i:<?= (int)$i ?>"<?=edu_content_lang_attr()?>><i><?= $i+1 ?></i><strong><?= e((string)$row[0]) ?></strong><span><?= e((string)$row[1]) ?></span></div><?php endforeach; ?>
      </div>
    </section>
    <?php
}

function render_kb_tour(string $classId, string $key, array $article, array $tour, ?array $simulation = null, array $topicProgress = [], bool $compact = false): void
{
    $steps = is_array($tour['steps'] ?? null) ? $tour['steps'] : [];
    $mistakes = is_array($tour['mistakes'] ?? null) ? $tour['mistakes'] : [];
    $check = is_array($tour['check'] ?? null) ? $tour['check'] : [];
    $topicProgress = array_replace(['visual'=>false,'simulation'=>false,'steps'=>false,'deep'=>false,'check'=>false,'complete'=>false], $topicProgress);
    $sequence = ['visual'];
    if ($simulation) { $sequence[] = 'simulate'; }
    $sequence = array_merge($sequence, ['steps','deep']);
    $progressKeyMap = ['visual'=>'visual','simulate'=>'simulation','steps'=>'steps','deep'=>'deep'];
    $unlocked = [];
    foreach ($sequence as $i => $seqName) {
        if ($i === 0) { $unlocked[$seqName] = true; continue; }
        $prevName = $sequence[$i-1];
        $prevProgressKey = $progressKeyMap[$prevName];
        $unlocked[$seqName] = !empty($topicProgress[$prevProgressKey]);
    }
    ?>
    <div class="kb-tour<?= $compact ? ' compact-tour' : '' ?>" id="kb-tour-<?= e($key) ?>" data-kb-tour data-kb-class="<?= e($classId) ?>" data-topic="<?= e($key) ?>" data-kb-initial='<?= e(json_encode($topicProgress, JSON_UNESCAPED_UNICODE)) ?>'>
        <?php if (!$compact): ?>
        <div class="kb-tour-header">
            <div><div class="eyebrow"><?= e(tr('Knowledge tour')) ?></div><h2<?=edu_content_lang_attr()?>><?= e($article['title']) ?></h2><p class="lead"<?=edu_content_lang_attr()?>><?= e($article['summary']) ?></p></div>
            <div class="kb-tour-meta"><span><?= e((string)($tour['level'] ?? tr('Základ'))) ?></span><span><?= e((string)($tour['time'] ?? tr('5–10 min'))) ?></span><button type="button" class="kb-tour-start"><?= e(tr('Spustit tour')) ?></button></div>
        </div>
        <div class="kb-quick-strip">
            <div><span><?= e(tr('Co řeší')) ?></span><strong<?=edu_content_lang_attr()?>><?= e($article['summary']) ?></strong></div>
            <div><span><?= e(tr('První princip')) ?></span><strong<?=edu_content_lang_attr()?>><?= e((string)($article['body'][0] ?? $article['summary'])) ?></strong></div>
            <div><span><?= e(tr('Příklad')) ?></span><strong<?=edu_content_lang_attr()?>><?= e((string)($article['example'] ?? tr('Vyzkoušej princip v simulaci nebo praktickém labu.'))) ?></strong></div>
        </div>
        <?php endif; ?>
        <?php render_kb_help_ladder($classId, $key, $article, $tour); ?>

        <div class="kb-tour-tabs<?= $simulation ? ' has-simulation' : '' ?>" role="tablist" aria-label="<?= e(tr('Části vysvětlení')) ?>">
            <button type="button" class="active<?= !empty($topicProgress['visual']) ? ' done' : '' ?>" data-kb-tab="visual"><span>1</span><b><?= e(tr('Podívej se')) ?></b><small><?= e(tr('+5 XP')) ?></small></button>
            <?php if ($simulation): ?><button type="button" class="<?= !empty($topicProgress['simulation']) ? 'done' : '' ?>" data-kb-tab="simulate" <?= empty($unlocked['simulate']) ? 'disabled' : '' ?>><span>2</span><b><?= e(tr('Vyzkoušej')) ?></b><small><?= e(tr('+20 XP')) ?></small></button><?php endif; ?>
            <button type="button" class="<?= !empty($topicProgress['steps']) ? 'done' : '' ?>" data-kb-tab="steps" <?= empty($unlocked['steps']) ? 'disabled' : '' ?>><span><?= $simulation ? '3' : '2' ?></span><b><?= e(tr('Projdi postup')) ?></b><small><?= e(tr('+10 XP')) ?></small></button>
            <button type="button" class="<?= !empty($topicProgress['deep']) ? 'done' : '' ?>" data-kb-tab="deep" <?= empty($unlocked['deep']) ? 'disabled' : '' ?>><span><?= $simulation ? '4' : '3' ?></span><b><?= e(tr('Pochop proč')) ?></b><small><?= e(tr('+5 XP')) ?></small></button>
        </div>
        <div class="lesson-gate-note auto-flow-note"><span>✦</span><p><?= tr_html('{lead} Nic nemusíš „vybírat“. Jakmile dokončíš aktivitu, krok se sám uloží, XP se připíšou a otevře se další část.', ['lead' => '<strong>' . e(tr('Automatický postup.')) . '</strong>']) ?></p></div>

        <section class="kb-tab-panel active" data-kb-panel="visual">
            <div class="kb-mental-model"><span><?= e(tr('Mentální model')) ?></span><p<?=edu_content_lang_attr()?>><?= e((string)($tour['mental'] ?? $article['summary'])) ?></p></div>
            <div class="kb-topic-demo-intro"><div><span class="eyebrow"><?= e(tr('Co se tady skutečně děje')) ?></span><h3<?=edu_content_lang_attr()?>><?= e((string)$article['title']) ?></h3><p<?=edu_content_lang_attr()?>><?= e((string)($article['summary'] ?? $tour['mental'] ?? '')) ?></p></div><small><?= e(tr('Nejdřív sleduj změnu. Potom si model ovládej sám.')) ?></small></div>
            <?php render_reality_demo($classId, $key, $article); ?>
            <details class="kb-interactive-details principle-details"><summary><?= e(tr('Chci vidět čistý princip bez příběhu')) ?></summary><?php render_assessment_visual($classId, $key, (string)(($article['title'] ?? '') . ' ' . ($article['summary'] ?? ''))); ?></details>
            <details class="kb-interactive-details"><summary><?= e(tr('Chci si princip rozebrat a ovládat')) ?></summary><?php render_kb_visual(is_array($tour['visual'] ?? null) ? $tour['visual'] : []); ?></details>
            <div class="kb-tour-callout"><strong><?= e(tr('Co si z toho odnést')) ?></strong><p<?=edu_content_lang_attr()?>><?= e((string)($article['body'][0] ?? $article['summary'])) ?></p></div>
            <div class="auto-step-status<?= !empty($topicProgress['visual']) ? ' done' : '' ?>" data-auto-step-status="visual"><i><?= !empty($topicProgress['visual']) ? '✓' : '◎' ?></i><div><strong><?= !empty($topicProgress['visual']) ? e(tr('Vizuální krok uložen')) : e(tr('Vyzkoušej vizuální model')) ?></strong><span><?= !empty($topicProgress['visual']) ? e(tr('+5 XP už je započítáno.')) : e(tr('Po smysluplné interakci se krok uloží automaticky.')) ?></span></div><em><?= e(tr('+5 XP')) ?></em></div>
        </section>

        <?php if ($simulation): ?>
        <section class="kb-tab-panel" data-kb-panel="simulate" hidden>
            <?php render_kb_simulation($simulation); ?>
        </section>
        <?php endif; ?>

        <section class="kb-tab-panel" data-kb-panel="steps" hidden>
            <div class="kb-stepper" data-kb-stepper>
                <div class="kb-step-progress"><span><?= e(tr('Postup')) ?></span><strong><b data-kb-step-current>1</b> / <?= max(1,count($steps)) ?></strong></div>
                <div class="kb-step-track"><?php foreach($steps as $i=>$step): ?><button type="button" class="kb-step-dot<?= $i===0?' active':'' ?>" data-kb-step-dot="<?= $i ?>" aria-label="<?= e(tr('Krok {n}', ['n' => $i+1])) ?>" <?= ($i>0 && empty($topicProgress['steps'])) ? 'disabled' : '' ?>></button><?php endforeach; ?></div>
                <?php foreach($steps as $i=>$step): ?><article class="kb-step-slide<?= $i===0?' active':'' ?>" data-kb-step="<?= $i ?>" <?= $i===0?'':'hidden' ?>><span><?= e(tr('KROK {n}', ['n' => str_pad((string)($i+1),2,'0',STR_PAD_LEFT)])) ?></span><h3<?=edu_content_lang_attr()?>><?= e((string)$step) ?></h3><p><?= e((string)($i === count($steps)-1 ? tr('Poslední krok není „hotovo“, ale ověření výsledku. Umět potvrdit opravu je součást technické práce.') : tr('Nezkoušej přeskočit dopředu. Každý krok má snížit počet možných příčin a dát ti důkaz pro další rozhodnutí.'))) ?></p></article><?php endforeach; ?>
                <div class="kb-step-actions"><button type="button" class="btn secondary kb-step-prev" disabled><?= e(tr('← Předchozí')) ?></button><button type="button" class="btn primary kb-step-next"><?= e(tr('Další krok →')) ?></button></div>
            </div>
            <?php if($mistakes): ?><div class="kb-mistakes"><div class="eyebrow"><?= e(tr('Pozor na slepé uličky')) ?></div><h3><?= e(tr('Nejčastější chyby')) ?></h3><div class="kb-mistake-grid"><?php foreach($mistakes as $m): ?><div><span>!</span><p<?=edu_content_lang_attr()?>><?= e((string)$m) ?></p></div><?php endforeach; ?></div></div><?php endif; ?>
        </section>

        <section class="kb-tab-panel" data-kb-panel="deep" hidden>
            <div class="kb-deep-grid">
                <div class="kb-deep-copy"><div class="eyebrow"><?= e(tr('Deep dive')) ?></div><h3><?= e(tr('Co se děje doopravdy')) ?></h3><?php foreach(($article['body'] ?? []) as $paragraph): ?><p<?=edu_content_lang_attr()?>><?= e((string)$paragraph) ?></p><?php endforeach; ?></div>
                <aside class="kb-example-card"><span><?= e(tr('Praktický příklad')) ?></span><code<?=edu_content_lang_attr()?>><?= e((string)($article['example'] ?? '')) ?></code><p><?= e(tr('Zkus před spuštěním příkazu vlastními slovy říct, jaký výsledek očekáváš a co by opačný výsledek znamenal.')) ?></p></aside>
            </div>
            <details class="kb-more-explanation"><summary><?= e(tr('Chci ještě podrobnější vysvětlení')) ?></summary><div><p><?= tr_html('{lead} nezačínej názvem technologie, ale pozorovatelným symptomem. Potom si polož otázku, jaký nejmenší test rozliší dvě nejpravděpodobnější příčiny.', ['lead' => '<strong>' . e(tr('Jak o tom přemýšlet:')) . '</strong>']) ?></p><p><?= tr_html('{lead} dokážeš nejen zopakovat definici, ale předpovědět výsledek změny a vysvětlit, co konkrétní měření potvrzuje i co ještě nepotvrzuje.', ['lead' => '<strong>' . e(tr('Jak poznat, že tomu rozumíš:')) . '</strong>']) ?></p><?php if (in_array($classId, ['class_1a','class_2a'], true)): ?><p><?= tr_html('{lead} pracuj v cyklu „cíl → vizuální rozhodnutí → náhled → test čitelnosti → úprava → export“. U grafiky není důležité jen to, že něco vypadá hezky, ale že umíš vysvětlit, proč je hierarchie, kontrast nebo spacing vhodný pro konkrétní sdělení.', ['lead' => '<strong>' . e(tr('Jak to použít v praxi:')) . '</strong>']) ?></p><?php else: ?><p><?= tr_html('{lead} zapisuj si „symptom → hypotéza → test → důkaz → změna → validace“. Tato struktura se přenáší do sítí i serverové diagnostiky.', ['lead' => '<strong>' . e(tr('Jak to použít v praxi:')) . '</strong>']) ?></p><?php endif; ?></div></details>
            <div class="auto-step-status reading<?= !empty($topicProgress['deep']) ? ' done' : '' ?>" data-auto-step-status="deep"><i><?= !empty($topicProgress['deep']) ? '✓' : '◔' ?></i><div><strong><?= !empty($topicProgress['deep']) ? e(tr('Vysvětlení uložené')) : e(tr('Krátké čtení se sleduje automaticky')) ?></strong><span><?= !empty($topicProgress['deep']) ? e(tr('+5 XP už je započítáno.')) : e(tr('Až bude tato část chvíli aktivní, systém ji sám označí jako projitou.')) ?></span><b aria-hidden="true"><u></u></b></div><em><?= e(tr('+5 XP')) ?></em></div>
        </section>

        <?php if (!empty($topicProgress['deep'])): $mlStudentKey=adaptive_student_key($classId); ?>
        <div class="ml-learning-sequence">
          <?php ml_render_worked_example($classId,$mlStudentKey,$key,$article); ?>
          <?php ml_render_browser_lab($classId,$mlStudentKey,$key,$article); ?>
          <?php ml_render_transfer($classId,$mlStudentKey,$key,$article); ?>
          <?php ml_render_explain_back($classId,$mlStudentKey,$key,$article); ?>
          <?php ml_render_knowledge_map($classId,$key,$GLOBALS['modules'][$classId]); ?>
          <?php ml_render_class_tips($classId,$mlStudentKey,$key); ?>
        </div>
        <?php endif; ?>

        <section class="kb-quiz-launch<?= !empty($topicProgress['deep']) ? ' ready' : ' locked' ?>" data-kb-quiz-launch>
            <div><div class="eyebrow"><?= e(tr('Samostatný knowledge check')) ?></div><h3><?= e(tr('Ověř si téma na vlastní stránce')) ?></h3><p><?= e(tr('Test se otevře v čistém režimu bez dlouhé stránky. Odpovědi se při každém novém pokusu promíchají a správná možnost nemá stabilní pozici.')) ?></p></div>
            <?php $quizParams=['topic'=>$key,'kb_class'=>$classId]; if(isset($_GET['reference']) && (string)$_GET['reference']==='1') $quizParams['reference']=1; if(isset($_GET['return']) && (string)$_GET['return']==='next') $quizParams['return']='next'; $quizUrl = module_url('kb_quiz', $quizParams); ?>
            <?php if (!empty($topicProgress['deep'])): ?>
                <a class="btn primary" data-kb-quiz-link href="<?= e($quizUrl) ?>"><?= !empty($topicProgress['check']) ? e(tr('Znovu otevřít knowledge check')) : e(tr('Spustit knowledge check →')) ?></a>
            <?php else: ?>
                <button class="btn primary" type="button" data-kb-quiz-link data-quiz-url="<?= e($quizUrl) ?>" disabled><?= e(tr('Nejdřív dokonči vysvětlení')) ?></button>
            <?php endif; ?>
            <div class="kb-complete-card<?= !empty($topicProgress['complete']) ? ' completed' : '' ?>" data-kb-complete-card><div><span><?= !empty($topicProgress['complete']) ? '✓' : '○' ?></span><div><strong data-kb-complete-title><?= !empty($topicProgress['complete']) ? e(tr('Lekce dokončena')) : e(tr('Poslední krok: knowledge check')) ?></strong><p data-kb-complete-copy><?= !empty($topicProgress['complete']) ? e(tr('Všechny povinné kroky jsou splněné. Další lekce je odemčená.')) : e(tr('Správná odpověď v samostatném knowledge checku dokončí tuto lekci a připíše XP.')) ?></p></div></div><div class="lesson-xp-pill"><?= e(tr('+15 XP check · +25 XP dokončení')) ?></div></div>
        </section>
    </div>
    <?php
}
