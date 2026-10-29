<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · záložka cockpitu `kompetence75` (česky): návrh kompetencí a cest 2.A / 4.A, pokrytí témat lekcí, vazba na ŠVP,
 * náhled cest a schválení / vrácení návrhu (POST kp75_approve / kp75_return, CSRF a politika v teacher.php, content.manage).
 */

require_once dirname(__DIR__, 2) . '/src/assessment/competencies_v75.php';

function comp75_tab_url(string $classId): string
{
    return '?' . http_build_query(['tab' => 'kompetence75', 'class' => $classId]);
}

/** Klíče témat lekcí 1–28 třídy (z balíčku lekce, bez celého modelu). @return array<int,list<string>> */
function comp75_lesson_topics(string $classId, array $module): array
{
    $out = [];
    for ($n = 1; $n <= 28; $n++) {
        $bundle = function_exists('lm71_bundle') ? lm71_bundle($classId, $module, $n) : [];
        $out[$n] = array_map('strval', array_keys((array)($bundle['topics'] ?? [])));
    }
    return $out;
}

function comp75_render_tab(array $modules, string $classId, string $csrf): void
{
    $allowed = function_exists('c70_classes') ? c70_classes() : COMP75_CLASSES;
    $classes = array_values(array_intersect(COMP75_CLASSES, $allowed));
    $requested = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : $classId;
    $class = in_array($requested, $classes, true) ? $requested : ($classes[0] ?? '');
    echo '<section class="teacher-page-head c70-head"><div><div class="eyebrow">Výuka · fáze E</div><h1>Kompetence a cesty 2.A a 4.A</h1>'
        . '<p>Návrh katalogu kompetencí a výukových cest pro 2.A (Grafika a webdesign II) a 4.A (Provoz sítí a služeb). Dokud návrh neschválíte, '
        . 'žáci ho nevidí a nic se jim nepočítá. Po schválení se třída zapojí stejně jako 1.A a 3.A: mapa kompetencí v profilu, výukové cesty a důkazy z testů, labu a lekcí.</p></div></section>';
    if ($class === '') {
        echo function_exists('c70_empty_html') ? c70_empty_html('Návrh se týká jen 2.A a 4.A.', 'Žádnou z těchto tříd nemáte v rozsahu – kompetence 1.A a 3.A najdete v záložce Kompetence.', [['kompetence', 'Kompetence (1.A, 3.A)']]) : '';
        return;
    }
    echo '<nav class="c70-picker" aria-label="Třída"><ul class="c70-classes">';
    foreach ($classes as $cid) echo '<li><a href="' . e(comp75_tab_url($cid)) . '"' . ($cid === $class ? ' aria-current="page"' : '') . '><strong>' . e((string)($modules[$cid]['name'] ?? $cid)) . '</strong></a></li>';
    echo '</ul></nav>';
    $subject = (string)comp75_subject_for_class($class);
    $catalog = comp75_draft_catalog()[$subject];
    $flat = comp75_flat($subject);
    $paths = comp75_draft_paths($class);
    $status = comp75_status($class);
    $coverage = comp75_coverage($class, comp75_lesson_topics($class, is_array($modules[$class] ?? null) ? $modules[$class] : []));
    $svpMap = comp75_svp_map();
    $outcomes = function_exists('svp72_outcomes') ? svp72_outcomes() : [];
    $svpIds = [];
    foreach ($flat as $id => $c) foreach ($svpMap[$id] ?? [] as $o) $svpIds[$o] = true;
    $pct = $coverage['total'] > 0 ? (int)round($coverage['mapped'] / $coverage['total'] * 100) : 0;
    if (function_exists('c70_kpis_html')) echo c70_kpis_html([
        ['Stav návrhu', COMP75_STATUS[$status['status']], $status['changed'] ? 'katalog se od schválení změnil' : ($status['at'] !== '' ? date('j. n. Y', (int)strtotime($status['at'])) . ' · ' . $status['by'] : 'čeká na vaše rozhodnutí'),
            $status['status'] === 'schvaleno' && !$status['changed'] ? 'ok' : 'warn'],
        ['Kompetence', (string)count($flat), (string)$catalog['label'], ''],
        ['Výukové cesty', (string)count($paths), 'každá s ověřením ve 3 variantách', count($paths) >= 3 ? 'ok' : 'warn'],
        ['Témata lekcí s kompetencí', $pct . ' %', $coverage['mapped'] . ' z ' . $coverage['total'] . ' témat L1–L28 (cíl ≥ 95 %)', $pct >= 95 ? 'ok' : 'warn'],
        ['Výsledky ŠVP', (string)count($svpIds), 'navázané výsledky z dokumentu školy', ''],
    ], 'Souhrn návrhu');
    echo comp75_decision_html($class, $status, $csrf);
    echo '<section class="c70-card c70-wide" aria-labelledby="kp75-cat"><h2 id="kp75-cat">Katalog: ' . e((string)$catalog['label']) . '</h2>'
        . '<p class="c70-muted">Úroveň 1–4 = pamatuje / použije / analyzuje / tvoří. Vazba na ŠVP cituje výsledky z dokumentu školy (ŠVP v72); lekce jsou ty, jejichž témata kompetence pokrývá.</p>'
        . '<div class="kp75-scroll"><table class="kp75-table"><caption class="c70-sr">Kompetence návrhu</caption><thead><tr><th scope="col">Kompetence</th><th scope="col">Úroveň</th><th scope="col">Výsledky ŠVP</th><th scope="col">Lekce</th></tr></thead><tbody>';
    foreach ($flat as $id => $c) {
        $lessons = array_keys(array_filter($coverage['lessons'], static fn(array $ids): bool => in_array($id, $ids, true)));
        $svp = [];
        foreach ($svpMap[$id] ?? [] as $o) $svp[] = '<li><span class="c70-chip">' . e($o) . '</span> ' . e((string)($outcomes[$o]['text'] ?? 'výsledek nenalezen')) . '</li>';
        echo '<tr><th scope="row"><span class="kp75-area">' . e((string)$c['area_label']) . '</span> ' . e((string)$c['label']) . '</th><td>' . (int)$c['level'] . ' · ' . e(comp62_level_label((int)$c['level'])) . '</td>'
            . '<td>' . ($svp !== [] ? '<ul class="kp75-svp">' . implode('', $svp) . '</ul>' : '<span class="c70-muted">chybí vazba</span>') . '</td>'
            . '<td>' . ($lessons !== [] ? e('L' . implode(', L', $lessons)) : '<span class="c70-muted">žádná lekce</span>') . '</td></tr>';
    }
    echo '</tbody></table></div>';
    if ($coverage['missing'] !== []) echo '<p class="c70-muted">Témata bez kompetence: ' . e(implode(', ', $coverage['missing'])) . '.</p>';
    echo '</section>';
    echo comp75_paths_html($paths, $flat);
}

function comp75_decision_html(string $class, array $status, string $csrf): string
{
    $can = function_exists('teacher_permission') && teacher_permission('content.manage');
    $html = '<section class="c70-card c70-wide kp75-decision" aria-labelledby="kp75-dec"><h2 id="kp75-dec">Rozhodnutí</h2>';
    $html .= match (true) {
        $status['status'] === 'schvaleno' && $status['changed'] => '<p role="status"><strong>Schváleno, ale katalog se mezitím změnil.</strong> Třída zůstává zapojená. Zkontrolujte změny a potvrďte je znovu (řízená změna verze katalogu).</p>',
        $status['status'] === 'schvaleno' => '<p role="status"><strong>Schváleno.</strong> Třída je zapojená do kompetencí a cest. Vrácením k úpravě ji z pilotu zase vyřadíte (dosavadní důkazy žáků zůstanou uložené).</p>',
        $status['status'] === 'vraceno' => '<p role="status"><strong>Vráceno k úpravě:</strong> ' . e($status['note']) . '</p>',
        default => '<p role="status"><strong>Návrh čeká na schválení.</strong> Projděte katalog a cesty níže. Žáci zatím nic nevidí.</p>',
    };
    if (!$can) return $html . '<p class="c70-muted">Schvalovat může učitel s oprávněním ke správě obsahu (asistent jen čte).</p></section>';
    $hidden = '<input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="class_id" value="' . e($class) . '"><input type="hidden" name="return_tab" value="kompetence75">';
    if ($status['status'] !== 'schvaleno' || $status['changed']) {
        $html .= '<form method="post" class="kp75-form">' . $hidden . '<input type="hidden" name="action" value="kp75_approve">'
            . '<label class="kp75-check"><input type="checkbox" name="confirm" value="1" required> Prošel/prošla jsem katalog i cesty a schvaluji je pro třídu.</label>'
            . '<button class="btn primary" type="submit">' . ($status['changed'] ? 'Potvrdit změněný katalog' : 'Schválit a zapojit třídu') . '</button></form>';
    }
    return $html . '<form method="post" class="kp75-form">' . $hidden . '<input type="hidden" name="action" value="kp75_return">'
        . '<label for="kp75-note-' . e($class) . '">Co upravit (povinné při vrácení)</label><textarea id="kp75-note-' . e($class) . '" name="note" rows="2" maxlength="' . COMP75_NOTE_MAX . '" required></textarea>'
        . '<button class="btn secondary" type="submit">Vrátit k úpravě</button></form></section>';
}

function comp75_paths_html(array $paths, array $flat): string
{
    $html = '<section class="c70-card c70-wide" aria-labelledby="kp75-paths"><h2 id="kp75-paths">Výukové cesty (náhled)</h2>';
    if ($paths === []) return $html . '<p class="c70-muted">Návrh zatím nemá žádnou cestu.</p></section>';
    $types = ['explain' => 'vysvětlení', 'pre' => 'předpověď', 'parsons' => 'pořadí kroků', 'retrieval' => 'vybavování', 'verify' => 'ověření', 'reflect' => 'reflexe'];
    $html .= '<p class="c70-muted">Každá cesta: vysvětlení → předpověď → poskládání postupu → vybavování → ověření (3 varianty po 4 otázkách) → reflexe a opakování po 1/3/7/14/30 dnech. Cesty nedávají body ani XP.</p><ul class="kp75-paths">';
    foreach ($paths as $p) {
        $steps = array_map(static fn(array $s): string => (string)($types[(string)$s['type']] ?? $s['type']), array_filter((array)$p['steps'], 'is_array'));
        $verify = 0;
        foreach ((array)$p['steps'] as $s) if (is_array($s) && (string)$s['type'] === 'verify') $verify = count((array)$s['variants']);
        $html .= '<li class="kp75-path"><h3>' . e((string)$p['title']) . '</h3><p>' . e((string)$p['goal']) . '</p>'
            . '<p class="c70-muted">' . e((string)($flat[(string)$p['competency']]['label'] ?? $p['competency'])) . ' · ' . (int)$p['minutes'] . ' min · ověření ve ' . $verify . ' variantách</p>'
            . '<p class="kp75-steps">' . e(implode(' → ', $steps)) . '</p></li>';
    }
    return $html . '</ul></section>';
}
