<?php

declare(strict_types=1);

/**
 * ?view=graphics_studio – Design Studio (1.A/2.A).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'graphics_studio' && in_array($classId, ['class_1a', 'class_2a'], true)) {
    render_header(tr('Studio'), $module);
    ?>
    <section class="studio-hero">
        <div><div class="eyebrow"><?= e(tr('Grafický sandbox')) ?> · <?= edu_cs((string)$module['name']) ?></div><h1><?= e(tr('Nejdřív si návrh otestuj. Potom ho přenes do Canvy.')) ?></h1><p><?= e(tr('Studio je bezpečné hřiště pro hierarchy, kontrast, grid a export. Výsledek není finální grafika — je to rozhodovací mapa, podle které pak postavíš plakát v Canvě.')) ?></p></div>
        <div class="studio-path"><span><?= e(tr('1 · brief')) ?></span><span><?= e(tr('2 · layout')) ?></span><span><?= e(tr('3 · test')) ?></span><span><?= e(tr('4 · handoff')) ?></span><span><?= e(tr('5 · Canva')) ?></span><span><?= e(tr('6 · QA')) ?></span><span><?= e(tr('7 · porovnání')) ?></span></div>
    </section>

    <section class="design-studio" data-design-studio data-ds-class="<?= e((string)$classId) ?>">
        <aside class="studio-controls" data-studio-stage="brief">
            <div class="studio-section"><span class="studio-step">01</span><div><h2><?= e(tr('Brief')) ?></h2><p><?= e(tr('Vyber situaci a napiš jen to, co musí divák pochopit.')) ?></p></div></div>
            <label><?= e(tr('Typ plakátu')) ?><select data-ds="brief"><option value="event"><?= e(tr('Školní / komunitní event')) ?></option><option value="esport"><?= e(tr('Esport turnaj')) ?></option><option value="concert"><?= e(tr('Koncert / festival')) ?></option><option value="product"><?= e(tr('Produkt / promo')) ?></option></select></label>
            <label><?= e(tr('Headline')) ?><input data-ds="title" maxlength="36" value="DESIGN NIGHT"></label>
            <label><?= e(tr('Klíčová informace')) ?><input data-ds="info" maxlength="52" value="18. 9. · 18:00 · Brno"></label>
            <label>CTA<input data-ds="cta" maxlength="30" value="REGISTRUJ SE"></label>

            <div class="studio-divider"></div>
            <div class="studio-section"><span class="studio-step">02</span><div><h2><?= e(tr('Vizuální systém')) ?></h2><p><?= e(tr('Měň málo parametrů, ale sleduj jejich dopad.')) ?></p></div></div>
            <label><?= e(tr('Formát')) ?><select data-ds="format"><option value="portrait">1080 × 1350 · <?= e(tr('post')) ?></option><option value="square">1080 × 1080 · square</option><option value="story">1080 × 1920 · story</option><option value="a4">A4 · <?= e(tr('tisk')) ?></option></select></label>
            <label>Grid<select data-ds="grid"><option value="4"><?= e(tr('4 sloupce')) ?></option><option value="6"><?= e(tr('6 sloupců')) ?></option><option value="8"><?= e(tr('8 sloupců')) ?></option></select></label>
            <label><?= e(tr('Titulek')) ?> <b data-ds-out="titleSize">64</b><input type="range" min="34" max="92" value="64" data-ds="titleSize"></label>
            <label><?= e(tr('Info')) ?> <b data-ds-out="infoSize">24</b><input type="range" min="14" max="40" value="24" data-ds="infoSize"></label>
            <label>Spacing <b data-ds-out="spacing">24</b><input type="range" min="8" max="48" value="24" data-ds="spacing"></label>
            <div class="studio-color-row"><label><?= e(tr('Pozadí')) ?><input type="color" value="#111218" data-ds="bg"></label><label><?= e(tr('Text')) ?><input type="color" value="#f7f7f4" data-ds="text"></label><label><?= e(tr('Akcent')) ?><input type="color" value="#a8ff3e" data-ds="accent"></label></div>
            <label class="studio-toggle"><input type="checkbox" data-ds="showGrid"><span><?= e(tr('Zobrazit grid')) ?></span></label>
            <label class="studio-toggle"><input type="checkbox" data-ds="gray"><span><?= e(tr('Grayscale test')) ?></span></label>
            <label class="studio-toggle"><input type="checkbox" data-ds="blur"><span><?= e(tr('3sekundový blur test')) ?></span></label>
            <div class="studio-stage-commit"><strong><?= e(tr('1 · Brief a systém')) ?></strong><span><?= e(tr('Headline, informace, CTA a základní layout jsou připravené.')) ?></span><button type="button" class="btn primary" data-studio-commit="brief"><?= e(tr('Potvrdit základ')) ?></button></div>
        </aside>

        <div class="studio-preview-column" data-studio-stage="visual">
            <div class="studio-preview-head"><div><span><?= e(tr('Živý návrh')) ?></span><strong><?= e(tr('Experimentuj bez strachu')) ?></strong></div><div class="studio-score" data-ds-score><?= e(tr('0 / 4 kontroly')) ?></div></div>
            <div class="studio-canvas-wrap"><div class="studio-canvas" data-ds-canvas><div class="studio-grid-overlay" data-ds-grid></div><span class="studio-kicker">EDUCANET · VISUAL LAB</span><h2 data-ds-title>DESIGN NIGHT</h2><p data-ds-info>18. 9. · 18:00 · Brno</p><button data-ds-cta>REGISTRUJ SE →</button><small><?= e(tr('Vizuální koncept · zkušební konfigurace')) ?></small></div></div>
            <div class="studio-thumbnail-row"><div><span><?= e(tr('Thumbnail test')) ?></span><div class="studio-thumb" data-ds-thumb><b>DESIGN NIGHT</b><i>18. 9. · Brno</i><em>REGISTRUJ SE</em></div></div><div class="studio-readability"><span><?= e(tr('Kontrast hlavního textu')) ?></span><strong data-ds-contrast>—</strong><small data-ds-contrast-note><?= e(tr('měním…')) ?></small></div></div>
            <div class="studio-coach" data-ds-coach><strong><?= e(tr('Design coach')) ?></strong><ul data-ds-feedback></ul></div>
            <div class="studio-stage-commit"><strong><?= e(tr('2 · Vizuální test')) ?></strong><span><?= e(tr('Pro pokračování musí Design coach hlásit 4 / 4 kontroly.')) ?></span><button type="button" class="btn primary" data-studio-commit="visual"><?= e(tr('Ověřit vizuál')) ?></button></div>
        </div>

        <aside class="studio-export" data-studio-stage="export">
            <div class="studio-section"><span class="studio-step">03</span><div><h2><?= e(tr('Export preset')) ?></h2><p><?= e(tr('Nastav výstup ještě před přesunem do Canvy.')) ?></p></div></div>
            <label><?= e(tr('Cíl')) ?><select data-ds="target"><option value="social"><?= e(tr('Sociální sítě')) ?></option><option value="web">Web</option><option value="print"><?= e(tr('Tisk')) ?></option></select></label>
            <label><?= e(tr('Formát')) ?><select data-ds="filetype"><option value="png">PNG</option><option value="jpg">JPG</option><option value="webp">WebP</option><option value="pdf">PDF</option></select></label>
            <label><?= e(tr('Kvalita')) ?> <b data-ds-out="quality">85</b>%<input type="range" min="50" max="100" value="85" data-ds="quality"></label>
            <div class="export-advice" data-ds-export-advice></div>
            <div class="studio-actions"><button type="button" class="btn primary" data-ds-download-png><?= e(tr('Stáhnout náhled PNG')) ?></button><button type="button" class="btn secondary" data-ds-download-config><?= e(tr('Exportovat konfiguraci')) ?></button><button type="button" class="btn tertiary" data-ds-copy><?= e(tr('Copy checklist pro Canvu')) ?></button></div>
            <details class="studio-explain"><summary><?= e(tr('Co přesně mám přenést do Canvy?')) ?></summary><ol><li><?= e(tr('Rozměr dokumentu a grid.')) ?></li><li><?= e(tr('Poměr velikostí headline / info / CTA.')) ?></li><li><?= e(tr('Barvy a kontrastní vztah.')) ?></li><li><?= e(tr('Spacing — ne absolutní pixely, ale rytmus.')) ?></li><li><?= e(tr('Export preset podle cílového média.')) ?></li></ol><p><?= e(tr('Studio není generátor hotového plakátu. Nutí tě nejdřív udělat důležitá rozhodnutí a v Canvě je potom vědomě realizovat.')) ?></p></details>
            <div class="studio-stage-commit"><strong><?= e(tr('3 · Exportní plán')) ?></strong><span><?= e(tr('Potvrď cíl, formát a kvalitu. Potom se odemkne Canva Transfer Guide.')) ?></span><button type="button" class="btn primary" data-studio-commit="export"><?= e(tr('Potvrdit preset')) ?></button></div>
        </aside>
    </section>

    <section class="canva-handoff" data-canva-handoff data-studio-stage="handoff">
        <header class="handoff-head">
            <div>
                <div class="eyebrow">Canva Transfer Guide · <?= edu_cs((string)$module['name']) ?></div>
                <h2><?= e(tr('Převeď svůj systém do Canvy krok za krokem')) ?></h2>
                <p><?= tr_html('Nemusíš kopírovat pixely. Přenes {vztahy}: formát, grid, hierarchii, paletu, spacing a exportní pravidla. Teprve potom přidej obraz, styl a detail.', ['vztahy' => '<strong>' . e(tr('vztahy')) . '</strong>']) ?></p>
            </div>
            <div class="handoff-progress" aria-label="<?= e(tr('Průběh Canva transferu')) ?>">
                <strong data-handoff-progress>0 / 7</strong>
                <span><?= e(tr('kroků připraveno')) ?></span>
                <div><i data-handoff-progressbar></i></div>
            </div>
        </header>

        <div class="handoff-layout">
            <nav class="handoff-nav" aria-label="<?= e(tr('Kroky přenosu do Canvy')) ?>">
                <button type="button" class="active" data-handoff-tab="dimensions"><span>01</span><b><?= e(tr('Dokument')) ?></b><small><?= e(tr('rozměr a formát')) ?></small></button>
                <button type="button" data-handoff-tab="grid"><span>02</span><b>Grid</b><small><?= e(tr('vodítka a okraje')) ?></small></button>
                <button type="button" data-handoff-tab="type"><span>03</span><b><?= e(tr('Hierarchie')) ?></b><small><?= e(tr('textové role')) ?></small></button>
                <button type="button" data-handoff-tab="palette"><span>04</span><b><?= e(tr('Paleta')) ?></b><small><?= e(tr('barvy a kontrast')) ?></small></button>
                <button type="button" data-handoff-tab="export"><span>05</span><b><?= e(tr('Export')) ?></b><small><?= e(tr('preset výsledku')) ?></small></button>
                <button type="button" data-handoff-tab="check"><span>06</span><b>Checklist</b><small><?= e(tr('kontrola před exportem')) ?></small></button>
                <button type="button" data-handoff-tab="compare"><span>07</span><b><?= e(tr('Porovnání')) ?></b><small>Studio vs. Canva</small></button>
            </nav>

            <div class="handoff-content">
                <article class="handoff-panel active" data-handoff-panel="dimensions">
                    <div class="handoff-panel-title"><span>01</span><div><h3><?= e(tr('Založ správný dokument')) ?></h3><p><?= e(tr('Rozměr je první technické rozhodnutí. Pokud ho změníš až na konci, rozpadne se kompozice i spacing.')) ?></p></div></div>
                    <div class="handoff-spec-grid">
                        <div><span><?= e(tr('Canva rozměr')) ?></span><strong data-ho-dimensions>1080 × 1350 px</strong><small data-ho-dimension-note><?= e(tr('Digitální dokument na výšku.')) ?></small></div>
                        <div><span><?= e(tr('Poměr stran')) ?></span><strong data-ho-ratio>4 : 5</strong><small><?= e(tr('V Canvě použij „Vlastní velikost“, pokud preset není přesný.')) ?></small></div>
                        <div><span><?= e(tr('Bezpečná zóna')) ?></span><strong data-ho-safezone>cca 86 px</strong><small><?= e(tr('Nedávej klíčový text těsně k okraji.')) ?></small></div>
                    </div>
                    <div class="handoff-demo handoff-size-demo">
                        <div class="size-sheet" data-ho-size-sheet><i></i><b>SAFE AREA</b><span data-ho-size-label>1080 × 1350</span></div>
                        <div class="handoff-instructions">
                            <ol>
                                <li><?= tr_html('Canva → {a} → {b}.', ['a' => '<strong>' . e(tr('Vytvořit design')) . '</strong>', 'b' => '<strong>' . e(tr('Vlastní velikost')) . '</strong>']) ?></li>
                                <li><?= e(tr('Zadej rozměr z této karty.')) ?></li>
                                <li><?= tr_html('Jednotku nastav na {px} pro digitál nebo odpovídající tiskový formát.', ['px' => '<strong>px</strong>']) ?></li>
                                <li><?= e(tr('Nevkládej zatím obrázky. Nejprve vytvoř strukturu.')) ?></li>
                            </ol>
                            <button type="button" class="mini-action" data-copy-step="dimensions"><?= e(tr('Kopírovat nastavení dokumentu')) ?></button>
                        </div>
                    </div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="dimensions"><span><?= e(tr('Dokument v Canvě mám založený ve správném rozměru.')) ?></span></label>
                </article>

                <article class="handoff-panel" data-handoff-panel="grid">
                    <div class="handoff-panel-title"><span>02</span><div><h3><?= e(tr('Přenes grid, ne náhodné pozice')) ?></h3><p><?= e(tr('Grid je kostra. Nemusí být ve výsledku vidět, ale pomůže držet společné hrany, rytmus a negativní prostor.')) ?></p></div></div>
                    <div class="handoff-grid-demo">
                        <div class="handoff-grid-sheet" data-ho-grid-sheet></div>
                        <div class="handoff-instructions">
                            <div class="spec-line"><span><?= e(tr('Doporučený grid')) ?></span><strong data-ho-grid><?= e(tr('4 sloupce')) ?></strong></div>
                            <div class="spec-line"><span><?= e(tr('Vnější okraj')) ?></span><strong data-ho-margin><?= e(tr('8 % šířky')) ?></strong></div>
                            <div class="spec-line"><span><?= e(tr('Mezera mezi sloupci')) ?></span><strong data-ho-gutter>cca 2–3 %</strong></div>
                            <ol>
                                <li><?= tr_html('Zapni v Canvě {a}.', ['a' => '<strong>' . e(tr('pravítka a vodítka')) . '</strong>']) ?></li>
                                <li><?= e(tr('Vytvoř levý a pravý bezpečný okraj.')) ?></li>
                                <li><?= e(tr('Rozděl pracovní plochu na počet sloupců ze Studia.')) ?></li>
                                <li><?= e(tr('Headline, info a CTA přichyť ke společným hranám.')) ?></li>
                            </ol>
                            <details><summary><?= e(tr('Proč neřešíme přesné pixely?')) ?></summary><p><?= e(tr('Studio přenáší vztahy. V Canvě můžeš drobně upravit mezery podle konkrétního fontu a obrazu, ale společné hrany a rytmus by měly zůstat.')) ?></p></details>
                        </div>
                    </div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="grid"><span><?= e(tr('Mám vytvořená vodítka a hlavní prvky sedí na společných hranách.')) ?></span></label>
                </article>

                <article class="handoff-panel" data-handoff-panel="type">
                    <div class="handoff-panel-title"><span>03</span><div><h3><?= e(tr('Přenes textové role')) ?></h3><p><?= e(tr('Nekopíruj slepě velikost z browseru. Zachovej poměr dominance mezi headline, informací a CTA.')) ?></p></div></div>
                    <div class="type-transfer">
                        <div class="type-preview" data-ho-type-preview>
                            <span>HEADLINE</span>
                            <strong data-ho-title-preview>DESIGN NIGHT</strong>
                            <p data-ho-info-preview>18. 9. · 18:00 · Brno</p>
                            <em data-ho-cta-preview>REGISTRUJ SE</em>
                        </div>
                        <div class="handoff-instructions">
                            <div class="hierarchy-bars">
                                <div><span><?= e(tr('Headline')) ?></span><i data-ho-title-bar></i><b data-ho-title-size>64</b></div>
                                <div><span><?= e(tr('Info')) ?></span><i data-ho-info-bar></i><b data-ho-info-size>24</b></div>
                                <div><span>CTA</span><i style="width:42%"></i><b><?= e(tr('akcent')) ?></b></div>
                            </div>
                            <ul class="explain-list">
                                <li><?= tr_html('{b} první věc, kterou divák přečte.', ['b' => '<strong>' . e(tr('Headline:')) . '</strong>']) ?></li>
                                <li><?= tr_html('{b} odpověď na „kdy / kde / co“.', ['b' => '<strong>' . e(tr('Info:')) . '</strong>']) ?></li>
                                <li><?= tr_html('{b} jasná akce, ne další odstavec.', ['b' => '<strong>CTA:</strong>']) ?></li>
                            </ul>
                            <div class="handoff-callout"><?= tr_html('{b} pokud v miniatuře nepoznáš headline, hierarchie je příliš slabá.', ['b' => '<b>' . e(tr('Pravidlo:')) . '</b>']) ?></div>
                        </div>
                    </div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="type"><span><?= e(tr('V Canvě je pořadí čtení headline → info → CTA jasné i v miniatuře.')) ?></span></label>
                </article>

                <article class="handoff-panel" data-handoff-panel="palette">
                    <div class="handoff-panel-title"><span>04</span><div><h3><?= e(tr('Přenes barevnou paletu a znovu ověř kontrast')) ?></h3><p><?= e(tr('Barvy z Design Studia jsou výchozí systém. Pokud je v Canvě změníš, znovu zkontroluj kontrast — obrázek může celý vztah barev změnit.')) ?></p></div></div>
                    <div class="palette-transfer">
                        <div class="palette-swatches">
                            <button type="button" data-copy-color="bg"><i data-ho-bg></i><span><?= e(tr('Pozadí')) ?></span><b data-ho-bg-code>#111218</b></button>
                            <button type="button" data-copy-color="text"><i data-ho-text></i><span><?= e(tr('Text')) ?></span><b data-ho-text-code>#f7f7f4</b></button>
                            <button type="button" data-copy-color="accent"><i data-ho-accent></i><span><?= e(tr('Akcent')) ?></span><b data-ho-accent-code>#a8ff3e</b></button>
                        </div>
                        <div class="contrast-lab">
                            <div><span><?= e(tr('Text / pozadí')) ?></span><strong data-ho-contrast>—</strong><small data-ho-contrast-state><?= e(tr('počítám…')) ?></small></div>
                            <div class="contrast-sample" data-ho-contrast-sample><b>Aa</b><span><?= e(tr('Malý text')) ?></span></div>
                            <p><?= tr_html('{b} pro zkopírování HEX hodnoty do schránky. V Canvě ji vlož do výběru barvy.', ['b' => '<strong>' . e(tr('Klikni na swatch')) . '</strong>']) ?></p>
                            <details><summary><?= e(tr('Co když používám fotografii?')) ?></summary><p><?= e(tr('Kontrast počítej vůči skutečnému místu, kde text leží. Pokud je fotografie rušivá, použij gradient, overlay, tmavší plochu nebo změň umístění textu místo náhodného obrysu a stínu.')) ?></p></details>
                        </div>
                    </div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="palette"><span><?= e(tr('Paletu jsem přenesl/a a kontrast ověřil/a na skutečném Canva návrhu.')) ?></span></label>
                </article>

                <article class="handoff-panel" data-handoff-panel="export">
                    <div class="handoff-panel-title"><span>05</span><div><h3><?= e(tr('Nastav export podle média')) ?></h3><p><?= e(tr('Export není poslední náhodné kliknutí. Formát souboru, rozměr a kvalita mají odpovídat tomu, kde bude plakát použit.')) ?></p></div></div>
                    <div class="export-transfer">
                        <div class="export-ticket">
                            <span><?= e(tr('CÍL')) ?></span><strong data-ho-export-target><?= e(tr('SOCIÁLNÍ SÍTĚ')) ?></strong>
                            <hr>
                            <span><?= e(tr('SOUBOR')) ?></span><strong data-ho-export-type>PNG</strong>
                            <hr>
                            <span><?= e(tr('KVALITA')) ?></span><strong data-ho-export-quality>85 %</strong>
                            <small data-ho-export-extra><?= e(tr('RGB · kontrola na mobilním náhledu')) ?></small>
                        </div>
                        <div class="handoff-instructions">
                            <ol>
                                <li><?= tr_html('V Canvě otevři {b}.', ['b' => '<strong>' . e(tr('Sdílet → Stáhnout')) . '</strong>']) ?></li>
                                <li><?= e(tr('Zvol formát z exportního ticketu.')) ?></li>
                                <li><?= e(tr('U digitálu zachovej cílový rozměr; nezvětšuj malý raster násilím.')) ?></li>
                                <li><?= tr_html('Po exportu otevři soubor {b}.', ['b' => '<strong>' . e(tr('mimo Canvu')) . '</strong>']) ?></li>
                                <li><?= e(tr('Zkontroluj text, ořez, barvy a datovou velikost.')) ?></li>
                            </ol>
                            <div class="handoff-callout" data-ho-export-warning></div>
                        </div>
                    </div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="export"><span><?= e(tr('Exportní preset mám nastavený a výsledný soubor jsem otevřel/a mimo editor.')) ?></span></label>
                </article>

                <article class="handoff-panel" data-handoff-panel="check">
                    <div class="handoff-panel-title"><span>06</span><div><h3><?= e(tr('Visual QA před odevzdáním')) ?></h3><p><?= e(tr('Teď design na chvíli přestaň „vylepšovat“ a začni ho testovat. Checklist odhalí víc než další efekt.')) ?></p></div></div>
                    <div class="qa-grid" data-ho-checklist>
                        <label><input type="checkbox" data-ho-check="headline"><span><b><?= e(tr('3sekundový test')) ?></b><?= e(tr('Headline poznám okamžitě.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="thumb"><span><b><?= e(tr('Thumbnail')) ?></b><?= e(tr('Hlavní sdělení funguje i v malém.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="contrast"><span><b><?= e(tr('Kontrast')) ?></b><?= e(tr('Text nezaniká v pozadí ani obrazu.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="grid"><span><b>Grid</b><?= e(tr('Prvky mají společné hrany a rytmus.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="spacing"><span><b>Spacing</b><?= e(tr('Mezery vytvářejí skupiny, nejsou náhodné.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="fonts"><span><b><?= e(tr('Typografie')) ?></b><?= e(tr('Max. 2 rodiny písem a jasné role.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="cta"><span><b>CTA</b><?= e(tr('Akce je snadno dohledatelná.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="source"><span><b><?= e(tr('Zdroje')) ?></b><?= e(tr('Obrazový materiál mám vlastní / licenčně ověřený.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="export"><span><b><?= e(tr('Export')) ?></b><?= e(tr('Soubor odpovídá cílovému médiu.')) ?></span></label>
                        <label><input type="checkbox" data-ho-check="outside"><span><b><?= e(tr('Mimo editor')) ?></b><?= e(tr('Finální soubor jsem skutečně otevřel/a a zkontroloval/a.')) ?></span></label>
                    </div>
                    <div class="qa-summary"><strong data-ho-qa-count>0 / 10</strong><span data-ho-qa-message><?= e(tr('Začni kontrolou největšího problému, ne dekorací.')) ?></span></div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="check"><span><?= e(tr('Visual QA mám dokončené a největší nalezený problém jsem opravil/a.')) ?></span></label>
                </article>

                <article class="handoff-panel" data-handoff-panel="compare">
                    <div class="handoff-panel-title"><span>07</span><div><h3><?= e(tr('Porovnej plán s výsledným Canva plakátem')) ?></h3><p><?= e(tr('Nahraj export z Canvy. Soubor se zpracuje jen lokálně v prohlížeči pro srovnání a tímto krokem se neodevzdává na server.')) ?></p></div></div>
                    <div class="compare-upload">
                        <label class="compare-drop"><input type="file" accept="image/png,image/jpeg,image/webp" data-ho-canva-file><span>＋</span><strong><?= e(tr('Nahrát Canva export')) ?></strong><small><?= e(tr('PNG / JPG / WebP · doporučeně finální velikost')) ?></small></label>
                        <div class="compare-file-meta" data-ho-file-meta><span><?= e(tr('Zatím není vybraný soubor.')) ?></span></div>
                    </div>
                    <div class="compare-stage" data-ho-compare-stage hidden>
                        <div class="compare-card">
                            <header><span>Studio</span><small><?= e(tr('plán / systém')) ?></small></header>
                            <div class="compare-planned" data-ho-planned>
                                <b data-ho-compare-title>DESIGN NIGHT</b>
                                <p data-ho-compare-info>18. 9. · 18:00 · Brno</p>
                                <em data-ho-compare-cta>REGISTRUJ SE</em>
                            </div>
                        </div>
                        <div class="compare-card">
                            <header><span>Canva</span><small><?= e(tr('finální realizace')) ?></small></header>
                            <div class="compare-image-wrap"><img alt="<?= e(tr('Náhled finálního Canva plakátu')) ?>" data-ho-canva-preview></div>
                        </div>
                    </div>
                    <div class="compare-results" data-ho-compare-results hidden>
                        <div class="compare-metrics">
                            <div><span><?= e(tr('Poměr stran')) ?></span><strong data-ho-aspect-score>—</strong><small data-ho-aspect-note></small></div>
                            <div><span><?= e(tr('Paleta')) ?></span><strong data-ho-palette-score>—</strong><small data-ho-palette-note></small></div>
                            <div><span>Checklist</span><strong data-ho-check-score>—</strong><small data-ho-check-note></small></div>
                        </div>
                        <div class="compare-reflection">
                            <h4><?= e(tr('Co se změnilo proti plánu?')) ?></h4>
                            <label><span><?= e(tr('Jedno záměrné vylepšení')) ?></span><textarea rows="2" data-ho-reflect-good placeholder="<?= e(tr('Např. změnil/a jsem kompozici obrázku, protože…')) ?>"></textarea></label>
                            <label><span><?= e(tr('Jedna věc, kterou bych ještě opravil/a')) ?></span><textarea rows="2" data-ho-reflect-fix placeholder="<?= e(tr('Např. CTA je v miniatuře pořád příliš slabé…')) ?>"></textarea></label>
                        </div>
                    </div>
                    <label class="handoff-done"><input type="checkbox" data-handoff-done="compare"><span><?= e(tr('Porovnal/a jsem plán s finálním exportem a umím vysvětlit alespoň jednu změnu.')) ?></span></label>
                </article>
            </div>
        </div>
    </section>

    <section class="panel studio-transfer">
        <div><div class="eyebrow"><?= e(tr('Přenos do Canvy')) ?></div><h2><?= e(tr('Hotová konfigurace ≠ hotový design')) ?></h2><p><?= e(tr('V Canvě teď doplň obrazový materiál, detailní typografii a vlastní vizuální styl. Hierarchii, kontrast a grid už ale neměň bez důvodu — právě jsi je otestoval/a.')) ?></p></div>
        <div class="button-row"><a class="btn primary" href="?view=graphics_guide" data-studio-finish-link><?= e(tr('Pokračovat na zadání')) ?></a><a class="btn secondary" href="?view=knowledgebase"><?= e(tr('Potřebuji vysvětlení')) ?></a></div>
    </section>
    <?php
    render_footer();
    exit;
}
