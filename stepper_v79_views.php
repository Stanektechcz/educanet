<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/stepper_v79.php';

/**
 * EDUCANET v79 · vykreslení stepperu (komponenta v78 `.ds-stepper` uvnitř `.ds78`): pruh kroků (desktop) / kompaktní „2/4“
 * (kontejner pod 480 px), souhrn chyb nahoře (role=alert, aria-live), chyba u pole, panely kroků, Zpět/Další a souhrn před potvrzením.
 * Texty: cockpit česky (výchozí), žák předá přeložená slova ze souboru toku (tr() + katalogy en/uk). Každý výstup přes e().
 */

/** Slova komponenty; $over = přeložené texty žákovské části. */
function sp79_words(array $over = []): array
{
    return $over + [
        'count' => 'Krok {n} ze {m}', 'done' => 'hotovo', 'current' => 'aktuální krok', 'wait' => 'čeká', 'nav' => 'Postup po krocích',
        'back' => 'Zpět', 'next' => 'Další', 'errors' => 'Než budete pokračovat, opravte:', 'edit' => 'Upravit', 'summary' => 'Souhrn před potvrzením',
    ];
}

/**
 * Pruh kroků: číslo + název + stav (hotovo / aktuální / čeká), „Krok 2 ze 4“; hotové a dosažené kroky jsou odkazy (GET ?krok=).
 * @param list<string> $labels
 * @param callable(int):string|null $href URL kroku
 */
function sp79_bar_html(array $labels, int $cur, int $reach, ?callable $href, array $w): string
{
    $n = count($labels);
    $cur = max(1, min($n, $cur));
    $count = strtr((string)$w['count'], ['{n}' => (string)$cur, '{m}' => (string)$n]);
    $html = '<nav class="ds78 sp79" id="sp79-top" aria-label="' . e((string)$w['nav']) . '"><div class="ds-stepper-wrap sp79-bar">'
        . '<p class="ds-stepper__count sp79-count">' . e($count) . ': <strong>' . e((string)$labels[$cur - 1]) . '</strong></p>'
        . '<p class="sp79-mini" aria-hidden="true"><span class="sp79-frac">' . $cur . '/' . $n . '</span> <strong>' . e((string)$labels[$cur - 1]) . '</strong></p>'
        . '<ol class="ds-stepper">';
    foreach (array_values($labels) as $i => $label) {
        $k = $i + 1;
        $state = $k < $cur ? 'done' : ($k === $cur ? 'current' : 'wait');
        $text = '<span class="ds-step__text">' . e((string)$label) . '<span class="ds-step__state">' . e((string)$w[$state]) . '</span></span>';
        if ($k !== $cur && $k <= $reach && $href !== null) $text = '<a class="sp79-link" href="' . e($href($k)) . '">' . $text . '</a>';
        $html .= '<li class="ds-step' . ($state === 'done' ? ' is-done' : ($state === 'current' ? ' is-current' : '')) . '"' . ($state === 'current' ? ' aria-current="step"' : '') . '>' . $text . '</li>';
    }
    return $html . '</ol></div></nav>';
}

/** Souhrn chyb nahoře – vždy v HTML (prázdný skrytý), aby ho JS mohl naplnit. @param array<string,string> $errors id pole → zpráva */
function sp79_errors_html(array $errors, array $w): string
{
    $items = '';
    foreach ($errors as $id => $msg) $items .= '<li><a href="#' . e((string)$id) . '">' . e((string)$msg) . '</a></li>';
    return '<div class="ds78 sp79-errs"><div class="ds-errors" id="sp79-errors" role="alert" aria-live="assertive" tabindex="-1" data-sp79-errors' . ($errors === [] ? ' hidden' : '') . '>'
        . '<p class="sp79-errs__t"><strong>' . e((string)$w['errors']) . '</strong></p><ul class="sp79-errs__list">' . $items . '</ul></div></div>';
}

/** Atributy pole s chybou: aria-invalid + aria-describedby (chyba, nápověda). */
function sp79_aria(array $errors, string $id, string $help = ''): string
{
    $ids = trim((isset($errors[$id]) ? $id . '-err ' : '') . $help);
    return (isset($errors[$id]) ? ' aria-invalid="true"' : '') . ($ids !== '' ? ' aria-describedby="' . e($ids) . '"' : '');
}

/** Chyba u pole (prázdný řetězec bez chyby). */
function sp79_field_error(array $errors, string $id): string
{
    return isset($errors[$id]) ? '<div class="ds78 sp79-ferr"><p class="ds-error" id="' . e($id) . '-err">' . e((string)$errors[$id]) . '</p></div>' : '';
}

/** Skryté údaje toku ve formuláři (tok a krok, ze kterého se odesílá). */
function sp79_hidden_html(string $flow, int $cur): string
{
    return '<input type="hidden" name="sp79_flow" value="' . e($flow) . '"><input type="hidden" name="sp79_step" value="' . $cur . '">';
}

/** Začátek panelu kroku; neaktivní panel má `hidden` (pole se ale odesílají – koncept ostatních kroků zůstává ve formuláři). */
function sp79_panel_open(int $k, int $cur, string $title, string $tag = 'section'): string
{
    return '<' . $tag . ' class="sp79-panel" id="sp79-p' . $k . '" data-sp79-panel="' . $k . '"' . ($k !== $cur ? ' hidden' : '') . ' aria-labelledby="sp79-h' . $k . '">'
        . '<h2 class="sp79-h" id="sp79-h' . $k . '" tabindex="-1" data-sp79-h>' . e($k . '. ' . $title) . '</h2>';
}

/**
 * Tlačítka kroku: primární první v DOM (Enter = Další / potvrzení), vizuálně vpravo. Zpět i Další mají formnovalidate (skryté panely
 * nesmí zablokovat odeslání; krok ověří server, s JS i klient – data-sp79-validate); potvrzení posledního kroku má nativní kontrolu.
 * $final = HTML potvrzovacího tlačítka posledního kroku (původní akce modulu, bez atributu name).
 */
function sp79_nav_html(string $side, int $cur, int $last, array $labels, array $w, string $final = ''): string
{
    $prefix = $side === 'student' ? 'sp79_s_' : 'sp79_';
    $primary = $cur < $last
        ? '<button type="submit" class="ds-btn ds-btn--primary" name="action" value="' . $prefix . 'next" formnovalidate data-sp79-validate>' . e((string)$w['next'] . ': ' . (string)($labels[$cur] ?? '')) . '</button>'
        : $final;
    $back = $cur > 1 ? '<button type="submit" class="ds-btn ds-btn--secondary" name="action" value="' . $prefix . 'back" formnovalidate>' . e((string)$w['back']) . '</button>' : '';
    return '<div class="ds78 sp79-w"><div class="sp79-nav">' . $primary . $back . '</div></div>';
}

/** Zpět/Další jako odkazy (toky bez rozpracovaných dat – jen výběr v URL, např. třída a lekce). */
function sp79_nav_links_html(string $nextHref, string $nextLabel, string $backHref, array $w): string
{
    return '<div class="ds78 sp79-w"><div class="sp79-nav">' . ($nextHref !== '' ? '<a class="ds-btn ds-btn--primary" href="' . e($nextHref) . '">' . e((string)$w['next'] . ': ' . $nextLabel) . '</a>' : '')
        . ($backHref !== '' ? '<a class="ds-btn ds-btn--secondary" href="' . e($backHref) . '">' . e((string)$w['back']) . '</a>' : '') . '</div></div>';
}

/** Potvrzovací tlačítko posledního kroku (odešle původní akci formuláře). */
function sp79_confirm_button(string $label): string
{
    return '<button type="submit" class="ds-btn ds-btn--primary" data-sp79-validate>' . e($label) . '</button>';
}

/**
 * Souhrn před potvrzením: řádky [pojem, hodnota, krok (0 = bez úprav), URL úpravy nebo ''].
 * @param list<array{0:string,1:string,2:int,3:string}> $rows
 */
function sp79_summary_html(array $rows, array $w): string
{
    $html = '<div class="ds78 sp79-w"><dl class="sp79-sum" aria-label="' . e((string)$w['summary']) . '">';
    foreach ($rows as [$term, $value, $step, $href]) {
        $html .= '<div class="sp79-sum__row"><dt>' . e($term) . '</dt><dd>' . e($value !== '' ? $value : '—')
            . ($href !== '' ? ' <a class="sp79-edit" href="' . e($href) . '">' . e((string)$w['edit']) . '<span class="ds-sr"> – ' . e($term) . '</span></a>' : '') . '</dd></div>';
    }
    return $html . '</dl></div>';
}

/** Stavová karta (hotovo / pozor / chyba) – stav vždy textem, barva jen doplňuje. */
function sp79_status_html(string $tone, string $label, string $text, string $extra = ''): string
{
    $tone = in_array($tone, ['ok', 'warn', 'danger'], true) ? $tone : 'ok';
    return '<div class="ds78 sp79-w"><div class="ds-card ds-status is-' . $tone . ' sp79-status" role="status"><p class="ds-status__label"><strong>' . e($label) . '</strong></p>'
        . ($text !== '' ? '<p>' . e($text) . '</p>' : '') . $extra . '</div></div>';
}

/** Rozpracovaný koncept: připomínka s odkazem „Pokračovat“ (a volitelně formulářem „Zahodit“). */
function sp79_resume_html(string $text, string $href, string $label, string $resetForm = ''): string
{
    return '<div class="ds78 sp79-w"><div class="sp79-resume" role="status"><p>' . e($text) . '</p><p class="sp79-resume__act"><a class="ds-btn ds-btn--secondary" href="' . e($href) . '">' . e($label) . '</a>' . $resetForm . '</p></div></div>';
}

/** CSS a JS stepperu pro žákovské stránky (cockpit je načítá přes registr modulů). */
function sp79_student_assets_html(): string
{
    $url = static fn(string $p): string => function_exists('asset_url') ? asset_url($p) : $p;
    return '<link rel="stylesheet" href="' . e($url('assets/components-v78.css?v=79.0')) . '"><link rel="stylesheet" href="' . e($url('assets/stepper-v79.css?v=79.0')) . '">'
        . '<script src="' . e($url('assets/stepper-v79.js?v=79.0')) . '" defer></script>';
}
