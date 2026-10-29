<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Nahlášení chyby / návrh vylepšení – vykreslení pro žáka (volá app/views/feedback.php)
 * a souhrn pro profil (volá profile_v60_views.php). Výstup jen přes e(), bez klientského JS.
 */

function feedback60_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/feedback-v60.css?v=60.0')) . '">' . "\n";
}

function feedback60_type_label(string $type): string
{
    return ['bug' => tr('Chyba'), 'improvement' => tr('Návrh vylepšení')][$type] ?? $type;
}

function feedback60_status_label(string $status): string
{
    return [
        'new' => tr('Čeká na posouzení'),
        'confirmed' => tr('Potvrzeno'),
        'rejected' => tr('Zamítnuto'),
        'duplicate' => tr('Duplicita'),
        'done' => tr('Vyřešeno'),
    ][$status] ?? $status;
}

/** Text chyby pro flash zprávu. */
function feedback60_error_text(string $error): string
{
    return [
        'type' => tr('Vyber, jestli jde o chybu, nebo o návrh vylepšení.'),
        'title_short' => tr('Titulek je moc krátký (aspoň {n} znaků).', ['n' => FB60_TITLE_MIN]),
        'title_long' => tr('Titulek je moc dlouhý (nejvýš {n} znaků).', ['n' => FB60_TITLE_MAX]),
        'desc_short' => tr('Popiš to podrobněji (aspoň {n} znaků).', ['n' => FB60_DESC_MIN]),
        'desc_long' => tr('Popis je moc dlouhý (nejvýš {n} znaků).', ['n' => FB60_DESC_MAX]),
        'limit' => tr('Dnes jsi poslal/a maximum hlášení. Zkus to zítra.'),
        'duplicate' => tr('Hlášení se stejným titulkem už jsi poslal/a.'),
    ][$error] ?? tr('Hlášení se nepodařilo odeslat.');
}

function feedback60_render_page(string $classId, string $studentKey, string $page, array $draft): void
{
    feedback60_assets();
    $remaining = fb60_remaining_today($classId, $studentKey);
    $defaults = fb60_reward_defaults();
    echo '<section class="fb60-hero"><div class="eyebrow">' . e(tr('Hlášení')) . '</div><h1>' . e(tr('Nahlásit chybu nebo navrhnout vylepšení')) . '</h1>'
        . '<p>' . e(tr('Pomoz nám zlepšit Educanet. Když učitel hlášení potvrdí, dostaneš body a XP (chyba obvykle {bp} b / {bx} XP, návrh {ip} b / {ix} XP).', [
            'bp' => (int)$defaults['bug']['points'], 'bx' => (int)$defaults['bug']['xp'], 'ip' => (int)$defaults['improvement']['points'], 'ix' => (int)$defaults['improvement']['xp']])) . '</p></section>';

    echo '<section class="dashboard-panel fb60-panel" aria-labelledby="fb60-form-title"><h2 id="fb60-form-title">' . e(tr('Nové hlášení')) . '</h2>';
    if ($remaining === 0) {
        echo '<p class="fb60-note" role="status">' . e(tr('Dnes jsi poslal/a maximum hlášení. Zkus to zítra.')) . '</p></section>';
    } else {
        feedback60_render_form($page, $draft, $remaining);
        echo '</section>';
    }
    feedback60_render_mine($classId, $studentKey);
}

function feedback60_render_form(string $page, array $draft, int $remaining): void
{
    $type = (string)($draft['type'] ?? 'bug');
    echo '<form method="post" class="fb60-form">'
        . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="fb60_submit">'
        . '<input type="hidden" name="page" value="' . e($page) . '">'
        . '<fieldset class="fb60-types"><legend>' . e(tr('Co posíláš')) . '</legend>'
        . '<label class="fb60-choice"><input type="radio" name="type" value="bug"' . ($type !== 'improvement' ? ' checked' : '') . ' required> ' . e(tr('Chyba')) . '</label>'
        . '<label class="fb60-choice"><input type="radio" name="type" value="improvement"' . ($type === 'improvement' ? ' checked' : '') . '> ' . e(tr('Návrh vylepšení')) . '</label></fieldset>';
    if ($page !== '') {
        echo '<p class="fb60-note">' . e(tr('Stránka: {page}', ['page' => $page])) . '</p>';
    }
    echo '<label class="fb60-field" for="fb60-title">' . e(tr('Titulek')) . '</label>'
        . '<input id="fb60-title" type="text" name="title" required minlength="' . FB60_TITLE_MIN . '" maxlength="' . FB60_TITLE_MAX . '" autocomplete="off" value="' . e((string)($draft['title'] ?? '')) . '">'
        . '<label class="fb60-field" for="fb60-desc">' . e(tr('Popis')) . '</label>'
        . '<textarea id="fb60-desc" name="description" required minlength="' . FB60_DESC_MIN . '" maxlength="' . FB60_DESC_MAX . '" rows="6" aria-describedby="fb60-desc-hint">' . e((string)($draft['description'] ?? '')) . '</textarea>'
        . '<p id="fb60-desc-hint" class="fb60-note">' . e(tr('U chyby napiš, co jsi dělal/a a co se stalo. Nepiš hesla ani osobní údaje. Dnes můžeš poslat ještě {n} hlášení.', ['n' => $remaining])) . '</p>'
        . '<button class="btn primary" type="submit">' . e(tr('Odeslat hlášení')) . '</button></form>';
}

function feedback60_render_mine(string $classId, string $studentKey): void
{
    $rows = fb60_for_student($classId, $studentKey);
    echo '<section class="dashboard-panel fb60-panel" aria-labelledby="fb60-mine-title"><h2 id="fb60-mine-title">' . e(tr('Moje hlášení')) . '</h2>';
    if ($rows === []) {
        echo '<p class="fb60-note">' . e(tr('Zatím jsi nic neposlal/a.')) . '</p></section>';
        return;
    }
    echo '<ul class="fb60-list">';
    foreach ($rows as $row) {
        $status = (string)($row['status'] ?? 'new');
        echo '<li class="fb60-item fb60-' . e($status) . '"><div class="fb60-item-head"><strong>' . e((string)$row['title']) . '</strong>'
            . '<span class="fb60-badge">' . e(feedback60_type_label((string)$row['type'])) . '</span>'
            . '<span class="fb60-badge fb60-status">' . e(feedback60_status_label($status)) . '</span></div>'
            . '<small>' . e(date('d.m.Y H:i', strtotime((string)($row['created_at'] ?? '')) ?: time())) . '</small>';
        if ((int)($row['reward_points'] ?? 0) > 0 && in_array($status, ['confirmed', 'done'], true)) {
            echo '<p class="fb60-reward">' . e(tr('Odměna: {p} b a {x} XP', ['p' => (int)$row['reward_points'], 'x' => (int)$row['reward_xp']])) . '</p>';
        }
        if ((string)($row['teacher_note'] ?? '') !== '') {
            echo '<p class="fb60-teacher-note"><strong>' . e(tr('Poznámka učitele')) . ':</strong> ' . e((string)$row['teacher_note']) . '</p>';
        }
        echo '</li>';
    }
    echo '</ul></section>';
}

/** Malý souhrn pro záložku Odznaky vlastního profilu. */
function feedback60_render_profile_summary(string $classId, string $studentKey): void
{
    require_once __DIR__ . '/profile_v60_ui.php';
    $sum = fb60_summary($classId, $studentKey);
    echo profile60_panel_open(tr('Nahlášené chyby a návrhy'), tr('Hlášení'), tr('Nahlásit chybu nebo návrh'), '?view=hlaseni', '', 'fb60-profile')
        . profile60_stats([[(string)(int)$sum['total'], tr('Nahlášeno'), 'teal'], [(string)(int)$sum['confirmed'], tr('potvrzeno'), 'yellow']])
        . ($sum['confirmed'] > 0 ? '<p class="p60-muted-note">' . e(tr('odměna celkem: {p} b a {x} XP', ['p' => $sum['points'], 'x' => $sum['xp']])) . '</p>' : '')
        . profile60_panel_close();
}
