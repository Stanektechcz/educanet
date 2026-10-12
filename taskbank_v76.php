<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · výsledky úloh s variantami jako důkaz kompetence v62 (zdroj `task`).
 *
 * Jedno kolo úlohy = jeden důkaz: vyřešeno napoprvé 1,0, napodruhé 0,75, napotřetí 0,5; ukázané řešení (3 chyby) 0,0.
 * Zapisuje se jen v třídách zapojených do kompetencí (comp62_enabled_for_class: pilot 1.A/3.A a schválené katalogy 2.A/4.A z v75)
 * a jen žákovi se stabilní identitou v58 (student_id stu_…). Odkaz na důkaz `tb76:<úloha>:r<kolo>` – žádné odpovědi ani jména.
 * Váha zdroje `task` v mastery_v62.php (formativní procvičení, menší než lab); do návrhů známek v66 nevstupuje (G66_MASTERY_SOURCES).
 * Selhání zápisu důkazu nesmí zablokovat odevzdání úlohy (try/catch, zápis do error_log bez dat žáka).
 */

/** Kompetence, kterou úloha dokládá, podle předmětu třídy (null = žádná). */
function tb76_task_competency(string $subject, string $task): ?string
{
    $map = [
        'os_site' => ['net_subnet' => 'net_addressing', 'net_mask' => 'net_addressing', 'net_ports' => 'net_services', 'net_route' => 'net_diagnose',
            'lnx_path' => 'lnx_navigation', 'lnx_chmod' => 'lnx_users', 'lnx_grep' => 'lnx_text'],
        'provoz_site' => ['net_subnet' => 'ops_routing', 'net_mask' => 'ops_routing', 'net_route' => 'ops_routing', 'net_ports' => 'ops_network_diag',
            'lnx_path' => 'ops_services', 'lnx_chmod' => 'ops_hardening', 'lnx_grep' => 'ops_observability'],
        'grafika_web' => ['gfx_color' => 'gfx_color_contrast', 'web_units' => 'web_css_layout', 'css_spec' => 'web_css_layout', 'img_dpi' => 'gfx_formats'],
        'grafika_web_ii' => ['gfx_color' => 'g2_visual_system', 'web_units' => 'w2_css_responsive', 'css_spec' => 'w2_design_system', 'img_dpi' => 'g2_handoff'],
    ];
    return $map[$subject][$task] ?? null;
}

/** Skóre kola: vyřešeno podle počtu pokusů, ukázané řešení 0. null = kolo ještě neskončilo. */
function tb76_round_score(array $entry): ?float
{
    if (!empty($entry['last_ok']) && (int)($entry['tries'] ?? 0) > 0) return max(0.5, 1.0 - 0.25 * ((int)$entry['tries'] - 1));
    return !empty($entry['shown']) ? 0.0 : null;
}

/** Zapíše důkaz za dokončené kolo (vrací počet nově zapsaných řádků: 0 nebo 1). */
function tb76_record_evidence(string $classId, string $ref, string $task, array $entry, int $now): int
{
    $score = tb76_round_score($entry);
    if ($score === null || preg_match('/^stu_[0-9a-f]{16}$/', $ref) !== 1) return 0;
    try {
        if (!function_exists('ev62_append')) require_once __DIR__ . '/evidence_v62.php';
        if (!function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId)) return 0;
        $subject = (string)comp62_subject_for_class($classId);
        $comp = tb76_task_competency($subject, $task);
        $catalog = comp62_competencies($subject);
        if ($comp === null || !isset($catalog[$comp])) return 0;
        $row = ['competency' => $comp, 'level' => max(1, min(4, (int)($catalog[$comp]['level'] ?? 2))), 'source' => 'task', 'score' => $score,
            'at' => date(DATE_ATOM, $now), 'artefact_ref' => 'tb76:' . $task . ':r' . (int)($entry['round'] ?? 1)];
        return (int)ev62_append($ref, [$row])['added'];
    } catch (Throwable $e) {
        error_log('EDUCANET v76 úlohy: důkaz kompetence se nezapsal (' . get_class($e) . ')');
        return 0;
    }
}
