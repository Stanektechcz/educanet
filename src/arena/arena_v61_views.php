<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Souboje – žákovské UI: tlačítko odvety v historii a statistiky třídy (jen pro toho, kdo výzvy přijímá).
 * Volá je arena_v60_challenge_views.php. Jen HTML nad arena_v61_duels.php; vše přes e().
 */

require_once __DIR__ . '/arena_v61_duels.php';
require_once __DIR__ . '/profile_v60_ui.php';

/** Odveta k dokončenému souboji: formulář s tlačítkem, nebo důvod, proč teď nejde. */
function arena61_rematch_html(array $row, string $studentKey, array $rematched, int $now): string
{
    $classId = (string)$row['class_id'];
    $opponent = arena61_opponent($row, $studentKey);
    if ((string)$row['status'] !== 'done' || $opponent === '') return '';
    if (isset($rematched[(string)$row['id']])) return '<small class="arena60-hint">' . e(tr('Odveta proběhla.')) . '</small>';
    [$ok, $reason] = arena60_can_challenge($classId, $studentKey, $opponent, $now);
    if (!$ok) return '<small class="arena60-hint">' . e(tr('Odveta teď nejde: {reason}', ['reason' => $reason])) . '</small>';
    return arena60_form_open('arena61_rematch', (string)$row['id']) . '<button class="btn secondary" type="submit">' . e(tr('Odveta')) . '</button></form>';
}

/** Souhrn třídy bez jmen; vidí ho jen žák se zapnutým přijímáním výzev (srovnání je dobrovolné). */
function arena61_render_class_stats(string $classId, string $studentKey): void
{
    if (!arena60_optin_get($classId, $studentKey)) return;
    $s = arena61_class_stats($classId, arena57_now());
    echo profile60_panel_open(tr('Souboje ve třídě'), tr('Statistiky'));
    if ($s['total'] === 0) {
        echo profile60_empty(tr('Ve třídě zatím neproběhl žádný souboj.'));
    } else {
        echo profile60_stats([
            [(string)(int)$s['counts']['done'], tr('dokončených soubojů'), 'teal'],
            [(string)(int)$s['optin'], tr('spolužáků přijímá výzvy'), 'yellow'],
            [$s['accept_percent'] === null ? '–' : $s['accept_percent'] . ' %', tr('výzev přijato')],
        ]);
    }
    echo '<p class="arena60-hint">' . e(tr('Souhrn bez jmen. Vidíš ho, protože sám/sama výzvy přijímáš.')) . '</p>' . profile60_panel_close();
}
