<?php

declare(strict_types=1);

/**
 * EDUCANET v59 · správa učitelských účtů z příkazové řádky (AUTHZ58-07). Jediná cesta obnovy přístupu (bez webového break-glass).
 *
 *   php tools/v59_teacher_accounts.php create-admin --login=jan.novak --name="Jan Novák"
 *   php tools/v59_teacher_accounts.php reset   --login=jan.novak        (nové jednorázové heslo, odhlásí relace)
 *   php tools/v59_teacher_accounts.php disable --login=… | enable --login=… | unlock --login=…
 *   php tools/v59_teacher_accounts.php assign  --login=… --class=class_3a[:networks|graphics|*]
 *   php tools/v59_teacher_accounts.php unassign --login=… --class=class_3a
 *   php tools/v59_teacher_accounts.php list
 *
 * Jednorázové heslo se vypíše jen na STDOUT (nikam se neukládá ani neloguje). První create-admin zapne režim účtů –
 * od té chvíle sdílený učitelský klíč neplatí. Úložiště: storage/ nebo EDUCANET_STORAGE_DIR.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/teacher_accounts_v59.php';
require_once dirname(__DIR__) . '/teacher_scope_v59.php';

$command = (string)($argv[1] ?? '');
// getopt() končí u prvního ne-přepínače (příkazu) – přepínače --klic=hodnota se proto čtou ručně.
$opts = [];
foreach (array_slice($argv, 2) as $arg) {
    if (preg_match('/^--(login|name|class|role)=(.*)$/s', (string)$arg, $m) === 1) $opts[$m[1]] = $m[2];
}
$fail = static function (string $message): never { fwrite(STDERR, 'CHYBA: ' . $message . PHP_EOL); exit(1); };
$findLogin = static function () use ($opts, $fail): array {
    $login = teacher59_normalize_login((string)($opts['login'] ?? ''));
    if ($login === '') $fail('Chybí --login=…');
    $account = teacher59_find_by_login($login);
    if ($account === null) $fail('Účet „' . $login . '“ neexistuje.');
    return $account;
};
$printOtp = static function (array $account, string $otp): void {
    echo 'Přihlašovací jméno: ' . $account['login'] . PHP_EOL;
    echo 'Jednorázové heslo:  ' . $otp . PHP_EOL;
    echo 'Platí do:           ' . date('j. n. Y H:i', (int)($account['otp']['expires_at'] ?? 0)) . PHP_EOL;
    echo 'Heslo se nikam neukládá – předejte ho osobně. Po prvním přihlášení si učitel nastaví vlastní heslo.' . PHP_EOL;
};
$parseClass = static function () use ($opts, $fail): array {
    $raw = (string)($opts['class'] ?? '');
    if ($raw === '') $fail('Chybí --class=class_3a[:networks|graphics|*]');
    [$classId, $subject] = array_pad(explode(':', $raw, 2), 2, '*');
    return ['class_id' => $classId, 'subject_id' => $subject === '' ? '*' : $subject];
};

try {
    if ($command !== 'create-admin' && $command !== 'list' && teacher59_mode() === 'legacy') {
        $fail('Učitelské účty ještě nejsou zapnuté. Nejdřív: create-admin --login=… --name=…');
    }
    switch ($command) {
        case 'create-admin':
            $result = teacher59_account_create(['login' => (string)($opts['login'] ?? ''), 'display_name' => (string)($opts['name'] ?? ''), 'role' => 'admin', 'assignments' => []], 'cli');
            echo 'Administrátorský účet je založený. Režim učitelských účtů je zapnutý (sdílený klíč už neplatí).' . PHP_EOL;
            $printOtp($result['account'], $result['otp']);
            // SEC59-05: pojistka mimo úložiště + úklid sdíleného klíče (bez vypsání jakéhokoli tajemství).
            echo PHP_EOL . 'DŮLEŽITÉ – dokončete zabezpečení:' . PHP_EOL;
            echo '  1. Nastavte na serveru proměnnou prostředí ' . TEACHER59_REQUIRED_ENV . '=1 (nebo teacher_accounts_required => true v souboru tajemství).'
                . PHP_EOL . '     Bez ní by chybějící soubor účtů (obnova starší zálohy, jiný EDUCANET_STORAGE_DIR) vrátil sdílený klíč s právy admina.' . PHP_EOL;
            echo '     Stav pojistky teď: ' . (teacher59_accounts_required() ? 'zapnutá' : 'NENASTAVENÁ') . PHP_EOL;
            echo '  2. Starý sdílený učitelský klíč (EDUCANET_TEACHER_EXPORT_KEY / teacher_export_key) odstraňte, nebo ho vyměňte za nový náhodný.' . PHP_EOL;
            break;
        case 'reset':
            $result = teacher59_account_reset((string)$findLogin()['id'], 'cli');
            echo 'Nové jednorázové heslo vydáno, všechny relace účtu jsou odhlášené.' . PHP_EOL;
            $printOtp($result['account'], $result['otp']);
            break;
        case 'disable':
        case 'enable':
            $account = $findLogin();
            teacher59_account_set_status((string)$account['id'], $command === 'disable' ? 'disabled' : 'active', 'cli');
            echo 'Účet ' . $account['login'] . ' je ' . ($command === 'disable' ? 'deaktivovaný.' : 'aktivní.') . PHP_EOL;
            break;
        case 'unlock':
            $account = $findLogin();
            teacher59_unlock_login((string)$account['login']);
            teacher59_log('unlocked', 'cli', (string)$account['id']);
            echo 'Zámek přihlášení účtu ' . $account['login'] . ' je zrušený.' . PHP_EOL;
            break;
        case 'assign':
        case 'unassign':
            $account = $findLogin();
            $target = $parseClass();
            $rows = array_values(array_filter((array)($account['assignments'] ?? []), static fn($r): bool => is_array($r) && (string)($r['class_id'] ?? '') !== $target['class_id']));
            if ($command === 'assign') $rows[] = $target;
            $role = isset($opts['role']) ? (string)$opts['role'] : (string)$account['role'];
            $fresh = teacher59_account_update_access((string)$account['id'], $role, $rows, 'cli');
            echo 'Přiřazení účtu ' . $fresh['login'] . ': ' . (implode(', ', array_map(static fn(array $r): string => $r['class_id'] . ':' . $r['subject_id'], (array)$fresh['assignments'])) ?: 'žádné') . PHP_EOL;
            break;
        case 'list':
            if (teacher59_mode() === 'legacy') { echo 'Režim: legacy (sdílený klíč). Účty zapne create-admin.' . PHP_EOL; break; }
            echo 'Režim: ' . teacher59_mode() . PHP_EOL;
            foreach (teacher59_accounts() as $row) {
                $classes = implode(', ', array_map(static fn(array $r): string => (string)$r['class_id'] . ':' . (string)$r['subject_id'], array_filter((array)($row['assignments'] ?? []), 'is_array')));
                printf("%-24s %-10s %-9s %-8s %s%s\n", (string)$row['login'], (string)$row['role'], (string)$row['status'], teacher59_otp_status($row),
                    $classes !== '' ? $classes : '-', teacher59_is_locked((string)$row['login']) ? ' [zamčeno]' : '');
            }
            break;
        default:
            fwrite(STDERR, "Použití: php tools/v59_teacher_accounts.php create-admin|reset|disable|enable|unlock|assign|unassign|list [--login=…] [--name=…] [--class=class_3a[:networks]]\n");
            exit(2);
    }
} catch (Throwable $e) {
    $fail($e->getMessage());
}
exit(0);
