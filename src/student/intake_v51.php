<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v51 · Seznamovací dotazník (převzato z V1) + aktivační účty.
 *
 * - dotazník, zasedací pořádek, analytika a export fungují stejně jako ve V1,
 * - odpovědi z V1 (složka V1/data) se automaticky a idempotentně importují,
 * - každý importovaný žák dostane předzaložený účet s jednorázovým aktivačním kódem,
 * - po aktivaci (vlastní školní e-mail + heslo) je účet rovnou propojený s třídou a daty.
 */

const INTAKE_V51_VERSION = '51.0';

function intake_v51_dir(string $sub = ''): string
{
    $dir = STORAGE_DIR . '/intake' . ($sub !== '' ? '/' . $sub : '');
    if (function_exists('storage_readonly') && storage_readonly()) return $dir; // v61: režim jen pro čtení nic nezakládá
    if (!is_dir($dir)) @mkdir($dir, 0770, true);
    $index = $dir . '/index.php';
    if (!is_file($index)) @file_put_contents($index, "<?php http_response_code(403); exit;\n");
    return $dir;
}

function intake_v51_path(string $name): string
{
    return intake_v51_dir() . '/' . preg_replace('/[^a-z0-9_]/', '', $name) . '.json.php';
}

function intake_v51_read(string $name): array
{
    $path = intake_v51_path($name);
    // v61: paměť požadavku podle epochy zápisů (intake_v51_update ji zvyšuje) – seznam žáků se nečte a nedekóduje stokrát.
    $memoOn = function_exists('storage_request_memo_enabled') && storage_request_memo_enabled();
    $hit = $memoOn ? ($GLOBALS['educanet_intake_read_memo'][$path] ?? null) : null;
    if (is_array($hit) && $hit[0] === storage_epoch()) return $hit[1];
    $data = intake_v51_read_file($path);
    if ($memoOn) $GLOBALS['educanet_intake_read_memo'][$path] = [storage_epoch(), $data];
    return $data;
}

function intake_v51_read_file(string $path): array
{
    if (!is_file($path)) return [];
    $raw = (string)@file_get_contents($path);
    $pos = strpos($raw, "\n");
    if ($pos !== false && str_starts_with($raw, '<?php')) $raw = substr($raw, $pos + 1);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Zápis pod zámkem a přes dočasný soubor – stejná bezpečnost jako ve V1. */
function intake_v51_update(string $name, callable $callback): array
{
    $path = intake_v51_path($name);
    if (function_exists('storage_readonly') && storage_readonly()) { // v61: suchý běh, nic se nezapíše (měření na ostrých datech)
        $dry = $callback(intake_v51_read_file($path));
        if (!is_array($dry)) throw new RuntimeException('Úprava dat dotazníku nevrátila platná data.');
        return $dry;
    }
    $lock = @fopen($path . '.lock', 'c+');
    if (!$lock) throw new RuntimeException('Nelze otevřít zámek úložiště dotazníku.');
    try {
        if (!flock($lock, LOCK_EX)) throw new RuntimeException('Nelze zamknout úložiště dotazníku.');
        $data = intake_v51_read_file($path);
        $result = $callback($data);
        if (!is_array($result)) throw new RuntimeException('Úprava dat dotazníku nevrátila platná data.');
        // Kódování proběhne dřív, než se cokoli zapíše – chyba nikdy nenechá prázdný soubor.
        $payload = "<?php http_response_code(403); exit; ?>\n" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR) . "\n";
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4)) . '.php';
        if (@file_put_contents($tmp, $payload, LOCK_EX) === false || !@rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Data dotazníku se nepodařilo bezpečně uložit.');
        }
        flock($lock, LOCK_UN);
        if (function_exists('php_json_cache_forget')) php_json_cache_forget($path);
        $GLOBALS['educanet_storage_epoch'] = (int)($GLOBALS['educanet_storage_epoch'] ?? 0) + 1; // v61: zneplatní paměti požadavku
        return $result;
    } finally {
        fclose($lock);
    }
}

function intake_v51_text(mixed $value, int $max = 3000): string
{
    $text = str_replace("\0", '', trim((string)(is_scalar($value) ? $value : '')));
    return u_strlen($text) > $max ? u_substr($text, 0, $max) : $text;
}

function intake_v51_list(mixed $value, int $maxItems = 30): array
{
    if (!is_array($value)) return [];
    $out = [];
    foreach (array_slice($value, 0, $maxItems) as $item) {
        $item = intake_v51_text($item, 200);
        if ($item !== '') $out[] = $item;
    }
    return array_values(array_unique($out));
}

function intake_v51_rating(mixed $value): int
{
    return max(1, min(10, (int)$value));
}

// ---------------------------------------------------------------------------
// Třídy a učebna
// ---------------------------------------------------------------------------

/** Typ dotazníku podle třídy: 1.A a 2.A grafika, 3.A vstupní SOSaPS, 4.A navazující SOSaPS. */
function intake_v51_course_type(string $classId, array $module = []): string
{
    $type = strtolower((string)($module['course_type'] ?? ''));
    if (str_starts_with($type, 'graphics')) return 'graphics';
    if ($type === 'networks_advanced') return 'networks_advanced';
    if (str_contains($type, 'network')) return 'networks';
    return match ($classId) {
        'class_1a', 'class_2a' => 'graphics',
        'class_3a' => 'networks',
        'class_4a' => 'networks_advanced',
        default => 'custom',
    };
}

function intake_v51_seat_label(int $row, int $seatCol): string
{
    $desk = (int)ceil($seatCol / 2);
    $side = $seatCol % 2 === 1 ? tr('vlevo') : tr('vpravo');
    return tr('Řada {row} · Lavice {desk} · {side}', ['row' => $row, 'desk' => $desk, 'side' => $side]);
}

function intake_v51_build_seats(int $rows, int $desks, array $activeMap = []): array
{
    $seats = [];
    for ($r = 1; $r <= $rows; $r++) {
        for ($desk = 1; $desk <= $desks; $desk++) {
            $active = $activeMap[$r . ':' . $desk] ?? true;
            foreach (['left', 'right'] as $sideIndex => $side) {
                $col = (($desk - 1) * 2) + $sideIndex + 1;
                $seats[] = ['id' => "r{$r}c{$col}", 'row' => $r, 'col' => $col, 'desk' => $desk, 'side' => $side, 'label' => intake_v51_seat_label($r, $col), 'active' => (bool)$active];
            }
        }
    }
    return $seats;
}

function intake_v51_default_intro(string $courseType, string $classId): string
{
    if ($classId === 'class_1a') return tr('Vítej v prvním ročníku. Pomoz mi poznat, co tě baví, co už umíš a jak se ti nejlépe učí.');
    return match ($courseType) {
        'graphics' => tr('Pomozte mi poznat, co vás baví, co už umíte a kam se chcete během roku posunout.'),
        'networks' => tr('Krátká vstupní diagnostika, abych mohl výuku nastavit podle vašich zkušeností a cílů.'),
        'networks_advanced' => tr('Navážeme na minulý rok. Zajímá mě, co vám fungovalo, co chcete změnit a jaký praktický posun chcete letos udělat.'),
        default => tr('Pomozte mi poznat, jak se vám nejlépe učí.'),
    };
}

/** Konfigurace dotazníku pro všechny třídy aktuální verze. */
function intake_v51_classes(array $modules): array
{
    $stored = intake_v51_read('classes');
    $out = [];
    foreach ($modules as $classId => $module) {
        if (!is_array($module)) continue;
        $classId = (string)$classId;
        $row = is_array($stored[$classId] ?? null) ? $stored[$classId] : [];
        $type = intake_v51_course_type($classId, $module);
        $rows = max(1, min(12, (int)($row['rows'] ?? 8)));
        $cols = max(1, min(10, (int)($row['cols'] ?? 6)));
        $seats = is_array($row['seats'] ?? null) && $row['seats'] ? array_values($row['seats']) : intake_v51_build_seats($rows, $cols);
        $out[$classId] = [
            'id' => $classId,
            'name' => (string)($module['name'] ?? $classId),
            'subject' => (string)($module['subject'] ?? ''),
            'code' => (string)($module['code'] ?? ''),
            'course_type' => $type,
            'open' => array_key_exists('open', $row) ? (bool)$row['open'] : true,
            'rows' => $rows,
            'cols' => $cols,
            'seats' => $seats,
            'intro' => trim((string)($row['intro'] ?? '')) !== '' ? (string)$row['intro'] : intake_v51_default_intro($type, $classId),
        ];
    }
    uasort($out, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));
    return $out;
}

function intake_v51_find_seat(array $class, string $seatId): ?array
{
    foreach ((array)($class['seats'] ?? []) as $seat) {
        if ((string)($seat['id'] ?? '') === $seatId && !empty($seat['active'])) return $seat;
    }
    return null;
}

function intake_v51_seat_map(array $class): array
{
    $map = [];
    foreach ((array)($class['seats'] ?? []) as $seat) {
        $row = (int)($seat['row'] ?? 0);
        $desk = (int)($seat['desk'] ?? (int)ceil(((int)($seat['col'] ?? 1)) / 2));
        $side = (string)($seat['side'] ?? (((int)($seat['col'] ?? 1) % 2 === 1) ? 'left' : 'right'));
        $map[$row][$desk][$side] = $seat;
    }
    return $map;
}

// ---------------------------------------------------------------------------
// Odpovědi
// ---------------------------------------------------------------------------

function intake_v51_responses(): array
{
    return array_values(array_filter(intake_v51_read('responses'), 'is_array'));
}

function intake_v51_responses_for_class(string $classId): array
{
    $rows = array_values(array_filter(intake_v51_responses(), static fn(array $r): bool => (string)($r['class_id'] ?? '') === $classId));
    usort($rows, static fn(array $a, array $b): int => strnatcasecmp(
        ($a['student']['last_name'] ?? '') . ' ' . ($a['student']['first_name'] ?? ''),
        ($b['student']['last_name'] ?? '') . ' ' . ($b['student']['first_name'] ?? '')
    ));
    return $rows;
}

function intake_v51_student_label(array $response): string
{
    return trim((string)($response['student']['first_name'] ?? '') . ' ' . (string)($response['student']['last_name'] ?? ''));
}

function intake_v51_response_key(array $response): string
{
    $classId = (string)($response['class_id'] ?? '');
    $label = intake_v51_student_label($response);
    return $classId !== '' && $label !== '' ? project_student_key($classId, $label) : '';
}

function intake_v51_response_for_student(string $classId, string $label): ?array
{
    if ($label === '') return null;
    $key = project_student_key($classId, $label);
    foreach (intake_v51_responses() as $response) {
        if ((string)($response['class_id'] ?? '') === $classId && intake_v51_response_key($response) === $key) return $response;
    }
    return null;
}

/** Řádky pro student_directory(): žáci, kteří dotazník vyplnili ve V1 nebo nově v aplikaci. */
function intake_v51_directory_rows(): array
{
    $rows = [];
    foreach (intake_v51_responses() as $response) {
        $first = trim((string)($response['student']['first_name'] ?? ''));
        $last = trim((string)($response['student']['last_name'] ?? ''));
        if ($first === '' || empty($response['class_id'])) continue;
        $rows[] = [
            'class_id' => (string)$response['class_id'],
            'first_name' => $first,
            'last_name' => $last,
            'preferred_name' => (string)($response['student']['preferred_name'] ?? ''),
            'seat_id' => (string)($response['student']['seat_id'] ?? ''),
            'seat_label' => (string)($response['student']['seat_label'] ?? ''),
        ];
    }
    return $rows;
}

// ---------------------------------------------------------------------------
// Závěrečné ověření – body
// ---------------------------------------------------------------------------

function intake_v51_quiz_questions(string $courseType): array
{
    if ($courseType === 'networks') {
        return [
            ['id'=>'dns','question'=>'K čemu slouží DNS?','options'=>['a'=>'Šifruje síťový provoz','b'=>'Překládá doménová jména na IP adresy','c'=>'Přiděluje uživatelská hesla','d'=>'Komprimuje přenášené soubory'],'correct'=>'b'],
            ['id'=>'ssh_port','question'=>'Jaký je výchozí port služby SSH?','options'=>['a'=>'21','b'=>'22','c'=>'53','d'=>'443'],'correct'=>'b'],
            ['id'=>'ping','question'=>'Který příkaz se běžně používá pro základní ověření dostupnosti zařízení v síti?','options'=>['a'=>'ping','b'=>'mkdir','c'=>'copy','d'=>'format'],'correct'=>'a'],
            ['id'=>'dhcp','question'=>'Co typicky zajišťuje DHCP server?','options'=>['a'=>'Automaticky přiděluje síťovou konfiguraci klientům','b'=>'Šifruje disk','c'=>'Překládá web do HTML','d'=>'Blokuje všechny porty'],'correct'=>'a'],
            ['id'=>'private_ip','question'=>'Která z těchto adres je typická privátní IPv4 adresa?','options'=>['a'=>'8.8.8.8','b'=>'1.1.1.1','c'=>'192.168.1.25','d'=>'185.199.108.153'],'correct'=>'c'],
            ['id'=>'firewall','question'=>'Jaká je hlavní úloha firewallu?','options'=>['a'=>'Řídit povolený a blokovaný síťový provoz podle pravidel','b'=>'Zrychlovat procesor','c'=>'Vytvářet uživatelské účty','d'=>'Automaticky zálohovat soubory'],'correct'=>'a'],
            ['id'=>'sftp','question'=>'Co nejlépe vystihuje SFTP?','options'=>['a'=>'Nešifrovaný přenos přes port 80','b'=>'Bezpečný přenos souborů využívající SSH','c'=>'Služba pro překlad domén','d'=>'Protokol pouze pro e-mail'],'correct'=>'b'],
            ['id'=>'ip','question'=>'K čemu v síti slouží IP adresa?','options'=>['a'=>'Identifikuje síťové rozhraní / zařízení pro komunikaci','b'=>'Nahrazuje heslo uživatele','c'=>'Určuje rychlost procesoru','d'=>'Slouží jen k pojmenování souborů'],'correct'=>'a'],
            ['id'=>'https','question'=>'Který port se standardně používá pro HTTPS?','options'=>['a'=>'25','b'=>'53','c'=>'80','d'=>'443'],'correct'=>'d'],
            ['id'=>'dns_problem','question'=>'Web funguje po zadání jeho IP adresy, ale nefunguje přes doménové jméno. Kde bys hledal/a problém jako první?','options'=>['a'=>'DNS','b'=>'Klávesnice','c'=>'Grafická karta','d'=>'USB port'],'correct'=>'a'],
        ];
    }
    if ($courseType === 'networks_advanced') {
        return [
            ['id'=>'mask24','question'=>'Jaká maska odpovídá prefixu /24 v IPv4?','options'=>['a'=>'255.0.0.0','b'=>'255.255.0.0','c'=>'255.255.255.0','d'=>'255.255.255.255'],'correct'=>'c'],
            ['id'=>'gateway','question'=>'K čemu slouží výchozí brána (default gateway)?','options'=>['a'=>'Směruje provoz do jiných sítí','b'=>'Přiděluje názvy souborům','c'=>'Nahrazuje DNS','d'=>'Měří rychlost CPU'],'correct'=>'a'],
            ['id'=>'tcp','question'=>'Které tvrzení nejlépe popisuje TCP?','options'=>['a'=>'Je spojově orientované a řeší spolehlivé doručení','b'=>'Nikdy nepoužívá porty','c'=>'Funguje jen v lokální síti','d'=>'Je to souborový systém Linuxu'],'correct'=>'a'],
            ['id'=>'traceroute','question'=>'Co ti ukáže traceroute / tracert?','options'=>['a'=>'Cestu přes jednotlivé síťové uzly k cíli','b'=>'Obsah DNS zóny','c'=>'Seznam uživatelů Linuxu','d'=>'Teplotu procesoru'],'correct'=>'a'],
            ['id'=>'dns_a','question'=>'Co typicky obsahuje DNS záznam typu A?','options'=>['a'=>'IPv4 adresu cíle','b'=>'Heslo k serveru','c'=>'MAC adresu routeru','d'=>'Číslo TCP portu'],'correct'=>'a'],
            ['id'=>'ssh_key','question'=>'Při běžném přihlášení SSH klíčem má klient chránit především…','options'=>['a'=>'svůj privátní klíč','b'=>'veřejný klíč serveru jako tajemství','c'=>'DNS A záznam','d'=>'IP adresu jako heslo'],'correct'=>'a'],
            ['id'=>'loopback','question'=>'Co označuje adresa 127.0.0.1?','options'=>['a'=>'Výchozí bránu internetu','b'=>'Lokální zařízení (loopback / localhost)','c'=>'Veřejný DNS server','d'=>'Broadcast celé sítě'],'correct'=>'b'],
            ['id'=>'bind_localhost','question'=>'Webová služba poslouchá pouze na 127.0.0.1:8080. Co to obvykle znamená?','options'=>['a'=>'Je dostupná jen lokálně na daném zařízení','b'=>'Je automaticky veřejná na internetu','c'=>'Port 8080 je vždy blokovaný','d'=>'DNS je určitě rozbitý'],'correct'=>'a'],
            ['id'=>'dns_diag','question'=>'Zařízení úspěšně pingne 8.8.8.8, ale doména example.com se nepřeloží. Nejpodezřelejší je…','options'=>['a'=>'DNS konfigurace / resolver','b'=>'Napájecí zdroj','c'=>'Grafický ovladač','d'=>'Rozlišení monitoru'],'correct'=>'a'],
            ['id'=>'chmod600','question'=>'Co v Linuxu typicky znamená oprávnění chmod 600 soubor?','options'=>['a'=>'Vlastník může číst a zapisovat, ostatní nemají oprávnění','b'=>'Všichni mohou vše','c'=>'Soubor lze pouze spustit','d'=>'Soubor je veřejně dostupný přes HTTP'],'correct'=>'a'],
        ];
    }
    return [];
}

function intake_v51_score_quiz(string $courseType, mixed $submitted): array
{
    $questions = intake_v51_quiz_questions($courseType);
    $submitted = is_array($submitted) ? $submitted : [];
    $score = 0; $answers = []; $missing = [];
    foreach ($questions as $q) {
        $id = (string)$q['id'];
        $selected = intake_v51_text($submitted[$id] ?? '', 4);
        if ($selected === '' || !array_key_exists($selected, $q['options'])) { $missing[] = $id; $selected = ''; }
        $correct = $selected !== '' && hash_equals((string)$q['correct'], $selected);
        if ($correct) $score++;
        $answers[$id] = ['selected' => $selected, 'correct' => $correct];
    }
    $max = count($questions);
    return ['score' => $score, 'max_score' => $max, 'percentage' => $max ? (int)round($score / $max * 100) : null, 'answers' => $answers, 'missing' => $missing];
}

// ---------------------------------------------------------------------------
// Chráněné soubory (plakát)
// ---------------------------------------------------------------------------

function intake_v51_upload_header(): string
{
    return "<?php http_response_code(403); exit; __halt_compiler(); ?>\n";
}

function intake_v51_save_upload(array $file, string $responseId): array
{
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => tr('Soubor je příliš velký.'),
            UPLOAD_ERR_PARTIAL => tr('Soubor se nahrál jen částečně.'),
            UPLOAD_ERR_NO_FILE => tr('Nahraj prosím hotový plakát.'),
            default => tr('Nahrání souboru se nepodařilo.'),
        });
    }
    $tmp = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    if ($tmp === '' || !is_uploaded_file($tmp)) throw new RuntimeException(tr('Nahraný soubor není platný.'));
    if ($size < 1) throw new RuntimeException(tr('Nahraný soubor je prázdný.'));
    if ($size > 12 * 1024 * 1024) throw new RuntimeException(tr('Plakát může mít maximálně 12 MB.'));
    $mime = class_exists('finfo') ? (string)(new finfo(FILEINFO_MIME_TYPE))->file($tmp) : (string)@mime_content_type($tmp);
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
    if (!isset($allowed[$mime])) throw new RuntimeException(tr('Povolené formáty plakátu jsou PNG, JPG, WEBP nebo PDF.'));
    $original = str_replace(["\r", "\n", '\\', '/'], '_', intake_v51_text($file['name'] ?? ('plakat.' . $allowed[$mime]), 180));
    $storageName = preg_replace('/[^A-Za-z0-9_-]/', '', $responseId) . '_' . bin2hex(random_bytes(5)) . '.upload.php';
    $path = intake_v51_dir('uploads') . '/' . $storageName;
    $in = @fopen($tmp, 'rb'); $out = @fopen($path, 'wb');
    if (!$in || !$out) {
        if (is_resource($in)) fclose($in);
        if (is_resource($out)) fclose($out);
        @unlink($path);
        throw new RuntimeException(tr('Soubor se nepodařilo bezpečně uložit.'));
    }
    try { fwrite($out, intake_v51_upload_header()); stream_copy_to_stream($in, $out); } finally { fclose($in); fclose($out); }
    return ['storage_name' => $storageName, 'original_name' => $original, 'mime' => $mime, 'size' => $size];
}

function intake_v51_upload_path(string $storageName): ?string
{
    $safe = preg_replace('/[^A-Za-z0-9_.-]/', '', $storageName);
    if ($safe === '' || $safe !== $storageName || str_contains($safe, '..')) return null;
    $path = intake_v51_dir('uploads') . '/' . $safe;
    return is_file($path) ? $path : null;
}

/** Odešle soubor odpovědi do prohlížeče (volající musí ověřit oprávnění). */
function intake_v51_stream_artifact(array $response): never
{
    $artifact = $response['assessment']['artifact'] ?? null;
    $path = is_array($artifact) ? intake_v51_upload_path((string)($artifact['storage_name'] ?? '')) : null;
    $raw = $path ? (string)@file_get_contents($path) : '';
    $header = intake_v51_upload_header();
    if ($raw === '' || !str_starts_with($raw, $header)) { http_response_code(404); exit(tr('Soubor není dostupný.')); }
    $bytes = substr($raw, strlen($header));
    $mime = (string)($artifact['mime'] ?? 'application/octet-stream');
    if (!in_array($mime, ['image/png', 'image/jpeg', 'image/webp', 'application/pdf'], true)) $mime = 'application/octet-stream';
    $name = str_replace(["\r", "\n", '"'], '', (string)($artifact['original_name'] ?? 'prace'));
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($bytes));
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: inline; filename="' . $name . '"; filename*=UTF-8\'\'' . rawurlencode($name));
    echo $bytes;
    exit;
}

function intake_v51_filesize(int $bytes): string
{
    return $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', ' ') . ' MB' : max(1, (int)round($bytes / 1024)) . ' kB';
}

function intake_v51_delete_upload(?string $storageName): void
{
    $path = $storageName ? intake_v51_upload_path($storageName) : null;
    if ($path) @unlink($path);
}

// ---------------------------------------------------------------------------
// Aktivační kódy
// ---------------------------------------------------------------------------

function intake_v51_generate_code(array $existing): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $code = '';
        for ($i = 0; $i < 8; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        $code = substr($code, 0, 4) . '-' . substr($code, 4);
        if (!isset($existing[$code])) return $code;
    }
    throw new RuntimeException('Nepodařilo se vygenerovat aktivační kód.');
}

function intake_v51_normalize_code(string $code): string
{
    $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    return strlen($clean) === 8 ? substr($clean, 0, 4) . '-' . substr($clean, 4) : '';
}

function intake_v51_activations(): array
{
    return intake_v51_read('activations');
}

/** Zajistí aktivační záznam pro každého žáka, který má uložený dotazník. */
function intake_v51_ensure_activations(): int
{
    $responses = intake_v51_responses();
    $created = 0;
    intake_v51_update('activations', static function (array $rows) use ($responses, &$created): array {
        $codes = [];
        foreach ($rows as $row) if (is_array($row) && !empty($row['code'])) $codes[(string)$row['code']] = true;
        foreach ($responses as $response) {
            $key = intake_v51_response_key($response);
            if ($key === '' || isset($rows[$key])) continue;
            $code = intake_v51_generate_code($codes);
            $codes[$code] = true;
            $rows[$key] = [
                'student_key' => $key,
                'class_id' => (string)$response['class_id'],
                'label' => intake_v51_student_label($response),
                'preferred_name' => (string)($response['student']['preferred_name'] ?? ''),
                'code' => $code,
                'created_at' => date(DATE_ATOM),
                'used_at' => null,
                'used_email' => '',
            ];
            $created++;
        }
        return $rows;
    });
    return $created;
}

function intake_v51_find_activation(string $code): ?array
{
    $code = intake_v51_normalize_code($code);
    if ($code === '') return null;
    foreach (intake_v51_activations() as $row) {
        if (is_array($row) && hash_equals((string)($row['code'] ?? ''), $code)) return $row;
    }
    return null;
}

function intake_v51_regenerate_activation(string $studentKey): string
{
    $new = '';
    intake_v51_update('activations', static function (array $rows) use ($studentKey, &$new): array {
        if (!isset($rows[$studentKey]) || !is_array($rows[$studentKey])) throw new RuntimeException('Aktivační záznam neexistuje.');
        $codes = [];
        foreach ($rows as $row) if (is_array($row) && !empty($row['code'])) $codes[(string)$row['code']] = true;
        $new = intake_v51_generate_code($codes);
        $rows[$studentKey]['code'] = $new;
        $rows[$studentKey]['used_at'] = null;
        $rows[$studentKey]['used_email'] = '';
        $rows[$studentKey]['regenerated_at'] = date(DATE_ATOM);
        return $rows;
    });
    return $new;
}

// ---------------------------------------------------------------------------
// Import z V1
// ---------------------------------------------------------------------------

function intake_v51_v1_data_dir(): string
{
    $custom = getenv('EDUCANET_V1_DATA_DIR');
    return is_string($custom) && trim($custom) !== '' ? rtrim(trim($custom), '/\\') : __DIR__ . '/V1/data';
}

function intake_v51_v1_read(string $name): array
{
    $path = intake_v51_v1_data_dir() . '/' . $name . '.php';
    if (!is_file($path)) return [];
    $raw = (string)@file_get_contents($path);
    $pos = strpos($raw, "\n");
    if ($pos !== false && str_starts_with($raw, '<?php')) $raw = substr($raw, $pos + 1);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Idempotentní import: odpovědi se párují podle ID z V1, soubory se kopírují do chráněného úložiště,
 * rozložení učebny se převezme jen tehdy, pokud ho učitel v nové verzi ještě neupravil.
 * Levná kontrola podpisu souborů zajistí, že se import při běžném požadavku nespouští opakovaně.
 */
function intake_v51_sync_v1(array $modules, bool $force = false): array
{
    if (function_exists('storage_readonly') && storage_readonly()) return ['imported' => 0, 'skipped' => 0, 'files' => 0, 'activations' => 0, 'ran' => false]; // v61: import V1 zapisuje a kopíruje soubory
    $dir = intake_v51_v1_data_dir();
    $files = [$dir . '/responses.json.php', $dir . '/classes.json.php'];
    $signature = '';
    foreach ($files as $file) $signature .= is_file($file) ? (filemtime($file) . ':' . filesize($file) . '|') : 'none|';
    $state = intake_v51_read('import_state');
    $stats = ['imported' => 0, 'skipped' => 0, 'files' => 0, 'activations' => 0, 'ran' => false];
    if (!$force && (string)($state['signature'] ?? '') === $signature) return $stats;
    $stats['ran'] = true;

    $v1Classes = intake_v51_v1_read('classes.json');
    if ($v1Classes) {
        intake_v51_update('classes', static function (array $stored) use ($v1Classes, $modules): array {
            foreach ($v1Classes as $class) {
                $id = (string)($class['id'] ?? '');
                if ($id === '' || !isset($modules[$id]) || isset($stored[$id]['seats'])) continue;
                $stored[$id] = [
                    'open' => true,
                    'rows' => max(1, min(12, (int)($class['rows'] ?? 8))),
                    'cols' => max(1, min(10, (int)($class['cols'] ?? 6))),
                    'seats' => is_array($class['seats'] ?? null) ? array_values($class['seats']) : [],
                    'intro' => (string)($class['intro'] ?? ''),
                    'imported_from' => 'V1',
                ];
            }
            return $stored;
        });
    }

    $v1Responses = array_values(array_filter(intake_v51_v1_read('responses.json'), 'is_array'));
    if ($v1Responses) {
        $deleted = array_flip(array_map('strval', (array)($state['deleted'] ?? [])));
        intake_v51_update('responses', static function (array $stored) use ($v1Responses, $modules, $dir, $deleted, &$stats): array {
            $known = $deleted;
            foreach ($stored as $row) if (is_array($row)) $known[(string)($row['id'] ?? '')] = true;
            foreach ($v1Responses as $response) {
                $id = (string)($response['id'] ?? '');
                if ($id === '' || isset($known[$id]) || !isset($modules[(string)($response['class_id'] ?? '')])) { $stats['skipped']++; continue; }
                $artifact = $response['assessment']['artifact'] ?? null;
                if (is_array($artifact) && !empty($artifact['storage_name'])) {
                    $name = preg_replace('/[^A-Za-z0-9_.-]/', '', (string)$artifact['storage_name']);
                    $source = $dir . '/uploads/' . $name;
                    $target = intake_v51_dir('uploads') . '/' . $name;
                    if ($name !== '' && is_file($source) && !is_file($target) && @copy($source, $target)) $stats['files']++;
                }
                $response['source'] = 'V1';
                $response['imported_at'] = date(DATE_ATOM);
                $stored[] = $response;
                $known[$id] = true;
                $stats['imported']++;
            }
            return array_values($stored);
        });
    }

    $stats['activations'] = intake_v51_ensure_activations();
    intake_v51_update('import_state', static fn(array $s): array => array_merge($s, ['signature' => $signature, 'last_run_at' => date(DATE_ATOM), 'last_stats' => $stats]));
    return $stats;
}

// ---------------------------------------------------------------------------
// Popisky pro učitelský profil
// ---------------------------------------------------------------------------

function intake_v51_labels(string $courseType): array
{
    $labels = [
        'interests'=>trm('Zájmy'),'free_time'=>trm('Volný čas'),'favorite_school_things'=>trm('Co ho/ji ve škole baví'),'strengths'=>trm('Silné stránky'),'improve'=>trm('Chce se zlepšit'),'projects'=>trm('Vlastní projekty'),'why_subject'=>trm('Proč tento předmět'),'expectations'=>trm('Očekávání od výuky'),'year_goal'=>trm('Cíl na tento rok'),'dream_project'=>trm('Vysněný projekt'),'practical_outcome'=>trm('Praktická dovednost'),'future_direction'=>trm('Směr po škole'),'future_detail'=>trm('Představa o budoucnosti'),'learning_styles'=>trm('Jak se nejlépe učí'),'work_mode'=>trm('Preferovaný režim práce'),'pace'=>trm('Tempo'),'feedback_styles'=>trm('Zpětná vazba'),'focus_conditions'=>trm('Podmínky pro soustředění'),'ask_help'=>trm('Ochota zeptat se'),'team_confidence'=>trm('Týmová práce'),'presentation_confidence'=>trm('Prezentování'),'independence'=>trm('Samostatnost'),'problem_solving'=>trm('Řešení problémů'),'organization'=>trm('Organizace práce'),'teacher_support'=>trm('Co má učitel dělat'),'teacher_avoid'=>trm('Co nepomáhá'),'learning_blockers'=>trm('Praktické překážky'),'devices'=>trm('Zařízení'),'systems'=>trm('Operační systémy'),'digital_confidence'=>trm('Jistota s počítačem'),'subject_tools'=>trm('Nástroje a technologie'),'subject_skills'=>trm('Praktické zkušenosti'),'subject_topics'=>trm('Co chce probírat'),'subject_confidence'=>trm('Jistota v předmětu'),'subject_detail_1'=>trm('Předmětová odpověď 1'),'subject_detail_2'=>trm('Předmětová odpověď 2'),'subject_detail_3'=>trm('Předmětová odpověď 3'),'last_year_best'=>trm('Co fungovalo minulý rok'),'last_year_change'=>trm('Co letos změnit'),'fun_fact'=>trm('Zajímavost'),'question_teacher'=>trm('Otázka na učitele'),'anything_else'=>trm('Cokoliv dalšího'),
    ];
    if ($courseType === 'graphics') {
        $labels['subject_detail_1'] = trm('Inspirace, styl, autoři nebo značky');
        $labels['subject_detail_2'] = trm('Vlastní tvorba nebo portfolio');
        $labels['subject_detail_3'] = trm('Co je na grafice nejtěžší');
    } elseif (in_array($courseType, ['networks', 'networks_advanced'], true)) {
        $labels['subject_detail_1'] = trm('Co chce prakticky zprovoznit');
        $labels['subject_detail_2'] = trm('Nejsložitější technický problém');
        $labels['subject_detail_3'] = trm('Téma, kterému se zatím vyhýbá');
    }
    return $labels;
}

function intake_v51_rating_keys(): array
{
    return ['ask_help','team_confidence','presentation_confidence','independence','problem_solving','organization','digital_confidence','subject_confidence'];
}

function intake_v51_profile_sections(): array
{
    return [
        ['about',trm('O studentovi'),['interests','free_time','favorite_school_things','strengths','improve','projects']],
        ['goals',trm('Cíle a budoucnost'),['why_subject','expectations','year_goal','dream_project','practical_outcome','future_direction','future_detail']],
        ['learning',trm('Jak se učí a pracuje'),['learning_styles','work_mode','pace','feedback_styles','focus_conditions','ask_help','team_confidence','presentation_confidence','independence','problem_solving','organization']],
        ['tech',trm('Technické zázemí'),['devices','systems','digital_confidence']],
        ['subject',trm('Předmětový profil'),['subject_tools','subject_skills','subject_topics','subject_confidence','subject_detail_1','subject_detail_2','subject_detail_3']],
        ['past',trm('Navázání na minulý rok'),['last_year_best','last_year_change']],
        ['support',trm('Podpora pro učitele'),['teacher_support','teacher_avoid','learning_blockers']],
        ['final',trm('Závěr'),['fun_fact','question_teacher','anything_else']],
    ];
}

function intake_v51_has_value(mixed $value): bool
{
    if (is_array($value)) return count(array_filter($value, static fn($v): bool => trim((string)$v) !== '')) > 0;
    return trim((string)$value) !== '';
}

function intake_v51_avg(array $responses, string $key): ?float
{
    $vals = [];
    foreach ($responses as $r) if (isset($r['answers'][$key]) && is_numeric($r['answers'][$key])) $vals[] = (float)$r['answers'][$key];
    return $vals ? array_sum($vals) / count($vals) : null;
}

function intake_v51_ranked(array $responses, string $key, int $limit = 6): array
{
    $counts = [];
    foreach ($responses as $r) foreach ((array)($r['answers'][$key] ?? []) as $x) if ((string)$x !== '') $counts[(string)$x] = ($counts[(string)$x] ?? 0) + 1;
    arsort($counts);
    return array_slice($counts, 0, $limit, true);
}

/** Vykreslí odpovědi po sekcích – sdíleno učitelským profilem i studentovým „Moje odpovědi“. */
function intake_v51_render_answers(array $response, string $courseType): void
{
    $answers = (array)($response['answers'] ?? []);
    $labels = intake_v51_labels($courseType);
    foreach (intake_v51_profile_sections() as [$id, $title, $keys]) {
        $visible = array_values(array_filter($keys, static fn(string $k): bool => intake_v51_has_value($answers[$k] ?? '')));
        if (!$visible) continue;
        echo '<section class="u51-answer-section"><h3>' . e(tr($title)) . '</h3><dl class="u51-answer-list">';
        foreach ($visible as $key) {
            $value = $answers[$key];
            $labelText = $labels[$key] ?? $key;
            echo '<div><dt>' . e(tr($labelText)) . '</dt><dd>';
            if (is_array($value)) {
                echo '<span class="u51-tags">';
                foreach ($value as $item) { $itemText = (string)$item; if (trim($itemText) !== '') echo '<span>' . e(tr($itemText)) . '</span>'; }
                echo '</span>';
            } elseif (in_array($key, intake_v51_rating_keys(), true) && is_numeric($value)) {
                $n = max(1, min(10, (int)$value));
                echo '<span class="u51-meter"><i style="width:' . ($n * 10) . '%"></i></span><b>' . $n . '<small>/10</small></b>';
            } else {
                echo nl2br(e((string)$value));
            }
            echo '</dd></div>';
        }
        echo '</dl></section>';
    }
}

/** Výsledek závěrečného ověření s body po otázkách. */
function intake_v51_render_assessment(array $response, string $courseType, string $artifactUrl, bool $showCorrect): void
{
    $assessment = (array)($response['assessment'] ?? []);
    $type = (string)($assessment['type'] ?? '');
    if ($type === 'knowledge_quiz') {
        $questions = intake_v51_quiz_questions($courseType);
        $score = (int)($assessment['score'] ?? 0);
        $max = (int)($assessment['max_score'] ?? count($questions));
        echo '<section class="u51-answer-section"><h3>' . e(tr('Závěrečné ověření')) . '</h3>';
        echo '<div class="u51-score"><strong>' . $score . '<small> / ' . $max . ' ' . e(tr('b.')) . '</small></strong><span>' . e(tr('{percent} % · vstupní diagnostika, ne známka', ['percent' => (int)($assessment['percentage'] ?? 0)])) . '</span></div>';
        echo '<ol class="u51-quiz-review">';
        foreach ($questions as $q) {
            $answer = (array)($assessment['answers'][$q['id']] ?? []);
            $selected = (string)($answer['selected'] ?? '');
            $ok = !empty($answer['correct']);
            echo '<li class="' . ($ok ? 'ok' : 'bad') . '"><span class="u51-pts">' . ($ok ? '1' : '0') . ' ' . e(tr('b.')) . '</span><div><strong>' . e((string)$q['question']) . '</strong>';
            echo '<small>' . e(tr('Odpověď: {answer}', ['answer' => $selected !== '' ? (string)($q['options'][$selected] ?? '—') : tr('bez odpovědi')])) . '</small>';
            if (!$ok && $showCorrect) echo '<small class="u51-correct">' . e(tr('Správně: {answer}', ['answer' => (string)($q['options'][$q['correct']] ?? '')])) . '</small>';
            echo '</div></li>';
        }
        echo '</ol></section>';
    } elseif ($type === 'graphics_poster') {
        $artifact = (array)($assessment['artifact'] ?? []);
        $isImage = str_starts_with((string)($artifact['mime'] ?? ''), 'image/');
        echo '<section class="u51-answer-section"><h3>' . e(tr('Praktický úkol · plakát')) . '</h3><div class="u51-poster">';
        if ($isImage && $artifactUrl !== '') echo '<a href="' . e($artifactUrl) . '" target="_blank" rel="noopener"><img src="' . e($artifactUrl) . '" alt="' . e(tr('Odevzdaný plakát')) . '" loading="lazy"></a>';
        echo '<div><strong>' . e((string)($artifact['original_name'] ?? tr('Plakát'))) . '</strong>';
        if (!empty($artifact['size'])) echo '<small>' . e(intake_v51_filesize((int)$artifact['size'])) . '</small>';
        if (trim((string)($assessment['design_note'] ?? '')) !== '') echo '<p>' . nl2br(e((string)$assessment['design_note'])) . '</p>';
        if ($artifactUrl !== '') echo '<a class="btn secondary" href="' . e($artifactUrl) . '" target="_blank" rel="noopener">' . e(tr('Otevřít soubor')) . '</a>';
        echo '</div></div></section>';
    }
}

// ---------------------------------------------------------------------------
// Studentská část
// ---------------------------------------------------------------------------

function intake_v51_current_label(): string
{
    return trim((string)($_SESSION['student_label'] ?? ''));
}

function intake_v51_student_has_response(string $classId): bool
{
    return intake_v51_response_for_student($classId, intake_v51_current_label()) !== null;
}

/** Zpracuje POST akce dotazníku a aktivace. Vrací true, pokud akci obsloužil (vždy přesměruje). */
function intake_v51_handle_post(string $action, array $modules): bool
{
    if ($action === 'intake_activate') {
        $bucket = 'intake-activate:' . substr(hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? '')), 0, 24);
        // Celá třída sdílí jednu školní IP: limit musí unést překlepy 30 žáků, hádání kódu (32^6) je i tak nereálné.
        if (!auth_rate_limit_check($bucket, 60)) { $_SESSION['flash'] = tr('Příliš mnoho pokusů. Zkus to za několik minut.'); redirect_to('?view=activate'); }
        $row = intake_v51_find_activation((string)($_POST['code'] ?? ''));
        $step = (string)($_POST['step'] ?? 'check');
        if (!$row || !isset($modules[(string)($row['class_id'] ?? '')])) {
            auth_rate_limit_fail($bucket);
            $_SESSION['flash'] = tr('Tento aktivační kód neznáme. Zkontroluj ho nebo se zeptej učitele.');
            redirect_to('?view=activate');
        }
        if (!empty($row['used_at'])) {
            $_SESSION['flash'] = tr('Tento kód už byl použit. Přihlas se e-mailem, který sis nastavil/a, nebo požádej učitele o nový kód.');
            redirect_to('?view=home');
        }
        $_SESSION['intake_activation_code'] = (string)$row['code'];
        if ($step === 'check') redirect_to('?view=activate');

        $email = local_email_normalize((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if (!local_email_is_allowed($email)) { $_SESSION['flash'] = tr('Použij svůj školní e-mail @{domain}.', ['domain' => google_workspace_domain()]); redirect_to('?view=activate'); }
        if ($password !== (string)($_POST['password_confirm'] ?? '')) { $_SESSION['flash'] = tr('Hesla se neshodují.'); redirect_to('?view=activate'); }
        if (($passwordError = local_password_validate($password, ['email' => $email, 'name' => (string)$row['label']])) !== null) { $_SESSION['flash'] = $passwordError; redirect_to('?view=activate'); }
        if (isset(local_accounts()[$email])) { $_SESSION['flash'] = tr('Pro tento e-mail už účet existuje. Přihlas se a v kroku propojení zadej aktivační kód.'); redirect_to('?view=home'); }
        $account = [
            'id' => bin2hex(random_bytes(16)), 'email' => $email, 'name' => (string)$row['label'],
            'password_hash' => local_password_hash($password), 'created_at' => date(DATE_ATOM),
            // Aktivační kód předal učitel osobně – ten nahrazuje ověření e-mailu.
            'verified_at' => date(DATE_ATOM), 'verified_by' => 'intake_activation_code',
            'verification_token_hash' => null, 'verification_expires_at' => null, 'reset_token_hash' => null, 'reset_expires_at' => null,
        ];
        if (!acc58_account_insert($email, $account)) { $_SESSION['flash'] = tr('Pro tento e-mail už účet existuje. Přihlas se a v kroku propojení zadej aktivační kód.'); redirect_to('?view=home'); }
        intake_v51_mark_used((string)$row['student_key'], $email);
        auth_rate_limit_clear($bucket);
        session_regenerate_id(true);
        $_SESSION['local_user'] = local_account_public($account);
        unset($_SESSION['google_user'], $_SESSION['intake_activation_code']);
        bind_auth_account(auth_user() ?? [], (string)$row['class_id'], (string)$row['label']);
        $_SESSION['flash'] = tr('Účet je aktivní. Tvoje odpovědi ze seznamovacího dotazníku jsou už uložené.');
        redirect_to('?view=dashboard');
    }

    if ($action === 'intake_link_code') {
        $user = auth_user();
        if (!$user) redirect_to('?view=home');
        $row = intake_v51_find_activation((string)($_POST['code'] ?? ''));
        if (!$row || !empty($row['used_at']) || !isset($modules[(string)($row['class_id'] ?? '')])) {
            $_SESSION['flash'] = tr('Aktivační kód není platný nebo už byl použit.');
            redirect_to('?view=link_account');
        }
        bind_auth_account($user, (string)$row['class_id'], (string)$row['label']);
        intake_v51_mark_used((string)$row['student_key'], (string)($user['email'] ?? ''));
        $_SESSION['flash'] = tr('Účet je propojený s tvými daty.');
        redirect_to('?view=dashboard');
    }

    if ($action === 'intake_submit') {
        $classId = current_class_id($modules);
        if ($classId === null) { $_SESSION['flash'] = tr('Nejdřív se přihlas.'); redirect_to('?view=home'); }
        $classes = intake_v51_classes($modules);
        $class = $classes[$classId] ?? null;
        $label = intake_v51_current_label();
        if (!$class || empty($class['open'])) { $_SESSION['flash'] = tr('Dotazník pro tvou třídu je právě uzavřený.'); redirect_to('?view=dashboard'); }
        if (intake_v51_student_has_response($classId)) { $_SESSION['flash'] = tr('Dotazník už máš vyplněný.'); redirect_to('?view=my_intake'); }
        try {
            $response = intake_v51_build_response($class, $label, $_POST, $_FILES);
        } catch (Throwable $e) {
            $_SESSION['intake_old'] = $_POST;
            $_SESSION['flash'] = $e->getMessage();
            redirect_to('?view=intake');
        }
        try {
            intake_v51_update('responses', static function (array $rows) use ($response, $classId, $label): array {
                foreach ($rows as $existing) {
                    if (!is_array($existing) || (string)($existing['class_id'] ?? '') !== $classId) continue;
                    if ((string)($existing['student']['seat_id'] ?? '') === (string)$response['student']['seat_id']) {
                        // v59 i18n: zpráva zůstává česká (msgid) – překlad se aplikuje až v catch přes tr(),
                        // aby šlo podle přesné shody rozeznat konflikt místa bez porovnávání přeloženého textu v JS.
                        throw new RuntimeException('Toto místo mezitím obsadil jiný student. Vyber prosím jiné místo.');
                    }
                    if (intake_v51_response_key($existing) === project_student_key($classId, $label)) {
                        throw new RuntimeException('Dotazník už máš vyplněný.');
                    }
                }
                $rows[] = $response;
                return array_values($rows);
            });
        } catch (Throwable $e) {
            intake_v51_delete_upload($response['assessment']['artifact']['storage_name'] ?? null);
            // v59 · SEC59-16: žákovi jen hláška z RuntimeException; jiné chyby (TypeError, I/O) jen do logu.
            if (!$e instanceof RuntimeException) error_log('EDUCANET v51 dotazník: ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
            $rawMessage = $e instanceof RuntimeException ? $e->getMessage() : '';
            $_SESSION['intake_old'] = $_POST;
            $_SESSION['intake_seat_conflict'] = $rawMessage === 'Toto místo mezitím obsadil jiný student. Vyber prosím jiné místo.';
            $_SESSION['flash'] = $rawMessage !== '' ? tr($rawMessage) : tr('Akci se nepodařilo dokončit.');
            redirect_to('?view=intake');
        }
        unset($_SESSION['intake_old'], $_SESSION['intake_seat']);
        if (function_exists('acc53_must_change_password') && acc53_must_change_password()) {
            unset($_SESSION['sess53_joined']);
            $_SESSION['flash'] = tr('Díky! Dotazník je uložený. Ještě si nastav vlastní heslo a jsi v kurzu.');
            redirect_to('?view=change_password');
        }
        $_SESSION['flash'] = tr('Díky! Dotazník je uložený. Teď můžeš pokračovat ve výuce.');
        redirect_to('?view=my_intake&done=1');
    }
    return false;
}

function intake_v51_mark_used(string $studentKey, string $email): void
{
    intake_v51_update('activations', static function (array $rows) use ($studentKey, $email): array {
        if (isset($rows[$studentKey]) && is_array($rows[$studentKey])) {
            $rows[$studentKey]['used_at'] = date(DATE_ATOM);
            $rows[$studentKey]['used_email'] = $email;
        }
        return $rows;
    });
}

function intake_v51_build_response(array $class, string $label, array $post, array $files): array
{
    // v59 i18n: registruje msgid z výjimek intake_v51_handle_post(), které se tam kvůli přesné shodě
    // (rozeznání konfliktu místa) nepřekládají tr() přímo v throw, ale až v catch.
    trm('Toto místo mezitím obsadil jiný student. Vyber prosím jiné místo.');
    trm('Dotazník už máš vyplněný.');
    // Jméno je svázané s přihlášeným účtem, aby učitel i student viděli data u správného žáka.
    $parts = preg_split('/\s+/u', trim($label), 2) ?: [];
    $first = intake_v51_text($parts[0] ?? '', 100);
    $last = intake_v51_text($parts[1] ?? '', 100);
    if ($first === '') throw new RuntimeException(tr('Tvůj účet nemá jméno. Požádej učitele o kontrolu přiřazení.'));
    $seatId = intake_v51_text($post['seat_id'] ?? '', 20);
    $seat = intake_v51_find_seat($class, $seatId);
    if (!$seat) throw new RuntimeException(tr('Vyber prosím své místo v učebně.'));
    if (empty($post['privacy_ack'])) throw new RuntimeException(tr('Potvrď prosím, že víš, k čemu učitel odpovědi používá.'));
    $responseId = 'resp_' . bin2hex(random_bytes(8));
    $type = (string)$class['course_type'];
    $assessment = [];
    if (in_array($type, ['networks', 'networks_advanced'], true)) {
        $quiz = intake_v51_score_quiz($type, $post['assessment_answers'] ?? []);
        if ($quiz['missing']) throw new RuntimeException(tr('Odpověz prosím na všechny otázky závěrečného ověření.'));
        $assessment = ['type' => 'knowledge_quiz', 'score' => $quiz['score'], 'max_score' => $quiz['max_score'], 'percentage' => $quiz['percentage'], 'answers' => $quiz['answers']];
    } elseif ($type === 'graphics') {
        $assessment = ['type' => 'graphics_poster', 'artifact' => intake_v51_save_upload((array)($files['graphics_poster'] ?? []), $responseId), 'design_note' => intake_v51_text($post['poster_note'] ?? '', 1200)];
    }
    $t = static fn(string $k, int $max = 3000): string => intake_v51_text($post[$k] ?? '', $max);
    $l = static fn(string $k): array => intake_v51_list($post[$k] ?? []);
    $r = static fn(string $k): int => intake_v51_rating($post[$k] ?? 5);
    return [
        'id' => $responseId,
        'class_id' => (string)$class['id'],
        'submitted_at' => date(DATE_ATOM),
        'source' => 'v51',
        'student' => ['first_name' => $first, 'last_name' => $last, 'preferred_name' => $t('preferred_name', 100), 'seat_id' => $seatId, 'seat_label' => (string)($seat['label'] ?? $seatId)],
        'answers' => [
            'interests' => $l('interests'), 'free_time' => $t('free_time'), 'favorite_school_things' => $t('favorite_school_things'), 'strengths' => $t('strengths'), 'improve' => $t('improve'), 'projects' => $t('projects'),
            'why_subject' => $t('why_subject'), 'expectations' => $t('expectations'), 'year_goal' => $t('year_goal'), 'dream_project' => $t('dream_project'), 'practical_outcome' => $t('practical_outcome'), 'future_direction' => $l('future_direction'), 'future_detail' => $t('future_detail'),
            'learning_styles' => $l('learning_styles'), 'work_mode' => $t('work_mode', 100), 'pace' => $t('pace', 100), 'feedback_styles' => $l('feedback_styles'), 'focus_conditions' => $l('focus_conditions'),
            'ask_help' => $r('ask_help'), 'team_confidence' => $r('team_confidence'), 'presentation_confidence' => $r('presentation_confidence'), 'independence' => $r('independence'), 'problem_solving' => $r('problem_solving'), 'organization' => $r('organization'),
            'teacher_support' => $t('teacher_support'), 'teacher_avoid' => $t('teacher_avoid'), 'learning_blockers' => $t('learning_blockers'),
            'devices' => $l('devices'), 'systems' => $l('systems'), 'digital_confidence' => $r('digital_confidence'),
            'subject_tools' => $l('subject_tools'), 'subject_skills' => $l('subject_skills'), 'subject_topics' => $l('subject_topics'), 'subject_confidence' => $r('subject_confidence'),
            'subject_detail_1' => $t('subject_detail_1'), 'subject_detail_2' => $t('subject_detail_2'), 'subject_detail_3' => $t('subject_detail_3'),
            'last_year_best' => $t('last_year_best'), 'last_year_change' => $t('last_year_change'),
            'fun_fact' => $t('fun_fact'), 'question_teacher' => $t('question_teacher'), 'anything_else' => $t('anything_else'),
        ],
        'assessment' => $assessment,
    ];
}
