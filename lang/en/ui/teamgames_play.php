<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „teamgames_play“ (angličtina).
 * Zdroj: teamgames_v58_game_bingo.php, teamgames_v58_game_escape.php, teamgames_v58_game_jeopardy.php,
 * teamgames_v58_game_netadmin.php, teamgames_v58_game_relay.php, teamgames_v58_game_tug.php
 * (viz lang/domains_v59.php, PLAN_I18N.md B6e). Jen žákovské texty (instrukce, stavy, hlášky, chyby
 * hráčům); učitelské a projekční texty zůstávají česky. Vlastník: builder i18n_teamgames_play.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // Příkazové bingo
    'Zdarma' => 'Free',
    'Terminál' => 'Terminal',
    'Tohle políčko na tvé kartě není.' => "That square isn't on your card.",
    'Políčko nebylo nalezeno.' => 'The square was not found.',
    'Neznámá akce bingo.' => 'Unknown bingo action.',
    'Příkazové bingo' => 'Command Bingo',

    // Úniková místnost
    'Síťař' => 'Network engineer',
    'Správce' => 'Administrator',
    'Detektiv' => 'Detective',
    'Dokumentátor' => 'Documentarian',
    'Typograf' => 'Typographer',
    'Kolorista' => 'Colourist',
    'Kodér' => 'Coder',
    'Art director' => 'Art director',
    'Tým nebyl nalezen.' => 'The team was not found.',
    'Tenhle tým už unikl.' => 'This team has already escaped.',
    'Nápovědy pro tenhle tým došly.' => 'This team is out of hints.',
    'Tým dostal nápovědu (+{s} s k výslednému času).' => 'The team got a hint (+{s} s to the final time).',
    'Tahle role se řeší v terminálu Labu.' => 'This role is solved in the Lab terminal.',
    'Nemáš přiřazenou roli.' => "You don't have a role assigned.",
    'Otázka se nenašla.' => 'The question was not found.',
    'Moc pokusů o zámek za minutu – chvilku počkej.' => 'Too many lock attempts in a minute – wait a moment.',
    'Zámek potřebuje {n} částí kódu (jednu od každé role).' => 'The lock needs {n} code parts (one from each role).',
    'Neznámá akce únikové místnosti.' => 'Unknown escape room action.',
    'Úniková místnost' => 'Escape Room',

    // Riskuj!
    'Otázka právě běží – nejdřív se musí uzavřít.' => 'A question is running right now – it has to close first.',
    'Teď je na tahu jiný tým.' => "It's another team's turn right now.",
    'Neplatné pole tabule.' => 'Invalid board cell.',
    'Tohle pole je už zahrané.' => 'This cell has already been played.',
    'Pro tohle pole chybí otázka.' => 'This cell is missing a question.',
    'Teď neběží žádná otázka.' => 'No question is running right now.',
    'Čas na odpověď vypršel.' => 'Time to answer has run out.',
    'Na tuhle otázku už jsi odpověděl(a).' => 'You already answered this question.',
    'Neznámá akce Riskuj!.' => 'Unknown Risk It! action.',
    'Riskuj!' => 'Risk It!',

    // Správci sítě / webu
    'Tenhle uzel neexistuje.' => "This node doesn't exist.",
    'Tenhle uzel se ještě neobjevil.' => "This node hasn't appeared yet.",
    'Tenhle uzel se opravuje v terminálu Labu.' => 'This node is fixed in the Lab terminal.',
    'Neznámá akce Správců sítě.' => 'Unknown Network/Web Admins action.',
    'Správci sítě / webu' => 'Network/Web Admins',

    // Štafeta
    'Tenhle tým už doběhl.' => 'This team has already finished.',
    'Pomoc jde přivolat až po {m} minutách na úseku.' => 'Help can only be called after {m} minutes on a leg.',
    'Na tomhle úseku už jste pomoc použili.' => "You've already used help on this leg.",
    'Kterýkoli spoluhráč teď může pomoct – tým dostal +{s} s.' => 'Any teammate can now help – the team got +{s} s.',
    'Tahle štafeta se odpovídá v terminálu Labu.' => 'This relay is answered in the Lab terminal.',
    'Neznámá akce štafety.' => 'Unknown relay action.',
    'Štafeta' => 'Relay',

    // Přetahovaná
    'Teď na tebe nečeká žádná otázka.' => 'No question is waiting for you right now.',
    'Tak rychle to nejde – počkej pár vteřin.' => "You can't go that fast – wait a few seconds.",
    'Neznámá akce přetahované.' => 'Unknown tug of war action.',
    'Přetahovaná' => 'Tug of War',
];
