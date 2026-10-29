<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 · Jednorázová hesla pro školní účty (SEC-01).
 *
 * Formát hesla: slovo-číslice-slovo-slovo, např. „sova-4827-mrak-kolo“.
 *   - slova z vlastního seznamu ACC58_WORDS (338 neutrálních českých slov, která se píší bez diakritiky),
 *   - 4 číslice jen z 2–9 (0/1 se pletou s o/l), vše malými písmeny, oddělené pomlčkou,
 *   - náhoda z random_int() (CSPRNG).
 * Entropie: 3 · log2(338) + 4 · log2(8) = 3 · 8,40 + 4 · 3 = 25,2 + 12 = 37,2 bitu (požadavek ≥ 36 bitů).
 * Proti online hádání navíc chrání limit pokusů přihlášení a platnost 14 dní.
 *
 * Model účtu (storage/local_accounts.json.php):
 *   password_hash         = hash jednorázového hesla (Argon2id / bcrypt),
 *   must_change_password  = true, dokud si žák nenastaví vlastní heslo,
 *   otp = { issued_at, expires_at (unix čas), issued_by ('teacher'|'cli'|'migration'|'system'), enc? }.
 *   Otevřené heslo se NIKDY neukládá. `enc` je šifrovaná kopie jen pro opakovaný tisk kartičky; po změně
 *   hesla žákem se celé `otp` smaže. `system` = automatické založení účtu (seznam žáků, registrace kódem hodiny).
 *
 * Šifrování `enc` (zvoleno automaticky, acc58_crypto_backend()):
 *   1. sodium_crypto_secretbox (XSalsa20-Poly1305), je-li rozšíření sodium,
 *   2. jinak OpenSSL AES-256-GCM (e-mail účtu jako AAD),
 *   3. bez obojího se `enc` neukládá – heslo učitel uvidí jen jednou na obrazovce hned po vydání.
 *   Lokální C:/php (PHP 8.4.16) nemá sodium, má OpenSSL s aes-256-gcm → používá se varianta 2.
 *   Klíč (32 B): secret `otp_card_key` (base64, v souboru s tajemstvími mimo web root), jinak
 *   storage/accounts_v58_key.json.php (vznikne při prvním použití, ochranný první řádek).
 *
 * API: acc58_generate_otp, acc58_issue_otp, acc58_issue_for_class, acc58_otp_status, acc58_reveal_otp,
 *      acc58_login_gate, acc58_change_password, acc58_migrate, acc58_auto_migrate, acc58_class_cards.
 */

require_once __DIR__ . '/accounts_v53.php';

const ACC58_OTP_TTL_DAYS = 14;
const ACC58_OTP_MAX_TTL_DAYS = 60;
const ACC58_OTP_DIGITS = '23456789';
const ACC58_OTP_DIGIT_COUNT = 4;
const ACC58_OTP_WORD_COUNT = 3;
const ACC58_MIN_ENTROPY_BITS = 36.0;
const ACC58_LOG_LIMIT = 2000;
const ACC58_MIGRATION_VERSION = 1;
const ACC58_ISSUERS = ['teacher', 'cli', 'migration', 'system'];
const ACC58_PROVEN_TTL = 1800;
const ACC58_MSG_EXPIRED = 'Jednorázové heslo vypršelo – požádej učitele o nové.';
const ACC58_MSG_LEGACY = 'Tvůj účet čeká na jednorázové heslo od učitele.';

const ACC58_WORDS = 'sova vlk los rys srna veverka datel kos drozd sokol orel havran kachna kohout pes kocour zebra bobr vydra kuna '
    . 'sysel krtek motyl mravenec pavouk krab humr losos pstruh okoun sumec holub strnad bizon jaguar puma gepard sob had drak '
    . 'jezevec sojka ondatra lasice koroptev kavka vrabec klokan panda tygr lev koala strom list mrak les hora kopec duha vlna '
    . 'oblak sopka laguna pramen vodopad potok jezero pole louka sad park ostrov planeta kometa luna obloha slunce mech jetel '
    . 'lilie fialka kopretina palma borovice tis habr vrba topol jedle smrk bor jasan javor buk dub seno klas zrno semeno kmen '
    . 'led mraz rosa mlha noc den rok jaro podzim zima jablko malina jahoda broskev citron ananas kokos cibule paprika brambor '
    . 'fazole hrach chleba dort med tvaroh jogurt vafle sirup kakao rozinka mandle datle bazalka pepr cukr vanilka oliva '
    . 'rizoto nudle karamel kompot limeta mango papaja avokado lampa okno kniha batoh kufr deka hrnek hrnec konvice sklenice '
    . 'krabice kolo auto vlak tramvaj letadlo raketa balon lopata kladivo pila provaz lano kotva veslo plachta kompas mapa '
    . 'globus hodiny zvonek kytara buben housle harfa piano kamera obraz barva dopis rukavice bota triko svetr kapsa kostka '
    . 'karta figurka lupa mikroskop teleskop baterka lucerna konev hadice nit klubko stan kanoe vor brusle helma domino '
    . 'krychle kruh vektor pixel robot laser radar sonda satelit atom proton neutron foton magnet motor ventil filtr kabel '
    . 'disk modem router server program modul senzor displej tablet monitor procesor archiv soubor ikona obvod dioda most '
    . 'hrad vesnice ulice trh divadlo kino muzeum galerie knihovna stadion tunel silnice socha plot balkon sklep pokoj chodba '
    . 'schody molo farma stodola chata chalupa zahrada vinice kaple rozhledna obora kemp nota tanec rytmus melodie akord tempo '
    . 'opera balet koncert orchestr sbor kvarteto trio duet polka film komiks legenda atlas minuta sekunda sobota leden duben '
    . 'srpen listopad prosinec hokej tenis golf sprint maraton turnaj medaile trofej branka start plavec jezdec judo karate '
    . 'florbal volejbal poklad mince koruna perla krystal diamant jantar zlato bronz ocel olovo helium neon argon kobalt '
    . 'titan nikl zinek chrom platina azur';

function acc58_key_path(): string { return STORAGE_DIR . '/accounts_v58_key.json.php'; }
function acc58_log_path(): string { return STORAGE_DIR . '/accounts_v58_log.json.php'; }
function acc58_state_path(): string { return STORAGE_DIR . '/accounts_v58_state.json.php'; }

// ---------------------------------------------------------------------------
// Atomické úpravy účtů (Z4): čtení → úprava → zápis v jednom zámku
// ---------------------------------------------------------------------------

/**
 * Upraví jeden účet pod zámkem local_accounts. $mutate(array $account): array vrací NOVÝ účet
 * (výjimka zápis zruší). Vrací uložený účet, nebo null, když účet neexistuje (pak se nic nezapíše).
 */
function acc58_account_update(string $email, callable $mutate): ?array
{
    $email = local_email_normalize($email);
    $out = null;
    storage_update(local_accounts_path(), static function (array $accounts) use ($email, $mutate, &$out): array {
        if (!is_array($accounts[$email] ?? null)) return $accounts;
        $next = $mutate($accounts[$email]);
        if (!is_array($next)) throw new RuntimeException('Úprava účtu nevrátila platná data.');
        $accounts[$email] = $next;
        $out = $next;
        return $accounts;
    });
    return $out;
}

/** Založí účet jen tehdy, když e-mail ještě neexistuje (i při souběžné registraci). Vrací true = založeno. */
function acc58_account_insert(string $email, array $account): bool
{
    $email = local_email_normalize($email);
    $created = false;
    storage_update(local_accounts_path(), static function (array $accounts) use ($email, $account, &$created): array {
        if (isset($accounts[$email])) return $accounts;
        $accounts[$email] = $account;
        $created = true;
        return $accounts;
    });
    return $created;
}

// ---------------------------------------------------------------------------
// Propojení účtu se jménem žáka (Z3)
// ---------------------------------------------------------------------------

/** Klíče mapy účtů (kromě $accountKey), které už jsou navázané na stejné jméno ve stejné třídě. */
function acc58_link_other_keys(string $classId, string $needle, string $accountKey): array
{
    $keys = [];
    foreach (student_account_map() as $key => $row) {
        if (!is_array($row) || (string)$key === $accountKey || (string)($row['class_id'] ?? '') !== $classId) continue;
        if (normalized_person_name((string)($row['student_label'] ?? '')) === $needle) $keys[] = (string)$key;
    }
    return $keys;
}

/** Školní (lokální) účty žáka podle třídy a jména, kromě účtu $accountKey. */
function acc58_link_local_accounts(string $classId, string $needle, string $accountKey, array $boundKeys): array
{
    $ids = array_fill_keys(array_map(static fn(string $k): string => substr($k, 6), array_filter($boundKeys, static fn(string $k): bool => str_starts_with($k, 'local:'))), true);
    $out = [];
    foreach (local_accounts() as $email => $row) {
        if (!is_array($row) || 'local:' . (string)($row['id'] ?? '') === $accountKey) continue;
        $sameName = (string)($row['class_id'] ?? '') === $classId && normalized_person_name((string)($row['student_label'] ?? '')) === $needle;
        if ($sameName || isset($ids[(string)($row['id'] ?? '')])) $out[(string)$email] = $row;
    }
    return $out;
}

/**
 * Smí se účet $accountKey (např. Google) navázat na žáka $studentLabel ve třídě $classId?
 * - jméno navázané na jiný než školní účet → zamítnuto (řeší učitel),
 * - jméno patří školnímu účtu → propojení musí potvrdit heslo toho účtu ($proof: jednorázové z kartičky
 *   ve stavu 'pending', nebo vlastní heslo). Vypršelé/sdílené heslo propojení nepotvrdí.
 * Vrací null = povoleno, jinak českou hlášku. Volající má před voláním použít limit pokusů.
 */
function acc58_link_allowed(string $classId, string $studentLabel, string $accountKey, string $proof = ''): ?string
{
    $needle = normalized_person_name($studentLabel);
    if ($needle === '' || $accountKey === '') return 'Vyber svoje jméno ze seznamu.';
    $others = acc58_link_other_keys($classId, $needle, $accountKey);
    if (array_filter($others, static fn(string $k): bool => !str_starts_with($k, 'local:'))) {
        return 'Tohle jméno už je propojené s jiným účtem. Jestli je to chyba, řekni učiteli.';
    }
    $locals = acc58_link_local_accounts($classId, $needle, $accountKey, $others);
    if (!$locals) return null;
    if ($proof === '') return 'Tohle jméno patří ke školnímu účtu. Pro propojení zadej heslo z kartičky od učitele (nebo své vlastní).';
    foreach ($locals as $row) {
        if (acc58_login_gate($row, null, $proof) !== null) continue;
        if (password_verify($proof, (string)($row['password_hash'] ?? ''))) return null;
    }
    return 'Heslo k propojení nesouhlasí. Zkontroluj kartičku, nebo požádej učitele o nové.';
}

// ---------------------------------------------------------------------------
// Generátor
// ---------------------------------------------------------------------------

function acc58_words(): array
{
    static $words = null;
    if ($words === null) $words = array_values(array_unique(preg_split('/\s+/', trim(ACC58_WORDS)) ?: []));
    return $words;
}

function acc58_entropy_bits(): float
{
    return ACC58_OTP_WORD_COUNT * log(count(acc58_words()), 2) + ACC58_OTP_DIGIT_COUNT * log(strlen(ACC58_OTP_DIGITS), 2);
}

function acc58_otp_pattern(): string
{
    return '/^[a-z]{2,12}-[2-9]{4}-[a-z]{2,12}-[a-z]{2,12}$/';
}

function acc58_generate_otp(): string
{
    $words = acc58_words();
    $last = count($words) - 1;
    $digits = '';
    for ($i = 0; $i < ACC58_OTP_DIGIT_COUNT; $i++) $digits .= ACC58_OTP_DIGITS[random_int(0, strlen(ACC58_OTP_DIGITS) - 1)];
    return $words[random_int(0, $last)] . '-' . $digits . '-' . $words[random_int(0, $last)] . '-' . $words[random_int(0, $last)];
}

/** Heslo, které projde validátorem i s kontextem účtu (jméno/e-mail se v něm nesmí objevit). */
function acc58_generate_valid_otp(array $context = []): string
{
    for ($i = 0; $i < 50; $i++) {
        $otp = acc58_generate_otp();
        if (local_password_validate($otp, $context) === null) return $otp;
    }
    throw new RuntimeException('Nepodařilo se vygenerovat jednorázové heslo.');
}

/** Hash Argon2id trvá ~0,35 s – hromadné vydání (třída, migrace) potřebuje delší limit běhu. */
function acc58_allow_slow_hashing(): void
{
    if (PHP_SAPI !== 'cli' && function_exists('set_time_limit')) @set_time_limit(300);
}

function acc58_issuer(string $by): string
{
    return in_array($by, ACC58_ISSUERS, true) ? $by : 'teacher';
}

// ---------------------------------------------------------------------------
// Šifrovaná kopie pro opakovaný tisk kartičky
// ---------------------------------------------------------------------------

/** Jen pro testy: vynutí backend ('sodium'|'openssl'|'none'); null = automaticky. */
function acc58_crypto_force(?string $backend = null, bool $set = false): ?string
{
    static $forced = null;
    if ($set) $forced = in_array($backend, ['sodium', 'openssl', 'none'], true) ? $backend : null;
    return $forced;
}

function acc58_crypto_backend(): string
{
    $forced = acc58_crypto_force();
    if ($forced !== null) return $forced;
    if (function_exists('sodium_crypto_secretbox')) return 'sodium';
    if (function_exists('openssl_encrypt') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true)) return 'openssl';
    return 'none';
}

function acc58_key(): ?string
{
    $secret = base64_decode(educanet_secret('otp_card_key'), true);
    if (is_string($secret) && strlen($secret) === 32) return $secret;
    $decode = static function (array $row): ?string {
        $key = base64_decode((string)($row['key'] ?? ''), true);
        return is_string($key) && strlen($key) === 32 ? $key : null;
    };
    $key = is_file(acc58_key_path()) ? $decode(load_php_json(acc58_key_path())) : null;
    if ($key !== null) return $key;
    $row = storage_update(acc58_key_path(), static function (array $row) use ($decode): array {
        if ($decode($row) !== null) return $row;
        return ['key' => base64_encode(random_bytes(32)), 'created_at' => date(DATE_ATOM), 'purpose' => 'v58 šifrovaná kopie jednorázových hesel pro tisk kartiček'];
    });
    @chmod(acc58_key_path(), 0600);
    return $decode($row);
}

function acc58_encrypt(string $plain, string $email): ?array
{
    $backend = acc58_crypto_backend();
    if ($backend === 'none') return null;
    $key = acc58_key();
    if ($key === null) return null;
    $message = $email . "\n" . $plain;
    if ($backend === 'sodium' && function_exists('sodium_crypto_secretbox')) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return ['alg' => 'sodium-secretbox', 'n' => base64_encode($nonce), 'c' => base64_encode(sodium_crypto_secretbox($message, $nonce, $key))];
    }
    if ($backend === 'openssl') {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($message, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $email, 16);
        if ($cipher === false) return null;
        return ['alg' => 'aes-256-gcm', 'n' => base64_encode($iv), 'c' => base64_encode($cipher), 't' => base64_encode($tag)];
    }
    return null;
}

function acc58_decrypt(array $enc, string $email): ?string
{
    $key = acc58_key();
    $nonce = base64_decode((string)($enc['n'] ?? ''), true);
    $cipher = base64_decode((string)($enc['c'] ?? ''), true);
    if ($key === null || !is_string($nonce) || !is_string($cipher)) return null;
    $plain = false;
    $alg = (string)($enc['alg'] ?? '');
    if ($alg === 'sodium-secretbox' && function_exists('sodium_crypto_secretbox_open')) {
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
    } elseif ($alg === 'aes-256-gcm' && function_exists('openssl_decrypt')) {
        $tag = base64_decode((string)($enc['t'] ?? ''), true);
        if (is_string($tag)) $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, $email);
    }
    if (!is_string($plain) || !str_starts_with($plain, $email . "\n")) return null;
    return substr($plain, strlen($email) + 1);
}

// ---------------------------------------------------------------------------
// Stav hesla a vydání
// ---------------------------------------------------------------------------

/** 'own' vlastní heslo · 'pending' platné jednorázové · 'expired' vypršelé · 'legacy' pořád sdílené/otevřené heslo. */
function acc58_otp_status(array $account, ?int $now = null): string
{
    $now ??= time();
    if (empty($account['must_change_password'])) return !empty($account['initial_password']) ? 'legacy' : 'own';
    $otp = is_array($account['otp'] ?? null) ? $account['otp'] : null;
    if ($otp === null || !isset($otp['expires_at'])) return 'legacy';
    return (int)$otp['expires_at'] <= $now ? 'expired' : 'pending';
}

function acc58_status_label(string $status): string
{
    return match ($status) {
        'own' => 'Vlastní heslo',
        'pending' => 'Čeká na první přihlášení',
        'expired' => 'Jednorázové heslo vypršelo',
        default => 'Čeká na jednorázové heslo',
    };
}

/** Pole účtu pro nové jednorázové heslo (hash + metadata + případná šifrovaná kopie). */
function acc58_otp_fields(string $plain, string $email, string $by, int $ttlDays, int $now): array
{
    $ttlDays = max(1, min(ACC58_OTP_MAX_TTL_DAYS, $ttlDays));
    $otp = ['issued_at' => $now, 'expires_at' => $now + $ttlDays * 86400, 'issued_by' => acc58_issuer($by)];
    $enc = acc58_encrypt($plain, $email);
    if ($enc !== null) $otp['enc'] = $enc;
    return ['password_hash' => local_password_hash($plain), 'must_change_password' => true, 'otp' => $otp, 'password_reset_at' => date(DATE_ATOM, $now)];
}

/** Vrací novou kopii účtu s jednorázovým heslem; otevřené `initial_password` vždy zmizí. */
function acc58_with_otp(array $account, array $fields): array
{
    unset($account['initial_password']);
    return array_replace($account, $fields);
}

function acc58_account_context(string $email, array $account): array
{
    return ['email' => $email, 'name' => (string)($account['student_label'] ?? $account['name'] ?? '')];
}

/** Vydá jednorázové heslo jednomu účtu a vrátí ho (jen pro okamžité zobrazení / tisk). */
function acc58_issue_otp(string $email, string $by, int $ttlDays = ACC58_OTP_TTL_DAYS): string
{
    $email = local_email_normalize($email);
    $account = local_accounts()[$email] ?? null;
    if (!is_array($account)) throw new RuntimeException('Účet neexistuje.');
    $plain = acc58_generate_valid_otp(acc58_account_context($email, $account));
    $fields = acc58_otp_fields($plain, $email, $by, $ttlDays, time());
    if (acc58_account_update($email, static fn(array $row): array => acc58_with_otp($row, $fields)) === null) throw new RuntimeException('Účet neexistuje.');
    acc58_log_many([acc58_log_entry('issue', $email, (string)($account['class_id'] ?? ''), $by)]);
    return $plain;
}

/**
 * Vydá hesla celé třídě. Bez $includeOwn jen účtům bez vlastního hesla (pending/expired/legacy).
 * Vrací [e-mail => ['label', 'password', 'expires_at']] – hesla nikdy nelogovat ani neukládat.
 */
function acc58_issue_for_class(string $classId, bool $includeOwn, string $by): array
{
    acc58_allow_slow_hashing();
    $prepared = [];
    foreach (local_accounts() as $email => $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
        if (!$includeOwn && acc58_otp_status($row) === 'own') continue;
        $plain = acc58_generate_valid_otp(acc58_account_context((string)$email, $row));
        $prepared[(string)$email] = ['plain' => $plain, 'seen_hash' => (string)($row['password_hash'] ?? ''), 'label' => (string)($row['student_label'] ?? $row['name'] ?? ''),
            'fields' => acc58_otp_fields($plain, (string)$email, $by, ACC58_OTP_TTL_DAYS, time())];
    }
    if (!$prepared) return [];
    $applied = [];
    storage_update(local_accounts_path(), static function (array $accounts) use ($prepared, $includeOwn, &$applied): array {
        foreach ($prepared as $email => $item) {
            $row = $accounts[$email] ?? null;
            // Mezitím si žák nastavil vlastní heslo → bez $includeOwn ho nepřepíšeme.
            if (!is_array($row) || (!$includeOwn && !hash_equals($item['seen_hash'], (string)($row['password_hash'] ?? '')))) continue;
            $accounts[$email] = acc58_with_otp($row, $item['fields']);
            $applied[$email] = ['label' => $item['label'], 'password' => $item['plain'], 'expires_at' => (int)$item['fields']['otp']['expires_at']];
        }
        return $accounts;
    });
    acc58_log_many(array_map(static fn(string $email): array => acc58_log_entry('issue_class', $email, $classId, $by), array_keys($applied)));
    return $applied;
}

/** Otevřené jednorázové heslo pro tisk kartičky – jen ve stavu 'pending' a jen se šifrovanou kopií. */
function acc58_reveal_otp(string $email): ?string
{
    $email = local_email_normalize($email);
    $row = local_accounts()[$email] ?? null;
    if (!is_array($row) || acc58_otp_status($row) !== 'pending') return null;
    $enc = $row['otp']['enc'] ?? null;
    return is_array($enc) ? acc58_decrypt($enc, $email) : null;
}

/**
 * Volá se po úspěšném password_verify při přihlášení. Vrací hlášku, když se účet přihlásit nesmí
 * (vypršelé jednorázové heslo, pořád sdílené heslo), jinak null. $password = zadané heslo (volitelné).
 */
function acc58_login_gate(array $account, ?int $now = null, ?string $password = null): ?string
{
    // v58 F6: archivovaný účet (absolvent po přechodu roku) se nepřihlásí.
    if (function_exists('identity58_login_gate') && ($blocked = identity58_login_gate($account)) !== null) return $blocked;
    if ($password !== null && hash_equals(ACC53_DEFAULT_PASSWORD, $password)) return ACC58_MSG_LEGACY;
    return match (acc58_otp_status($account, $now)) {
        'expired' => ACC58_MSG_EXPIRED,
        'legacy' => ACC58_MSG_LEGACY,
        default => null,
    };
}

// ---------------------------------------------------------------------------
// Změna hesla po prvním přihlášení
// ---------------------------------------------------------------------------

/** Session právě prokázala jednorázové heslo (přihlášení) nebo účet vznikl v této session (kód hodiny). */
function acc58_mark_session_proven(string $email): void
{
    $_SESSION['acc58_proven'] = ['email' => local_email_normalize($email), 'at' => time()];
}

function acc58_session_proven(string $email): bool
{
    $row = $_SESSION['acc58_proven'] ?? null;
    return is_array($row) && hash_equals((string)($row['email'] ?? ''), local_email_normalize($email))
        && (int)($row['at'] ?? 0) >= time() - ACC58_PROVEN_TTL;
}

/** Celá změna hesla (ověření, validace, uložení). Vrací null při úspěchu, jinak českou hlášku. */
function acc58_change_password(string $email, string $current, string $password, string $confirm): ?string
{
    $email = local_email_normalize($email);
    $account = local_accounts()[$email] ?? null;
    if (!is_array($account)) return 'Účet neexistuje.';
    $hash = (string)($account['password_hash'] ?? '');
    if (!acc58_session_proven($email) && !password_verify($current, $hash)) return 'Stávající heslo nesouhlasí.';
    if (!hash_equals($password, $confirm)) return 'Nová hesla se neshodují.';
    $context = acc58_account_context($email, $account) + ['current_hash' => $hash];
    $error = local_password_validate($password, $context);
    if ($error !== null) return $error;
    acc53_set_password($email, $password);
    unset($_SESSION['acc58_proven']);
    return null;
}

// ---------------------------------------------------------------------------
// Migrace ze sdíleného hesla
// ---------------------------------------------------------------------------

/** Potřebuje účet jednorázové heslo? (sdílené/otevřené heslo; vlastní hesla se nemění) */
function acc58_migration_candidate(array $row): bool
{
    $status = acc58_otp_status($row);
    if ($status === 'legacy') return true;
    if ($status !== 'own') return false;
    $school = (string)($row['verified_by'] ?? '') === 'school_provisioning' || acc53_is_demo_account($row);
    if (!$school || !empty($row['password_changed_at'])) return false;
    return password_verify(ACC53_DEFAULT_PASSWORD, (string)($row['password_hash'] ?? ''));
}

/** Vydá OTP účtům na sdíleném heslu, odstraní `initial_password` ze všech účtů. Idempotentní, vrací jen počty. */
function acc58_migrate(bool $dryRun): array
{
    acc58_allow_slow_hashing();
    $accounts = local_accounts();
    $stats = ['accounts' => 0, 'issued' => 0, 'kept_own' => 0, 'kept_pending' => 0, 'kept_expired' => 0, 'plaintext_removed' => 0, 'dry_run' => $dryRun];
    $prepared = [];
    foreach ($accounts as $email => $row) {
        if (!is_array($row)) continue;
        $stats['accounts']++;
        if (array_key_exists('initial_password', $row)) $stats['plaintext_removed']++;
        if (acc58_migration_candidate($row)) {
            $stats['issued']++;
            if (!$dryRun) {
                $plain = acc58_generate_valid_otp(acc58_account_context((string)$email, $row));
                $prepared[(string)$email] = ['seen_hash' => (string)($row['password_hash'] ?? ''), 'fields' => acc58_otp_fields($plain, (string)$email, 'migration', ACC58_OTP_TTL_DAYS, time())];
            }
            continue;
        }
        $status = acc58_otp_status($row);
        $stats[$status === 'own' ? 'kept_own' : ($status === 'expired' ? 'kept_expired' : 'kept_pending')]++;
    }
    if ($dryRun || ($stats['issued'] === 0 && $stats['plaintext_removed'] === 0)) return $stats;
    $applied = [];
    storage_update(local_accounts_path(), static function (array $rows) use ($prepared, &$applied): array {
        foreach ($rows as $email => $row) {
            if (!is_array($row)) continue;
            $item = $prepared[$email] ?? null;
            if ($item !== null && hash_equals($item['seen_hash'], (string)($row['password_hash'] ?? ''))) {
                $rows[$email] = acc58_with_otp($row, $item['fields']);
                $applied[] = [(string)$email, (string)($row['class_id'] ?? '')];
                continue;
            }
            unset($row['initial_password']);
            $rows[$email] = $row;
        }
        return $rows;
    });
    $stats['issued'] = count($applied);
    acc58_log_many(array_map(static fn(array $a): array => acc58_log_entry('migration', $a[0], $a[1], 'migration'), $applied));
    return $stats;
}

/** Jednorázová migrace po nasazení: levná kontrola verze, pak zámek na stavovém souboru. Chyby jen do logu. */
function acc58_auto_migrate(): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        if ((int)(load_php_json(acc58_state_path())['migration_version'] ?? 0) >= ACC58_MIGRATION_VERSION) return;
        storage_update(acc58_state_path(), static function (array $state): array {
            if ((int)($state['migration_version'] ?? 0) >= ACC58_MIGRATION_VERSION) return $state;
            $stats = acc58_migrate(false);
            return array_replace($state, ['migration_version' => ACC58_MIGRATION_VERSION, 'migrated_at' => date(DATE_ATOM),
                'issued' => (int)$stats['issued'], 'plaintext_removed' => (int)$stats['plaintext_removed']]);
        });
    } catch (Throwable $e) {
        error_log('EDUCANET v58 migrace hesel: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------------------------
// Přehledy pro učitele a log
// ---------------------------------------------------------------------------

/** Účty třídy pro učitelský přehled (bez hesel). */
function acc58_class_rows(string $classId, ?int $now = null): array
{
    $rows = [];
    foreach (local_accounts() as $email => $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
        $status = acc58_otp_status($row, $now);
        $rows[] = [
            'email' => (string)$email,
            'label' => (string)($row['student_label'] ?? $row['name'] ?? ''),
            'demo' => acc53_is_demo_account($row),
            'status' => $status,
            'expires_at' => in_array($status, ['pending', 'expired'], true) ? (int)($row['otp']['expires_at'] ?? 0) : null,
            'can_reprint' => $status === 'pending' && is_array($row['otp']['enc'] ?? null),
            'last_login_at' => (string)($row['last_login_at'] ?? ''),
            'enc' => is_array($row['otp']['enc'] ?? null) ? $row['otp']['enc'] : null,
        ];
    }
    usort($rows, static fn(array $a, array $b): int => strnatcasecmp($a['label'], $b['label']));
    return $rows;
}

/** Kartičky k tisku: jen účty 'pending' s dešifrovatelnou kopií mají 'password', ostatní null. */
function acc58_class_cards(string $classId): array
{
    return array_map(static function (array $row): array {
        $row['password'] = $row['can_reprint'] && is_array($row['enc']) ? acc58_decrypt($row['enc'], $row['email']) : null;
        unset($row['enc']);
        return $row;
    }, acc58_class_rows($classId));
}

function acc58_log_entry(string $event, string $email, string $classId, string $by): array
{
    $actor = $by === 'teacher' && function_exists('teacher_display_name') ? u_substr(teacher_display_name(), 0, 80) : $by;
    return ['at' => date(DATE_ATOM), 'event' => $event, 'actor' => $actor, 'issued_by' => acc58_issuer($by), 'email' => $email, 'class_id' => $classId];
}

/** Log vydání (kdo/kdy/komu) – nikdy neobsahuje heslo. Drží posledních ACC58_LOG_LIMIT záznamů. */
function acc58_log_many(array $entries): void
{
    if (!$entries) return;
    try {
        storage_update(acc58_log_path(), static fn(array $rows): array => array_slice(array_merge(array_values($rows), array_values($entries)), -ACC58_LOG_LIMIT));
    } catch (Throwable $e) {
        error_log('EDUCANET v58 log hesel: ' . $e->getMessage());
    }
}
