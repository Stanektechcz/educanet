<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „projects“ (angličtina) – projekty podle levelu.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Projekty' => 'Projects',
    'Nabídky skutečné práce podle levelu' => 'Real work offers based on your level',
    'Klienti nabízejí skutečné zakázky. Kontakt s klientem vždy jde přes školu – nikdy mu nedáváme tvoje osobní údaje.' => 'Clients offer real projects. Contact with the client always goes through the school – we never give them your personal data.',
    'Zatím tu není žádná nabídka. Zkontroluj to znovu později.' => 'There is no offer here yet. Check back later.',
    'Nabídka projektů' => 'Project offers',
    'Projekt už není dostupný' => 'The project is no longer available',
    'Odměna v Kč' => 'Money reward (CZK)',
    'Do portfolia' => 'For your portfolio',
    'Certifikát' => 'Certificate',
    'Jiná odměna' => 'Other reward',
    'Čeká na vyjádření' => 'Waiting for a decision',
    'Přijato' => 'Accepted',
    'Nepřijato' => 'Not accepted',
    'Staženo' => 'Withdrawn',
    'Vyžaduje úroveň {n}' => 'Requires level {n}',
    'Vyžaduje souhlas zákonného zástupce' => 'Requires guardian consent',
    'Volná místa: {n}' => 'Open spots: {n}',
    'Termín: {date}' => 'Deadline: {date}',
    'Potřebuješ úroveň {need} (máš {have}).' => 'You need level {need} (you have {have}).',
    'Stáhnout přihlášku' => 'Withdraw application',
    'Kapacita je naplněná.' => 'Capacity is full.',
    'Proč tě projekt zajímá?' => 'Why are you interested in this project?',
    'Projevit zájem' => 'Show interest',
    'Moje přihlášky' => 'My applications',
    'Historie přihlášek k projektům' => 'Project application history',
    'Zatím ses k žádnému projektu nepřihlásil/a.' => "You haven't applied to any project yet.",
    'Přihláška byla odeslána.' => 'Application was sent.',
    'Přihlášku se nepodařilo odeslat.' => 'Could not send the application.',
    'Přihláška byla stažena.' => 'Application was withdrawn.',
    'Přihlášku se nepodařilo stáhnout.' => 'Could not withdraw the application.',
    'Cyklus projektu a portfolio' => 'Project cycle and portfolio',
    'Chybí ti kompetence:' => 'You are missing these competencies:',
    'Přihlásit se můžeš, až kompetence zvládneš.' => 'You can apply once you have mastered them.',
    'Na tenhle projekt ti zatím chybí požadované kompetence.' => 'You do not have the competencies required for this project yet.',
];
