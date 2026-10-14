<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · seznam hlavních stránek pro tools/v80_2_a11y_audit.php a schválené výjimky.
 * who: t = učitel (klíč, role admin), s = žák 3.A (vývojový vstup), anon = nepřihlášený; shots = snímky 375/768/1280/1920 světlý + tmavý.
 */

/** @return list<array{id:string,who:string,url:string,tab:string,section:string,shots:bool,dark:bool}> */
function v802_pages(): array
{
    $t = static fn(string $id, string $tab, string $section, string $query = '&class=class_3a', bool $shots = false): array
        => ['id' => $id, 'who' => 't', 'url' => '/teacher.php?tab=' . $tab . $query, 'tab' => $tab, 'section' => $section, 'shots' => $shots, 'dark' => in_array($tab, UI68_TEACHER_DARK_TABS, true)];
    $s = static fn(string $id, string $view, string $extra = '', bool $shots = false, string $who = 's'): array
        => ['id' => $id, 'who' => $who, 'url' => '/' . ($view === '' ? '' : '?view=' . $view . $extra), 'tab' => '', 'section' => '', 'shots' => $shots, 'dark' => in_array($view === '' ? 'home' : $view, UI67_DARK_VIEWS, true)];
    return [
        // učitel – sekce Dnes
        $t('t-dnes', 'attention', 'dnes'),
        $t('t-hodina', 'hodina', 'dnes', '&class=class_3a', true),
        $t('t-priprava', 'priprava79', 'dnes'),
        $t('t-streda', 'streda77', 'dnes', '', true),
        $t('t-tyden', 'tyden74', 'dnes', ''),
        $t('t-prehled', 'prehled', 'dnes'),
        // učitel – Výuka
        $t('t-hromadne', 'hromadne74', 'vyuka', '', true),
        $t('t-schvalovani', 'schvalovani', 'vyuka'),
        $t('t-ulohy', 'ulohy75', 'vyuka'),
        // učitel – Žáci (třída)
        $t('t-mapa', 'mapa801', 'trida'),
        $t('t-vysledky', 'class_results', 'trida'),
        $t('t-detail', 'student360', 'trida'),
        $t('t-pokrok', 'pokrok80', 'trida'),
        $t('t-pristupy', 'pristupy', 'trida'),
        // učitel – Správa
        $t('t-sprava', 'sprava_prehled', 'sprava', ''),
        // žák
        $s('s-domu', 'dashboard', '', true),
        $s('s-hodina', 'hodina'),
        $s('s-ulohy', 'ulohy'),
        $s('s-uloha', 'ulohy', '&uloha=lnx_chmod', true),
        $s('s-pokrok', 'pokrok'),
        $s('s-cviceni', 'cviceni'),
        $s('s-opakovat', 'opakovat'),
        $s('s-profil', 'profile'),
        $s('s-lab', 'lab'),
        $s('s-prikazy', 'prikazy'),
        $s('s-materialy', 'materialy'),
        $s('s-vysledky', 'vysledky'),
        $s('s-prihlaseni', '', '', false, 'anon'),
    ];
}

/**
 * Schválené výjimky: [kontrola => [id stránky (nebo '*') => důvod]]. Každá je v BUILD_MANIFEST_V80_2.md (Změny limitů auditů).
 * Kontroly: small (cíle < 44 px), tiny (písmo < 14 px), contrast, headings, landmarks, focus, dark, zoom.
 *
 * @return array<string,array<string,string>>
 */
function v802_exceptions(): array
{
    return [];
}

/** Je pro stránku $page a kontrolu $kind schválená výjimka? */
function v802_excepted(string $kind, string $page): bool
{
    $ex = v802_exceptions()[$kind] ?? [];
    return isset($ex[$page]) || isset($ex['*']);
}
