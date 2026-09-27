<?php

declare(strict_types=1);

return json_decode(<<<'JSON'
{
  "class_1a": [
    {
      "id": "v30_1a_19",
      "number": 19,
      "title": "Lekce 19 · Visual audit clinic",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Najít konkrétní problém v existujícím návrhu a opravit jej na základě hierarchie, kontrastu a spacingu",
      "knowledge": [
        "design-feedback",
        "content-first-layout"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "design-feedback"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "design-feedback",
            "content-first-layout"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_20",
      "number": 20,
      "title": "Lekce 20 · Content-first landing",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Převést stručný obsahový brief do webového skeletonu bez zbytečných dekorací.",
      "knowledge": [
        "content-first-layout"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "content-first-layout"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "content-first-layout"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_21",
      "number": 21,
      "title": "Lekce 21 · Responsive hero",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Navrhnout hero sekci, která zachová prioritu obsahu na desktopu i mobilu.",
      "knowledge": [
        "responsive-art-direction",
        "content-first-layout"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "responsive-art-direction"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "responsive-art-direction",
            "content-first-layout"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_22",
      "number": 22,
      "title": "Lekce 22 · Microcopy & CTA",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Napsat konkrétní CTA a pomocné texty, které snižují nejistotu uživatele.",
      "knowledge": [
        "microcopy-cta"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "microcopy-cta"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "microcopy-cta"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_23",
      "number": 23,
      "title": "Lekce 23 · Accessible color & type",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Ověřit kontrast, čitelnost a hierarchii bez závislosti jen na barvě.",
      "knowledge": [
        "forms-a11y-i",
        "web-typography-i"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "forms-a11y-i"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "forms-a11y-i",
            "web-typography-i"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_24",
      "number": 24,
      "title": "Lekce 24 · Image art direction",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Připravit různé cropy stejného obrazu pro desktop, card a mobil bez ztráty focal pointu.",
      "knowledge": [
        "responsive-art-direction",
        "image-composition"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "responsive-art-direction"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "responsive-art-direction",
            "image-composition"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_25",
      "number": 25,
      "title": "Lekce 25 · Component starter",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Navrhnout jednoduchou komponentu s default/focus/error stavem a jasným obsahem.",
      "knowledge": [
        "components-i",
        "forms-a11y-i"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "components-i"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "components-i",
            "forms-a11y-i"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_26",
      "number": 26,
      "title": "Lekce 26 · Critique workshop",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Dávat a přijímat konkrétní design feedback podle cíle a evidence.",
      "knowledge": [
        "design-feedback"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "design-feedback"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "design-feedback"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_27",
      "number": 27,
      "title": "Lekce 27 · Landing sprint",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "V týmu vytvořit funkční responzivní landing page od briefu po QA.",
      "knowledge": [
        "content-first-layout",
        "microcopy-cta",
        "responsive-art-direction"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "content-first-layout"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "content-first-layout",
            "microcopy-cta",
            "responsive-art-direction"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_1a_28",
      "number": 28,
      "title": "Lekce 28 · Portfolio & mastery",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Vybrat nejlepší výstup, popsat rozhodnutí a doložit, které skills byly prokázány.",
      "knowledge": [
        "design-feedback",
        "design-feedback"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "design-feedback"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "design-feedback",
            "design-feedback"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    }
  ],
  "class_2a": [
    {
      "id": "v30_2a_19",
      "number": 19,
      "title": "Lekce 19 · Research framing",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Převést neurčitý produktový problém do výzkumné otázky a jednoduchého testovacího plánu.",
      "knowledge": [
        "information-architecture-ii",
        "usability-test-ii"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "information-architecture-ii"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "information-architecture-ii",
            "usability-test-ii"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_20",
      "number": 20,
      "title": "Lekce 20 · Card sorting lab",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Ověřit seskupení obsahu a navrhnout informační architekturu podle mentálních modelů uživatelů.",
      "knowledge": [
        "card-sorting",
        "information-architecture-ii"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "card-sorting"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "card-sorting",
            "information-architecture-ii"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_21",
      "number": 21,
      "title": "Lekce 21 · Responsive component stress test",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Otestovat komponentu na dlouhý obsah, malé viewporty a různé stavy.",
      "knowledge": [
        "auto-layout-ii",
        "css-responsive-ii"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "auto-layout-ii"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "auto-layout-ii",
            "css-responsive-ii"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_22",
      "number": 22,
      "title": "Lekce 22 · Form recovery states",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Navrhnout validaci, chyby a recovery tak, aby uživatel nepřišel o práci.",
      "knowledge": [
        "error-recovery-ux",
        "form-states-ii"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "error-recovery-ux"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "error-recovery-ux",
            "form-states-ii"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_23",
      "number": 23,
      "title": "Lekce 23 · Accessible interaction",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Ověřit focus, klávesnici, target size a alternativu k drag/hover gestům.",
      "knowledge": [
        "interaction-accessibility",
        "accessibility-audit-ii"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "interaction-accessibility"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "interaction-accessibility",
            "accessibility-audit-ii"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_24",
      "number": 24,
      "title": "Lekce 24 · Design tokens to code",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Připravit tokeny a komponentovou specifikaci použitelnou při implementaci.",
      "knowledge": [
        "design-tokens-ii",
        "design-handoff-qa"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "design-tokens-ii"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "design-tokens-ii",
            "design-handoff-qa"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_25",
      "number": 25,
      "title": "Lekce 25 · Usability round 2",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Provést krátký test bez navádění, zaznamenat evidence a upravit návrh.",
      "knowledge": [
        "usability-test-ii"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "usability-test-ii"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "usability-test-ii"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_26",
      "number": 26,
      "title": "Lekce 26 · Design QA clinic",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Převést nalezený nesoulad na reprodukovatelné issue a ověřit opravu.",
      "knowledge": [
        "design-handoff-qa"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "design-handoff-qa"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "design-handoff-qa"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_27",
      "number": 27,
      "title": "Lekce 27 · Product sprint",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Propojit IA, komponenty, responsive pravidla, accessibility a testování v týmovém capstone.",
      "knowledge": [
        "card-sorting",
        "interaction-accessibility",
        "design-handoff-qa"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "card-sorting"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "card-sorting",
            "interaction-accessibility",
            "design-handoff-qa"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_2a_28",
      "number": 28,
      "title": "Lekce 28 · Case study & mastery",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Vytvořit stručnou case study se skutečným problémem, iterací, důkazem a výsledkem.",
      "knowledge": [
        "design-handoff-qa",
        "portfolio-case-study"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "design-handoff-qa"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "design-handoff-qa",
            "portfolio-case-study"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup vede k nejlépe obhajitelnému návrhu?",
          "options": [
            "Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.",
            "Začít dekoracemi a rozhodnout podle osobního vkusu.",
            "Měnit několik parametrů současně bez testu."
          ],
          "correct": 0,
          "explanation": "Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    }
  ],
  "class_3a": [
    {
      "id": "v30_3a_19",
      "number": 19,
      "title": "Lekce 19 · Filesystem incident",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Diagnostikovat permission/disk/mount problém bez změn naslepo.",
      "knowledge": [
        "linux-file-troubleshooting",
        "linux-filesystem"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "linux-file-troubleshooting"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "linux-file-troubleshooting",
            "linux-filesystem"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_20",
      "number": 20,
      "title": "Lekce 20 · systemd dependency lab",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Rozlišit problém procesu, unit konfigurace, dependency a restart policy.",
      "knowledge": [
        "processes-systemd",
        "processes-systemd"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "processes-systemd"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "processes-systemd",
            "processes-systemd"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_21",
      "number": 21,
      "title": "Lekce 21 · Journal forensic",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Najít relevantní incidentní okno a spojit log se změnou a symptomem.",
      "knowledge": [
        "journal-logs",
        "journal-logs"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "journal-logs"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "journal-logs",
            "journal-logs"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_22",
      "number": 22,
      "title": "Lekce 22 · SSH keys in practice",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Oddělit TCP dostupnost, výběr identity, oprávnění a serverovou autorizaci.",
      "knowledge": [
        "ssh-key-operations",
        "ssh-keys-ops"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "ssh-key-operations"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "ssh-key-operations",
            "ssh-keys-ops"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_23",
      "number": 23,
      "title": "Lekce 23 · DNS evidence chain",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Postavit DNS diagnostiku od resolveru přes záznam po aplikační test.",
      "knowledge": [
        "dns",
        "dns-record-types"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "dns"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "dns",
            "dns-record-types"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_24",
      "number": 24,
      "title": "Lekce 24 · Stateful firewall lab",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Navrhnout least-privilege pravidlo a potvrdit pozitivní i negativní test.",
      "knowledge": [
        "stateful-firewall",
        "linux-firewall"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "stateful-firewall"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "stateful-firewall",
            "linux-firewall"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_25",
      "number": 25,
      "title": "Lekce 25 · Safe Bash automation",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Napsat opakovatelný skript s kontrolou vstupů, chybami a logem.",
      "knowledge": [
        "bash-error-handling",
        "shell-cron"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "bash-error-handling"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "bash-error-handling",
            "shell-cron"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_26",
      "number": 26,
      "title": "Lekce 26 · Service recovery drill",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.",
      "knowledge": [
        "service-debug-chain",
        "web-service-linux"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "service-debug-chain"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "service-debug-chain",
            "web-service-linux"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_27",
      "number": 27,
      "title": "Lekce 27 · Team incident lab",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.",
      "knowledge": [
        "stateful-firewall",
        "bash-error-handling"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "stateful-firewall"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "stateful-firewall",
            "bash-error-handling"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_3a_28",
      "number": 28,
      "title": "Lekce 28 · Mastery review",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Zopakovat nejslabší skills a dokončit praktické mastery evidence před závěrečným projektem.",
      "knowledge": [
        "linux-file-troubleshooting",
        "ssh-key-operations"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "linux-file-troubleshooting"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "linux-file-troubleshooting",
            "ssh-key-operations"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    }
  ],
  "class_4a": [
    {
      "id": "v30_4a_19",
      "number": 19,
      "title": "Lekce 19 · Golden signals lab",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.",
      "knowledge": [
        "golden-signals",
        "logs-monitoring"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "golden-signals"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "golden-signals",
            "logs-monitoring"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_20",
      "number": 20,
      "title": "Lekce 20 · Reverse proxy evidence",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Odlišit DNS/TLS/proxy/upstream závadu pomocí minimálního test chainu.",
      "knowledge": [
        "reverse-proxy",
        "http-observability"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "reverse-proxy"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "reverse-proxy",
            "http-observability"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_21",
      "number": 21,
      "title": "Lekce 21 · Container networking",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.",
      "knowledge": [
        "container-networking"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "container-networking"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "container-networking"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_22",
      "number": 22,
      "title": "Lekce 22 · Restore game day",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.",
      "knowledge": [
        "backup-restore"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "backup-restore"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "backup-restore"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_23",
      "number": 23,
      "title": "Lekce 23 · Hardening audit",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.",
      "knowledge": [
        "ssh-hardening",
        "ssh-hardening"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "ssh-hardening"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "ssh-hardening",
            "ssh-hardening"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_24",
      "number": 24,
      "title": "Lekce 24 · Safe change & rollback",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Definovat stop conditions, rollback trigger a následnou end-to-end validaci.",
      "knowledge": [
        "rollback-strategy",
        "change-management"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "rollback-strategy"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "rollback-strategy",
            "change-management"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_25",
      "number": 25,
      "title": "Lekce 25 · Drift & automation",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.",
      "knowledge": [
        "config-drift",
        "infrastructure-as-code"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "config-drift"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "config-drift",
            "infrastructure-as-code"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_26",
      "number": 26,
      "title": "Lekce 26 · Performance capacity",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.",
      "knowledge": [
        "performance-engineering",
        "capacity-planning"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "performance-engineering"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "performance-engineering",
            "capacity-planning"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_27",
      "number": 27,
      "title": "Lekce 27 · Incident command",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.",
      "knowledge": [
        "incident-command",
        "slo-postmortem"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "incident-command"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "incident-command",
            "slo-postmortem"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    },
    {
      "id": "v30_4a_28",
      "number": 28,
      "title": "Lekce 28 · Reliability mastery",
      "subtitle": "2 × 45 minut · více cest k pochopení",
      "goal": "Propojit observability, safe change, incident response a recovery do závěrečného reliability drill.",
      "knowledge": [
        "golden-signals",
        "rollback-strategy",
        "incident-command"
      ],
      "schedule": [
        {
          "time": "0–10",
          "title": "Rychlý mentální model",
          "text": "Animovaná ukázka + předpověď výsledku."
        },
        {
          "time": "10–25",
          "title": "Dva způsoby vysvětlení",
          "text": "Jednoduché vysvětlení a konkrétní příklad."
        },
        {
          "time": "25–45",
          "title": "Guided practice",
          "text": "Jedna změna, jeden test, jedna evidence."
        },
        {
          "time": "45–70",
          "title": "Samostatná aplikace",
          "text": "Přenesení principu do vlastního případu."
        },
        {
          "time": "70–82",
          "title": "Peer / QA check",
          "text": "Krátký test s konkrétním feedbackem."
        },
        {
          "time": "82–90",
          "title": "Exit ticket",
          "text": "Vysvětli princip vlastními slovy."
        }
      ],
      "steps": [
        {
          "id": "model",
          "time": "12 min",
          "title": "01 · Pochop princip více způsoby",
          "kind": "knowledge",
          "xp": 25,
          "knowledge": [
            "golden-signals"
          ],
          "tasks": [
            "Projdi animovaný model.",
            "Použij alespoň jednu alternativní cestu „Vysvětli jinak“.",
            "Napiš princip jednou vlastní větou."
          ]
        },
        {
          "id": "predict",
          "time": "12 min",
          "title": "02 · Předpověz výsledek",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        },
        {
          "id": "guided",
          "time": "20 min",
          "title": "03 · Guided practice",
          "kind": "manual",
          "xp": 30,
          "knowledge": [
            "golden-signals",
            "rollback-strategy",
            "incident-command"
          ],
          "tasks": [
            "Vyber jeden konkrétní případ.",
            "Před změnou napiš, co očekáváš.",
            "Proveď jeden krok a zaznamenej evidence."
          ]
        },
        {
          "id": "apply",
          "time": "25 min",
          "title": "04 · Samostatná aplikace",
          "kind": "manual",
          "xp": 35,
          "tasks": [
            "Aplikuj princip na nový příklad.",
            "Nepiš jen výsledek – přidej důvod a ověření."
          ]
        },
        {
          "id": "review",
          "time": "13 min",
          "title": "05 · Review / QA",
          "kind": "manual",
          "xp": 25,
          "tasks": [
            "Nech výsledek zkontrolovat podle jasného checklistu.",
            "Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba."
          ]
        },
        {
          "id": "exit",
          "time": "8 min",
          "title": "06 · Exit ticket",
          "kind": "quiz",
          "xp": 20,
          "question": "Který postup je nejlepší při diagnostice?",
          "options": [
            "Symptom → hypotéza → test → důkaz → nejmenší změna → validace.",
            "Restartovat vše a sledovat, zda problém zmizí.",
            "Měnit firewall, DNS a službu současně."
          ],
          "correct": 0,
          "explanation": "Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu."
        }
      ],
      "worksheet": [
        "Předpověď: co očekávám před změnou / testem?",
        "Evidence: co přesně jsem pozoroval/a a co z toho plyne?",
        "Reflexe: co bych příště vysvětlil/a spolužákovi jinak?"
      ],
      "teacher_notes": [
        "Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.",
        "Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení."
      ]
    }
  ]
}
JSON, true, 512, JSON_THROW_ON_ERROR);
