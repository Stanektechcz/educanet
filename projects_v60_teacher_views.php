<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Učitelský registr projektů (modul 'projekty' v teacher58_modules()). Rozsah tříd a role vynucuje
 * teacher59_guard_post()/teacher_require_permission() dřív, než sem dorazí požadavek (viz teacher.php).
 * Vždy česky (učitelský cockpit).
 *
 * Poznámka pro učitele: klient nemá přístup do systému. Kontakt s klientem řeší vždy škola, žádné
 * osobní údaje žáka mu nepředáváme bez explicitního souhlasu zákonného zástupce a vedení školy.
 */

function proj60_teacher_allowed_classes(): array
{
    return function_exists('teacher59_allowed_class_ids') ? teacher59_allowed_class_ids() : [];
}

/** Zpracuje POST prefixu proj60_ (voláno z teacher58_modules()['projekty']['post']). */
function proj60_teacher_handle_post(string $action): void
{
    $allowed = proj60_teacher_allowed_classes();
    if ($action === 'proj60_save') {
        $id = is_string($_POST['id'] ?? null) ? (string)$_POST['id'] : '';
        $classes = array_values(array_filter((array)($_POST['classes'] ?? []), 'is_string'));
        $input = $_POST;
        $input['classes'] = $classes;
        $itemId = proj60_save($id, $input, $allowed, teacher59_current_id() ?? 'teacher');
        $_SESSION['flash'] = $itemId !== null ? tr('Projekt byl uložen.') : tr('Projekt se nepodařilo uložit – zkontroluj údaje.');
    } elseif ($action === 'proj60_status') {
        $id = is_string($_POST['id'] ?? null) ? (string)$_POST['id'] : '';
        $status = is_string($_POST['status'] ?? null) ? (string)$_POST['status'] : '';
        $_SESSION['flash'] = proj60_set_status($id, $status) ? tr('Stav projektu byl změněn.') : tr('Stav se nepodařilo změnit.');
    } elseif ($action === 'proj60_decide') {
        $appId = is_string($_POST['application_id'] ?? null) ? (string)$_POST['application_id'] : '';
        $decision = is_string($_POST['decision'] ?? null) ? (string)$_POST['decision'] : '';
        $consent = !empty($_POST['consent_confirmed']);
        $decision = $decision === 'approve' ? 'approved' : ($decision === 'reject' ? 'rejected' : $decision);
        $ok = proj60_decide($appId, $decision, $consent, teacher59_current_id() ?? 'teacher');
        $_SESSION['flash'] = $ok ? tr('Přihláška byla vyřízena.') : tr('Přihlášku se nepodařilo vyřídit – u placených projektů je nutné nejdřív potvrdit souhlas zákonného zástupce.');
    }
    redirect_to('teacher.php?tab=projekty');
}

function proj60_render_teacher_tab(string $classId, string $csrf): void
{
    $allowed = proj60_teacher_allowed_classes();
    $isAdmin = function_exists('teacher59_is_admin') && teacher59_is_admin();
    $items = proj60_all();
    if (!$isAdmin) {
        $items = array_filter($items, static function (array $item) use ($allowed): bool {
            $itemClasses = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
            return $itemClasses !== [] && array_diff($itemClasses, $allowed) === [];
        });
    }
    echo '<section class="teacher-panel"><h2>' . e(tr('Projekty podle levelu')) . '</h2>';
    echo '<p class="teacher-note">' . e(tr('Klient nemá přístup do systému. Kontakt vždy zajišťuje škola, osobní údaje žáka klientovi nepředávej bez souhlasu vedení a zákonného zástupce.')) . '</p>';

    echo '<h3>' . e(tr('Nový projekt')) . '</h3>';
    proj60_render_item_form('', [], $allowed, $csrf);

    echo '<h3>' . e(tr('Katalog projektů')) . '</h3><table class="teacher-table"><thead><tr><th>' . e(tr('Název')) . '</th><th>' . e(tr('Min. level')) . '</th><th>' . e(tr('Odměna')) . '</th><th>' . e(tr('Třídy')) . '</th><th>' . e(tr('Stav')) . '</th><th></th></tr></thead><tbody>';
    foreach ($items as $id => $item) {
        if (!is_array($item)) continue;
        $itemClasses = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
        $canManage = $itemClasses !== [] && array_diff($itemClasses, $allowed) === [];
        echo '<tr><td>' . e((string)$item['title']) . '</td><td>' . (int)$item['min_level'] . '</td><td>' . e((string)$item['reward_type']) . ($item['requires_guardian_consent'] ? ' (' . e(tr('souhlas zákonného zástupce')) . ')' : '') . '</td><td>' . e($itemClasses === [] ? tr('žádné') : implode(', ', $itemClasses)) . '</td><td>' . e((string)$item['status']) . '</td><td>';
        if ($canManage) {
            foreach (['open' => tr('Otevřít'), 'closed' => tr('Uzavřít'), 'done' => tr('Hotovo')] as $status => $label) {
                if ($status === (string)$item['status']) continue;
                echo '<form method="post" class="t-inline-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="id" value="' . e((string)$id) . '"><input type="hidden" name="status" value="' . e($status) . '"><input type="hidden" name="action" value="proj60_status"><button class="btn secondary small" type="submit">' . e($label) . '</button></form>';
            }
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';

    proj60_render_applications($allowed, $isAdmin, $csrf);
    echo '</section>';
}

function proj60_render_item_form(string $id, array $item, array $allowed, string $csrf): void
{
    $classes = array_values(array_filter((array)($item['classes'] ?? []), 'is_string'));
    echo '<form method="post" class="t-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="action" value="proj60_save"><input type="hidden" name="id" value="' . e($id) . '">'
        . '<label>' . e(tr('Název projektu')) . ' <input type="text" name="title" required value="' . e((string)($item['title'] ?? '')) . '"></label>'
        . '<label>' . e(tr('Klient (veřejný štítek, bez kontaktu)')) . ' <input type="text" name="client_label" value="' . e((string)($item['client_label'] ?? '')) . '"></label>'
        . '<label>' . e(tr('Veřejné shrnutí (vidí všichni)')) . ' <textarea name="summary_public">' . e((string)($item['summary_public'] ?? '')) . '</textarea></label>'
        . '<label>' . e(tr('Detail (jen po dosažení levelu)')) . ' <textarea name="detail_private">' . e((string)($item['detail_private'] ?? '')) . '</textarea></label>'
        . '<label>' . e(tr('Dovednosti (oddělené čárkou)')) . ' <input type="text" name="skills" value="' . e(implode(', ', (array)($item['skills'] ?? []))) . '"></label>'
        . proj60_render_required_fields((array)($item['required_competencies'] ?? []))
        . '<label>' . e(tr('Minimální úroveň')) . ' <input type="number" name="min_level" min="1" required value="' . (int)($item['min_level'] ?? 1) . '"></label>'
        . '<label>' . e(tr('Typ odměny')) . ' <select name="reward_type"><option value="kc">' . e(tr('Peníze (Kč)')) . '</option><option value="portfolio">' . e(tr('Do portfolia')) . '</option><option value="certificate">' . e(tr('Certifikát')) . '</option><option value="other">' . e(tr('Jiné')) . '</option></select></label>'
        . '<label>' . e(tr('Poznámka k odměně (bez konkrétních částek do systému)')) . ' <input type="text" name="reward_note" value="' . e((string)($item['reward_note'] ?? '')) . '"></label>'
        . '<label>' . e(tr('Kapacita (počet míst)')) . ' <input type="number" name="capacity" min="1" value="' . (int)($item['capacity'] ?? 1) . '"></label>'
        . '<label>' . e(tr('Termín (RRRR-MM-DD, nepovinné)')) . ' <input type="text" name="deadline" value="' . e((string)($item['deadline'] ?? '')) . '"></label>'
        . '<fieldset><legend>' . e(tr('Třídy, kterým se projekt nabízí')) . '</legend>';
    foreach ($allowed as $c) {
        echo '<label class="t-check"><input type="checkbox" name="classes[]" value="' . e($c) . '"' . (in_array($c, $classes, true) ? ' checked' : '') . '> ' . e($c) . '</label>';
    }
    echo '</fieldset>'
        . '<label class="t-check"><input type="checkbox" name="requires_guardian_consent"' . (!empty($item['requires_guardian_consent']) ? ' checked' : '') . '> ' . e(tr('Vyžaduje souhlas zákonného zástupce (u peněžní odměny je vždy vynuceno)')) . '</label>'
        . '<label>' . e(tr('Stav')) . ' <select name="status"><option value="draft">' . e(tr('Koncept')) . '</option><option value="open">' . e(tr('Otevřený')) . '</option><option value="closed">' . e(tr('Uzavřený')) . '</option><option value="done">' . e(tr('Hotovo')) . '</option></select></label>'
        . '<button class="btn primary" type="submit">' . e(tr('Uložit projekt')) . '</button></form>';
}

/** Až 3 požadované kompetence (zámek projektu; platí jen v pilotních třídách kompetencí, jinde se ignoruje). */
function proj60_render_required_fields(array $required): string
{
    $known = proj60_known_competencies();
    $states = ['rozpracovano' => 'rozpracováno', 'zvladnuto' => 'zvládnuto', 'upevneno' => 'upevněno'];
    $html = '<fieldset><legend>Požadované kompetence (nepovinné, max. ' . PROJ60_COMPETENCY_MAX . ', jen pilotní třídy)</legend>';
    for ($i = 0; $i < PROJ60_COMPETENCY_MAX; $i++) {
        $cur = is_array($required[$i] ?? null) ? $required[$i] : ['id' => '', 'min_state' => 'zvladnuto'];
        $html .= '<label>Kompetence ' . ($i + 1) . ' <select name="rc_id[]"><option value="">– žádná –</option>';
        foreach ($known as $id => $label) $html .= '<option value="' . e($id) . '"' . ($id === (string)$cur['id'] ? ' selected' : '') . '>' . e($label) . '</option>';
        $html .= '</select></label><label>Minimální stav <select name="rc_state[]">';
        foreach ($states as $value => $label) $html .= '<option value="' . e($value) . '"' . ($value === (string)$cur['min_state'] ? ' selected' : '') . '>' . e($label) . '</option>';
        $html .= '</select></label>';
    }
    return $html . '</fieldset>';
}

function proj60_render_applications(array $allowed, bool $isAdmin, string $csrf): void
{
    $apps = proj60_applications_for_classes($isAdmin ? teacher59_all_class_ids() : $allowed);
    echo '<h3>' . e(tr('Přihlášky žáků')) . '</h3>';
    if ($apps === []) {
        echo '<p class="teacher-empty">' . e(tr('Zatím žádné přihlášky.')) . '</p>';
        return;
    }
    echo '<table class="teacher-table"><thead><tr><th>' . e(tr('Projekt')) . '</th><th>' . e(tr('Žák')) . '</th><th>' . e(tr('Level při přihlášení')) . '</th><th>' . e(tr('Stav')) . '</th><th>' . e(tr('Motivace')) . '</th><th></th></tr></thead><tbody>';
    foreach ($apps as $app) {
        $project = (array)$app['project'];
        $studentLabel = function_exists('adaptive_student_label') ? adaptive_student_label((string)$app['class_id'], (string)$app['student_key']) : (string)$app['student_key'];
        echo '<tr><td>' . e((string)$project['title']) . '</td><td>' . e($studentLabel) . '</td><td>' . (int)$app['level_at_apply'] . ' / ' . (int)$project['min_level'] . '</td><td>' . e((string)$app['status']) . '</td><td>' . e(mb_substr((string)$app['motivation'], 0, 160)) . '</td><td>';
        if ((string)$app['status'] === 'interested') {
            $needsConsent = !empty($project['requires_guardian_consent']);
            echo '<form method="post" class="t-inline-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="application_id" value="' . e((string)$app['id']) . '"><input type="hidden" name="action" value="proj60_decide"><input type="hidden" name="decision" value="approve">';
            if ($needsConsent) {
                echo '<label class="t-check"><input type="checkbox" name="consent_confirmed" required> ' . e(tr('Souhlas zákonného zástupce ověřen')) . '</label>';
            }
            echo '<button class="btn secondary small" type="submit">' . e(tr('Schválit')) . '</button></form>';
            echo '<form method="post" class="t-inline-form"><input type="hidden" name="csrf" value="' . e($csrf) . '"><input type="hidden" name="application_id" value="' . e((string)$app['id']) . '"><input type="hidden" name="action" value="proj60_decide"><input type="hidden" name="decision" value="reject"><button class="btn secondary small" type="submit">' . e(tr('Zamítnout')) . '</button></form>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
}
