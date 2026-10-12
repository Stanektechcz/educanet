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
    'úloha' => 'task',   // v76
    'pozorování učitele' => 'teacher observation',   // v76
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
    // v75 · kompetence 2.A a 4.A
    'Umím sestavit design systém z tokenů, mezer a komponent' => 'I can build a design system from tokens, spacing and components',
    'Umím převést návrh do responzivního rozvržení v CSS' => 'I can turn a design into a responsive CSS layout',
    'Umím provést audit přístupnosti návrhu a navrhnout opravy' => 'I can audit a design for accessibility and propose fixes',
    'Umím navrhnout formulář se stavy a srozumitelnými chybami' => 'I can design a form with states and clear error messages',
    'Umím ověřit použitelnost testem a uspořádat obsah' => 'I can check usability with a test and organise content',
    'Umím navrhnout responzivní typografickou škálu' => 'I can design a responsive type scale',
    'Umím vytvořit vizuální kampaň s jasnou hierarchií a kontrastem' => 'I can create a visual campaign with clear hierarchy and contrast',
    'Umím předat návrh k realizaci a obhájit ho v případové studii' => 'I can hand off a design for building and defend it in a case study',
    'Umím navrhnout adresní plán a směrování sítě' => 'I can design an addressing plan and network routing',
    'Umím nastavit reverzní proxy a ověřit certifikát TLS' => 'I can set up a reverse proxy and check a TLS certificate',
    'Umím diagnostikovat síť podle paketů a stavu spojení' => 'I can diagnose a network from packets and connection state',
    'Umím zabezpečit službu: SSH, firewall, práva a aktualizace' => 'I can secure a service: SSH, firewall, permissions and updates',
    'Umím sledovat službu pomocí logů, metrik a cílů SLO' => 'I can monitor a service with logs, metrics and SLO targets',
    'Umím provést řízenou změnu s plánem návratu' => 'I can make a controlled change with a rollback plan',
    'Umím navrhnout zálohu podle RPO a RTO a ověřit obnovu' => 'I can design backups for RPO and RTO and verify a restore',
    'Umím popsat infrastrukturu jako kód a najít odchylku konfigurace' => 'I can describe infrastructure as code and find configuration drift',
    'Umím spravovat služby a kontejnery včetně závislostí a restartu' => 'I can manage services and containers, including dependencies and restarts',
    'Umím změřit výkon a naplánovat kapacitu' => 'I can measure performance and plan capacity',
    'Umím vést incident podle runbooku a sepsat rozbor bez obviňování' => 'I can run an incident from a runbook and write a blameless review',
];
