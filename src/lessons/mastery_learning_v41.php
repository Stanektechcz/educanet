<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCAnet v41 · mastery learning engine.
 * One evidence/event layer for misconceptions, confidence calibration,
 * worked-example fading, transfer, explain-back, classroom pulse and authoring.
 */

function ml_store(string $name): array { return adaptive_store('ml_' . $name); }
/** v58 (F2): RMW kolekce mastery vrstvy pod jedním zámkem (náhrada za ml_store_save). */
function ml_store_update(string $name,callable $fn): array { return adaptive_store_update('ml_' . $name,$fn); }
function ml_store_path(string $name): string { return adaptive_store_path('ml_' . $name); }
function ml_id(string $prefix='ml'): string { return $prefix . '_' . bin2hex(random_bytes(7)); }
function ml_lower(string $value): string { return function_exists('mb_strtolower') ? mb_strtolower($value,'UTF-8') : strtolower($value); }

function ml_topic_family(string $topic,array $article=[]): string
{
    $hay=ml_lower($topic.' '.(string)($article['title']??'').' '.(string)($article['summary']??''));
    if(preg_match('/dns|dhcp|route|routing|vlan|subnet|tcp|udp|packet|firewall|ssh|nginx|systemd|journal|linux|service|container|backup|restore|storage|inode|observ|incident|bash/u',$hay)) return 'ops';
    if(preg_match('/type|font|typograph|hierarch|grid|layout|contrast|color|crop|image|raster|vector|responsive|component|form|accessib|wireframe|ux|ui|design|handoff/u',$hay)) return 'design';
    return 'general';
}

function ml_misconception_spec(string $topic,array $article=[],int $selected=-1,int $correct=-2): array
{
    $hay=ml_lower($topic.' '.(string)($article['title']??'').' '.(string)($article['summary']??''));
    $defs=[
      ['rx'=>'dns','code'=>'dns-layer-confusion','label'=>'DNS ≠ webová služba','coach'=>'Odděl překlad jména od samotné dostupnosti služby. Zeptej se: co se liší mezi IP adresou a hostname?'],
      ['rx'=>'dhcp|apipa','code'=>'dhcp-state-confusion','label'=>'Adresa ≠ důkaz funkčního DHCP','coach'=>'Rozliš statickou/APIPA adresu od skutečně dokončeného DHCP procesu.'],
      ['rx'=>'route|gateway|subnet|vlan','code'=>'path-vs-host','label'=>'Host funguje ≠ cesta funguje','coach'=>'Ověř lokální síť, gateway a další hop zvlášť. Každý test má zúžit místo poruchy.'],
      ['rx'=>'firewall','code'=>'firewall-first-guess','label'=>'Firewall jako první domněnka','coach'=>'Nejdřív zjisti, zda služba poslouchá a kde se cesta láme. Firewall testuj jako konkrétní hypotézu.'],
      ['rx'=>'ssh','code'=>'ssh-auth-vs-connectivity','label'=>'SSH autentizace ≠ konektivita','coach'=>'Permission denied znamená, že ses k SSH službě pravděpodobně dostal. Teď řeš autentizaci/klíče/oprávnění.'],
      ['rx'=>'systemd|service|nginx','code'=>'restart-without-evidence','label'=>'Restart bez důkazu','coach'=>'Nejdřív status/log/listener. Restart je změna stavu, ne diagnostický důkaz.'],
      ['rx'=>'storage|inode|disk','code'=>'capacity-only','label'=>'Volné GB ≠ volná kapacita všeho','coach'=>'Vedle datových bloků existují i inody, quota a filesystem limity. Hledej metriku odpovídající symptomu.'],
      ['rx'=>'backup|restore','code'=>'backup-equals-recovery','label'=>'Backup ≠ ověřitelný restore','coach'=>'Záloha má hodnotu až tehdy, když umíš prokázat obnovu v cílovém RPO/RTO.'],
      ['rx'=>'incident|observ|latency','code'=>'single-metric-cause','label'=>'Jedna metrika ≠ root cause','coach'=>'Metrika popisuje symptom. Pro příčinu potřebuješ korelaci více důkazů nebo rozlišovací experiment.'],
      ['rx'=>'hierarch','code'=>'hierarchy-equals-color','label'=>'Hierarchie není jen barva','coach'=>'Vyzkoušej velikost, váhu, pozici a spacing. Barva je pouze jeden z více signálů priority.'],
      ['rx'=>'typograph|font|readab','code'=>'font-equals-typography','label'=>'Font ≠ typografický systém','coach'=>'Sleduj velikost, řádkování, délku řádku, kontrast a role textu, ne jen název fontu.'],
      ['rx'=>'contrast|accessib','code'=>'pretty-equals-accessible','label'=>'Estetické ≠ přístupné','coach'=>'Ověř čitelnost, focus, touch target a význam i bez barvy. Vzhled sám není důkaz použitelnosti.'],
      ['rx'=>'responsive|layout|grid','code'=>'responsive-equals-shrink','label'=>'Responsive ≠ zmenšit desktop','coach'=>'Změň pravidla layoutu podle dostupného prostoru. Priorita obsahu je důležitější než pouhé škálování.'],
      ['rx'=>'crop|image','code'=>'crop-without-focal','label'=>'Crop bez focal pointu','coach'=>'Nejdřív definuj, co musí zůstat viditelné. Teprve potom měň poměr a výřez.'],
      ['rx'=>'component|design.system|handoff','code'=>'happy-path-only','label'=>'Komponenta není jen default stav','coach'=>'Ověř loading, error, focus, dlouhý text, disabled a responsive variantu.'],
      ['rx'=>'form|error','code'=>'error-without-recovery','label'=>'Chyba bez cesty k opravě','coach'=>'Dobrá validace říká co je špatně, kde a jak to opravit – ve správný okamžik.'],
    ];
    foreach($defs as $d) if(preg_match('/'.$d['rx'].'/u',$hay)) return $d;
    return ['code'=>'evidence-vs-guess','label'=>'Domněnka bez důkazu','coach'=>'Odděl pozorovaný fakt od domněnky. Vyber nejmenší test, který rozliší dvě možné příčiny.'];
}

function ml_record_learning_event(string $classId,string $studentKey,string $topic,string $source,bool $correct,array $meta=[]): array
{
    $row=[
      'id'=>ml_id('ev'),'class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'source'=>$source,'correct'=>$correct,
      'confidence'=>max(0,min(3,(int)($meta['confidence']??0))),'latency_ms'=>max(0,min(600000,(int)($meta['latency_ms']??0))),
      'hints'=>max(0,(int)($meta['hints']??0)),'selected'=>$meta['selected']??null,'correct_index'=>$meta['correct_index']??null,
      'transfer'=>(bool)($meta['transfer']??false),'explain_back'=>(bool)($meta['explain_back']??false),'created_at'=>date(DATE_ATOM),
    ];
    if(!$correct){$spec=ml_misconception_spec($topic,(array)($meta['article']??[]),(int)($meta['selected']??-1),(int)($meta['correct_index']??-2));$row['misconception']=$spec['code'];$row['misconception_label']=$spec['label'];}
    storage_append('adaptive_ml_events',$row);
    return $row;
}

function ml_student_events(string $classId,string $studentKey,?string $topic=null): array
{
    return array_values(array_filter(ml_store('events'),static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey&&($topic===null||(string)($r['topic']??'')===$topic)));
}

function ml_misconception_summary(string $classId,?string $studentKey=null): array
{
    $counts=[];
    foreach(ml_store('events') as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||empty($r['misconception']))continue;if($studentKey!==null&&(string)($r['student_key']??'')!==$studentKey)continue;$code=(string)$r['misconception'];if(!isset($counts[$code]))$counts[$code]=['code'=>$code,'label'=>(string)($r['misconception_label']??$code),'count'=>0,'students'=>[],'topics'=>[]];$counts[$code]['count']++;$counts[$code]['students'][(string)($r['student_key']??'')]=true;$counts[$code]['topics'][(string)($r['topic']??'')]=true;}
    foreach($counts as &$c){$c['student_count']=count($c['students']);$c['topics']=array_keys($c['topics']);unset($c['students']);}unset($c);
    usort($counts,static fn($a,$b)=>(int)$b['count']<=>(int)$a['count']); return $counts;
}

function ml_confidence_calibration(string $classId,string $studentKey): array
{
    $events=ml_student_events($classId,$studentKey);$bins=['underconfident'=>0,'calibrated'=>0,'overconfident'=>0];
    foreach($events as $r){$c=(int)($r['confidence']??0);if($c<=0)continue;$ok=!empty($r['correct']);if($ok&&$c===1)$bins['underconfident']++;elseif(!$ok&&$c===3)$bins['overconfident']++;else$bins['calibrated']++;}
    return $bins;
}

function ml_worked_example_spec(string $classId,string $topic,array $article=[]): array
{
    $r=reality_demo_spec($classId,$topic,$article);$family=ml_topic_family($topic,$article);
    if($family==='ops'){
        $steps=[
          ['Symptom','Popiš přesně pozorovaný problém bez domněnky o příčině.'],
          ['Hypotéza','Vyber jednu vrstvu nebo příčinu, kterou lze rozlišit testem.'],
          ['Test','Proveď nejmenší bezpečný test, který přidá informaci.'],
          ['Evidence','Zapiš, co výsledek dokazuje a co ještě ne.'],
          ['Změna + validace','Změň jednu věc a ověř pozitivní i negativní scénář.'],
        ];
    } else {
        $steps=[
          ['Cíl','Pojmenuj, co má uživatel nejrychleji pochopit nebo udělat.'],
          ['Problém','Najdi konkrétní vizuální/UX konflikt místo dojmu „nelíbí se mi“.'],
          ['Pravidlo','Vyber jeden princip: hierarchy, spacing, typografie, contrast nebo flow.'],
          ['Změna','Uprav jen to, co daný problém skutečně řeší.'],
          ['Ověření','Porovnej před/po, jiný viewport nebo reálný úkol uživatele.'],
        ];
    }
    return ['title'=>(string)($r['title']??$article['title']??$topic),'brief'=>(string)($r['brief']??$article['summary']??''),'steps'=>$steps,'family'=>$family];
}

function ml_transfer_spec(string $classId,string $studentKey,string $topic,array $article=[]): array
{
    $base=ml_worked_example_spec($classId,$topic,$article);$seed=hexdec(substr(hash('sha256',$studentKey.'|'.$topic),0,6));$variant=($seed%3)+1;$family=$base['family'];$hay=ml_lower($topic.' '.(string)($article['title']??''));
    if($family==='ops'){
      if(str_contains($hay,'dns')){$q='Nový interní nástroj funguje přes IP, ale v jednom kontejneru ne přes hostname. Co nejlépe oddělí DNS problém od aplikačního?';$opts=['Restartovat kontejner i server','Porovnat resolver/config a provést DNS dotaz ze stejného kontextu','Změnit port aplikace'];$correct=1;}
      elseif(str_contains($hay,'ssh')){$q='TCP/22 odpovídá a SSH vrací „Permission denied (publickey)“. Která vrstva je teď nejpravděpodobnější?';$opts=['Routing mezi sítěmi','Autentizace/authorized_keys/oprávnění','DNS resolver'];$correct=1;}
      elseif(str_contains($hay,'firewall')){$q='Lokálně curl funguje, vzdáleně timeout. Jaký další test nejlépe zúží problém?';$opts=['Ověřit listener/bind a cestu/firewall z konkrétního zdroje','Přeinstalovat službu','Vymazat logy'];$correct=0;}
      else{$q='Stejný princip se objevil v jiné službě. Co je nejlepší první krok?';$opts=['Změnit několik věcí současně','Formulovat hypotézu a provést nejmenší rozlišovací test','Restartovat celý server'];$correct=1;}
    } else {
      if(preg_match('/contrast|accessib/u',$hay)){$q='CTA je dobře vidět na desktopu, ale na mobilu splývá s fotografií. Co je nejspolehlivější oprava?';$opts=['Přidat náhodnou sytou barvu','Ověřit kontrast v reálném stavu a upravit overlay/text/token','Zmenšit CTA'];$correct=1;}
      elseif(preg_match('/responsive|layout|grid/u',$hay)){$q='Karta funguje na 1440 px, na 320 px přetéká. Co je správný transfer principu?';$opts=['Zmenšit celý desktop na 22 %','Změnit layout pravidla/prioritu obsahu pro dostupnou šířku','Skrýt všechen text'];$correct=1;}
      else{$q='Stejný obsah má fungovat v jiném kontextu. Co ověřuje skutečný transfer?';$opts=['Zkopírovat původní řešení pixel po pixelu','Použít stejný princip a znovu ověřit cíl v novém kontextu','Použít více efektů'];$correct=1;}
    }
    return ['id'=>'transfer-'.$topic.'-'.$variant,'variant'=>$variant,'question'=>$q,'options'=>$opts,'correct'=>$correct,'why'=>'Transfer znamená použít stejný princip v novém kontextu, ne zopakovat stejný povrchový postup.'];
}

function ml_transfer_submit(string $classId,string $studentKey,string $topic,int $answer,int $confidence,array $module): array
{
    $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];$spec=ml_transfer_spec($classId,$studentKey,$topic,$article);$ok=$answer===(int)$spec['correct'];
    ml_record_learning_event($classId,$studentKey,$topic,'transfer',$ok,['confidence'=>$confidence,'selected'=>$answer,'correct_index'=>$spec['correct'],'transfer'=>true,'article'=>$article]);
    $id=$classId.'|'.$studentKey.'|'.$topic;storage_map_update(ml_store_path('transfer'),$id,static fn(?array $old): array => ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'best'=>max((int)($old['best']??0),$ok?100:0),'attempts'=>(int)($old['attempts']??0)+1,'last_correct'=>$ok,'updated_at'=>date(DATE_ATOM)]);
    if($ok && function_exists('skill_add_evidence')){
        $links=[]; foreach(skill_knowledge_topic_map($classId) as $slug=>$mapped) if((string)$mapped===$topic)$links[]=(string)$slug; foreach(skill_additional_knowledge_evidence_map($classId) as $mapped=>$slug) if((string)$mapped===$topic)$links[]=(string)$slug;
        foreach(array_slice(array_values(array_unique($links)),0,2) as $slug){try{skill_add_evidence($classId,$studentKey,$slug,'practice','transfer:'.$spec['id'],100,100,'automatic',['topic'=>$topic,'kind'=>'transfer']);}catch(Throwable $e){}}
    }
    return ['correct'=>$ok,'why'=>$spec['why'],'spec'=>$spec];
}

function ml_explain_back_prompt(string $classId,string $topic,array $article=[]): string
{
    $family=ml_topic_family($topic,$article);$title=(string)($article['title']??$topic);
    return $family==='ops' ? 'Vysvětli vlastními slovy, proč samotný jeden pozitivní test ještě nemusí dokazovat, že „'.$title.'“ funguje ve všech vrstvách.' : 'Vysvětli vlastními slovy, jak poznáš, že rozhodnutí v tématu „'.$title.'“ skutečně zlepšilo cíl uživatele a není jen vizuální preference.';
}

function ml_explain_back_save(string $classId,string $studentKey,string $topic,string $text): array
{
    $text=trim(u_substr($text,0,1400));if(u_strlen($text)<20)throw new RuntimeException(tr('Napiš alespoň jednu konkrétní větu.'));$id=$classId.'|'.$studentKey.'|'.$topic;$tokens=preg_split('/\s+/u',ml_lower($text))?:[];$markers=['proto','důkaz','ověř','protože','změn','uživatel','test','výsledek'];$hits=0;foreach($markers as $m)foreach($tokens as $t)if(str_starts_with($t,$m)){$hits++;break;}$state=$hits>=2?'strong':($hits>=1?'developing':'needs_review');storage_map_update(ml_store_path('explain_back'),$id,static fn(?array $current): array => ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'text'=>$text,'state'=>$state,'updated_at'=>date(DATE_ATOM)]);ml_record_learning_event($classId,$studentKey,$topic,'explain_back',$state!=='needs_review',['explain_back'=>true]);return $rows[$id];
}

function ml_error_journal(string $classId,string $studentKey): array
{
    $sum=ml_misconception_summary($classId,$studentKey);$out=[];foreach(array_slice($sum,0,8) as $m){$topic=(string)($m['topics'][0]??'');$spec=ml_misconception_spec($topic);$out[]=['label'=>$m['label'],'count'=>$m['count'],'topic'=>$topic,'reframe'=>$spec['coach']];}return $out;
}

function ml_related_topics(string $classId,string $topic,array $module,int $limit=5): array
{
    $article=(array)($module['knowledgebase'][$topic]??[]);$base=ml_lower($topic.' '.(string)($article['title']??'').' '.(string)($article['summary']??''));$words=array_values(array_filter(array_unique(preg_split('/[^\pL\pN]+/u',$base)?:[]),static fn($w)=>u_strlen($w)>4));$scores=[];
    foreach((array)($module['knowledgebase']??[]) as $k=>$a){if((string)$k===$topic||!is_array($a))continue;$hay=ml_lower((string)$k.' '.(string)($a['title']??'').' '.(string)($a['summary']??''));$score=0;foreach($words as $w)if(str_contains($hay,$w))$score++;if($score>0)$scores[]=['topic'=>(string)$k,'title'=>(string)($a['title']??$k),'score'=>$score];}
    usort($scores,static fn($a,$b)=>$b['score']<=>$a['score']);return array_slice($scores,0,$limit);
}

function ml_class_starter(string $classId): array
{
    $mis=ml_misconception_summary($classId);$retrieval=adaptive_retrieval_state_rows();$due=0;$today=date('Y-m-d');foreach($retrieval as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['due_date']??'9999-12-31')<=$today)$due++;
    $top=$mis[0]??null;return ['minutes'=>min(8,max(3,$top?6:3)),'title'=>$top?'Oprav mentální model: '.$top['label']:'Rychlé retrieval opakování','detail'=>$top?'Nejdřív individuální odpověď, potom krátké porovnání ve dvojici. '.$top['student_count'].' studentů má dostupnou podobnou evidence stopu.':$due.' retrieval položek je právě splatných napříč třídou.','misconception'=>$top,'due'=>$due];
}

function ml_intervention_groups(string $classId): array
{
    $students=project_students_for_class($classId);$groups=[];
    foreach($students as $key=>$student){foreach(ml_misconception_summary($classId,(string)$key) as $m){if((int)$m['count']<2)continue;$code=(string)$m['code'];if(!isset($groups[$code]))$groups[$code]=['code'=>$code,'label'=>$m['label'],'students'=>[],'count'=>0];$groups[$code]['students'][]=['key'=>(string)$key,'label'=>(string)($student['label']??'Student')];$groups[$code]['count']+=(int)$m['count'];break;}}
    $groups=array_values($groups);usort($groups,static fn($a,$b)=>count($b['students'])<=>count($a['students']));return array_slice($groups,0,6);
}

function ml_browser_lab_spec(string $classId,string $topic,array $article=[]): array
{
    $family=ml_topic_family($topic,$article);$r=reality_demo_spec($classId,$topic,$article);
    if($family==='ops')return ['type'=>'terminal','title'=>'Browser terminal · bezpečný sandbox','brief'=>'Zkus příkazy jako ve skutečné diagnostice. Nic se nespouští na serveru – jde o deterministický model scénáře.','commands'=>['status','logs','listen','network','test'],'scenario'=>(string)($r['title']??$topic)];
    return ['type'=>'visual','title'=>'Visual Editor · před/po','brief'=>'Změň jen několik parametrů a sleduj, jak ovlivní hierarchy, čitelnost a responsive chování.','scenario'=>(string)($r['title']??$topic)];
}

function ml_terminal_command(string $topic,string $command): array
{
    $c=trim(ml_lower($command));$hay=ml_lower($topic);
    if(preg_match('/systemctl status|\bstatus\b/',$c))return ['ok'=>true,'out'=>str_contains($hay,'systemd')||str_contains($hay,'service')?"● app.service - Demo service\n   Active: failed (Result: exit-code)\n   Main PID: 2418 (code=exited, status=1)":"● nginx.service - nginx\n   Active: active (running)",'hint'=>'Status říká stav služby, ne vždy příčinu.'];
    if(preg_match('/journalctl|\blogs\b/',$c))return ['ok'=>true,'out'=>"Sep 12 14:03 app[2418]: connect() failed (111: Connection refused)\nSep 12 14:03 proxy[932]: upstream 127.0.0.1:8080 unavailable",'hint'=>'Log přidal důkaz o konkrétním upstreamu.'];
    if(preg_match('/ss |netstat|\blisten\b/',$c))return ['ok'=>true,'out'=>"LISTEN 0 511 0.0.0.0:80 nginx\nLISTEN 0 128 127.0.0.1:22 sshd",'hint'=>'Listener ověřuje, zda proces skutečně přijímá spojení na daném socketu.'];
    if(preg_match('/ip route|ping|dig|nslookup|\bnetwork\b/',$c))return ['ok'=>true,'out'=>str_contains($hay,'dns')?";; ->>HEADER<<- status: NXDOMAIN\n;; SERVER: 10.0.0.53#53":"default via 10.0.0.1 dev eth0\n10.0.0.0/24 dev eth0 proto kernel",'hint'=>'Síťový test má odpovědět na jednu konkrétní hypotézu.'];
    if(preg_match('/curl|nc |telnet|\btest\b/',$c))return ['ok'=>true,'out'=>"HTTP/1.1 502 Bad Gateway\nserver: nginx\ncontent-length: 157",'hint'=>'HTTP 502 dokazuje, že ses dostal k proxy; problém je dál v řetězci.'];
    return ['ok'=>false,'out'=>'Sandbox tento příkaz v této lekci nesimuluje. Zkus: status, logs, listen, network nebo test.','hint'=>'Cílem není memorovat příkazy, ale zvolit nástroj podle hypotézy.'];
}

// Live classroom pulse ------------------------------------------------------
function ml_live_rows(): array { return ml_store('live'); }
function ml_live_active(string $classId): ?array {foreach(array_reverse(ml_live_rows()) as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['status']??'')==='open')return $r;return null;}
function ml_live_start(string $classId,string $question,array $options,int $correct=-1): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel.');$question=trim(u_substr($question,0,400));$options=array_values(array_filter(array_map(static fn($x)=>trim(u_substr((string)$x,0,180)),$options)));if($question===''||count($options)<2)throw new RuntimeException('Doplň otázku a alespoň dvě možnosti.');$row=['id'=>ml_id('live'),'class_id'=>$classId,'question'=>$question,'options'=>$options,'correct'=>$correct,'status'=>'open','phase'=>'individual','responses'=>[],'created_at'=>date(DATE_ATOM)];ml_store_update('live',static function(array $rows) use($classId,$row): array {foreach($rows as &$r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['status']??'')==='open')$r['status']='closed';unset($r);$rows[]=$row;return $rows;});return $row;
}
function ml_live_set_phase(string $id,string $phase): void {if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel.');ml_store_update('live',static function(array $rows) use($id,$phase): array {foreach($rows as &$r)if(is_array($r)&&(string)($r['id']??'')===$id){$r['phase']=in_array($phase,['individual','discuss','revote','closed'],true)?$phase:'individual';if($r['phase']==='closed')$r['status']='closed';}unset($r);return $rows;});}
function ml_live_answer(string $classId,string $studentKey,string $id,int $answer): array {$found=null;ml_store_update('live',static function(array $rows) use($classId,$studentKey,$id,$answer,&$found): array {foreach($rows as &$r){if(!is_array($r)||(string)($r['id']??'')!==$id||(string)($r['class_id']??'')!==$classId||(string)($r['status']??'')!=='open')continue;$phase=(string)($r['phase']??'individual');$r['responses'][$studentKey]=is_array($r['responses'][$studentKey]??null)?$r['responses'][$studentKey]:[];$r['responses'][$studentKey][$phase]=['answer'=>$answer,'at'=>date(DATE_ATOM)];$found=$r;break;}unset($r);if(!$found)throw new RuntimeException(tr('Aktivní otázka už není dostupná.'));return $rows;});return $found;}
function ml_live_distribution(array $live,string $phase): array {$out=array_fill(0,count((array)($live['options']??[])),0);foreach((array)($live['responses']??[]) as $r){$a=$r[$phase]['answer']??null;if(is_int($a)||ctype_digit((string)$a))if(isset($out[(int)$a]))$out[(int)$a]++;}return $out;}

// Authoring + student-created challenges -----------------------------------
function ml_scenario_validate(array $row): array {$errors=[];$warnings=[];$title=trim((string)($row['title']??''));$brief=trim((string)($row['brief']??''));if($title==='')$errors[]=tr('Chybí název.');if($brief==='')$errors[]=tr('Chybí situace.');elseif(u_strlen($brief)<20)$warnings[]=tr('Situace je velmi krátká; doplň konkrétní důkaz nebo kontext.');$choices=array_values(array_filter((array)($row['choices']??[]),static fn($x)=>trim((string)$x)!==''));if(count($choices)<2)$errors[]=tr('Jsou potřeba alespoň dvě volby.');$norm=array_map(static fn($x)=>ml_lower(trim((string)$x)),$choices);if(count(array_unique($norm))!==count($norm))$errors[]=tr('Volby musí být odlišné.');if((int)($row['correct']??-1)<0||(int)($row['correct']??-1)>=count($choices))$errors[]=tr('Není určena správná volba.');if(trim((string)($row['why']??''))==='')$warnings[]=tr('Doplň vysvětlení, co výsledek dokazuje.');return ['ok'=>!$errors,'errors'=>$errors,'warnings'=>$warnings,'choices'=>$choices];}
function ml_scenario_save(array $input,bool $teacher=true): array {if($teacher&&!teacher_export_authenticated())throw new RuntimeException('Pouze učitel.');$id=trim((string)($input['id']??''));if($id===''||!isset(ml_store('scenarios')[$id]))$id=ml_id('scn');$row=['id'=>$id,'class_id'=>(string)($input['class_id']??''),'title'=>trim(u_substr((string)($input['title']??''),0,180)),'brief'=>trim(u_substr((string)($input['brief']??''),0,1000)),'choices'=>array_values(array_filter([(string)($input['choice_0']??''),(string)($input['choice_1']??''),(string)($input['choice_2']??'')],static fn($x)=>trim($x)!=='')),'correct'=>(int)($input['correct']??0),'why'=>trim(u_substr((string)($input['why']??''),0,1000)),'status'=>$teacher?(string)($input['status']??'draft'):'pending','created_by'=>$teacher?teacher_display_name():(string)($input['student_key']??''),'updated_at'=>date(DATE_ATOM)];$v=ml_scenario_validate($row);if(!$v['ok'])throw new RuntimeException(implode(' ',$v['errors']));$row['choices']=$v['choices'];if(!in_array($row['status'],['draft','published','pending','rejected'],true))$row['status']='draft';storage_map_update(ml_store_path('scenarios'),$id,static fn(?array $current): array => $row);return $row;}
function ml_scenarios_for_class(string $classId,string $status='published'): array {return array_values(array_filter(ml_store('scenarios'),static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&($status===''||(string)($r['status']??'')===$status)));}

function ml_performance_budget(): array {return ['js_kb'=>260,'css_kb'=>260,'dom_nodes'=>2200,'ttfb_ms'=>250,'lcp_ms'=>2500];}

function ml_goal_get(string $classId,string $studentKey): array
{
    $row=ml_store('goals')[$classId.'|'.$studentKey]??[];return is_array($row)?$row:[];
}
function ml_goal_save(string $classId,string $studentKey,string $strength,string $focus,string $evidence): array
{
    $id=$classId.'|'.$studentKey;return (array)storage_map_update(ml_store_path('goals'),$id,static fn(?array $current): array => ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'strength'=>trim(u_substr($strength,0,500)),'focus'=>trim(u_substr($focus,0,500)),'evidence'=>trim(u_substr($evidence,0,700)),'updated_at'=>date(DATE_ATOM)]);
}
function ml_current_lesson_number(string $classId,array $schoolYear): int
{
    $rows=adaptive_school_year_rows($schoolYear,$classId);$today=date('Y-m-d');$best=1;
    foreach($rows as $r){if(!is_array($r))continue;$d=(string)($r['date']??'');$n=(int)($r['lesson_number']??0);if($n>0&&$d!==''&&$d<=$today)$best=$n;if($d>$today)break;}
    return max(1,min(28,$best));
}
function ml_teacher_copilot_plan(string $classId,array $lesson): array
{
    $starter=ml_class_starter($classId);$groups=ml_intervention_groups($classId);$mis=ml_misconception_summary($classId);$goal=(string)($lesson['goal']??$lesson['title']??'');
    return [
      ['minutes'=>'0–'.$starter['minutes'],'title'=>$starter['title'],'text'=>$starter['detail']],
      ['minutes'=>$starter['minutes'].'–25','title'=>'Reality Demo + prediction','text'=>'Nejdřív nechat studenty předpovědět výsledek, potom ukázat důsledek a hranici důkazu.'],
      ['minutes'=>'25–50','title'=>'Worked example → completion','text'=>'Jeden celý vzor, druhý s chybějícími kroky. Cíl: '.$goal],
      ['minutes'=>'50–70','title'=>'Samostatný transfer','text'=>'Nový kontext stejného principu; nehodnotit povrchovou shodu.'],
      ['minutes'=>'70–82','title'=>$groups?'Mini-intervence / peer instruction':'Peer review / QA','text'=>$groups?count($groups).' mini-skupin podle podobného misconception.':'Studenti porovnají důkaz a argument, ne jen odpověď.'],
      ['minutes'=>'82–90','title'=>'Exit ticket','text'=>$mis?'Ověř nejčastější misconception: '.$mis[0]['label']:'Jedna transfer otázka + confidence.'],
    ];
}

function ml_scenario_set_status(string $id,string $status): void
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel.');if(!in_array($status,['draft','published','pending','rejected'],true))throw new RuntimeException('Neplatný stav.');$moderator=teacher_display_name();ml_store_update('scenarios',static function(array $rows) use($id,$status,$moderator): array {if(!is_array($rows[$id]??null))throw new RuntimeException('Scénář nebyl nalezen.');if($status==='published'){ $v=ml_scenario_validate($rows[$id]); if(!$v['ok'])throw new RuntimeException(implode(' ',$v['errors'])); if(trim((string)($rows[$id]['why']??''))==='')throw new RuntimeException('Před publikováním doplň, co výsledek dokazuje.'); }$rows[$id]['status']=$status;$rows[$id]['moderated_by']=$moderator;$rows[$id]['updated_at']=date(DATE_ATOM);return $rows;});
}

// v41 completion layer ------------------------------------------------------
function ml_interleave_group(string $topic,array $article=[]): string
{
    $h=ml_lower($topic.' '.(string)($article['title']??'').' '.(string)($article['summary']??''));
    $map=[
      'dns'=>['dns','resolver','hostname'],'network'=>['routing','route','gateway','subnet','vlan','tcp','packet','dhcp','firewall'],
      'service'=>['systemd','service','nginx','proxy','ssh','listener','journal'],'storage'=>['storage','inode','filesystem','backup','restore'],
      'security'=>['security','permission','hardening','auth','certificate'],'design-type'=>['typograph','font','hierarch','readability'],
      'design-layout'=>['layout','grid','responsive','spacing','component'],'design-a11y'=>['accessib','contrast','form','error'],'design-image'=>['crop','raster','vector','image','export']
    ];
    foreach($map as $g=>$words)foreach($words as $w)if(str_contains($h,$w))return $g;
    return ml_topic_family($topic,$article)==='ops'?'ops-other':'design-other';
}
function ml_interleave_candidates(array $rows,int $limit=3): array
{
    if(count($rows)<=1)return array_slice($rows,0,max(1,$limit));$picked=[];$used=[];$pool=$rows;
    while($pool&&count($picked)<max(1,$limit)){
        $idx=null;
        foreach($pool as $i=>$r){$g=ml_interleave_group((string)($r['topic']??''),(array)($r['article']??[]));if(!isset($used[$g])){$idx=$i;break;}}
        if($idx===null)$idx=array_key_first($pool);$r=$pool[$idx];$picked[]=$r;$used[ml_interleave_group((string)($r['topic']??''),(array)($r['article']??[]))]=true;array_splice($pool,(int)$idx,1);
    }
    return $picked;
}

function ml_failure_scenario(string $classId): array
{
    if(in_array($classId,['class_3a','class_4a'],true)){
        $set=[
          ['q'=>'Web vrací 502, nginx běží. Jaký test nejlépe zúží problém jako první?','o'=>['Restartovat celý server','Ověřit upstream/listener a log proxy','Vypnout firewall'],'c'=>1],
          ['q'=>'Přes IP služba funguje, přes hostname ne. Který důkaz potřebuješ jako první?','o'=>['DNS odpověď resolveru','CPU load','Restart aplikace'],'c'=>0],
          ['q'=>'Aplikace hlásí „No space left“, ale disk má volnou kapacitu. Co ověřit?','o'=>['Inody/filesystem limity','DNS TTL','TLS certifikát'],'c'=>0],
          ['q'=>'SSH vrací publickey denied. Co je nejpřesnější další krok?','o'=>['Ověřit použitý klíč, permissions a auth log','Rebootovat server','Změnit gateway'],'c'=>0],
        ];
    } else {
        $set=[
          ['q'=>'CTA je na mobilu téměř neviditelné. Co ověřit nejdřív?','o'=>['Kontrast, hierarchy a dostupný prostor','Přidat další barvu','Zvětšit logo'],'c'=>0],
          ['q'=>'Karta funguje na desktopu, na 320 px přetéká. Co změnit nejdřív?','o'=>['Pravidlo layoutu/min-width a wrapping','Přidat stín','Změnit font rodinu'],'c'=>0],
          ['q'=>'Formulář po chybě jen zčervená. Co chybí?','o'=>['Konkrétní text chyby a vazba na pole','Více animace','Gradient'],'c'=>0],
          ['q'=>'Nadpis je obrovský, ale hierarchy je pořád nejasná. Co řešit?','o'=>['Vztahy velikost/váha/spacing/pozice','Ještě větší nadpis','Více barev'],'c'=>0],
        ];
    }
    $i=abs(crc32($classId.'|'.date('Y-m-d'))) % count($set);return $set[$i];
}
function ml_failure_inject(string $classId): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel.');$s=ml_failure_scenario($classId);return ml_live_start($classId,$s['q'],$s['o'],$s['c']);
}

function ml_scenario_templates(string $classId): array
{
    if(in_array($classId,['class_3a','class_4a'],true))return [
      'network'=>['label'=>'Síťová diagnostika','title'=>'IP funguje, služba ne','brief'=>'Student má několik důkazů z IP, DNS, portu a aplikace. Musí vybrat test, který nejlépe rozliší možné vrstvy.','choices'=>['Ověřit konkrétní vrstvu podle hypotézy','Restartovat vše','Změnit více věcí současně'],'correct'=>0,'why'=>'Správný test mění jednu nejistotu na důkaz.'],
      'service'=>['label'=>'Service incident','title'=>'Služba je active, ale klient dostává chybu','brief'=>'Status služby vypadá dobře, klient přesto selhává. Co ověřit dál?','choices'=>['Listener/log + skutečný request','Reboot','Vymazat logy'],'correct'=>0,'why'=>'Active stav ještě nedokazuje end-to-end funkčnost.'],
    ];
    return [
      'critique'=>['label'=>'Design critique','title'=>'Vypadá dobře, ale cíl se ztrácí','brief'=>'Návrh je estetický, uživatel ale nepozná hlavní akci. Co změnit jako první?','choices'=>['Hierarchy a vztahy prvků','Přidat dekoraci','Změnit všechny barvy'],'correct'=>0,'why'=>'Design rozhodnutí se má opírat o cíl uživatele.'],
      'responsive'=>['label'=>'Responsive bug','title'=>'Karta přetéká na 320 px','brief'=>'Desktop varianta funguje, úzký viewport rozbije obsah. Jaký zásah je nejpřesnější?','choices'=>['Opravit layout/wrapping/min-width','Zmenšit logo','Přidat stín'],'correct'=>0,'why'=>'Responsive problém je problém pravidla rozložení, ne dekorace.'],
    ];
}

function ml_tip_save(string $classId,string $studentKey,string $topic,string $text,bool $teacher=false): array
{
    $text=trim(u_substr($text,0,700));if(u_strlen($text)<15)throw new RuntimeException(tr('Tip musí obsahovat alespoň jednu konkrétní větu.'));$row=['id'=>ml_id('tip'),'class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'text'=>$text,'status'=>$teacher?'published':'pending','created_at'=>date(DATE_ATOM),'moderated_by'=>$teacher?teacher_display_name():''];storage_map_update(ml_store_path('class_tips'),(string)$row['id'],static fn(?array $current): array => $row);return $row;
}
function ml_tips_for_topic(string $classId,string $topic,string $status='published'): array
{
    return array_values(array_filter(ml_store('class_tips'),static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['topic']??'')===$topic&&($status===''||(string)($r['status']??'')===$status)));
}
function ml_tips_for_class(string $classId,string $status=''): array
{
    return array_values(array_filter(ml_store('class_tips'),static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId&&($status===''||(string)($r['status']??'')===$status)));
}
function ml_tip_set_status(string $id,string $status): void
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel.');if(!in_array($status,['pending','published','rejected'],true))throw new RuntimeException('Neplatný stav tipu.');$moderator=teacher_display_name();storage_map_update(ml_store_path('class_tips'),$id,static function(?array $row) use($status,$moderator): array {if(!is_array($row))throw new RuntimeException('Tip nebyl nalezen.');$row['status']=$status;$row['moderated_by']=$moderator;$row['moderated_at']=date(DATE_ATOM);return $row;});
}

function ml_portfolio_narrative_get(string $classId,string $studentKey,string $recordId): array
{
    $row=ml_store('portfolio_narratives')[$classId.'|'.$studentKey.'|'.$recordId]??[];return is_array($row)?$row:[];
}
function ml_portfolio_narrative_save(string $classId,string $studentKey,string $recordId,array $input): array
{
    $id=$classId.'|'.$studentKey.'|'.$recordId;$fields=['problem','role','decision','failed','iteration','now'];$row=['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'record_id'=>$recordId,'updated_at'=>date(DATE_ATOM)];foreach($fields as $f)$row[$f]=trim(u_substr((string)($input[$f]??''),0,900));$nonEmpty=0;foreach($fields as $f)if($row[$f]!=='')$nonEmpty++;if($nonEmpty<3)throw new RuntimeException(tr('Doplň alespoň tři části příběhu projektu.'));storage_map_update(ml_store_path('portfolio_narratives'),$id,static fn(?array $current): array => $row);return $row;
}
function ml_goal_rows_for_class(string $classId): array
{
    return array_values(array_filter(ml_store('goals'),static fn($r)=>is_array($r)&&(string)($r['class_id']??'')===$classId));
}

function ml_scenario_submit(string $classId,string $studentKey,string $scenarioId,int $answer,int $confidence=0): array
{
    $row=ml_store('scenarios')[$scenarioId]??null;if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['status']??'')!=='published')throw new RuntimeException(tr('Scénář není dostupný.'));$options=(array)($row['choices']??[]);if(!isset($options[$answer]))throw new RuntimeException(tr('Neplatná volba.'));$correct=$answer===(int)($row['correct']??-1);$topic=(string)($row['topic']??('scenario-'.$scenarioId));ml_record_learning_event($classId,$studentKey,$topic,'custom_scenario',$correct,['confidence'=>max(0,min(3,$confidence)),'selected'=>$answer,'correct_index'=>(int)($row['correct']??-1)]);return ['correct'=>$correct,'why'=>(string)($row['why']??''),'correct_index'=>(int)($row['correct']??-1)];
}
