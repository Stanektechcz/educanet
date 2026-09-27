# EDUCANET v31 · Teacher/Admin access na aaPanelu

Teacher Cockpit je na `https://TVA-DOMENA/teacher.php`.

## Doporučená varianta pro aaPanel: secret soubor mimo `/www/wwwroot`

Vytvoř:

```text
/www/server/educanet/educanet.secrets.php
```

Tuto cestu v31 načítá automaticky a je mimo document root všech běžných aaPanel webů. Pokud ji nepoužiješ, aplikace jako přenosný fallback hledá také `educanet.secrets.php` o jednu úroveň nad document rootem.

Obsah:

```php
<?php
return [
    'teacher_export_key' => 'SEM_VLOZ_NOVY_DLOUHY_NAHODNY_KLIC',
    'teacher_name' => 'Adrian Staněk',
    'teacher_team_id' => 'educanet-teachers',
    'teacher_team_name' => 'EDUCANET učitelé',

    // Volitelné. Bez těchto hodnot EDU Tutor používá lokální bounded režim.
    'tutor_endpoint' => '',
    'tutor_token' => '',
    'tutor_model' => '',
];
```

V ZIPu je `educanet.secrets.example.php`, který můžeš použít jako šablonu, ale **reálný secret nikdy nevracej do web rootu ani do Git repozitáře**.

Doporučená práva:

```bash
mkdir -p /www/server/educanet
chmod 750 /www/server/educanet
chmod 640 /www/server/educanet/educanet.secrets.php
```

Soubor musí být čitelný PHP-FPM uživatelem daného webu. Pokud máš přísnější ownership, nastav ho podle PHP uživatele webu v aaPanelu.

Nový klíč si vytvoř například:

```bash
openssl rand -hex 32
```

Klíč, který už někdy prošel chatem, screenshotem nebo veřejnou dokumentací, pro produkci znovu nepoužívej.

## Alternativa: environment proměnná

Aplikace stále podporuje:

```text
EDUCANET_TEACHER_EXPORT_KEY
EDUCANET_TEACHER_NAME
EDUCANET_TEACHER_TEAM_ID
EDUCANET_TEACHER_TEAM_NAME
```

a volitelně:

```text
EDUCANET_TUTOR_ENDPOINT
EDUCANET_TUTOR_TOKEN
EDUCANET_TUTOR_MODEL
```

Environment proměnná má přednost před secret souborem.

Pro nestandardní umístění secret souboru lze nastavit:

```text
EDUCANET_SECRETS_FILE=/bezpecna/cesta/educanet.secrets.php
```

## Přihlášení

Na `/teacher.php` zadej:

- **Jméno učitele** – auditní/display jméno.
- **Učitelský klíč** – hodnota `teacher_export_key` / `EDUCANET_TEACHER_EXPORT_KEY`.

Aplikace nemá žádné tovární heslo natvrdo ve zdrojovém kódu.

## Bounded EDU Tutor

Bez externího endpointu funguje lokální tutor nad kontextem právě otevřené Knowledge lekce. Vede studenta otázkami a malými kroky a nevydává správnou odpověď k hodnocenému checku.

Pokud připojíš kompatibilní AI endpoint, aplikace odesílá pouze kontext aktuální lekce + studentův dotaz. Do requestu neposílá jméno studenta ani jeho student key. Endpoint je opt-in a bez konfigurace se žádná data mimo server neposílají.

---

## v32 · nejjednodušší aaPanel instalace

Distribuce nyní obsahuje také skutečně pojmenovaný template:

```text
private/educanet.secrets.php
```

Je v něm pouze placeholder. Produkční secret vytvoř jedním příkazem:

```bash
cd /www/wwwroot/TVUJ_WEB
bash tools/install_teacher_secret.sh
```

Výchozí cílová cesta je:

```text
/www/server/educanet/educanet.secrets.php
```

Po instalaci proveď kontrolu:

```bash
php tools/check_install.php
```

Chceš-li vlastní cestu:

```bash
EDUCANET_SECRETS_FILE=/bezpecna/cesta/educanet.secrets.php bash tools/install_teacher_secret.sh
```


## v32.1 · ISPConfig + CLI hotfix

Pokud je aplikace nasazená v ISPConfig cestě jako `/var/www/clients/client10/web9/web/...`, instalační skript nyní automaticky uloží secret do `/var/www/clients/client10/web9/private/educanet.secrets.php`. `tools/check_install.php` je CLI-only nástroj; přes web už nespadne na `STDERR`, ale bezpečně zobrazí instrukci ke spuštění přes SSH.


## v45.7 · týmové uložené filtry

Saved Filters podporují dvě úrovně viditelnosti: **Osobní** a **Týmový**. Tým se určuje stabilním `teacher_team_id`; lidský název zobrazený v UI nastavuje `teacher_team_name`.

Doporučená konfigurace v secret souboru:

```php
return [
    // ...
    'teacher_team_id' => 'educanet-brno-it',
    'teacher_team_name' => 'EDUCANET Brno · IT',
];
```

Stejné `teacher_team_id` musí používat všichni učitelé, kteří mají sdílet týmové presety. Hodnotu po vytvoření týmových filtrů zbytečně neměň; změna ID vytvoří nový oddělený team namespace. `teacher_team_id` není heslo, ale identifikátor hranice sdílení.

Týmový preset lze použít každým učitelem ve stejném týmu. **Smazat jej může pouze jeho autor**; backend kontroluje `owner_key` nezávisle na tom, zda se v UI zobrazí mazací tlačítko. Nové v45.7 presety používají navíc náhodný HttpOnly actor token svázaný se jménem učitele, takže stejný display název z jiného prohlížeče nestačí k převzetí vlastnictví. Staré osobní presety zachovávají legacy kompatibilitu.

Existující filtry z v45.5/v45.6, které pole `scope` nemají, se z bezpečnostních důvodů považují za **osobní** a upgradem se samy nesdílí.


## v46 · role učitele

Volitelně nastav v externím secret souboru roli:

```php
'teacher_role' => 'teacher', // admin | lead | teacher | assistant
```

Role je vždy ověřována na serveru. `assistant` má read-only analytiku, osobní filtry, poznámky a omezené označování; nemůže publikovat hodnocení, měnit kurikulum, spravovat projekty, vytvářet intervence ani automatizace. `teacher` a `lead` mají standardní plná učitelská oprávnění, `admin` je neomezený.


## v46.1 · týmové role, audit a automation runner

Pro plné Teacher Operations nastav kromě `teacher_team_id` také výchozí roli:

```php
'teacher_role' => 'admin', // admin | lead | teacher | assistant
```

Po přihlášení se browser-bound teacher actor zapíše do týmového registru. `admin` může spravovat celý tým, `lead` může spravovat standardní učitelské role podle serverové permission matice, `teacher` provádí běžné výukové zásahy a `assistant` má omezený rozsah. UI nikdy není jedinou ochranou — každá mutační akce znovu ověřuje permission na serveru.

Automatické watch filtry spouštěj mimo web request, například:

```bash
*/15 * * * * cd /cesta/k/EDUCANET && php tools/v46_automation_tick.php >/dev/null 2>&1
```

Operations Audit je dostupný v Teacher → Podpora → **Audit operací**. Safe Undo je dostupný pouze autorovi vratné bulk akce, maximálně 15 minut a jen pokud se dotčené záznamy od té doby nezměnily.
