<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Vykreslení nabídky projektů (žák) – volá ho app/views/projects.php.
 */

function projects60_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/projects-v60.css?v=60.0')) . '">' . "\n";
}

function projects60_reward_label(string $type): string
{
    return [
        'kc' => tr('Odměna v Kč'),
        'portfolio' => tr('Do portfolia'),
        'certificate' => tr('Certifikát'),
        'other' => tr('Jiná odměna'),
    ][$type] ?? $type;
}

function projects60_status_label(string $status): string
{
    return [
        'interested' => tr('Čeká na vyjádření'),
        'approved' => tr('Přijato'),
        'rejected' => tr('Nepřijato'),
        'withdrawn' => tr('Staženo'),
    ][$status] ?? $status;
}

function projects60_render(string $classId, string $studentKey, int $level): void
{
    projects60_assets();
    echo '<section class="proj60-hero"><div class="eyebrow">' . e(tr('Projekty')) . '</div><h1>' . e(tr('Nabídky skutečné práce podle levelu')) . '</h1>'
        . '<p>' . e(tr('Klienti nabízejí skutečné zakázky. Kontakt s klientem vždy jde přes školu – nikdy mu nedáváme tvoje osobní údaje.')) . '</p></section>';

    echo '<p><a class="btn secondary" href="?view=projekt65">' . e(tr('Cyklus projektu a portfolio')) . '</a></p>';
    $items = proj60_for_class($classId);
    if ($items === []) {
        echo '<section class="dashboard-panel proj60-empty"><p>' . e(tr('Zatím tu není žádná nabídka. Zkontroluj to znovu později.')) . '</p></section>';
    } else {
        echo '<section class="proj60-grid" aria-label="' . e(tr('Nabídka projektů')) . '">';
        foreach ($items as $id => $item) {
            projects60_render_card((string)$id, $item, $classId, $studentKey, $level);
        }
        echo '</section>';
    }

    projects60_render_my_applications($classId, $studentKey);
}

function projects60_render_card(string $id, array $item, string $classId, string $studentKey, int $level): void
{
    $minLevel = (int)$item['min_level'];
    $hasLevel = $level >= $minLevel;
    $view = proj60_public_view($item, $hasLevel);
    $applications = storage_read(proj60_applications_path());
    $own = proj60_find_own($applications, $classId, $studentKey, $id);
    $approvedCount = proj60_approved_count($applications, $id);
    $capacity = (int)$item['capacity'];

    echo '<article class="proj60-card">';
    echo '<h2>' . e($view['title']) . '</h2>';
    if ($view['client_label'] !== '') echo '<p class="proj60-client">' . e($view['client_label']) . '</p>';
    if ($view['summary_public'] !== '') echo '<p>' . e($view['summary_public']) . '</p>';
    if ($view['skills'] !== []) {
        echo '<ul class="proj60-skills">';
        foreach ($view['skills'] as $s) echo '<li>' . e($s) . '</li>';
        echo '</ul>';
    }
    echo '<div class="proj60-meta">';
    echo '<span class="proj60-level' . ($hasLevel ? ' proj60-level-ok' : '') . '">' . e(tr('Vyžaduje úroveň {n}', ['n' => $minLevel])) . '</span>';
    echo '<span>' . e(projects60_reward_label($view['reward_type'])) . ($view['reward_note'] !== '' ? ': ' . e($view['reward_note']) : '') . '</span>';
    if ($view['requires_guardian_consent']) echo '<span class="proj60-consent">' . e(tr('Vyžaduje souhlas zákonného zástupce')) . '</span>';
    echo '<span>' . e(tr('Volná místa: {n}', ['n' => max(0, $capacity - $approvedCount)])) . '</span>';
    if ($view['deadline'] !== '') echo '<span>' . e(tr('Termín: {date}', ['date' => $view['deadline']])) . '</span>';
    echo '</div>';

    $missing = proj60_missing_competencies($classId, $studentKey, $item);
    if ($missing !== []) {
        $labels = proj60_known_competencies();
        echo '<p class="proj60-locked">' . e(tr('Chybí ti kompetence:')) . ' <span' . edu_content_lang_attr() . '>' . e(implode('; ', array_map(static fn(string $id): string => (string)($labels[$id] ?? $id), $missing))) . '</span></p>';
    }

    if (!$hasLevel) {
        echo '<p class="proj60-locked">' . e(tr('Potřebuješ úroveň {need} (máš {have}).', ['need' => $minLevel, 'have' => $level])) . '</p>';
        echo '</article>';
        return;
    }

    if ($view['detail_private'] !== '') echo '<p class="proj60-detail">' . e($view['detail_private']) . '</p>';

    if ($own !== null && (string)$own['status'] !== 'withdrawn') {
        echo '<p class="proj60-status">' . e(projects60_status_label((string)$own['status'])) . '</p>';
        if (in_array((string)$own['status'], ['interested', 'approved'], true)) {
            echo '<form method="post" class="proj60-inline-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
                . '<input type="hidden" name="action" value="proj60_withdraw"><input type="hidden" name="application_id" value="' . e((string)$own['id']) . '">'
                . '<button class="btn secondary small" type="submit">' . e(tr('Stáhnout přihlášku')) . '</button></form>';
        }
    } elseif ($missing !== []) {
        echo '<p class="proj60-note">' . e(tr('Přihlásit se můžeš, až kompetence zvládneš.')) . '</p>';
    } elseif ($approvedCount >= $capacity) {
        echo '<p class="proj60-note">' . e(tr('Kapacita je naplněná.')) . '</p>';
    } else {
        echo '<form method="post" class="proj60-apply-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
            . '<input type="hidden" name="action" value="proj60_apply"><input type="hidden" name="project_id" value="' . e($id) . '">'
            . '<label>' . e(tr('Proč tě projekt zajímá?')) . ' <textarea name="motivation" maxlength="' . PROJ60_MOTIVATION_MAX . '"></textarea></label>'
            . '<button class="btn primary" type="submit">' . e(tr('Projevit zájem')) . '</button></form>';
    }
    echo '</article>';
}

function projects60_render_my_applications(string $classId, string $studentKey): void
{
    $apps = proj60_my_applications($classId, $studentKey);
    echo '<section class="dashboard-panel proj60-my"><div class="dashboard-panel-head"><div><div class="eyebrow">' . e(tr('Moje přihlášky')) . '</div><h2>' . e(tr('Historie přihlášek k projektům')) . '</h2></div></div>';
    if ($apps === []) {
        echo '<p class="proj60-note">' . e(tr('Zatím ses k žádnému projektu nepřihlásil/a.')) . '</p></section>';
        return;
    }
    echo '<ul class="proj60-app-list">';
    foreach ($apps as $a) {
        echo '<li><strong>' . e($a['title']) . '</strong><span>' . e(projects60_status_label($a['status'])) . '</span>'
            . '<small>' . e(date('d.m.Y H:i', strtotime($a['at']) ?: time())) . '</small></li>';
    }
    echo '</ul></section>';
}
