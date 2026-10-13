<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79_views.php';

/**
 * EDUCANET v79 · Admin – nový školní rok a účty jako stepper: Registr a kolize → Školní rok → Souhrn → Plán a účty.
 * Jeden formulář s původní akcí identity58_plan (CSRF, jen administrátor); krok „Školní rok“ uloží rok do konceptu (sp79_next).
 * Plán se dál jen počítá v paměti, zápisy (přechod roku, rozlišení jmenovců) běží výhradně z příkazové řádky.
 */

const ID79_LABELS = ['Registr a kolize', 'Školní rok', 'Souhrn', 'Plán a účty'];

function sp79_def_identita(): array
{
    return [
        'admin' => true,
        'fields' => ['year' => ['type' => 'int', 'range' => [2000, 2100]]],
        'scope' => static fn(array $d): string => 'rok',
        'validate' => static fn(int $step, array $d): array => $step === 2 && ($d['year'] === '' || $d['year'] === 'x') ? ['idt58-year' => 'Zadejte rok začátku nového školního roku (2000–2100), např. ' . (int)date('Y') . '.'] : [],
        'query' => static fn(array $d, int $step): array => [],
    ];
}

/** Krok, rok a chyby; ?plan= (po identity58_plan) = krok 4. */
function id79_state(?int $planYear, int $defaultYear): array
{
    $draft = sp79_draft_for('identita', 'rok');
    if ($planYear !== null) {
        sp79_draft_put('identita', ['scope' => 'rok', 'step' => 4, 'max' => 4, 'data' => ['year' => (string)$planYear]] + $draft);
        return ['cur' => 4, 'reach' => 4, 'year' => $planYear, 'errors' => []];
    }
    $reach = max(2, (int)$draft['max']);
    $cur = sp79_current_step('identita', $reach, (int)$draft['step']);
    $errors = $cur === (int)$draft['step'] ? (array)$draft['errors'] : [];
    if ($errors !== []) sp79_draft_put('identita', ['errors' => []] + $draft);
    else sp79_track('identita', $draft, $cur);
    $raw = (string)($draft['data']['year'] ?? '');
    return ['cur' => $cur, 'reach' => $reach, 'year' => preg_match('/^\d{4}$/', $raw) === 1 ? (int)$raw : $defaultYear, 'errors' => $errors];
}

function id79_url(int $step, ?int $planYear = null): string
{
    return '?' . http_build_query(['tab' => 'identita'] + ($step === 4 && $planYear !== null ? ['plan' => (string)$planYear] : []) + ['krok' => (string)$step]);
}

function id79_bar_html(array $st): string
{
    return sp79_bar_html(ID79_LABELS, (int)$st['cur'], (int)$st['reach'], static fn(int $k): string => id79_url($k, (int)$st['year']), sp79_words());
}

/** Krok 3: souhrn před sestavením plánu. */
function id79_summary_html(array $st, int $students, int $dups): string
{
    $y = (int)$st['year'];
    return '<p>Plán se spočítá jen v paměti – nic se nezapíše. Provedení přechodu i rozlišení jmenovců běží výhradně z příkazové řádky po záloze.</p>'
        . sp79_summary_html([
            ['Nový školní rok', $y . '/' . ($y + 1), 2, id79_url(2)],
            ['Žáků v registru', (string)$students, 1, id79_url(1)],
            ['Kolize jmen ke kontrole', (string)$dups, 1, id79_url(1)],
            ['Co plán ukáže', 'přesuny tříd, absolventy do archivu, přenos XP a labu, jmenovce v cílové třídě, zálohu a blokace', 0, ''],
        ], sp79_words());
}

/** Krok 4: co dál – účty a provedení (jen odkazy a příkazy, nic se nespouští). */
function id79_next_steps_html(int $year): string
{
    $link = static fn(string $tab, string $label): string => function_exists('c70_tab_visible') && !c70_tab_visible($tab) ? e($label) : '<a href="' . e('?tab=' . $tab) . '">' . e($label) . '</a>';
    return '<div class="ds78 sp79-w"><div class="ds-card sp79-next"><h3>Další kroky: účty a provedení</h3><ol class="sp79-list">'
        . '<li>Zálohujte úložiště a vyzkoušejte přechod nanečisto: <code>php tools/backup_storage.php</code>, <code>php tools/v58_rollover.php --dry-run --year=' . $year . '</code>, teprve pak <code>--apply</code>.</li>'
        . '<li>Noví žáci: jednorázová hesla a kartičky v záložce ' . $link('pristupy', 'Přístupy') . '.</li>'
        . '<li>Třídy a předměty učitelů pro nový rok: ' . $link('ucitele', 'Učitelé') . ' (jen v režimu účtů).</li>'
        . '<li>Jmenovce v cílové třídě rozlište před přechodem (krok 1 – kolize jmen).</li></ol></div></div>';
}
