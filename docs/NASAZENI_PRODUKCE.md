# EDUCANET – nasazení do produkce (v59)

Průvodce pro školního administrátora, který nasazuje EDUCANET na skutečný server
(ISPConfig, aaPanel nebo vlastní VPS). Předpokládá základní znalost SSH a
webového serveru; kde je potřeba rozhodnutí školy, je to označené.
Pro aaPanel (web is.stanektech.cz) je hotový konkrétní postup v `docs/NASAZENI_AAPANEL.md`
se šablonami v `docs/deploy/aapanel/`.

## 1. Předpoklady

- **PHP 8.1 nebo novější** (vyvíjeno a testováno na 8.4, ale kód musí běžet i na
  minimální podporované 8.1 – viz `INSTALL.md`).
- Povinná rozšíření: `mbstring`, `json`, `session`, `fileinfo`, `hash`.
- `sodium` NEBO `openssl` s podporou `aes-256-gcm` (šifrování e-mailů účtů).
- Volitelně: `curl` nebo `allow_url_fopen`, pokud škola chce Google přihlášení;
  `intl`, `apcu` (výkon).
- Webový server: Apache 2.4 s `.htaccess` (`AllowOverride All` alespoň pro
  kořen), nebo nginx (viz `docs/deploy/nginx-educanet.conf.example`) + PHP-FPM.
- **HTTPS** – certifikát (Let's Encrypt nebo jiný). Aplikace bez HTTPS produkčně
  neběží (cookies, HSTS, `EDUCANET_APP_URL`).
- Funkční MTA (odchozí pošta) se SPF/DKIM pro doménu, pokud škola chce reset
  hesla a ověření e-mailem.
- Žádná databáze – úložiště jsou JSON soubory v `storage/`.

Zkontroluj základ instalace: `php tools/check_install.php` (jen SSH/CLI).
Před samotným nasazením do produkce navíc: `php tools/preflight.php` (viz §7).

## 2. Rozvržení adresářů

Doporučené rozvržení (např. ISPConfig `web1`):

```
/var/www/clients/client1/web1/
├── web/                    ← DocumentRoot = vydání z tools/build_release.php
│   ├── storage/            ← živá data (zapisovatelná, chráněná .htaccess)
│   ├── uploads/            ← nahrané soubory žáků (bez PHP spouštění)
│   └── ...
└── private/
    └── educanet.secrets.php   ← secrets MIMO web root
```

`storage/` a `uploads/` mohou zůstat uvnitř vydání (chráněné vlastním
`.htaccess`), nebo je škola může přesunout mimo docroot přes
`EDUCANET_STORAGE_DIR` – v tom případě je to **vědomé rozhodnutí** zapsané do
`/etc/educanet.env` (viz `docs/deploy/educanet.env.example`).

Soubor se secrety (`private/educanet.secrets.php`) vznikne zkopírováním a
úpravou `educanet.secrets.example.php` **mimo web root**. Alternativa/doplněk:
proměnná `EDUCANET_TEACHER_EXPORT_KEY` přímo v prostředí FPM poolu.

## 3. První nasazení

1. Sestav vydání na vývojovém stroji (mimo produkční server, nebo přímo na
   serveru v dočasném adresáři):
   ```bash
   php tools/build_release.php --out=/srv/educanet-releases/v59-2026-09-27 --lint
   ```
   `--lint` navíc spustí `php -l` na každém `.php` souboru vydání.
   SEC59-11: `RELEASE_MANIFEST.json` zůstává uvnitř vydání (docroot), ale je
   zakázaný kořenovým `.htaccess` i vzorovými vhosty – ověřuje to
   `tools/v59_preflight_audit.php` a `tools/http_smoke.php`.
2. Nahraj obsah adresáře na server jako `web/` (rsync/scp), nebo rozbal `--zip`
   výstup.
3. Nastav `private/educanet.secrets.php` (mimo web root) a/nebo
   `/etc/educanet.env` podle `docs/deploy/educanet.env.example`.
   Do souboru se secrety patří i **`otp_card_key`** – klíč šifrovaných kopií
   jednorázových hesel na kartičkách žáků (SEC58-10). Bez něj si ho aplikace
   vytvoří v `storage/accounts_v58_key.json.php`, tedy hned vedle dat, a klíč pak
   putuje v každé záloze spolu s daty. Nový klíč vygeneruj:
   ```bash
   php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
   ```
   **Přenášíš-li stávající data** a v `storage/` už `accounts_v58_key.json.php`
   je, vlož do `otp_card_key` jeho hodnotu `key` (stejný base64 řetězec) a soubor
   ze `storage/` smaž až poté, co `tools/preflight.php` potvrdí shodu. Jinak
   dříve vydané kartičky nepůjde znovu vytisknout (hesla žáků fungují dál, jen by
   bylo potřeba je vydat znovu). Preflight hodnotu klíče nikdy nevypisuje.
4. Nastav webový server podle `docs/deploy/apache-vhost-educanet.conf.example`
   nebo `docs/deploy/nginx-educanet.conf.example` a php.ini podle
   `docs/deploy/php-fpm-educanet.ini.example`.
5. Ověř oprávnění: `storage/`, `uploads/`, `cache/runtime` musí být
   zapisovatelné uživatelem PHP-FPM poolu; zbytek souborů stačí ke čtení.
6. Migrace úložiště (na čerstvém prázdném úložišti se provedou dvě migrace, které
   bez dat jen zapíšou značku; u přenesených dat z v58 už bývají hotové – spusť to
   i tak, jako návyk):
   ```bash
   php tools/migrate.php --status
   php tools/migrate.php --apply --backup-now
   ```
7. **První učitelský administrátorský účet** (v59 – nahrazuje starý sdílený
   `teacher_export_key` pro přihlášení do `teacher.php` **i** pro CSV export
   (`export_extra_csv.php`) – klíč se v accounts módu už nepoužívá vůbec):
   ```bash
   php tools/v59_teacher_accounts.php create-admin --login=jan.novak --name="Jan Novák"
   ```
   Jednorázové heslo (OTP) se vypíše **jen na terminál** – nikam se neukládá ani
   neloguje. Předej ho administrátorovi bezpečným kanálem (osobně/telefonicky),
   ne e-mailem v otevřeném textu. Při prvním přihlášení na `teacher.php` si
   admin musí nastavit vlastní heslo.

   **SEC59-05 – po vytvoření prvního admina:**
   - Nastav `EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1` (v `/etc/educanet.env`, viz
     `docs/deploy/educanet.env.example`) – accounts mód pak při chybějícím
     souboru účtů vrátí 503 místo tichého pádu zpět do legacy režimu se sdíleným
     klíčem.
   - **Smaž nebo rotuj** starý `EDUCANET_TEACHER_EXPORT_KEY`/
     `teacher_export_key` v `private/educanet.secrets.php` – ponechaný klíč je
     zbytečné riziko (nepoužívá se, ale pořád funguje jako platný secret, pokud
     ho někdo zkusí). `tools/preflight.php` na ponechaný klíč v accounts módu
     hlásí `FAIL`.
8. Přihlas se jako admin na `teacher.php` → záložka **„Učitelé“** a vytvoř účty
   pro ostatní vyučující, přiřaď jim třídy a předměty (role `teacher`/
   `assistant`, `admin` jen pro správce systému).
9. Jednorázová hesla žáků:
   ```bash
   php tools/v58_issue_passwords.php --migrate
   ```
   Kartičky pro tisk: `teacher.php?tab=pristupy&print=1&class=class_3a` (a
   obdobně pro ostatní třídy) – přihlášený administrátor/učitel s přístupem k
   dané třídě.
10. Nastav cron podle `docs/deploy/educanet.cron.example` (zálohy, retention,
    týdenní preflight).
11. Spusť ověření (§7); dokud všechno neprojde (žádný `FAIL`), aplikaci žákům
    nezpřístupňuj.

## 4. Migrace úložiště

- `php tools/migrate.php --status` – vypíše provedené i čekající migrace a
  verze schémat.
- `php tools/migrate.php --apply --backup-now` – provede čekající migrace;
  vždy nejdřív vyžaduje čerstvou zálohu `storage/` (mladší než 1 h),
  `--backup-now` ji sama vytvoří.
- Spouštěj vždy před prvním použitím nové verze a po každém nasazení, které
  mění formát úložiště (viz `CHANGELOG_*` dané verze).

## 5. Zálohy a obnova

- **Záloha**: `php tools/backup_storage.php --keep=14` (výchozí cíl:
  `EDUCANET_BACKUP_DIR`, jinak `<rodič projektu>/educanet-backups` – vždy MIMO
  web root). `--with-uploads` přidá i `uploads/`.
- **Obnova**: `php tools/restore_storage.php` (viz nápověda nástroje pro
  parametry) – použij po havárii nebo před riskantní operací, kterou chceš umět
  vrátit.
- Retence záloh (`--keep`), šifrování zálohy a jejich kopie mimo server
  (off-site) jsou **rozhodnutí školy** – nástroj sám šifrování ani nahrávání na
  vzdálené úložiště nedělá; případně to řeš na úrovni systému (např. `rclone`
  cron navíc, nebo šifrovaný svazek pro `EDUCANET_BACKUP_DIR`).
- Cron: denní záloha a denní retention/archivaci logů (`tools/v58_retention.php
  --apply`) – viz `docs/deploy/educanet.cron.example`.

## 6. Aktualizace a rollback

1. Sestav nové vydání do nového adresáře (`tools/build_release.php --out=...`,
   jiný název než předchozí – např. s datem/verzí).
2. Záloha storage před update: `php tools/backup_storage.php --keep=14`.
3. Přepni DocumentRoot / symlink `current -> <nové vydání>` (atomická operace
   na úrovni systému, žádný okamžik s napůl nahraným kódem).
4. `php tools/migrate.php --apply --backup-now`.
5. Ověř (§7) – preflight, http_smoke, run_audits.
6. **Rollback**: přepni symlink zpět na předchozí adresář vydání. Pokud nová
   verze zapisovala do úložiště novým formátem, obnov `storage/` ze zálohy
   pořízené v kroku 2 (`tools/restore_storage.php`) – proto je záloha
   bezprostředně před migrací povinná.

**Přechod běžící instalace z v58 na v59** = postup výše a navíc z §3: krok 3
(`otp_card_key` – přenes stávající klíč ze `storage/`), krok 7 (první admin přes
`tools/v59_teacher_accounts.php create-admin`, pak `EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1`
a odstranění sdíleného klíče) a krok 8 (účty ostatních vyučujících s přiřazením
tříd a předmětů). Do založení prvního admina běží učitelská část postaru se
sdíleným klíčem; po něm už jen přes účty. Rollback na v58 po založení účtů:
v58 soubor účtů nezná a vrátí se ke sdílenému klíči – ten proto před rollbackem
znovu nastav (nový náhodný, ne ten původní).

## 7. Kontrolní seznam před zpřístupněním žákům

```bash
# 1) Připravenost prostředí (nikdy netiskne secrety ani data žáků)
php tools/preflight.php --env-file=/etc/educanet.env --strict

# 2) Chování naživo (proti skutečné doméně, ne localhost)
php tools/http_smoke.php --base=https://skola.example

# 3) Funkční audity aplikace (na dočasné kopii úložiště, nikdy na ostré storage/)
php tools/run_audits.php --with-smoke
```

Všechny tři musí projít (žádný `FAIL`) předtím, než dáš žákům přihlašovací
kartičky. `preflight.php --strict` vrací nenulový kód i jen na `WARN` – u
volitelných věcí (Google přihlášení, `intl`/`apcu`) to může být v pořádku,
pokud škola danou funkci vědomě nepoužívá; u ostatních `WARN` prověř, jestli
nejde o přehlédnuté nastavení.

## 8. Soukromí dat nezletilých

- Ukládej jen nezbytné údaje; žádná osobní data do URL, logů ani chybových
  hlášek (aplikace to dodržuje, ale platí to i pro vlastní úpravy/skripty).
- Žebříčky v Aréně respektují nastavení soukromí (iniciály / celé jméno /
  anonymně) – neobcházej to vlastním exportem.
- Zálohy obsahují osobní údaje žáků – zacházej s nimi jako s citlivými daty
  (přístup jen pro administrátora, šifrování/off-site podle rozhodnutí školy).
- Žádné externí služby, CDN ani analytika bez rozhodnutí školy (viz `tutor_*`
  níže).

## 9. Otevřené otázky pro školu

Tyto body nemá smysl rozhodovat automaticky – projednej je se školou před
ostrým nasazením:

- **`/V1/` přes web**: SEC59-01 – `V1/` (legacy aplikace s web-nastavitelným
  prvním heslem) se od v59 **nedostane do vydání vůbec**
  (`tools/build_release.php` ho úplně vylučuje) a navíc je zablokovaný
  vícevrstvě i kdyby v adresáři zůstal: `V1/.htaccess`
  (`<FilesMatch "."> Require all denied`), kořenový `.htaccess`
  (`mod_rewrite` guard vracející 403 na `/V1/...` nezávisle na tom, jak Apache
  slučuje `<FilesMatch>` sekce z různých úrovní) a vzorové vhosty
  (`LocationMatch`/`location` blok). Import starších dat z dotazníků
  (`intake_v51_sync_v1()`) čte soubory přímo ze souborového systému, ne přes
  HTTP – web přístup k `/V1/` proto aplikace nepotřebuje. Pokud škola ještě
  potřebuje import z `V1/data`, zkopíruj **jen ten podadresář** (ne celé `V1/`)
  mimo web root a nastav `EDUCANET_V1_DATA_DIR` na jeho cestu – `intake_v51.php`
  proměnnou už podporuje.
- **`materials/*.md`** (testové banky otázek/odpovědí) – výchozí je **zakázat
  z webu úplně** (`materials/.htaccess`); čte je jen serverový kód. Jediná
  výjimka je `materials/school_year/SCHOOL_YEAR_*.md` (roční plán, na který
  odkazuje `teacher_calendar.php`) – SEC59-22: `WEEKLY_SCHEDULE.md` (rozvrh) v
  téže složce **není** veřejný, protože na něj kalendář neodkazuje.
- **Archiv historických `*.md`** (desítky `RELEASE_*`, `UPGRADE_*`,
  `PATCH_MANIFEST_*`, `CHANGELOG_*` v kořeni) – kořenový `.htaccess` je z webu
  blokuje bez ohledu na to, jestli zůstanou v kořeni repozitáře nebo se
  přesunou do `docs/archive/` (samostatný úklid, mimo rozsah tohoto nasazení).
- **HSTS**: hlavička `Strict-Transport-Security` v příkladech `docs/deploy/` je
  od SEC59-13 **defaultně zakomentovaná/vypnutá** (`EDUCANET_HSTS=0` v
  `docs/deploy/educanet.env.example`) – zapni ji (odkomentuj v
  `apache-vhost-educanet.conf.example`/`nginx-educanet.conf.example` a nastav
  `EDUCANET_HSTS=1`) až **poté**, co škola potvrdí doménu a `includeSubDomains`
  (běží úplně všechny subdomény jen přes HTTPS trvale?) – zapnutí je nevratné,
  dokud prohlížečům nevyprší `max-age`. `preload` v příkladech není a zůstává
  ještě opatrnější volba navíc, jen s výslovným rozhodnutím školy.
- **Reverzní proxy**: pokud je před serverem CDN/reverzní proxy, ověř, že
  `X-Forwarded-Proto` posílá jen důvěryhodná vrstva (jinak si útočník může sám
  nastavit hlavičku a obejít `educanet_is_https()`) a že rate limity podle
  `REMOTE_ADDR` vidí skutečnou IP klienta (`X-Forwarded-For` od důvěryhodné
  proxy), ne IP proxy serveru.
- **Google přihlášení** – zapnout jen pokud škola používá Google Workspace pro
  vzdělávání a schválí OAuth origin/doménu.
- **Pošta (MTA/SMTP)** – kdo bude poštovní server spravovat, SPF/DKIM pro
  doménu, `EDUCANET_MAIL_FROM`.
- **Zálohy** – retence (`--keep=N`), šifrování zálohy, kopie mimo server
  (off-site) – nástroj to sám nezajišťuje.
- **`tutor_*`** (externí AI tutor) – vyžaduje odeslání dat žáků mimo server
  školy; zapnout jen s výslovným souhlasem školy a po posouzení GDPR dopadu.
- **`EDUCANET_STORAGE_DIR` mimo docroot** – i s `.htaccess` ochranou uvnitř
  docrootu je přesun mimo něj jistota navíc; škola/administrátor rozhodne podle
  hostingu.
- **Verze PHP na produkčním serveru** – 8.1 je podporované minimum, ale novější
  (8.2/8.3/8.4) je rychlejší a má delší podporu; závisí na možnostech hostingu.

## v67 · provozní doplňky

### Upozornění „Module already loaded“ (PHP)

Hláška `PHP Warning: Module "xyz" is already loaded` znamená, že je rozšíření načtené dvakrát. Zjistíš a opravíš to takto:

1. `php --ini` vypíše hlavní `php.ini` a všechny dodatečné `.ini` soubory (adresář `conf.d`/`php.d`).
2. `php -m | sort | uniq -d` ukáže duplicity; v ini souborech hledej řádky `extension=…` (např. `grep -rn "^extension" $(php --ini | grep -o '/[^ ]*\.ini')`).
3. Ponech řádek `extension=` jen v jednom souboru, druhý zakomentuj středníkem, a restartuj PHP-FPM.
4. `php tools/preflight.php` (od v67) hlásí duplicitně načtená rozšíření jako upozornění.

### Nastavení PHP pro produkci a cron

- `display_errors=Off` (chyby jdou jen do logu; preflight ho kontroluje, týdenní health také).
- `session.use_strict_mode=1` (aplikace ho nastavuje sama; v ini ho nech také zapnuté).
- `apc.enable_cli=1`, je-li nainstalované rozšíření `apcu`: cron a CLI nástroje pak sdílejí cache s webem. Bez něj aplikace funguje, jen pomaleji.
- Týdenní health (`educanet-cron.sh health`) od v67 přidává sloupec **Provoz**: stáří a šifrování poslední zálohy, `backup_key`, čekající migrace, poslední noční přepočet, APCu, `session.use_strict_mode`, `display_errors`.

### Klíč šifrovaných záloh bez vypsání

```bash
php tools/backup_key_init.php                 # náhled (nic nezapíše)
php tools/backup_key_init.php --apply         # zapíše backup_key do secrets (0600), vypíše jen 8 znaků otisku
```

Existující klíč se nikdy nepřepíše. Otisk (8 znaků) si zapiš k uložené kopii klíče v trezoru – klíč samotný nástroj nikdy nevypíše. Viz `docs/ZALOHY_V61.md`.

### Noční přepočet kompetencí (cron nightly)

`bash /www/server/educanet/educanet-cron.sh nightly` (01:30): nejdřív záloha, potom `tools/v67_nightly_recompute.php` s `EDUCANET_NIGHTLY_ALLOW=1`. Nástroj odmítne běh bez tohoto povolení, bez zálohy mladší než 1 hodina i při druhém souběžném běhu (`storage/.nightly.lock`). Zapisuje jen přes `storage_update`/`ev62_append`, je idempotentní a vypisuje jen počty. **Nerozlišuje ostrá data a kopii** – spouštěj ho jen na zamýšleném úložišti.

### Migrace s rollbackem

```bash
php tools/migrate.php --rollback=0003_projects_v65            # migrace s down(): náhled; bez down(): přesný příkaz obnovy ze zálohy před migrací
php tools/migrate.php --rollback=<id> --apply                 # jen migrace s down(); vyžaduje zálohu mladší než 1 h
```

Migrace bez funkce `down()` se nevrací automaticky: nástroj vypíše příkaz `php tools/restore_storage.php --from=<záloha před migrací> --apply`. Před každým rollbackem proveď čerstvou zálohu.

### Přechod školního roku a data v62–v67

`php tools/v67_rollover_plan.php --year=RRRR` (jen čte) spočítá, co přechod udělá s důkazy kompetencí, cestami, portfolii, cíli profilu a rozpracovanými projekty. Soubory v62–v67 jsou klíčované stabilním `student_id`, takže žáky do nové třídy doprovodí samy; rozpracované projektové cykly staré třídy je třeba uzavřít u učitele (přechod roku je z bezpečnostních důvodů nemění).
