# EDUCANET v61 – šifrované zálohy

Zálohy dat žáků (`storage/`, volitelně `uploads/`) se dají ukládat šifrovaně: **sodium secretstream
XChaCha20-Poly1305**, jeden archiv `storage-RRRRmmdd-HHMMSS.edubak` + `….edubak.sha256` (otisk
šifrovaného souboru). Uvnitř je `manifest.json` (SHA-256 každého souboru) a kopie souborů. Bez klíče
se z archivu nedá přečíst ani názvy souborů; špatný klíč, poškození i zkrácení archivu skončí chybou
a nic se neobnoví.

Nástroje: `tools/backup_storage.php --encrypt`, `tools/restore_storage.php --decrypt`
(PHP rozšíření `sodium` – v PHP 8.3 na aaPanelu bývá; ověř `php -m | grep sodium`).

## 1. Klíč `backup_key`

Klíč je 32 náhodných bajtů v base64, uložený v souboru secrets
(`/www/server/educanet/educanet.secrets.php`, `root:www`, 0640) pod názvem `backup_key`.
**Nikdy ho nedávej do `educanet.env`, do gitu ani do e-mailu a nikdy ho neukládej do stejné zálohy
ani na stejný server jako jediný exemplář.** Bez klíče jsou šifrované zálohy nečitelné – ztráta
klíče = ztráta záloh.

Vygenerování přímo do secrets bez vypsání (nepřepíše existující klíč):

```bash
php -r '$f="/www/server/educanet/educanet.secrets.php"; $s=require $f; if(!empty($s["backup_key"])){fwrite(STDERR,"backup_key už existuje, nic se nemění\n");exit(1);} copy($f,$f.".bak-".time()); $s["backup_key"]=base64_encode(random_bytes(32)); file_put_contents($f,"<?php\nreturn ".var_export($s,true).";\n"); echo "backup_key nastaven\n";'
```

(`file_put_contents` do stávajícího souboru zachová vlastníka i práva; `….bak-<čas>` je kopie původního
souboru bez klíče – po kontrole ji smaž.)

### Uložení klíče mimo server (povinné)

Jednou si klíč zobraz **na vlastním terminálu** a ulož ho do správce hesel a do tištěné obálky v
trezoru školy (alespoň dvě nezávislá místa):

```bash
php -r '$s=require "/www/server/educanet/educanet.secrets.php"; echo $s["backup_key"], PHP_EOL;'
```

Archiv `.edubak` pak můžeš bezpečně kopírovat mimo server (jiný disk, cloud školy) – bez klíče je
k ničemu.

## 2. Zálohování

Ručně:

```bash
edu tools/backup_storage.php --encrypt --keep=14 --with-uploads
```

- `--encrypt` bez nastaveného `backup_key` skončí chybou (exit 1) – **nikdy se nevytvoří nešifrovaná
  náhrada**.
- `--encrypt-if-key` (cron, `deploy_aapanel.sh`): šifruje, je-li klíč nastavený; jinak vypíše varování
  `WARN backup_key není nastavený` a udělá běžnou nešifrovanou zálohu.
- `--keep=N` ponechá posledních N šifrovaných archivů (i s `.sha256`); nešifrované adresáře záloh
  `storage-*/` rotace nemaže.
- Hned po zápisu se archiv zkusí rozšifrovat do dočasného adresáře a porovnat s manifestem; teprve
  potom se `.sha256` zapíše. Dočasné otevřené kopie se vždy smažou.
- Soubor `accounts_v58_key.json.php` (klíč kartiček) se do záloh nikdy nezahrnuje (jako dosud).

Cron `backup` (`educanet-cron.sh`) používá `--encrypt-if-key`. Záložka **Provoz** v učitelském
cockpitu ukazuje poslední zálohu včetně údaje „Šifrovaná: ano (.edubak)“.

## 3. Obnova krok za krokem

1. **Najdi archiv** v `/www/educanet-backup` (nebo ho nahraj z úložiště mimo server) a ověř otisk:
   `sha256sum -c storage-RRRRmmdd-HHMMSS.edubak.sha256`.
2. **Klíč**: musí být v `educanet.secrets.php` jako `backup_key` (při obnově na novém serveru ho tam
   vlož z trezoru – nevypisuj ho do terminálu ani historie příkazů).
3. **Náhled** (nic nemění; vypíše, které soubory by se přidaly/změnily):
   ```bash
   edu tools/restore_storage.php --from=/www/educanet-backup/storage-….edubak --decrypt
   ```
   Archiv se rozšifruje do dočasného adresáře (`--tmp=<adresář>`, výchozí systémový temp, práva 0700),
   ověří se otisk `.sha256` i SHA-256 všech souborů proti manifestu a dočasný adresář se na konci smaže.
4. **Obnova**:
   ```bash
   edu tools/restore_storage.php --from=/www/educanet-backup/storage-….edubak --decrypt --apply
   ```
   Před přepsáním se vytvoří bezpečnostní záloha aktuálního stavu (do `EDUCANET_BACKUP_DIR`).
   `--prune` navíc smaže soubory, které v záloze nejsou (jen vědomě).
5. **Po obnově**: `edu tools/preflight.php --env-file=/www/server/educanet/educanet.env`,
   `edu tools/storage_selftest.php`, přihlášení do `teacher.php`. Klíč kartiček
   (`otp_card_key`) je v secrets, ne v záloze; chybí-li, vydej nová jednorázová hesla.

Chyby: `Dešifrování selhalo (špatný klíč nebo poškozený archiv)` – jiný klíč než při zálohování nebo
poškozený soubor; `Archiv je zkrácený` – nedokončený přenos; `Otisk SHA-256 … nesouhlasí` – soubor se po
záloze změnil. V žádném z těchto případů se do `storage/` nic nezapíše.

## 4. Test obnovy (doporučeno čtvrtletně)

Obnova na **kopii**, nikdy na ostrou `storage/`:

```bash
mkdir -p /www/educanet-restore-test/storage
edu tools/restore_storage.php --from=/www/educanet-backup/storage-….edubak --decrypt \
    --dest=/www/educanet-restore-test/storage --apply
# srovnání s ostrými daty (bez zámků a bez klíče kartiček, který záloha neobsahuje):
diff -rq --exclude='*.lock' --exclude=accounts_v58_key.json.php /www/wwwroot/is.stanektech.cz/storage /www/educanet-restore-test/storage
rm -rf /www/educanet-restore-test
```

Rozdíly smí být jen u souborů, které se změnily po záloze. Automatický ekvivalent běží v auditu
`tools/v61_ops_audit.php` (šifrovaný archiv bez klíče nejde přečíst, se špatným klíčem selže, se správným
obnoví na kopii přesně – shoda SHA-256, rotace funguje).

## 5. Poznámky

- Záloha na tom samém serveru nechrání před ztrátou serveru – kopíruj archivy mimo server.
- Dočasný otevřený adresář `.enc-tmp-*` v adresáři záloh vzniká jen při běhu zálohy (0700) a maže se i při
  chybě; zůstane-li po násilně ukončeném běhu, smaž ho ručně.
- Rotace klíče: nový `backup_key` použije jen nové zálohy; staré archivy potřebují starý klíč – starý
  klíč proto uchovej, dokud nejstarší archiv jím zašifrovaný nevyprší.
