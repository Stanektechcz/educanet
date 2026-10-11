<?php

declare(strict_types=1);

/**
 * v72.1 · katalog msgid domény „lesson_v72_1“ (en): dobrovolné AI prompty v lekci a upozornění na soukromí.
 * Klíč = přesně český text ze zdroje (ai_prompts_v72_1.php). Samotné prompty jsou výukový obsah a zůstávají česky.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Procvič si s AI (dobrovolné)' => 'Practise with AI (voluntary)',
    'Než začneš:' => 'Before you start:',
    'Použij jen AI nástroj, který povolila škola nebo tvůj učitel.' => 'Only use an AI tool your school or teacher has allowed.',
    'Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.' => 'Don\'t enter personal data – yours or anyone else\'s: name, email, phone, address, photos or class.',
    'Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.' => 'Don\'t enter school login details: passwords, one-time codes, the lesson code or links from EDUCANET.',
    'Výstup AI vždy ověř – v programu, v simulátoru nebo u učitele. AI se plete i s jistotou.' => 'Always check what the AI says – in the program, in the simulator or with your teacher. AI can be wrong and still sound sure.',
    'Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil sám.' => 'If AI helped with work you hand in, say so: which tool, what for and what you changed yourself.',
    'Prompt označ a zkopíruj do AI nástroje. Prompty jsou česky – AI ti může odpovídat i v jiném jazyce, když ji o to požádáš.' => 'Select a prompt and copy it into the AI tool. The prompts are in Czech – the AI can answer in another language if you ask it to.',
];
