<?php

declare(strict_types=1);

/**
 * v53 · Založení školních účtů.
 *   php tools/v53_provision_accounts.php                  – založí účty žáků (v58: jednorázová hesla) a vypíše přehled bez hesel
 *   php tools/v53_provision_accounts.php --demo=class_2a  – přidá demo účet pro třídu
 *   php tools/v53_provision_accounts.php --teacher        – vygeneruje učitelský klíč a secret soubor
 *   php tools/v53_provision_accounts.php --reset=mail     – vydá účtu nové jednorázové heslo (vypíše ho jen do terminálu)
 *   Hesla celé třídy: php tools/v58_issue_passwords.php --class=<id>, kartičky: teacher.php?tab=pristupy
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require_once $root . '/accounts_v53.php';

$args = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--([a-z_]+)(?:=(.*))?$/', $arg, $m)) $args[$m[1]] = $m[2] ?? '1';
}

if (isset($args['reset'])) {
    $otp = acc53_reset_password((string)$args['reset'], 'cli');
    echo 'Nové jednorázové heslo účtu ' . $args['reset'] . ': ' . $otp . PHP_EOL;
    echo 'Platí ' . ACC58_OTP_TTL_DAYS . ' dní, po přihlášení si ho žák musí změnit.' . PHP_EOL;
    exit(0);
}

if (isset($args['teacher'])) {
    $key = bin2hex(random_bytes(24));
    $name = (string)($args['teacher_name'] ?? 'Adrian Staněk');
    $path = educanet_recommended_secret_path();
    $existing = is_file($path) ? (require $path) : [];
    $secrets = array_merge(is_array($existing) ? $existing : [], [
        'teacher_export_key' => $key,
        'teacher_name' => $name,
        'teacher_role' => 'admin',
        'teacher_team_id' => (string)($existing['teacher_team_id'] ?? 'educanet-teachers'),
        'teacher_team_name' => (string)($existing['teacher_team_name'] ?? 'EDUCANET učitelé'),
    ]);
    $php = "<?php\n\n// EDUCANET · učitelský přístup. Soubor patří MIMO veřejný web root.\nreturn " . var_export($secrets, true) . ";\n";
    if (!is_dir(dirname($path))) @mkdir(dirname($path), 0770, true);
    if (@file_put_contents($path, $php) === false) { fwrite(STDERR, 'Nelze zapsat ' . $path . PHP_EOL); exit(1); }
    @chmod($path, 0640);
    echo 'UČITELSKÝ ÚČET' . PHP_EOL;
    echo '  adresa:  /teacher.php' . PHP_EOL;
    echo '  jméno:   ' . $name . PHP_EOL;
    echo '  klíč:    ' . $key . PHP_EOL;
    echo '  uloženo: ' . $path . PHP_EOL;
    exit(0);
}

if (isset($args['demo'])) {
    $classId = (string)$args['demo'];
    if (!isset($modules[$classId])) { fwrite(STDERR, 'Neznámá třída ' . $classId . PHP_EOL); exit(1); }
    $label = 'Demo ' . (string)$modules[$classId]['name'];
    $email = 'demo.' . str_replace('class_', '', $classId) . '@' . google_workspace_domain();
    $res = acc53_ensure_account($label, $classId, ['email' => $email, 'demo' => true, 'by' => 'cli']);
    echo 'DEMO ÚČET ' . $modules[$classId]['name'] . ' · ' . $modules[$classId]['subject'] . PHP_EOL;
    echo '  e-mail: ' . $res['email'] . PHP_EOL;
    echo '  heslo:  ' . ($res['created'] ? (string)$res['password'] . ' (jednorázové, platí ' . ACC58_OTP_TTL_DAYS . ' dní)' : 'účet už existoval – nové heslo: --reset=' . $res['email']) . PHP_EOL;
    exit(0);
}

$stats = acc53_provision_all($modules, true, 'cli');
echo 'Založeno nových účtů: ' . $stats['created'] . PHP_EOL . PHP_EOL;
foreach ($modules as $classId => $module) {
    $rows = acc53_class_accounts((string)$classId);
    if (!$rows) continue;
    echo strtoupper((string)$module['name']) . ' · ' . $module['subject'] . ' (' . count($rows) . ')' . PHP_EOL;
    foreach ($rows as $row) {
        echo '  ' . str_pad($row['label'], 26) . str_pad($row['email'], 34)
            . $row['password_hint'] . ($row['expires_at'] ? ' (do ' . date('j. n. Y', (int)$row['expires_at']) . ')' : '')
            . ($row['demo'] ? ' · demo' : '') . PHP_EOL;
    }
    echo PHP_EOL;
}
echo 'Nové účty mají jednorázová hesla (' . ACC58_OTP_TTL_DAYS . ' dní). Kartičky: teacher.php?tab=pristupy nebo php tools/v58_issue_passwords.php --class=<id>.' . PHP_EOL;
echo 'Po prvním přihlášení si žák nastaví vlastní heslo (min. ' . LOCAL_PASSWORD_MIN_LENGTH . ' znaků, písmeno + číslice).' . PHP_EOL;
