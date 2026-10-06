<?php

declare(strict_types=1);

/**
 * EDUCANET v68 · přesměrování vyřazených žákovských pohledů (volá index.php před routerem, jen GET).
 *
 *   ?view=v48_state → ?view=dashboard (pohled vyřazen; učitelská projekce má vlastní ?tab=teach&v48_state=1),
 *   ?view=continue  → ?view=dashboard,
 *   ?view=one_task  → ?view=dashboard (výjimka: task=kb s platným názvem tématu míří rovnou na plnou lekci ?view=kb_lesson).
 * Soubor app/views/one_task.php a knihovny One Task zůstávají do v69 (načítá je skupina libs „layout“).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Cíl přesměrování (relativní URL) nebo null, když se pohled nepřesměrovává. */
function routes68_redirect_target(string $view, string $method, array $query): ?string
{
    if ($method !== 'GET' && $method !== 'HEAD') return null;
    if ($view === 'v48_state' || $view === 'continue') return '?view=dashboard';
    if ($view !== 'one_task') return null;
    $topic = $query['topic'] ?? null;
    if (($query['task'] ?? '') === 'kb' && is_string($topic) && preg_match('/^[A-Za-z0-9_\-]{1,80}$/', $topic) === 1) {
        return '?view=kb_lesson&topic=' . rawurlencode($topic);
    }
    return '?view=dashboard';
}

/** Odešle 302 a skončí, když pohled patří mezi vyřazené. */
function routes68_redirect_if_retired(string $view, string $method, array $query): void
{
    $target = routes68_redirect_target($view, $method, $query);
    if ($target === null) return;
    header('Location: ' . $target, true, 302);
    exit;
}
