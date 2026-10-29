<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/teamgames_v64_roles.php';

/**
 * EDUCANET v64 · žákovské šablony týmových her: moje role a retrospektiva po hře (3 otázky).
 * Role je rada „co dělám v týmu“ a mění se mezi koly; přínos se nezobrazuje číslem ostatním.
 */

function tg64_render_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/arena-v64.css?v=64.0')) . '">' . "\n";
}

/** Panel role (běžící hra s rolemi). */
function tg64_render_role_panel(array $session, string $studentKey): void
{
    $role = tg64_role_for($session, $studentKey);
    if ($role === null) return;
    tg64_render_assets();
    echo '<section class="tg64-card" aria-labelledby="tg64-role"><h2 id="tg64-role">' . e(tr('Tvoje role v týmu: {role}', ['role' => tg64_role_label($role)])) . '</h2>'
        . '<p>' . e(tg64_role_hint($role)) . '</p><p class="tg64-note">' . e(tr('Role se mezi hrami střídá, aby si každý vyzkoušel všechny.')) . '</p></section>';
}

/** Formulář retrospektivy po dohrané hře (bez JS, POST tg64_retro). */
function tg64_render_retro_form(array $session, string $classId, string $studentKey, string $csrf): void
{
    $teamId = tg58_team_of($session, $studentKey);
    if ($teamId === null) return;
    tg64_render_assets();
    $done = tg64_retro_done($classId, (string)$session['id'], $teamId, $studentKey);
    echo '<section class="tg64-card" aria-labelledby="tg64-retro"><h2 id="tg64-retro">' . e(tr('Co si z hry odnášíme')) . '</h2>';
    if ($done) echo '<p role="status">' . e(tr('Díky, retrospektiva je odeslaná. Můžeš ji přepsat.')) . '</p>';
    echo '<p class="tg64-note">' . e(tr('Odpovědi uvidí jen učitel, bez jmen. Stačí jedna věta, maximálně {n} znaků.', ['n' => TG64_RETRO_MAX])) . '</p>'
        . '<form method="post" class="tg64-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="tg64_retro">'
        . '<input type="hidden" name="game" value="' . e((string)$session['id']) . '">';
    foreach (tg64_retro_questions() as $i => $q) {
        echo '<label for="tg64-q' . $i . '">' . e($q) . '</label><textarea id="tg64-q' . $i . '" name="a[' . $i . ']" rows="2" maxlength="' . TG64_RETRO_MAX . '"></textarea>';
    }
    echo '<button class="btn primary" type="submit">' . e(tr('Odeslat')) . '</button></form></section>';
}
