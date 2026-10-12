<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · banka otázek výukových cest 4.A – záloha a obnova (kategorie `backup`, kompetence ops_backup).
 * Statická data ve tvaru banky týmových her (single/bool/numeric), čte je jen p63_bank() (týmové hry ne). Výukový obsah (česky).
 * Číselné odpovědi ověřuje výpočtem tools/v75_competency_audit.php (RPO = interval záloh, objem přírůstků, doba obnovy).
 */

$q = static fn(string $id, string $type, string $prompt, array $options, mixed $answer, string $explain): array => ['id' => 'ops.backup.' . $id, 'line' => 'paths', 'classes' => null,
    'category' => 'backup', 'difficulty' => 2, 'type' => $type, 'prompt' => $prompt, 'options' => $options, 'answer' => $answer, 'tolerance' => $type === 'numeric' ? 0 : null,
    'accept' => [], 'explain' => $explain, 'time_s' => 40, 'source' => 'v75', 'status' => 'published'];

return [
    $q('001', 'single', 'Co vyjadřuje RPO (Recovery Point Objective)?', ['kolik dat (času) smíme nejvýš ztratit', 'jak dlouho smí obnova trvat', 'kolik kopií zálohy máme', 'jak velký je disk se zálohou'], 'kolik dat (času) smíme nejvýš ztratit', 'RPO je nejstarší bod, ke kterému se smíme vrátit – určuje, jak často je nutné zálohovat.'),
    $q('002', 'single', 'Co vyjadřuje RTO (Recovery Time Objective)?', ['jak dlouho smí obnova služby nejvýš trvat', 'kolik dat smíme ztratit', 'jak často mažeme staré zálohy', 'kolik stojí úložiště'], 'jak dlouho smí obnova služby nejvýš trvat', 'RTO je čas od výpadku do obnovené služby.'),
    $q('003', 'numeric', 'Záloha běží každé 4 hodiny. Kolik hodin dat nejvýš ztratíš, když disk selže těsně před další zálohou?', [], 4, 'Nejhorší případ je celý interval mezi zálohami: 4 hodiny.'),
    $q('004', 'numeric', 'Firma požaduje RPO 15 minut. Kolikrát za den (24 h) musí nejméně proběhnout záloha nebo replikace?', [], 96, '24 h × 60 min / 15 min = 96 záloh denně.'),
    $q('005', 'single', 'Co znamená pravidlo zálohování 3-2-1?', ['3 kopie dat, 2 různá média, 1 kopie mimo budovu', '3 zálohy denně, 2 týdně, 1 měsíčně', '3 disky v RAID, 2 servery, 1 cloud', '3 administrátoři, 2 hesla, 1 klíč'], '3 kopie dat, 2 různá média, 1 kopie mimo budovu', 'Pravidlo chrání proti selhání média i proti požáru nebo krádeži v jedné budově.'),
    $q('006', 'bool', 'RAID 1 (zrcadlení) nahrazuje zálohu, protože data jsou na dvou discích.', [], false, 'RAID chrání před selháním disku, ale smazání nebo zašifrování souboru se zrcadlí na oba disky – záloha je samostatná kopie v čase.'),
    $q('007', 'single', 'Čím se liší přírůstková (inkrementální) záloha od plné?', ['ukládá jen změny od poslední zálohy', 'ukládá vždy všechna data', 'ukládá jen systémové soubory', 'neukládá se na disk'], 'ukládá jen změny od poslední zálohy', 'Přírůstková záloha je rychlá a malá, k obnově je ale potřeba plná záloha a všechny přírůstky po ní.'),
    $q('008', 'numeric', 'Plná záloha má 200 GB a každý den přibude přírůstek 5 GB. Kolik GB zabere plná záloha a 6 denních přírůstků?', [], 230, '200 + 6 × 5 = 230 GB.'),
    $q('009', 'numeric', 'Obnova přenáší 360 GB rychlostí 2 GB za minutu. Kolik minut potrvá samotný přenos?', [], 180, '360 / 2 = 180 minut (3 hodiny) – samotný přenos už překročí RTO 2 hodiny.'),
    $q('010', 'bool', 'Záloha, ze které se nikdy nezkoušela obnova, je ověřená záloha.', [], false, 'Záloha je ověřená, až když z ní obnovíš data a zkontroluješ je (např. kontrolním součtem nebo spuštěním služby).'),
    $q('011', 'single', 'Proč se doporučuje mít aspoň jednu zálohu offline nebo neměnnou (immutable)?', ['ransomware ji nezašifruje ani nesmaže', 'je rychlejší', 'zabere méně místa', 'nemusí se šifrovat'], 'ransomware ji nezašifruje ani nesmaže', 'Útočník s přístupem do sítě často maže i dostupné zálohy; offline nebo neměnná kopie přežije.'),
    $q('012', 'single', 'Čím ověříš, že obnovený soubor je shodný s originálem?', ['porovnám kontrolní součet (např. SHA-256)', 'podívám se na název souboru', 'zkontroluji datum v kalendáři', 'stačí, že se soubor otevřel'], 'porovnám kontrolní součet (např. SHA-256)', 'Shodný otisk SHA-256 znamená shodný obsah bajt po bajtu.'),
    $q('013', 'numeric', 'Uchováváš 7 denních, 4 týdenní a 12 měsíčních záloh. Kolik bodů obnovy máš celkem k dispozici?', [], 23, '7 + 4 + 12 = 23 bodů obnovy.'),
    $q('014', 'bool', 'Záloha databáze kopírováním souborů za běhu bez zastavení zápisů může být nekonzistentní.', [], true, 'Za běhu se soubory mění; použij nástroj databáze (dump) nebo snapshot v konzistentním stavu.'),
    $q('015', 'single', 'Co patří do runbooku obnovy?', ['kroky obnovy, kdo je provádí, kde jsou klíče a jak ověřit výsledek', 'jen seznam disků', 'heslo administrátora v čitelné podobě', 'reklama na zálohovací software'], 'kroky obnovy, kdo je provádí, kde jsou klíče a jak ověřit výsledek', 'Runbook má zvládnout i člověk ve stresu ve 3 ráno; hesla patří do trezoru, ne do textu.'),
    $q('016', 'numeric', 'Disk se zálohami má 2 000 GB, plná záloha 300 GB a týdně se dělá jedna plná. Kolik celých plných záloh se na disk vejde?', [], 6, '2000 / 300 = 6,67, celých záloh je tedy 6.'),
    $q('017', 'single', 'Kdy je vhodné zálohu šifrovat?', ['vždy, když opouští chráněný server nebo jde mimo budovu', 'nikdy, šifrování zálohu poškodí', 'jen u obrázků', 'jen když je menší než 1 GB'], 'vždy, když opouští chráněný server nebo jde mimo budovu', 'Záloha obsahuje stejně citlivá data jako originál; klíč se uchovává odděleně od zálohy.'),
    $q('018', 'bool', 'Snapshot na stejném úložišti jako originál chrání před požárem serverovny.', [], false, 'Snapshot zmizí spolu s úložištěm – kopie mimo budovu je samostatný bod pravidla 3-2-1.'),
    $q('019', 'numeric', 'Služba spadla v 10:00, poslední záloha byla v 6:00 a obnova skončila ve 12:30. Kolik hodin dat se ztratilo (rozdíl výpadku a poslední zálohy)?', [], 4, '10:00 − 6:00 = 4 hodiny ztracených dat (skutečné RPO). Výpadek trval 2,5 h (skutečné RTO).'),
    $q('020', 'single', 'Jak často je rozumné zkoušet obnovu ze zálohy (game day)?', ['pravidelně, např. jednou za měsíc nebo po velké změně', 'jen jednou při instalaci', 'nikdy, je to riskantní', 'jen když už došlo k havárii'], 'pravidelně, např. jednou za měsíc nebo po velké změně', 'Pravidelný nácvik ověří zálohy, runbook i lidi – a změří skutečné RTO.'),
    $q('021', 'numeric', 'Rozdílová (diferenciální) záloha ukládá změny od poslední plné. Plná je v neděli, v pondělí přibude 3 GB změn, v úterý dalších 2 GB. Kolik GB má úterní rozdílová záloha?', [], 5, 'Rozdílová záloha obsahuje všechny změny od plné zálohy: 3 + 2 = 5 GB.'),
    $q('022', 'single', 'Co uděláš jako první, když zjistíš, že poslední tři noční zálohy selhaly?', ['spustím ruční zálohu, ověřím ji a zjistím příčinu selhání', 'smažu staré zálohy, aby bylo místo', 'počkám, jestli další noc projde', 'vypnu monitoring záloh'], 'spustím ruční zálohu, ověřím ji a zjistím příčinu selhání', 'Nejdřív obnovit ochranu dat (RPO roste každou hodinou), pak opravit příčinu a doplnit upozornění.'),
];
