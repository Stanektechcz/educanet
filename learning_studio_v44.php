<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

// v59 OPS-02: tr() i při načtení bez bootstrap.php (CLI audity).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCAnet v44 · Learning Studio & Visual Reasoning
 *
 * Extends v43 cognitive labs with multi-representation reasoning, teach-back,
 * personal memory cards, before/after mental-model snapshots and a compact
 * learning GPS. The layer is evidence-driven and never writes grades.
 */

function v44_family_bridge(string $family, string $classId): array
{
    $graphics = in_array($classId, ['class_1a','class_2a'], true);
    $common = [
        'big_idea' => $graphics
            ? 'Nejdřív hledej vztah mezi obsahem, prioritou a chováním rozhraní. Vizuální detail je až důsledek správné struktury.'
            : 'Nejdřív odděl vrstvy systému. Jeden pozorovaný symptom obvykle nedokazuje příčinu; potřebuješ test s vysokou informační hodnotou.',
        'analogy' => $graphics
            ? 'Představ si návrh jako navigaci v budově: člověk musí bez přemýšlení poznat, kam jít jako první a co je vedlejší.'
            : 'Představ si systém jako cestu zásilky přes několik překladišť. Každé překladiště může fungovat, selhat nebo předat chybnou informaci.',
        'analogy_limit' => $graphics
            ? 'Analogie neříká, že každý návrh musí být lineární. Reálné rozhraní může mít více rovnocenných cest podle úkolu uživatele.'
            : 'Analogie zjednodušuje paralelní děje, cache, retry a asynchronní komunikaci. V reálném systému mohou probíhat současně.',
        'markers' => $graphics ? ['obsah','hierarchie','uživatel','kontrast','ověř'] : ['vrstva','důkaz','test','výsledek','ověř'],
        'question' => $graphics ? 'Co se změnilo pro uživatele — a proč?' : 'Která vrstva se změnila — a co tím opravdu dokazujeme?',
    ];

    $map = [
        'layout' => ['big_idea'=>'Hierarchie a layout vedou pozornost. Správná kompozice zmenšuje počet rozhodnutí, která musí uživatel udělat.', 'analogy'=>'Layout je jako titulní strana novin: první pohled musí okamžitě odlišit hlavní zprávu od detailů.', 'analogy_limit'=>'Na rozdíl od novin je digitální rozhraní interaktivní a musí fungovat v různých velikostech i stavech.', 'markers'=>['hierarchie','spacing','pozice','priorita','uživatel']],
        'typography' => ['big_idea'=>'Typografie je systém rolí a rytmu, ne jen výběr fontu.', 'analogy'=>'Typografická hierarchie funguje jako hlas při mluvení: důraz, tempo a pauzy mění, co posluchač pochopí jako nejdůležitější.', 'analogy_limit'=>'Psaný text nemá intonaci; význam musí nést velikost, váha, spacing, struktura a kontext.', 'markers'=>['hierarchie','řádkování','délka','role','čitelnost']],
        'responsive' => ['big_idea'=>'Responsive design mění pravidla layoutu podle dostupného prostoru; není to zmenšený desktop.', 'analogy'=>'Je to jako přebalit stejný obsah do různě velkých kufrů: priorita zůstává, rozmístění se mění.', 'analogy_limit'=>'Obsah nemusí být vždy identický; někdy je správná i art direction nebo změna pořadí podle úkolu.', 'markers'=>['viewport','obsah','breakpoint','layout','priorita']],
        'accessibility' => ['big_idea'=>'Přístupnost znamená, že význam i ovládání zůstávají dostupné různým lidem a způsobům interakce.', 'analogy'=>'Je to jako dveře do budovy: nestačí, že vypadají dobře; musí se dát najít, otevřít a použít.', 'analogy_limit'=>'Digitální bariéry nejsou jen fyzické; patří sem semantika, fokus, kontrast, pohyb i kognitivní zátěž.', 'markers'=>['fokus','kontrast','label','klávesnice','význam']],
        'components' => ['big_idea'=>'Komponenta je opakovatelná smlouva o struktuře, stavech a pravidlech; tokeny drží konzistenci napříč systémem.', 'analogy'=>'Design systém je stavebnice: díly mají společné rozměry a pravidla, takže se dají bezpečně kombinovat.', 'analogy_limit'=>'Komponenty nejsou univerzální řešení všeho; špatná abstrakce může být stejně drahá jako duplicita.', 'markers'=>['token','komponenta','varianta','stav','konzistence']],
        'forms' => ['big_idea'=>'Formulář je dialog. Každé pole musí mít jasný účel, očekávání, stav a možnost zotavení z chyby.', 'analogy'=>'Je to jako rozhovor s úředníkem: potřebuješ vědět, na co se ptá, co je špatně a jak chybu opravit.', 'analogy_limit'=>'Formulář musí fungovat bez lidského doplňujícího vysvětlení, proto je semantika a mikrocopy kritická.', 'markers'=>['label','chyba','stav','validace','obnova']],
        'information' => ['big_idea'=>'Informační architektura organizuje obsah podle mentálního modelu uživatele, ne podle interní struktury firmy.', 'analogy'=>'Je to jako orientační systém v knihovně: dobré kategorie zkracují cestu k tomu, co člověk hledá.', 'analogy_limit'=>'Digitální obsah může být současně v několika kontextech a vyhledávání může strukturu doplňovat.', 'markers'=>['uživatel','kategorie','navigace','obsah','úkol']],
        'image' => ['big_idea'=>'Obraz musí nést správnou informaci i po změně velikosti, výřezu a média.', 'analogy'=>'Výřez fotografie je jako okno: posun okna mění, co divák považuje za hlavní objekt.', 'analogy_limit'=>'Vektor/raster, komprese a art direction přidávají technické limity, které analogie okna nezachytí.', 'markers'=>['výřez','focal','rozlišení','formát','kontext']],
        'handoff' => ['big_idea'=>'Handoff předává záměr a pravidla, ne pouze screenshot. Implementátor musí poznat strukturu, stavy a akceptační kritéria.', 'analogy'=>'Je to jako technický výkres: hezká vizualizace nestačí, pokud chybí rozměry, materiál a tolerance.', 'analogy_limit'=>'Digitální produkt se mění v čase; handoff je spíš společná smlouva než jednorázové předání.', 'markers'=>['specifikace','stav','token','akceptace','implementace']],
        'usability' => ['big_idea'=>'Usability ověřuje, zda skutečný uživatel dokáže dokončit úkol, ne zda návrh vypadá logicky autorovi.', 'analogy'=>'Je to jako testovat mapu s někým, kdo místo nezná — autorova znalost nesmí maskovat chyby.', 'analogy_limit'=>'Jedno pozorování není statistický důkaz; cílem je hledat opakující se tření a příčiny.', 'markers'=>['úkol','uživatel','pozorování','problém','ověření']],
        'motion' => ['big_idea'=>'Motion má vysvětlovat změnu stavu, vztah nebo kontinuitu; dekorativní pohyb nesmí přehlušit význam.', 'analogy'=>'Animace je jako gesto při vysvětlování: dobré gesto ukáže vztah, špatné jen odvádí pozornost.', 'analogy_limit'=>'Digitální pohyb musí respektovat výkon, reduced-motion a přesné načasování interakce.', 'markers'=>['stav','pohyb','změna','čas','význam']],
        'branding' => ['big_idea'=>'Identita je konzistentní systém významu a rozpoznatelnosti, ne soubor náhodně hezkých prvků.', 'analogy'=>'Je to jako hlas člověka: poznáš ho podle opakujících se znaků, ne jediné věty.', 'analogy_limit'=>'Značka vzniká i chováním produktu, obsahem a zkušeností, nejen vizuálními prvky.', 'markers'=>['identita','konzistence','barva','typografie','význam']],
        'portfolio' => ['big_idea'=>'Silné portfolio dokazuje způsob rozhodování, iteraci a dopad — ne jen finální obrázek.', 'analogy'=>'Je to laboratorní protokol: výsledek je důležitý, ale bez postupu nevíme, co autor skutečně umí.', 'analogy_limit'=>'Portfolio je kurátorovaný příběh; nemá dokumentovat každý krok bez priority.', 'markers'=>['problém','rozhodnutí','iterace','evidence','výsledek']],
        'dns' => ['big_idea'=>'DNS překládá jméno na adresu. Úspěch přes IP a selhání přes hostname izoluje problém před aplikační vrstvu.', 'analogy'=>'DNS je podobné kontaktům v telefonu: jméno musíš převést na číslo, než můžeš spojení zahájit.', 'analogy_limit'=>'DNS není jeden centrální seznam; existují cache, delegace, více typů záznamů a různé resolvery.', 'markers'=>['hostname','resolver','IP','dotaz','odpověď']],
        'dhcp' => ['big_idea'=>'DHCP vyjednává síťovou konfiguraci v několika stavech; klient před ACK ještě nemá potvrzenou konfiguraci.', 'analogy'=>'Je to jako nabídka pronájmu: nejdřív poptáš, dostaneš nabídku, požádáš o ni a teprve potom je potvrzena.', 'analogy_limit'=>'Síť může mít relay, více serverů, lease renewal a statické rezervace.', 'markers'=>['discover','offer','request','ack','lease']],
        'routing' => ['big_idea'=>'Routing rozhoduje o dalším hopu podle cílové adresy a routovací tabulky; gateway není obecný „internetový server“.', 'analogy'=>'Je to jako třídění zásilek podle směrovací tabulky: každý uzel vybírá další úsek cesty.', 'analogy_limit'=>'Reálné routování může být dynamické, asymetrické a ovlivněné politikami nebo metrikou.', 'markers'=>['cíl','route','gateway','prefix','hop']],
        'packet' => ['big_idea'=>'Paketová analýza odděluje, co bylo skutečně odesláno a přijato, od toho, co si myslíme, že aplikace udělala.', 'analogy'=>'Je to jako sledovat jednotlivé obálky na pásu místo číst souhrnnou zprávu systému.', 'analogy_limit'=>'Capture může chybět šifrovaný obsah, offload nebo provoz na jiné části cesty.', 'markers'=>['paket','SYN','ACK','zdroj','cíl']],
        'observability' => ['big_idea'=>'Observability spojuje metriky, logy a trasování tak, aby šlo z pozorovaných výstupů odvodit vnitřní stav systému.', 'analogy'=>'Je to palubní deska auta doplněná servisním logem: jeden budík málokdy vysvětlí příčinu.', 'analogy_limit'=>'Více telemetrie automaticky neznamená lepší diagnózu; záleží na správných signálech a kontextu.', 'markers'=>['metrika','log','latence','chyba','saturace']],
        'filesystem' => ['big_idea'=>'Kapacita filesystemu není jen počet volných gigabajtů; limitem mohou být inode, mount, práva nebo cesta zápisu.', 'analogy'=>'Je to sklad: může mít volnou podlahu, ale žádné volné přihrádky pro nové položky.', 'analogy_limit'=>'Filesystémy mají další vlastnosti jako quota, reserved blocks, overlay vrstvy a síťová úložiště.', 'markers'=>['disk','inode','mount','cesta','kapacita']],
        'permissions' => ['big_idea'=>'Přístup vzniká kombinací identity procesu, vlastníka, skupiny, mode bitů a případných dalších politik.', 'analogy'=>'Je to jako sada klíčů a oprávnění v budově: nestačí vědět, komu místnost patří.', 'analogy_limit'=>'ACL, capabilities, SELinux/AppArmor a kontejnery mohou standardní Unix práva dále měnit.', 'markers'=>['vlastník','skupina','práva','proces','přístup']],
        'systemd' => ['big_idea'=>'systemd spravuje životní cyklus služby, závislosti a stav; „proces existuje“ není totéž jako „služba je zdravá“.', 'analogy'=>'Je to provozní řád budovy: říká, co se má spustit, v jakém pořadí a co dělat při problému.', 'analogy_limit'=>'Aplikace může mít vlastní supervisor, container runtime nebo externí orchestraci.', 'markers'=>['unit','service','status','dependency','restart']],
        'logs' => ['big_idea'=>'Log je časově uspořádaná evidence událostí. Hodnota vzniká až propojením času, komponenty a symptomu.', 'analogy'=>'Je to deník provozu: jednotlivá věta dává smysl až v kontextu toho, co se dělo před a po ní.', 'analogy_limit'=>'Logy mohou být neúplné, zpožděné nebo samy o sobě zavádějící bez metrik a reprodukce.', 'markers'=>['čas','událost','služba','chyba','kontext']],
        'ssh' => ['big_idea'=>'SSH spojení má oddělenou síťovou dostupnost, identitu serveru, autentizaci a autorizaci.', 'analogy'=>'Je to vstup do zabezpečené budovy: nejdřív se k ní musíš dostat, pak ověřit budovu, prokázat identitu a mít oprávnění.', 'analogy_limit'=>'SSH podporuje různé metody autentizace, forwarding, bastiony a politiky, které analogie nezachytí.', 'markers'=>['port','host key','autentizace','klíč','oprávnění']],
        'firewall' => ['big_idea'=>'Firewall rozhoduje podle pravidel o toku provozu. Otevřený port aplikace ještě neznamená, že je dosažitelný z každé zóny.', 'analogy'=>'Je to kontrolní bod na trase: služba může čekat na cíli, ale cesta k ní může být zakázána.', 'analogy_limit'=>'Pravidla mohou být stavová, vícevrstvá a rozdělená mezi host, cloud, router i aplikaci.', 'markers'=>['pravidlo','zdroj','cíl','port','stav']],
        'service' => ['big_idea'=>'Funkční služba vyžaduje proces, správný bind/listener, dostupnost závislostí a validní odpověď.', 'analogy'=>'Je to obchod: nestačí, že je zaměstnanec uvnitř; dveře musí být otevřené a služba musí skutečně obsloužit zákazníka.', 'analogy_limit'=>'Služba může být distribuovaná, health check může měřit jen část funkce a odpověď může být cacheovaná.', 'markers'=>['proces','listener','port','health','odpověď']],
        'automation' => ['big_idea'=>'Bezpečná automatizace musí být předvídatelná, idempotentní, pozorovatelná a bezpečně selhat.', 'analogy'=>'Je to výrobní linka: každý krok musí mít jasný vstup, výstup a způsob zastavení při chybě.', 'analogy_limit'=>'Software může pracovat s nedeterministickými API, concurrency a částečnými selháními.', 'markers'=>['vstup','stav','chyba','idempotence','ověření']],
        'proxy' => ['big_idea'=>'Reverse proxy/TLS odděluje klientské spojení od upstreamu. Chyba 502 může vzniknout až za funkčním TLS a proxy procesem.', 'analogy'=>'Je to recepce, která přijme návštěvníka a teprve potom ho přepojí na konkrétní kancelář.', 'analogy_limit'=>'Proxy může dělat load balancing, cache, retry, transformace a více TLS terminací.', 'markers'=>['client','TLS','proxy','upstream','502']],
        'release' => ['big_idea'=>'Release je řízená změna systému. Bez pozorování a možnosti návratu nevíme, zda problém způsobila právě změna.', 'analogy'=>'Je to výměna součástky za provozu: potřebuješ měřit stav před, během a po změně a mít cestu zpět.', 'analogy_limit'=>'Distribuované releasy mohou mít více verzí současně a rollback nemusí vrátit data do původního stavu.', 'markers'=>['deploy','změna','metrika','rollback','ověření']],
        'backup' => ['big_idea'=>'Backup není hotový, dokud nebyl ověřen restore. Cílem není soubor zálohy, ale obnovitelná služba a data.', 'analogy'=>'Je to náhradní klíč: hodnotu má jen tehdy, když skutečně odemkne správné dveře ve správný okamžik.', 'analogy_limit'=>'Obnova zahrnuje pořadí systémů, konzistenci, RPO/RTO a závislosti, nejen jeden soubor.', 'markers'=>['backup','restore','RPO','RTO','ověření']],
        'iac' => ['big_idea'=>'Infrastructure as Code převádí očekávaný stav do verzované deklarace; drift je rozdíl mezi deklarací a realitou.', 'analogy'=>'Je to recept a skutečně uvařené jídlo: pokud kuchař něco změní bokem, výsledek už receptu neodpovídá.', 'analogy_limit'=>'Některé zdroje mají runtime stav a externí zásahy, které nelze bezpečně řídit čistou deklarací.', 'markers'=>['desired state','drift','plan','apply','verze']],
        'performance' => ['big_idea'=>'Výkon je řetězec limitů. Optimalizace má začít měřením bottlenecku, ne náhodnou změnou konfigurace.', 'analogy'=>'Je to doprava přes několik úzkých míst: rozšíření široké části silnice nepomůže, pokud kolona stojí jinde.', 'analogy_limit'=>'Bottleneck se může měnit se zatížením a latence vzniká součtem i frontami v několika vrstvách.', 'markers'=>['latence','throughput','saturace','bottleneck','měření']],
        'containers' => ['big_idea'=>'Kontejner izoluje proces a jeho filesystem/network namespace, ale sdílí kernel a závisí na hostu i orchestrace.', 'analogy'=>'Je to oddělená pracovní kabina ve stejné hale: vlastní prostor, ale společná budova a infrastruktura.', 'analogy_limit'=>'Izolace není plná virtualizace a síť, storage či cgroups mají vlastní komplexitu.', 'markers'=>['image','container','namespace','port','volume']],
        'incident' => ['big_idea'=>'Incident response minimalizuje dopad pomocí jasné evidence, hypotéz, bezpečných zásahů a ověření návratu služby.', 'analogy'=>'Je to práce záchranného týmu: nejdřív stabilizovat situaci, současně sbírat důkazy a komunikovat.', 'analogy_limit'=>'Technické incidenty mají nejasnou příčinu, paralelní týmy a změny stavu v reálném čase.', 'markers'=>['symptom','hypotéza','důkaz','zásah','validace']],
    ];

    return array_replace($common, $map[$family] ?? []);
}

function v44_studio_spec(string $classId, array $lesson, array $module): array
{
    $lab = cv43_lab_spec($classId, $lesson, $module);
    $bridge = v44_family_bridge((string)$lab['family'], $classId);
    $expected = array_values(array_map('strval', (array)($lab['model']['expected'] ?? [])));
    $bad = $expected;
    if (count($bad) >= 3) { [$bad[1],$bad[2]] = [$bad[2],$bad[1]]; }
    elseif (count($bad) >= 2) { $bad = array_reverse($bad); }
    $lessonNo = (int)($lesson['number'] ?? 0);
    $title = (string)($lesson['title'] ?? 'Lekce');

    return [
        'id' => 'v44-'.$classId.'-L'.str_pad((string)$lessonNo,2,'0',STR_PAD_LEFT),
        'class_id' => $classId,
        'lesson_number' => $lessonNo,
        'lesson_title' => $title,
        'topic' => (string)($lab['topic'] ?? ''),
        'family' => (string)($lab['family'] ?? ''),
        'renderer' => (string)($lab['renderer'] ?? 'flow'),
        'problem' => (string)($lab['problem'] ?? ''),
        'big_idea' => (string)$bridge['big_idea'],
        'analogy' => (string)$bridge['analogy'],
        'analogy_limit' => (string)$bridge['analogy_limit'],
        'teachback_markers' => array_values(array_unique(array_map('strval',(array)$bridge['markers']))),
        'teachback_question' => 'Vysvětli vlastními slovy: '.(string)$bridge['question'].' Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.',
        'representations' => [
            ['id'=>'reality','label'=>'Realita','kicker'=>'Konkrétní problém','title'=>$title,'text'=>(string)$lab['problem']],
            ['id'=>'xray','label'=>'X-Ray','kicker'=>'Co je uvnitř','title'=>'Vrstvy systému','text'=>'Rozděl problém na '.count((array)$lab['layers']).' vrstev a sleduj vždy jen jednu.'],
            ['id'=>'principle','label'=>'Princip','kicker'=>'Jedna myšlenka','title'=>'Co si z toho odnést','text'=>(string)$bridge['big_idea']],
            ['id'=>'contrast','label'=>'Kontrast','kicker'=>'Chybný vs. funkční model','title'=>'Rozdíl, který rozhoduje','text'=>(string)($lab['compare']['why'] ?? '')],
            ['id'=>'transfer','label'=>'Transfer','kicker'=>'Nový kontext','title'=>'Použij stejný princip jinde','text'=>(string)($lab['transfer'] ?? '')],
        ],
        'difference' => [
            'before' => $bad,
            'after' => $expected,
            'prompt' => 'Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?',
        ],
        'memory_seed' => [
            'sentence'=>(string)($lab['memory']['sentence'] ?? $bridge['big_idea']),
            'trap'=>(string)($lab['memory']['trap'] ?? ($lab['compare']['bad'] ?? '')),
            'cue'=>(string)($lab['memory']['retrieval'] ?? 'Jak bys tento princip poznal v nové situaci?'),
        ],
        'replay' => [
            ['at'=>90,'label'=>'Princip','text'=>(string)$bridge['big_idea']],
            ['at'=>60,'label'=>'Past','text'=>(string)($lab['compare']['bad'] ?? '')],
            ['at'=>30,'label'=>'Transfer','text'=>(string)($lab['transfer'] ?? '')],
        ],
        'model_expected' => $expected,
        'lab' => $lab,
    ];
}

function v44_teachback_rows(): array { return adaptive_store('v44_teachbacks'); }
function v44_memory_rows(): array { return adaptive_store('v44_memory_cards'); }
function v44_stage_rows(): array { return adaptive_store('v44_stages'); }

function v44_key(string $classId,string $studentKey,int $lessonNumber): string
{
    return $classId.'|'.$studentKey.'|L'.$lessonNumber;
}

function v44_teachback_submit(string $classId,string $studentKey,array $lesson,string $text): array
{
    $text=trim(u_substr($text,0,1800));
    if(u_strlen($text)<30) throw new RuntimeException('Vysvětlení je zatím příliš krátké. Zkus popsat vztah příčina → důkaz → závěr.');
    $spec=v44_studio_spec($classId,$lesson,$GLOBALS['modules'][$classId]);
    $lower=ml_lower($text);$markers=(array)$spec['teachback_markers'];$hits=[];
    foreach($markers as $m){$m=ml_lower((string)$m);if($m!==''&&str_contains($lower,$m))$hits[]=$m;}
    $causal=0;foreach(['proto','protože','důkaz','ověř','znamená','pokud','výsledek','příčin'] as $m)if(str_contains($lower,$m))$causal++;
    $markerScore=count($markers)?count(array_unique($hits))/count($markers):0;
    $lengthScore=min(1,max(0,(u_strlen($text)-30)/350));
    $score=(int)round(min(1,$markerScore*.6+min(1,$causal/3)*.3+$lengthScore*.1)*100);
    $state=$score>=72?'strong':($score>=42?'developing':'needs_review');
    $key=v44_key($classId,$studentKey,(int)$lesson['number']);
    storage_map_update(adaptive_store_path('v44_teachbacks'),$key,static fn(?array $current): array => ['id'=>$key,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$lesson['number'],'topic'=>$spec['topic'],'text'=>$text,'score'=>$score,'state'=>$state,'markers_hit'=>array_values(array_unique($hits)),'updated_at'=>date(DATE_ATOM)]);
    if(function_exists('ml_record_learning_event'))ml_record_learning_event($classId,$studentKey,(string)$spec['topic'],'visual_teachback',$state!=='needs_review',['lesson'=>(int)$lesson['number'],'score'=>$score,'visual_reasoning'=>true]);
    $feedback=$state==='strong'?'Vysvětlení obsahuje princip i vazbu na důkaz. Teď ho zkus použít bez nápovědy v transferu.':($state==='developing'?'Jádro je vidět. Doplň, jaký důkaz by tvůj závěr potvrdil nebo vyvrátil.':'Zatím spíš popisuješ výsledek. Zkus explicitně říct, co se změnilo, jak to poznáš a co z toho smíš tvrdit.');
    return ['score'=>$score,'state'=>$state,'feedback'=>$feedback,'markers_hit'=>array_values(array_unique($hits))];
}

function v44_memory_save(string $classId,string $studentKey,array $lesson,array $input): array
{
    $sentence=trim(u_substr((string)($input['sentence']??''),0,420));
    $trap=trim(u_substr((string)($input['trap']??''),0,420));
    $cue=trim(u_substr((string)($input['cue']??''),0,420));
    if(u_strlen($sentence)<12||u_strlen($trap)<8)throw new RuntimeException('Doplň jednu konkrétní větu a jednu typickou past.');
    $spec=v44_studio_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$key=v44_key($classId,$studentKey,(int)$lesson['number']);
    $saved=(array)storage_map_update(adaptive_store_path('v44_memory_cards'),$key,static fn(?array $current): array => ['id'=>$key,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$lesson['number'],'topic'=>$spec['topic'],'sentence'=>$sentence,'trap'=>$trap,'cue'=>$cue,'updated_at'=>date(DATE_ATOM)]);
    if(function_exists('ml_record_learning_event'))ml_record_learning_event($classId,$studentKey,(string)$spec['topic'],'memory_card',true,['lesson'=>(int)$lesson['number'],'visual_reasoning'=>true]);
    return $saved;
}

function v44_stage_complete(string $classId,string $studentKey,array $lesson,string $stage): array
{
    if(!in_array($stage,['experiment','compare','replay'],true))throw new RuntimeException('Neplatná fáze.');
    $key=v44_key($classId,$studentKey,(int)$lesson['number']).'|'.$stage;return (array)storage_map_update(adaptive_store_path('v44_stages'),$key,static fn(?array $current): array => ['id'=>$key,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$lesson['number'],'stage'=>$stage,'completed_at'=>date(DATE_ATOM)]);
}

function v44_student_state(string $classId,string $studentKey,array $lesson): array
{
    static $cache=[];
    $cacheKey=v44_key($classId,$studentKey,(int)$lesson['number']);
    if(isset($cache[$cacheKey]))return $cache[$cacheKey];
    $teach=v44_teachback_rows()[$cacheKey]??null;$memory=v44_memory_rows()[$cacheKey]??null;$cv=cv43_student_lab_state($classId,$studentKey,$lesson);
    $stage=[];foreach(v44_stage_rows() as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey&&(int)($r['lesson_number']??0)===(int)$lesson['number'])$stage[(string)$r['stage']]=true;
    $spec=v44_studio_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$transfer=false;
    if(function_exists('ml_student_events'))foreach(ml_student_events($classId,$studentKey,(string)$spec['topic']) as $e){if(is_array($e)&&!empty($e['correct'])&&in_array((string)($e['source']??''),['transfer','custom_scenario'],true)){$transfer=true;break;}}
    $models=cv43_model_rows();$before=$models[cv43_model_key($classId,$studentKey,(int)$lesson['number'],'before')]??null;$after=$models[cv43_model_key($classId,$studentKey,(int)$lesson['number'],'after')]??null;
    return $cache[$cacheKey]=['teachback'=>is_array($teach)?$teach:null,'memory'=>is_array($memory)?$memory:null,'cv43'=>$cv,'stage'=>$stage,'transfer'=>$transfer,'before'=>is_array($before)?$before:null,'after'=>is_array($after)?$after:null];
}

function v44_learning_gps(string $classId,string $studentKey,array $lesson): array
{
    $state=v44_student_state($classId,$studentKey,$lesson);$model=is_array($state['cv43']['model']??null)?$state['cv43']['model']:null;
    $steps=[
        ['id'=>'orient','label'=>tr('Předpověz'),'done'=>!empty($state['cv43']['prediction_done']),'hint'=>tr('Nejdřív vytvoř hypotézu.')],
        ['id'=>'experiment','label'=>tr('Změň'),'done'=>!empty($state['stage']['experiment']),'hint'=>tr('Manipuluj modelem a sleduj důsledek.')],
        ['id'=>'model','label'=>tr('Sestav model'),'done'=>is_array($model)&&!empty($model['correct']),'hint'=>tr('Seřaď části podle skutečného vztahu.')],
        ['id'=>'explain','label'=>tr('Vysvětli'),'done'=>is_array($state['teachback'])&&in_array((string)($state['teachback']['state']??''),['developing','strong'],true),'hint'=>tr('Popiš příčinu, důkaz a závěr.')],
        ['id'=>'transfer','label'=>tr('Použij jinde'),'done'=>!empty($state['transfer']),'hint'=>tr('Ověř princip v novém kontextu.')],
    ];
    $next=null;foreach($steps as $s)if(!$s['done']){$next=$s;break;}
    return ['steps'=>$steps,'next'=>$next,'complete'=>$next===null,'state'=>$state];
}

function v44_teacher_class_summary(string $classId,int $lessonNumber): array
{
    $students=project_students_for_class($classId);$studentKeys=array_fill_keys(array_keys($students),true);$total=count($studentKeys);
    $p=['prediction'=>0,'experiment'=>0,'model'=>0,'explain'=>0,'transfer'=>0];$teachStates=['strong'=>0,'developing'=>0,'needs_review'=>0];
    $lesson=v42_find_lesson($classId,$lessonNumber);if(!$lesson)return ['total'=>$total,'progress'=>$p,'teach_states'=>$teachStates,'bottleneck'=>'prediction','bottleneck_label'=>'Předpověď'];
    $spec=v44_studio_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$topic=(string)$spec['topic'];
    $done=['prediction'=>[],'experiment'=>[],'model'=>[],'explain'=>[],'transfer'=>[]];
    foreach(cv43_attempt_rows() as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(int)($r['lesson_number']??0)!==$lessonNumber)continue;$sk=(string)($r['student_key']??'');if($studentKeys&&!isset($studentKeys[$sk]))continue;$kind=(string)($r['kind']??'');if($kind==='prediction')$done['prediction'][$sk]=true;if($kind==='mental_model'&&!empty($r['correct'])&&(string)($r['meta']['phase']??'current')==='current')$done['model'][$sk]=true;}
    foreach(v44_stage_rows() as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(int)($r['lesson_number']??0)!==$lessonNumber||(string)($r['stage']??'')!=='experiment')continue;$sk=(string)($r['student_key']??'');if(!$studentKeys||isset($studentKeys[$sk]))$done['experiment'][$sk]=true;}
    foreach(v44_teachback_rows() as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(int)($r['lesson_number']??0)!==$lessonNumber)continue;$sk=(string)($r['student_key']??'');if($studentKeys&&!isset($studentKeys[$sk]))continue;$st=(string)($r['state']??'needs_review');if(isset($teachStates[$st]))$teachStates[$st]++;if(in_array($st,['strong','developing'],true))$done['explain'][$sk]=true;}
    if(function_exists('ml_store'))foreach(ml_store('events') as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(string)($r['topic']??'')!==$topic||empty($r['correct'])||!in_array((string)($r['source']??''),['transfer','custom_scenario'],true))continue;$sk=(string)($r['student_key']??'');if(!$studentKeys||isset($studentKeys[$sk]))$done['transfer'][$sk]=true;}
    foreach($done as $k=>$rows)$p[$k]=count($rows);
    $labels=['prediction'=>'Předpověď','experiment'=>'Manipulace','model'=>'Mentální model','explain'=>'Vysvětlení','transfer'=>'Transfer'];$bottleneck='prediction';$ratio=PHP_FLOAT_MAX;foreach($p as $k=>$count){$r=$total>0?$count/$total:0;if($r<$ratio){$ratio=$r;$bottleneck=$k;}}
    return ['total'=>$total,'progress'=>$p,'teach_states'=>$teachStates,'bottleneck'=>$bottleneck,'bottleneck_label'=>$labels[$bottleneck]??$bottleneck];
}
