# Testy, hodnocení a učitelská analytika (v66)

Vrstva v66 dává učiteli tři věci: ranní přehled „co udělat dnes“, položkovou analýzu testů a **návrh** hodnocení s řetězcem důkazů. O známce vždy rozhoduje učitel.
Technický popis: `CHANGELOG_V66.md`, soubory: `BUILD_MANIFEST_V66.md`, instalace: `INSTALL.md` (sekce v66).

## Rozhodnutí školy (zapsaná v kódu)
| Téma | Rozhodnutí |
|---|---|
| Podoba návrhu | známka 1–5 + slovní hodnocení (výborně / chvalitebně / základy / jen minimum / zatím ne) |
| Váhy zdrojů | sumativní testy 40 % · zvládnutí kompetencí 40 % · projekty v65 20 % (nastavitelné po třídách) |
| Hranice | 1 od 90 %, 2 od 75 %, 3 od 50 %, 4 od 30 %, jinak 5 (nastavitelné po třídách) |
| Zapnutí | výchozí **VYPNUTO**, zapíná jen administrátor po třídách (cockpit → Testy a hodnocení → Nastavení návrhu hodnocení) |
| Co vidí žák | řetězec důkazů vždy (je-li hodnocení ve třídě zapnuté), známku až po převzetí učitelem |
| Sumativní test | označuje učitel třídy v rozsahu; správné odpovědi se ukážou až po odevzdání, test nedává odměny a pořadí otázek je u každého žáka jiné (stabilní) |
| Prahy analýzy | od 20 pokusů; těžká položka p < 0,3, snadná p > 0,9, slabě rozlišuje r < 0,2 |
| Integrita | štítek „k ověření“ (pod 5 s na otázku, nebo shoda špatných odpovědí ≥ 0,8 Jaccard); jen učitel, žák ho nevidí, nikdy trest |
| Exporty | lokální CSV (středník, UTF-8 BOM, ochrana proti vzorcům) a PDF tiskem z prohlížeče |
| Retence | štítky integrity a ranní log do konce školního roku; převzaté známky 30 dní po odchodu žáka |
| Pilot | 3.A a 1.A (kompetence a cesty); analýza a ranní přehled běží v každé třídě, kde jsou data |

## Pro žáka (`?view=hodnoceni`)
- Odkaz „Moje hodnocení: z čeho se skládá“ je v mapě kompetencí (profil), jen ve třídě se zapnutým návrhem.
- Stránka ukazuje rozpis zdrojů s váhami, tvůj výsledek v každém zdroji a **důkazy**: sumativní testy, kompetence (s typem zdroje, skóre a datem) a hodnocení projektů.
- Hry, aréna ani body do podkladu nepatří.
- Známka se objeví až po tom, co ji učitel převezme. Do té doby stránka říká, že učitel zatím žádnou známku nepřevzal.
- Žák nikdy nevidí jména spolužáků ani štítky integrity.

## Pro učitele (cockpit → Podpora → Testy a hodnocení, `?tab=hodnoceni66`)
1. **Ráno: co udělat dnes.** Nejvýš 5 položek seřazených podle dopadu, zbytek pod „Dalších N položek“:
   - *uvízl(a)* v kroku cesty (≥ 3 pokusy bez splnění; dopad 3 + 0,1 × pokusy),
   - *neaktivní* žák (≥ 7 dní bez aktivity; dopad 1,5 až 3),
   - *třída nezvládá kompetenci* (≥ 3 žáci „rozpracováno“ a aspoň polovina žáků s důkazy; dopad 1 + počet žáků).
   Každá položka má jedno tlačítko: přiřadit opakovací cestu **jednomu žákovi** (zobrazí se mu v „Co dál“) nebo celé třídě. Cockpit čte jen cache z cronu; bez ní napíše „připravuje se“.
2. **Položková analýza.** Pro každý test tabulka položek k revizi (p, r, D, slabé distraktory). Pod 20 pokusy je položka „málo dat“. Do analýzy jde první pokus každého žáka.
3. **Druhy testů.** Označení testu lekce nebo ověření cesty jako sumativního/formativního. Startovní test je vždy formativní.
4. **Návrh hodnocení.** Tabulka žáků s návrhem a podíly zdrojů; **Řetězec důkazů** otevře detail (stejné důkazy jako vidí žák); **Převzít návrh** uloží známku beze změny. Převzetí zaznamená čas, známku, hash vzorce a hash učitele.
5. **K ověření.** Štítky integrity (rychlé odevzdání, shoda špatných odpovědí). Slouží k rozhovoru se žákem, ne k trestu; skóre se kvůli nim nikdy nemění.
6. **Exporty.** CSV položkové analýzy a návrhů, tisk/PDF stránky. Soubor návrhů obsahuje jména žáků vaší třídy – nakládejte s ním jako s klasifikací.

## Bod pro DPIA školy (data nezletilých)
- **Co se ukládá navíc:** `student_id` a doba trvání u výsledků startovního testu; cache analýzy bez jmen; štítky integrity jen s hashem; ranní přehled a log zásahů jen s hashi; převzatá známka (známka, procenta, hash vzorce, hash učitele).
- **Co se neukládá:** volný text, jména v cache a logu, IP adresy, odpovědi spolužáků u jiných žáků.
- **Účel a základ:** podpora učitele při hodnocení a včasné pomoci; automatický výpočet není rozhodnutí – každou známku převezme učitel a žák vidí, z čeho vznikla.
- **Rizika a opatření:** profilování žáka (návrh je vypnutý, zapíná admin po třídách, vzorec je žákům viditelný); falešné podezření z podvodu (štítek jen pro učitele, bez dopadu na skóre); únik exportu (lokální soubor, žádná sdílená adresa, doporučeno mazat po použití).
- **Retence:** štítky integrity a ranní log se po skončení školního roku mažou (`tools/v58_retention.php --apply`), převzaté známky 30 dní po odchodu žáka.
- **Žádné externí služby:** výpočet běží lokálně, exporty se stahují do prohlížeče učitele, PDF vzniká tiskem.

## Provoz
- Cron: `v66_item_analysis` (denně v noci) a `v66_morning_build` (po–pá ráno), viz `docs/deploy/educanet.cron.example` a `docs/deploy/aapanel/educanet-cron.sh.example`.
- Ruční přepočet jedné třídy: tlačítko „Přepočítat“ v cockpitu nebo `php tools/v66_item_analysis.php --class=class_3a`.
- Kontrola: `php tools/v66_assessment_audit.php` a `php tools/v66_assessment_http_audit.php` (konec `…_AUDIT_OK`).
