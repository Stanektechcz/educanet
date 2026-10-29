<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/student/portfolio_v75.php';

/**
 * EDUCANET v75 · portfolio – žákovská sekce „Připnuté práce“ na stránce ?view=portfolio (pod výběrem projektů v65).
 * Formuláře bez JS (třída p65-noprint – při tisku se skryjí), tisk / PDF tlačítkem stránky v65. Texty přes tr().
 * v79: nová práce se připíná po krocích (portfolio_v79.php); úprava reflexe a odebrání zůstávají u každé práce.
 */

function pf75_t_kind(string $kind): string
{
    return match ($kind) { 'link' => tr('Odkaz na práci'), 'task' => tr('Výsledek úlohy'), default => tr('Projekt') };
}

function pf75_render_student_section(string $classId, string $studentKey): void
{
    if ($studentKey === '') return;
    $pins = pf75_pins($classId, $studentKey);
    $csrf = '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
    echo '<link rel="stylesheet" href="' . e(function_exists('asset_url') ? asset_url('assets/learn-v75.css?v=75.0') : 'assets/learn-v75.css?v=75.0') . '">'
        . '<div class="p65 ds78"><section class="p65-card" aria-labelledby="pf75-h"><h2 id="pf75-h">' . e(tr('Připnuté práce')) . '</h2>'
        . '<p class="p65-noprint">' . e(tr('Připni odkaz na svou grafiku nebo web, vyřešenou úlohu nebo projekt a napiš, co ses naučil/a. Vidíš to jen ty a tvoji učitelé, kteří ti můžou napsat komentář.')) . '</p>';
    if ($pins === []) {
        echo '<div class="l75-empty" role="status"><p>' . e(tr('Zatím tu nic není. Začni třeba odkazem na práci, na kterou jsi hrdý/á.')) . '</p>'
            . '<p><a class="btn primary" href="#sp79-top">' . e(tr('Připnout první práci')) . '</a></p></div>';   // v79.1: prázdný stav s akcí (krokový formulář níže)
    } else {
        echo '<ol class="pf75-list">';
        foreach ($pins as $id => $p) echo pf75_pin_html((string)$id, $p, $csrf);
        echo '</ol>';
    }
    if (count($pins) < PF75_MAX) {
        require_once dirname(__DIR__, 2) . '/src/student/portfolio_v79.php';   // v79: stepper Odkaz → Reflexe → Uložit (akce pf75_s_pin beze změny)
        echo pf79_render($classId, $studentKey, $pins, $csrf);
    }
    else echo '<p class="l75-muted p65-noprint">' . e(tr('Máš připnuto nejvíc {n} prací. Další přidáš, až nějakou odebereš.', ['n' => PF75_MAX])) . '</p>';
    echo '</section></div>';
}

/**
 * v76 · připnuté práce do lokálního HTML exportu v65 (port65v_send_export): bez odkazů (URL jen jako text), bez skriptů a formulářů,
 * s reflexí žáka a komentářem učitele. Prázdné portfolio = prázdný řetězec (export ukáže jen výběr projektů).
 */
function pf75_export_section_html(string $classId, string $studentKey): string
{
    if ($studentKey === '') return '';
    $pins = pf75_pins($classId, $studentKey);
    if ($pins === []) return '';
    $html = '<h2>' . e(tr('Připnuté práce')) . '</h2>';
    foreach ($pins as $p) {
        $html .= '<article class="item"><h3>' . e((string)$p['title']) . '</h3><p class="meta">' . e(pf75_t_kind((string)$p['kind']))
            . ((string)($p['url'] ?? '') !== '' ? ' · ' . e((string)$p['url']) : '') . '</p>'
            . ((string)($p['reflection'] ?? '') !== '' ? '<p><strong>' . e(tr('Moje reflexe:')) . '</strong> ' . e((string)$p['reflection']) . '</p>' : '')
            . (is_array($p['comment'] ?? null) ? '<p><strong>' . e(tr('Komentář učitele:')) . '</strong> ' . e((string)($p['comment']['text'] ?? '')) . '</p>' : '') . '</article>';
    }
    return $html;
}

function pf75_pin_html(string $id, array $p, string $csrf): string
{
    $title = e((string)$p['title']);
    if ((string)$p['kind'] === 'link' && ($url = function_exists('safe_url') ? safe_url((string)$p['url']) : '') !== '') {
        $title = '<a href="' . e($url) . '" rel="noopener noreferrer" target="_blank">' . $title . '</a> <span class="pf75-url">' . e((string)$p['url']) . '</span>';
    }
    $html = '<li class="pf75-pin"><h3><span class="l75-chip">' . e(pf75_t_kind((string)$p['kind'])) . '</span> ' . $title . '</h3>';
    $html .= (string)$p['reflection'] !== '' ? '<p class="pf75-reflection"><strong>' . e(tr('Moje reflexe:')) . '</strong> ' . e((string)$p['reflection']) . '</p>' : '<p class="l75-muted">' . e(tr('Reflexi zatím nemáš napsanou.')) . '</p>';
    if (is_array($p['comment'] ?? null)) {
        $html .= '<p class="pf75-comment"><strong>' . e(tr('Komentář učitele:')) . '</strong> ' . e((string)$p['comment']['text']) . ' <span class="l75-muted">(' . e((string)$p['comment']['by']) . ', ' . e(date('j. n. Y', (int)strtotime((string)$p['comment']['at']))) . ')</span></p>';
    }
    $html .= '<details class="p65-noprint"><summary>' . e(tr('Upravit reflexi nebo odebrat')) . '</summary>'
        . '<form method="post" class="l75-form">' . $csrf . '<input type="hidden" name="action" value="pf75_s_reflect"><input type="hidden" name="pin" value="' . e($id) . '">'
        . '<label for="pf75-r-' . e($id) . '">' . e(tr('Reflexe (nejvýš {n} znaků)', ['n' => PF75_TEXT_MAX])) . '</label><textarea id="pf75-r-' . e($id) . '" name="reflection" rows="3" maxlength="' . PF75_TEXT_MAX . '">' . e((string)$p['reflection']) . '</textarea>'
        . '<button class="btn secondary" type="submit">' . e(tr('Uložit reflexi')) . '</button></form>'
        . '<form method="post">' . $csrf . '<input type="hidden" name="action" value="pf75_s_unpin"><input type="hidden" name="pin" value="' . e($id) . '"><button class="btn secondary" type="submit">' . e(tr('Odebrat z portfolia')) . '</button></form></details>';
    return $html . '</li>';
}

// v79: připnutí nové práce je stepper Odkaz → Reflexe → Uložit (portfolio_v79.php, pf79_render); dřívější pf75_forms_html vyřazena.

/** POST pf75_s_* (CSRF ověřil index.php; žák ze session). */
function pf75_handle_student_post(string $action, string $classId): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    if ($studentKey === '' || !in_array($action, PF75_STUDENT_ACTIONS, true)) { $_SESSION['flash'] = tr('Nepodařilo se určit studentský profil.'); redirect_to('?view=portfolio'); }
    $pin = is_string($_POST['pin'] ?? null) ? (string)$_POST['pin'] : '';
    if ($action === 'pf75_s_pin') {
        $res = pf75_pin($classId, $studentKey, $_POST);
        $_SESSION['flash'] = $res['ok'] ? tr('Práce je připnutá v portfoliu.') : match ((string)$res['error']) {
            'url' => tr('Odkaz musí začínat https:// a vést na veřejnou stránku.'), 'title' => tr('Doplň název práce.'),
            'task' => tr('Připnout jde jen vyřešenou úlohu.'), 'project' => tr('Projekt se nepodařilo najít.'),
            'full' => tr('Máš připnuto nejvíc {n} prací.', ['n' => PF75_MAX]), 'duplicate' => tr('Tahle práce už v portfoliu je.'), default => tr('Práci se nepodařilo připnout.'),
        };
    } else {
        $ok = pf75_update($classId, $studentKey, $pin, $action === 'pf75_s_unpin' ? null : (is_string($_POST['reflection'] ?? null) ? (string)$_POST['reflection'] : ''));
        $_SESSION['flash'] = !$ok ? tr('Práce nebyla nalezena.') : ($action === 'pf75_s_unpin' ? tr('Práce je odebraná z portfolia.') : tr('Reflexe je uložená.'));
    }
    redirect_to('?view=portfolio#pf75-h');
}
