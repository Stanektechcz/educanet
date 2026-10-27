<?php

declare(strict_types=1);

// Self-contained regression audit: no authentication, network, or production storage writes.
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once dirname(__DIR__) . '/virtualni_dilna_v82.php';

function vd82_assert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException('VD82_AUDIT_FAIL: ' . $message);
}
$catalog = vd82_catalog();
vd82_assert(count($catalog) === 2, 'Počet samostatných simulací');
vd82_assert(count($catalog['pc']['steps']) === 7, 'Počítačové scénáře');
vd82_assert(count($catalog['sit']['steps']) === 8, 'Síťové scénáře');

$start = vd82_default_state();
vd82_assert(vd82_progress($start,'pc') === ['done'=>0, 'total'=>7], 'Výchozí postup');
[$blocked,$ok] = vd82_decide($start,'pc','pdf','pdf');
vd82_assert(!$ok && vd82_progress($blocked,'pc')['done'] === 0, 'Závislost před dokončením');
vd82_assert($blocked['errors'] === 1, 'Závislost eviduje chybu');
[$wrong,$ok] = vd82_decide($start,'pc','copy','delete');
vd82_assert(!$ok && !$wrong['done']['pc'], 'Nesprávný zásah není hotovým krokem');
[$copy,$ok] = vd82_decide($start,'pc','copy','copy');
vd82_assert($ok && vd82_progress($copy,'pc')['done'] === 1, 'Kopírování');
[$move,$ok] = vd82_decide($copy,'pc','move','move');
vd82_assert($ok, 'Přesun');
[$pdf,$ok] = vd82_decide($move,'pc','pdf','pdf');
vd82_assert($ok && vd82_progress($pdf,'pc')['done'] === 3, 'Export PDF po přípravě');
vd82_assert(vd82_progress($pdf,'sit')['done'] === 0, 'Izolace modulů');
[$again,$ok] = vd82_decide($pdf,'pc','copy','copy');
vd82_assert($ok && $again === $pdf, 'Idempotentní odpověď');

[$router,$ok] = vd82_decide($start,'sit','power','power');
vd82_assert($ok, 'Napájení routeru');
[$wan,$ok] = vd82_decide($router,'sit','wan','wan');
vd82_assert($ok, 'WAN');
[$wifi,$ok] = vd82_decide($wan,'sit','wifi','wpa');
vd82_assert($ok, 'Silné Wi-Fi');
[$password,$ok] = vd82_decide($wifi,'sit','password','strong');
vd82_assert($ok, 'Politika hesla');
[$noSpeed,$ok] = vd82_decide($password,'sit','speed','speed');
vd82_assert(!$ok, 'Test neproběhne bez LAN');
[$lan,$ok] = vd82_decide($password,'sit','lan','lan');
vd82_assert($ok, 'LAN');
[$speed,$ok] = vd82_decide($lan,'sit','speed','speed');
vd82_assert($ok, 'Test rychlosti');
[$foreign,$ok] = vd82_decide($speed,'sit','foreign','clients');
vd82_assert($ok, 'Ověření cizího klienta');
[$secure,$ok] = vd82_decide($foreign,'sit','isolate','secure');
vd82_assert($ok && vd82_progress($secure,'sit')['done'] === 8, 'Síť hotova');

vd82_assert($secure['grade_impact'] === false && $secure['xp_impact'] === false && $secure['mastery_impact'] === false, 'Formativní dopad');
vd82_assert(count($secure['history']) <= 40, 'Omezená historie');
vd82_assert(vd82_student_id('class_1a','a') !== vd82_student_id('class_2a','a'), 'Izolace tříd');
vd82_assert(vd82_student_id('class_1a','a') !== vd82_student_id('class_1a','b'), 'Izolace žáků');
echo "VD82_AUDIT_OK\n";
