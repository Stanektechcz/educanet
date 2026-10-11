<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · HTTP část auditu tools/v73_reflection_audit.php (načítá se z něj; používá jeho $tmp, $cacheDir, $check).
 *   a) bez účtů (klíč učitele): záložka Reflexe hodiny, CSRF 419, uložení, limit 300 znaků, neznámá akce, „Minule“ v Dnešní hodině,
 *   b) v režimu účtů: cizí třída 403, asistent jen čte (bez formuláře, POST 403), učitel třídy uloží, admin čte, jiný učitel ne.
 */

$posted = static fn(string $class, int $lesson): array => array_values(array_filter(lr73_entries($class, storage_read(lr73_path(), false)), static fn(array $r): bool => (int)$r['lesson'] === $lesson));
audit_prewarm_accounts($GLOBALS['modules']);
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V73R_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$h = Harness::start($env);
try {
    audit_login_teacher($h, V73R_KEY, 'Audit Učitel');
    $page = $h->request('GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_4a', 'lesson' => '3']);
    $body = (string)$page['body'];
    $check('HTTP: záložka Reflexe hodiny – 200, CSS v73, formulář lr73_save (stav povinný, 3 volby, úsek, úkol, rychlé volby, 3 texty s maxlength 300, upozornění bez jmen a známek), pokrytí',
        audit_response_clean($page) && str_contains($body, '<h1>Reflexe hodiny</h1>') && str_contains($body, 'assets/cockpit-v73.css') && str_contains($body, 'value="lr73_save"')
        && substr_count($body, 'name="state"') === 3 && str_contains($body, 'required') && str_contains($body, 'name="stop_seg"') && str_contains($body, 'name="stop_task"')
        && substr_count($body, 'maxlength="300"') === 3 && str_contains($body, 'nepište jména žáků ani známky') && str_contains($body, 'S reflexí'));
    $csrf = (string)$h->csrfToken($body);
    $noCsrf = $h->request('POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_4a', 'lesson' => '3', 'state' => 'cele'], ['follow_redirects' => false]);
    $okPost = $h->request('POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_4a', 'lesson' => '3', 'state' => 'cast', 'stop_seg' => '0', 'picks_adjust' => ['cas'],
        'next' => 'Začít opakováním.', 'csrf' => $csrf, 'return_tab' => 'reflexe73'], ['follow_redirects' => false]);
    $saved = $posted('class_4a', 3);
    $check('HTTP: lr73_save bez CSRF → 419; s CSRF uloží (303 zpět na Reflexi téže třídy a lekce)', (int)$noCsrf['status'] === 419 && in_array((int)$okPost['status'], [302, 303], true)
        && str_contains((string)($okPost['headers']['Location'] ?? ''), 'tab=reflexe73') && str_contains((string)($okPost['headers']['Location'] ?? ''), 'lesson=3')
        && count($saved) === 1 && $saved[0]['state'] === 'cast' && $saved[0]['picks_adjust'] === ['cas'] && $saved[0]['next'] === 'Začít opakováním.');
    $long = $h->request('POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_4a', 'lesson' => '2', 'state' => 'cele', 'worked' => str_repeat('x', 301), 'csrf' => $csrf], ['follow_redirects' => false]);
    $noState = $h->request('POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_4a', 'lesson' => '2', 'csrf' => $csrf], ['follow_redirects' => false]);
    $unknown = $h->request('POST', '/teacher.php', ['action' => 'lr73_delete', 'class_id' => 'class_4a', 'lesson' => '3', 'csrf' => $csrf], ['follow_redirects' => false]);
    $badLesson = $h->request('POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_4a', 'lesson' => '40', 'state' => 'cele', 'csrf' => $csrf], ['follow_redirects' => false]);
    $check('HTTP: text 301 znaků, chybějící stav a lekce 40 se neuloží; neznámá lr73_* akce je zakázaná', $posted('class_4a', 2) === [] && $posted('class_4a', 40) === [] && (int)$unknown['status'] !== 200
        && in_array((int)$long['status'], [302, 303], true) && in_array((int)$noState['status'], [302, 303], true) && count($posted('class_4a', 3)) === 1);
    $flash = (string)$h->request('GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_4a', 'lesson' => '3'])['body'];
    $check('HTTP: uložená reflexe je v seznamu (bez jmen, „odučena část“, poznámka na příště)', str_contains($flash, 'Na příště:</strong> Začít opakováním.') && str_contains($flash, 'odučena část'));
} finally {
    $h->stop();
}

$acc = v59sf_create_accounts();
$h2 = Harness::start($env);
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
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_1a']);
    $aForeign = $as('a', 'POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_3a', 'lesson' => '8', 'state' => 'cele', 'csrf' => (string)$h2->csrfToken((string)$aPage['body'])], ['follow_redirects' => false]);
    $aGetForeign = $as('a', 'GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A – reflexe cizí třídy 3.A POST i GET → 403, nic se neuloží', audit_response_clean($aPage) && (int)$aForeign['status'] === 403 && (int)$aGetForeign['status'] === 403 && $posted('class_3a', 8) === []);
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_3a']);
    $sPost = $as('s', 'POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_3a', 'lesson' => '8', 'state' => 'cele', 'csrf' => (string)$h2->csrfToken((string)$sPage['body'])], ['follow_redirects' => false]);
    $check('účty: asistent 3.A jen čte – bez formuláře („Máte jen čtení“), POST lr73_save zamítnut oprávněním (content.manage), nic se neuloží [' . (int)$sPost['status'] . ']', audit_response_clean($sPage) && str_contains((string)$sPage['body'], 'Máte jen čtení')
        && !str_contains((string)$sPage['body'], 'value="lr73_save"') && in_array((int)$sPost['status'], [302, 303, 403], true) && $posted('class_3a', 8) === []);
    $bPage = $as('b', 'GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_3a', 'lesson' => '8']);
    $bPost = $as('b', 'POST', '/teacher.php', ['action' => 'lr73_save', 'class_id' => 'class_3a', 'lesson' => '8', 'state' => 'neoduceno', 'next' => 'Přesunout na příští týden.', 'csrf' => (string)$h2->csrfToken((string)$bPage['body'])], ['follow_redirects' => false]);
    $adminView = (string)$as('admin', 'GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_3a', 'lesson' => '8'])['body'];
    $sAfter = (string)$as('s', 'GET', '/teacher.php', ['tab' => 'reflexe73', 'class' => 'class_3a', 'lesson' => '8'])['body'];
    $bEntry = $posted('class_3a', 8)[0] ?? [];
    $check('účty: učitel 3.A uloží (autor = hash účtu), administrátor jeho reflexi čte („jiný učitel“), asistent třídy ji nevidí (čte jen autor a administrátor)',
        in_array((int)$bPost['status'], [302, 303], true) && ($bEntry['author'] ?? '') === substr(hash('sha256', 'lr73|' . teacher59_owner_key_for($acc['id']['b'])), 0, 16)
        && str_contains($adminView, 'Přesunout na příští týden.') && str_contains($adminView, 'jiný učitel') && !str_contains($sAfter, 'Přesunout na příští týden.'));
} finally {
    $h2->stop();
}
