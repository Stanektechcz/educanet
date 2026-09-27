<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v45 · Visual Simulation Engine
 *
 * A single simulation contract covers all 112 structured lessons. Lesson-specific
 * cognitive LabSpec (v43) supplies the concrete problem and mental model, while
 * v45 adds manipulable parameters, deterministic live evaluation, A/B snapshots,
 * what-if perturbations and teacher projection metadata.
 */

function v45_is_graphics(string $classId): bool
{
    return in_array($classId, ['class_1a','class_2a'], true);
}

function v45_param(string $id,string $label,float $min,float $max,float $value,float $target,float $step=1,string $unit='',string $hint=''): array
{
    return ['id'=>$id,'label'=>$label,'type'=>'range','min'=>$min,'max'=>$max,'value'=>$value,'target'=>$target,'step'=>$step,'unit'=>$unit,'hint'=>$hint];
}

function v45_family_profile(string $family,string $classId): array
{
    $g=v45_is_graphics($classId);
    $base=$g ? [
        'metric_labels'=>['quality'=>'Funkčnost','clarity'=>'Čitelnost','resilience'=>'Adaptivita','risk'=>'Riziko'],
        'params'=>[
            v45_param('density','Hustota',20,100,74,48,1,'%','Kolik informací soutěží o pozornost.'),
            v45_param('contrast','Kontrast',1,10,3.1,5.2,.1,':1','Rozdíl mezi klíčovým obsahem a okolím.'),
            v45_param('spacing','Spacing',4,48,12,24,1,'px','Prostor, který pomáhá seskupovat obsah.'),
            v45_param('viewport','Viewport',280,1440,1200,390,10,'px','Dostupný prostor komponenty.'),
        ],
        'renderer'=>'design',
    ] : [
        'metric_labels'=>['quality'=>'Dostupnost','clarity'=>'Evidence','resilience'=>'Odolnost','risk'=>'Riziko'],
        'params'=>[
            v45_param('latency','Latence',5,800,280,80,5,' ms','Doba odpovědi sledované vrstvy.'),
            v45_param('loss','Ztráta / chyby',0,30,8,1,.5,'%','Míra neúspěšných požadavků nebo paketů.'),
            v45_param('capacity','Kapacita',10,100,46,78,1,'%','Rezerva zdrojů nebo propustnosti.'),
            v45_param('signal','Kvalita evidence',0,100,35,80,1,'%','Jak přesně měření odděluje jednotlivé hypotézy.'),
        ],
        'renderer'=>'system',
    ];

    $overrides=[];
    switch($family){
        case 'typography':
            $overrides=['params'=>[
                v45_param('font_size','Velikost textu',12,72,28,42,1,'px','Rozdíl rolí textu.'),
                v45_param('line_height','Řádkování',1,2.2,1.15,1.55,.05,'×','Rytmus a čitelnost řádků.'),
                v45_param('line_width','Délka řádku',28,110,96,66,1,' znaků','Příliš dlouhý řádek zhoršuje návrat oka.'),
                v45_param('contrast','Kontrast',1,10,3,5,.1,':1','Rozlišení hlavního a sekundárního textu.'),
            ]]; break;
        case 'responsive':
            $overrides=['params'=>[
                v45_param('viewport','Viewport',280,1440,1280,390,10,'px','Sleduj bod, kde se obsah přestává vejít.'),
                v45_param('columns','Počet sloupců',1,4,2,1,1,'','Na úzkém prostoru je důležitější priorita než hustota.'),
                v45_param('gap','Gap',4,48,28,16,1,'px','Mezera mezi prvky musí odpovídat prostoru.'),
                v45_param('min_item','Min. šířka prvku',120,520,340,220,10,'px','Určuje, kdy má layout přejít do jiné struktury.'),
            ]]; break;
        case 'accessibility':
            $overrides=['params'=>[
                v45_param('contrast','Kontrast',1,10,2.4,5,.1,':1','Čitelnost textu a stavů.'),
                v45_param('target','Touch target',20,64,28,44,1,'px','Velikost interaktivní plochy.'),
                v45_param('focus','Viditelnost focusu',0,100,20,85,1,'%','Ovládání klávesnicí musí být čitelné.'),
                v45_param('motion','Intenzita pohybu',0,100,80,28,1,'%','Pohyb nemá nést jedinou informaci.'),
            ]]; break;
        case 'components':
            $overrides=['params'=>[
                v45_param('variants','Počet ad-hoc variant',1,14,10,3,1,'','Méně jasně pojmenovaných variant je udržitelnější.'),
                v45_param('token_use','Využití tokenů',0,100,28,90,1,'%','Podíl hodnot řízených systémem.'),
                v45_param('drift','Vizuální drift',0,100,62,8,1,'%','Odchylka mezi stejnými komponentami.'),
                v45_param('states','Pokrytí stavů',0,100,35,90,1,'%','Default, hover, focus, error, disabled…'),
            ]]; break;
        case 'forms':
            $overrides=['params'=>[
                v45_param('fields','Počet polí',2,24,16,7,1,'','Požaduj jen data nutná pro úkol.'),
                v45_param('label','Srozumitelnost labelů',0,100,45,90,1,'%','Label má říct co a proč.'),
                v45_param('error','Kvalita chyby',0,100,25,92,1,'%','Chyba musí říct, co opravit.'),
                v45_param('recovery','Možnost opravy',0,100,30,90,1,'%','Student/uživatel nesmí ztratit celý postup.'),
            ]]; break;
        case 'handoff':
            $overrides=['params'=>[
                v45_param('spec','Úplnost specifikace',0,100,45,95,1,'%','Stavy, tokeny, rozměry a chování musí být dohledatelné.'),
                v45_param('assets','Připravenost assetů',0,100,55,95,1,'%','Exporty, názvy a formáty mají být jednoznačné.'),
                v45_param('states','Pokrytí stavů',0,100,32,92,1,'%','Default, loading, empty, error, hover/focus a responsive.'),
                v45_param('ambiguity','Nejednoznačnost',0,100,72,10,1,'%','Čím méně domýšlení při implementaci, tím menší drift.'),
            ]]; break;
        case 'usability':
            $overrides=['params'=>[
                v45_param('success','Task success',0,100,48,92,1,'%','Dokončení skutečného uživatelského úkolu.'),
                v45_param('time','Čas úkolu',5,300,190,55,5,' s','Čas je signál, ne jediný cíl.'),
                v45_param('errors','Chyby uživatele',0,12,6,1,1,'','Počet oprav a slepých cest.'),
                v45_param('evidence','Kvalita pozorování',0,100,38,92,1,'%','Pozoruj chování, ne jen názor uživatele.'),
            ]]; break;
        case 'information':
            $overrides=['params'=>[
                v45_param('depth','Hloubka navigace',1,8,6,3,1,' úrovně','Přílišná hloubka zvyšuje hledání.'),
                v45_param('choices','Volby na úrovni',2,18,14,7,1,'','Příliš mnoho rovnocenných voleb zvyšuje nejistotu.'),
                v45_param('labels','Jasnost názvů',0,100,48,90,1,'%','Názvy musí odpovídat mentálnímu modelu uživatele.'),
                v45_param('evidence','Výzkumná evidence',0,100,25,85,1,'%','Struktura má vycházet z úkolů uživatele.'),
            ]]; break;
        case 'image':
            $overrides=['params'=>[
                v45_param('scale','Zvětšení rastru',50,500,320,100,10,'%','Při zvětšení rastru roste viditelnost pixelů.'),
                v45_param('crop','Síla cropu',0,100,72,35,1,'%','Přílišný crop může odstranit význam.'),
                v45_param('focus','Focal point',0,100,18,55,1,'%','Pozice důležitého motivu v obrazu.'),
                v45_param('quality','Export kvalita',10,100,45,82,1,'%','Kompromis mezi kvalitou a velikostí.'),
            ]]; break;
        case 'motion':
            $overrides=['params'=>[
                v45_param('duration','Délka animace',80,1800,1200,280,20,' ms','Rychlost má podporovat pochopení změny stavu.'),
                v45_param('distance','Vzdálenost pohybu',0,220,160,32,2,'px','Velký pohyb zvyšuje vizuální hluk.'),
                v45_param('layers','Současně animované vrstvy',1,8,6,2,1,'','Jeden významový vztah v jeden moment.'),
                v45_param('meaning','Významovost pohybu',0,100,35,90,1,'%','Pohyb má vysvětlit změnu, ne pouze dekorovat.'),
            ]]; break;
        case 'dns':
            $overrides=['params'=>[
                v45_param('dns_latency','DNS latence',1,800,380,45,5,' ms','Čas získání odpovědi resolveru.'),
                v45_param('cache_hit','Cache hit',0,100,20,85,1,'%','Vyšší hit rate snižuje opakované dotazy.'),
                v45_param('failure','NXDOMAIN/SERVFAIL',0,40,18,1,1,'%','Chybové odpovědi DNS vrstvy.'),
                v45_param('evidence','Izolace vrstvy',0,100,30,90,1,'%','Jak dobře test odděluje DNS od HTTP služby.'),
            ]]; break;
        case 'dhcp':
            $overrides=['params'=>[
                v45_param('pool','Volný DHCP pool',0,100,12,70,1,'%','Dostatek volných adres.'),
                v45_param('lease','Doba lease',5,1440,30,480,5,' min','Příliš krátká lease zvyšuje provoz a churn.'),
                v45_param('loss','Ztráta broadcastu',0,40,16,1,1,'%','DORA potřebuje doručení klíčových kroků.'),
                v45_param('relay','Správnost relay',0,100,40,95,1,'%','Mezi VLAN je nutný správný relay/helper.'),
            ]]; break;
        case 'routing':
            $overrides=['params'=>[
                v45_param('prefix','Specificita route',8,32,16,24,1,' /','Longest-prefix match rozhoduje o cestě.'),
                v45_param('metric','Route metric',1,500,220,40,1,'','Nižší metrika může být preferovaná cesta.'),
                v45_param('gateway','Dostupnost gateway',0,100,55,98,1,'%','Next hop musí být dosažitelný.'),
                v45_param('evidence','Traceroute evidence',0,100,35,90,1,'%','Důkaz má ukázat, kde se cesta mění.'),
            ]]; break;
        case 'packet':
            $overrides=['params'=>[
                v45_param('rtt','RTT',1,900,320,70,5,' ms','Round-trip time mezi konci.'),
                v45_param('loss','Packet loss',0,30,9,1,.5,'%','Ztráta ovlivní retransmise i throughput.'),
                v45_param('window','TCP window',8,1024,64,512,8,' KB','Okno ovlivňuje využití linky při vyšším RTT.'),
                v45_param('capture','Kvalita capture',0,100,25,90,1,'%','Capture point musí odpovídat hypotéze.'),
            ]]; break;
        case 'observability':
            $overrides=['params'=>[
                v45_param('latency','p95 latence',20,2000,720,180,10,' ms','Tail latency zachytí problémy, které průměr schová.'),
                v45_param('errors','Error rate',0,25,8,1,.2,'%','Poměr selhání.'),
                v45_param('saturation','Saturace',0,100,92,62,1,'%','Rezerva před vyčerpáním zdroje.'),
                v45_param('signal','Kvalita signálu',0,100,38,90,1,'%','Metrika musí být akční a vztahovat se k cíli.'),
            ]]; break;
        case 'filesystem':
            $overrides=['params'=>[
                v45_param('disk','Disk usage',0,100,72,60,1,'%','Kapacita bloků.'),
                v45_param('inodes','Inode usage',0,100,98,55,1,'%','Volné GB nepomohou, když dojdou inody.'),
                v45_param('iowait','I/O wait',0,100,38,8,1,'%','Čekání procesů na storage.'),
                v45_param('evidence','Kvalita diagnostiky',0,100,30,90,1,'%','Rozliš bloky, inody a výkon.'),
            ]]; break;
        case 'permissions':
            $overrides=['params'=>[
                v45_param('owner','Správný owner',0,100,35,95,1,'%','Identita procesu vs. vlastník souboru.'),
                v45_param('mode','Přesnost práv',0,100,45,95,1,'%','Least privilege bez blokace legitimního přístupu.'),
                v45_param('scope','Rozsah změny',0,100,85,20,1,'%','Čím širší zásah, tím větší riziko.'),
                v45_param('evidence','Audit evidence',0,100,35,90,1,'%','Kdo, co a proč skutečně potřebuje.'),
            ]]; break;
        case 'systemd':
            $overrides=['params'=>[
                v45_param('restart','Restart rate',0,60,18,1,1,'/h','Časté restarty jsou symptom, ne oprava.'),
                v45_param('deps','Zdraví dependencies',0,100,55,95,1,'%','Služba může být active, ale závislost ne.'),
                v45_param('config','Validita configu',0,100,58,98,1,'%','Validace před restartem snižuje riziko.'),
                v45_param('logs','Kvalita log evidence',0,100,30,92,1,'%','Journal má potvrdit skutečnou příčinu.'),
            ]]; break;
        case 'logs':
            $overrides=['params'=>[
                v45_param('noise','Log noise',0,100,82,25,1,'%','Příliš šumu skrývá signál.'),
                v45_param('context','Kontext logu',0,100,35,90,1,'%','Čas, service, request/correlation ID.'),
                v45_param('retention','Retence',1,90,3,21,1,' dní','Musí pokrýt očekávané incidenty.'),
                v45_param('query','Přesnost filtru',0,100,25,90,1,'%','Filtruj podle hypotézy, ne náhodně.'),
            ]]; break;
        case 'ssh':
            $overrides=['params'=>[
                v45_param('reach','TCP/22 dostupnost',0,100,70,98,1,'%','Nejdřív ověř transport.'),
                v45_param('key','Správnost klíče',0,100,38,98,1,'%','Autentizace musí odpovídat účtu a serveru.'),
                v45_param('perm','Práva .ssh',0,100,44,95,1,'%','Příliš otevřená práva mohou klíč zneplatnit.'),
                v45_param('evidence','Debug evidence',0,100,35,90,1,'%','Verbose výstup oddělí fáze spojení.'),
            ]]; break;
        case 'firewall':
            $overrides=['params'=>[
                v45_param('scope','Rozsah pravidla',0,100,88,20,1,'%','Menší rozsah = menší blast radius.'),
                v45_param('allow','Legitimní průchod',0,100,55,98,1,'%','Povolení správného provozu.'),
                v45_param('block','Blokace rizika',0,100,48,92,1,'%','Blokuj jen to, co má být blokováno.'),
                v45_param('logging','Log evidence',0,100,30,85,1,'%','Důkaz o hitu pravidla.'),
            ]]; break;
        case 'service':
        case 'proxy':
            $overrides=['params'=>[
                v45_param('listener','Listener zdraví',0,100,75,98,1,'%','Proces musí skutečně naslouchat na správném socketu.'),
                v45_param('upstream','Upstream zdraví',0,100,42,98,1,'%','Proxy může být healthy, upstream ne.'),
                v45_param('timeout','Timeout',50,10000,7000,1200,50,' ms','Timeout musí odlišit pomalost od nedostupnosti.'),
                v45_param('evidence','Layer evidence',0,100,35,92,1,'%','Testuj jednotlivé přechody zvlášť.'),
            ]]; break;
        case 'automation':
        case 'iac':
            $overrides=['params'=>[
                v45_param('scope','Blast radius',1,100,80,20,1,'%','Automatizace má začít malým bezpečným rozsahem.'),
                v45_param('idempotence','Idempotence',0,100,45,95,1,'%','Opakované spuštění nesmí nekontrolovaně měnit stav.'),
                v45_param('validation','Validace',0,100,35,95,1,'%','Pre-check + post-check.'),
                v45_param('rollback','Rollback připravenost',0,100,25,90,1,'%','Změna bez návratu je vysoké riziko.'),
            ]]; break;
        case 'release':
            $overrides=['params'=>[
                v45_param('traffic','Traffic na nové verzi',0,100,100,10,1,'%','Canary omezuje blast radius.'),
                v45_param('errors','Error rate',0,25,7,1,.2,'%','Sleduj rozdíl proti baseline.'),
                v45_param('observe','Observation window',1,60,3,15,1,' min','Příliš krátké okno může skrýt regresi.'),
                v45_param('rollback','Rollback readiness',0,100,30,95,1,'%','Návrat musí být ověřitelný a rychlý.'),
            ]]; break;
        case 'backup':
            $overrides=['params'=>[
                v45_param('freshness','Stáří backupu',0,168,72,12,1,' h','RPO určuje tolerovanou ztrátu dat.'),
                v45_param('restore','Doba restore',1,240,180,30,1,' min','RTO určuje potřebnou rychlost obnovy.'),
                v45_param('verified','Ověřená obnovitelnost',0,100,20,95,1,'%','Backup bez testu restore není evidence obnovy.'),
                v45_param('copies','Nezávislé kopie',1,5,1,3,1,'','Oddělené kopie snižují společné selhání.'),
            ]]; break;
        case 'performance':
            $overrides=['params'=>[
                v45_param('load','Zátěž',0,150,120,65,1,'%','Sleduj chování i před saturací.'),
                v45_param('latency','p95 latence',20,3000,1100,220,10,' ms','Latence obvykle roste nelineárně u limitu.'),
                v45_param('headroom','Headroom',0,100,12,35,1,'%','Rezerva pro špičky a selhání.'),
                v45_param('queue','Queue depth',0,100,72,12,1,'%','Fronta je signál, že poptávka převyšuje zpracování.'),
            ]]; break;
        case 'containers':
            $overrides=['params'=>[
                v45_param('cpu','CPU limit',10,400,60,160,10,'%','Limit nesmí škrtit běžnou zátěž.'),
                v45_param('memory','Memory headroom',0,100,12,35,1,'%','Omez riziko OOM.'),
                v45_param('health','Healthcheck kvalita',0,100,35,90,1,'%','Healthcheck má měřit skutečnou schopnost sloužit.'),
                v45_param('restart','Restart loop',0,30,12,1,1,'/h','Restart policy nesmí maskovat příčinu.'),
            ]]; break;
        case 'incident':
            $overrides=['params'=>[
                v45_param('blast','Blast radius',0,100,75,20,1,'%','První cíl je omezit dopad.'),
                v45_param('signal','Kvalita evidence',0,100,35,90,1,'%','Rozhoduj podle pozorování, ne dojmu.'),
                v45_param('changes','Současné změny',0,10,6,1,1,'','Více změn současně komplikuje kauzalitu.'),
                v45_param('recovery','Recovery readiness',0,100,28,92,1,'%','Mitigace/rollback musí být připravené.'),
            ]]; break;
    }
    return array_replace($base,$overrides);
}

function v45_param_target_score(array $param,float $value): float
{
    $min=(float)$param['min'];$max=(float)$param['max'];$target=(float)$param['target'];$span=max(.0001,$max-$min);
    $distance=abs($value-$target)/$span;
    return max(0.0,min(100.0,100.0-$distance*180.0));
}

function v45_evaluate_state(array $spec,array $input): array
{
    $params=[];$scores=[];
    foreach((array)$spec['params'] as $p){
        $id=(string)$p['id'];$v=isset($input[$id])?(float)$input[$id]:(float)$p['value'];
        $v=max((float)$p['min'],min((float)$p['max'],$v));$params[$id]=$v;$scores[$id]=v45_param_target_score($p,$v);
    }
    $avg=$scores?array_sum($scores)/count($scores):0.0;
    $spread=$scores?(max($scores)-min($scores)):0.0;
    $quality=(int)round($avg);
    $clarity=(int)round(max(0,min(100,$avg*.78+(100-$spread)*.22)));
    $resilience=(int)round(max(0,min(100,$avg*.62+(min($scores?:[0]))*.38)));
    $risk=(int)round(max(0,min(100,100-$resilience)));
    return ['params'=>$params,'parameter_scores'=>array_map(static fn($v)=>(int)round($v),$scores),'metrics'=>['quality'=>$quality,'clarity'=>$clarity,'resilience'=>$resilience,'risk'=>$risk],'ready'=>$quality>=72&&$risk<=38];
}

function v45_simulation_spec(string $classId,array $lesson,array $module): array
{
    $lab=cv43_lab_spec($classId,$lesson,$module);$profile=v45_family_profile((string)$lab['family'],$classId);
    $baseline=[];$target=[];foreach($profile['params'] as $p){$baseline[$p['id']]=$p['value'];$target[$p['id']]=$p['target'];}
    $stress=$baseline;$n=0;foreach($profile['params'] as $p){$id=$p['id'];$span=(float)$p['max']-(float)$p['min'];$direction=((float)$p['target']>=(float)$p['value'])?-1:1;$stress[$id]=max((float)$p['min'],min((float)$p['max'],(float)$p['value']+$direction*$span*(.16+$n*.025)));$n++;}
    $experiment=$baseline;$i=0;foreach($profile['params'] as $p){$id=$p['id'];$experiment[$id]=(float)$p['value']+((float)$p['target']-(float)$p['value'])*($i%2===0?.55:.25);$i++;}
    $whatIf=[
        ['id'=>'stress','title'=>'Co když se podmínky zhorší?','text'=>'Zvýší se tlak na slabé místo. Sleduj, která metrika se zlomí jako první.','values'=>$stress],
        ['id'=>'partial','title'=>'Co když opravíš jen polovinu?','text'=>'Částečný zásah může zlepšit symptom, ale nemusí odstranit příčinu.','values'=>$experiment],
        ['id'=>'target','title'=>'Co když nastavíš funkční variantu?','text'=>'Přibliž parametry cílovému stavu a porovnej přínos i kompromisy.','values'=>$target],
    ];
    return [
        'id'=>$lab['id'].'-sim45','lab_id'=>$lab['id'],'class_id'=>$classId,'lesson_number'=>(int)$lesson['number'],'lesson_title'=>(string)$lesson['title'],'family'=>$lab['family'],'renderer'=>$profile['renderer'],
        'problem'=>$lab['problem'],'question'=>$lab['question'],'princip'=>$lab['compare']['why'],'transfer'=>$lab['transfer'],'layers'=>$lab['layers'],'timeline'=>$lab['timeline'],
        'params'=>$profile['params'],'metric_labels'=>$profile['metric_labels'],'baseline'=>$baseline,'target'=>$target,'what_if'=>$whatIf,
        'projection'=>['ask'=>'Který parametr podle vás změní výsledek nejvíc — a proč?','reveal'=>'Nejdřív měň jen jednu proměnnou. Až pak dovol třídě kombinovat zásahy.','compare'=>'Zamkni variantu A, vytvoř B a nech třídu popsat nejen „která je lepší“, ale jaký důkaz to ukazuje.'],
    ];
}

function v45_all_sim_specs(): array
{
    static $cache=null;if(is_array($cache))return $cache;
    global $modules;$out=[];foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){foreach(v42_lessons_for_class($classId) as $lesson){$s=v45_simulation_spec($classId,$lesson,$modules[$classId]);$out[$s['id']]=$s;}}
    return $cache=$out;
}

function v45_snapshot_rows(): array { return adaptive_store('v45_sim_snapshots'); }
function v45_event_rows(): array { return adaptive_store('v45_sim_events'); }
function v45_student_key(string $classId,string $studentKey,int $lessonNo): string { return $classId.'|'.$studentKey.'|L'.$lessonNo; }

function v45_sim_snapshot_save(string $classId,string $studentKey,array $lesson,string $slot,array $raw,string $scenario='manual',string $note=''): array
{
    if(!in_array($slot,['a','b','best'],true))$slot='best';
    $spec=v45_simulation_spec($classId,$lesson,$GLOBALS['modules'][$classId]);$eval=v45_evaluate_state($spec,$raw);$key=v45_student_key($classId,$studentKey,(int)$lesson['number']);
    $cleanNote=u_substr(trim(strip_tags($note)),0,600);
    storage_map_update(adaptive_store_path('v45_sim_snapshots'),$key,static function(?array $entry) use($classId,$studentKey,$lesson,$slot,$eval,$scenario,$cleanNote): array {$entry=$entry??['class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$lesson['number'],'slots'=>[]];
    $entry['slots'][$slot]=['params'=>$eval['params'],'metrics'=>$eval['metrics'],'ready'=>$eval['ready'],'scenario'=>u_substr(trim($scenario),0,80),'note'=>$cleanNote,'saved_at'=>date(DATE_ATOM)];$entry['updated_at']=date(DATE_ATOM);return $entry;});
    $id='v45_'.bin2hex(random_bytes(7));$event=['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$lesson['number'],'family'=>$spec['family'],'slot'=>$slot,'scenario'=>$scenario,'metrics'=>$eval['metrics'],'ready'=>$eval['ready'],'created_at'=>date(DATE_ATOM)];adaptive_store_update('v45_sim_events',static function(array $events) use($id,$event): array {$events[$id]=$event;if(count($events)>5000)$events=array_slice($events,-4200,null,true);return $events;});
    if(function_exists('ml_record_learning_event'))ml_record_learning_event($classId,$studentKey,(string)cv43_lab_spec($classId,$lesson,$GLOBALS['modules'][$classId])['topic'],'visual_simulation',$eval['ready'],['lesson'=>(int)$lesson['number'],'v45'=>true,'quality'=>$eval['metrics']['quality'],'risk'=>$eval['metrics']['risk']]);
    return ['slot'=>$slot,'note'=>$cleanNote]+$eval;
}

function v45_student_state(string $classId,string $studentKey,array $lesson): array
{
    $rows=v45_snapshot_rows();return is_array($rows[v45_student_key($classId,$studentKey,(int)$lesson['number'])]??null)?$rows[v45_student_key($classId,$studentKey,(int)$lesson['number'])]:[];
}

function v45_teacher_summary(string $classId,int $lessonNo): array
{
    $students=project_students_for_class($classId);$valid=array_fill_keys(array_keys($students),true);$rows=v45_snapshot_rows();$explored=[];$ready=[];$quality=[];$risk=[];
    foreach($rows as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(int)($r['lesson_number']??0)!==$lessonNo)continue;$sk=(string)($r['student_key']??'');if($valid&&!isset($valid[$sk]))continue;$explored[$sk]=true;$slots=(array)($r['slots']??[]);foreach($slots as $s){if(!is_array($s))continue;$m=(array)($s['metrics']??[]);if(isset($m['quality']))$quality[]=(int)$m['quality'];if(isset($m['risk']))$risk[]=(int)$m['risk'];if(!empty($s['ready']))$ready[$sk]=true;}}
    return ['students'=>count($students),'explored'=>count($explored),'ready'=>count($ready),'avg_quality'=>$quality?(int)round(array_sum($quality)/count($quality)):0,'avg_risk'=>$risk?(int)round(array_sum($risk)/count($risk)):0];
}
