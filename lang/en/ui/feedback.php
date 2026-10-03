<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „feedback“ (anglicky) – nahlášení chyby / návrh vylepšení.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Chyba' => 'Bug',
    'Návrh vylepšení' => 'Improvement idea',
    'Čeká na posouzení' => 'Waiting for review',
    'Potvrzeno' => 'Confirmed',
    'Zamítnuto' => 'Rejected',
    'Duplicita' => 'Duplicate',
    'Vyřešeno' => 'Resolved',
    'Vyber, jestli jde o chybu, nebo o návrh vylepšení.' => 'Choose whether this is a bug or an improvement idea.',
    'Titulek je moc krátký (aspoň {n} znaků).' => 'The title is too short (at least {n} characters).',
    'Titulek je moc dlouhý (nejvýš {n} znaků).' => 'The title is too long (at most {n} characters).',
    'Popiš to podrobněji (aspoň {n} znaků).' => 'Describe it in more detail (at least {n} characters).',
    'Popis je moc dlouhý (nejvýš {n} znaků).' => 'The description is too long (at most {n} characters).',
    'Dnes jsi poslal/a maximum hlášení. Zkus to zítra.' => 'You have sent the maximum number of reports today. Try again tomorrow.',
    'Hlášení se stejným titulkem už jsi poslal/a.' => 'You have already sent a report with the same title.',
    'Hlášení se nepodařilo odeslat.' => 'The report could not be sent.',
    'Hlášení' => 'Reports',
    'Nahlásit chybu nebo navrhnout vylepšení' => 'Report a bug or suggest an improvement',
    'Pomoz nám zlepšit Educanet. Když učitel hlášení potvrdí, dostaneš body a XP (chyba obvykle {bp} b / {bx} XP, návrh {ip} b / {ix} XP).' => 'Help us improve Educanet. When your teacher confirms a report, you get points and XP (a bug is usually {bp} pts / {bx} XP, an idea {ip} pts / {ix} XP).',
    'Nové hlášení' => 'New report',
    'Co posíláš' => 'What are you sending',
    'Stránka: {page}' => 'Page: {page}',
    'Titulek' => 'Title',
    'Popis' => 'Description',
    'U chyby napiš, co jsi dělal/a a co se stalo. Nepiš hesla ani osobní údaje. Dnes můžeš poslat ještě {n} hlášení.' => 'For a bug, write what you were doing and what happened. Do not include passwords or personal data. You can send {n} more reports today.',
    'Odeslat hlášení' => 'Send report',
    'Moje hlášení' => 'My reports',
    'Zatím jsi nic neposlal/a.' => 'You have not sent anything yet.',
    'Odměna: {p} b a {x} XP' => 'Reward: {p} pts and {x} XP',
    'Poznámka učitele' => 'Teacher note',
    'Nahlášené chyby a návrhy' => 'Reported bugs and ideas',
    'odměna celkem: {p} b a {x} XP' => 'total reward: {p} pts and {x} XP',
    'Nahlásit chybu nebo návrh' => 'Report a bug or idea',
    'Nahlásit chybu' => 'Report a bug',
    'Díky! Hlášení je odeslané, učitel ho posoudí.' => 'Thanks! Your report has been sent, your teacher will review it.',
    'Nahlášeno' => 'Reported',
    'potvrzeno' => 'confirmed',
];
