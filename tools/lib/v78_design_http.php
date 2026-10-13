<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v78 · HTTP část tools/v78_design_tokens_audit.php (používá $tmp, $check).
 *   a) klíč učitele: záložka design78 200 bez chyb, tokeny/komponenty/JS v78, ukázka bez formuláře, GET nic nezapíše do úložiště,
 *      volba „tmavý“ → data-theme="dark" a tmavé vrstvy cockpitu; ostatní záložky (Dnešní hodina) CSS v78 nenačítají,
 *   b) režim účtů: admin 200 a položka v menu; učitel i asistent 403 a bez položky v menu.
 */

require_once __DIR__ . '/http_harness.php';
require_once __DIR__ . '/v59_scope_fixtures.php';

$v78Tree = static function (string $dir): string {
    $rows = [];
    if (!is_dir($dir)) return '';
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->isFile() && !str_ends_with($f->getFilename(), '.lock')) $rows[] = str_replace('\\', '/', substr($f->getPathname(), strlen($dir))) . '|' . hash_file('sha256', $f->getPathname());
    }
    sort($rows);
    return hash('sha256', implode("\n", $rows));
};
$demo = static function (string $body): string {
    $start = strpos($body, '<div class="ds78 ds-demo" id="ds78">');
    $end = strpos($body, '<div class="ds-toasts"');
    return $start !== false && $end !== false && $end > $start ? substr($body, $start, $end - $start) : '';
};

// ---------------------------------------------------------------- a) klíč učitele
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V78D_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    audit_login_teacher($h, V78D_KEY, 'Audit Design');
    $h->request('GET', '/teacher.php', ['tab' => 'design78']);
    $before = $v78Tree($tmp);
    $page = $h->request('GET', '/teacher.php', ['tab' => 'design78']);
    $after = $v78Tree($tmp);
    $body = (string)$page['body'];
    $d = $demo($body);
    $check('design78: 200 bez chyb, nadpis, tokeny + komponenty + ukázkové CSS a JS v78, kořen .ds78',
        audit_response_clean($page) && str_contains($body, '<h1>Design systém</h1>') && str_contains($body, 'assets/tokens-v78.css') && str_contains($body, 'assets/components-v78.css')
        && str_contains($body, 'assets/design-v78.css') && str_contains($body, 'assets/components-v78.js') && $d !== '');
    $rows = ds78_contrast_rows();
    $check('design78: tabulka kontrastu (' . count($rows) . ' z ' . count($rows) . ' dvojic AA), všech 18 komponent v ukázce',
        str_contains($d, count($rows) . ' z ' . count($rows) . ' dvojic splňuje') && count(array_filter(['ds-card', 'ds-status', 'ds-btn--primary', 'ds-btn--secondary', 'ds-btn--danger', 'ds-btn--icon', 'ds-badge', 'ds-stepper',
            'role="tablist"', 'ds-acc', 'ds-table', 'ds-field', 'ds-error', 'ds-empty', 'data-ds-toast', 'ds-dialog', 'ds-skeleton', 'ds-breadcrumb', 'ds-bottomnav', 'ds-drawer', 'ds-actionbar'],
            static fn(string $n): bool => str_contains($d, $n))) === 21);
    $check('design78: ukázka nic neodesílá (žádný <form>, žádné name= u polí) a GET nezapíše do dočasného úložiště', !str_contains($d, '<form') && !preg_match('~<(?:input|textarea|select)[^>]*\sname=~', $d) && $before === $after && $before !== '');
    $hod = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a'])['body'];
    $check('ostatní záložky CSS/JS v78 nenačítají (Dnešní hodina beze změny vzhledu)', !str_contains($hod, 'tokens-v78.css') && !str_contains($hod, 'components-v78'));
    $csrf = (string)$h->csrfToken($body);
    $h->request('POST', '/teacher.php', ['action' => 'teacher68_theme_set', 'theme' => 'dark', 'return' => '?tab=design78', 'csrf' => $csrf], ['follow_redirects' => false]);
    $dark = (string)$h->request('GET', '/teacher.php', ['tab' => 'design78'])['body'];
    $check('motiv „tmavý“: <html data-theme="dark"> a tmavé tokeny cockpitu bez media dotazu (tokeny v78 přepne :root[data-theme="dark"] .ds78)',
        preg_match('~<html lang="cs" data-theme="dark"~', $dark) === 1 && str_contains($dark, 'assets/tokens-dark-v67.css?v=67.0">'));
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- b) režim účtů
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V78D_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
$jars = [];
try {
    $as = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h2, $jarSwap, &$jars, $acc): array {
        if (!isset($jars[$who])) {
            $jarSwap([]);
            audit_login_teacher_account($h2, $acc['login'][$who], $acc['pw'][$who]);
            $jars[$who] = $jarSwap(null);
        }
        $jarSwap($jars[$who]);
        $r = $h2->request($method, $path, $fields, $headers);
        $jars[$who] = $jarSwap(null);
        return $r;
    };
    $adm = $as('admin', 'GET', '/teacher.php', ['tab' => 'design78']);
    $check('účty: administrátor vidí Design systém (200) a položku v menu Správa', audit_response_clean($adm) && str_contains((string)$adm['body'], '<h1>Design systém</h1>') && str_contains((string)$adm['body'], '?tab=design78'));
    $ta = $as('a', 'GET', '/teacher.php', ['tab' => 'design78'], ['follow_redirects' => false]);
    $tm = $as('a', 'GET', '/teacher.php', ['tab' => 'hodina']);
    $sa = $as('s', 'GET', '/teacher.php', ['tab' => 'design78'], ['follow_redirects' => false]);
    $check('účty: učitel i asistent → 403 a v menu odkaz na design78 nemají', (int)$ta['status'] === 403 && (int)$sa['status'] === 403 && !str_contains((string)$ta['body'], 'Tokeny, komponenty')
        && !str_contains((string)$tm['body'], '?tab=design78'));
} finally {
    $h2->stop();
}
