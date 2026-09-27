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
