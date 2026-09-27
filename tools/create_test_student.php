<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(400); exit("Spouštěj pouze přes SSH/CLI.\n"); }
require dirname(__DIR__) . '/bootstrap.php';

$options = getopt('', ['class::','email::','name::','password::','remove']);
$classId = (string)($options['class'] ?? 'class_1a');
$email = local_email_normalize((string)($options['email'] ?? 'demo.student@educanet.cz'));
$name = trim((string)($options['name'] ?? 'Testovací student'));
$password = (string)($options['password'] ?? '');

if (isset($options['remove'])) {
    // v58 (F2): odstranění účtu i vazby atomicky pod zámky obou souborů.
    $accountsPath = local_accounts_path();
    $mapPath = STORAGE_DIR . '/student_accounts.json.php';
    $removed = false;
    storage_update_many([$accountsPath, $mapPath], static function (array $data) use ($accountsPath, $mapPath, $email, &$removed): array {
        $existing = is_array($data[$accountsPath][$email] ?? null) ? $data[$accountsPath][$email] : null;
        if (!$existing) return $data;
        $id = (string)($existing['id'] ?? '');
        unset($data[$accountsPath][$email]);
        if ($id !== '') unset($data[$mapPath]['local:' . $id]);
        $removed = true;
        return $data;
    });
    if (!$removed) { echo "Testovací účet $email neexistuje.\n"; exit(0); }
    echo "Testovací účet $email byl odstraněn.\n";
    exit(0);
}

if (!isset($modules[$classId])) {
    fwrite(STDERR, "Neplatná třída. Použij class_1a, class_2a, class_3a nebo class_4a.\n");
    exit(2);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Neplatný e-mail.\n");
    exit(2);
}
if ($password === '') {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $password = 'Edu-';
    for ($i=0; $i<18; $i++) $password .= $alphabet[random_int(0, strlen($alphabet)-1)];
}
if (($error = local_password_validate($password)) !== null) {
    fwrite(STDERR, $error . "\n");
    exit(2);
}

$accountsPath = local_accounts_path();
$mapPath = STORAGE_DIR . '/student_accounts.json.php';
$hash = local_password_hash($password);
$id = '';
// v58 (F2): účet i vazba na třídu se zapíší atomicky pod zámky obou souborů.
storage_update_many([$accountsPath, $mapPath], static function (array $data) use ($accountsPath, $mapPath, $email, $name, $hash, $classId, &$id): array {
$existing = is_array($data[$accountsPath][$email] ?? null) ? $data[$accountsPath][$email] : null;
$id = is_array($existing) && !empty($existing['id']) ? (string)$existing['id'] : bin2hex(random_bytes(16));
$data[$accountsPath][$email] = [
    'id' => $id,
    'email' => $email,
    'name' => $name,
    'password_hash' => $hash,
    'created_at' => (string)($existing['created_at'] ?? date(DATE_ATOM)),
    'verified_at' => date(DATE_ATOM),
    'verification_token_hash' => null,
    'verification_expires_at' => null,
    'reset_token_hash' => null,
    'reset_expires_at' => null,
    'is_test_account' => true,
    'updated_at' => date(DATE_ATOM),
];
$data[$mapPath]['local:' . $id] = [
    'provider' => 'local',
    'email' => $email,
    'class_id' => $classId,
    'student_label' => $name,
    'linked_at' => date(DATE_ATOM),
    'is_test_account' => true,
];
return $data;
});

$label = (string)($modules[$classId]['name'] ?? $classId);
$subject = (string)($modules[$classId]['subject'] ?? '');
echo "\nEDUCANET testovací student je připravený.\n";
echo "----------------------------------------\n";
echo "Třída:  $label · $subject\n";
echo "Jméno:  $name\n";
echo "E-mail: $email\n";
echo "Heslo:  $password\n";
echo "----------------------------------------\n";
echo "Přihlášení: /?view=home#local-login\n";
echo "Účet je už ověřený a předem propojený s třídou.\n\n";
