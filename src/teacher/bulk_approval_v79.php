<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79_views.php';

/**
 * EDUCANET v79 · Hromadné schválení jako stepper: Rozsah → Náhled změn → Potvrzení → Výsledek (s „Vrátit“).
 * Rozsah je GET formulář (?rozsah=, ?tyden=, ?class=, ?co_*=, ?krok=2), Náhled → Potvrzení uloží výběr položek do konceptu
 * (POST sp79_next), Potvrzení odešle beze změny ba74_approve (items[], class_ids[], confirm) a Výsledek je ba74_result_html.
 */

const BA79_LABELS = ['Rozsah', 'Náhled změn', 'Potvrzení', 'Výsledek'];
const BA79_ITEM_RE = '/^class_[a-z0-9]{1,8}\|\d{1,2}\|(lesson|svp)\|[A-Za-z0-9_-]{0,64}$/';

function sp79_def_hromadne(): array
{
    return [
        'perm' => 'content.manage',
        'fields' => [
            'items' => ['type' => 'list', 're' => BA79_ITEM_RE, 'max' => BA74_MAX + 1], 'class_ids' => ['type' => 'list', 're' => '/^class_[a-z0-9]{1,8}$/', 'max' => 8],
            'ba_rozsah' => ['type' => 'enum', 'values' => array_keys(BA74_SCOPES)], 'ba_tyden' => ['type' => 'text', 'max' => 10], 'ba_class' => ['type' => 'text', 'max' => 16],
            'co_lekce' => ['type' => 'flag'], 'co_svp' => ['type' => 'flag'],
        ],
        'scope' => static fn(array $d): string => ba79_scope(ba79_filter_from_data($d)),
        'validate' => 'ba79_validate',
        'query' => static fn(array $d, int $step): array => ba79_query(ba79_filter_from_data($d)),
    ];
}

/** Filtr z konceptu (tvar jako GET). */
function ba79_filter_from_data(array $d): array
{
    return ['rozsah' => (string)$d['ba_rozsah'], 'tyden' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$d['ba_tyden']) === 1 ? (string)$d['ba_tyden'] : '',
        'class' => preg_match('/^class_[a-z0-9]{1,8}$/', (string)$d['ba_class']) === 1 ? (string)$d['ba_class'] : '', 'co_lekce' => (string)$d['co_lekce'], 'co_svp' => (string)$d['co_svp']];
}

function ba79_scope(array $f): string
{
    return implode('|', [$f['rozsah'], $f['tyden'], $f['class'], $f['co_lekce'], $f['co_svp']]);
}

/** GET parametry náhledu pro filtr. */
function ba79_query(array $f): array
{
    return array_filter(['f' => '1', 'rozsah' => $f['rozsah'], 'tyden' => $f['tyden'], 'class' => $f['class'], 'co_lekce' => $f['co_lekce'], 'co_svp' => $f['co_svp']], static fn(string $v): bool => $v !== '');
}

/** @return array<string,string> */
function ba79_validate(int $step, array $d): array
{
    if ($step !== 2) return [];
    if ($d['items'] === []) return ['ba79-items' => 'Vyberte aspoň jednu položku ke schválení.'];
    if (count($d['items']) > BA74_MAX) return ['ba79-items' => 'Najednou jde schválit nejvýš ' . BA74_MAX . ' položek – zužte výběr na týden nebo třídu.'];
    return [];
}

function ba79_url(array $f, int $step): string
{
    return '?' . http_build_query(['tab' => 'hromadne74'] + ($step > 1 ? ba79_query($f) : []) + ['krok' => (string)$step]);
}

/** Krok a koncept pro záložku. */
function ba79_state(array $f, bool $submitted, bool $result, bool $can): array
{
    $draft = sp79_draft_for('hromadne', ba79_scope($f));
    if ($result) {
        sp79_draft_clear('hromadne');
        return ['cur' => 4, 'reach' => 4, 'draft' => $draft, 'errors' => [], 'items' => null];
    }
    if (!$submitted) return ['cur' => 1, 'reach' => 1, 'draft' => $draft, 'errors' => [], 'items' => null];
    $reach = $can ? max(2, min(3, (int)$draft['max'])) : 2;
    $cur = sp79_current_step('hromadne', $reach, max(2, min(3, (int)$draft['step'])));
    $errors = $cur === (int)$draft['step'] ? (array)$draft['errors'] : [];
    if ($errors !== []) sp79_draft_put('hromadne', ['errors' => []] + $draft);
    else sp79_track('hromadne', $draft, $cur);
    return ['cur' => $cur, 'reach' => $reach, 'draft' => $draft, 'errors' => $errors, 'items' => $draft['data'] !== [] ? (array)($draft['data']['items'] ?? []) : null];
}

/** Souhrn vybraných položek před potvrzením. */
function ba79_confirm_html(array $modules, array $chosen, array $f): string
{
    $lessons = count(array_filter($chosen, static fn(array $it): bool => $it['kind'] === 'lesson'));
    $t14 = count(array_filter($chosen, static fn(array $it): bool => $it['t14']));
    $classes = array_values(array_unique(array_map(static fn(array $it): string => ba74_class_name($modules, (string)$it['class']), $chosen)));
    $rows = [
        ['Vybráno ke schválení', count($chosen) . ' ' . (count($chosen) === 1 ? 'položka' : (count($chosen) < 5 ? 'položky' : 'položek')), 2, ba79_url($f, 2)],
        ['Obsah lekcí / vazby na ŠVP', $lessons . ' / ' . (count($chosen) - $lessons), 2, ''],
        ['Učí se do 14 dní (T-14)', (string)$t14, 0, ''],
        ['Třídy', implode(', ', $classes), 1, ba79_url($f, 1)],
    ];
    $list = '<ul class="sp79-list">';
    foreach (array_slice($chosen, 0, 12) as $it) {
        $list .= '<li>' . e(ba74_class_name($modules, (string)$it['class']) . ' · L' . (int)$it['lesson'] . ' · ' . ($it['kind'] === 'lesson' ? 'obsah lekce' : 'vazba na ŠVP')) . ($it['t14'] ? ' <span class="c70-chip c70-chip-warn">T-14</span>' : '') . '</li>';
    }
    if (count($chosen) > 12) $list .= '<li>… a dalších ' . (count($chosen) - 12) . '</li>';
    return sp79_summary_html($rows, sp79_words()) . $list . '</ul>';
}
