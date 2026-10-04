# EDUCANET v59 – nasazení na aaPanel: is.stanektech.cz

Konkrétní postup pro web **https://is.stanektech.cz** v aaPanelu s kořenem
`/www/wwwroot/is.stanektech.cz` (nginx + PHP-FPM). Obecné principy, migrace, zálohy a otevřené
otázky pro školu jsou v `docs/NASAZENI_PRODUKCE.md` – tady je jen to, co je pro aaPanel jiné.
Šablony souborů: `docs/deploy/aapanel/`.

## Rozvržení na serveru

| Cesta | Obsah | Vlastník / práva |
|---|---|---|
| `/www/wwwroot/is.stanektech.cz/` | kořen webu = obsah vydání z `tools/build_release.php` | `www:www`, 755/644 |
| `…/storage/`, `…/uploads/` | živá data žáků (nasazení je nikdy nepřepisuje) | `www:www`, 770/660 |
| `/www/server/educanet/educanet.secrets.php` | secrety (`otp_card_key`) – **mimo web** | `root:www`, 0640 |
| `/www/server/educanet/educanet.env` | prostředí pro CLI a cron | `root:www`, 0640 |
| `/www/server/educanet/educanet-rules.conf` | pravidla nginx (include v konfiguraci webu) | `root:root`, 0644 |
| `/www/server/educanet/deploy_aapanel.sh`, `educanet-cron.sh` | nasazení a plánované úlohy | `root:root`, 0750 |
| `/www/educanet-backup/` | zálohy `storage/` + `uploads/` | `www:www`, 0750 |
| `/www/educanet/releases/`, `/www/educanet/code-backup/` | rozbalená vydání, zálohy kódu pro rollback | `root:root`, 0750 |

`/www/server/educanet/educanet.secrets.php` je cesta, kterou aplikace na aaPanelu hledá sama
(`educanet_recommended_secret_path()`); `EDUCANET_SECRETS_FILE` ji jen potvrzuje.

## 1. Příprava v aaPanelu (jednou)

1. **App Store → PHP 8.3** (8.1 je minimum, 8.3 doporučeno). V nastavení PHP 8.3 →
   *Install extensions* doinstaluj **fileinfo** a **opcache** (aaPanel je ve výchozím stavu
   nemá); zkontroluj `mbstring` a `openssl` nebo `sodium`. *Disabled functions* nech výchozí –
   aplikace `exec`, `shell_exec` apod. nepotřebuje.
2. **Website → Add site**: doména `is.stanektech.cz`, kořen `/www/wwwroot/is.stanektech.cz`,
   PHP 8.3, **bez databáze a bez FTP**. DNS záznam A/AAAA musí mířit na server.
3. **Site → SSL → Let's Encrypt** (ověření souborem), pak zapni **Force HTTPS**.
   Stojí-li před serverem Cloudflare nebo jiná proxy, vyřeš nejdřív `docs/NASAZENI_PRODUKCE.md` §9
   (důvěryhodnost `X-Forwarded-*`).
4. **Site → Site directory**: vypni **Anti-XSS attack (open_basedir)**. Panelový `.user.ini`
   by zakázal čtení `/www/server/educanet/`; open_basedir místo něj nastavuje
   `educanet-rules.conf` (web + secrets + `/tmp`).
5. **Site → URL rewrite**: nech prázdné (vlastní `location /` je v pravidlech).
6. Přes SSH jako root:
   ```bash
   mkdir -p /www/server/educanet /www/educanet-backup /www/educanet/releases /www/educanet/code-backup
   chown root:www /www/server/educanet && chmod 750 /www/server/educanet /www/educanet
   chown www:www /www/educanet-backup && chmod 750 /www/educanet-backup
   touch /www/wwwlogs/is.stanektech.cz.php-error.log && chown www:www /www/wwwlogs/is.stanektech.cz.php-error.log
   ```
   Nahraj do `/www/server/educanet/` (z projektu):
   - `docs/deploy/aapanel/educanet-rules.nginx.conf.example` jako `educanet-rules.conf`,
   - `docs/deploy/aapanel/educanet.env.example` jako `educanet.env`,
   - `docs/deploy/aapanel/deploy_aapanel.sh.example` jako `deploy_aapanel.sh`,
   - `docs/deploy/aapanel/educanet-cron.sh.example` jako `educanet-cron.sh`,
   - `docs/deploy/aapanel/update_aapanel.sh.example` jako `update.sh` (v61, viz §5),
   - `educanet.secrets.example.php` jako `educanet.secrets.php`.

   Práva: `chown root:www educanet.env educanet.secrets.php && chmod 0640 educanet.env educanet.secrets.php`,
   `chmod 0750 deploy_aapanel.sh educanet-cron.sh update.sh`, `chmod 0644 educanet-rules.conf`.
7. **Secrety**: v `educanet.secrets.php` nastav `otp_card_key` na nový klíč
   (`php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"`) a `teacher_export_key` nech
   prázdný (v režimu učitelských účtů se nepoužívá). **Stěhuješ-li existující data** a je v nich
   `storage/accounts_v58_key.json.php`, dej do `otp_card_key` jeho hodnotu `key` – viz
   `docs/NASAZENI_PRODUKCE.md` §3, krok 3. Klíč nikam nekopíruj ani neposílej e-mailem.
8. **Website → Config** (konfigurace webu): hned za řádek `#SSL-END` vlož
   ```nginx
   include /www/server/educanet/educanet-rules.conf;
   ```
   a u `index` nech jen `index.php`. Vzor výsledku: `docs/deploy/aapanel/is.stanektech.cz.nginx.conf.example`.
   Panel při uložení spustí `nginx -t` a chybnou konfiguraci neuloží. Máš-li jinou verzi PHP
   než 8.3, změň socket `/tmp/php-cgi-83.sock` v `educanet-rules.conf`.

## 2. První nasazení

1. Na vývojovém stroji sestav vydání (`storage/`, secrety ani `V1/` do něj nejdou):
   ```bash
   php tools/build_release.php --out=../educanet-v59-2026-09-27 --lint --zip
   ```
2. Nahraj `educanet-v59-2026-09-27.zip` na server (např. do `/root/`) přes SFTP nebo *Files* v panelu.
3. Suchý běh (nic nemění – ukáže, které soubory by se nahrály či smazaly) a pak ostré nasazení:
   ```bash
   /www/server/educanet/deploy_aapanel.sh deploy /root/educanet-v59-2026-09-27.zip
   /www/server/educanet/deploy_aapanel.sh deploy /root/educanet-v59-2026-09-27.zip --apply
   ```
   Skript nahraje kód přes `rsync --delete` (panelové `index.html`/`404.html` zmizí, `storage/`,
   `uploads/`, `.user.ini` a `.well-known/` zůstanou), nastaví vlastníka `www` a spustí migraci
   úložiště a preflight.
4. **Stěhuješ-li data** z dosavadní instalace: po prvním `--apply` zkopíruj obsah starého
   `storage/` (a `uploads/`) do `/www/wwwroot/is.stanektech.cz/storage/` (a `uploads/`),
   `chown -R www:www` a spusť `--apply` znovu (nový název zipu), aby proběhla migrace nad
   přenesenými daty.
5. `https://is.stanektech.cz/` má ukázat přihlášení žáka.

## 3. Účty a provoz

PHP nástroje spouštěj vždy jako `www` s prostředím aplikace (jinak by vznikly soubory patřící
rootovi, do kterých web nezapíše):

```bash
cd /www/wwwroot/is.stanektech.cz
edu() { runuser -u www -- env -i PATH=/usr/bin:/bin HOME=/tmp bash -c 'set -a; . /www/server/educanet/educanet.env; set +a; exec /www/server/php/83/bin/php "$@"' _ "$@"; }
```

1. **První učitelský admin** (jednorázové heslo se vypíše jen na terminál – předej ho osobně):
   ```bash
   edu tools/v59_teacher_accounts.php create-admin --login=jmeno.prijmeni --name="Jméno Příjmení"
   ```
   `EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1` nech v **obou** souborech (`educanet.env` i
   `educanet-rules.conf`) zapnuté **po celou dobu, i při prvním nasazení** (od v61 už se nic
   nekomentuje). `create-admin` funguje i bez souboru účtů; do jeho spuštění web učitelskou část
   vrací 503 s hláškou „Učitelské účty ještě nejsou založené“ a sdílený klíč nežije (ověřuje
   `tools/v61_ops_audit.php`). Po prvním nasazení tedy jen: `create-admin` → přihlášení admina.
2. Admin se přihlásí na `https://is.stanektech.cz/teacher.php`, nastaví si heslo a v záložce
   **Učitelé** založí ostatní vyučující s jejich třídami a předměty.
3. Hesla žáků: `edu tools/v58_issue_passwords.php --migrate`, kartičky v `teacher.php?tab=pristupy`.
4. **Cron** (aaPanel → Cron → typ *Shell Script*), každá úloha jeden řádek:

   | Úloha | Kdy | Příkaz |
   |---|---|---|
   | Záloha dat | denně 00:15 | `bash /www/server/educanet/educanet-cron.sh backup` |
   | Retence logů | denně 00:45 | `bash /www/server/educanet/educanet-cron.sh retention` |
   | Automatizace | pondělí 03:00 | `bash /www/server/educanet/educanet-cron.sh automation` |
   | Preflight (hlídání) | pondělí 06:00 | `bash /www/server/educanet/educanet-cron.sh preflight` |
   | Týdenní kontrola provozu (v61) | pondělí 06:30 | `bash /www/server/educanet/educanet-cron.sh health` |

   **Týdenní kontrola** (`tools/v61_weekly_health.php`) spustí samotest úložiště, preflight a
   měření rychlosti jen čtením. Souhrn (jen PASS/WARN/FAIL a počty, žádné osobní údaje) zapíše do
   `storage/ops_health_v61.json.php` (posledních 12 běhů), do logu úlohy a do
   `/www/educanet-backup/health-v61.log`. Při posledním FAIL/WARN (nebo když kontrola neběžela déle
   než 10 dní) vidí **jen administrátor** v učitelském cockpitu žlutý/červený pruh s odkazem na
   detail v záložce **Provoz**. Úloha končí kódem 0 (OK), 2 (WARN) nebo 1 (FAIL); aaPanel pak úlohu
   označí jako neúspěšnou – chtěné.

   Zálohy v `/www/educanet-backup` (ne `/www/backup/educanet` – ten aaPanel drží jen pro roota a `www` do něj nezapíše) leží na stejném serveru. Kopii mimo server musí zajistit škola.
   **Šifrování (v61):** nastav-li se v secrets `backup_key`, cron `backup` a nasazení vytvářejí šifrované
   archivy `.edubak` (jinak varování a nešifrovaná záloha). Generování a uložení klíče mimo server,
   obnova krok za krokem a test obnovy: **`docs/ZALOHY_V61.md`**.

## 4. Kontrola před zpřístupněním žákům

```bash
edu tools/preflight.php --env-file=/www/server/educanet/educanet.env --strict
edu tools/http_smoke.php --base=https://is.stanektech.cz
```

Obojí musí skončit bez `FAIL`. `http_smoke` na nginx ověří skutečné zákazy (`storage/`, `tools/`,
`*.json.php`, `RELEASE_MANIFEST.json`…), které lokálně nešlo vyzkoušet. Ručně navíc:

- `https://is.stanektech.cz/storage/` a `https://is.stanektech.cz/bootstrap.php` → 404,
- `http://is.stanektech.cz/` → přesměrování na HTTPS,
- učitel s přiřazenou jedinou třídou nevidí ostatní třídy.

`tools/run_audits.php` se na produkci nespouští – běží na vývojovém stroji před sestavením vydání.

## 4a. Kontrola ukládání

Ověří, že web (uživatel `www`) opravdu zapisuje a čte data: práva a vlastníka `storage/`, `uploads/`,
`cache/runtime`, zápis přes zámek (`storage_update`) i přidávání do proudů, `session.save_path`,
`open_basedir`, volné místo, čerstvost zálohy a čitelnost všech datových souborů. Nic nemění v datech
žáků (zkušební soubory `_selftest_*` se hned mažou) a nevypisuje jejich obsah. `deploy_aapanel.sh`
ho po každém `--apply` spustí sám (jen informativně); ručně:

```bash
sudo -u www env -i PATH=/usr/bin:/bin HOME=/tmp bash -c 'set -a; . /www/server/educanet/educanet.env; set +a; \
  cd /www/wwwroot/is.stanektech.cz && /www/server/php/83/bin/php tools/storage_selftest.php \
  --base=https://is.stanektech.cz --open-basedir=/www/wwwroot/is.stanektech.cz/:/www/server/educanet/:/tmp/'
```

Konec `STORAGE_SELFTEST_OK` = vše v pořádku (`warn` jsou upozornění, ne chyby). `--base` navíc ověří
platnost TLS certifikátu (`session.cookie_secure=1` bez platného certifikátu znemožní přihlášení).
Při `FAIL` ve vlastnictví souborů: `chown -R www:www storage uploads cache`.

## 5. Aktualizace a rollback

### 5a. Jedním příkazem: `update.sh` (v61)

```bash
/www/server/educanet/update.sh
```

Skript (root) stáhne ZIP větve `main` z GitHubu (s cache-busterem), rozbalí ho do `/tmp`, nainstaluje
**aktuální** `deploy_aapanel.sh` z ZIPu (po `bash -n`), spustí `deploy_aapanel.sh from-dir <vydání> --apply`
(záloha dat i kódu, rsync bez dotyku `storage/` a `uploads/`, migrace, preflight, samotest), reloadne
nginx **jen když projde `nginx -t`**, ověří, že `built_at` v `RELEASE_MANIFEST.json` na serveru
odpovídá rozbalenému vydání, spustí `storage_selftest.php --base=https://is.stanektech.cz` a
`tools/http_smoke.php --base=https://is.stanektech.cz` (s `-d allow_url_fopen=1`) a uklidí `/tmp`.
**Poslední řádek** výstupu je vždy právě jeden z:

- `NASAZENO_OK` (exit 0),
- `NASAZENI_FAIL <důvod>` (exit 1) – před ním je vypsaný příkaz pro rollback kódu. Nic se automaticky
  nevrací (nová verze mohla změnit formát dat – viz níže).

Log běhu: `/www/educanet/update-logs/update-<čas>.log`. `update.sh` sám sebe neaktualizuje; po změně
v `update_aapanel.sh.example` ho nahraj ručně (§1). Preflight `FAIL` po nasazení se počítá jako
`NASAZENI_FAIL` – před prvním nasazením proto nejdřív proveď §2–§3 (secrets, `create-admin`).

### 5b. Ruční nasazení ze ZIPu

```bash
/www/server/educanet/deploy_aapanel.sh deploy /root/educanet-v60-….zip          # suchý běh
/www/server/educanet/deploy_aapanel.sh deploy /root/educanet-v60-….zip --apply
```

Každé `--apply` nejdřív zálohuje `storage/` do `/www/educanet-backup` a stávající kód do
`/www/educanet/code-backup/<čas>`; na konci vypíše příkaz pro rollback:

```bash
/www/server/educanet/deploy_aapanel.sh rollback /www/educanet/code-backup/<čas> --apply
```

Rollback vrací jen **kód**. Pokud nová verze už změnila formát dat, obnov i data ze zálohy
pořízené před nasazením (nejdřív bez `--apply` = jen porovnání):

```bash
edu tools/restore_storage.php --from=/www/educanet-backup/<záloha>
edu tools/restore_storage.php --from=/www/educanet-backup/<záloha> --apply
```

## 6. Na co si dát v aaPanelu pozor

- **Neměň verzi PHP webu** bez úpravy socketu v `educanet-rules.conf` – panel přepíše jen
  `#PHP-INFO`, pravidla by dál volala starý socket (chyba 502).
- **Nezapínej znovu open_basedir** v *Site directory* – `.user.ini` by zablokoval secrety.
- **Nepřidávej URL rewrite** z nabídky panelu (`duplicate location "/"`).
- **HSTS** zůstává vypnuté (`EDUCANET_HSTS=0`, hlavička zakomentovaná), dokud škola nepotvrdí,
  že všechny subdomény `stanektech.cz` běží trvale jen na HTTPS.
- Panelové **Backup → Site** a **Files** zahrnují i `storage/` s daty žáků – s takovými zálohami
  zacházej jako s citlivými daty.
- Logy: `/www/wwwlogs/is.stanektech.cz.log`, `…error.log`, chyby PHP `…php-error.log`.
