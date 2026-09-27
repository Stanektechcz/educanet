# EDUCANET v58 · Identita žáka a přechod školního roku (F6, ARC-06)

## Proč
Do v57 byla identita žáka = třída + normalizované jméno. Důsledky: dva žáci se stejným jménem ve třídě sdílejí
všechna data a na nový školní rok neexistoval přechod (žák ve 3.A by začínal od nuly, absolventi by zůstali ve 4.A).
Klíč žáka se vyskytuje ve ~750 místech v 69 souborech, proto se **data nepřepisují** – řeší se mapováním.

## Model
- Každý žák má stálé `student_id` (`stu_<16 hex>`) v registru `storage/identity_v58.json.php`.
- Registr drží `students{…}` (třída, jméno, rok nástupu `cohort`, stav `active|archived|left`, aliasy, historie)
  a `alias_index{alias → student_id}`.
- **Aliasy** = všechny dosavadní klíče: řádek adresáře `dir:<třída>|<jméno>`, `<třída>:student:<h>` (projekty, sociální
  funkce, adaptivní učení, Linux Lab), `<třída>:s:<h>` (profil XP, skill tree), starší `:n:/:l:/:g:`, účty `local:<id>`,
  `google:<sub>`, `email:<e-mail>` a soubor stavu labu `lab:<třída>__<sha1>`.
- `student_id` se zapisuje do mapy účtů (`student_accounts`) i do lokálních účtů. Registr se sestavuje automaticky
  při změně seznamu žáků nebo účtů (`identity58_ensure()`, signatura) – idempotentně.
- Přiřazení nového řádku k žákovi probíhá jen podle účtu nebo řádku adresáře, **nikdy podle datového klíče**
  (ten se mezi ročníky opakuje). Když datový klíč už patří jinému žákovi, registr to nahlásí jako kolizi.

## Kolize jmen – bezpečný postup
1. `php tools/v58_identity.php` (nebo záložka učitele *Podpora → Identita a nový rok*) vypíše podezřelé případy:
   jmenovci v jedné třídě, víc účtů u jednoho žáka, klíč patřící jinému žákovi, stejné jméno ve více třídách.
2. Učitel jmenovce rozliší, např. „Jan Novák (B)“:
   `php tools/backup_storage.php` a pak
   `php tools/v58_identity.php --split=<student_id> --label="Jan Novák (B)" --account=<klíč účtu> --apply`
   (bez `--apply` jen náhled). Vznikne **nové** `student_id` s novými klíči; účet druhého žáka se přepojí na nové jméno.
3. **Data sloučená do té doby zůstávají u původního ID** a automaticky rozdělit nejdou (nelze poznat, co udělal kdo).
   Učitel případně ručně upraví hodnocení; nový žák začíná s čistým profilem.
4. Přejmenování existujícího žáka (např. kvůli jmenovci v cílové třídě před přechodem roku):
   `php tools/v58_identity.php --relabel=<student_id> --label="Petr Svoboda (B)" --apply` – profil a lab se zkopírují
   do nových klíčů, staré zůstávají jako historie.

## Přechod školního roku
```bash
php tools/backup_storage.php                                   # povinné, záloha musí být mladší než 1 h
php tools/v58_rollover.php --dry-run --year=2027 [volby]       # plán: počty, kolize, stav zálohy a háčků
php tools/v58_rollover.php --apply   --year=2027 [volby]       # provedení
```
Co se stane při `--apply` (nic se nemaže ani nepřepisuje):
- 4.A → stav `archived`, přihlášení zablokováno; 3.A → 4.A, 2.A → 3.A, 1.A → 2.A (registr, mapa účtů, lokální účty).
- XP, odznaky/úspěchy a události labu a her se **zkopírují** do nového klíče žáka v nové třídě; vyřešené úrovně
  Linux Labu (procvičování) také. Kurz třídy (lekce, znalostní báze, studio, testy) zůstává historií ročníku.
- Třídy dostanou nové kódy (staré přestanou platit – absolvent se nepřipojí starým kódem).
- Seznamy tříd se upraví překryvem registru (`student_directory()`), dokud škola nenahraje nový seznam.
- Záznam roku v registru obsahuje jen počty (žádná jména). Jména vypisuje CLI jen na interaktivní obrazovku
  (`--names` / `--no-names`).
- Druhé spuštění pro stejný rok nic nezmění. Bez zálohy, s nevyřešeným jmenovcem v cílové třídě nebo bez háčků
  aplikace (INTEGRATION) nástroj odmítne (exit 2).

## Rozhodnutí školy (volby a doporučení)
| Otázka | Volba | Doporučení |
|---|---|---|
| Přenášet XP | `--no-carry-xp` vypne | **Přenést** – motivace a kontinuita. |
| Přenášet odznaky | `--no-carry-badges` vypne | **Přenést** – odznaky jsou za dlouhodobé výsledky. |
| Přenášet postup v Linux Labu | `--no-carry-lab` vypne | **Přenést** vyřešené úrovně; rozpracované instance ne. |
| Data kurzu | vždy archiv | **Archivovat** – kurz nového ročníku je jiný; historie zůstává dostupná přes alias. |
| Absolventi (4.A) | `--allow-graduate-login` povolí přihlášení bez třídy | **Zablokovat přihlášení**. |
| Retence archivu 4.A | zatím ruční | **1 rok** po absolvování, pak anonymizace/smazání přes retenční nástroj (`tools/v58_retention.php`, rozšířit). Rozhodne škola podle GDPR. |
| Opakování ročníku | `--repeat=stu_a,stu_b` | Žák zůstává ve třídě se svými daty; učitel případně vynuluje kurz ručně. |
| Odchod ze školy | `--left=stu_c` | Stav `left`, přihlášení zablokováno, data podle retence. |
| Kódy tříd | `--keep-codes` ponechá | **Nové kódy** každý rok. |
| Nový seznam žáků od školy | po přechodu nahrát aktualizovaný `student_directory.php` | Přesunutí žáci mají alias i pro novou třídu, takže se nezdvojí. Nového jmenovce ve stejné třídě registr nahlásí jako kolizi – rozliš ho před prvním přihlášením. |

## Známá omezení
- Nový žák se stejným jménem jako loňský žák téže třídy sdílí datový klíč s jeho historií (klíče jsou třída + jméno);
  registr to hlásí (`alias_conflict`) a řešením je rozlišené jméno (`--split`/`--relabel`) před prvním přihlášením.
- Přenáší se profil XP (`learning_profiles`) a stav labu v57 (kontext procvičování). Ostatní data vázaná na třídu
  (projekty, hodnocení, skill tree, body v53, progres v56, závody, hry) zůstávají historií ročníku pod starými klíči.
- Kohorta (`cohort`) je informativní (rok začátku posledního přechodu, jinak podle data – září).

## API (identity_v58.php, identity_v58_rollover.php)
`identity58_registry()`, `identity58_build(bool $dryRun)`, `identity58_ensure()`, `identity58_id_for_alias()`,
`identity58_id_for_student()`, `identity58_current_student_id()`, `identity58_aliases()`, `identity58_duplicates()`,
`identity58_directory_overlay()`, `identity58_login_gate()`, `identity58_assignment_allowed()`, `identity58_class_code()`,
`identity58_rollover_plan()`, `identity58_rollover_apply()`, `identity58_split()`, `identity58_relabel()`,
`identity58_backup_check()`. Učitel: `identity58_render_teacher_tab(string $csrf)`, `identity58_teacher_handle_post()`.
Audit: `php tools/v58_identity_audit.php` → `V58_IDENTITY_AUDIT_OK checks=63 failed=0`.
