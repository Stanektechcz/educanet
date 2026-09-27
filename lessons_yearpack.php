<?php

declare(strict_types=1);

return json_decode(<<<'JSON'
{
  "class_1a": [
    {
      "id": "gfx_web_anatomy",
      "number": 10,
      "title": "Lekce 10 · Anatomie webu: hierarchie od plakátu k obrazovce",
      "subtitle": "2 × 45 minut",
      "goal": "Přenést principy vizuální hierarchie do jednoduché webové stránky a rozlišit header, hero, obsah, CTA a footer.",
      "knowledge": [
        "web-layout-basics-i",
        "layout-rhythm-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Rozlož stránku na role",
          "text": "Najdi header, hero, hlavní obsah, CTA a footer."
        },
        {
          "time": "15–30",
          "title": "Priorita obsahu",
          "text": "Zkrať zadání na headline, supporting text a CTA."
        },
        {
          "time": "30–45",
          "title": "Webový skeleton",
          "text": "Nakresli desktop frame 1440 px."
        },
        {
          "time": "45–60",
          "title": "Scan test",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Co se stane na mobilu",
          "text": "Označ prvky, které mohou jít pod sebe."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "anatomy",
          "time": "15 min",
          "title": "01 · Rozlož stránku na role",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "web-layout-basics-i"
          ],
          "tasks": [
            "Najdi header, hero, hlavní obsah, CTA a footer.",
            "U každé části napiš její jediný hlavní účel."
          ]
        },
        {
          "id": "hierarchy",
          "time": "15 min",
          "title": "02 · Priorita obsahu",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zkrať zadání na headline, supporting text a CTA.",
            "Vyber jednu informaci, která nesmí soutěžit s headline."
          ]
        },
        {
          "id": "grid",
          "time": "15 min",
          "title": "03 · Webový skeleton",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nakresli desktop frame 1440 px.",
            "Použij společné hrany a konzistentní horizontální padding.",
            "Neřeš zatím dekorace."
          ],
          "knowledge": [
            "layout-rhythm-i"
          ]
        },
        {
          "id": "scan",
          "time": "12 min",
          "title": "04 · Scan test",
          "kind": "quiz",
          "xp": 25,
          "question": "Co má uživatel pochopit z hero sekce během několika sekund?",
          "options": [
            "Co stránka nabízí a jaký je hlavní další krok.",
            "Všechny detaily webu a celý footer.",
            "Pouze název použitého fontu."
          ],
          "correct": 0,
          "explanation": "Hero má rychle sdělit hodnotu a primární akci."
        },
        {
          "id": "mobile_think",
          "time": "15 min",
          "title": "05 · Co se stane na mobilu",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Označ prvky, které mohou jít pod sebe.",
            "Urči, co musí zůstat nad přehybem."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Který princip se z plakátu přenáší na web nejlépe?",
          "options": [
            "Jasná hierarchie a řízená cesta pozornosti.",
            "Pevné souřadnice každého prvku.",
            "Co nejvíce dekorativních efektů."
          ],
          "correct": 0,
          "explanation": "Médium se mění, ale práce s prioritou a pozorností zůstává."
        }
      ],
      "worksheet": [
        "Zakresli 5 hlavních oblastí landing page.",
        "Napiš headline do 8 slov.",
        "Vyber primární CTA.",
        "Popiš jednu věc, kterou bys na mobilu přesunul/a."
      ],
      "teacher_notes": [
        "Nezačínej HTML/CSS. Cílem je vizuální a informační model.",
        "Při review se ptej „co vidíš první?“ místo „líbí se ti to?“."
      ]
    },
    {
      "id": "gfx_responsive_basics",
      "number": 11,
      "title": "Lekce 11 · Responsive základ: jedna stránka, tři šířky",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout stejný obsah pro mobil, tablet a desktop bez slepého zmenšování.",
      "knowledge": [
        "responsive-layout-i",
        "responsive-series"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Responsive ≠ zmenšení",
          "text": "Porovnej 390, 768 a 1440 px."
        },
        {
          "time": "15–30",
          "title": "Mobile first skeleton",
          "text": "Navrhni 390px frame."
        },
        {
          "time": "30–45",
          "title": "Tablet adaptace",
          "text": "Přidej druhý sloupec jen tam, kde pomůže."
        },
        {
          "time": "45–60",
          "title": "Desktop kompozice",
          "text": "Rozšiř layout na 1440 px."
        },
        {
          "time": "60–75",
          "title": "Breakpoint mindset",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "principle",
          "time": "15 min",
          "title": "01 · Responsive ≠ zmenšení",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "responsive-layout-i"
          ],
          "tasks": [
            "Porovnej 390, 768 a 1440 px.",
            "Urči, co se přeskupí, co zůstane a co se může skrýt."
          ]
        },
        {
          "id": "mobile",
          "time": "15 min",
          "title": "02 · Mobile first skeleton",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni 390px frame.",
            "Obsah řaď podle priority, ne podle desktopového pořadí."
          ]
        },
        {
          "id": "tablet",
          "time": "15 min",
          "title": "03 · Tablet adaptace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Přidej druhý sloupec jen tam, kde pomůže.",
            "Ověř délku řádků a spacing."
          ]
        },
        {
          "id": "desktop",
          "time": "15 min",
          "title": "04 · Desktop kompozice",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozšiř layout na 1440 px.",
            "Neroztahuj text přes celou šířku."
          ]
        },
        {
          "id": "breakpoint",
          "time": "12 min",
          "title": "05 · Breakpoint mindset",
          "kind": "quiz",
          "xp": 25,
          "question": "Kdy dává smysl změnit layout?",
          "options": [
            "Když obsah přestává fungovat, ne jen na předem naučeném čísle.",
            "Vždy přesně po 100 px.",
            "Jen na desktopu."
          ],
          "correct": 0,
          "explanation": "Breakpoint řeší konkrétní problém obsahu a prostoru."
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co se má napříč šířkami zachovat?",
          "options": [
            "Obsahová priorita a vizuální systém.",
            "Přesný počet sloupců.",
            "Stejná velikost nadpisu v pixelech."
          ],
          "correct": 0,
          "explanation": "Responsive mění provedení, ne význam a prioritu."
        }
      ],
      "worksheet": [
        "Nakresli mobile/tablet/desktop stejných 5 sekcí.",
        "U každého breakpointu napiš jednu změnu a důvod."
      ],
      "teacher_notes": [
        "Studentům dovol odlišná řešení, pokud umí vysvětlit prioritu obsahu.",
        "Kontroluj především přetečení, dlouhé řádky a CTA."
      ]
    },
    {
      "id": "gfx_components_intro",
      "number": 12,
      "title": "Lekce 12 · Komponenty: tlačítka, karty a opakovatelná pravidla",
      "subtitle": "2 × 45 minut",
      "goal": "Pochopit komponentu jako opakovatelný vzor se stavy, ne jako jednorázový obrázek.",
      "knowledge": [
        "components-i",
        "design-system-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Co je komponenta",
          "text": "Rozliš jednorázový blok a opakovatelnou komponentu."
        },
        {
          "time": "15–30",
          "title": "Button systém",
          "text": "Navrhni primary a secondary button."
        },
        {
          "time": "30–45",
          "title": "Stavy",
          "text": "Přidej default, hover/focus a disabled."
        },
        {
          "time": "45–60",
          "title": "Karta",
          "text": "Vytvoř kartu s názvem, popisem a akcí."
        },
        {
          "time": "60–75",
          "title": "Konzistence",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "component",
          "time": "15 min",
          "title": "01 · Co je komponenta",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "components-i"
          ],
          "tasks": [
            "Rozliš jednorázový blok a opakovatelnou komponentu.",
            "Vyber Button a Card jako první dva vzory."
          ]
        },
        {
          "id": "button",
          "time": "15 min",
          "title": "02 · Button systém",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni primary a secondary button.",
            "Sjednoť výšku, padding, radius a typografii."
          ]
        },
        {
          "id": "states",
          "time": "15 min",
          "title": "03 · Stavy",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Přidej default, hover/focus a disabled.",
            "Stav nesmí být vyjádřen jen barvou."
          ]
        },
        {
          "id": "card",
          "time": "15 min",
          "title": "04 · Karta",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vytvoř kartu s názvem, popisem a akcí.",
            "Použij stejné spacing tokeny jako u buttonu."
          ]
        },
        {
          "id": "consistency",
          "time": "12 min",
          "title": "05 · Konzistence",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je největší výhoda komponent?",
          "options": [
            "Stejné pravidlo lze bezpečně opakovat a měnit na jednom místě.",
            "Každá karta může mít náhodný padding.",
            "Není potřeba řešit stavy."
          ],
          "correct": 0,
          "explanation": "Komponenty snižují náhodnost a zrychlují iteraci."
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co patří do definice tlačítka?",
          "options": [
            "Vzhled, obsahová pravidla a interakční stavy.",
            "Jen barva pozadí.",
            "Pouze export PNG."
          ],
          "correct": 0,
          "explanation": "Komponenta není statický screenshot."
        }
      ],
      "worksheet": [
        "Definuj Button: height, padding, radius, text style.",
        "Nakresli 3 stavy.",
        "Definuj Card se stejným spacing systémem."
      ],
      "teacher_notes": [
        "Neřeš komplexní design system. Cílem je opakovatelnost.",
        "Důraz na focus state připraví studenty na accessibility."
      ]
    },
    {
      "id": "gfx_web_typography",
      "number": 13,
      "title": "Lekce 13 · Webová typografie: čitelnost, délka řádku a rytmus",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout typografii pro obrazovku s jasnými rolemi, rozumnou délkou řádku a konzistentním vertikálním rytmem.",
      "knowledge": [
        "web-typography-i",
        "editorial-typography-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Textové role",
          "text": "Definuj Display/H1, H2, body, small a action."
        },
        {
          "time": "15–30",
          "title": "Řádek a line-height",
          "text": "Zkrať příliš dlouhý body text."
        },
        {
          "time": "30–45",
          "title": "Type scale",
          "text": "Sestav malou škálu bez 10 náhodných velikostí."
        },
        {
          "time": "45–60",
          "title": "Vertikální rytmus",
          "text": "Použij opakující se spacing mezi headingem, textem a akcí."
        },
        {
          "time": "60–75",
          "title": "Čitelnost",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "roles",
          "time": "15 min",
          "title": "01 · Textové role",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "web-typography-i"
          ],
          "tasks": [
            "Definuj Display/H1, H2, body, small a action.",
            "Každá role musí mít konkrétní účel."
          ]
        },
        {
          "id": "line",
          "time": "15 min",
          "title": "02 · Řádek a line-height",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zkrať příliš dlouhý body text.",
            "Uprav line-height tak, aby odstavce byly čitelné."
          ]
        },
        {
          "id": "scale",
          "time": "15 min",
          "title": "03 · Type scale",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Sestav malou škálu bez 10 náhodných velikostí.",
            "Ověř mobile headline wrap."
          ]
        },
        {
          "id": "rhythm",
          "time": "15 min",
          "title": "04 · Vertikální rytmus",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Použij opakující se spacing mezi headingem, textem a akcí."
          ]
        },
        {
          "id": "readability",
          "time": "12 min",
          "title": "05 · Čitelnost",
          "kind": "quiz",
          "xp": 25,
          "question": "Která změna nejčastěji zlepší dlouhý odstavec na desktopu?",
          "options": [
            "Omezit šířku textového sloupce a nastavit vhodný line-height.",
            "Roztáhnout text přes celý monitor.",
            "Snížit font pod 12 px."
          ],
          "correct": 0,
          "explanation": "Čitelnost ovlivňuje šířka řádku, velikost i line-height."
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je typografický systém?",
          "options": [
            "Malý soubor pojmenovaných rolí a pravidel používaných konzistentně.",
            "Jeden font pro každý odstavec.",
            "Seznam efektů v editoru."
          ],
          "correct": 0,
          "explanation": "Systém dává textu předvídatelnou strukturu."
        }
      ],
      "worksheet": [
        "Navrhni 5 textových rolí.",
        "Pro body text napiš size/line-height/max-width.",
        "Otestuj H1 na 390 px."
      ],
      "teacher_notes": [
        "Vysvětluj role, ne magická čísla.",
        "Při hodnocení se ptej, zda je stránka skenovatelná bez barev."
      ]
    },
    {
      "id": "gfx_web_images",
      "number": 14,
      "title": "Lekce 14 · Obraz pro web: crop, rozlišení a export",
      "subtitle": "2 × 45 minut",
      "goal": "Připravit vizuály pro web tak, aby podporovaly obsah, měly správný crop a nebyly zbytečně datově těžké.",
      "knowledge": [
        "image-web-i",
        "production-preflight-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Úloha obrazu",
          "text": "Urči, zda obrázek vysvětluje, dokumentuje nebo jen dekoruje."
        },
        {
          "time": "15–30",
          "title": "Crop pro hero",
          "text": "Najdi focal point."
        },
        {
          "time": "30–45",
          "title": "Export variant",
          "text": "Připrav master a web export."
        },
        {
          "time": "45–60",
          "title": "Formát",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Obsah vs dekorace",
          "text": "Napiš smysluplný alt popis pro informační obraz."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "purpose",
          "time": "15 min",
          "title": "01 · Úloha obrazu",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "image-web-i"
          ],
          "tasks": [
            "Urči, zda obrázek vysvětluje, dokumentuje nebo jen dekoruje.",
            "U dekorativního obrazu zvaž, zda ho vůbec potřebuješ."
          ]
        },
        {
          "id": "crop",
          "time": "15 min",
          "title": "02 · Crop pro hero",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Najdi focal point.",
            "Připrav desktop a mobile crop.",
            "Nenech text překrýt důležitý detail."
          ]
        },
        {
          "id": "export",
          "time": "15 min",
          "title": "03 · Export variant",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Připrav master a web export.",
            "Zvol rozumný rozměr podle použití."
          ],
          "knowledge": [
            "production-preflight-i"
          ]
        },
        {
          "id": "format",
          "time": "12 min",
          "title": "04 · Formát",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je nejhorší produkční návyk?",
          "options": [
            "Nahrát na web obří originál bez potřeby a bez kontroly výsledku.",
            "Připravit samostatný crop pro mobil.",
            "Otevřít export mimo editor."
          ],
          "correct": 0,
          "explanation": "Webový asset má odpovídat reálnému použití."
        },
        {
          "id": "alt",
          "time": "15 min",
          "title": "05 · Obsah vs dekorace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Napiš smysluplný alt popis pro informační obraz.",
            "U čistě dekorativního prvku označ, že nemá nést informaci."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co kontroluješ před předáním obrázku na web?",
          "options": [
            "Crop, rozměr, vizuální kvalitu, datovou přiměřenost a význam.",
            "Jen název vrstvy ve Figmě.",
            "Pouze počet barev."
          ],
          "correct": 0,
          "explanation": "Asset musí fungovat vizuálně i produkčně."
        }
      ],
      "worksheet": [
        "Vyber 1 fotografii a připrav desktop/mobile crop.",
        "Zapiš cílový rozměr exportu.",
        "Napiš alt text nebo zdůvodni dekorativní použití."
      ],
      "teacher_notes": [
        "Nepotřebujeme přesné KB limity bez kontextu. Uč datovou přiměřenost.",
        "Dovol vlastní fotografie i legální stock."
      ]
    },
    {
      "id": "gfx_forms_accessibility",
      "number": 15,
      "title": "Lekce 15 · Formuláře a přístupnost: stav musí být pochopitelný",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout jednoduchý formulář s jasnými labely, focus stavem, chybou a úspěchem bez závislosti pouze na barvě.",
      "knowledge": [
        "forms-a11y-i",
        "components-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Anatomie formuláře",
          "text": "Rozliš label, input, helper text, error a submit."
        },
        {
          "time": "15–30",
          "title": "Default + focus",
          "text": "Navrhni field default."
        },
        {
          "time": "30–45",
          "title": "Error state",
          "text": "Zobraz chybu textem i vizuálním signálem."
        },
        {
          "time": "45–60",
          "title": "Success + loading",
          "text": "Navrhni potvrzení odeslání."
        },
        {
          "time": "60–75",
          "title": "Bez barvy",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "form",
          "time": "15 min",
          "title": "01 · Anatomie formuláře",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "forms-a11y-i"
          ],
          "tasks": [
            "Rozliš label, input, helper text, error a submit.",
            "Placeholder nepoužívej jako jediný label."
          ]
        },
        {
          "id": "default",
          "time": "15 min",
          "title": "02 · Default + focus",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni field default.",
            "Přidej jasně viditelný focus stav."
          ]
        },
        {
          "id": "error",
          "time": "15 min",
          "title": "03 · Error state",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zobraz chybu textem i vizuálním signálem.",
            "Řekni uživateli, jak ji opravit."
          ]
        },
        {
          "id": "success",
          "time": "15 min",
          "title": "04 · Success + loading",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni potvrzení odeslání.",
            "Přidej loading/disabled stav tlačítka."
          ]
        },
        {
          "id": "color",
          "time": "12 min",
          "title": "05 · Bez barvy",
          "kind": "quiz",
          "xp": 25,
          "question": "Proč nestačí označit chybu jen červeným okrajem?",
          "options": [
            "Význam musí být pochopitelný i bez rozlišení barvy; pomůže text/ikona.",
            "Protože červená je zakázaná barva.",
            "Protože každý input musí být zelený."
          ],
          "correct": 0,
          "explanation": "Stav má více než jeden vizuální signál."
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je nejlepší error message?",
          "options": [
            "Konkrétní, blízko pole a říká, co má uživatel opravit.",
            "„ERROR 12“.",
            "Pouze červená hvězdička."
          ],
          "correct": 0,
          "explanation": "Dobrá chyba vede k nápravě."
        }
      ],
      "worksheet": [
        "Nakresli pole v default/focus/error/success.",
        "Napiš konkrétní chybovou hlášku.",
        "Zkontroluj, že error funguje i v grayscale."
      ],
      "teacher_notes": [
        "Používej keyboard/focus jako designový stav, ne technickou poznámku.",
        "Nehodnoť estetiku erroru výš než srozumitelnost."
      ]
    },
    {
      "id": "gfx_landing_wireframe",
      "number": 16,
      "title": "Lekce 16 · Landing page: wireframe od briefu k flow",
      "subtitle": "2 × 45 minut",
      "goal": "Převést jednoduchý brief do struktury landing page a obhájit pořadí sekcí před vizuálním stylingem.",
      "knowledge": [
        "web-layout-basics-i",
        "responsive-layout-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Brief",
          "text": "Napiš cílovou skupinu, problém, nabídku a jedinou hlavní akci."
        },
        {
          "time": "15–30",
          "title": "Content inventory",
          "text": "Sepiš obsah, který musí stránka obsahovat."
        },
        {
          "time": "30–45",
          "title": "Sekvence sekcí",
          "text": "Seřaď hero → důvěra → benefit → detail → CTA."
        },
        {
          "time": "45–60",
          "title": "Wireframe",
          "text": "Postav low-fi desktop a mobile."
        },
        {
          "time": "60–75",
          "title": "Co testuje wireframe",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "3min user walk-through",
          "text": "Nech spolužáka popsat, co by udělal jako první."
        }
      ],
      "steps": [
        {
          "id": "brief",
          "time": "15 min",
          "title": "01 · Brief",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Napiš cílovou skupinu, problém, nabídku a jedinou hlavní akci."
          ]
        },
        {
          "id": "content",
          "time": "15 min",
          "title": "02 · Content inventory",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Sepiš obsah, který musí stránka obsahovat.",
            "Odstraň duplicity a dekorativní text bez funkce."
          ]
        },
        {
          "id": "flow",
          "time": "15 min",
          "title": "03 · Sekvence sekcí",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Seřaď hero → důvěra → benefit → detail → CTA.",
            "U každé sekce napiš otázku, na kterou odpovídá."
          ]
        },
        {
          "id": "wireframe",
          "time": "15 min",
          "title": "04 · Wireframe",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Postav low-fi desktop a mobile.",
            "Nepoužívej finální fotografie ani styling."
          ]
        },
        {
          "id": "wire",
          "time": "12 min",
          "title": "05 · Co testuje wireframe",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je primární účel wireframu?",
          "options": [
            "Ověřit strukturu, prioritu a flow dříve než vizuální detaily.",
            "Vybrat finální stíny a gradienty.",
            "Exportovat hotový web."
          ],
          "correct": 0,
          "explanation": "Wireframe zlevňuje změny struktury."
        },
        {
          "id": "review",
          "time": "15 min",
          "title": "06 · 3min user walk-through",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech spolužáka popsat, co by udělal jako první.",
            "Zapiš jednu změnu podle pozorování."
          ]
        }
      ],
      "worksheet": [
        "Brief ve 4 větách.",
        "Content inventory.",
        "Pořadí 5–7 sekcí.",
        "Desktop + mobile wireframe.",
        "1 změna po peer walk-through."
      ],
      "teacher_notes": [
        "Nechte studenty nejprve testovat flow bez vizuálního wow efektu.",
        "Peer test nesmí být „líbí/nelíbí“, ale „co očekáváš, že se stane?“."
      ]
    },
    {
      "id": "gfx_ui_kit_intro",
      "number": 17,
      "title": "Lekce 17 · Mini UI kit: pravidla místo náhodných hodnot",
      "subtitle": "2 × 45 minut",
      "goal": "Sestavit malý UI kit s barvami, typografií, spacingem a 3 komponentami a použít jej v jedné obrazovce.",
      "knowledge": [
        "design-system-i",
        "components-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Malá sada tokenů",
          "text": "Definuj 1–2 text colors, background, primary action."
        },
        {
          "time": "15–30",
          "title": "Buttons",
          "text": "Vytvoř primary/secondary + stavy."
        },
        {
          "time": "30–45",
          "title": "Card + input",
          "text": "Vytvoř card a form field."
        },
        {
          "time": "45–60",
          "title": "Slož obrazovku",
          "text": "Postav jednoduchou landing/registration obrazovku jen z definovaných pravidel."
        },
        {
          "time": "60–75",
          "title": "Změna systému",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Dokumentace",
          "text": "Na jednu stránku napiš barvy, typografii, spacing a komponenty."
        }
      ],
      "steps": [
        {
          "id": "tokens",
          "time": "15 min",
          "title": "01 · Malá sada tokenů",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "design-system-i"
          ],
          "tasks": [
            "Definuj 1–2 text colors, background, primary action.",
            "Zvol spacing škálu 4/8 násobků."
          ]
        },
        {
          "id": "buttons",
          "time": "15 min",
          "title": "02 · Buttons",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vytvoř primary/secondary + stavy.",
            "Použij stejné height/padding pravidlo."
          ]
        },
        {
          "id": "cards",
          "time": "15 min",
          "title": "03 · Card + input",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vytvoř card a form field.",
            "Použij stejné radius/spacing principy."
          ]
        },
        {
          "id": "screen",
          "time": "15 min",
          "title": "04 · Slož obrazovku",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Postav jednoduchou landing/registration obrazovku jen z definovaných pravidel."
          ]
        },
        {
          "id": "token",
          "time": "12 min",
          "title": "05 · Změna systému",
          "kind": "quiz",
          "xp": 25,
          "question": "Co má nastat, když změníš primary color token?",
          "options": [
            "Relevantní komponenty se změní konzistentně.",
            "Každou komponentu musíš hledat a měnit náhodně.",
            "Změní se pouze název souboru."
          ],
          "correct": 0,
          "explanation": "Sdílené pravidlo je smysl systému."
        },
        {
          "id": "doc",
          "time": "15 min",
          "title": "06 · Dokumentace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Na jednu stránku napiš barvy, typografii, spacing a komponenty.",
            "Přidej 2 „do/don’t“ příklady."
          ]
        }
      ],
      "worksheet": [
        "1 stránka UI kit dokumentace.",
        "Barvy + type scale + spacing.",
        "Button, Card, Field se stavy.",
        "Ukázková obrazovka."
      ],
      "teacher_notes": [
        "Omez rozsah; cílem není napodobit enterprise design system.",
        "Hodnoť konzistenci a vysvětlitelnost pravidel."
      ]
    },
    {
      "id": "gfx_landing_capstone",
      "number": 18,
      "title": "Lekce 18 · Capstone: responzivní landing page",
      "subtitle": "2 × 45 minut",
      "goal": "Spojit hierarchii, typografii, obraz, responsive layout, komponenty a accessibility do finální landing page s krátkou case study.",
      "knowledge": [
        "web-layout-basics-i",
        "responsive-layout-i",
        "web-typography-i",
        "forms-a11y-i",
        "design-system-i"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Scope a success criteria",
          "text": "Vyber brief."
        },
        {
          "time": "15–30",
          "title": "Wireframe",
          "text": "Připrav mobile + desktop flow."
        },
        {
          "time": "30–45",
          "title": "Visual system",
          "text": "Použij vlastní mini UI kit."
        },
        {
          "time": "45–60",
          "title": "Responsive + states",
          "text": "Ověř 390/768/1440."
        },
        {
          "time": "60–75",
          "title": "Design QA",
          "text": "Proveď 3s test, grayscale, crop, focus a export/prezentaci."
        },
        {
          "time": "75–90",
          "title": "Obhajoba",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "plan",
          "time": "15 min",
          "title": "01 · Scope a success criteria",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vyber brief.",
            "Definuj 3 věci, které musí uživatel pochopit.",
            "Definuj hlavní CTA."
          ]
        },
        {
          "id": "wire",
          "time": "15 min",
          "title": "02 · Wireframe",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Připrav mobile + desktop flow.",
            "Neřeš detaily dříve než strukturu."
          ]
        },
        {
          "id": "visual",
          "time": "15 min",
          "title": "03 · Visual system",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Použij vlastní mini UI kit.",
            "Nastav typografii, barvy a obraz."
          ]
        },
        {
          "id": "responsive",
          "time": "15 min",
          "title": "04 · Responsive + states",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř 390/768/1440.",
            "Přidej focus/error stavy tam, kde jsou relevantní."
          ]
        },
        {
          "id": "qa",
          "time": "15 min",
          "title": "05 · Design QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Proveď 3s test, grayscale, crop, focus a export/prezentaci.",
            "Oprav největší nalezený problém."
          ]
        },
        {
          "id": "defense",
          "time": "12 min",
          "title": "06 · Obhajoba",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je nejsilnější obhajoba designového rozhodnutí?",
          "options": [
            "Cíl → pozorování/evidence → konkrétní rozhodnutí.",
            "„Protože se mi to líbí“.",
            "„Protože to bylo v šabloně“."
          ],
          "correct": 0,
          "explanation": "Case study má ukázat myšlení, ne jen finální screenshot."
        }
      ],
      "worksheet": [
        "Brief + success criteria.",
        "Mobile/Desktop wireframe.",
        "Finální návrh.",
        "Mini UI kit.",
        "5bodový QA záznam.",
        "5 vět case study."
      ],
      "teacher_notes": [
        "Použij jako semestrální checkpoint.",
        "U studentů s rychlejším tempem požaduj druhou variantu hero nebo jednoduchý prototyp."
      ]
    }
  ],
  "class_2a": [
    {
      "id": "ui_ia_flow",
      "number": 10,
      "title": "Lekce 10 · Informační architektura + user flow",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout strukturu malého webu podle úkolů uživatele a převést ji do jednoznačného user flow.",
      "knowledge": [
        "information-architecture-ii",
        "portfolio-case-study"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Obsah jako struktura",
          "text": "Sepiš obsah do skupin podle uživatelského cíle."
        },
        {
          "time": "15–30",
          "title": "Sitemap",
          "text": "Navrhni 5–8 stránek/sekcí."
        },
        {
          "time": "30–45",
          "title": "User flow",
          "text": "Nakresli cestu od vstupu k cíli."
        },
        {
          "time": "45–60",
          "title": "Navigace",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Tree test nanečisto",
          "text": "Dej spolužákovi 3 úkoly bez ukázání designu."
        },
        {
          "time": "75–90",
          "title": "Iterace",
          "text": "Uprav jednu část IA podle testu."
        }
      ],
      "steps": [
        {
          "id": "ia",
          "time": "15 min",
          "title": "01 · Obsah jako struktura",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "information-architecture-ii"
          ],
          "tasks": [
            "Sepiš obsah do skupin podle uživatelského cíle.",
            "Pojmenuj skupiny běžným jazykem."
          ]
        },
        {
          "id": "sitemap",
          "time": "15 min",
          "title": "02 · Sitemap",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni 5–8 stránek/sekcí.",
            "Omez zbytečné úrovně navigace."
          ]
        },
        {
          "id": "flow",
          "time": "15 min",
          "title": "03 · User flow",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nakresli cestu od vstupu k cíli.",
            "Přidej rozhodovací bod jen když je skutečně potřeba."
          ]
        },
        {
          "id": "nav",
          "time": "12 min",
          "title": "04 · Navigace",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je dobrý název položky navigace?",
          "options": [
            "Takový, u kterého uživatel rozumně předvídá obsah po kliknutí.",
            "Interní název databázové tabulky.",
            "Nejasný kreativní termín bez kontextu."
          ],
          "correct": 0,
          "explanation": "Navigace má snižovat nejistotu."
        },
        {
          "id": "test",
          "time": "15 min",
          "title": "05 · Tree test nanečisto",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Dej spolužákovi 3 úkoly bez ukázání designu.",
            "Zapiš, kde váhal."
          ]
        },
        {
          "id": "iterate",
          "time": "15 min",
          "title": "06 · Iterace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Uprav jednu část IA podle testu.",
            "Zapiš před/po a důvod."
          ]
        }
      ],
      "worksheet": [
        "Content inventory.",
        "Sitemap.",
        "1 user flow.",
        "3 testovací úkoly.",
        "1 iterace podle pozorování."
      ],
      "teacher_notes": [
        "Testujte strukturu bez vizuálu, aby vzhled nemaskoval problém IA.",
        "Nepředepisuj jedinou správnou sitemapu."
      ]
    },
    {
      "id": "ui_forms_states",
      "number": 11,
      "title": "Lekce 11 · Form UX: validace, chyby a stavy",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout formulář jako stavový systém včetně loading, error, success a obnovy po chybě.",
      "knowledge": [
        "form-states-ii",
        "interaction-patterns-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "State inventory",
          "text": "Sepiš default/focus/filled/error/disabled/loading/success."
        },
        {
          "time": "15–30",
          "title": "Validace",
          "text": "Rozliš preventivní nápovědu a error po odeslání."
        },
        {
          "time": "30–45",
          "title": "Loading a double submit",
          "text": "Navrhni průběh po kliknutí Submit."
        },
        {
          "time": "45–60",
          "title": "Recovery",
          "text": "Po chybě zachovej správně vyplněná data."
        },
        {
          "time": "60–75",
          "title": "Chybová zpráva",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Prototype",
          "text": "Propoj success i error větev."
        }
      ],
      "steps": [
        {
          "id": "states",
          "time": "15 min",
          "title": "01 · State inventory",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "form-states-ii"
          ],
          "tasks": [
            "Sepiš default/focus/filled/error/disabled/loading/success.",
            "Urči, které stavy patří fieldu a které celému formuláři."
          ]
        },
        {
          "id": "validation",
          "time": "15 min",
          "title": "02 · Validace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozliš preventivní nápovědu a error po odeslání.",
            "Error formuluj konkrétně."
          ]
        },
        {
          "id": "loading",
          "time": "15 min",
          "title": "03 · Loading a double submit",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni průběh po kliknutí Submit.",
            "Zabraň nejasnému opakovanému odeslání."
          ]
        },
        {
          "id": "recovery",
          "time": "15 min",
          "title": "04 · Recovery",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Po chybě zachovej správně vyplněná data.",
            "Přesuň pozornost k problému."
          ]
        },
        {
          "id": "error",
          "time": "12 min",
          "title": "05 · Chybová zpráva",
          "kind": "quiz",
          "xp": 25,
          "question": "Která zpráva pomáhá nejvíc?",
          "options": [
            "„Heslo musí mít alespoň 10 znaků.“",
            "„Error.“",
            "„Něco je špatně.“"
          ],
          "correct": 0,
          "explanation": "Konkrétní chyba umožní uživateli situaci opravit."
        },
        {
          "id": "prototype",
          "time": "15 min",
          "title": "06 · Prototype",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Propoj success i error větev.",
            "Otestuj klávesnicí základní průchod."
          ]
        }
      ],
      "worksheet": [
        "State matrix formuláře.",
        "Error texty.",
        "Success stav.",
        "Klikací prototype nebo sekvence screenshotů."
      ],
      "teacher_notes": [
        "Hodnoť zotavení po chybě, ne jen krásný default.",
        "Připomeň, že disabled bez vysvětlení může být problém."
      ]
    },
    {
      "id": "ui_autolayout_variants",
      "number": 12,
      "title": "Lekce 12 · Auto Layout, varianty a responsive komponenty",
      "subtitle": "2 × 45 minut",
      "goal": "Postavit responzivní komponentu s variantami a minimem lokálních override.",
      "knowledge": [
        "auto-layout-ii",
        "design-tokens-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Auto Layout mental model",
          "text": "Rozliš hug/fill/fixed."
        },
        {
          "time": "15–30",
          "title": "Responsive button",
          "text": "Text změň na delší variantu."
        },
        {
          "time": "30–45",
          "title": "Card variants",
          "text": "Vytvoř compact/default variantu."
        },
        {
          "time": "45–60",
          "title": "Nested layout",
          "text": "Sestav kartu z menších komponent."
        },
        {
          "time": "60–75",
          "title": "Override",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Stress test",
          "text": "Použij dlouhý text, malý viewport a chybějící obrázek."
        }
      ],
      "steps": [
        {
          "id": "auto",
          "time": "15 min",
          "title": "01 · Auto Layout mental model",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "auto-layout-ii"
          ],
          "tasks": [
            "Rozliš hug/fill/fixed.",
            "Pochop padding vs gap."
          ]
        },
        {
          "id": "button",
          "time": "15 min",
          "title": "02 · Responsive button",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Text změň na delší variantu.",
            "Komponenta se nesmí rozpadnout."
          ]
        },
        {
          "id": "card",
          "time": "15 min",
          "title": "03 · Card variants",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vytvoř compact/default variantu.",
            "Napoj stejné tokeny."
          ]
        },
        {
          "id": "nested",
          "time": "15 min",
          "title": "04 · Nested layout",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Sestav kartu z menších komponent.",
            "Omez absolutní pozicování."
          ]
        },
        {
          "id": "override",
          "time": "12 min",
          "title": "05 · Override",
          "kind": "quiz",
          "xp": 25,
          "question": "Kdy je lokální override varovný signál?",
          "options": [
            "Když stejnou výjimku opakuješ na mnoha instancích a měla by být systémovým pravidlem.",
            "Když změníš text instance.",
            "Když komponentu pojmenuješ."
          ],
          "correct": 0,
          "explanation": "Opakovaná výjimka často znamená chybějící variantu nebo token."
        },
        {
          "id": "stress",
          "time": "15 min",
          "title": "06 · Stress test",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Použij dlouhý text, malý viewport a chybějící obrázek.",
            "Zapiš 2 opravy."
          ]
        }
      ],
      "worksheet": [
        "Komponenta Button + Card.",
        "Varianty.",
        "Stress test se 3 extrémními vstupy."
      ],
      "teacher_notes": [
        "Neomezuj výuku na Figma mechaniku; student má vysvětlit pravidlo layoutu.",
        "Vyžaduj názvy variant podle významu."
      ]
    },
    {
      "id": "ui_accessibility_audit",
      "number": 13,
      "title": "Lekce 13 · Accessibility audit webového návrhu",
      "subtitle": "2 × 45 minut",
      "goal": "Provést strukturovaný audit kontrastu, focusu, pořadí, targetů, textů a stavů a opravit prioritní problémy.",
      "knowledge": [
        "accessibility-audit-ii",
        "accessibility-design"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Audit checklist",
          "text": "Zkontroluj kontrast, focus, texty, stavy a velikost ovládacích prvků."
        },
        {
          "time": "15–30",
          "title": "Keyboard flow",
          "text": "Seřaď očekávané pořadí focusu."
        },
        {
          "time": "30–45",
          "title": "Zoom a reflow",
          "text": "Ověř, co se stane při větším textu/užším viewportu."
        },
        {
          "time": "45–60",
          "title": "Form errors",
          "text": "Ověř labely a chybové stavy."
        },
        {
          "time": "60–75",
          "title": "Accessibility",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Prioritized fixes",
          "text": "Seřaď nálezy High/Medium/Low."
        }
      ],
      "steps": [
        {
          "id": "audit",
          "time": "15 min",
          "title": "01 · Audit checklist",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "accessibility-audit-ii"
          ],
          "tasks": [
            "Zkontroluj kontrast, focus, texty, stavy a velikost ovládacích prvků.",
            "Nespoléhej jen na barvu."
          ]
        },
        {
          "id": "keyboard",
          "time": "15 min",
          "title": "02 · Keyboard flow",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Seřaď očekávané pořadí focusu.",
            "Navrhni focus state, který není překrytý."
          ]
        },
        {
          "id": "zoom",
          "time": "15 min",
          "title": "03 · Zoom a reflow",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř, co se stane při větším textu/užším viewportu.",
            "Najdi clipping nebo horizontální overflow."
          ]
        },
        {
          "id": "errors",
          "time": "15 min",
          "title": "04 · Form errors",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř labely a chybové stavy.",
            "Každá chyba má textovou nápravu."
          ]
        },
        {
          "id": "a11y",
          "time": "12 min",
          "title": "05 · Accessibility",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je správný přístup?",
          "options": [
            "Accessibility řešit od návrhu komponent a flow, ne až jako poslední kontrolu.",
            "Přidat accessibility po exportu.",
            "Stačí zvýšit saturaci barev."
          ],
          "correct": 0,
          "explanation": "Přístupnost je součást funkční kvality produktu."
        },
        {
          "id": "fix",
          "time": "15 min",
          "title": "06 · Prioritized fixes",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Seřaď nálezy High/Medium/Low.",
            "Oprav 2 největší bariéry a dokumentuj změnu."
          ]
        }
      ],
      "worksheet": [
        "Audit 8 bodů.",
        "Prioritizace nálezů.",
        "Before/after 2 oprav."
      ],
      "teacher_notes": [
        "Učitel nemusí známkovat studenty za zapamatování čísel; důležitější je systematický audit a náprava.",
        "Používej reálný prototyp studentů."
      ]
    },
    {
      "id": "ui_handoff_html_css",
      "number": 14,
      "title": "Lekce 14 · Design → HTML/CSS: handoff, který jde implementovat",
      "subtitle": "2 × 45 minut",
      "goal": "Převést designové rozhodnutí do implementační specifikace: struktura, tokeny, komponenty a responsive pravidla.",
      "knowledge": [
        "html-css-handoff-ii",
        "design-tokens-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Design vs DOM struktura",
          "text": "Rozděl obrazovku na header/main/sections/footer."
        },
        {
          "time": "15–30",
          "title": "Token handoff",
          "text": "Zapiš color/spacing/type tokeny."
        },
        {
          "time": "30–45",
          "title": "Responsive rules",
          "text": "Popiš, kdy se layout skládá pod sebe."
        },
        {
          "time": "45–60",
          "title": "States specification",
          "text": "Sepiš hover/focus/loading/error/disabled."
        },
        {
          "time": "60–75",
          "title": "Handoff",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Developer review",
          "text": "Nech spolužáka podle handoffu popsat implementaci."
        }
      ],
      "steps": [
        {
          "id": "structure",
          "time": "15 min",
          "title": "01 · Design vs DOM struktura",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "html-css-handoff-ii"
          ],
          "tasks": [
            "Rozděl obrazovku na header/main/sections/footer.",
            "Pojmenuj komponenty podle funkce."
          ]
        },
        {
          "id": "tokens",
          "time": "15 min",
          "title": "02 · Token handoff",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zapiš color/spacing/type tokeny.",
            "Vyhni se seznamu 40 náhodných pixel hodnot."
          ]
        },
        {
          "id": "responsive",
          "time": "15 min",
          "title": "03 · Responsive rules",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Popiš, kdy se layout skládá pod sebe.",
            "Uveď max-width a chování obrázku."
          ]
        },
        {
          "id": "states",
          "time": "15 min",
          "title": "04 · States specification",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Sepiš hover/focus/loading/error/disabled.",
            "Doplň keyboard poznámky."
          ]
        },
        {
          "id": "handoff",
          "time": "12 min",
          "title": "05 · Handoff",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je slabý handoff?",
          "options": [
            "Jeden screenshot bez stavů, rozměrových pravidel a chování.",
            "Tokeny + komponenty + state matrix.",
            "Responsive anotace."
          ],
          "correct": 0,
          "explanation": "Implementace potřebuje pravidla, ne pouze vzhled jednoho viewportu."
        },
        {
          "id": "devreview",
          "time": "15 min",
          "title": "06 · Developer review",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech spolužáka podle handoffu popsat implementaci.",
            "Oprav 2 nejasnosti."
          ]
        }
      ],
      "worksheet": [
        "1 stránka handoff specifikace.",
        "Tokeny.",
        "Responsive pravidla.",
        "State matrix.",
        "2 opravy po review."
      ],
      "teacher_notes": [
        "Nemusí se psát plný kód. Cílem je spojit design a implementační myšlení.",
        "U technicky silnějších studentů lze přidat jednoduchou HTML/CSS realizaci."
      ]
    },
    {
      "id": "ui_css_layout",
      "number": 15,
      "title": "Lekce 15 · CSS layout mindset: Flex, Grid a responsive pravidla",
      "subtitle": "2 × 45 minut",
      "goal": "Pochopit, jak se návrhové vztahy mapují do Flexbox/Grid modelu, a navrhovat proveditelné responsive rozložení.",
      "knowledge": [
        "css-responsive-ii",
        "html-css-handoff-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Flex vs Grid",
          "text": "Flex použij pro jednorozměrné uspořádání."
        },
        {
          "time": "15–30",
          "title": "Hero jako layout rule",
          "text": "Popiš desktop dvě kolony."
        },
        {
          "time": "30–45",
          "title": "Card grid",
          "text": "Navrhni grid, který se přirozeně mění 3→2→1."
        },
        {
          "time": "45–60",
          "title": "Responsive",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "DevTools/inspection mindset",
          "text": "U existující stránky identifikuj container, gap, max-width a layout směr."
        },
        {
          "time": "75–90",
          "title": "Implementation notes",
          "text": "Ke 3 komponentám napiš layout model a breakpoint behavior."
        }
      ],
      "steps": [
        {
          "id": "flexgrid",
          "time": "15 min",
          "title": "01 · Flex vs Grid",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "css-responsive-ii"
          ],
          "tasks": [
            "Flex použij pro jednorozměrné uspořádání.",
            "Grid pro dvourozměrné vztahy a oblasti."
          ]
        },
        {
          "id": "hero",
          "time": "15 min",
          "title": "02 · Hero jako layout rule",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Popiš desktop dvě kolony.",
            "Na mobilu změň flow na jeden sloupec."
          ]
        },
        {
          "id": "cards",
          "time": "15 min",
          "title": "03 · Card grid",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni grid, který se přirozeně mění 3→2→1.",
            "Nedrž pevnou šířku, která způsobí overflow."
          ]
        },
        {
          "id": "breakpoint",
          "time": "12 min",
          "title": "04 · Responsive",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je lepší než navrhovat pouze „desktop a mobil“?",
          "options": [
            "Definovat chování komponent mezi šířkami a testovat, kdy se obsah začne lámat.",
            "Ignorovat tablet.",
            "Použít obrázek celé stránky."
          ],
          "correct": 0,
          "explanation": "Responsive je kontinuální chování, ne dvě fotografie."
        },
        {
          "id": "inspect",
          "time": "15 min",
          "title": "05 · DevTools/inspection mindset",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "U existující stránky identifikuj container, gap, max-width a layout směr.",
            "Neměň produkční web."
          ]
        },
        {
          "id": "spec",
          "time": "15 min",
          "title": "06 · Implementation notes",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ke 3 komponentám napiš layout model a breakpoint behavior."
          ]
        }
      ],
      "worksheet": [
        "Flex/Grid rozhodnutí pro 3 části stránky.",
        "Breakpoint behavior.",
        "Card grid 3→2→1."
      ],
      "teacher_notes": [
        "Výklad může být bez rozsáhlého programování; důležitý je převod vztahů do layout modelu.",
        "Pokud umí HTML/CSS, dovol mini implementaci."
      ]
    },
    {
      "id": "ui_usability_test",
      "number": 16,
      "title": "Lekce 16 · Usability test: pozorování místo dojmologie",
      "subtitle": "2 × 45 minut",
      "goal": "Připravit krátký usability test, pozorovat chování bez navádění a prioritizovat zjištění podle dopadu.",
      "knowledge": [
        "usability-test-ii",
        "design-critique-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Test plan",
          "text": "Definuj 3 realistické úkoly."
        },
        {
          "time": "15–30",
          "title": "Moderace",
          "text": "Nedoplňuj nápovědu příliš brzy."
        },
        {
          "time": "30–45",
          "title": "Evidence notes",
          "text": "Rozliš „kliknul třikrát zpět“ od „navigace je špatná“."
        },
        {
          "time": "45–60",
          "title": "Prioritizace",
          "text": "Ohodnoť četnost × dopad."
        },
        {
          "time": "60–75",
          "title": "Navádění",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Redesign",
          "text": "Proveď 1–3 změny."
        }
      ],
      "steps": [
        {
          "id": "plan",
          "time": "15 min",
          "title": "01 · Test plan",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "usability-test-ii"
          ],
          "tasks": [
            "Definuj 3 realistické úkoly.",
            "Nepopisuj přesnou cestu k cíli."
          ]
        },
        {
          "id": "observe",
          "time": "15 min",
          "title": "02 · Moderace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nedoplňuj nápovědu příliš brzy.",
            "Zapisuj pozorování, ne interpretaci."
          ]
        },
        {
          "id": "notes",
          "time": "15 min",
          "title": "03 · Evidence notes",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozliš „kliknul třikrát zpět“ od „navigace je špatná“."
          ]
        },
        {
          "id": "severity",
          "time": "15 min",
          "title": "04 · Prioritizace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ohodnoť četnost × dopad.",
            "Vyber maximálně 3 problémy k redesignu."
          ]
        },
        {
          "id": "leading",
          "time": "12 min",
          "title": "05 · Navádění",
          "kind": "quiz",
          "xp": 25,
          "question": "Která věta je při testu nejlepší?",
          "options": [
            "„Co byste teď udělal/a?“",
            "„Klikněte na modré tlačítko vpravo.“",
            "„Vidíte, že menu je tady?“"
          ],
          "correct": 0,
          "explanation": "Moderátor má zjišťovat mentální model, ne učit řešení."
        },
        {
          "id": "iterate",
          "time": "15 min",
          "title": "06 · Redesign",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Proveď 1–3 změny.",
            "U každé napiš problém → evidence → změna."
          ]
        }
      ],
      "worksheet": [
        "Test plan 3 úkolů.",
        "Pozorovací záznam.",
        "Seznam problémů + severity.",
        "Before/after redesign."
      ],
      "teacher_notes": [
        "Stačí 1–3 testující ve třídě pro nácvik metody; nejde o reprezentativní výzkum.",
        "Vyžaduj oddělení pozorování od interpretace."
      ]
    },
    {
      "id": "ui_design_qa",
      "number": 17,
      "title": "Lekce 17 · Design QA: porovnej implementaci s pravidly, ne s pixely",
      "subtitle": "2 × 45 minut",
      "goal": "Provést design QA realizace a rozlišit funkční odchylku, accessibility problém a přijatelnou implementační variaci.",
      "knowledge": [
        "design-qa-handoff-ii",
        "accessibility-audit-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Design QA mindset",
          "text": "Kontroluj hierarchii, spacing, komponenty, states a responsive behavior."
        },
        {
          "time": "15–30",
          "title": "Compare",
          "text": "Porovnej design a implementaci ve 3 viewports."
        },
        {
          "time": "30–45",
          "title": "Severity",
          "text": "Critical = brání úkolu; High = zásadní funkční/a11y problém; Low = kosmetika."
        },
        {
          "time": "45–60",
          "title": "QA issue",
          "text": "Napiš steps, expected, actual a screenshot/reference."
        },
        {
          "time": "60–75",
          "title": "Priorita",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Verify fix",
          "text": "Po opravě zopakuj původní scénář."
        }
      ],
      "steps": [
        {
          "id": "qa",
          "time": "15 min",
          "title": "01 · Design QA mindset",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "design-qa-handoff-ii"
          ],
          "tasks": [
            "Kontroluj hierarchii, spacing, komponenty, states a responsive behavior.",
            "Neřeš 1px rozdíl před rozbitou funkcí."
          ]
        },
        {
          "id": "compare",
          "time": "15 min",
          "title": "02 · Compare",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Porovnej design a implementaci ve 3 viewports.",
            "Sepiš pouze ověřitelné rozdíly."
          ]
        },
        {
          "id": "severity",
          "time": "15 min",
          "title": "03 · Severity",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Critical = brání úkolu; High = zásadní funkční/a11y problém; Low = kosmetika."
          ]
        },
        {
          "id": "issue",
          "time": "15 min",
          "title": "04 · QA issue",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Napiš steps, expected, actual a screenshot/reference.",
            "Přiřaď severity."
          ]
        },
        {
          "id": "priority",
          "time": "12 min",
          "title": "05 · Priorita",
          "kind": "quiz",
          "xp": 25,
          "question": "Co opravovat první?",
          "options": [
            "Problém, který blokuje úkol nebo zásadně porušuje použitelnost/přístupnost.",
            "Nejmenší rozdíl stínu.",
            "Pořadí názvů vrstev ve Figmě."
          ],
          "correct": 0,
          "explanation": "QA řadí podle dopadu."
        },
        {
          "id": "verify",
          "time": "15 min",
          "title": "06 · Verify fix",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Po opravě zopakuj původní scénář.",
            "Issue uzavři až po ověření."
          ]
        }
      ],
      "worksheet": [
        "3 viewport compare.",
        "5 QA issues max.",
        "Severity + expected/actual.",
        "Verification poznámka."
      ],
      "teacher_notes": [
        "QA učí spolupráci Designer–Developer.",
        "Nepodporuj blame culture; issue popisuje stav produktu."
      ]
    },
    {
      "id": "ui_product_capstone",
      "number": 18,
      "title": "Lekce 18 · Capstone: produktová landing page + case study",
      "subtitle": "2 × 45 minut",
      "goal": "Spojit IA, responsive UI, komponenty, accessibility, testování a handoff do jednoho obhajitelného produktového návrhu.",
      "knowledge": [
        "information-architecture-ii",
        "auto-layout-ii",
        "accessibility-audit-ii",
        "usability-test-ii",
        "design-qa-handoff-ii"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Product brief",
          "text": "Definuj uživatele, problém, cíl stránky a success signal."
        },
        {
          "time": "15–30",
          "title": "IA + wireframe",
          "text": "Sitemap/sekvence."
        },
        {
          "time": "30–45",
          "title": "UI system",
          "text": "Tokeny, komponenty a stavy."
        },
        {
          "time": "45–60",
          "title": "Usability + accessibility",
          "text": "Proveď krátký test."
        },
        {
          "time": "60–75",
          "title": "Iterace + handoff",
          "text": "Zapracuj 2–4 podložené změny."
        },
        {
          "time": "75–90",
          "title": "Case study",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "brief",
          "time": "15 min",
          "title": "01 · Product brief",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Definuj uživatele, problém, cíl stránky a success signal."
          ]
        },
        {
          "id": "flow",
          "time": "15 min",
          "title": "02 · IA + wireframe",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Sitemap/sekvence.",
            "Mobile + desktop wireframe."
          ]
        },
        {
          "id": "system",
          "time": "15 min",
          "title": "03 · UI system",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Tokeny, komponenty a stavy.",
            "Responzivní pravidla."
          ]
        },
        {
          "id": "test",
          "time": "15 min",
          "title": "04 · Usability + accessibility",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Proveď krátký test.",
            "Proveď accessibility audit.",
            "Prioritizuj nálezy."
          ]
        },
        {
          "id": "iterate",
          "time": "15 min",
          "title": "05 · Iterace + handoff",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zapracuj 2–4 podložené změny.",
            "Připrav implementační handoff."
          ]
        },
        {
          "id": "case",
          "time": "12 min",
          "title": "06 · Case study",
          "kind": "quiz",
          "xp": 25,
          "question": "Co má kvalitní case study ukázat?",
          "options": [
            "Problém, proces, evidence, rozhodnutí, výsledek a reflexi.",
            "Jen galerii finálních screenshotů.",
            "Pouze použitý software."
          ],
          "correct": 0,
          "explanation": "Case study dokazuje způsob uvažování."
        }
      ],
      "worksheet": [
        "Brief.",
        "IA/user flow.",
        "Wireframes.",
        "Design system mini-spec.",
        "Responsive screens.",
        "Test evidence.",
        "Accessibility audit.",
        "Handoff.",
        "Case study."
      ],
      "teacher_notes": [
        "Vhodné jako závěrečný projekt 2.A.",
        "U týmové varianty použij Project Workspace role Designer/Researcher/Developer/QA/Presenter."
      ]
    }
  ],
  "class_3a": [
    {
      "id": "os_linux_cli",
      "number": 10,
      "title": "Lekce 10 · Linux CLI + filesystem: orientace bez klikání",
      "subtitle": "2 × 45 minut",
      "goal": "Orientovat se v Linux filesystemu, používat pwd/ls/cd/cp/mv/rm bezpečně a vysvětlit rozdíl absolutní a relativní cesty.",
      "knowledge": [
        "linux-filesystem"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Filesystem mapa",
          "text": "Rozliš /home, /etc, /var, /tmp a /usr."
        },
        {
          "time": "15–30",
          "title": "Navigace",
          "text": "Použij pwd, ls -la, cd."
        },
        {
          "time": "30–45",
          "title": "Práce se soubory",
          "text": "Vytvoř adresář lab."
        },
        {
          "time": "45–60",
          "title": "Cesty",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Inspect before change",
          "text": "Použij file/stat/cat nebo head podle typu dat."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "fs",
          "time": "15 min",
          "title": "01 · Filesystem mapa",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "linux-filesystem"
          ],
          "tasks": [
            "Rozliš /home, /etc, /var, /tmp a /usr.",
            "Najdi domovský adresář uživatele."
          ]
        },
        {
          "id": "nav",
          "time": "15 min",
          "title": "02 · Navigace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Použij pwd, ls -la, cd.",
            "Vyzkoušej relativní i absolutní cestu."
          ]
        },
        {
          "id": "files",
          "time": "15 min",
          "title": "03 · Práce se soubory",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vytvoř adresář lab.",
            "Zkopíruj a přesuň testovací soubor.",
            "Před rm ověř pwd a cestu."
          ]
        },
        {
          "id": "path",
          "time": "12 min",
          "title": "04 · Cesty",
          "kind": "quiz",
          "xp": 25,
          "question": "Která cesta začínající / je absolutní?",
          "options": [
            "/var/log/nginx/error.log",
            "../logs/error.log",
            "./error.log"
          ],
          "correct": 0,
          "explanation": "Absolutní cesta začíná od root filesystemu."
        },
        {
          "id": "inspect",
          "time": "15 min",
          "title": "05 · Inspect before change",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Použij file/stat/cat nebo head podle typu dat.",
            "Needituj konfiguraci naslepo."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Proč je před destruktivním příkazem důležité ověřit pwd a argument?",
          "options": [
            "Chyba v cestě může zasáhnout jiná data než zamýšlíš.",
            "Protože pwd restartuje shell.",
            "Kvůli DNS."
          ],
          "correct": 0,
          "explanation": "Bezpečná práce v CLI stojí na kontrole scope změny."
        }
      ],
      "worksheet": [
        "Napiš význam 5 adresářů.",
        "5 příkazů + co vrací.",
        "Příklad absolutní/relativní cesty.",
        "Bezpečnostní pravidlo před rm."
      ],
      "teacher_notes": [
        "Používej sandbox/testovací adresář.",
        "Nedávej destruktivní příklady nad skutečnými systémovými cestami."
      ]
    },
    {
      "id": "os_users_permissions",
      "number": 11,
      "title": "Lekce 11 · Uživatelé, skupiny a oprávnění",
      "subtitle": "2 × 45 minut",
      "goal": "Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.",
      "knowledge": [
        "users-permissions"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Owner / Group / Other",
          "text": "Přečti ls -l."
        },
        {
          "time": "15–30",
          "title": "rwx",
          "text": "Převeď rw-r----- na význam."
        },
        {
          "time": "30–45",
          "title": "Skupinový přístup",
          "text": "Navrhni skupinu webteam."
        },
        {
          "time": "45–60",
          "title": "Least privilege",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Vlastnictví",
          "text": "Vysvětli rozdíl chmod a chown."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "15 min",
          "title": "01 · Owner / Group / Other",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "users-permissions"
          ],
          "tasks": [
            "Přečti ls -l.",
            "Rozliš owner, group a ostatní."
          ]
        },
        {
          "id": "mode",
          "time": "15 min",
          "title": "02 · rwx",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Převeď rw-r----- na význam.",
            "Navrhni oprávnění pro konfigurační soubor s tajným obsahem."
          ]
        },
        {
          "id": "group",
          "time": "15 min",
          "title": "03 · Skupinový přístup",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni skupinu webteam.",
            "Odděl read-only a write potřebu."
          ]
        },
        {
          "id": "least",
          "time": "12 min",
          "title": "04 · Least privilege",
          "kind": "quiz",
          "xp": 25,
          "question": "Které oprávnění je bezpečnější pro soubor, který má číst vlastník i skupina, ale měnit jen vlastník?",
          "options": [
            "640",
            "777",
            "666"
          ],
          "correct": 0,
          "explanation": "640 dává owner rw, group r a others nic."
        },
        {
          "id": "ownership",
          "time": "15 min",
          "title": "05 · Vlastnictví",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vysvětli rozdíl chmod a chown.",
            "Uveď, kdy použít skupinu místo world-write."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Proč není chmod 777 univerzální oprava?",
          "options": [
            "Dává všem zbytečně široká práva a maskuje skutečný model přístupu.",
            "Protože zakazuje čtení.",
            "Protože mění IP adresu."
          ],
          "correct": 0,
          "explanation": "Oprávnění se mají odvíjet od potřebných rolí."
        }
      ],
      "worksheet": [
        "Rozepiš 3 permission strings.",
        "Navrhni mode pro config/log/shared file.",
        "Vysvětli chmod vs chown."
      ],
      "teacher_notes": [
        "Používej model „kdo potřebuje co dělat“.",
        "Propoj s budoucím SSH a webserverem."
      ]
    },
    {
      "id": "os_processes_systemd",
      "number": 12,
      "title": "Lekce 12 · Procesy + systemd: služba není magie",
      "subtitle": "2 × 45 minut",
      "goal": "Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.",
      "knowledge": [
        "processes-systemd",
        "journal-logs"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Proces a služba",
          "text": "Použij ps/top nebo ekvivalent."
        },
        {
          "time": "15–30",
          "title": "systemctl status",
          "text": "Přečti Active, Main PID a poslední log řádky."
        },
        {
          "time": "30–45",
          "title": "start/restart/reload",
          "text": "Vysvětli rozdíl restart a reload."
        },
        {
          "time": "45–60",
          "title": "Enable vs start",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Failed service",
          "text": "Z statusu najdi první konkrétní chybu."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "process",
          "time": "15 min",
          "title": "01 · Proces a služba",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "processes-systemd"
          ],
          "tasks": [
            "Použij ps/top nebo ekvivalent.",
            "Rozliš PID a service unit."
          ]
        },
        {
          "id": "status",
          "time": "15 min",
          "title": "02 · systemctl status",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Přečti Active, Main PID a poslední log řádky.",
            "Nezačínej restartem."
          ]
        },
        {
          "id": "actions",
          "time": "15 min",
          "title": "03 · start/restart/reload",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vysvětli rozdíl restart a reload.",
            "Před reloadem ověř konfiguraci, pokud služba umí syntax check."
          ]
        },
        {
          "id": "enable",
          "time": "12 min",
          "title": "04 · Enable vs start",
          "kind": "quiz",
          "xp": 25,
          "question": "Co dělá enable typicky?",
          "options": [
            "Nastaví automatické spuštění služby při odpovídajícím boot targetu; nemusí ji právě teď spustit.",
            "Vymaže logy služby.",
            "Otevře firewall port."
          ],
          "correct": 0,
          "explanation": "Enable a runtime start jsou dvě odlišné věci."
        },
        {
          "id": "fail",
          "time": "15 min",
          "title": "05 · Failed service",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Z statusu najdi první konkrétní chybu.",
            "Navrhni nejmenší další test."
          ],
          "knowledge": [
            "journal-logs"
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Proč je „restartuj to“ slabá první diagnostika?",
          "options": [
            "Mění stav dřív, než získáš evidence, a může skrýt příčinu.",
            "Restart je vždy pomalý.",
            "Restart funguje jen na Windows."
          ],
          "correct": 0,
          "explanation": "Diagnostika má nejprve pozorovat a lokalizovat problém."
        }
      ],
      "worksheet": [
        "Process vs service.",
        "systemctl status – 5 polí k přečtení.",
        "Restart vs reload vs enable.",
        "Incident: služba failed – další test."
      ],
      "teacher_notes": [
        "Doporuč bezpečný lokální lab/VM.",
        "Připomínej syntax check před reloadem webserveru."
      ]
    },
    {
      "id": "os_logs_journal",
      "number": 13,
      "title": "Lekce 13 · Logy a journalctl: časová osa důkazů",
      "subtitle": "2 × 45 minut",
      "goal": "Použít logy jako cílený zdroj evidence podle služby, času a severity místo čtení tisíců řádků.",
      "knowledge": [
        "journal-logs"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Co je užitečný log",
          "text": "Najdi timestamp, source/service, severity a message."
        },
        {
          "time": "15–30",
          "title": "Filtruj",
          "text": "Omez log na konkrétní unit/službu."
        },
        {
          "time": "30–45",
          "title": "Sleduj změnu",
          "text": "Spusť follow/tail jen při reprodukci testovacího problému."
        },
        {
          "time": "45–60",
          "title": "Noise",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Korelace",
          "text": "Propoj service status + journal + port test."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "log",
          "time": "15 min",
          "title": "01 · Co je užitečný log",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "journal-logs"
          ],
          "tasks": [
            "Najdi timestamp, source/service, severity a message.",
            "Odliš symptom od root cause."
          ]
        },
        {
          "id": "filter",
          "time": "15 min",
          "title": "02 · Filtruj",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Omez log na konkrétní unit/službu.",
            "Omez časové okno kolem incidentu."
          ]
        },
        {
          "id": "follow",
          "time": "15 min",
          "title": "03 · Sleduj změnu",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Spusť follow/tail jen při reprodukci testovacího problému.",
            "Zapiš změnu před/po."
          ]
        },
        {
          "id": "noise",
          "time": "12 min",
          "title": "04 · Noise",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je nejlepší při dlouhém logu?",
          "options": [
            "Začít incidentním časem a konkrétní službou.",
            "Číst celý log od začátku systému.",
            "Vymazat log a čekat."
          ],
          "correct": 0,
          "explanation": "Filtr snižuje šum a chrání kauzalitu."
        },
        {
          "id": "correlate",
          "time": "15 min",
          "title": "05 · Korelace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Propoj service status + journal + port test.",
            "Napiš jednu hypotézu, kterou evidence vylučuje."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co znamená, že log neobsahuje chybu?",
          "options": [
            "Samo o sobě to nedokazuje, že systém je v pořádku; možná sleduješ špatnou vrstvu nebo zdroj.",
            "Služba je určitě zdravá.",
            "Firewall je vypnutý."
          ],
          "correct": 0,
          "explanation": "Absence záznamu je slabší evidence než cílený pozitivní test."
        }
      ],
      "worksheet": [
        "Incident timestamp.",
        "3 filtry logu.",
        "Hypotéza potvrzena/vyloučena."
      ],
      "teacher_notes": [
        "Učte časovou korelaci napříč logy.",
        "Student má vysvětlit, proč konkrétní filtr používá."
      ]
    },
    {
      "id": "os_ssh_keys",
      "number": 14,
      "title": "Lekce 14 · SSH/SFTP: bezpečný vzdálený přístup",
      "subtitle": "2 × 45 minut",
      "goal": "Nastavit a ověřit SSH klíčové přihlášení, rozlišit autentizaci od síťové dostupnosti a použít SFTP bezpečně.",
      "knowledge": [
        "ssh-keys-ops",
        "ssh-sftp"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Cesta SSH spojení",
          "text": "Ověř DNS/IP, TCP/22 a teprve potom autentizaci."
        },
        {
          "time": "15–30",
          "title": "Klíče",
          "text": "Rozliš private/public key."
        },
        {
          "time": "30–45",
          "title": "Permissions",
          "text": "Ověř bezpečné oprávnění privátního klíče."
        },
        {
          "time": "45–60",
          "title": "Permission denied",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "SFTP",
          "text": "Přeneste testovací soubor."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "path",
          "time": "15 min",
          "title": "01 · Cesta SSH spojení",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "ssh-keys-ops"
          ],
          "tasks": [
            "Ověř DNS/IP, TCP/22 a teprve potom autentizaci.",
            "Rozliš timeout a Permission denied."
          ]
        },
        {
          "id": "keys",
          "time": "15 min",
          "title": "02 · Klíče",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozliš private/public key.",
            "Private key nesdílej ani neukládej do projektu."
          ]
        },
        {
          "id": "permissions",
          "time": "15 min",
          "title": "03 · Permissions",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř bezpečné oprávnění privátního klíče.",
            "Zkontroluj authorized_keys vlastníka."
          ]
        },
        {
          "id": "deny",
          "time": "12 min",
          "title": "04 · Permission denied",
          "kind": "quiz",
          "xp": 25,
          "question": "TCP/22 funguje, ale SSH vrací Permission denied. Kde je nejsilnější hypotéza?",
          "options": [
            "Autentizace/uživatel/klíč, ne základní síťová dostupnost.",
            "DHCP server.",
            "Monitor počítače."
          ],
          "correct": 0,
          "explanation": "Aplikace už odpověděla, takže síťová cesta k SSH existuje."
        },
        {
          "id": "sftp",
          "time": "15 min",
          "title": "05 · SFTP",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Přeneste testovací soubor.",
            "Ověř cílovou cestu a práva."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Který soubor je tajný?",
          "options": [
            "Private key.",
            "Public key.",
            "known_hosts bez dalšího kontextu."
          ],
          "correct": 0,
          "explanation": "Private key chrání identitu a nesmí se sdílet."
        }
      ],
      "worksheet": [
        "SSH diagnostická cesta.",
        "Private vs public key.",
        "3 příčiny Permission denied.",
        "SFTP transfer check."
      ],
      "teacher_notes": [
        "Nikdy nevyžaduj reálné studentské private keys do odevzdání.",
        "Pracuj s testovacími klíči/VM."
      ]
    },
    {
      "id": "os_firewall_services",
      "number": 15,
      "title": "Lekce 15 · Firewall + služby: co poslouchá a kdo se tam dostane",
      "subtitle": "2 × 45 minut",
      "goal": "Propojit listener, bind adresu, firewall a client test do jednoho diagnostického modelu.",
      "knowledge": [
        "linux-firewall",
        "ports"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Listener ≠ dostupnost",
          "text": "Rozliš proces poslouchá / bind address / firewall / routa."
        },
        {
          "time": "15–30",
          "title": "ss/lsof mindset",
          "text": "Zjisti port a bind adresu."
        },
        {
          "time": "30–45",
          "title": "Firewall rule",
          "text": "Povol jen potřebný source/service."
        },
        {
          "time": "45–60",
          "title": "Bind",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Positive + negative test",
          "text": "Ověř povolenou cestu."
        },
        {
          "time": "75–90",
          "title": "Exit ticket",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "layers",
          "time": "15 min",
          "title": "01 · Listener ≠ dostupnost",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "linux-firewall"
          ],
          "tasks": [
            "Rozliš proces poslouchá / bind address / firewall / routa.",
            "Sepiš service matrix."
          ]
        },
        {
          "id": "listen",
          "time": "15 min",
          "title": "02 · ss/lsof mindset",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zjisti port a bind adresu.",
            "Rozliš 127.0.0.1 vs 0.0.0.0 vs konkrétní IP."
          ]
        },
        {
          "id": "rule",
          "time": "15 min",
          "title": "03 · Firewall rule",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Povol jen potřebný source/service.",
            "Nevytvářej any-any pravidlo."
          ]
        },
        {
          "id": "localhost",
          "time": "12 min",
          "title": "04 · Bind",
          "kind": "quiz",
          "xp": 25,
          "question": "Služba poslouchá pouze 127.0.0.1:8080. Co očekáváš z jiného stroje?",
          "options": [
            "Nebude přímo dostupná přes síťové rozhraní, i kdyby firewall port dovoloval.",
            "Bude vždy dostupná.",
            "DNS se automaticky změní."
          ],
          "correct": 0,
          "explanation": "Loopback bind omezuje listener na lokální host."
        },
        {
          "id": "validate",
          "time": "15 min",
          "title": "05 · Positive + negative test",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř povolenou cestu.",
            "Ověř, že zakázaná cesta stále nefunguje."
          ]
        },
        {
          "id": "exit",
          "time": "12 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je správná firewall validace?",
          "options": [
            "Otestovat povolený i zakázaný scénář z relevantního zdroje.",
            "Otestovat jen ping.",
            "Zkontrolovat pouze syntaxi pravidla."
          ],
          "correct": 0,
          "explanation": "Bez negativního testu nevíš, zda jsi neotevřel příliš mnoho."
        }
      ],
      "worksheet": [
        "Service matrix 4 řádky.",
        "Listener/bind evidence.",
        "Firewall allow + deny test."
      ],
      "teacher_notes": [
        "Používej sandbox pravidla; nedělej změny na produkčním školním serveru.",
        "Důraz na rollback/console access v reálné správě."
      ]
    },
    {
      "id": "os_web_service",
      "number": 16,
      "title": "Lekce 16 · Linux webová služba: od procesu k HTTP",
      "subtitle": "2 × 45 minut",
      "goal": "Nasadit nebo analyzovat jednoduchou webovou službu a ověřit process → listener → firewall → DNS → HTTP.",
      "knowledge": [
        "web-service-linux",
        "dns"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Service chain",
          "text": "Proces běží."
        },
        {
          "time": "15–30",
          "title": "Local health",
          "text": "Otestuj localhost/health nebo lokální curl."
        },
        {
          "time": "30–45",
          "title": "Remote path",
          "text": "Otestuj službu z klienta."
        },
        {
          "time": "45–60",
          "title": "HTTP evidence",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "DNS name",
          "text": "Namapuj testovací jméno nebo interpretuj záznam."
        },
        {
          "time": "75–90",
          "title": "Runbook",
          "text": "Napiš 5 kroků ověření služby od lokálního procesu po klienta."
        }
      ],
      "steps": [
        {
          "id": "chain",
          "time": "15 min",
          "title": "01 · Service chain",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "web-service-linux"
          ],
          "tasks": [
            "Proces běží.",
            "Listener existuje.",
            "Síťová cesta.",
            "DNS.",
            "HTTP odpověď."
          ]
        },
        {
          "id": "local",
          "time": "15 min",
          "title": "02 · Local health",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Otestuj localhost/health nebo lokální curl.",
            "Zapiš HTTP status."
          ]
        },
        {
          "id": "remote",
          "time": "15 min",
          "title": "03 · Remote path",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Otestuj službu z klienta.",
            "Při rozdílu local/remote lokalizuj bind/firewall/routing."
          ]
        },
        {
          "id": "502",
          "time": "12 min",
          "title": "04 · HTTP evidence",
          "kind": "quiz",
          "xp": 25,
          "question": "Když reverse proxy vrátí 502, co to dokazuje?",
          "options": [
            "Proxy je dosažitelná, ale má problém komunikovat s upstreamem nebo dostat validní odpověď.",
            "DNS vždy selhalo.",
            "Klient nemá IP."
          ],
          "correct": 0,
          "explanation": "HTTP 502 vzniká na proxy/gateway vrstvě."
        },
        {
          "id": "dns",
          "time": "15 min",
          "title": "05 · DNS name",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Namapuj testovací jméno nebo interpretuj záznam.",
            "Ověř, že jméno míří na správný endpoint."
          ]
        },
        {
          "id": "doc",
          "time": "15 min",
          "title": "06 · Runbook",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Napiš 5 kroků ověření služby od lokálního procesu po klienta."
          ]
        }
      ],
      "worksheet": [
        "Service chain diagram.",
        "Local/remote test.",
        "HTTP status evidence.",
        "5krokový runbook."
      ],
      "teacher_notes": [
        "Může být simulované prostředí, Docker/VM nebo předpřipravený lab.",
        "Nezáviset na veřejném DNS."
      ]
    },
    {
      "id": "os_shell_cron",
      "number": 17,
      "title": "Lekce 17 · Shell + cron: malá automatizace s logem a bezpečným selháním",
      "subtitle": "2 × 45 minut",
      "goal": "Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.",
      "knowledge": [
        "shell-cron"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Skript jako opakovatelný postup",
          "text": "Použij jasný vstup, výstup a exit status."
        },
        {
          "time": "15–30",
          "title": "Guard clauses",
          "text": "Před změnou ověř podmínky."
        },
        {
          "time": "30–45",
          "title": "Log output",
          "text": "Přidej timestamp a výsledek."
        },
        {
          "time": "45–60",
          "title": "Cron/system timer",
          "text": "Naplánuj testovací úlohu."
        },
        {
          "time": "60–75",
          "title": "Cron problém",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Ověření",
          "text": "Prokaž, že job proběhl."
        }
      ],
      "steps": [
        {
          "id": "script",
          "time": "15 min",
          "title": "01 · Skript jako opakovatelný postup",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "shell-cron"
          ],
          "tasks": [
            "Použij jasný vstup, výstup a exit status.",
            "Neukládej hesla přímo do skriptu."
          ]
        },
        {
          "id": "safe",
          "time": "15 min",
          "title": "02 · Guard clauses",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Před změnou ověř podmínky.",
            "Při chybě skonči s nenulovým exit code."
          ]
        },
        {
          "id": "log",
          "time": "15 min",
          "title": "03 · Log output",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Přidej timestamp a výsledek.",
            "Rozliš stdout/stderr konceptuálně."
          ]
        },
        {
          "id": "schedule",
          "time": "15 min",
          "title": "04 · Cron/system timer",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Naplánuj testovací úlohu.",
            "Zohledni pracovní adresář a PATH."
          ]
        },
        {
          "id": "cron",
          "time": "12 min",
          "title": "05 · Cron problém",
          "kind": "quiz",
          "xp": 25,
          "question": "Skript funguje ručně, ale ne z cronu. Co ověřit mezi prvními?",
          "options": [
            "PATH, pracovní adresář, uživatele a logovaný stderr.",
            "Barvu terminálu.",
            "DNS TTL webu."
          ],
          "correct": 0,
          "explanation": "Naplánované prostředí se může lišit od interaktivního shellu."
        },
        {
          "id": "verify",
          "time": "15 min",
          "title": "06 · Ověření",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Prokaž, že job proběhl.",
            "Ověř výsledek, ne jen existenci schedule."
          ]
        }
      ],
      "worksheet": [
        "Pseudo/real script 8–20 řádků.",
        "Guard condition.",
        "Log sample.",
        "Schedule.",
        "Verification evidence."
      ],
      "teacher_notes": [
        "Používej neškodné úlohy (např. health report, kopie test souboru).",
        "Neautomatizujte destruktivní příkazy."
      ]
    },
    {
      "id": "os_net_capstone",
      "number": 18,
      "title": "Lekce 18 · Capstone: nefunguje služba – Linux + síť v jednom incidentu",
      "subtitle": "2 × 45 minut",
      "goal": "Systematicky vyřešit kombinovaný incident od klienta přes DNS/síť/firewall až po systemd službu a doložit root cause.",
      "knowledge": [
        "linux-filesystem",
        "processes-systemd",
        "journal-logs",
        "ssh-keys-ops",
        "linux-firewall",
        "web-service-linux"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Symptom + scope",
          "text": "Zapiš co nefunguje, komu a od kdy."
        },
        {
          "time": "15–30",
          "title": "Síťová evidence",
          "text": "IP/gateway/DNS."
        },
        {
          "time": "30–45",
          "title": "Server evidence",
          "text": "Service status."
        },
        {
          "time": "45–60",
          "title": "Nejmenší oprava",
          "text": "Změň jen potvrzenou příčinu."
        },
        {
          "time": "60–75",
          "title": "Validace",
          "text": "Ověř původní user path."
        },
        {
          "time": "75–90",
          "title": "Incident note",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "triage",
          "time": "15 min",
          "title": "01 · Symptom + scope",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zapiš co nefunguje, komu a od kdy.",
            "Odděl client vs server symptom."
          ]
        },
        {
          "id": "network",
          "time": "15 min",
          "title": "02 · Síťová evidence",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "IP/gateway/DNS.",
            "Port test.",
            "Poznamenej první selhávající vrstvu."
          ]
        },
        {
          "id": "server",
          "time": "15 min",
          "title": "03 · Server evidence",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Service status.",
            "Listener.",
            "Logy kolem incidentu."
          ]
        },
        {
          "id": "fix",
          "time": "15 min",
          "title": "04 · Nejmenší oprava",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Změň jen potvrzenou příčinu.",
            "Připrav rollback."
          ]
        },
        {
          "id": "validate",
          "time": "15 min",
          "title": "05 · Validace",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř původní user path.",
            "Ověř službu lokálně.",
            "Ověř, že security policy zůstala zachována."
          ]
        },
        {
          "id": "post",
          "time": "12 min",
          "title": "06 · Incident note",
          "kind": "quiz",
          "xp": 25,
          "question": "Co patří do stručného incident záznamu?",
          "options": [
            "Symptom, evidence, root cause, změna a validační důkaz.",
            "Jen „restart hotov“.",
            "Seznam všech příkazů bez závěru."
          ],
          "correct": 0,
          "explanation": "Záznam má umožnit pochopit rozhodnutí a ověřit opravu."
        }
      ],
      "worksheet": [
        "Incident timeline.",
        "Hypotézy a testy.",
        "Root cause.",
        "Oprava + rollback.",
        "3 validační důkazy."
      ],
      "teacher_notes": [
        "Vhodné jako mastery/capstone 3.A.",
        "Hodnoť diagnostický proces, ne rychlost náhodného nalezení chyby."
      ]
    }
  ],
  "class_4a": [
    {
      "id": "ops_systemd_dependencies",
      "number": 10,
      "title": "Lekce 10 · systemd do hloubky: dependencies, restart policy, failure",
      "subtitle": "2 × 45 minut",
      "goal": "Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.",
      "knowledge": [
        "systemd-advanced",
        "logs-monitoring"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Unit anatomy",
          "text": "Rozliš Unit/Service/Install."
        },
        {
          "time": "15–30",
          "title": "Dependencies",
          "text": "Rozliš After/Wants/Requires konceptuálně."
        },
        {
          "time": "30–45",
          "title": "Restart loop",
          "text": "Z logu zjisti proč proces padá."
        },
        {
          "time": "45–60",
          "title": "Ordering",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Drop-in/override mindset",
          "text": "Navrhni minimální override místo kopie celého unit souboru."
        },
        {
          "time": "75–90",
          "title": "Validate",
          "text": "daemon-reload konceptuálně."
        }
      ],
      "steps": [
        {
          "id": "unit",
          "time": "15 min",
          "title": "01 · Unit anatomy",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "systemd-advanced"
          ],
          "tasks": [
            "Rozliš Unit/Service/Install.",
            "Najdi ExecStart, User a Restart."
          ]
        },
        {
          "id": "deps",
          "time": "15 min",
          "title": "02 · Dependencies",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozliš After/Wants/Requires konceptuálně.",
            "Nakresli dependency chain služby."
          ]
        },
        {
          "id": "failure",
          "time": "15 min",
          "title": "03 · Restart loop",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Z logu zjisti proč proces padá.",
            "Nezvyšuj restart aggressiveness před opravou příčiny."
          ]
        },
        {
          "id": "after",
          "time": "12 min",
          "title": "04 · Ordering",
          "kind": "quiz",
          "xp": 25,
          "question": "Co typicky vyjadřuje After=?",
          "options": [
            "Pořadí startu vůči jiné unit; samo o sobě nemusí vytvářet tvrdou závislost.",
            "Firewall allow rule.",
            "DNS priority."
          ],
          "correct": 0,
          "explanation": "Ordering a dependency nejsou totéž."
        },
        {
          "id": "override",
          "time": "15 min",
          "title": "05 · Drop-in/override mindset",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni minimální override místo kopie celého unit souboru.",
            "Zapiš rollback."
          ]
        },
        {
          "id": "validate",
          "time": "15 min",
          "title": "06 · Validate",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "daemon-reload konceptuálně.",
            "Status + log + health check po změně."
          ]
        }
      ],
      "worksheet": [
        "Unit anatomy.",
        "Dependency diagram.",
        "Failure evidence.",
        "Override + rollback."
      ],
      "teacher_notes": [
        "Používej testovací unit.",
        "Důraz na evidence před restartem."
      ]
    },
    {
      "id": "ops_storage_filesystems",
      "number": 11,
      "title": "Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident",
      "subtitle": "2 × 45 minut",
      "goal": "Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.",
      "knowledge": [
        "storage-filesystems"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Storage layers",
          "text": "Device → partition/LV → filesystem → mount point."
        },
        {
          "time": "15–30",
          "title": "df/du mindset",
          "text": "Zjisti, který filesystem je plný."
        },
        {
          "time": "30–45",
          "title": "Log growth",
          "text": "Najdi podezřelý růst logu."
        },
        {
          "time": "45–60",
          "title": "Inodes",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Mount validation",
          "text": "Ověř správný mount point po reboot scénáři."
        },
        {
          "time": "75–90",
          "title": "Prevence",
          "text": "Navrhni monitoring kapacity + retention."
        }
      ],
      "steps": [
        {
          "id": "layers",
          "time": "15 min",
          "title": "01 · Storage layers",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "storage-filesystems"
          ],
          "tasks": [
            "Device → partition/LV → filesystem → mount point.",
            "Rozliš kapacitu a inode."
          ]
        },
        {
          "id": "measure",
          "time": "15 min",
          "title": "02 · df/du mindset",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zjisti, který filesystem je plný.",
            "Teprve potom hledej velké adresáře."
          ]
        },
        {
          "id": "logs",
          "time": "15 min",
          "title": "03 · Log growth",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Najdi podezřelý růst logu.",
            "Nevymaž náhodně aktivní log bez pochopení služby."
          ]
        },
        {
          "id": "inode",
          "time": "12 min",
          "title": "04 · Inodes",
          "kind": "quiz",
          "xp": 25,
          "question": "Filesystem hlásí volné GB, ale nelze vytvořit soubor. Co může být problém?",
          "options": [
            "Vyčerpané inodes / příliš mnoho souborů.",
            "DNS cache.",
            "TLS SAN."
          ],
          "correct": 0,
          "explanation": "Kapacita v bajtech není jediný limit filesystemu."
        },
        {
          "id": "mount",
          "time": "15 min",
          "title": "05 · Mount validation",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř správný mount point po reboot scénáři.",
            "Zapiš bezpečný recovery postup."
          ]
        },
        {
          "id": "prevent",
          "time": "15 min",
          "title": "06 · Prevence",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni monitoring kapacity + retention.",
            "Definuj threshold a akci."
          ]
        }
      ],
      "worksheet": [
        "Storage diagram.",
        "df vs du vs inode vysvětlení.",
        "Incident disk full – 5 kroků.",
        "Preventivní monitoring."
      ],
      "teacher_notes": [
        "Nedávej studentům mazat skutečné systémové logy.",
        "Pracujte s připravenou strukturou dat."
      ]
    },
    {
      "id": "ops_backup_restore",
      "number": 12,
      "title": "Lekce 12 · Backup strategie: RPO/RTO a restore drill",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.",
      "knowledge": [
        "backup-strategy",
        "backup-restore"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "RPO/RTO",
          "text": "RPO = kolik dat smíš ztratit."
        },
        {
          "time": "15–30",
          "title": "Co zálohovat",
          "text": "Data, konfigurace, metadata/secrets odděleně podle rizika."
        },
        {
          "time": "30–45",
          "title": "Restore drill",
          "text": "Obnov do testovacího cíle."
        },
        {
          "time": "45–60",
          "title": "Backup vs restore",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Integrity + app validation",
          "text": "Ověř soubory/databázi podle typu."
        },
        {
          "time": "75–90",
          "title": "Runbook",
          "text": "Napiš restore kroky, odpovědnosti a stop conditions."
        }
      ],
      "steps": [
        {
          "id": "rpo",
          "time": "15 min",
          "title": "01 · RPO/RTO",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "backup-strategy"
          ],
          "tasks": [
            "RPO = kolik dat smíš ztratit.",
            "RTO = jak dlouho může trvat obnova."
          ]
        },
        {
          "id": "strategy",
          "time": "15 min",
          "title": "02 · Co zálohovat",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Data, konfigurace, metadata/secrets odděleně podle rizika.",
            "Definuj retention."
          ]
        },
        {
          "id": "restore",
          "time": "15 min",
          "title": "03 · Restore drill",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Obnov do testovacího cíle.",
            "Nenič produkční data během testu."
          ]
        },
        {
          "id": "backup",
          "time": "12 min",
          "title": "04 · Backup vs restore",
          "kind": "quiz",
          "xp": 25,
          "question": "Kdy je backup skutečně důvěryhodný?",
          "options": [
            "Když byl obnoven a výsledek ověřen.",
            "Když job skončil zelenou ikonou.",
            "Když je soubor velký."
          ],
          "correct": 0,
          "explanation": "Bez restore testu neznáš reálnou obnovitelnost."
        },
        {
          "id": "integrity",
          "time": "15 min",
          "title": "05 · Integrity + app validation",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř soubory/databázi podle typu.",
            "Spusť aplikační health test."
          ]
        },
        {
          "id": "runbook",
          "time": "15 min",
          "title": "06 · Runbook",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Napiš restore kroky, odpovědnosti a stop conditions."
          ]
        }
      ],
      "worksheet": [
        "RPO/RTO.",
        "Backup matrix.",
        "Restore evidence.",
        "Runbook."
      ],
      "teacher_notes": [
        "Vhodné pro simulovaný dataset.",
        "Uč rozdíl backup success vs business recovery."
      ]
    },
    {
      "id": "ops_hardening",
      "number": 13,
      "title": "Lekce 13 · Hardening Linux služby: SSH, firewall, aktualizace a minimální práva",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout bezpečné minimum služby bez „security by checkbox“ a současně zachovat ověřitelný přístup a rollback.",
      "knowledge": [
        "ssh-hardening",
        "backup-strategy"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Attack surface",
          "text": "Sepiš vystavené služby."
        },
        {
          "time": "15–30",
          "title": "SSH baseline",
          "text": "Klíče, omezené účty a bezpečná práva."
        },
        {
          "time": "30–45",
          "title": "Firewall scope",
          "text": "Povol management pouze z potřebné zóny/IP rozsahu."
        },
        {
          "time": "45–60",
          "title": "Patch plan",
          "text": "Zjisti dopad aktualizace."
        },
        {
          "time": "60–75",
          "title": "Lockout risk",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Security validation",
          "text": "Pozitivní test oprávněného přístupu."
        }
      ],
      "steps": [
        {
          "id": "surface",
          "time": "15 min",
          "title": "01 · Attack surface",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "ssh-hardening"
          ],
          "tasks": [
            "Sepiš vystavené služby.",
            "Odstraň/omez nepotřebné cesty přístupu."
          ]
        },
        {
          "id": "ssh",
          "time": "15 min",
          "title": "02 · SSH baseline",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Klíče, omezené účty a bezpečná práva.",
            "Nesdílej privátní klíče."
          ]
        },
        {
          "id": "fw",
          "time": "15 min",
          "title": "03 · Firewall scope",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Povol management pouze z potřebné zóny/IP rozsahu.",
            "Připrav console/rollback cestu."
          ]
        },
        {
          "id": "patch",
          "time": "15 min",
          "title": "04 · Patch plan",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zjisti dopad aktualizace.",
            "Definuj restart potřebu a validační test."
          ]
        },
        {
          "id": "lockout",
          "time": "12 min",
          "title": "05 · Lockout risk",
          "kind": "quiz",
          "xp": 25,
          "question": "Co musíš řešit před zpřísněním vzdáleného SSH/firewall přístupu?",
          "options": [
            "Ověřený alternativní přístup/rollback, aby ses nezamkl venku.",
            "Jen barvu promptu.",
            "TTL webového obrázku."
          ],
          "correct": 0,
          "explanation": "Hardening bez recovery plánu může vytvořit vlastní incident."
        },
        {
          "id": "verify",
          "time": "15 min",
          "title": "06 · Security validation",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Pozitivní test oprávněného přístupu.",
            "Negativní test zakázané cesty.",
            "Audit log změny."
          ]
        }
      ],
      "worksheet": [
        "Attack surface list.",
        "SSH baseline.",
        "Firewall scope.",
        "Patch+rollback plán.",
        "Positive/negative validation."
      ],
      "teacher_notes": [
        "Defenzivní lab; nepracovat s cizími systémy.",
        "Důraz na minimální scope a recovery."
      ]
    },
    {
      "id": "ops_containers",
      "number": 14,
      "title": "Lekce 14 · Containers: proces, image, volume a síť",
      "subtitle": "2 × 45 minut",
      "goal": "Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.",
      "knowledge": [
        "containers-basics",
        "binding"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Container mental model",
          "text": "Image ≠ container."
        },
        {
          "time": "15–30",
          "title": "Port mapping",
          "text": "Rozliš container port a host port."
        },
        {
          "time": "30–45",
          "title": "Persistent data",
          "text": "Urči, která data musí přežít nový container."
        },
        {
          "time": "45–60",
          "title": "Recreate",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Health + logs",
          "text": "Ověř status/health."
        },
        {
          "time": "75–90",
          "title": "Incident",
          "text": "Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "15 min",
          "title": "01 · Container mental model",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "containers-basics"
          ],
          "tasks": [
            "Image ≠ container.",
            "Volume ≠ image layer.",
            "Port publish ≠ aplikace automaticky poslouchá."
          ]
        },
        {
          "id": "ports",
          "time": "15 min",
          "title": "02 · Port mapping",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozliš container port a host port.",
            "Ověř bind/listener uvnitř služby."
          ]
        },
        {
          "id": "volume",
          "time": "15 min",
          "title": "03 · Persistent data",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Urči, která data musí přežít nový container.",
            "Neukládej secrets do image."
          ]
        },
        {
          "id": "restart",
          "time": "12 min",
          "title": "04 · Recreate",
          "kind": "quiz",
          "xp": 25,
          "question": "Co se typicky stane s daty uloženými jen ve writable layer containeru po jeho odstranění/recreate?",
          "options": [
            "Mohou být ztracena; persistentní data patří do vhodného volume/storage.",
            "Automaticky se přesunou do DNS.",
            "Vždy se uloží do image registry."
          ],
          "correct": 0,
          "explanation": "Ephemeral runtime a persistent storage jsou oddělené koncepty."
        },
        {
          "id": "health",
          "time": "15 min",
          "title": "05 · Health + logs",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř status/health.",
            "Přečti aplikační log před změnou."
          ]
        },
        {
          "id": "incident",
          "time": "15 min",
          "title": "06 · Incident",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu.",
            "Navrhni nejmenší opravu + validaci."
          ]
        }
      ],
      "worksheet": [
        "Image/container/volume/network diagram.",
        "Port mapping.",
        "Persistent data decision.",
        "Container incident evidence."
      ],
      "teacher_notes": [
        "Lze realizovat v Dockeru/Podmanu nebo čistě simulovat.",
        "Nezaváděj orchestraci dřív, než studenti chápou jednu instanci."
      ]
    },
    {
      "id": "ops_automation_consistency",
      "number": 15,
      "title": "Lekce 15 · Automatizace + configuration consistency",
      "subtitle": "2 × 45 minut",
      "goal": "Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.",
      "knowledge": [
        "automation-shell",
        "config-drift"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Idempotentní změna",
          "text": "Nejdřív zjisti current state."
        },
        {
          "time": "15–30",
          "title": "Preconditions",
          "text": "Ověř host, config a backup/rollback."
        },
        {
          "time": "30–45",
          "title": "Drift",
          "text": "Porovnej deklarovaný a skutečný stav."
        },
        {
          "time": "45–60",
          "title": "Automatizace",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Dry-run/plan",
          "text": "Vygeneruj plán změn."
        },
        {
          "time": "75–90",
          "title": "Apply + validate",
          "text": "Po změně změř desired outcome."
        }
      ],
      "steps": [
        {
          "id": "idempotent",
          "time": "15 min",
          "title": "01 · Idempotentní změna",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "automation-shell"
          ],
          "tasks": [
            "Nejdřív zjisti current state.",
            "Pokud je desired state už splněn, nic zbytečně neměň."
          ]
        },
        {
          "id": "guard",
          "time": "15 min",
          "title": "02 · Preconditions",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř host, config a backup/rollback.",
            "Při nesplněné podmínce bezpečně skonči."
          ]
        },
        {
          "id": "drift",
          "time": "15 min",
          "title": "03 · Drift",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Porovnej deklarovaný a skutečný stav.",
            "Rozhodni, který je source of truth."
          ]
        },
        {
          "id": "script",
          "time": "12 min",
          "title": "04 · Automatizace",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je horší než ruční postup?",
          "options": [
            "Automatizace, která rychle a opakovaně provádí chybnou změnu bez guardů.",
            "Skript s dry-runem.",
            "Validace po změně."
          ],
          "correct": 0,
          "explanation": "Automatizace násobí dobré i špatné rozhodnutí."
        },
        {
          "id": "dry",
          "time": "15 min",
          "title": "05 · Dry-run/plan",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Vygeneruj plán změn.",
            "Zkontroluj scope před apply."
          ]
        },
        {
          "id": "evidence",
          "time": "15 min",
          "title": "06 · Apply + validate",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Po změně změř desired outcome.",
            "Zapiš idempotentní druhý průchod bez změny."
          ]
        }
      ],
      "worksheet": [
        "Current vs desired state.",
        "Preconditions.",
        "Pseudo-script.",
        "Dry-run output.",
        "Validation."
      ],
      "teacher_notes": [
        "Zadání drž defenzivní a v sandboxu.",
        "Hodnoť safe failure a idempotenci."
      ]
    },
    {
      "id": "ops_packet_diagnostics",
      "number": 16,
      "title": "Lekce 16 · Pokročilá síťová diagnostika: packet evidence + socket state",
      "subtitle": "2 × 45 minut",
      "goal": "Propojit packet capture, TCP stavy a serverový listener do jedné incidentní hypotézy.",
      "knowledge": [
        "packet-diagnostics-advanced",
        "tcp"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Capture with question",
          "text": "Formuluj hypotézu před filtrem."
        },
        {
          "time": "15–30",
          "title": "SYN patterns",
          "text": "Rozliš timeout, RST a SYN-ACK."
        },
        {
          "time": "30–45",
          "title": "Server socket",
          "text": "Porovnej capture s ss/lsof."
        },
        {
          "time": "45–60",
          "title": "RST",
          "text": ""
        },
        {
          "time": "60–75",
          "title": "Po TCP",
          "text": "Když TCP funguje, pokračuj TLS/HTTP podle symptomu."
        },
        {
          "time": "75–90",
          "title": "Incident timeline",
          "text": "Seřaď packet + server evidence podle času."
        }
      ],
      "steps": [
        {
          "id": "capture",
          "time": "15 min",
          "title": "01 · Capture with question",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "packet-diagnostics-advanced"
          ],
          "tasks": [
            "Formuluj hypotézu před filtrem.",
            "Zachyť jen potřebný provoz."
          ]
        },
        {
          "id": "syn",
          "time": "15 min",
          "title": "02 · SYN patterns",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozliš timeout, RST a SYN-ACK.",
            "Propoj s firewallem/listenerem."
          ]
        },
        {
          "id": "server",
          "time": "15 min",
          "title": "03 · Server socket",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Porovnej capture s ss/lsof.",
            "Ověř správný bind."
          ]
        },
        {
          "id": "rst",
          "time": "12 min",
          "title": "04 · RST",
          "kind": "quiz",
          "xp": 25,
          "question": "SYN → okamžitý RST typicky znamená?",
          "options": [
            "Cíl je dosažitelný, ale port/spojení je aktivně odmítnuté.",
            "DNS dotaz se nikdy neposlal.",
            "Klient nemá MAC adresu gateway."
          ],
          "correct": 0,
          "explanation": "RST je explicitní TCP odpověď."
        },
        {
          "id": "tls",
          "time": "15 min",
          "title": "05 · Po TCP",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Když TCP funguje, pokračuj TLS/HTTP podle symptomu.",
            "Neskákej zpět k DHCP bez evidence."
          ]
        },
        {
          "id": "timeline",
          "time": "15 min",
          "title": "06 · Incident timeline",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Seřaď packet + server evidence podle času.",
            "Napiš root-cause boundary: co víš a co ještě ne."
          ]
        }
      ],
      "worksheet": [
        "Hypotéza.",
        "Packet pattern.",
        "Socket evidence.",
        "Layer boundary.",
        "Root cause nebo další test."
      ],
      "teacher_notes": [
        "Používej předpřipravené pcapy nebo izolovaný lab.",
        "Nezachytávej citlivý provoz třetích osob."
      ]
    },
    {
      "id": "ops_incident_runbook",
      "number": 17,
      "title": "Lekce 17 · Incident response: runbook, komunikace a blameless postmortem",
      "subtitle": "2 × 45 minut",
      "goal": "Řídit incident podle severity, rolí, timeline, mitigation a následného postmortemu bez chaosu a hledání viníka.",
      "knowledge": [
        "incident-runbook",
        "slo-postmortem"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Incident roles",
          "text": "Incident lead, investigator, communicator."
        },
        {
          "time": "15–30",
          "title": "Severity + impact",
          "text": "Popiš uživatelský dopad."
        },
        {
          "time": "30–45",
          "title": "Mitigation first",
          "text": "Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby."
        },
        {
          "time": "45–60",
          "title": "Timeline",
          "text": "Zapisuj fakta s časem."
        },
        {
          "time": "60–75",
          "title": "Postmortem",
          "text": ""
        },
        {
          "time": "75–90",
          "title": "Follow-up",
          "text": "Každá akce má ownera, prioritu a ověřitelný výsledek."
        }
      ],
      "steps": [
        {
          "id": "roles",
          "time": "15 min",
          "title": "01 · Incident roles",
          "kind": "knowledge",
          "xp": 30,
          "knowledge": [
            "incident-runbook"
          ],
          "tasks": [
            "Incident lead, investigator, communicator.",
            "Odděl koordinaci od hluboké diagnostiky."
          ]
        },
        {
          "id": "severity",
          "time": "15 min",
          "title": "02 · Severity + impact",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Popiš uživatelský dopad.",
            "Nastav prioritu podle dopadu, ne technické zajímavosti."
          ]
        },
        {
          "id": "mitigate",
          "time": "15 min",
          "title": "03 · Mitigation first",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby.",
            "Root cause může pokračovat po stabilizaci."
          ]
        },
        {
          "id": "timeline",
          "time": "15 min",
          "title": "04 · Timeline",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Zapisuj fakta s časem.",
            "Odděl fakta a hypotézy."
          ]
        },
        {
          "id": "blame",
          "time": "12 min",
          "title": "05 · Postmortem",
          "kind": "quiz",
          "xp": 25,
          "question": "Co je cílem blameless postmortemu?",
          "options": [
            "Pochopit systémové faktory a definovat konkrétní preventivní akce.",
            "Najít jednoho člověka k potrestání.",
            "Vyhnout se technickým detailům."
          ],
          "correct": 0,
          "explanation": "Postmortem má zlepšit systém a proces."
        },
        {
          "id": "actions",
          "time": "15 min",
          "title": "06 · Follow-up",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Každá akce má ownera, prioritu a ověřitelný výsledek.",
            "Vyber 2 nejdůležitější."
          ]
        }
      ],
      "worksheet": [
        "Severity/impact.",
        "Role assignment.",
        "Timeline.",
        "Mitigation.",
        "Postmortem: 2 follow-up actions."
      ],
      "teacher_notes": [
        "Použij Project Workspace role Leader/Developer/QA/Presenter pro týmový incident.",
        "Hodnoť rozhodování a komunikaci stejně jako techniku."
      ]
    },
    {
      "id": "ops_reliability_capstone",
      "number": 18,
      "title": "Lekce 18 · Capstone: produkční reliability drill",
      "subtitle": "2 × 45 minut",
      "goal": "Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.",
      "knowledge": [
        "systemd-advanced",
        "storage-filesystems",
        "backup-strategy",
        "ssh-hardening",
        "containers-basics",
        "automation-shell",
        "packet-diagnostics-advanced",
        "incident-runbook"
      ],
      "schedule": [
        {
          "time": "0–15",
          "title": "Triage",
          "text": "Urči impact/severity."
        },
        {
          "time": "15–30",
          "title": "Evidence matrix",
          "text": "DNS/TCP/TLS/HTTP."
        },
        {
          "time": "30–45",
          "title": "Mitigation",
          "text": "Rozhodni rollback/fix/failover podle evidence."
        },
        {
          "time": "45–60",
          "title": "Recovery validation",
          "text": "Ověř user path, health, error rate a security boundary."
        },
        {
          "time": "60–75",
          "title": "Prevent repeat",
          "text": "Navrhni guard/monitor/automation, který problém zachytí nebo omezí."
        },
        {
          "time": "75–90",
          "title": "Postmortem defense",
          "text": ""
        }
      ],
      "steps": [
        {
          "id": "triage",
          "time": "15 min",
          "title": "01 · Triage",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Urči impact/severity.",
            "Zmraz riskantní změny.",
            "Rozděl role týmu."
          ]
        },
        {
          "id": "evidence",
          "time": "15 min",
          "title": "02 · Evidence matrix",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "DNS/TCP/TLS/HTTP.",
            "Service/containers/logs/storage.",
            "Vyber nejmenší test pro každou hypotézu."
          ]
        },
        {
          "id": "mitigation",
          "time": "15 min",
          "title": "03 · Mitigation",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Rozhodni rollback/fix/failover podle evidence.",
            "Zapiš stop condition."
          ]
        },
        {
          "id": "recovery",
          "time": "15 min",
          "title": "04 · Recovery validation",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Ověř user path, health, error rate a security boundary.",
            "Při obnově dat proveď integrity check."
          ]
        },
        {
          "id": "automation",
          "time": "15 min",
          "title": "05 · Prevent repeat",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Navrhni guard/monitor/automation, který problém zachytí nebo omezí.",
            "Neautomatizuj neověřený fix."
          ]
        },
        {
          "id": "final",
          "time": "12 min",
          "title": "06 · Postmortem defense",
          "kind": "quiz",
          "xp": 25,
          "question": "Co nejlépe dokazuje zvládnutí capstone?",
          "options": [
            "Konzistentní evidence chain, bezpečná obnova, validace a konkrétní preventivní kroky.",
            "Co nejvíc spuštěných příkazů.",
            "Jedna správná náhodná změna."
          ],
          "correct": 0,
          "explanation": "Reliability je opakovatelný rozhodovací proces."
        }
      ],
      "worksheet": [
        "Incident timeline.",
        "Evidence matrix.",
        "Mitigation + rollback.",
        "Validation matrix.",
        "Postmortem.",
        "2 preventive actions."
      ],
      "teacher_notes": [
        "Závěrečný týmový drill 4.A.",
        "Učitel může během scénáře injectovat další symptom, ale musí zachovat řešitelnost z evidence."
      ]
    }
  ]
}
JSON, true, 512, JSON_THROW_ON_ERROR);
