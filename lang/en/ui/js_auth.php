<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog JS msgid domény „js_auth“ (angličtina).
 * assets/ui-v51.js, assets/auth-v54.js, assets/session-v53.js, assets/student-ui-v50-7-7.js
 * (viz lang/domains_v59.php). Vkládá edu_tr_json_js(). Vlastník: builder B1.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Vyber prosím své místo v učebně.' => 'Please choose your seat in the classroom.',
    'Odpověz prosím na všechny otázky.' => 'Please answer all the questions.',
    'Potvrď prosím souhlas na konci kroku.' => 'Please confirm your consent at the end of the step.',
    'Nahraj prosím hotový plakát.' => 'Please upload your finished poster.',
    'Doplň prosím povinné údaje.' => 'Please fill in the required details.',
    'Krok {n} z {total}' => 'Step {n} of {total}',
    'Odesílám…' => 'Sending...',
    'Vybrat hotový plakát' => 'Choose the finished poster',
    'Soubor může mít maximálně 12 MB.' => 'The file can be at most 12 MB.',
    'Řada {row} · Lavice {desk}' => 'Row {row} - Desk {desk}',
    'Krok {n}' => 'Step {n}',
    'Kroky' => 'Steps',
    'Zpět' => 'Back',
    'Pokračovat' => 'Continue',
    'Vyber prosím své místo' => 'Please choose your seat',
    'Pokračovat s týmem' => 'Continue with the team',
    'Pokračovat na úkolu' => 'Continue with the task',
];
