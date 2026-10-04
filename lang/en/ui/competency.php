<?php

declare(strict_types=1);

/**
 * v62 · katalog msgid domény „competency“ (angličtina) – záložka Kompetence v profilu žáka.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Umím určit síť, masku a adresu zařízení' => 'I can work out the network, mask and address of a device',
    'Umím vysvětlit DNS a DHCP a ověřit je v praxi' => 'I can explain DNS and DHCP and check them in practice',
    'Umím krok za krokem diagnostikovat síťový problém' => 'I can diagnose a network problem step by step',
    'Umím rozlišit porty a služby a ověřit, co poslouchá' => 'I can tell ports and services apart and check what is listening',
    'Umím popsat základy zabezpečení sítě a firewallu' => 'I can describe the basics of network security and firewalls',
    'Umím se pohybovat v shellu a pracovat se soubory' => 'I can move around the shell and work with files',
    'Umím filtrovat a zpracovat text v příkazové řádce' => 'I can filter and process text on the command line',
    'Umím spravovat uživatele a oprávnění' => 'I can manage users and permissions',
    'Umím spravovat služby a číst systémové logy' => 'I can manage services and read system logs',
    'Umím se bezpečně připojit přes SSH a klíče' => 'I can connect securely with SSH and keys',
    'Umím automatizovat úlohy skriptem a cronem' => 'I can automate tasks with a script and cron',
    'Umím postavit sémantickou kostru webové stránky' => 'I can build the semantic skeleton of a web page',
    'Umím rozmístit prvky pomocí CSS a přizpůsobit stránku úzkému displeji' => 'I can lay out elements with CSS and adapt a page to a narrow screen',
    'Umím posoudit přístupnost webu a opravit běžné chyby' => 'I can assess web accessibility and fix common mistakes',
    'Umím navrhnout srozumitelné ovládání a text výzvy k akci' => 'I can design clear controls and a call-to-action text',
    'Umím zvolit barvy a ověřit kontrast textu' => 'I can choose colours and check text contrast',
    'Umím zvolit a skloubit písma pro čitelný text' => 'I can choose and pair typefaces for readable text',
    'Umím vybrat správný grafický formát a export' => 'I can pick the right graphic format and export settings',
    'Umím vystavět kompozici s jasnou hierarchií' => 'I can build a composition with a clear hierarchy',
    'test' => 'test',
    'projekt' => 'project',
    'lab' => 'lab',
    'hra' => 'game',
    'aréna' => 'arena',
    'lekce' => 'lesson',
    'pamatuje' => 'remember',
    'použije' => 'apply',
    'analyzuje' => 'analyse',
    'tvoří' => 'create',
    'Upevněno' => 'Consolidated',
    'Zvládnuto' => 'Mastered',
    'Rozpracováno' => 'In progress',
    'Zatím neověřeno' => 'Not verified yet',
    'Úroveň: {level}' => 'Level: {level}',
    'Důkazů: {n} ({sources})' => 'Evidence: {n} ({sources})',
    'Zatím tu nic není.' => "There's nothing here yet.",
    'Mapa ukazuje, co už umíš podle testů, lekcí a úloh v labu. Hra sama zvládnutí nedá, je to jen trénink. Mapu vidíš jen ty.' => 'The map shows what you can already do, based on tests, lessons and lab tasks. A game alone never counts as mastery, it is just practice. Only you can see this map.',
    'Umím {n} z {total} kompetencí' => 'I can do {n} of {total} competencies',
    'Umím' => 'I can',
    'Učím se' => 'I am learning',
    'Zatím ne' => 'Not yet',
];
