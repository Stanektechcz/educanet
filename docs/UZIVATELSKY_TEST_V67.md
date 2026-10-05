# EDUCANET v67 · uživatelský test (provede škola)

Cíl: ověřit, že žák a učitel zvládnou základní úkoly sami. Test provádí škola; vývoj dodává tento skript, šablonu záznamu (`docs/ut_v67_zaznam.csv`) a souhrn (`php tools/v67_ut_summary.php`).

## Účastníci a pravidla

- 5 žáků (kódy Z1–Z5, ideálně z 3.A a 1.A, různé úrovně) a 2 učitelé (U1–U2). **V záznamu nejsou jména, jen kódy.**
- Testovací účty a vymyšlená data, ne skutečné výsledky žáků. Telefon (390 px) i počítač (1280 px).
- Moderátor nic nevysvětluje, jen čte zadání a měří čas. Úloha končí splněním, vzdáním se nebo po 3 minutách. `uspech` = 1 jen když účastník úlohu splnil sám.

## Úlohy žáka (role `zak`)

| Kód | Zadání (čte moderátor) | Hotovo, když |
|---|---|---|
| Z-01 | „Zjisti, co máš dnes udělat jako první.“ | otevře kartu Teď nebo Co dál |
| Z-02 | „Najdi svoji mapu kompetencí.“ | je na záložce Kompetence v profilu |
| Z-03 | „Přidej si cíl: chceš zvládnout jednu kompetenci.“ | v Můj růst je nový cíl |
| Z-04 | „Zkontroluj, jak ti jde plnění cíle.“ | zapsaná týdenní kontrola |
| Z-05 | „Zjisti, z čeho se skládá tvoje hodnocení.“ | otevře Moje hodnocení |
| Z-06 | „Najdi své portfolio.“ | otevře Moje portfolio |
| Z-07 | „Nastav, co o tobě vidí spolužáci.“ | uloží sdílení v Můj růst |

## Úlohy učitele (role `ucitel`)

| Kód | Zadání | Hotovo, když |
|---|---|---|
| U-01 | „Zjisti, kdo ve třídě uvízl.“ | otevře ranní přehled |
| U-02 | „Podívej se na mapu kompetencí třídy.“ | otevře záložku Kompetence |
| U-03 | „Zkontroluj, jestli proběhla záloha a noční přepočet.“ (jen administrátor) | najde Provoz → Týdenní kontrola |

## Záznam a vyhodnocení

1. Každý pokus jeden řádek v `docs/ut_v67_zaznam.csv` (středník): `ucastnik;role;uloha;uspech;cas_s;poznamka`.
2. `php tools/v67_ut_summary.php` vypíše úspěšnost a medián času po úlohách; úlohy pod 80 % označí „do roadmapy“.
3. Zjištění zapiš do `ROADMAP_V68.md`; kritická (úloha splněná < 50 %) se opraví před dalším vydáním.
