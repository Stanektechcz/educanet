<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · vazba lekcí na ŠVP školy (rozhodnutí školy 8. 10. 2026, bod 1).
 *
 * Zdroj: veřejný ŠVP „Inteligentní digitální systémy 2026“ na brno.educanet.cz (Zákonné dokumenty → ŠVP), ověřeno 11. 10. 2026.
 * Data: krátké formulace výsledků vzdělávání (doslovně z tabulek „učivo a výstupy“, bez delších pasáží), id přidělené EDUCANETem
 * (ids26-<předmět><ročník>-<nn>), vyučovací předmět, ročník, strana dokumentu. Mapování 112 lekcí → výsledky je v svp_v72_map.php.
 *
 * Každá vazba je NÁVRH, dokud ji učitel neschválí (stejné schvalování jako obsah v72: modul `schvalovani`, akce
 * lc72_svp_approve / lc72_svp_return, úložiště storage/lesson_approvals_v72.json.php pod klíčem „svp“, otisk vazby).
 * Vazba je oddělená od overlaye obsahu – nemění otisk schváleného obsahu lekcí (schválení L5–L16 zůstávají platná).
 */

const SVP72_VERSION = 1;
/** Zdroj vazby (jediný dokument školy; RVP je uvedené jen tak, jak ho cituje ŠVP). */
const SVP72_SOURCE = [
    'title' => 'Školní vzdělávací program „Inteligentní digitální systémy 2026“',
    'school' => 'Střední odborná škola EDUCAnet Brno, o.p.s.',
    'field' => '18-20-M/01 Informační technologie',
    'rvp' => 'Rámcový vzdělávací program pro obor vzdělání Informační technologie (podle ŠVP)',
    'valid_from' => '2026-09-01',
    'ref' => 'č. j. EDU2026/938',
    'url' => 'https://brno.educanet.cz/wp-content/uploads/2026/09/SVP-EDUCAnet-Brno-Inteligentni-digitalni-systemy-2026.pdf',
    'index_url' => 'https://brno.educanet.cz/zakonne-dokumenty/',
    'verified_at' => '2026-10-11',
];
const SVP72_SUBJECTS = [
    'DGD' => 'Digitální a generativní design', 'FEV' => 'Front-end vývoj', 'AS' => 'Aplikační software',
    'SAV' => 'Systémy a virtualizace', 'SBE' => 'Síťová bezpečnost', 'ASY' => 'Architektura systémů',
];
/** Ročník třídy pro porovnání s ročníkem výsledku (výsledek jiného ročníku se v přehledu označí). */
const SVP72_CLASS_YEAR = ['class_1a' => 1, 'class_2a' => 2, 'class_3a' => 3, 'class_4a' => 4];
const SVP72_ACTIONS = ['lc72_svp_approve', 'lc72_svp_return'];
const SVP72_NOTE_MAX = 300;

/**
 * Výsledky vzdělávání: id => [předmět, ročník, strana ŠVP, krátká formulace]. Formulace jsou z tabulek „učivo a výstupy“,
 * zkrácené jen vynecháním výčtů v závorce (vyznačeno „…“).
 * @return array<string,array{subject:string,year:int,page:int,text:string}>
 */
function svp72_outcomes(): array
{
    static $out = null;
    if ($out !== null) return $out;
    $rows = [
        // Digitální a generativní design (s. 140)
        'ids26-dgd1-01' => ['DGD', 1, 140, 'vytvoří a upraví rastrovou a vektorovou grafiku'],
        'ids26-dgd1-02' => ['DGD', 1, 140, 'vytvoří grafické návrhy'],
        'ids26-dgd1-03' => ['DGD', 1, 140, 'rozlišuje grafické formáty, jejich vlastnosti a použití'],
        'ids26-dgd1-04' => ['DGD', 1, 140, 'volí vhodné grafické formáty s ohledem na použití a další zpracování'],
        'ids26-dgd2-01' => ['DGD', 2, 140, 'navrhne uživatelské rozhraní (wireframe, interaktivní prototyp) a ověří základní použitelnost'],
        'ids26-dgd2-02' => ['DGD', 2, 140, 'provede základní střih a úpravu videa a export do vhodných formátů'],
        // Aplikační software (s. 136)
        'ids26-as1-01' => ['AS', 1, 136, 'vytvoří prezentaci pomocí odpovídajícího softwaru'],
        'ids26-as1-02' => ['AS', 1, 136, 'si je vědom možností a výhod, ale i rizik (porušování autorských práv) … spojených s používáním IT zařízení'],
        // Front-end vývoj (s. 168)
        'ids26-fev2-01' => ['FEV', 2, 168, 'aplikuje zásady tvorby WWW stránek'],
        'ids26-fev2-02' => ['FEV', 2, 168, 'vytváří webové stránky v jazyce HTML včetně validace'],
        'ids26-fev2-03' => ['FEV', 2, 168, 'formátuje webové stránky pomocí jazyka CSS'],
        'ids26-fev2-04' => ['FEV', 2, 168, 'optimalizuje WWW stránky pro internetové vyhledávače'],
        // Systémy a virtualizace (s. 125–127)
        'ids26-sav2-01' => ['SAV', 2, 125, 'rozumí základním síťovým protokolům (TCP/UDP, IP, HTTP/HTTPS, DNS, SSH, FTP/SFTP)'],
        'ids26-sav2-02' => ['SAV', 2, 125, 'používá Linux jako běžný uživatel: práce s účtem, soubory, adresáři, právy a procesy'],
        'ids26-sav2-03' => ['SAV', 2, 125, 'ovládá základní nástroje pro vzdálenou komunikaci a přenos (SSH, scp, sftp)'],
        'ids26-sav2-04' => ['SAV', 2, 125, 'umí napsat a spustit jednoduché Bash skripty … pro automatizaci běžných úloh'],
        'ids26-sav2-05' => ['SAV', 2, 125, 'aplikuje základní bezpečnostní opatření pro klientská prostředí (silná hesla, SSH klíče, aktualizace, základní firewall)'],
        'ids26-sav3-01' => ['SAV', 3, 126, 'instaluje a konfiguruje síťové služby (web, DNS, DHCP, e-mail, SMB/Fileshare, DB) a zabezpečí je'],
        'ids26-sav3-02' => ['SAV', 3, 126, 'spravuje uživatelské účty, skupiny, oprávnění a ACL pro servery'],
        'ids26-sav3-03' => ['SAV', 3, 126, 'provádí pokročilejší administraci Linux serveru (systemd, správa služeb, logy, aktualizace, balíčkovací systémy)'],
        'ids26-sav3-04' => ['SAV', 3, 126, 'používá Bash pro automatizaci administrativních úloh'],
        'ids26-sav3-05' => ['SAV', 3, 126, 'nastaví a spravuje úložiště (RAID, LVM), plánuje a provádí zálohování a obnovu'],
        'ids26-sav3-06' => ['SAV', 3, 126, 'zabezpečí server (firewall, … TLS certifikáty, aktualizace, hardening)'],
        'ids26-sav3-07' => ['SAV', 3, 126, 'monitoruje a loguje provoz …, nasadí základní opatření pro dostupnost a zotavení'],
        'ids26-sav3-08' => ['SAV', 3, 126, 'nasadí a spravuje virtualizaci/containery v produkci (KVM/Hyper-V, Docker základ)'],
        'ids26-sav3-09' => ['SAV', 3, 126, 'provádí audit, zálohy a obnovy, troubleshooting výkonu a sítě'],
        'ids26-sav4-01' => ['SAV', 4, 127, 'nastaví účty uživatelů a skupin a jejich oprávnění'],
        'ids26-sav4-02' => ['SAV', 4, 127, 'zajistí integritu, důvěrnost a bezpečnost dat v OS'],
        'ids26-sav4-03' => ['SAV', 4, 127, 'zabezpečí počítače proti zneužití'],
        'ids26-sav4-04' => ['SAV', 4, 127, 'definuje funkci a význam jednotlivých síťových služeb'],
        // Síťová bezpečnost (s. 183–184)
        'ids26-sbe3-01' => ['SBE', 3, 183, 'klasifikuje sítě podle zvoleného kritéria'],
        'ids26-sbe3-02' => ['SBE', 3, 183, 'využívá referenční model ISO/OSI a TCP/IP k popisu síťové komunikace'],
        'ids26-sbe3-03' => ['SBE', 3, 183, 'definuje základní komunikační protokoly'],
        'ids26-sbe3-04' => ['SBE', 3, 183, 'nakonfiguruje základní parametry aktivního prvku sítě'],
        'ids26-sbe3-05' => ['SBE', 3, 183, 'zrealizuje jednoduchou síť s využitím pasivních a aktivních prvků'],
        'ids26-sbe4-01' => ['SBE', 4, 184, 'nakonfiguruje parametry počítače pro práci v síti (síťová adresa, DHCP, DNS)'],
        'ids26-sbe4-02' => ['SBE', 4, 184, 'používá druhy šifrování pro zabezpečené připojení a správně je aplikuje'],
        'ids26-sbe4-03' => ['SBE', 4, 184, 'orientuje se v IP adresaci počítačových sítí'],
        'ids26-sbe4-04' => ['SBE', 4, 184, 'použije funkci DHCP služby'],
        'ids26-sbe4-05' => ['SBE', 4, 184, 'použije funkci překladu síťových adres'],
        'ids26-sbe4-06' => ['SBE', 4, 184, 'rozlišuje principy a významy routování mezi sítěmi'],
        'ids26-sbe4-07' => ['SBE', 4, 184, 'definuje základní způsoby napadení sítí a orientuje se v principech jejich obrany'],
        'ids26-sbe4-08' => ['SBE', 4, 184, 'navrhne vhodné zabezpečení počítačové sítě'],
        'ids26-sbe4-09' => ['SBE', 4, 184, 'identifikuje závadu v síti vhodným postupem'],
        'ids26-sbe4-10' => ['SBE', 4, 184, 'odstraní běžné závady v síti'],
        'ids26-sbe4-11' => ['SBE', 4, 184, 'konzultuje problémy s technickou podporou'],
        // Architektura systémů (s. 176)
        'ids26-asy4-01' => ['ASY', 4, 176, 'vyhodnotí výkonnostní rozdíly HW konfigurace'],
        'ids26-asy4-02' => ['ASY', 4, 176, 'dokáže identifikovat slabá místa hodnocené konfigurace a navrhnout optimalizace'],
    ];
    $out = [];
    foreach ($rows as $id => [$subject, $year, $page, $text]) $out[$id] = ['subject' => $subject, 'year' => $year, 'page' => $page, 'text' => $text];
    return $out;
}

/** Mapování lekcí (návrh): [třída => [číslo lekce => list id výsledků]]. */
function svp72_map(): array
{
    static $map = null;
    if ($map === null) {
        $loaded = require __DIR__ . '/svp_v72_map.php';
        $map = is_array($loaded) ? $loaded : [];
    }
    return $map;
}

/** Výsledky jedné lekce (jen platná id). @return list<string> */
function svp72_lesson_ids(string $classId, int $number): array
{
    $known = svp72_outcomes();
    return array_values(array_filter(array_map('strval', (array)(svp72_map()[$classId][$number] ?? [])), static fn(string $id): bool => isset($known[$id])));
}

/** Otisk vazby lekce (16 hex) – mění se s výsledky i verzí zdroje; stav schválení se do něj nepočítá. */
function svp72_hash(array $ids): string
{
    $outcomes = svp72_outcomes();
    $rows = array_map(static fn(string $id): array => [$id, (string)($outcomes[$id]['text'] ?? '')], $ids);
    return substr(hash('sha256', (string)json_encode([SVP72_VERSION, SVP72_SOURCE['url'], SVP72_SOURCE['verified_at'], $rows], JSON_UNESCAPED_UNICODE)), 0, 16);
}

/**
 * Pole `curriculum.svp` modelu lm71 (bez stavu schválení – ten je ve storage, viz svp72_status). Bez vazby null.
 * @return array{source:string,url:string,verified_at:string,hash:string,status:string,outcomes:list<array{id:string,subject:string,subject_label:string,year:int,page:int,text:string}>}|null
 */
function svp72_curriculum(string $classId, int $number): ?array
{
    $ids = svp72_lesson_ids($classId, $number);
    if ($ids === []) return null;
    $outcomes = svp72_outcomes();
    $rows = [];
    foreach ($ids as $id) {
        $o = $outcomes[$id];
        $rows[] = ['id' => $id, 'subject' => $o['subject'], 'subject_label' => SVP72_SUBJECTS[$o['subject']] ?? $o['subject'], 'year' => $o['year'], 'page' => $o['page'], 'text' => $o['text']];
    }
    return ['source' => SVP72_SOURCE['title'], 'url' => SVP72_SOURCE['url'], 'verified_at' => SVP72_SOURCE['verified_at'], 'hash' => svp72_hash($ids), 'status' => 'navrh', 'outcomes' => $rows];
}

/** Katalog výsledků třídy po předmětech (podle mapování): [předmět => list id]. */
function svp72_class_catalog(string $classId): array
{
    $out = [];
    $outcomes = svp72_outcomes();
    foreach ((array)(svp72_map()[$classId] ?? []) as $n => $_) {
        foreach (svp72_lesson_ids($classId, (int)$n) as $id) $out[$outcomes[$id]['subject']][$id] = true;
    }
    ksort($out);
    return array_map(static function (array $ids): array { $list = array_keys($ids); sort($list); return $list; }, $out);
}

/** Záznamy schválení vazby: [třída => [číslo => záznam]]. */
function svp72_records(): array
{
    if (!defined('STORAGE_DIR') || !function_exists('storage_read')) return [];
    $data = storage_read(STORAGE_DIR . '/lesson_approvals_v72.json.php', false);
    return is_array($data['svp'] ?? null) ? $data['svp'] : [];
}

/** Efektivní stav vazby: chybi | navrh | schvaleno | vraceno | zmeneno. @return array{status:string,label:string,hash:string,at:string,by:string,note:string} */
function svp72_status(string $classId, int $number, ?array $records = null): array
{
    $cur = svp72_curriculum($classId, $number);
    if ($cur === null) return ['status' => 'chybi', 'label' => 'chybí vazba', 'hash' => '', 'at' => '', 'by' => '', 'note' => ''];
    $rec = ($records ?? svp72_records())[$classId][(string)$number] ?? null;
    $status = 'navrh';
    if (is_array($rec)) {
        $same = (string)($rec['hash'] ?? '') === $cur['hash'];
        if ((string)($rec['status'] ?? '') === 'schvaleno') $status = $same ? 'schvaleno' : 'zmeneno';
        if ((string)($rec['status'] ?? '') === 'vraceno' && $same) $status = 'vraceno';
    }
    $labels = ['navrh' => 'návrh vazby (neschváleno)', 'schvaleno' => 'vazba schválena', 'vraceno' => 'vazba vrácena k úpravě', 'zmeneno' => 'vazba změněna po schválení – schvalte znovu'];
    return ['status' => $status, 'label' => $labels[$status], 'hash' => $cur['hash'], 'at' => is_array($rec) ? (string)($rec['at'] ?? '') : '',
        'by' => is_array($rec) ? (string)($rec['by'] ?? '') : '', 'note' => is_array($rec) && $status === 'vraceno' ? (string)($rec['note'] ?? '') : ''];
}

/**
 * Rozhodnutí o vazbě (čistá validace + zápis přes storage_update). $lessons = čísla lekcí s otisky, které učitel viděl.
 * @param array<int,string> $lessons číslo => otisk
 * @return int počet zapsaných rozhodnutí
 */
function svp72_decide(string $action, string $classId, array $lessons, string $note, string $by, ?int $now = null): int
{
    if (!in_array($action, SVP72_ACTIONS, true)) throw new RuntimeException('Neznámá akce vazby na ŠVP.');
    if (!isset(SVP72_CLASS_YEAR[$classId])) throw new RuntimeException('Neplatná třída.');
    if ($lessons === []) throw new RuntimeException('Vyberte aspoň jednu lekci.');
    $note = trim((string)preg_replace('/\s+/u', ' ', $note));
    if ($action === 'lc72_svp_return' && $note === '') throw new RuntimeException('Napište krátce, co ve vazbě na ŠVP upravit (nejvýš ' . SVP72_NOTE_MAX . ' znaků).');
    if (mb_strlen($note) > SVP72_NOTE_MAX) throw new RuntimeException('Důvod vrácení může mít nejvýš ' . SVP72_NOTE_MAX . ' znaků.');
    $status = $action === 'lc72_svp_approve' ? 'schvaleno' : 'vraceno';
    $at = date(DATE_ATOM, $now ?? time());
    $by = mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, 60);
    $records = [];
    foreach ($lessons as $n => $hash) {
        $cur = svp72_curriculum($classId, (int)$n);
        if ($cur === null) throw new RuntimeException('Lekce ' . (int)$n . ' nemá návrh vazby na ŠVP.');
        if (!hash_equals($cur['hash'], (string)$hash)) throw new RuntimeException('Vazba lekce ' . (int)$n . ' se mezitím změnila. Zkontrolujte ji a rozhodněte znovu.');
        $records[(string)(int)$n] = ['status' => $status, 'hash' => $cur['hash'], 'at' => $at, 'by' => $by, 'note' => $status === 'vraceno' ? $note : ''];
    }
    storage_update(STORAGE_DIR . '/lesson_approvals_v72.json.php', static function (array $d) use ($classId, $records): array {
        $d['v'] = 1;
        $d['svp'] = is_array($d['svp'] ?? null) ? $d['svp'] : [];
        $log = array_values(array_filter((array)($d['log'] ?? []), 'is_array'));
        foreach ($records as $n => $record) {
            $d['svp'][$classId][$n] = $record;
            $log[] = ['class' => $classId, 'lesson' => (int)$n, 'kind' => 'svp', 'status' => $record['status'], 'hash' => $record['hash'], 'at' => $record['at'], 'by' => $record['by']];
        }
        $d['log'] = array_slice($log, -(defined('LC72_LOG_MAX') ? LC72_LOG_MAX : 500));
        return $d;
    });
    return count($records);
}

/** POST lc72_svp_approve / lc72_svp_return (politiku, CSRF a oprávnění ověřil teacher.php; volá lc72_teacher_handle_post). */
function svp72_teacher_handle_post(string $action): void
{
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!isset(SVP72_CLASS_YEAR[$classId]) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    $lessons = [];
    foreach ((array)($_POST['svp'] ?? []) as $n => $hash) {
        if (is_string($hash) && preg_match('/^\d{1,2}$/', (string)$n) === 1 && (int)$n >= 1 && (int)$n <= 28 && preg_match('/^[0-9a-f]{16}$/', $hash) === 1) $lessons[(int)$n] = $hash;
    }
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $count = svp72_decide($action, $classId, $lessons, is_string($_POST['note'] ?? null) ? (string)$_POST['note'] : '', $by);
    $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $action === 'lc72_svp_approve'
        ? 'Vazba na ŠVP je schválená (' . $count . ' ' . ($count === 1 ? 'lekce' : 'lekcí') . ').'
        : 'Vazba na ŠVP je vrácená k úpravě (' . $count . ' ' . ($count === 1 ? 'lekce' : 'lekcí') . ').'];
    $query = ['tab' => 'schvalovani', 'class' => $classId];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query) . '#svp72');
}
