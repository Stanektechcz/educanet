<?php

declare(strict_types=1);

/**
 * v35 · Reality Demos
 * Short, evidence-first situations derived from the actual lesson topic.
 * They never award XP/mastery; they are rehearsal before assessed work.
 */
function reality_demo_spec(string $classId, string $topic, array $article = []): array
{
    $graphics = in_array($classId, ['class_1a','class_2a'], true);
    $hay = strtolower($topic . ' ' . (string)($article['title'] ?? '') . ' ' . (string)($article['summary'] ?? ''));
    $base = assessment_visual_spec($classId, $topic, $hay);
    $kind = (string)($base['kind'] ?? ($graphics ? 'hierarchy' : 'decision'));
    $title = trim((string)($article['title'] ?? $topic));

    $s = [
        'topic' => $topic,
        'kind' => $kind,
        'scene' => $graphics ? 'design' : 'topology',
        'label' => 'Reálná situace',
        'title' => $title,
        'brief' => $graphics
            ? 'Návrh vypadá na první pohled hotově, ale v reálném použití selže jedna důležitá věc.'
            : 'Uživatel hlásí problém. Máš jen několik důkazů a musíš rozhodnout, co otestovat jako první.',
        'objective' => 'Najdi nejmenší ověřitelný krok, který oddělí domněnku od důkazu.',
        'known' => ['Máš konkrétní symptom.','Máš jeden pozorovatelný stav.'],
        'unknown' => ['Příčina zatím není potvrzená.'],
        'choices' => [],
        'correct' => 0,
        'good' => 'Správně. Změna řeší příčinu nebo přidává důkaz, ne jen maskuje symptom.',
        'bad' => 'Tohle může něco změnit, ale zatím nevíš, zda řešíš příčinu. Nejprve potřebuješ přesnější důkaz.',
        'proof' => (string)($base['evidence'] ?? 'Důkaz musí odpovídat tomu, co skutečně měříš.'),
        'not_proof' => 'Jeden úspěšný test neprokazuje funkčnost celé cesty.',
        'before' => 'Před zásahom',
        'after' => 'Po ověření',
    ];

    if ($graphics) {
        if (preg_match('/form|error-recovery|form-states/u', $hay)) {
            $s += [];
            $s['scene']='form'; $s['title']='Formulář, který „nic nedělá“';
            $s['brief']='Uživatel odešle formulář s chybným e-mailem. Stránka zůstane stejná a není jasné, co opravit.';
            $s['known']=['Pole e-mail obsahuje neplatný formát.','Submit proběhl, ale uživatel nevidí vazbu chyby na pole.'];
            $s['unknown']=['Zda uživatel pochopí, kde a jak chybu opravit.'];
            $s['choices']=[
                ['label'=>'Zobrazit chybu přímo u pole + konkrétní návod','result'=>'Pole dostane stav chyby, popis „Zadej e-mail ve tvaru jmeno@domena.cz“ a fokus se vrátí na problém.'],
                ['label'=>'Přidat červený toast „Chyba“ nahoru','result'=>'Uživatel ví, že něco selhalo, ale stále neví kde ani proč.'],
                ['label'=>'Zvětšit tlačítko Odeslat','result'=>'CTA je výraznější, příčina chyby ale zůstává stejná.'],
            ];
            $s['proof']='Inline chyba propojená s konkrétním polem zkracuje cestu k opravě a funguje i bez spoléhání jen na barvu.';
            $s['not_proof']='Červená barva sama o sobě nedokazuje srozumitelnost ani přístupnost.';
        } elseif (preg_match('/accessib|contrast/u', $hay)) {
            $s['scene']='design'; $s['title']='CTA je krásné, ale nejde přečíst';
            $s['brief']='Na světlé fotografii je tenký světlý text tlačítka. V mockupu působí elegantně, na projektoru a telefonu mizí.';
            $s['known']=['CTA je klíčová akce.','Text a pozadí mají nízký vizuální kontrast.'];
            $s['unknown']=['Zda CTA zůstane čitelné v různých podmínkách a pro různé uživatele.'];
            $s['choices']=[
                ['label'=>'Zvýšit skutečný kontrast textu/pozadí a znovu ověřit','result'=>'CTA zůstane rozpoznatelné i v grayscale a při horším displeji.'],
                ['label'=>'Přidat jemný shadow bez změny barev','result'=>'Může pomoct lokálně, ale kontrastní problém nemusí být vyřešen.'],
                ['label'=>'Přidat animaci tlačítka','result'=>'Pohyb zvýší pozornost, ne čitelnost.'],
            ];
            $s['proof']='Ověřuješ měřitelný rozdíl mezi textem a pozadím, ne subjektivní dojem.';
            $s['not_proof']='To, že CTA vidíš na svém monitoru, neprokazuje dostatečnou čitelnost obecně.';
        } elseif (preg_match('/responsive|art-direction|css-responsive/u', $hay)) {
            $s['scene']='responsive'; $s['title']='Desktop funguje, mobil rozbije obsah';
            $s['brief']='Hero má fixní dvousloupcový layout. Na 390 px se nadpis láme do pěti řádků a obličej ve fotografii zmizí mimo výřez.';
            $s['known']=['Desktop varianta je použitelná.','Na mobilu není dost horizontálního prostoru.'];
            $s['unknown']=['Které části mají změnit strukturu a které jen velikost.'];
            $s['choices']=[
                ['label'=>'Změnit layout podle breakpointu + upravit crop/focal point','result'=>'Obsah se skládá vertikálně, hlavní motiv zůstane viditelný a CTA má bezpečný prostor.'],
                ['label'=>'Zmenšit celý desktop na 45 %','result'=>'Všechno se vejde, ale text i ovládání jsou příliš malé.'],
                ['label'=>'Skrýt obrázek i sekundární text','result'=>'Problém zmizí, ale spolu s důležitým obsahem.'],
            ];
            $s['proof']='Responsive návrh zachovává prioritu obsahu, ne přesné desktopové souřadnice.';
            $s['not_proof']='To, že se nic horizontálně nepřelévá, ještě neznamená dobrou mobilní zkušenost.';
        } elseif (preg_match('/typograph|editorial|web-typography/u', $hay)) {
            $s['scene']='type'; $s['title']='Text je správně, ale nikdo ho nedočte';
            $s['brief']='Článek používá 14 px text, dlouhé řádky a těsný line-height. Obsah je věcně správný, čtení je ale únavné.';
            $s['known']=['Textový obsah je správný.','Délka řádku a vertikální rytmus zvyšují námahu při čtení.'];
            $s['unknown']=['Která kombinace parametrů udrží hierarchii i čitelnost.'];
            $s['choices']=[
                ['label'=>'Upravit velikost, line-height a maximální délku řádku jako systém','result'=>'Text má stabilní rytmus, rychlejší skenování a jasné role.'],
                ['label'=>'Použít tři další fonty pro pestrost','result'=>'Přibude variabilita, ale neřeší se čtecí rytmus.'],
                ['label'=>'Vše dát tučně','result'=>'Zmizí rozdíl mezi rolí nadpisu, textu a metadat.'],
            ];
            $s['proof']='Typografie se hodnotí jako systém rolí a rytmu, ne jako izolovaná velikost fontu.';
            $s['not_proof']='Větší font sám o sobě nezaručí čitelnost.';
        } elseif (preg_match('/information-architecture|card-sorting/u', $hay)) {
            $s['scene']='ia'; $s['title']='Uživatel neví, kam kliknout';
            $s['brief']='Navigace používá interní názvy oddělení. Uživatel hledá „vrácení zboží“, ale žádná položka tomu neodpovídá.';
            $s['known']=['Obsah existuje.','Názvy kategorií odpovídají organizaci firmy, ne mentálnímu modelu uživatele.'];
            $s['unknown']=['Jak lidé přirozeně seskupují a pojmenovávají tento obsah.'];
            $s['choices']=[
                ['label'=>'Ověřit seskupení/názvy krátkým card sortingem nebo tree testem','result'=>'Navigace se přeuspořádá podle skutečných očekávání uživatelů.'],
                ['label'=>'Přidat další položky do menu','result'=>'Více možností zvyšuje šum bez důkazu, že názvy dávají smysl.'],
                ['label'=>'Zmenšit font navigace','result'=>'Vizuální změna neřeší informační architekturu.'],
            ];
            $s['proof']='Struktura se ověřuje podle toho, zda uživatelé dokážou předvídat umístění obsahu.';
            $s['not_proof']='Interně logická struktura nemusí být srozumitelná návštěvníkovi.';
        } elseif (preg_match('/handoff|design-qa/u', $hay)) {
            $s['scene']='handoff'; $s['title']='Design vypadá hotově, implementace tápe';
            $s['brief']='Komponenta má jen ideální screenshot. Chybí loading, error, hover, focus a chování při dlouhém textu.';
            $s['known']=['Happy path je navržený.','Vývojář nemá specifikované okrajové stavy.'];
            $s['unknown']=['Jak komponenta reaguje na reálná data a interakce.'];
            $s['choices']=[
                ['label'=>'Doplnit state matrix + edge cases + tokeny/handoff poznámky','result'=>'Implementace má jednoznačný kontrakt pro běžné i chybové stavy.'],
                ['label'=>'Poslat větší PNG screenshot','result'=>'Více pixelů nedoplní chybějící behaviorální pravidla.'],
                ['label'=>'Nechat stavy rozhodnout až vývojáře','result'=>'Výsledek může fungovat, ale přestává být konzistentní s design systémem.'],
            ];
            $s['proof']='Kvalitní handoff popisuje chování a omezení, ne jen vzhled.';
            $s['not_proof']='Pixel-perfect screenshot není specifikace interakce.';
        } elseif (preg_match('/component|token|design-system|auto-layout/u', $hay)) {
            $s['scene']='component'; $s['title']='Jedna karta, pět různých verzí';
            $s['brief']='Každá stránka používá téměř stejnou kartu, ale s jiným spacingem, radiusem a stavem tlačítka.';
            $s['known']=['Vzory se opakují.','Odchylky vznikají ručním kopírováním.'];
            $s['unknown']=['Které vlastnosti jsou skutečné varianty a které nekonzistence.'];
            $s['choices']=[
                ['label'=>'Definovat komponentu, varianty a sdílené tokeny','result'=>'Změna tokenu se propíše konzistentně a varianty mají jasný význam.'],
                ['label'=>'Opravit každou kartu ručně','result'=>'Dnešní screenshot bude lepší, systém ale zůstane nekonzistentní.'],
                ['label'=>'Přidat další variantu pro každou výjimku','result'=>'Počet variant roste a komponenta přestává být předvídatelná.'],
            ];
            $s['proof']='Design systém snižuje počet nahodilých rozhodnutí a drží konzistenci napříč obrazovkami.';
            $s['not_proof']='Stejný název vrstvy neznamená stejnou komponentu.';
        } elseif (preg_match('/image|crop|photo|art-direction/u', $hay)) {
            $s['scene']='crop'; $s['title']='Fotografie funguje jen v jednom poměru stran';
            $s['brief']='Na desktopu je subjekt uprostřed. Mobilní 4:5 crop mu odřízne obličej a text překryje detail.';
            $s['known']=['Hlavní motiv má konkrétní focal point.','Cílové formáty mají různé poměry stran.'];
            $s['unknown']=['Jaký crop zachová význam v každém formátu.'];
            $s['choices']=[
                ['label'=>'Nastavit focal point a připravit art-directed crop pro cílové formáty','result'=>'Subjekt i textová bezpečná zóna přežijí změnu formátu.'],
                ['label'=>'Použít jeden center-crop všude','result'=>'Některé formáty vyjdou, jiné ztratí hlavní motiv.'],
                ['label'=>'Přidat větší text přes obrázek','result'=>'Konflikt s obrazem se ještě zvýší.'],
            ];
            $s['proof']='Art direction zachovává komunikační cíl napříč formáty, ne identický výřez.';
            $s['not_proof']='To, že desktopový crop funguje, neprokazuje správnost mobilního cropu.';
        } elseif (preg_match('/raster|vector|resolution|export|preflight|production/u', $hay)) {
            $s['scene']='export'; $s['title']='Logo je ostré v návrhu a rozmazané po exportu';
            $s['brief']='Grafika je exportovaná jako malý PNG a následně zvětšená na velký banner.';
            $s['known']=['Raster má konečné rozlišení.','Cílová velikost je větší než export.'];
            $s['unknown']=['Který formát a rozměr odpovídá cílovému použití.'];
            $s['choices']=[
                ['label'=>'Použít vektor pro logo / správný cílový raster a udělat preflight','result'=>'Hrany zůstanou ostré a datová velikost odpovídá médiu.'],
                ['label'=>'Zvětšit PNG v editoru','result'=>'Interpolace přidá pixely, ne skutečný detail.'],
                ['label'=>'Přidat sharpen filtr','result'=>'Hrany mohou působit ostřeji, původní detail se ale nevrátí.'],
            ];
            $s['proof']='Formát a rozlišení se volí podle typu grafiky a cílového výstupu.';
            $s['not_proof']='Velký počet kilobajtů sám o sobě nezaručuje vhodný export.';
        } elseif (preg_match('/microcopy|cta/u', $hay)) {
            $s['scene']='design'; $s['title']='Tlačítko „Pokračovat“ neříká, co se stane';
            $s['brief']='Na stránce jsou tři CTA se stejným slovem „Pokračovat“. Uživatel neví, zda odešle formulář, přejde na platbu nebo otevře detail.';
            $s['known']=['Akce mají rozdílné důsledky.','Popisky jsou stejné.'];
            $s['unknown']=['Zda uživatel dokáže před kliknutím předvídat výsledek.'];
            $s['choices']=[
                ['label'=>'Pojmenovat CTA podle výsledku akce','result'=>'„Odeslat přihlášku“, „Přejít k platbě“ a „Zobrazit detail“ snižují nejistotu.'],
                ['label'=>'Všechna CTA obarvit výrazněji','result'=>'Viditelnost roste, význam zůstává nejasný.'],
                ['label'=>'Přidat šipku ke každému tlačítku','result'=>'Ikona může naznačit směr, ale ne konkrétní důsledek.'],
            ];
            $s['proof']='Dobrá microcopy umožní předvídat důsledek akce ještě před kliknutím.';
            $s['not_proof']='Výrazné CTA nemusí být srozumitelné CTA.';
        } else {
            $s['scene']='design'; $s['title']='3sekundový test hierarchie';
            $s['brief']='Plakát obsahuje název, datum, místo i CTA, ale všechny prvky mají téměř stejný důraz.';
            $s['known']=['Všechny potřebné informace na návrhu jsou.','První pohled nemá jasný cíl.'];
            $s['unknown']=['Zda divák pochopí nejdůležitější sdělení během několika sekund.'];
            $s['choices']=[
                ['label'=>'Vytvořit jasné pořadí headline → klíčová informace → CTA','result'=>'Pohled má čitelnou cestu a méně prvků si konkuruje.'],
                ['label'=>'Zvětšit úplně všechno','result'=>'Poměr důrazu se nezmění, jen se zaplní více plochy.'],
                ['label'=>'Přidat další dekoraci do prázdného místa','result'=>'Přibude konkurence o pozornost bez zlepšení sdělení.'],
            ];
            $s['proof']='Hierarchie je pořadí pozornosti; ověřuje se rychlým pohledem, thumbnail testem a konzistencí rolí.';
            $s['not_proof']='Přítomnost všech informací neznamená, že je člověk přečte ve správném pořadí.';
        }
    } else {
        if (preg_match('/dns/u', $hay)) {
            $s['scene']='topology'; $s['title']='Web přes IP funguje, přes jméno ne';
            $s['brief']='Uživatel otevře portal.school.local a dostane chybu. Přímá IP 10.20.0.15 odpovídá.';
            $s['known']=['IP konektivita k serveru funguje.','Selhává použití jména.'];
            $s['unknown']=['Zda chybí DNS odpověď, je špatný resolver nebo chybný záznam.'];
            $s['choices']=[
                ['label'=>'Ověřit resolver a konkrétní DNS odpověď pomocí dig/nslookup','result'=>'Zjistíš, zda dotaz dostává správnou IP, NXDOMAIN nebo timeout.'],
                ['label'=>'Restartovat web server','result'=>'Aplikace na IP už odpovídá, restart neověřuje problém s překladem jména.'],
                ['label'=>'Vypnout firewall na serveru','result'=>'IP cesta už funguje; vypnutí firewallu je široký zásah bez důkazu.'],
            ];
            $s['proof']='Pokud IP funguje a DNS dotaz nevrací správnou odpověď, problém je před aplikační vrstvou.';
            $s['not_proof']='Funkční ping neprokazuje správný DNS překlad.';
        } elseif (preg_match('/dhcp/u', $hay)) {
            $s['scene']='topology'; $s['title']='Notebook dostal 169.254.x.x';
            $s['brief']='Po připojení do školní Wi‑Fi má klient adresu 169.254.23.8, chybí default gateway a internet nefunguje.';
            $s['known']=['Klient nemá očekávanou DHCP konfiguraci.','Link je aktivní.'];
            $s['unknown']=['Ve kterém kroku DORA komunikace odpověď mizí.'];
            $s['choices']=[
                ['label'=>'Zachytit/ověřit DHCP Discover → Offer → Request → ACK','result'=>'Zjistíš, zda server neodpovídá, nabídka se nevrací nebo ACK nedorazí.'],
                ['label'=>'Ručně nastavit náhodnou IP','result'=>'Klient možná začne komunikovat, ale DHCP problém zůstane a může vzniknout konflikt.'],
                ['label'=>'Restartovat prohlížeč','result'=>'Prohlížeč neřídí přidělení IP adresy.'],
            ];
            $s['proof']='APIPA 169.254.x.x je silný signál, že klient nedokončil očekávané DHCP přidělení.';
            $s['not_proof']='Samotná 169.254 adresa neříká, zda je chyba na klientovi, relay, serveru nebo cestě.';
        } elseif (preg_match('/ssh|sftp|ssh-key/u', $hay)) {
            $s['scene']='stack'; $s['title']='SSH vrací „Permission denied (publickey)“';
            $s['brief']='TCP/22 odpovídá a handshake proběhne, ale účet se nepřihlásí pomocí klíče.';
            $s['known']=['Síťová cesta k portu 22 funguje.','sshd odpovídá.','Selhává autentizace klíčem.'];
            $s['unknown']=['Zda klient používá správný klíč a server ho přijímá pro daný účet.'];
            $s['choices']=[
                ['label'=>'Použít ssh -vvv a ověřit nabízený klíč + authorized_keys/práva','result'=>'Oddělíš chybu identity, cesty ke klíči a serverových oprávnění.'],
                ['label'=>'Otevřít port 22 ve firewallu znovu','result'=>'Port už odpovídá; firewall není první podezření.'],
                ['label'=>'Restartovat celý server','result'=>'Široký zásah bez důkazu může problém skrýt a přidat výpadek.'],
            ];
            $s['proof']='Konkrétní SSH chyba lokalizuje problém hlouběji než prostý „nejde SSH“.';
            $s['not_proof']='Permission denied není totéž jako timeout nebo connection refused.';
        } elseif (preg_match('/tls|certificate/u', $hay)) {
            $s['scene']='stack'; $s['title']='HTTPS funguje jen s varováním certifikátu';
            $s['brief']='TCP/443 je dostupný, server odpovídá, ale prohlížeč hlásí nesoulad jména v certifikátu.';
            $s['known']=['Síť i TLS endpoint jsou dostupné.','Identita certifikátu nesedí na požadovaný hostname.'];
            $s['unknown']=['Zda je problém SAN/SNI, špatný vhost nebo nasazený nesprávný certifikát.'];
            $s['choices']=[
                ['label'=>'Ověřit SNI/hostname, SAN certifikátu a konfiguraci vhostu','result'=>'Zjistíš, který certifikát endpoint skutečně posílá a pro jaká jména platí.'],
                ['label'=>'Ignorovat varování v prohlížeči','result'=>'Aplikace se otevře, ale bezpečnostní problém zůstává.'],
                ['label'=>'Změnit DNS na libovolnou jinou IP','result'=>'Bez důkazu můžeš uživatele poslat na jiný server.'],
            ];
            $s['proof']='Úspěšný TCP/443 neprokazuje správnou TLS identitu.';
            $s['not_proof']='Platná časová perioda certifikátu sama neznamená správný hostname.';
        } elseif (preg_match('/backup|restore/u', $hay)) {
            $s['scene']='restore'; $s['title']='Backup existuje. Umíš ho ale obnovit?';
            $s['brief']='Produkční databáze je poškozená. Poslední noční backup má status SUCCESS, ale nikdy nebyl testovaně obnoven.';
            $s['known']=['Backup artefakt existuje.','Obnova je kritická pro RTO/RPO.'];
            $s['unknown']=['Zda je backup integritní, kompletní a skutečně obnovitelný.'];
            $s['choices']=[
                ['label'=>'Obnovit do izolovaného cíle a validovat data/aplikaci','result'=>'Změříš skutečný restore čas, integritu a datovou mezeru bez rizika pro produkci.'],
                ['label'=>'Přepsat produkci backupem bez testu','result'=>'Pokud je backup vadný, incident se může zhoršit.'],
                ['label'=>'Spolehnout se na zelený status backup jobu','result'=>'Úspěšné vytvoření souboru není důkaz úspěšného restore.'],
            ];
            $s['proof']='Backup je ověřený až testovaným restore a následnou validací.';
            $s['not_proof']='SUCCESS jobu neprokazuje použitelnost dat.';
        } elseif (preg_match('/storage|filesystem|disk|inode/u', $hay)) {
            $s['scene']='metrics'; $s['title']='„No space left“ při 35 % volné kapacity';
            $s['brief']='Aplikace nemůže vytvářet nové soubory. df -h ukazuje 65 % využití, ale zápis selhává.';
            $s['known']=['Bajtová kapacita není vyčerpaná.','Vytváření nových souborů selhává.'];
            $s['unknown']=['Zda jsou vyčerpané inode nebo je problém v mountu/kvótě.'];
            $s['choices']=[
                ['label'=>'Ověřit inode, mount a cílový filesystem místo dalšího df -h','result'=>'Najdeš odlišný limit, který běžná kapacita v GB neukazuje.'],
                ['label'=>'Smazat náhodný velký soubor','result'=>'Může uvolnit bajty, ale pokud chybí inode, příčina zůstane.'],
                ['label'=>'Restartovat aplikaci','result'=>'Restart neobnoví vyčerpané inode.'],
            ];
            $s['proof']='Různé storage metriky odpovídají na různé otázky: bajty, inode, mount, kvóty.';
            $s['not_proof']='df -h není kompletní důkaz o všech limitech filesystému.';
        } elseif (preg_match('/container/u', $hay)) {
            $s['scene']='stack'; $s['title']='Container běží, služba zvenku ne';
            $s['brief']='`docker ps` ukazuje container jako Up. Aplikace uvnitř odpovídá na 127.0.0.1:3000, ale host:8080 je nedostupný.';
            $s['known']=['Proces uvnitř containeru běží.','Lokální aplikace uvnitř odpovídá.'];
            $s['unknown']=['Zda je port publikovaný a zda aplikace binduje na správné rozhraní.'];
            $s['choices']=[
                ['label'=>'Ověřit bind address + port mapping + host listener','result'=>'Zjistíš, zda je problém uvnitř aplikace, v runtime mappingu nebo na hostu.'],
                ['label'=>'Restartovat Docker daemon','result'=>'Může způsobit širší výpadek a zatím nemáš důkaz, že runtime selhal.'],
                ['label'=>'Přidat další container se stejnou aplikací','result'=>'Nová instance nezaručí správné publikování portu.'],
            ];
            $s['proof']='Running container neprokazuje dostupnost služby přes host network path.';
            $s['not_proof']='`docker ps` je stav runtime objektu, ne end-to-end health check.';
        } elseif (preg_match('/monitor|golden|performance|capacity|latency|slo|observ/u', $hay)) {
            $s['scene']='metrics'; $s['title']='Aplikace je „up“, ale uživatelé čekají 8 sekund';
            $s['brief']='Health check je zelený, error rate nízký, ale p95 latency prudce roste a CPU saturace se blíží 100 %.';
            $s['known']=['Dostupnost není totéž co použitelnost.','Latency a saturation se mění současně.'];
            $s['unknown']=['Zda je bottleneck CPU, downstream služba nebo fronta práce.'];
            $s['choices']=[
                ['label'=>'Korelovat latency, traffic, errors a saturation + rozpad podle vrstvy','result'=>'Získáš časovou a kauzální stopu, kde se čekání skutečně tvoří.'],
                ['label'=>'Sledovat jen uptime','result'=>'Uptime zůstane zelený a problém uživatele neuvidíš.'],
                ['label'=>'Navýšit timeout všude','result'=>'Symptom se může posunout, bottleneck ale zůstane.'],
            ];
            $s['proof']='Golden signals dávají společný obraz o uživatelském dopadu a kapacitním limitu.';
            $s['not_proof']='Jedna metrika bez kontextu obvykle neurčí příčinu.';
        } elseif (preg_match('/packet|tcp/u', $hay)) {
            $s['scene']='topology'; $s['title']='Klient posílá SYN, SYN‑ACK se nikdy nevrátí';
            $s['brief']='Packet capture ukazuje opakované TCP SYN na :443, ale žádnou odpověď ze serveru.';
            $s['known']=['Klient pakety skutečně odesílá.','Na capture point se nevrací SYN‑ACK.'];
            $s['unknown']=['Zda je problém v cestě, firewallu, listeneru nebo návratové trase.'];
            $s['choices']=[
                ['label'=>'Porovnat capture na dalších bodech + ověřit listener/firewall/return path','result'=>'Postupně lokalizuješ, kde přesně packet mizí.'],
                ['label'=>'Vymazat DNS cache','result'=>'TCP pokus už míří na konkrétní IP, DNS není první vrstva problému.'],
                ['label'=>'Znovu načíst stránku desetkrát','result'=>'Získáš více stejných SYN retransmisí, ne nový důkaz.'],
            ];
            $s['proof']='Capture potvrzuje existenci konkrétního packetu na konkrétním místě v konkrétním čase.';
            $s['not_proof']='Absence odpovědi na klientovi neříká sama o sobě, na kterém hopu se ztratila.';
        } elseif (preg_match('/firewall|security|acl|zone/u', $hay)) {
            $s['scene']='topology'; $s['title']='Služba funguje lokálně, vzdáleně timeout';
            $s['brief']='`curl localhost:8080` na serveru funguje. Z klienta ve správné síti TCP spojení timeoutuje.';
            $s['known']=['Aplikace běží a lokálně naslouchá.','Vzdálená cesta k portu neprojde.'];
            $s['unknown']=['Zda port blokuje host firewall, síťový firewall nebo nesprávný bind.'];
            $s['choices']=[
                ['label'=>'Ověřit bind/listener a pravidla po cestě přes konkrétní testy','result'=>'Oddělíš host-local dostupnost od síťové politiky a bind address.'],
                ['label'=>'Vypnout všechny firewally','result'=>'Může to službu zpřístupnit, ale ztrácíš důkaz i bezpečnostní hranice.'],
                ['label'=>'Restartovat DNS','result'=>'Test už používá konkrétní port a lokální služba odpovídá.'],
            ];
            $s['proof']='Localhost úspěch potvrzuje aplikaci, ne vzdálenou network policy.';
            $s['not_proof']='Jednorázové otevření portu „any/any“ není správná diagnostika.';
        } elseif (preg_match('/systemd|process|service|proxy|bind|http|load-balanc/u', $hay)) {
            $s['scene']='stack'; $s['title']='Nginx vrací 502 Bad Gateway';
            $s['brief']='Frontend proxy odpovídá, ale upstream aplikace na 127.0.0.1:9000 je `connection refused`.';
            $s['known']=['Klient dosáhne na proxy.','Proxy nedokáže navázat spojení na upstream.'];
            $s['unknown']=['Zda app neběží, naslouchá jinde nebo má chybnou dependency.'];
            $s['choices']=[
                ['label'=>'Ověřit systemd stav + listener + log upstream aplikace','result'=>'Zjistíš, zda proces běží, kde naslouchá a proč případně skončil.'],
                ['label'=>'Restartovat Nginx','result'=>'Proxy už request přijímá; upstream problém tím nemusí zmizet.'],
                ['label'=>'Zvýšit proxy timeout','result'=>'Connection refused není pomalá odpověď, ale odmítnuté spojení.'],
            ];
            $s['proof']='502 + connection refused lokalizuje problém mezi proxy a upstream listener.';
            $s['not_proof']='Running Nginx neprokazuje running upstream aplikaci.';
        } elseif (preg_match('/rout|gateway|subnet|cidr|ipv6|vlan|arp|nat/u', $hay)) {
            $s['scene']='topology'; $s['title']='Gateway pingne, server v jiné VLAN ne';
            $s['brief']='Klient 192.168.10.42/24 dosáhne na gateway 192.168.10.1, ale 192.168.20.20 je nedostupný.';
            $s['known']=['Lokální L2/L3 cesta ke gateway funguje.','Cíl je v jiné síti.'];
            $s['unknown']=['Zda chybí route, policy, návratová cesta nebo je chybný prefix.'];
            $s['choices']=[
                ['label'=>'Ověřit prefix, route table, policy a return path po jednotlivých hopech','result'=>'Lokalizuješ, jestli host správně volí gateway a zda routing existuje oběma směry.'],
                ['label'=>'Změnit klientovi IP do VLAN serveru','result'=>'Obejdeš routing problém a zároveň můžeš porušit segmentaci.'],
                ['label'=>'Restartovat switch bez dalších testů','result'=>'Široký zásah bez důkazu nepomůže lokalizaci.'],
            ];
            $s['proof']='Prefix rozhoduje local/remote; routing a policy pak určují další hop.';
            $s['not_proof']='Funkční gateway neprokazuje dosažitelnost všech vzdálených sítí.';
        } elseif (preg_match('/incident|rollback|change|canary|runbook/u', $hay)) {
            $s['scene']='incident'; $s['title']='Po deployi vyskočí error rate na 18 %';
            $s['brief']='Pět minut po změně začnou některé requesty padat. Nová verze je jediná známá změna, ale ještě není potvrzená příčina.';
            $s['known']=['Změna časově koreluje s incidentem.','Uživatelé mají měřitelný dopad.'];
            $s['unknown']=['Zda chybu skutečně způsobila nová verze nebo souběžný problém.'];
            $s['choices']=[
                ['label'=>'Stabilizovat: porovnat baseline/canary a bezpečně rollbacknout při jasném zlepšení','result'=>'Minimalizuješ dopad a zároveň získáš silný experimentální důkaz o vlivu změny.'],
                ['label'=>'Nasadit ještě jednu změnu naslepo','result'=>'Přidáš další proměnnou a zhoršíš schopnost určit příčinu.'],
                ['label'=>'Čekat bez měření, jestli se to samo spraví','result'=>'Dopad pokračuje a nepřibývá žádný důkaz.'],
            ];
            $s['proof']='Rollback, který obnoví baseline, je silný důkaz korelace změny s incidentem; stále je potřeba postmortem příčiny.';
            $s['not_proof']='Časová korelace sama není definitivní root cause.';
        } elseif (preg_match('/automation|shell|bash|cron|idempoten|infrastructure-as-code|desired|drift|iac/u', $hay)) {
            $s['scene']='incident'; $s['title']='Skript funguje poprvé, podruhé rozbije konfiguraci';
            $s['brief']='Automatizace přidá stejný řádek do konfigurace při každém spuštění a následně služba nenastartuje.';
            $s['known']=['Skript nemá guard na current state.','Opakované spuštění mění systém znovu.'];
            $s['unknown']=['Jak upravit postup, aby byl bezpečně opakovatelný.'];
            $s['choices']=[
                ['label'=>'Nejdřív zjistit current state, měnit jen rozdíl a po změně validovat','result'=>'Opakované spuštění vede ke stejnému desired state bez dalšího poškození.'],
                ['label'=>'Zakázat druhé spuštění skriptu','result'=>'Obejdeš symptom, ale automatizace zůstane křehká a neobnovitelná.'],
                ['label'=>'Po každém běhu restartovat celý server','result'=>'Restart neřeší nekorektní desired state.'],
            ];
            $s['proof']='Idempotentní automatizace mění systém pouze tehdy, když se current state liší od desired state.';
            $s['not_proof']='To, že první běh prošel, neprokazuje bezpečnost opakovaného běhu.';
        } else {
            $s['scene']='incident'; $s['title']='Symptom není příčina';
            $s['brief']='Služba nefunguje a tým má několik teorií. Každý navrhuje jiný restart nebo změnu konfigurace.';
            $s['known']=['Existuje reprodukovatelný symptom.','Příčina zatím není potvrzená.'];
            $s['unknown']=['Která vrstva selhává a jaký test ji umí odlišit od ostatních.'];
            $s['choices']=[
                ['label'=>'Formulovat jednu hypotézu a udělat nejmenší rozlišovací test','result'=>'Každý krok přidává informaci a zužuje prostor možných příčin.'],
                ['label'=>'Restartovat vše najednou','result'=>'Symptom možná zmizí, ale příčina i důkaz se ztratí.'],
                ['label'=>'Změnit několik nastavení současně','result'=>'Nebudeš vědět, která změna měla vliv.'],
            ];
            $s['proof']='Diagnostika je řízené snižování nejistoty pomocí testů.';
            $s['not_proof']='Zmizení symptomu po širokém restartu samo nevysvětluje root cause.';
        }
    }

    return $s;
}

function reality_demo_scene(array $s): void
{
    $scene=(string)($s['scene']??'topology');
    if (in_array($scene,['topology'],true)) {
        ?>
        <div class="reality-stage topology-stage" data-reality-stage>
          <div class="topo-node client"><i>◉</i><strong>Klient</strong><small>požadavek</small></div>
          <div class="topo-link l1"><span></span></div>
          <div class="topo-node middle"><i>◇</i><strong><?= e(in_array((string)($s['kind']??''),['dns','dhcp'],true)?strtoupper((string)$s['kind']):'Gateway') ?></strong><small>rozhodnutí</small></div>
          <div class="topo-link l2"><span></span></div>
          <div class="topo-node target"><i>□</i><strong>Server</strong><small>cílová služba</small></div>
          <div class="reality-packet" aria-hidden="true"></div>
          <div class="stage-status bad" data-stage-status>symptom reprodukován</div>
        </div>
        <?php
    } elseif (in_array($scene,['stack'],true)) {
        ?>
        <div class="reality-stage stack-stage" data-reality-stage>
          <div class="stack-layer ok"><span>1</span><strong>Síť / TCP</strong><small>ověřeno</small></div>
          <div class="stack-layer current"><span>2</span><strong>Služba / listener</strong><small>tady hledáme důkaz</small></div>
          <div class="stack-layer pending"><span>3</span><strong>Autentizace / upstream</strong><small>zatím nevíme</small></div>
          <div class="stack-layer pending"><span>4</span><strong>Aplikace</strong><small>zatím nevíme</small></div>
          <div class="stage-status bad" data-stage-status>cesta není kompletní</div>
        </div>
        <?php
    } elseif (in_array($scene,['metrics'],true)) {
        ?>
        <div class="reality-stage metrics-stage" data-reality-stage>
          <div class="metric-card"><span>Dostupnost</span><strong>99.9 %</strong><i class="good"></i></div>
          <div class="metric-card"><span>Latency p95</span><strong>8.2 s</strong><i class="bad"></i></div>
          <div class="metric-card"><span>Saturation</span><strong>97 %</strong><i class="bad"></i></div>
          <div class="metric-chart" aria-hidden="true"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b></div>
          <div class="stage-status bad" data-stage-status>jedna zelená metrika nestačí</div>
        </div>
        <?php
    } elseif (in_array($scene,['incident','restore'],true)) {
        ?>
        <div class="reality-stage incident-stage" data-reality-stage>
          <div class="incident-line"><span class="dot ok"></span><div><strong>14:00 · baseline</strong><small>normální stav</small></div></div>
          <div class="incident-line"><span class="dot change"></span><div><strong>14:05 · změna</strong><small>nová proměnná</small></div></div>
          <div class="incident-line"><span class="dot bad"></span><div><strong>14:10 · symptom</strong><small>uživatelský dopad</small></div></div>
          <div class="incident-line future"><span class="dot"></span><div><strong>další krok?</strong><small>vyber zásah podle důkazu</small></div></div>
          <div class="stage-status bad" data-stage-status>příčina není potvrzená</div>
        </div>
        <?php
    } elseif ($scene==='form') {
        ?>
        <div class="reality-stage form-stage" data-reality-stage>
          <div class="mini-form"><label>E-mail<input value="student@" readonly></label><span class="form-error" data-form-error hidden>Zadej e-mail ve tvaru jmeno@domena.cz</span><button type="button">Odeslat přihlášku</button></div>
          <div class="stage-status bad" data-stage-status>chyba bez vysvětlení</div>
        </div>
        <?php
    } elseif ($scene==='responsive') {
        ?>
        <div class="reality-stage responsive-stage" data-reality-stage>
          <div class="device desktop"><span>1440</span><div><strong>STUDENT<br>DESIGN LAB</strong><p>Obsah + fotografie</p><button>Registrace</button></div></div>
          <div class="device phone"><span>390</span><div><strong>STUDENT DESIGN LAB</strong><p>Obsah + fotografie</p><button>Registrace</button></div></div>
          <div class="stage-status bad" data-stage-status>desktop pravidla se nevejdou na mobil</div>
        </div>
        <?php
    } elseif ($scene==='type') {
        ?>
        <div class="reality-stage type-stage" data-reality-stage>
          <article><span>REPORT</span><h4>Jak navrhovat čitelné rozhraní</h4><p>Typografie není pouze volba fontu. Velikost, délka řádku, řádkování a hierarchie společně rozhodují o tom, jestli text opravdu přečteme bez zbytečné námahy a zda dokážeme rychle rozlišit role jednotlivých informací.</p></article>
          <div class="stage-status bad" data-stage-status>dlouhý řádek · těsný rytmus</div>
        </div>
        <?php
    } elseif ($scene==='ia') {
        ?>
        <div class="reality-stage ia-stage" data-reality-stage>
          <nav><button>Oddělení A</button><button>Procesy</button><button>Podpora</button><button>Dokumentace</button></nav>
          <div class="search-intent"><span>Uživatel hledá</span><strong>„Jak vrátím zboží?“</strong><small>Kam bys klikl?</small></div>
          <div class="stage-status bad" data-stage-status>názvy odpovídají firmě, ne uživateli</div>
        </div>
        <?php
    } elseif ($scene==='handoff' || $scene==='component') {
        ?>
        <div class="reality-stage handoff-stage" data-reality-stage>
          <div class="component-preview"><span>COMPONENT</span><strong>Card / Default</strong><button>Pokračovat</button></div>
          <div class="state-list"><span class="ok">Default ✓</span><span>Error ?</span><span>Loading ?</span><span>Focus ?</span><span>Long text ?</span></div>
          <div class="stage-status bad" data-stage-status>happy path není celý komponent</div>
        </div>
        <?php
    } elseif ($scene==='crop') {
        ?>
        <div class="reality-stage crop-stage" data-reality-stage>
          <div class="crop-photo"><span class="crop-person"></span><strong>SUMMER<br>WORKSHOP</strong><i class="crop-window"></i></div>
          <div class="stage-status bad" data-stage-status>mobilní crop ztrácí focal point</div>
        </div>
        <?php
    } elseif ($scene==='export') {
        ?>
        <div class="reality-stage export-stage" data-reality-stage>
          <div class="export-mark raster-mark">A</div><div class="export-arrow">× 6</div><div class="export-mark zoomed-mark">A</div>
          <div class="export-meta"><span>PNG 320 px</span><strong>→ banner 1920 px</strong></div>
          <div class="stage-status bad" data-stage-status>interpolace nevytvoří nový detail</div>
        </div>
        <?php
    } else {
        ?>
        <div class="reality-stage design-stage" data-reality-stage>
          <div class="design-poster"><span>EDUCANET</span><strong>STUDENT<br>NIGHT</strong><p>PÁTEK · 18:00 · BRNO</p><button>PŘIJĎ TAKY</button><i class="eye-path"></i></div>
          <div class="stage-status bad" data-stage-status>více prvků soutěží o první pohled</div>
        </div>
        <?php
    }
}

function render_reality_demo(string $classId, string $topic, array $article = []): void
{
    $s = reality_demo_spec($classId,$topic,$article);
    $choices = is_array($s['choices']??null)?$s['choices']:[];
    ?>
    <section class="reality-demo" data-reality-demo data-scene="<?= e((string)$s['scene']) ?>" data-topic="<?= e($topic) ?>">
      <header class="reality-demo-head">
        <div><span><?= e((string)$s['label']) ?></span><h3><?= e((string)$s['title']) ?></h3><p><?= e((string)$s['brief']) ?></p></div>
        <div class="reality-head-actions"><div class="reality-badge">nanečisto · bez XP</div><button type="button" class="reality-motion-toggle" data-reality-motion-toggle aria-pressed="true" title="Omezit pohyb animací">Pohyb: auto</button></div>
      </header>
      <div class="reality-phases" aria-label="Postup situace"><span class="active">1 · problém</span><span>2 · důkaz</span><span>3 · rozhodnutí</span><span>4 · výsledek</span></div>
      <div class="reality-layout">
        <div class="reality-main">
          <?php reality_demo_scene($s); ?>
          <div class="reality-evidence-grid">
            <div><span>Co už víme</span><?php foreach((array)$s['known'] as $v):?><p>✓ <?=e((string)$v)?></p><?php endforeach;?></div>
            <div><span>Co zatím nevíme</span><?php foreach((array)$s['unknown'] as $v):?><p>○ <?=e((string)$v)?></p><?php endforeach;?></div>
          </div>
        </div>
        <aside class="reality-decision">
          <span>Teď rozhodni</span><h4><?= e((string)$s['objective']) ?></h4>
          <div class="reality-choice-list">
            <?php foreach($choices as $i=>$choice): if(!is_array($choice))continue; ?>
              <button type="button" data-reality-choice="<?= $i ?>" data-correct="<?= $i===(int)$s['correct']?'1':'0' ?>"><i><?= chr(65+$i) ?></i><span><?= e((string)($choice['label']??'')) ?></span></button>
            <?php endforeach; ?>
          </div>
          <div class="reality-feedback" data-reality-feedback hidden aria-live="polite"><strong></strong><p></p></div>
          <button type="button" class="reality-reset" data-reality-reset hidden>Zkusit znovu</button>
        </aside>
      </div>
      <div class="reality-explanation" data-reality-explanation hidden>
        <div><span>Co tento výsledek dokazuje</span><p><?= e((string)$s['proof']) ?></p></div>
        <div><span>Co z něj ještě tvrdit nemůžeš</span><p><?= e((string)$s['not_proof']) ?></p></div>
      </div>
      <script type="application/json" data-reality-config><?= json_encode(['good'=>$s['good'],'bad'=>$s['bad'],'choices'=>$choices],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
    </section>
    <?php
}
