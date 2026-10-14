# Ruční kontrola přístupnosti (v80.2)

Automatický audit `php tools/v80_2_a11y_audit.php` ověřuje šířky 320–1920 px, světlý i tmavý režim, zvětšení 200 % a 400 %,
cíle dotyku, písmo, kontrast vykresleného textu, `lang`, nadpisy, landmarky a pořadí i viditelnost fokusu. Co stroj spolehlivě
nepozná – jak stránku **slyší** čtečka obrazovky, jak se ovládá **prstem na skutečném zařízení** a jestli je pořadí fokusu
**smysluplné** – projde člověk podle tohoto seznamu. Stačí 30–45 minut; výsledek zapište do tabulky na konci (✔ / ✘ + poznámka).

**Data:** jen vývojová instance s fiktivními žáky (`student_directory.php`), nikdy ostrá data školy. Spuštění viz `CLAUDE.md`
(dev server, `?class=class_3a&student=…`, učitel přes `teacher.php` s místním klíčem).

## 1. Klávesnice (Chrome nebo Edge, bez myši)
1. **Tab / Shift+Tab** projde odkazy, tlačítka a pole v pořadí čtení (shora dolů, zleva doprava); fokus je vždy vidět (obrys 3 px).
2. První Tab na stránce vede na odkaz **Přeskočit na obsah** (je-li na stránce) a Enter přesune fokus do hlavního obsahu.
3. **Enter / mezerník** spustí tlačítko; **Esc** zavře dialog, drawer a menu účtu a fokus se vrátí na prvek, který je otevřel.
4. Ve stepperu (příprava hodiny, hromadné schválení, úloha s variantou) se po **Další / Zpět** přesune fokus na nadpis kroku.
5. Záložky (`role=tab`): **šipky, Home, End**; akordeony (`details`): Enter / mezerník.
6. V tabulkách s řazením (Výsledky, Mapa třídy) jde řadit klávesnicí a posouvat rámeček tabulky šipkami (rámeček má fokus).
7. Nikde fokus „nezmizí“ (neskočí na `body`) a nezůstane uvězněný mimo otevřený dialog.

## 2. NVDA (Windows, zdarma) + Chrome
Zapnout NVDA, v Chrome otevřít stránku, **Insert+F7** (seznam prvků):
1. **Nadpisy (H):** jeden `h1` s názvem stránky, pod ním `h2` sekcí – bez přeskočené úrovně.
2. **Orientační body (D):** hlavička, navigace (každá má název – „Hlavní navigace“, „Hlavní cíle“…), hlavní obsah, patička.
3. **Formuláře (F):** každé pole přečte svůj popisek, nápovědu (`aria-describedby`) a při chybě i chybu; souhrn chyb se přečte sám (`role=alert`).
4. **Stav bez barvy:** odznaky a buňky mapy třídy čte NVDA slovy („umí“, „potřebuje pomoc“, „hotovo“) – ne jen barvou.
5. **Živé oblasti:** po odeslání se přečte potvrzení (toast `role=status`), v Mapě třídy počet vybraných žáků.
6. **Jazyk:** žákovské UI v angličtině / ukrajinštině čte NVDA správným hlasem (`<html lang>`), český výukový obsah česky (`lang="cs"`).
7. **Ikonová tlačítka** (zavřít, menu, Více) mají srozumitelný název.

## 3. Skutečný telefon a tablet
Telefon (Android Chrome nebo iPhone Safari) a tablet (na výšku i na šířku), přihlášení přes Wi-Fi na vývojovou instanci:
1. Žádný vodorovný posun stránky (posouvá se jen rámeček široké tabulky); text není useknutý.
2. Spodní lišta žáka (Domů · Dnes · Úlohy · Profil) a učitele (4 sekce + Více) jde pohodlně trefit palcem, nepřekrývá obsah ani klávesnici.
3. Pole formuláře otevírají správnou klávesnici (čísla, e-mail, URL); po zvětšení prsty (pinch) se stránka nerozbije.
4. Otočení zařízení zachová rozpracovaný stav (stepper, vyplněné pole).
5. Tmavý režim zařízení: stránky žáka s tmavým režimem (Domů, Přehled, Profil, Úlohy, Portfolio, Můj pokrok, Cvičení, Opakování) ztmavnou, ostatní zůstanou světlé a čitelné.

## 4. Zvětšení, kontrast a pohyb
1. **Zoom prohlížeče 200 % a 400 %** (Ctrl + +) na notebooku 1280 px: obsah se přeskládá do jednoho sloupce bez vodorovného posunu, lišty nezakrývají víc než polovinu výšky.
2. **Velikost písma 200 %** (nastavení prohlížeče → velikost písma): text se nepřekrývá, tlačítka rostou s textem.
3. **Vysoký kontrast Windows** (Alt+Shift+PrtScn): rámečky polí a fokus jsou vidět.
4. **Omezení pohybu** (Windows: Nastavení → Usnadnění → Vizuální efekty → Animace vypnuté): skeleton, toast a drawer se nehýbou.

## 5. Výsledky (vyplní testující)
| Kontrola | Učitel (PC 1366 px) | Žák (telefon) | Tablet | Poznámka |
|---|---|---|---|---|
| Tab / Shift+Tab, viditelný fokus | ☐ | ☐ | ☐ | |
| Přeskočit na obsah, Esc vrací fokus | ☐ | ☐ | ☐ | |
| Stepper: fokus na nadpis kroku | ☐ | ☐ | ☐ | |
| NVDA: nadpisy a orientační body | ☐ | ☐ | – | |
| NVDA: formuláře, chyby, živé oblasti | ☐ | ☐ | – | |
| NVDA: stav slovy (odznaky, mapa) | ☐ | ☐ | – | |
| Jazyk čtečky (cs / en / uk) | – | ☐ | – | |
| Bez vodorovného posunu, palcem trefitelné | ☐ | ☐ | ☐ | |
| Správná klávesnice u polí | – | ☐ | ☐ | |
| Tmavý režim podle zařízení | ☐ | ☐ | ☐ | |
| Zoom 200 % a 400 % | ☐ | ☐ | ☐ | |
| Vysoký kontrast, omezení pohybu | ☐ | ☐ | ☐ | |

Datum, prohlížeč, verze NVDA a zařízení: ______________________ · Testující: ______________________

Nalezené problémy zapište jako hlášení (Správa › Hlášení) nebo do `ROADMAP` další vrstvy; opravy ověřte znovu auditem
`tools/v80_2_a11y_audit.php`.
