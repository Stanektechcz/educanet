<?php
declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
return json_decode(<<<'JSON'
[
  {
    "class_id": "class_2a",
    "first_name": "Jan",
    "last_name": "Barth",
    "preferred_name": "honzo",
    "seat_id": "r1c1",
    "seat_label": "Řada 1 · Lavice 1 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Alexandr",
    "last_name": "Baru",
    "preferred_name": "Alex",
    "seat_id": "r3c1",
    "seat_label": "Řada 3 · Lavice 1 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Andrej",
    "last_name": "Gladkov",
    "preferred_name": "Andrej",
    "seat_id": "r3c5",
    "seat_label": "Řada 3 · Lavice 3 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Sebastian",
    "last_name": "Greihansel",
    "preferred_name": "Sebo",
    "seat_id": "r2c5",
    "seat_label": "Řada 2 · Lavice 3 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Hynek",
    "last_name": "Halám",
    "preferred_name": "",
    "seat_id": "r4c6",
    "seat_label": "Řada 4 · Lavice 3 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "František",
    "last_name": "Hejl",
    "preferred_name": "",
    "seat_id": "r4c4",
    "seat_label": "Řada 4 · Lavice 2 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Ondřej",
    "last_name": "Hlavina",
    "preferred_name": "Ondro",
    "seat_id": "r1c5",
    "seat_label": "Řada 1 · Lavice 3 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Ondřej",
    "last_name": "Hrdina",
    "preferred_name": "Ondy",
    "seat_id": "r3c3",
    "seat_label": "Řada 3 · Lavice 2 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Mia",
    "last_name": "Konečná",
    "preferred_name": "Mia",
    "seat_id": "r3c8",
    "seat_label": "Řada 3 · Lavice 4 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Adam",
    "last_name": "Kopal",
    "preferred_name": "",
    "seat_id": "r1c6",
    "seat_label": "Řada 1 · Lavice 3 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "David",
    "last_name": "Kromer",
    "preferred_name": "David",
    "seat_id": "r1c2",
    "seat_label": "Řada 1 · Lavice 1 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Matous",
    "last_name": "Mikl",
    "preferred_name": "Mata",
    "seat_id": "r4c5",
    "seat_label": "Řada 4 · Lavice 3 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "mykhailo",
    "last_name": "mishakovskyi",
    "preferred_name": "misha",
    "seat_id": "r3c6",
    "seat_label": "Řada 3 · Lavice 3 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Matěj",
    "last_name": "Nociar",
    "preferred_name": "",
    "seat_id": "r2c6",
    "seat_label": "Řada 2 · Lavice 3 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Václav",
    "last_name": "Němeček",
    "preferred_name": "Vašek",
    "seat_id": "r3c2",
    "seat_label": "Řada 3 · Lavice 1 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Marek",
    "last_name": "Oplt",
    "preferred_name": "Marek",
    "seat_id": "r2c11",
    "seat_label": "Řada 2 · Lavice 6 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Samuel",
    "last_name": "Poništ",
    "preferred_name": "je jedno",
    "seat_id": "r4c1",
    "seat_label": "Řada 4 · Lavice 1 · vlevo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Richard",
    "last_name": "Vošvrda",
    "preferred_name": "Ríša,Richard je to jedno",
    "seat_id": "r4c2",
    "seat_label": "Řada 4 · Lavice 1 · vpravo"
  },
  {
    "class_id": "class_2a",
    "first_name": "Matyáš",
    "last_name": "Šedý",
    "preferred_name": "Maty",
    "seat_id": "r4c7",
    "seat_label": "Řada 4 · Lavice 4 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Roman",
    "last_name": "Dirda",
    "preferred_name": "",
    "seat_id": "r2c6",
    "seat_label": "Řada 2 · Lavice 3 · vpravo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Lukáš",
    "last_name": "Grenar",
    "preferred_name": "",
    "seat_id": "r2c5",
    "seat_label": "Řada 2 · Lavice 3 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Matyáš",
    "last_name": "Hanák",
    "preferred_name": "Maty",
    "seat_id": "r2c2",
    "seat_label": "Řada 2 · Lavice 1 · vpravo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Michaela",
    "last_name": "Kuzevičová",
    "preferred_name": "Míša",
    "seat_id": "r2c4",
    "seat_label": "Řada 2 · Lavice 2 · vpravo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Stanislav",
    "last_name": "Mandiak",
    "preferred_name": "Stas",
    "seat_id": "r3c1",
    "seat_label": "Řada 3 · Lavice 1 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Marek",
    "last_name": "Pavlica",
    "preferred_name": "",
    "seat_id": "r3c5",
    "seat_label": "Řada 3 · Lavice 3 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Martin",
    "last_name": "Procházka",
    "preferred_name": "Martin",
    "seat_id": "r2c3",
    "seat_label": "Řada 2 · Lavice 2 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "David",
    "last_name": "Volgemut",
    "preferred_name": "",
    "seat_id": "r3c3",
    "seat_label": "Řada 3 · Lavice 2 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Barbora",
    "last_name": "Černá",
    "preferred_name": "Bára, Barča",
    "seat_id": "r2c1",
    "seat_label": "Řada 2 · Lavice 1 · vlevo"
  },
  {
    "class_id": "class_3a",
    "first_name": "Vítězslav",
    "last_name": "Čápek",
    "preferred_name": "Vítek",
    "seat_id": "r3c6",
    "seat_label": "Řada 3 · Lavice 3 · vpravo"
  },
  {
    "class_id": "class_4a",
    "first_name": "Matyáš",
    "last_name": "Borusík",
    "preferred_name": "Mega drsnej borec😎",
    "seat_id": "r2c3",
    "seat_label": "Řada 2 · Lavice 2 · vlevo"
  },
  {
    "class_id": "class_4a",
    "first_name": "Kristína",
    "last_name": "Vážanová",
    "preferred_name": "Tína",
    "seat_id": "r1c4",
    "seat_label": "Řada 1 · Lavice 2 · vpravo"
  }
]
JSON, true, 512, JSON_THROW_ON_ERROR);
