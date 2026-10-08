<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · glosář anglických termínů v obsahu lekcí (vlna 1, L5–L16).
 * Pravidlo obsahové stopy: česká terminologie má přednost; anglický termín jen s vysvětlením nebo se záznamem zde.
 * Každá lekce overlaye uvádí v poli `glossary` id termínů, které v textu používá – audit v72 ověří, že id existuje
 * a že se termín (`match`, jinak `term`, bez ohledu na velikost písmen) v textu lekce opravdu vyskytuje.
 * Seznam povolených termínů je návrh – čeká na rozhodnutí školy (sekce 5, bod 11 promptu v71–v75).
 * Data obsahu (≈ 110 ř.), bez výstupu. Oblasti: grafika (1.A/2.A), site (3.A), provoz (4.A).
 */

/** @return array<string, array{term:string,cs:string,explain:string,area:string,match?:string}> */
function lg72_terms(): array
{
    static $terms = null;
    if ($terms !== null) return $terms;
    $g = static fn(string $term, string $cs, string $explain, string $area, string $match = ''): array => ['term' => $term, 'cs' => $cs, 'explain' => $explain, 'area' => $area] + ($match !== '' ? ['match' => $match] : []);
    return $terms = [
        // --- grafika a web (1.A, 2.A)
        'hex' => $g('HEX', 'šestnáctkový kód barvy', 'Zápis barvy jako #RRGGBB, např. #1A73E8.', 'grafika'),
        'crop' => $g('crop', 'ořez', 'Výřez obrázku, který mění kompozici a poměr stran.', 'grafika'),
        'focal-point' => $g('focal point', 'ohnisko pozornosti', 'Místo, kam se oko podívá jako první.', 'grafika'),
        'cta' => $g('CTA (call to action)', 'výzva k akci', 'Prvek, který říká, co má divák udělat (tlačítko, odkaz).', 'grafika', 'CTA'),
        'grid' => $g('grid', 'mřížka', 'Neviditelná síť sloupců a řádků pro zarovnání; v CSS také rozvržení Grid.', 'grafika'),
        'wireframe' => $g('wireframe', 'drátěný model', 'Náčrt rozvržení bez barev a dekorací, který ověřuje strukturu.', 'grafika'),
        'safe-zone' => $g('safe zone', 'bezpečná zóna', 'Část plochy, kam nezasahuje rozhraní aplikace ani ořez.', 'grafika'),
        'thumbnail' => $g('thumbnail', 'náhled', 'Zmenšená podoba návrhu, jak ji uživatel uvidí v seznamu.', 'grafika'),
        'whitespace' => $g('whitespace', 'volný prostor', 'Prázdné místo, které odděluje skupiny a vede oko.', 'grafika'),
        'preflight' => $g('preflight', 'kontrola před odevzdáním', 'Kontrolní seznam rozměru, ořezu, kontrastu, údajů a názvu souboru.', 'grafika'),
        'hero' => $g('hero', 'úvodní sekce', 'První velká sekce stránky s nadpisem a hlavní akcí.', 'grafika'),
        'mobile-first' => $g('mobile first', 'nejdřív mobil', 'Postup, kdy se nejdřív navrhuje nejužší verze s nejdůležitějším obsahem.', 'grafika'),
        'breakpoint' => $g('breakpoint', 'bod zlomu', 'Šířka, při které se mění rozvržení, protože obsah přestává fungovat.', 'grafika'),
        'hover' => $g('hover', 'najetí myší', 'Stav prvku, nad kterým je ukazatel myši.', 'grafika'),
        'focus' => $g('focus', 'fokus', 'Stav prvku, na kterém je ovládání z klávesnice; musí být viditelný.', 'grafika'),
        'padding' => $g('padding', 'vnitřní okraj', 'Mezera mezi obsahem prvku a jeho okrajem.', 'grafika'),
        'gap' => $g('gap', 'mezera mezi prvky', 'Rozestup mezi sousedními prvky v rozvržení.', 'grafika'),
        'alt-text' => $g('alt text', 'alternativní text', 'Textový popis obrázku pro čtečky obrazovky a při nenačtení obrázku.', 'grafika'),
        'label' => $g('label', 'popisek pole', 'Trvalý text, který říká, co do pole formuláře patří.', 'grafika'),
        'placeholder' => $g('placeholder', 'zástupný text', 'Ukázkový text v prázdném poli; při psaní zmizí, proto nenahrazuje popisek.', 'grafika'),
        'landing-page' => $g('landing page', 'úvodní (vstupní) stránka', 'Stránka s jedním cílem, na kterou přichází návštěvník z kampaně.', 'grafika'),
        'spacing' => $g('spacing', 'rozestupy', 'Systém mezer v rozhraní, nejlépe podle malé škály hodnot.', 'grafika'),
        'case-study' => $g('case study', 'případová studie', 'Příběh projektu: problém → rozhodnutí → výsledek s důkazem.', 'grafika'),
        'microinteraction' => $g('microinteraction', 'mikrointerakce', 'Malá odezva rozhraní na akci uživatele (spouštěč → zpětná vazba).', 'grafika'),
        'touch-target' => $g('touch target', 'cíl dotyku', 'Plocha, kterou lze trefit prstem; doporučeně aspoň 44 × 44 px.', 'grafika'),
        'design-token' => $g('design token', 'návrhový token', 'Pojmenovaná hodnota (barva, mezera, písmo) sdílená návrhem i kódem.', 'grafika'),
        'handoff' => $g('handoff', 'předání návrhu', 'Specifikace pro vývojáře: struktura, tokeny, stavy a chování.', 'grafika'),
        'reduced-motion' => $g('reduced motion', 'omezení pohybu', 'Nastavení systému, kdy uživatel nechce animace; význam musí zůstat.', 'grafika'),
        'sitemap' => $g('sitemap', 'mapa webu', 'Přehled stránek webu a jejich vztahů.', 'grafika'),
        'user-flow' => $g('user flow', 'cesta uživatele', 'Diagram kroků od vstupu na web k cíli.', 'grafika'),
        'validation' => $g('validation', 'kontrola zadaných údajů', 'Ověření, že údaje ve formuláři mají správný tvar.', 'grafika'),
        'auto-layout' => $g('Auto Layout', 'automatické rozvržení', 'Funkce návrhového nástroje, kdy se prvek přizpůsobuje obsahu a rodiči.', 'grafika'),
        'screen-reader' => $g('screen reader', 'čtečka obrazovky', 'Program, který předčítá obsah stránky nevidomým a slabozrakým.', 'grafika'),
        'flexbox' => $g('Flexbox', 'pružné rozvržení v jedné ose', 'Model rozvržení CSS pro řadu nebo sloupec prvků.', 'grafika'),
        'usability-test' => $g('usability test', 'test použitelnosti', 'Pozorování skutečného uživatele při plnění úkolů, bez navádění.', 'grafika'),
        // --- operační systémy a sítě (3.A)
        'link-local' => $g('link-local', 'lokální adresa linky', 'Adresa IPv6 z rozsahu fe80::/10 platná jen na jednom segmentu sítě.', 'site'),
        'dual-stack' => $g('dual-stack', 'souběh IPv4 a IPv6', 'Provoz, kdy zařízení i služby používají IPv4 i IPv6 zároveň.', 'site'),
        'nxdomain' => $g('NXDOMAIN', 'neexistující jméno', 'Odpověď DNS, že dotazované jméno neexistuje.', 'site'),
        'monitoring' => $g('monitoring', 'sledování provozu', 'Pravidelné měření dostupnosti a stavu služeb s upozorněním.', 'site'),
        'lease' => $g('lease', 'zápůjčka adresy', 'Doba, po kterou klient smí používat adresu přidělenou serverem DHCP.', 'site'),
        'mac' => $g('MAC', 'fyzická adresa síťové karty', 'Identifikátor rozhraní ve tvaru 52:54:00:3a:7c:17.', 'site'),
        'rst' => $g('RST', 'odmítnutí spojení', 'Příznak TCP, kterým cíl spojení aktivně odmítne (např. na portu nikdo neposlouchá).', 'site'),
        'display-filter' => $g('display filter', 'zobrazovací filtr', 'Výraz, který ze záznamu provozu ukáže jen pakety odpovídající otázce.', 'site'),
        'vlsm' => $g('VLSM', 'podsítě proměnné délky', 'Dělení sítě na podsítě různé velikosti podle potřeby.', 'site'),
        'gateway' => $g('gateway', 'výchozí brána', 'Adresa routeru, přes kterou jde provoz mimo vlastní podsíť.', 'site'),
        'acl' => $g('ACL', 'seznam pravidel přístupu', 'Pravidla, kdo smí s kým komunikovat a na jakém portu.', 'site'),
        'ttl' => $g('TTL', 'doba platnosti záznamu', 'Počet sekund, po které smí klient držet odpověď DNS v paměti.', 'site'),
        'tls' => $g('TLS', 'šifrovaná vrstva spojení', 'Protokol, který šifruje spojení a ověřuje certifikát serveru (HTTPS).', 'site'),
        'shell' => $g('shell', 'příkazový interpret', 'Program, do kterého se píšou příkazy (v Linux Labu bash).', 'site'),
        'path' => $g('path', 'cesta k souboru', 'Absolutní cesta začíná „/“, relativní vychází z aktuálního adresáře.', 'site'),
        'least-privilege' => $g('least privilege', 'nejmenší potřebná práva', 'Každý dostane jen práva, která opravdu potřebuje.', 'site'),
        'sudo' => $g('sudo', 'příkaz s právy správce', 'Spustí jeden příkaz s právy jiného uživatele (obvykle roota) a zaznamená to.', 'site'),
        'systemd-unit' => $g('unit', 'jednotka systemd', 'Popis služby nebo jiného objektu, který systemd spravuje (např. nginx.service).', 'site'),
        'pid' => $g('PID', 'číslo procesu', 'Jednoznačné číslo běžícího procesu.', 'site'),
        'reload' => $g('reload', 'načtení konfigurace', 'Služba si znovu načte nastavení bez úplného restartu.', 'site'),
        'journal' => $g('journal', 'systémový žurnál', 'Centrální log systemd; čte se příkazem journalctl.', 'site'),
        'severity' => $g('severity', 'závažnost', 'Úroveň záznamu logu (např. err, warning, info).', 'site'),
        'sftp' => $g('SFTP', 'přenos souborů přes SSH', 'Šifrovaný přenos souborů po spojení SSH.', 'site'),
        'authorized-keys' => $g('authorized_keys', 'seznam povolených klíčů', 'Soubor na serveru s veřejnými klíči, kterými se smí uživatel přihlásit.', 'site'),
        'known-hosts' => $g('known_hosts', 'seznam známých serverů', 'Soubor u klienta s klíči serverů, ke kterým se už připojil.', 'site'),
        'bind' => $g('bind', 'navázání na adresu', 'Adresa a port, na kterých služba přijímá spojení (např. 127.0.0.1:8080).', 'site'),
        'listener' => $g('listener', 'naslouchající služba', 'Proces, který čeká na příchozí spojení na portu.', 'site'),
        'firewall' => $g('firewall', 'brána firewall', 'Filtr, který podle pravidel povoluje nebo zakazuje provoz.', 'site'),
        'runbook' => $g('runbook', 'provozní postup', 'Krok za krokem psaný postup ověření nebo opravy, použitelný kýmkoli z týmu.', 'site'),
        'reverse-proxy' => $g('reverse proxy', 'reverzní proxy', 'Server, který přijímá požadavky klientů a předává je službě za sebou.', 'site'),
        'upstream' => $g('upstream', 'služba za proxy', 'Cílová služba, které proxy předává požadavky.', 'site'),
        // --- provoz sítí a služeb (4.A)
        'load-balancer' => $g('load balancer', 'vyvažovač zátěže', 'Rozděluje požadavky mezi více serverů a vyřazuje nezdravé.', 'provoz'),
        'round-robin' => $g('round-robin', 'střídání dokola', 'Rozdělování požadavků po řadě na každý server.', 'provoz'),
        'health-check' => $g('health check', 'kontrola zdraví', 'Pravidelný test, zda služba odpovídá správně, ne jen zda je otevřený port.', 'provoz'),
        'observability' => $g('observability', 'pozorovatelnost', 'Schopnost z výstupů (logy, metriky, odpovědi) poznat, co se v systému děje.', 'provoz'),
        'canary' => $g('canary', 'kanárkové (postupné) nasazení', 'Nová verze nejdřív jen pro malou část provozu s podmínkou zastavení.', 'provoz'),
        'rollback' => $g('rollback', 'návrat verze', 'Vrácení předchozí verze aplikace; nevrací změněná data.', 'provoz'),
        'restore' => $g('restore', 'obnova ze zálohy', 'Obnovení dat ze zálohy; důvěryhodná je jen ověřená obnova.', 'provoz'),
        'p95' => $g('p95', '95. percentil', 'Hodnota, pod kterou je 95 % měření (např. doby odezvy).', 'provoz'),
        'slo' => $g('SLO', 'cíl spolehlivosti', 'Cílová hodnota ukazatele za období, např. 99,9 % úspěšných požadavků za 30 dní.', 'provoz'),
        'sli' => $g('SLI', 'ukazatel spolehlivosti', 'Měřená veličina z pohledu uživatele, např. podíl úspěšných požadavků.', 'provoz'),
        'error-budget' => $g('error budget', 'rozpočet chyb', 'Povolené množství chyb nebo výpadku daný rozdílem 100 % − SLO.', 'provoz'),
        'postmortem' => $g('postmortem', 'rozbor incidentu', 'Zápis po incidentu: časová osa, příčina, faktory a opatření, bez hledání viníka.', 'provoz'),
        'iac' => $g('IaC', 'infrastruktura jako kód', 'Konfigurace serverů popsaná ve verzovaných souborech.', 'provoz'),
        'drift' => $g('drift', 'odchylka konfigurace', 'Rozdíl mezi požadovaným stavem v repozitáři a skutečným stavem.', 'provoz'),
        'blast-radius' => $g('blast radius', 'dosah změny', 'Kolik systémů a uživatelů změna zasáhne, když se pokazí.', 'provoz'),
        'source-of-truth' => $g('source of truth', 'zdroj pravdy', 'Jediné místo, jehož obsah je závazný (typicky repozitář).', 'provoz'),
        'tail-latency' => $g('tail latency', 'dlouhý ocas latence', 'Nejpomalejší část požadavků (např. nad p95), kterou průměr skryje.', 'provoz'),
        'headroom' => $g('headroom', 'kapacitní rezerva', 'Kapacita nad cílovou zátěží pro špičky a výpadek části systému.', 'provoz'),
        'throughput' => $g('throughput', 'propustnost', 'Počet zpracovaných požadavků za čas, např. požadavků/s.', 'provoz'),
        'drop-in' => $g('drop-in', 'doplňující soubor jednotky', 'Malý soubor, který mění jen vybrané řádky jednotky systemd.', 'provoz'),
        'restart-loop' => $g('restart loop', 'smyčka restartů', 'Služba opakovaně padá a znovu startuje, aniž se odstraní příčina.', 'provoz'),
        'inode' => $g('inode', 'záznam souboru', 'Datová struktura souborového systému; každý soubor jeden potřebuje.', 'provoz'),
        'mount-point' => $g('mount point', 'bod připojení', 'Adresář, ve kterém je souborový systém zpřístupněný.', 'provoz'),
        'retention' => $g('retention', 'retence (doba uchování)', 'Jak dlouho se data, logy nebo zálohy uchovávají.', 'provoz'),
        'rpo' => $g('RPO', 'přípustná ztráta dat', 'Kolik dat (času) smíme při výpadku ztratit.', 'provoz'),
        'rto' => $g('RTO', 'přípustná doba obnovy', 'Jak dlouho smí trvat obnovení provozu.', 'provoz'),
        'attack-surface' => $g('attack surface', 'plocha útoku', 'Všechny vystavené služby a cesty, kudy lze systém napadnout.', 'provoz'),
        'hardening' => $g('hardening', 'zabezpečení (zpevnění) systému', 'Omezení služeb, práv a přístupů na nezbytné minimum.', 'provoz'),
        'lockout' => $g('lockout', 'zamčení se venku', 'Ztráta vzdáleného přístupu po chybné změně; předchází se mu náhradním přístupem.', 'provoz'),
        'container' => $g('container', 'kontejner', 'Izolovaný běžící proces vytvořený z obrazu.', 'provoz'),
        'image' => $g('image', 'obraz kontejneru', 'Neměnná šablona souborů a nastavení, ze které vzniká kontejner.', 'provoz'),
        'volume' => $g('volume', 'svazek', 'Trvalé úložiště připojené ke kontejneru; přežije jeho znovuvytvoření.', 'provoz'),
        'port-mapping' => $g('port mapping', 'mapování portů', 'Propojení portu hostitele s portem v kontejneru (hostitel:kontejner).', 'provoz'),
        'idempotence' => $g('idempotence', 'opakovatelnost bez vedlejších změn', 'Opakované spuštění dá stejný výsledek a nic navíc nemění.', 'provoz', 'idempotent'),
        'dry-run' => $g('dry-run', 'běh nanečisto', 'Ukáže plánované změny, ale nic neprovede.', 'provoz'),
        'syn-ack' => $g('SYN-ACK', 'potvrzení navázání spojení', 'Odpověď TCP, že cíl spojení přijímá.', 'provoz'),
    ];
}

/** Termín podle id, nebo null. */
function lg72_term(string $id): ?array
{
    return lg72_terms()[$id] ?? null;
}

/** Termíny lekce (jen existující id, v pořadí lekce). @param list<string> $ids @return list<array<string,string>> */
function lg72_for_lesson(array $ids): array
{
    $out = [];
    foreach ($ids as $id) {
        $t = is_string($id) ? lg72_term($id) : null;
        if ($t !== null) $out[] = ['id' => $id] + $t;
    }
    return $out;
}

/** Vyskytuje se termín v textu? (bez ohledu na velikost písmen; `match` má přednost před `term`) */
function lg72_mentioned(string $id, string $text): bool
{
    $t = lg72_term($id);
    if ($t === null) return false;
    $needle = (string)($t['match'] ?? $t['term']);
    if (str_contains($needle, '(')) $needle = trim((string)strstr($needle, '(', true));
    return $needle !== '' && mb_stripos($text, $needle) !== false;
}
