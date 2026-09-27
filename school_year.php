<?php

declare(strict_types=1);

return json_decode(<<<'JSON'
{
    "meta": {
        "school_year": "2026/2027",
        "weekday": "středa",
        "weekly_minutes": 90,
        "start": "2026-09-01",
        "end": "2027-06-30",
        "location": "Brno-město",
        "wednesdays_total": 44,
        "teaching_wednesdays": 40,
        "note": "Plán odpovídá reálnému středečnímu rozvrhu. Každá třída vidí pouze svůj blok 2 × 45 minut; výjimky, exkurze a odpadlé hodiny se řeší nad konkrétní třídou.",
        "class_schedule": {
            "class_1a": {
                "label": "1.A",
                "subject": "DGD · Grafika a webdesign",
                "periods": [
                    1,
                    2
                ],
                "start": "08:00",
                "end": "09:40",
                "room": "1.A"
            },
            "class_4a": {
                "label": "4.A",
                "subject": "SOSaPS · Seminář OS a sítě",
                "periods": [
                    3,
                    4
                ],
                "start": "10:00",
                "end": "11:40",
                "room": "3.A"
            },
            "class_2a": {
                "label": "2.A",
                "subject": "GRA · Grafika a webdesign",
                "periods": [
                    6,
                    7
                ],
                "start": "12:45",
                "end": "14:20",
                "room": "2.A"
            },
            "class_3a": {
                "label": "3.A",
                "subject": "SOSaPS · Seminář OS a sítě",
                "periods": [
                    8,
                    9
                ],
                "start": "14:30",
                "end": "16:05",
                "room": "ITuč"
            }
        }
    },
    "calendar": [
        {
            "date": "2026-09-02",
            "status": "no_school",
            "kind": "no_lesson",
            "title": "Výuka kurzu ještě nezačala",
            "description": "Kurz startuje 9. 9. 2026 seznamovacím blokem."
        },
        {
            "date": "2026-09-09",
            "status": "teaching",
            "kind": "intro",
            "title": "Seznamovací blok",
            "description": "Seznámení, pravidla kurzu, nástroje a vstupní diagnostika. První řádná lekce je 16. 9. 2026."
        },
        {
            "date": "2026-09-16",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 1,
            "title": "Lekce 1",
            "slot": 1
        },
        {
            "date": "2026-09-23",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 2,
            "title": "Lekce 2",
            "slot": 2
        },
        {
            "date": "2026-09-30",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 3,
            "title": "Lekce 3",
            "slot": 3
        },
        {
            "date": "2026-10-07",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 4,
            "title": "Lekce 4",
            "slot": 4
        },
        {
            "date": "2026-10-14",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 5,
            "title": "Lekce 5",
            "slot": 5
        },
        {
            "date": "2026-10-21",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 6,
            "title": "Lekce 6",
            "slot": 6
        },
        {
            "date": "2026-10-28",
            "status": "no_school",
            "kind": "holiday",
            "title": "Státní svátek · Den vzniku samostatného československého státu"
        },
        {
            "date": "2026-11-04",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 7,
            "title": "Lekce 7",
            "slot": 7
        },
        {
            "date": "2026-11-11",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 8,
            "title": "Lekce 8",
            "slot": 8
        },
        {
            "date": "2026-11-18",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 9,
            "title": "Lekce 9",
            "slot": 9
        },
        {
            "date": "2026-11-25",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 10,
            "title": "Lekce 10",
            "slot": 10
        },
        {
            "date": "2026-12-02",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 11,
            "title": "Lekce 11",
            "slot": 11
        },
        {
            "date": "2026-12-09",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 12,
            "title": "Lekce 12",
            "slot": 12
        },
        {
            "date": "2026-12-16",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 13,
            "title": "Lekce 13",
            "slot": 13
        },
        {
            "date": "2026-12-23",
            "status": "no_school",
            "kind": "break",
            "title": "Vánoční prázdniny"
        },
        {
            "date": "2026-12-30",
            "status": "no_school",
            "kind": "break",
            "title": "Vánoční prázdniny"
        },
        {
            "date": "2027-01-06",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 14,
            "title": "Lekce 14",
            "slot": 14
        },
        {
            "date": "2027-01-13",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 15,
            "title": "Lekce 15",
            "slot": 15
        },
        {
            "date": "2027-01-20",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 16,
            "title": "Lekce 16",
            "slot": 16
        },
        {
            "date": "2027-01-27",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 17,
            "title": "Lekce 17",
            "slot": 17
        },
        {
            "date": "2027-02-03",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 18,
            "title": "Lekce 18",
            "slot": 18
        },
        {
            "date": "2027-02-10",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 19,
            "title": "Lekce 19",
            "slot": 19
        },
        {
            "date": "2027-02-17",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 20,
            "title": "Lekce 20",
            "slot": 20
        },
        {
            "date": "2027-02-24",
            "status": "no_school",
            "kind": "break",
            "title": "Jarní prázdniny · Brno-město"
        },
        {
            "date": "2027-03-03",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 21,
            "title": "Lekce 21",
            "slot": 21
        },
        {
            "date": "2027-03-10",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 22,
            "title": "Lekce 22",
            "slot": 22
        },
        {
            "date": "2027-03-17",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 23,
            "title": "Lekce 23",
            "slot": 23
        },
        {
            "date": "2027-03-24",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 24,
            "title": "Lekce 24",
            "slot": 24
        },
        {
            "date": "2027-03-31",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 25,
            "title": "Lekce 25",
            "slot": 25
        },
        {
            "date": "2027-04-07",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 26,
            "title": "Lekce 26",
            "slot": 26
        },
        {
            "date": "2027-04-14",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 27,
            "title": "Lekce 27",
            "slot": 27
        },
        {
            "date": "2027-04-21",
            "status": "teaching",
            "kind": "lesson",
            "lesson_number": 28,
            "title": "Lekce 28",
            "slot": 28
        },
        {
            "date": "2027-04-28",
            "status": "teaching",
            "kind": "project",
            "title": "Projektový checkpoint 1",
            "description": "Brief, tým, role, scope a Definition of Done.",
            "slot": 29
        },
        {
            "date": "2027-05-05",
            "status": "teaching",
            "kind": "project",
            "title": "Projektový checkpoint 2",
            "description": "První funkční/prototypová verze a evidence.",
            "slot": 30
        },
        {
            "date": "2027-05-12",
            "status": "teaching",
            "kind": "project",
            "title": "Projektový checkpoint 3",
            "description": "Peer/QA review a opravy.",
            "slot": 31
        },
        {
            "date": "2027-05-19",
            "status": "teaching",
            "kind": "mastery",
            "title": "Mastery clinic",
            "description": "Individuální doplnění chybějících evidence a práce podle Skill Tree.",
            "slot": 32
        },
        {
            "date": "2027-05-26",
            "status": "teaching",
            "kind": "project",
            "title": "Capstone sprint 1",
            "description": "Souvislá týmová práce, teacher coaching pouze podle potřeby.",
            "slot": 33
        },
        {
            "date": "2027-06-02",
            "status": "teaching",
            "kind": "project",
            "title": "Capstone sprint 2",
            "description": "Integrace, QA, finální výstup, dokumentace a obhajoba rozhodnutí.",
            "slot": 34
        },
        {
            "date": "2027-06-09",
            "status": "teaching",
            "kind": "assessment",
            "title": "Obhajoby / demo",
            "description": "Prezentace, role evaluation a evidence pro Mastery.",
            "slot": 35
        },
        {
            "date": "2027-06-16",
            "status": "teaching",
            "kind": "reflection",
            "title": "Retrospektiva + peer feedback",
            "description": "Self review, týmové Keep/Improve/Try a konkrétní peer feedback.",
            "slot": 36
        },
        {
            "date": "2027-06-23",
            "status": "teaching",
            "kind": "portfolio",
            "title": "Portfolio & showcase",
            "description": "Výběr ověřených výstupů, profil a Featured Project.",
            "slot": 37
        },
        {
            "date": "2027-06-30",
            "status": "teaching",
            "kind": "close",
            "title": "Uzavření roku",
            "description": "Skill Mastery recap, osobní growth summary, feedback ke kurzu a plán dalšího rozvoje.",
            "slot": 38
        }
    ],
    "class_tail_focus": {
        "class_1a": "Responsive Landing Sprint · portfolio a design critique",
        "class_2a": "Product Page Studio · usability, handoff a case study",
        "class_3a": "Linux + Network Incident Lab · evidence-first troubleshooting",
        "class_4a": "Production Reliability Drill · incident, recovery a postmortem"
    }
}
JSON, true, 512, JSON_THROW_ON_ERROR);
