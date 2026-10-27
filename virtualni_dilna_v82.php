<?php

declare(strict_types=1);

/**
 * EDUCANET v82 – originální Virtuální dílna inspirovaná veřejně popsaným
 * modelem simulace eduKuba. Neobsahuje kód, grafiku ani chráněný obsah třetích stran.
 *
 * Formativní evidence: bez vlivu na známky, XP nebo Skill Mastery.
 * Každé rozhodnutí ověřuje server; GET je vždy read-only.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) {
    http_response_code(403); exit;
}

function vd82_catalog(): array
{
    return [
        'pc' => [
            'label' => 'Počítačová dílna',
            'intro' => 'Pracuj v bezpečném školním počítači. Nauč se spravovat soubory, ověřovat práva, vytvořit PDF a odstranit příčinu zpomalení.',
            'steps' => [
                'copy' => ['title'=>'Obrázek patří do projektu', 'task'=>'V Home leží obrazek.png. Přenes kopii do Data/Projekt, ale originál zachovej.', 'options'=>['copy'=>'Kopírovat do Data/Projekt', 'move'=>'Přesunout do Data/Projekt', 'delete'=>'Smazat původní soubor'], 'answer'=>'copy', 'hint'=>'Projekt potřebuje kopii; původní soubor musí zůstat v Home.', 'requires'=>[]],
                'move' => ['title'=>'Uspořádej poznámky', 'task'=>'V Home leží poznamky.txt. Má být pouze ve složce Data/Projekt.', 'options'=>['copy'=>'Kopírovat', 'move'=>'Přesunout', 'delete'=>'Smazat'], 'answer'=>'move', 'hint'=>'Když soubor nesmí zůstat na původním místě, použij přesun.', 'requires'=>[]],
                'rights' => ['title'=>'Kde mohu ukládat?', 'task'=>'Student zkusil uložit soubor do systémové složky Windows. Co musí respektovat?', 'options'=>['admin'=>'Vypnout kontrolu práv', 'rights'=>'Oprávnění složek a vlastní pracovní prostor', 'retry'=>'Zkoušet znovu bez změny'], 'answer'=>'rights', 'hint'=>'Systémové složky nejsou určeny pro běžné studentské soubory.', 'requires'=>[]],
                'pdf' => ['title'=>'Odevzdej referát', 'task'=>'Projekt obsahuje obrázek a poznámky. Potřebuješ přenositelné odevzdání.', 'options'=>['pdf'=>'Exportovat dokument jako PDF', 'exe'=>'Přejmenovat jej na .exe', 'screen'=>'Poslat pouze screenshot'], 'answer'=>'pdf', 'hint'=>'Zvol formát, který zachovává stránkování a otevře jej i jiné zařízení.', 'requires'=>['copy','move']],
                'scan' => ['title'=>'Podezřelý soubor', 'task'=>'Antivirus varuje před škodlivým souborem. Jak začneš?', 'options'=>['run'=>'Spustit pro ověření', 'scan'=>'Provést bezpečné skenování', 'disable'=>'Vypnout ochranu'], 'answer'=>'scan', 'hint'=>'Neznámý soubor nespouštěj.', 'requires'=>[]],
                'quarantine' => ['title'=>'Nalezená hrozba', 'task'=>'Sken našel hrozbu. Jak bezpečně zabráníš spuštění?', 'options'=>['ignore'=>'Ignorovat upozornění', 'quarantine'=>'Přesunout do karantény', 'share'=>'Odeslat spolužákovi'], 'answer'=>'quarantine', 'hint'=>'Karanténa soubor izoluje bez jeho spouštění.', 'requires'=>['scan']],
                'slow' => ['title'=>'Počítač je pomalý', 'task'=>'Správce úloh ukazuje nereagující testovací proces. Co provedeš?', 'options'=>['end'=>'Ukončit konkrétní nereagující proces', 'remove'=>'Smazat složku Windows', 'power'=>'Odpojit elektřinu'], 'answer'=>'end', 'hint'=>'Pracuj jen s označeným procesem, ne se systémovými soubory.', 'requires'=>['quarantine']],
            ],
        ],
        'sit' => [
            'label' => 'Síťová dílna',
            'intro' => 'Zapoj domácí router, nastav Wi-Fi, ověř kvalitu spojení a odstraň cizí zařízení. Jde o simulaci, ne zásah do skutečné sítě.',
            'steps' => [
                'power' => ['title'=>'Router nereaguje', 'task'=>'Router je vypnutý. Jaký je první fyzický krok?', 'options'=>['power'=>'Připojit napájení', 'reset'=>'Tovární reset', 'wifi'=>'Změnit heslo Wi-Fi'], 'answer'=>'power', 'hint'=>'Bez napájení nepoběží žádné porty ani Wi-Fi.', 'requires'=>[]],
                'wan' => ['title'=>'Internet od poskytovatele', 'task'=>'Kam připojíš kabel od modemu/poskytovatele?', 'options'=>['lan'=>'Do portu LAN', 'wan'=>'Do portu WAN', 'usb'=>'Do USB'], 'answer'=>'wan', 'hint'=>'WAN spojuje router s nadřazenou sítí.', 'requires'=>['power']],
                'lan' => ['title'=>'Školní pracovní stanice', 'task'=>'Kam patří ethernetový kabel z počítače?', 'options'=>['lan'=>'Do portu LAN', 'wan'=>'Do portu WAN', 'power'=>'Do napájení'], 'answer'=>'lan', 'hint'=>'LAN je místní síť za routerem.', 'requires'=>['power']],
                'wifi' => ['title'=>'Zprovozni bezdrátovou síť', 'task'=>'Který režim zapne přístupový bod s vhodným zabezpečením?', 'options'=>['open'=>'Otevřená síť', 'wpa'=>'WPA2/WPA3 Personal', 'wep'=>'WEP'], 'answer'=>'wpa', 'hint'=>'WEP ani otevřená síť neposkytují přiměřené zabezpečení.', 'requires'=>['wan']],
                'password' => ['title'=>'Silné heslo Wi-Fi', 'task'=>'Zvol správnou politiku přístupového hesla.', 'options'=>['short'=>'12345678', 'default'=>'Ponechat heslo od výrobce', 'strong'=>'Unikátní dlouhé heslo (nejméně 12 znaků)'], 'answer'=>'strong', 'hint'=>'Výchozí a běžně používaná hesla změň.', 'requires'=>['wifi']],
                'speed' => ['title'=>'Ověř spojení', 'task'=>'Jak ověříš rychlost stahování, odesílání i odezvu?', 'options'=>['speed'=>'Test download / upload / ping', 'ping'=>'Zkontrolovat jen ikonku Wi-Fi', 'dns'=>'Změnit DNS bez měření'], 'answer'=>'speed', 'hint'=>'Použij měření a rozlišuj latenci od propustnosti.', 'requires'=>['lan','password']],
                'foreign' => ['title'=>'Cizí zařízení v síti', 'task'=>'V seznamu klientů vidíš neznámé zařízení. Jak začneš?', 'options'=>['clients'=>'Prověřit seznam klientů a identitu zařízení', 'nothing'=>'Ignorovat', 'all'=>'Odpojit všechna známá zařízení'], 'answer'=>'clients', 'hint'=>'Nejdřív ověř, zda opravdu jde o neznámé zařízení.', 'requires'=>['speed']],
                'isolate' => ['title'=>'Zabezpeč síť', 'task'=>'Neznámý klient byl potvrzen. Který postup je bezpečný?', 'options'=>['share'=>'Sdílet přístupové heslo', 'secure'=>'Změnit heslo, odpojit klienta a ověřit WPA2/WPA3', 'rename'=>'Jen přejmenovat síť'], 'answer'=>'secure', 'hint'=>'Samotná změna názvu sítě klienta nezablokuje.', 'requires'=>['foreign']],
            ],
        ],
    ];
}

function vd82_default_state(): array
{
    return ['done'=>['pc'=>[], 'sit'=>[]], 'attempts'=>0, 'errors'=>0, 'hints'=>0, 'history'=>[], 'last_at'=>null, 'hint_for'=>null, 'grade_impact'=>false, 'xp_impact'=>false, 'mastery_impact'=>false];
}

function vd82_progress(array $state, string $track): array
{
    $steps = vd82_catalog()[$track]['steps'] ?? [];
    $done = (array)($state['done'][$track] ?? []);
    return ['done'=>count(array_intersect(array_keys($steps), array_keys($done))), 'total'=>count($steps)];
}

/**
 * Pure reducer – action choice is compared to an independent answer key.
 * Returns [new state, success flag, message]. Never executes shell/network commands.
 */
function vd82_decide(array $old, string $track, string $step, string $choice): array
{
    $catalog = vd82_catalog();
    if (!isset($catalog[$track]['steps'][$step])) return [$old, false, 'Neznámý úkol.'];
    $def = $catalog[$track]['steps'][$step];
    $state = array_replace_recursive(vd82_default_state(), $old);
    $done = (array)($state['done'][$track] ?? []);
    $state['attempts'] = min(1000000, (int)$state['attempts'] + 1);
    if (isset($done[$step])) return [$old, true, 'Úkol už je hotový.'];
    $missing = array_values(array_diff((array)$def['requires'], array_keys($done)));
    $ok = $missing === [] && $choice === $def['answer'];
    if ($ok) {
        $state['done'][$track][$step] = gmdate('c');
        $message = 'Hotovo. Postup se uložil.';
    } else {
        $state['errors'] = min(1000000, (int)$state['errors'] + 1);
        $message = $missing !== [] ? 'Nejdřív dokonči předchozí návazné kroky.' : 'Tohle řešení neodpovídá zadání. Můžeš to zkusit znovu.';
    }
    $state['last_at'] = gmdate('c');
    $state['history'][] = ['at'=>$state['last_at'], 'track'=>$track, 'step'=>$step, 'ok'=>$ok];
    $state['history'] = array_slice($state['history'], -40);
    return [$state, $ok, $message];
}

function vd82_student_id(string $classId, string $studentKey): string
{
    return hash('sha256', $classId . "\0" . $studentKey);
}

function vd82_storage_path(): string
{
    return STORAGE_DIR . '/virtualni_dilna_v82.json.php';
}

function vd82_read(string $classId, string $studentKey): array
{
    if ($studentKey === '') return vd82_default_state();
    $all = storage_read_request(vd82_storage_path(), true);
    $row = $all['students'][vd82_student_id($classId, $studentKey)] ?? null;
    return is_array($row) && ($row['class_id'] ?? '') === $classId
        ? array_replace_recursive(vd82_default_state(), (array)($row['state'] ?? []))
        : vd82_default_state();
}

function vd82_save_choice(string $classId, string $studentKey, string $track, string $step, string $choice): array
{
    if (!in_array($classId, ['class_1a','class_2a','class_3a','class_4a'], true) || $studentKey === '') {
        throw new RuntimeException('Pro pokračování se přihlas ke své třídě.');
    }
    $id = vd82_student_id($classId, $studentKey);
    $result = [false,'Odpověď se nepodařilo uložit.'];
    storage_update(vd82_storage_path(), static function (array $all) use ($id, $classId, $track, $step, $choice, &$result): array {
        $row = is_array($all['students'][$id] ?? null) ? $all['students'][$id] : [];
        if (isset($row['class_id']) && $row['class_id'] !== $classId) throw new RuntimeException('Záznam nepatří do třídy.');
        $old = is_array($row['state'] ?? null) ? $row['state'] : vd82_default_state();
        [$state,$ok,$message] = vd82_decide($old,$track,$step,$choice);
        $all['students'][$id] = ['class_id'=>$classId, 'state'=>$state];
        $result = [$ok,$message];
        return $all;
    });
    return $result;
}

function vd82_save_hint(string $classId, string $studentKey, string $track, string $step): void
{
    if (!isset(vd82_catalog()[$track]['steps'][$step]) || $studentKey === '') throw new RuntimeException('Neplatná nápověda.');
    $id = vd82_student_id($classId, $studentKey);
    storage_update(vd82_storage_path(), static function (array $all) use ($id,$classId,$track,$step): array {
        $row = is_array($all['students'][$id] ?? null) ? $all['students'][$id] : [];
        if (isset($row['class_id']) && $row['class_id'] !== $classId) throw new RuntimeException('Záznam nepatří do třídy.');
        $state = is_array($row['state'] ?? null) ? $row['state'] : vd82_default_state();
        $state['hints'] = min(1000000, (int)($state['hints'] ?? 0) + 1);
        $state['hint_for'] = [$track, $step];
        $state['last_at'] = gmdate('c');
        $all['students'][$id] = ['class_id'=>$classId, 'state'=>$state];
        return $all;
    });
}

function vd82_teacher_stats(string $classId): array
{
    $all = storage_read_request(vd82_storage_path(), true);
    $result = [];
    foreach ((array)($all['students'] ?? []) as $id=>$row) {
        if (!is_array($row) || ($row['class_id'] ?? '') !== $classId) continue;
        if (!preg_match('/^[a-f0-9]{64}$/', (string)$id)) continue;
        $result[(string)$id] = (array)($row['state'] ?? []);
    }
    return $result;
}

function vd82_render_teacher_stats(string $classId): void
{
    $rows = vd82_teacher_stats($classId);
    $roster = function_exists('project_students_for_class') ? project_students_for_class($classId) : [];
    $labels = [];
    foreach ((array)$roster as $key=>$student) $labels[vd82_student_id($classId,(string)$key)] = (string)($student['label'] ?? 'Student');
    uasort($rows, static fn(array $a,array $b): int => strcmp((string)($b['last_at']??''),(string)($a['last_at']??'')));
    echo '<section class="teacher-panel" aria-labelledby="vd82-teacher-title"><div class="teacher-panel-head"><div><h2 id="vd82-teacher-title">Virtuální dílna – praktické simulace</h2><p>Formativní postup · chyby · nápovědy; bez automatických známek a XP.</p></div></div>';
    if (!$rows) { echo '<p>Pro tuto třídu zatím nejsou uloženy žádné výsledky.</p></section>'; return; }
    echo '<div style="overflow-x:auto"><table><thead><tr><th scope="col">Student</th><th scope="col">Počítač</th><th scope="col">Síť</th><th scope="col">Pokusy</th><th scope="col">Chyby</th><th scope="col">Nápovědy</th><th scope="col">Naposledy</th></tr></thead><tbody>';
    foreach ($rows as $id=>$state) {
        $pc = vd82_progress($state, 'pc'); $net = vd82_progress($state, 'sit');
        $label = $labels[$id] ?? ('Student · ' . substr($id,0,8));
        echo '<tr><th scope="row">', e($label), '</th><td>', $pc['done'], '/', $pc['total'], '</td><td>', $net['done'], '/', $net['total'], '</td><td>', (int)($state['attempts'] ?? 0), '</td><td>', (int)($state['errors'] ?? 0), '</td><td>', (int)($state['hints'] ?? 0), '</td><td>', e((string)($state['last_at'] ?? '—')), '</td></tr>';
    }
    echo '</tbody></table></div></section>';
}
