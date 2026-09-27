<?php

declare(strict_types=1);

/**
 * Přesměrování course/topics/tools/knowledgebase na Materiály (v56), pokud chybí ?classic.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

// v56 · jedna cesta učení: lekce (teorie → test → projekt → odevzdání), materiály, výsledky.
if (in_array($view, ['course', 'topics', 'tools', 'knowledgebase'], true) && !isset($_GET['classic'])) {
    $v56Section = $view === 'topics' ? 'temata' : ($view === 'tools' || $view === 'knowledgebase' ? ($view === 'tools' ? 'programy' : 'temata') : 'lekce');
    redirect_to('?view=materialy&sekce=' . $v56Section);
}
