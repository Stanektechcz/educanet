<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Učitelský registr hlášení (modul 'hlaseni' v teacher58_modules()). Vždy česky.
 * Rozsah tříd a roli vynucuje teacher59_guard_post()/teacher_require_permission() DŘÍV, než sem POST
 * dorazí (politika fb60_decide → entita fb60_report, prefix fb60_ → content.manage; asistent jen čte).
 * Seznam se navíc sám filtruje na třídy učitele (teacher59_allowed_class_ids), admin vidí vše.
 */

const FB60_T_STATUS = ['new' => 'Nové', 'confirmed' => 'Potvrzeno', 'rejected' => 'Zamítnuto', 'duplicate' => 'Duplicita', 'done' => 'Vyřešeno'];
const FB60_T_TYPE = ['bug' => 'Chyba', 'improvement' => 'Vylepšení'];
const FB60_T_FLASH = [
    'not_found' => 'Hlášení už neexistuje.',
    'transition' => 'Z tohoto stavu už tuto změnu provést nejde.',
    'rewarded' => 'Hlášení už bylo odměněno – zamítnout ho nejde, jen označit jako vyřešené.',
    'note_long' => 'Poznámka je delší než 500 znaků.',
    'decision' => 'Neznámé rozhodnutí.',
];

function fb60_teacher_is_admin(): bool
{
    return function_exists('teacher59_is_admin') && teacher59_is_admin();
}

function fb60_teacher_allowed_classes(): array
{
    return function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : [];
}

function fb60_teacher_can_manage(): bool
{
    return !function_exists('teacher_permission') || teacher_permission('content.manage');
}

/** Zpracuje POST prefixu fb60_ (voláno z teacher58_modules()['hlaseni']['post']). */
function fb60_teacher_handle_post(string $action): void
{
    if ($action === 'fb60_decide') {
        $str = static fn(string $k): string => is_string($_POST[$k] ?? null) ? (string)$_POST[$k] : '';
        $result = fb60_decide($str('id'), $str('decision'), (int)$str('points'), (int)$str('xp'), $str('note'),
            function_exists('teacher59_current_id') ? (teacher59_current_id() ?? 'teacher') : 'teacher', fb60_teacher_is_admin());
        if (!$result['ok']) {
            $_SESSION['flash'] = FB60_T_FLASH[(string)$result['error']] ?? 'Rozhodnutí se nepodařilo uložit.';
        } elseif ($result['status'] === 'confirmed') {
            $_SESSION['flash'] = 'Hlášení potvrzeno: ' . (int)$result['points'] . ' b a ' . (int)$result['xp'] . ' XP (každé hlášení se odměňuje jen jednou).'
                . ((int)$result['xp'] > 0 && !$result['xp_applied'] ? ' XP se nepodařilo připsat – žák nemá dohledatelný profil.' : '');
        } else {
            $_SESSION['flash'] = 'Stav hlášení byl změněn.';
        }
    }
    redirect_to('teacher.php?tab=hlaseni');
}

function fb60_render_teacher_tab(string $classId, string $csrf): void
{
    $allowed = fb60_teacher_allowed_classes();
    $isAdmin = fb60_teacher_is_admin();
    $filters = [
        'status' => is_string($_GET['fb_status'] ?? null) && isset(FB60_T_STATUS[$_GET['fb_status']]) ? (string)$_GET['fb_status'] : '',
        'type' => is_string($_GET['fb_type'] ?? null) && isset(FB60_T_TYPE[$_GET['fb_type']]) ? (string)$_GET['fb_type'] : '',
        'class' => is_string($_GET['fb_class'] ?? null) ? (string)$_GET['fb_class'] : '',
    ];
    $rows = fb60_list($allowed, $isAdmin, $filters);
    echo '<section class="teacher-panel"><h2>Hlášení chyb a návrhů</h2>'
        . '<p class="teacher-note">Potvrzením hlášení žák získá body a XP – za jedno hlášení jen jednou, i kdyby ses k potvrzení vrátil/a. Zamítnout už odměněné hlášení nejde.</p>';
    fb60_render_filters($filters, $allowed, $isAdmin);
    if ($rows === []) {
        echo '<p class="teacher-empty">Žádná hlášení neodpovídají filtru.</p></section>';
        return;
    }
    $canManage = fb60_teacher_can_manage();
    echo '<div class="fb60-t-list">';
    foreach ($rows as $row) fb60_render_teacher_row($row, $csrf, $canManage, $isAdmin);
    echo '</div></section>';
}

function fb60_render_filters(array $filters, array $allowed, bool $isAdmin): void
{
    echo '<form method="get" class="teacher-filter-row"><input type="hidden" name="tab" value="hlaseni">';
    foreach (['fb_status' => ['Stav', FB60_T_STATUS, $filters['status']], 'fb_type' => ['Typ', FB60_T_TYPE, $filters['type']]] as $name => [$label, $options, $current]) {
        echo '<label>' . e($label) . '<select name="' . e($name) . '"><option value="">vše</option>';
        foreach ($options as $value => $text) echo '<option value="' . e((string)$value) . '"' . ($value === $current ? ' selected' : '') . '>' . e($text) . '</option>';
        echo '</select></label>';
    }
    $classes = $isAdmin ? array_map('strval', array_unique(array_merge($allowed, array_column(array_filter(storage_read(fb60_path()), 'is_array'), 'class_id')))) : $allowed;
    echo '<label>Třída<select name="fb_class"><option value="">všechny</option>';
    foreach ($classes as $c) echo '<option value="' . e($c) . '"' . ($c === $filters['class'] ? ' selected' : '') . '>' . e($c) . '</option>';
    echo '</select></label><button class="btn secondary" type="submit">Filtrovat</button></form>';
}

function fb60_render_teacher_row(array $row, string $csrf, bool $canManage, bool $isAdmin): void
{
    $class = (string)$row['class_id'];
    $student = function_exists('adaptive_student_label') ? adaptive_student_label($class, (string)$row['student_key']) : 'Student';
    $status = (string)($row['status'] ?? 'new');
    echo '<article class="fb60-t-row fb60-' . e($status) . '"><header><strong>' . e((string)$row['title']) . '</strong> '
        . '<span class="fb60-badge">' . e(FB60_T_TYPE[(string)$row['type']] ?? (string)$row['type']) . '</span> '
        . '<span class="fb60-badge fb60-status">' . e(FB60_T_STATUS[$status] ?? $status) . '</span></header>'
        . '<small>' . e($class) . ' · ' . e($student) . ' · ' . e(date('d.m.Y H:i', strtotime((string)($row['created_at'] ?? '')) ?: time()))
        . ((string)($row['page'] ?? '') !== '' ? ' · stránka: ' . e((string)$row['page']) : '') . '</small>'
        . '<p class="fb60-t-desc">' . nl2br(e((string)$row['description'])) . '</p>';
    if ((int)($row['reward_points'] ?? 0) > 0) {
        echo '<p class="fb60-reward">Odměna: ' . (int)$row['reward_points'] . ' b a ' . (int)$row['reward_xp'] . ' XP</p>';
    }
    if ((string)($row['teacher_note'] ?? '') !== '') echo '<p class="fb60-teacher-note">Poznámka žákovi: ' . e((string)$row['teacher_note']) . '</p>';
    if ($canManage && $status !== 'done') fb60_render_decision_form($row, $csrf, $isAdmin);
    echo '</article>';
}

function fb60_render_decision_form(array $row, string $csrf, bool $isAdmin): void
{
    $id = (string)$row['id'];
    $defaults = fb60_reward_defaults()[(string)$row['type']] ?? ['points' => 3, 'xp' => 30];
    $points = (int)($row['reward_points'] ?? 0) > 0 ? (int)$row['reward_points'] : (int)$defaults['points'];
    $xp = (int)($row['reward_points'] ?? 0) > 0 ? (int)$row['reward_xp'] : (int)$defaults['xp'];
    $locked = (int)($row['reward_points'] ?? 0) > 0;
    echo '<form method="post" class="fb60-t-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="fb60_decide"><input type="hidden" name="id" value="' . e($id) . '">'
        . '<label>Body (1–10)<input type="number" name="points" min="1" max="' . FB60_MAX_POINTS . '" value="' . $points . '"' . ($locked ? ' readonly' : '') . '></label>';
    if ($isAdmin) {
        echo '<label>XP (0–' . FB60_MAX_XP . ')<input type="number" name="xp" min="0" max="' . FB60_MAX_XP . '" value="' . $xp . '"' . ($locked ? ' readonly' : '') . '></label>';
    } else {
        echo '<label>XP<select name="xp"' . ($locked ? ' disabled' : '') . '>';
        foreach (FB60_XP_PRESETS as $preset) echo '<option value="' . $preset . '"' . ($preset === $xp ? ' selected' : '') . '>' . $preset . '</option>';
        echo '</select>' . ($locked ? '<input type="hidden" name="xp" value="' . $xp . '">' : '') . '</label>';
    }
    echo '<label class="fb60-t-note">Poznámka žákovi (max 500 znaků)<textarea name="note" maxlength="' . FB60_NOTE_MAX . '" rows="2">' . e((string)($row['teacher_note'] ?? '')) . '</textarea></label>'
        . '<div class="fb60-t-actions"><button class="btn primary small" type="submit" name="decision" value="confirm">Potvrdit a odměnit</button>'
        . '<button class="btn secondary small" type="submit" name="decision" value="reject">Zamítnout</button>'
        . '<button class="btn secondary small" type="submit" name="decision" value="duplicate">Duplicita</button>'
        . '<button class="btn secondary small" type="submit" name="decision" value="done">Hotovo</button></div></form>';
}
