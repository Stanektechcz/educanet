<?php

declare(strict_types=1);

/**
 * ?view=precache – seznam klíčových assetů s aktuální verzí (v61 · rychlost).
 * Service worker (sw.js) si ho stáhne při instalaci a předčte z něj přesně ty URL, které stránky skutečně žádají
 * (asset_url() = ?v=<čas změny souboru>). Obsah je veřejný a neosobní (jen cesty k souborům); odpověď se necachuje.
 * Je v groupě views_early, aby šla stáhnout i žákovi, který si ještě nezměnil heslo.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'precache') {
    $precachePaths = [
        'assets/app.css', 'assets/mastery.css', 'assets/student-ui-v50-7-7.css', 'assets/ui-v51.css', 'assets/tutorial-v52.css',
        'assets/session-v53.css', 'assets/tokens-v61.css', 'assets/brand-v54.css', 'assets/components-v61.css', 'assets/nav-v61.css', 'assets/student-v55.css', 'assets/learning-v56.css', 'assets/i18n-v59.css',
        'assets/cognitive-v43.css', 'assets/learning-studio-v44.css', 'assets/visual-simulation-v45.css', 'assets/paths-card-v63.css',
        'assets/app.js', 'assets/student-ui-v50-7-7.js', 'assets/ui-v51.js', 'assets/student-v55.js', 'assets/learning-v56.js', 'assets/nav-v61.js', 'assets/i18n-v58.js',
        'assets/cognitive-v43.js', 'assets/learning-studio-v44.js', 'assets/visual-simulation-v45.js',
    ];
    $precacheUrls = [];
    foreach ($precachePaths as $precachePath) {
        if (is_file(dirname(__DIR__, 2) . '/' . $precachePath)) $precacheUrls[] = asset_url($precachePath);
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['version' => 1, 'urls' => $precacheUrls], JSON_UNESCAPED_SLASHES);
    exit;
}
