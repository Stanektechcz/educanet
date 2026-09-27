<?php

declare(strict_types=1);

return json_decode(<<<'JSON'
{
  "class_1a": {
    "web-layout-basics-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Header pomáhá orientaci, hero rychle vysvětluje hodnotu "
          ],
          [
            "Krok 2",
            "Nezačínej dekoracemi. Nejprve určuj priority obsahu a vz"
          ],
          [
            "Krok 3",
            "Dobrá stránka zůstává pochopitelná i jako jednoduchý čer"
          ]
        ]
      },
      "mental": "Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.",
      "steps": [
        "Header pomáhá orientaci, hero rychle vysvětluje hodnotu a hlavní obsah vede uživatele k cíli.",
        "Nezačínej dekoracemi. Nejprve určuj priority obsahu a vztahy mezi bloky.",
        "Dobrá stránka zůstává pochopitelná i jako jednoduchý černobílý wireframe.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Anatomie webové stránky“?",
        "options": [
          "Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Header pomáhá orientaci, hero rychle vysvětluje hodnotu a hlavní obsah vede uživatele k cíli."
      }
    },
    "responsive-layout-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Mobile není zmenšený desktop. Často potřebuje jiné pořad"
          ],
          [
            "Krok 2",
            "Breakpoint dává smysl ve chvíli, kdy obsah nebo komponen"
          ],
          [
            "Krok 3",
            "Testuj také mezilehlé šířky, nejen dvě předem připravené"
          ]
        ]
      },
      "mental": "Responzivní návrh zachovává informační prioritu a přeskupuje obsah podle dostupného prostoru.",
      "steps": [
        "Mobile není zmenšený desktop. Často potřebuje jiné pořadí, kratší headline nebo změnu kolony na stack.",
        "Breakpoint dává smysl ve chvíli, kdy obsah nebo komponenta přestává fungovat.",
        "Testuj také mezilehlé šířky, nejen dvě předem připravené obrazovky.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Responsive layout I“?",
        "options": [
          "Responzivní návrh zachovává informační prioritu a přeskupuje obsah podle dostupného prostoru.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Mobile není zmenšený desktop. Často potřebuje jiné pořadí, kratší headline nebo změnu kolony na stack."
      }
    },
    "components-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Tlačítko má roli, variantu a stavy. Karta má jasnou vnit"
          ],
          [
            "Krok 2",
            "Konzistence neznamená, že vše vypadá stejně; znamená, že"
          ],
          [
            "Krok 3",
            "Stav focus, disabled nebo error je stejně důležitý jako "
          ]
        ]
      },
      "mental": "Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.",
      "steps": [
        "Tlačítko má roli, variantu a stavy. Karta má jasnou vnitřní strukturu a opakovatelný spacing.",
        "Konzistence neznamená, že vše vypadá stejně; znamená, že podobné věci používají stejná pravidla.",
        "Stav focus, disabled nebo error je stejně důležitý jako default.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Komponenty I: opakovatelné UI vzory“?",
        "options": [
          "Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Tlačítko má roli, variantu a stavy. Karta má jasnou vnitřní strukturu a opakovatelný spacing."
      }
    },
    "web-typography-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Používej malý počet pojmenovaných textových rolí."
          ],
          [
            "Krok 2",
            "Čitelnost ovlivňuje velikost, line-height, délka řádku i"
          ],
          [
            "Krok 3",
            "Headline musí být testovaný i na mobilu, kde se zalomení"
          ]
        ]
      },
      "mental": "Typografie na obrazovce musí být skenovatelná, čitelná a odolná vůči změně šířky.",
      "steps": [
        "Používej malý počet pojmenovaných textových rolí.",
        "Čitelnost ovlivňuje velikost, line-height, délka řádku i kontrast.",
        "Headline musí být testovaný i na mobilu, kde se zalomení může dramaticky změnit.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Webová typografie I“?",
        "options": [
          "Typografie na obrazovce musí být skenovatelná, čitelná a odolná vůči změně šířky.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Používej malý počet pojmenovaných textových rolí."
      }
    },
    "image-web-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Jeden master může potřebovat více cropů pro různé poměry"
          ],
          [
            "Krok 2",
            "Rozměr exportu má odpovídat zobrazované ploše; zbytečně "
          ],
          [
            "Krok 3",
            "Informační obraz potřebuje textovou alternativu; čistě d"
          ]
        ]
      },
      "mental": "Webový obraz musí podporovat obsah, mít správný crop a odpovídat skutečnému použití.",
      "steps": [
        "Jeden master může potřebovat více cropů pro různé poměry stran.",
        "Rozměr exportu má odpovídat zobrazované ploše; zbytečně velký originál zvyšuje datovou zátěž.",
        "Informační obraz potřebuje textovou alternativu; čistě dekorativní obraz nesmí nést jedinou důležitou informaci.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Obraz a assety pro web“?",
        "options": [
          "Webový obraz musí podporovat obsah, mít správný crop a odpovídat skutečnému použití.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Jeden master může potřebovat více cropů pro různé poměry stran."
      }
    },
    "forms-a11y-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Label popisuje význam pole a zůstává čitelný i po zadání"
          ],
          [
            "Krok 2",
            "Chyba má být konkrétní a nemá být signalizovaná jen barv"
          ],
          [
            "Krok 3",
            "Focus pomáhá uživateli chápat, který prvek právě ovládá."
          ]
        ]
      },
      "mental": "Formulář musí být srozumitelný v defaultu, při focusu i při chybě.",
      "steps": [
        "Label popisuje význam pole a zůstává čitelný i po zadání hodnoty.",
        "Chyba má být konkrétní a nemá být signalizovaná jen barvou.",
        "Focus pomáhá uživateli chápat, který prvek právě ovládá.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Formuláře a přístupnost I“?",
        "options": [
          "Formulář musí být srozumitelný v defaultu, při focusu i při chybě.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Label popisuje význam pole a zůstává čitelný i po zadání hodnoty."
      }
    },
    "design-system-i": {
      "time": "10–16 min",
      "level": "Základ",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Začni typografií, barvami a spacingem; teprve potom sklá"
          ],
          [
            "Krok 2",
            "Token pojmenuj podle účelu, pokud má fungovat napříč pro"
          ],
          [
            "Krok 3",
            "Dokumentace má ukázat správné použití i typickou chybu."
          ]
        ]
      },
      "mental": "Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.",
      "steps": [
        "Začni typografií, barvami a spacingem; teprve potom skládej komponenty.",
        "Token pojmenuj podle účelu, pokud má fungovat napříč produktem.",
        "Dokumentace má ukázat správné použití i typickou chybu.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Mini design systém I“?",
        "options": [
          "Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Začni typografií, barvami a spacingem; teprve potom skládej komponenty."
      }
    }
  },
  "class_2a": {
    "information-architecture-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Content inventory je vstup; následně obsah seskupuj podl"
          ],
          [
            "Krok 2",
            "Sitemap popisuje hierarchii, user flow popisuje konkrétn"
          ],
          [
            "Krok 3",
            "Test struktury může proběhnout i bez finálního vizuálu."
          ]
        ]
      },
      "mental": "IA organizuje obsah podle očekávání a cílů uživatele, ne podle interní struktury firmy.",
      "steps": [
        "Content inventory je vstup; následně obsah seskupuj podle významu a úkolů.",
        "Sitemap popisuje hierarchii, user flow popisuje konkrétní cestu za cílem.",
        "Test struktury může proběhnout i bez finálního vizuálu.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Informační architektura II“?",
        "options": [
          "IA organizuje obsah podle očekávání a cílů uživatele, ne podle interní struktury firmy.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Content inventory je vstup; následně obsah seskupuj podle významu a úkolů."
      }
    },
    "form-states-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Error musí vést k opravě a zachovat správně vyplněná dat"
          ],
          [
            "Krok 2",
            "Loading chrání uživatele před nejistotou a dvojím odeslá"
          ],
          [
            "Krok 3",
            "Focus order a keyboard flow jsou součást návrhu."
          ]
        ]
      },
      "mental": "Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.",
      "steps": [
        "Error musí vést k opravě a zachovat správně vyplněná data.",
        "Loading chrání uživatele před nejistotou a dvojím odesláním.",
        "Focus order a keyboard flow jsou součást návrhu.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Form UX a stavový model“?",
        "options": [
          "Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Error musí vést k opravě a zachovat správně vyplněná data."
      }
    },
    "auto-layout-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Rozliš hug, fill a fixed podle role prvku."
          ],
          [
            "Krok 2",
            "Gap a padding jsou jiné veličiny."
          ],
          [
            "Krok 3",
            "Varianty mají popisovat skutečné stavy/velikosti, ne nah"
          ]
        ]
      },
      "mental": "Auto Layout vyjadřuje vztahy mezi obsahem a prostorem, takže komponenta lépe reaguje na změnu textu a viewportu.",
      "steps": [
        "Rozliš hug, fill a fixed podle role prvku.",
        "Gap a padding jsou jiné veličiny.",
        "Varianty mají popisovat skutečné stavy/velikosti, ne nahodilé kopie komponent.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Auto Layout a varianty II“?",
        "options": [
          "Auto Layout vyjadřuje vztahy mezi obsahem a prostorem, takže komponenta lépe reaguje na změnu textu a viewportu.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Rozliš hug, fill a fixed podle role prvku."
      }
    },
    "accessibility-audit-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Barva nesmí být jediným nositelem významu."
          ],
          [
            "Krok 2",
            "Keyboard focus musí být viditelný a ovládání musí mít sm"
          ],
          [
            "Krok 3",
            "Při zoom/reflow nesmí klíčový obsah zmizet nebo být zakr"
          ]
        ]
      },
      "mental": "Audit hledá bariéry v kontrastu, ovládání, focusu, reflow, textech a stavových informacích.",
      "steps": [
        "Barva nesmí být jediným nositelem významu.",
        "Keyboard focus musí být viditelný a ovládání musí mít smysluplné pořadí.",
        "Při zoom/reflow nesmí klíčový obsah zmizet nebo být zakrytý.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Accessibility audit II“?",
        "options": [
          "Audit hledá bariéry v kontrastu, ovládání, focusu, reflow, textech a stavových informacích.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Barva nesmí být jediným nositelem významu."
      }
    },
    "html-css-handoff-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Statický screenshot nepopisuje responsive chování ani st"
          ],
          [
            "Krok 2",
            "Tokeny snižují množství náhodných lokálních hodnot."
          ],
          [
            "Krok 3",
            "Komponenta potřebuje obsahové hranice a edge cases, neje"
          ]
        ]
      },
      "mental": "Handoff popisuje strukturu, pravidla a chování, které developer potřebuje k věrné a robustní implementaci.",
      "steps": [
        "Statický screenshot nepopisuje responsive chování ani stavy.",
        "Tokeny snižují množství náhodných lokálních hodnot.",
        "Komponenta potřebuje obsahové hranice a edge cases, nejen ideální demo.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Design → HTML/CSS handoff“?",
        "options": [
          "Handoff popisuje strukturu, pravidla a chování, které developer potřebuje k věrné a robustní implementaci.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Statický screenshot nepopisuje responsive chování ani stavy."
      }
    },
    "css-responsive-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro d"
          ],
          [
            "Krok 2",
            "Max-width chrání čitelnost a kompozici na velkých obrazo"
          ],
          [
            "Krok 3",
            "Breakpoint vybírej podle chování obsahu."
          ]
        ]
      },
      "mental": "Flexbox a Grid jsou modely vztahů mezi prvky, které lze využít k implementaci responzivních pravidel.",
      "steps": [
        "Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro dvourozměrné oblasti.",
        "Max-width chrání čitelnost a kompozici na velkých obrazovkách.",
        "Breakpoint vybírej podle chování obsahu.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „CSS layout mindset“?",
        "options": [
          "Flexbox a Grid jsou modely vztahů mezi prvky, které lze využít k implementaci responzivních pravidel.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro dvourozměrné oblasti."
      }
    },
    "usability-test-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Úkol popisuje cíl, ne postup."
          ],
          [
            "Krok 2",
            "Pozorování zapisuj jako chování, interpretaci až následn"
          ],
          [
            "Krok 3",
            "Prioritizuj problém podle dopadu a četnosti, ne podle os"
          ]
        ]
      },
      "mental": "Krátký usability test sleduje, zda uživatel dokáže splnit úkol bez navádění a kde vzniká nejistota.",
      "steps": [
        "Úkol popisuje cíl, ne postup.",
        "Pozorování zapisuj jako chování, interpretaci až následně.",
        "Prioritizuj problém podle dopadu a četnosti, ne podle osobního vkusu.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Usability test II“?",
        "options": [
          "Krátký usability test sleduje, zda uživatel dokáže splnit úkol bez navádění a kde vzniká nejistota.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Úkol popisuje cíl, ne postup."
      }
    },
    "design-qa-handoff-ii": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Issue popisuje steps, expected, actual a severity."
          ],
          [
            "Krok 2",
            "Nejdřív řeš funkční a accessibility dopad, potom kosmeti"
          ],
          [
            "Krok 3",
            "Po opravě zopakuj původní scénář a teprve potom issue uz"
          ]
        ]
      },
      "mental": "Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.",
      "steps": [
        "Issue popisuje steps, expected, actual a severity.",
        "Nejdřív řeš funkční a accessibility dopad, potom kosmetické odchylky.",
        "Po opravě zopakuj původní scénář a teprve potom issue uzavři.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Design QA a handoff II“?",
        "options": [
          "Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Issue popisuje steps, expected, actual a severity."
      }
    }
  },
  "class_3a": {
    "linux-filesystem": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "/etc obvykle obsahuje konfiguraci, /var proměnlivá provo"
          ],
          [
            "Krok 2",
            "Absolutní cesta začíná v /, relativní v aktuálním pracov"
          ],
          [
            "Krok 3",
            "Před destruktivní operací kontroluj scope pomocí pwd a p"
          ]
        ]
      },
      "mental": "Filesystem je hierarchie začínající v / a administrátor potřebuje rozumět cestám dřív, než začne měnit soubory.",
      "steps": [
        "/etc obvykle obsahuje konfiguraci, /var proměnlivá provozní data a logy, /home uživatelská data.",
        "Absolutní cesta začíná v /, relativní v aktuálním pracovním adresáři.",
        "Před destruktivní operací kontroluj scope pomocí pwd a přesné cesty.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Linux filesystem a CLI“?",
        "options": [
          "Filesystem je hierarchie začínající v / a administrátor potřebuje rozumět cestám dřív, než začne měnit soubory.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "/etc obvykle obsahuje konfiguraci, /var proměnlivá provozní data a logy, /home uživatelská data."
      }
    },
    "users-permissions": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "r/w/x mají jiný význam pro soubor a adresář."
          ],
          [
            "Krok 2",
            "Skupina je běžný způsob, jak sdílet přístup bez world-wr"
          ],
          [
            "Krok 3",
            "chmod mění mode, chown vlastnictví."
          ]
        ]
      },
      "mental": "Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.",
      "steps": [
        "r/w/x mají jiný význam pro soubor a adresář.",
        "Skupina je běžný způsob, jak sdílet přístup bez world-writable oprávnění.",
        "chmod mění mode, chown vlastnictví.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Uživatelé, skupiny a oprávnění“?",
        "options": [
          "Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "r/w/x mají jiný význam pro soubor a adresář."
      }
    },
    "processes-systemd": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "systemctl status spojuje stav, PID a poslední logy."
          ],
          [
            "Krok 2",
            "Restart ukončí a znovu spustí proces; reload může načíst"
          ],
          [
            "Krok 3",
            "Enable řeší boot-time activation, ne nutně okamžité spuš"
          ]
        ]
      },
      "mental": "Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.",
      "steps": [
        "systemctl status spojuje stav, PID a poslední logy.",
        "Restart ukončí a znovu spustí proces; reload může načíst konfiguraci bez plného restartu.",
        "Enable řeší boot-time activation, ne nutně okamžité spuštění.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Procesy a systemd“?",
        "options": [
          "Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "systemctl status spojuje stav, PID a poslední logy."
      }
    },
    "journal-logs": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Začni časovým oknem a konkrétní unit."
          ],
          [
            "Krok 2",
            "Severity je vodítko, ne absolutní pravda."
          ],
          [
            "Krok 3",
            "Koreluj log s měřením portu, health checkem a změnovou h"
          ]
        ]
      },
      "mental": "Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.",
      "steps": [
        "Začni časovým oknem a konkrétní unit.",
        "Severity je vodítko, ne absolutní pravda.",
        "Koreluj log s měřením portu, health checkem a změnovou historií.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „journalctl a provozní logy“?",
        "options": [
          "Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Začni časovým oknem a konkrétní unit."
      }
    },
    "ssh-keys-ops": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Private key zůstává tajný, public key se instaluje na se"
          ],
          [
            "Krok 2",
            "Příliš široká práva privátního klíče mohou být klientem "
          ],
          [
            "Krok 3",
            "Timeout, connection refused a permission denied jsou růz"
          ]
        ]
      },
      "mental": "SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.",
      "steps": [
        "Private key zůstává tajný, public key se instaluje na server.",
        "Příliš široká práva privátního klíče mohou být klientem odmítnuta.",
        "Timeout, connection refused a permission denied jsou různé evidence.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „SSH klíče a vzdálená správa“?",
        "options": [
          "SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Private key zůstává tajný, public key se instaluje na server."
      }
    },
    "linux-firewall": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0"
          ],
          [
            "Krok 2",
            "Firewall pravidlo má mít co nejmenší source/destination/"
          ],
          [
            "Krok 3",
            "Validuj povolený i zakázaný scénář."
          ]
        ]
      },
      "mental": "Dostupnost služby vzniká kombinací listeneru, bind adresy, routingu a firewall policy.",
      "steps": [
        "0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0.0.1 jen lokálně.",
        "Firewall pravidlo má mít co nejmenší source/destination/service scope.",
        "Validuj povolený i zakázaný scénář.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Linux firewall a service exposure“?",
        "options": [
          "Dostupnost služby vzniká kombinací listeneru, bind adresy, routingu a firewall policy.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0.0.1 jen lokálně."
      }
    },
    "web-service-linux": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Lokální curl odlišuje problém aplikace od vzdálené síťov"
          ],
          [
            "Krok 2",
            "HTTP status je evidence aplikační/proxy vrstvy."
          ],
          [
            "Krok 3",
            "Po opravě testuj původní uživatelskou cestu, ne pouze lo"
          ]
        ]
      },
      "mental": "End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.",
      "steps": [
        "Lokální curl odlišuje problém aplikace od vzdálené síťové cesty.",
        "HTTP status je evidence aplikační/proxy vrstvy.",
        "Po opravě testuj původní uživatelskou cestu, ne pouze localhost.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Linux webová služba: end-to-end cesta“?",
        "options": [
          "End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Lokální curl odlišuje problém aplikace od vzdálené síťové cesty."
      }
    },
    "shell-cron": {
      "time": "10–16 min",
      "level": "Střední",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Cron/timer běží v jiném prostředí než interaktivní shell"
          ],
          [
            "Krok 2",
            "Secrets nepatří do zdrojového skriptu."
          ],
          [
            "Krok 3",
            "Naplánování není důkaz úspěchu; ověř reálný výsledek."
          ]
        ]
      },
      "mental": "Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.",
      "steps": [
        "Cron/timer běží v jiném prostředí než interaktivní shell; PATH a working directory mohou být jiné.",
        "Secrets nepatří do zdrojového skriptu.",
        "Naplánování není důkaz úspěchu; ověř reálný výsledek.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Shell automatizace a plánování“?",
        "options": [
          "Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Cron/timer běží v jiném prostředí než interaktivní shell; PATH a working directory mohou být jiné."
      }
    }
  },
  "class_4a": {
    "systemd-advanced": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "After určuje pořadí, Requires/Wants vyjadřují různé síly"
          ],
          [
            "Krok 2",
            "Restart loop může zatížit systém a skrýt původní chybu."
          ],
          [
            "Krok 3",
            "Drop-in override bývá bezpečnější než kopie celé vendor "
          ]
        ]
      },
      "mental": "Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.",
      "steps": [
        "After určuje pořadí, Requires/Wants vyjadřují různé síly závislosti.",
        "Restart loop může zatížit systém a skrýt původní chybu.",
        "Drop-in override bývá bezpečnější než kopie celé vendor unit.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „systemd: dependencies a failure policy“?",
        "options": [
          "Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "After určuje pořadí, Requires/Wants vyjadřují různé síly závislosti."
      }
    },
    "storage-filesystems": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "df ukazuje filesystem, du hledá využití v adresářích; vý"
          ],
          [
            "Krok 2",
            "Inodes mohou dojít dřív než GB."
          ],
          [
            "Krok 3",
            "Mazání bez pochopení ownera procesu a retention policy m"
          ]
        ]
      },
      "mental": "Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.",
      "steps": [
        "df ukazuje filesystem, du hledá využití v adresářích; výsledky nemusí být stejné kvůli otevřeným souborům/mountům.",
        "Inodes mohou dojít dřív než GB.",
        "Mazání bez pochopení ownera procesu a retention policy může způsobit další incident.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Storage a filesystems“?",
        "options": [
          "Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "df ukazuje filesystem, du hledá využití v adresářích; výsledky nemusí být stejné kvůli otevřeným souborům/mountům."
      }
    },
    "backup-strategy": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "RPO popisuje tolerovatelnou ztrátu dat v čase."
          ],
          [
            "Krok 2",
            "RTO popisuje cílový čas obnovení služby."
          ],
          [
            "Krok 3",
            "Restore drill má probíhat do bezpečného testovacího cíle"
          ]
        ]
      },
      "mental": "Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.",
      "steps": [
        "RPO popisuje tolerovatelnou ztrátu dat v čase.",
        "RTO popisuje cílový čas obnovení služby.",
        "Restore drill má probíhat do bezpečného testovacího cíle a ověřit integritu i funkci.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Backup strategie, RPO a RTO“?",
        "options": [
          "Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "RPO popisuje tolerovatelnou ztrátu dat v čase."
      }
    },
    "ssh-hardening": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Preferuj klíče a omezený management scope."
          ],
          [
            "Krok 2",
            "Firewall a SSH config měň s ověřeným rollbackem/console "
          ],
          [
            "Krok 3",
            "Security změna potřebuje pozitivní i negativní validační"
          ]
        ]
      },
      "mental": "Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.",
      "steps": [
        "Preferuj klíče a omezený management scope.",
        "Firewall a SSH config měň s ověřeným rollbackem/console access.",
        "Security změna potřebuje pozitivní i negativní validační test.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „SSH hardening a bezpečný management“?",
        "options": [
          "Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Preferuj klíče a omezený management scope."
      }
    },
    "containers-basics": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Image je neměnný build artefakt, container konkrétní ins"
          ],
          [
            "Krok 2",
            "Volume slouží pro data, která mají přežít recreate."
          ],
          [
            "Krok 3",
            "Publikovaný host port neřeší špatný listener nebo neheal"
          ]
        ]
      },
      "mental": "Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.",
      "steps": [
        "Image je neměnný build artefakt, container konkrétní instance.",
        "Volume slouží pro data, která mají přežít recreate.",
        "Publikovaný host port neřeší špatný listener nebo nehealthy aplikaci uvnitř.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Containers: image, runtime, volume a network“?",
        "options": [
          "Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Image je neměnný build artefakt, container konkrétní instance."
      }
    },
    "automation-shell": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Idempotentní druhý průchod nemá vytvářet další změny."
          ],
          [
            "Krok 2",
            "Dry-run/plan umožňuje zkontrolovat scope."
          ],
          [
            "Krok 3",
            "Automatizace bez guardů násobí chybu rychleji než ruční "
          ]
        ]
      },
      "mental": "Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.",
      "steps": [
        "Idempotentní druhý průchod nemá vytvářet další změny.",
        "Dry-run/plan umožňuje zkontrolovat scope.",
        "Automatizace bez guardů násobí chybu rychleji než ruční práce.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Bezpečná automatizace a idempotence“?",
        "options": [
          "Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Idempotentní druhý průchod nemá vytvářet další změny."
      }
    },
    "packet-diagnostics-advanced": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "SYN timeout, RST a SYN-ACK vedou k různým hypotézám."
          ],
          [
            "Krok 2",
            "Capture bez filtru rychle vytváří šum."
          ],
          [
            "Krok 3",
            "TCP úspěch neznamená automaticky funkční TLS/HTTP."
          ]
        ]
      },
      "mental": "Packet capture má odpovědět na konkrétní otázku a musí být korelovaný se socket state na serveru.",
      "steps": [
        "SYN timeout, RST a SYN-ACK vedou k různým hypotézám.",
        "Capture bez filtru rychle vytváří šum.",
        "TCP úspěch neznamená automaticky funkční TLS/HTTP.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Pokročilá packet diagnostika“?",
        "options": [
          "Packet capture má odpovědět na konkrétní otázku a musí být korelovaný se socket state na serveru.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "SYN timeout, RST a SYN-ACK vedou k různým hypotézám."
      }
    },
    "incident-runbook": {
      "time": "10–16 min",
      "level": "Pokročilé",
      "visual": {
        "type": "layers",
        "items": [
          [
            "Krok 1",
            "Mitigation může mít přednost před detailním root cause, "
          ],
          [
            "Krok 2",
            "Timeline odděluje fakta od hypotéz."
          ],
          [
            "Krok 3",
            "Postmortem má konkrétní akce s ownerem a výsledkem."
          ]
        ]
      },
      "mental": "Incident response je strukturovaný proces: impact, role, evidence, mitigation, validation a follow-up.",
      "steps": [
        "Mitigation může mít přednost před detailním root cause, pokud rychle obnoví službu bezpečným rollbackem.",
        "Timeline odděluje fakta od hypotéz.",
        "Postmortem má konkrétní akce s ownerem a výsledkem.",
        "Použij princip na krátkém příkladu nebo labu.",
        "Vysvětli vlastními slovy, jaký důkaz bys hledal/a."
      ],
      "mistakes": [
        "Přeskočit přímo k řešení bez modelu.",
        "Zaměnit pozorování za domněnku.",
        "Neověřit výsledek po změně."
      ],
      "check": {
        "q": "Co je hlavní princip tématu „Incident runbook a koordinace“?",
        "options": [
          "Incident response je strukturovaný proces: impact, role, evidence, mitigation, validation a follow-up.",
          "Stačí si zapamatovat název nástroje bez kontextu.",
          "Nejdůležitější je provést co nejvíc změn najednou."
        ],
        "correct": 0,
        "why": "Mitigation může mít přednost před detailním root cause, pokud rychle obnoví službu bezpečným rollbackem."
      }
    }
  }
}
JSON, true, 512, JSON_THROW_ON_ERROR);
