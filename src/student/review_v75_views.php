<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/student/review_v75.php';
require_once dirname(__DIR__, 2) . '/src/assessment/taskbank_v75_views.php';

/**
 * EDUCANET v75 · „Opakování před písemkou“ – žákovské UI (?view=opakovani). Otázky z exit ticketů se zobrazí jako samotest
 * (správná odpověď až po rozbalení, bez JS), úlohy k procvičení jsou vlastní varianty žáka z banky úloh. Texty přes tr().
 */

function rv75_render_student_page(string $classId, string $studentKey): void
{
    echo tb75_css_link() . '<div class="l75-wrap ds78"><h1>' . e(tr('Opakování před písemkou')) . '</h1>';
    $pack = rv75_pack($classId);
    if ($pack === null) {
        echo '<div class="l75-empty" role="status"><strong>' . e(tr('Teď tu žádné opakování není.')) . '</strong> '
            . e(tr('Před písemkou ho připraví učitel z toho, co celé třídě dělalo potíže. Mezitím můžeš procvičovat v úlohách s variantami nebo ve výukových cestách.')) . '</div>'
            . (tb75_tasks_for_class($classId) !== [] ? '<p><a class="btn secondary" href="?view=ulohy">' . e(tr('Úlohy s variantami')) . '</a></p>' : '') . '</div>';
        return;
    }
    echo '<p class="l75-lead">' . e(tr('Lekce {from}–{to}. Vybrali jsme to, co dělalo potíže celé třídě – nejde o tvoje chyby a nic se nehodnotí.', ['from' => (int)$pack['from'], 'to' => (int)$pack['to']])) . '</p>';
    $exits = array_values(array_filter((array)($pack['exits'] ?? []), 'is_array'));
    echo '<section class="l75-task" aria-labelledby="rv75-ex"><h2 id="rv75-ex">' . e(tr('Na co si dát pozor')) . '</h2>';
    if ($exits === []) {
        echo '<p class="l75-muted">' . e(tr('V exit ticketech těchto lekcí nebyla žádná otázka, na kterou by třída často odpověděla špatně.')) . '</p>';
    } else {
        echo '<p class="l75-muted">' . e(tr('Zkus si nejdřív odpovědět sám/sama, pak rozbal správnou odpověď.')) . '</p><ol class="rv75-list">';
        foreach ($exits as $x) {
            echo '<li class="rv75-q"><p class="l75-prompt"' . edu_content_lang_attr() . '><span class="l75-chip">' . e(tr('Lekce {n}', ['n' => (int)$x['lesson']])) . '</span> ' . e((string)$x['question']) . '</p><ul class="rv75-options"' . edu_content_lang_attr() . '>';
            foreach ((array)$x['options'] as $o) echo '<li>' . e((string)$o) . '</li>';
            echo '</ul><details><summary>' . e(tr('Ukázat správnou odpověď')) . '</summary><p><strong' . edu_content_lang_attr() . '>' . e((string)($x['options'][(int)$x['correct']] ?? '')) . '</strong></p>'
                . ((string)$x['explanation'] !== '' ? '<p class="l75-muted"' . edu_content_lang_attr() . '>' . e((string)$x['explanation']) . '</p>' : '') . '</details></li>';
        }
        echo '</ol>';
    }
    echo '</section>';
    $tasks = array_values(array_filter((array)($pack['tasks'] ?? []), static fn($t): bool => is_string($t) && in_array($t, tb75_tasks_for_class($classId), true)));
    echo '<section aria-labelledby="rv75-tk"><h2 id="rv75-tk">' . e(tr('Procvič si')) . '</h2>';
    if ($tasks === []) echo '<p class="l75-muted">' . e(tr('K těmto lekcím teď nejsou úlohy s variantami.')) . '</p>';
    foreach ($tasks as $t) tb75_render_task($classId, $studentKey, $t, 'opakovani');
    echo '</section></div>';
}
