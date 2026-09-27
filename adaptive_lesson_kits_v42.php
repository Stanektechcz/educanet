<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCAnet v42 · Adaptive Lesson Kits
 *
 * A lesson always stays understandable without AI or an external video. This
 * layer only adapts the amount of scaffolding and exposes reusable lesson
 * assets (video guide, slides, worksheet, prompt pack and exam preparation).
 */

function v42_lessons_for_class(string $classId): array
{
    static $cache=[]; if(isset($cache[$classId])) return $cache[$classId];
    global $modules,$nextLessons,$extendedLessons;
    $module = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    $rows = [[
        'id'=>'lesson_1_primary','number'=>1,'title'=>'Lekce 1 · Základní dvouhodinový blok',
        'subtitle'=>'2 × 45 minut','goal'=>(string)($module['lesson_note'] ?? 'Úvodní dvouhodinový blok.'),
        'knowledge'=>array_slice(array_keys((array)($module['knowledgebase'] ?? [])),0,5),
        'steps'=>[['id'=>'primary','title'=>'Primární blok','kind'=>'manual']],
        'worksheet'=>[], 'teacher_notes'=>[(string)($module['intro'] ?? '')], 'source'=>'module',
    ]];

    // Student runtime already has the class-specific lesson cache in memory.
    if (isset($nextLessons[$classId]) && is_array($nextLessons[$classId])) {
        $r=$nextLessons[$classId];$r['number']=2;$r['source']='next';$rows[]=$r;
        foreach((array)($extendedLessons[$classId]??[]) as $lesson){if(is_array($lesson)){$lesson['source']='extended';$rows[]=$lesson;}}
    } else {
        $next = require __DIR__ . '/next_lessons.php';
        if (isset($next[$classId]) && is_array($next[$classId])) { $r=$next[$classId]; $r['number']=2; $r['source']='next'; $rows[]=$r; }
        $extended = require __DIR__ . '/extended_lessons.php';
        foreach (['lessons_plus.php','lessons_more.php','lessons_ecosystem.php','lessons_yearpack.php','lessons_v30.php'] as $file) {
            $extra = require __DIR__ . '/' . $file;
            if (isset($extra[$classId]) && is_array($extra[$classId])) $extended[$classId]=array_merge($extended[$classId]??[],$extra[$classId]);
        }
        foreach ((array)($extended[$classId] ?? []) as $lesson) { if(is_array($lesson)){ $lesson['source']='extended'; $rows[]=$lesson; } }
    }
    usort($rows,static fn($a,$b)=>(int)($a['number']??0)<=>(int)($b['number']??0));
    $by=[]; foreach($rows as $row){$n=(int)($row['number']??0);if($n>=1&&$n<=28)$by[$n]=$row;} ksort($by);
    return $cache[$classId]=array_values($by);
}

function v42_find_lesson(string $classId, int $number): ?array
{
    foreach(v42_lessons_for_class($classId) as $lesson) if((int)($lesson['number']??0)===$number) return $lesson;
    return null;
}


function v42_lesson_url(array $lesson): string
{
    $n=(int)($lesson['number']??0);
    if($n<=1)return '?view=dashboard';
    if($n===2)return '?view=next_lesson';
    return module_url('course_lesson',['lesson'=>(string)($lesson['id']??'')]);
}

function v42_lesson_key(string $classId, array $lesson): string
{
    return $classId . '|L' . str_pad((string)((int)($lesson['number']??0)),2,'0',STR_PAD_LEFT);
}

function v42_topic_rows(array $lesson, array $module): array
{
    $out=[];
    foreach(array_values(array_unique(array_map('strval',(array)($lesson['knowledge']??[])))) as $topic){
        $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];
        $out[]=['topic'=>$topic,'title'=>(string)($article['title']??$topic),'summary'=>trim((string)($article['summary']??'')),'article'=>$article];
    }
    return $out;
}

function v42_lane_preferences(): array { return adaptive_store('lesson_lane_preferences'); }
function v42_lane_preference_save(string $classId,string $studentKey,array $lesson,string $lane): void
{
    if(!in_array($lane,['auto','guided','standard','challenge'],true)) $lane='auto';
    $id=v42_lesson_key($classId,$lesson).'|'.$studentKey;
    storage_map_update(adaptive_store_path('lesson_lane_preferences'),$id,static fn(?array $current): ?array => $lane==='auto'?null:['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)($lesson['number']??0),'lane'=>$lane,'updated_at'=>date(DATE_ATOM)]);
}

function v42_active_recovery_for_lesson(string $classId,string $studentKey,int $lessonNumber): bool
{
    foreach(adaptive_absence_rows() as $r){
        if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(string)($r['student_key']??'')!==$studentKey)continue;
        if((int)($r['lesson_number']??0)===$lessonNumber && (string)($r['status']??'recovery')!=='done') return true;
    }
    return false;
}

function v42_lesson_signal_summary(string $classId,string $studentKey,array $lesson): array
{
    $topics=array_values(array_unique(array_map('strval',(array)($lesson['knowledge']??[]))));
    $events=[]; foreach($topics as $topic) foreach(ml_student_events($classId,$studentKey,$topic) as $e) if(is_array($e)) $events[]=$e;
    usort($events,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));
    $events=array_slice($events,0,30);
    $correct=0;$wrong=0;$overconfident=0;$transferOk=0;
    foreach($events as $e){$ok=!empty($e['correct']);$ok?$correct++:$wrong++;if(!$ok&&(int)($e['confidence']??0)>=3)$overconfident++;if($ok&&!empty($e['transfer']))$transferOk++;}
    $help=0;foreach(adaptive_store('help_events') as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(string)($r['student_key']??'')!==$studentKey)continue;if(in_array((string)($r['topic']??''),$topics,true))$help++;}
    $total=$correct+$wrong;$accuracy=$total?($correct/$total):null;
    return ['events'=>$total,'correct'=>$correct,'wrong'=>$wrong,'accuracy'=>$accuracy,'overconfident'=>$overconfident,'transfer_ok'=>$transferOk,'help'=>$help,'topics'=>$topics];
}

function v42_learning_lane(string $classId,string $studentKey,array $lesson): array
{
    $id=v42_lesson_key($classId,$lesson).'|'.$studentKey;$pref=(string)(v42_lane_preferences()[$id]['lane']??'auto');
    $signals=v42_lesson_signal_summary($classId,$studentKey,$lesson);$number=(int)($lesson['number']??0);
    if($pref!=='auto')$lane=$pref;
    elseif(v42_active_recovery_for_lesson($classId,$studentKey,$number))$lane='guided';
    elseif($signals['overconfident']>=1||$signals['wrong']>=2||$signals['help']>=3)$lane='guided';
    elseif($signals['events']>=4 && ($signals['accuracy']??0)>=0.8 && $signals['transfer_ok']>=1)$lane='challenge';
    else $lane='standard';
    $meta=[
      'guided'=>['label'=>'Více podpory','tag'=>'GUIDED','description'=>'Více worked examples, menší kroky a kontrola misconception před samostatnou prací.','accent'=>'support'],
      'standard'=>['label'=>'Vyvážená cesta','tag'=>'STANDARD','description'=>'Ukázka → guided practice → samostatná aplikace → transfer.','accent'=>'standard'],
      'challenge'=>['label'=>'Rychleji k výzvě','tag'=>'STRETCH','description'=>'Méně opakování, rychlejší transfer a obtížnější rozšíření.','accent'=>'challenge'],
    ][$lane];
    $reason='Výchozí cesta podle aktuální evidence.';
    if($pref!=='auto')$reason='Tuto cestu sis zvolil/a ručně pro tuto lekci.';
    elseif(v42_active_recovery_for_lesson($classId,$studentKey,$number))$reason='Lekce je v Recovery Path, proto dostáváš více podpory.';
    elseif($lane==='guided'&&$signals['overconfident']>0)$reason='Systém zachytil jistou, ale chybnou odpověď – nejdřív opravíme mentální model.';
    elseif($lane==='guided')$reason='V tématech této lekce se opakovaly chyby nebo více žádostí o jiné vysvětlení.';
    elseif($lane==='challenge')$reason='Dosavadní evidence i transfer jsou silné, takže můžeš rychleji k náročnějším úlohám.';
    return array_merge(['lane'=>$lane,'reason'=>$reason,'signals'=>$signals,'preference'=>$pref],$meta);
}

function v42_lesson_resource_overrides(): array
{
    return adaptive_store('lesson_resources');
}

function v42_lesson_resource_override(string $classId,array $lesson): ?array
{
    $id=v42_lesson_key($classId,$lesson);
    $row=v42_lesson_resource_overrides()[$id]??null;
    return is_array($row)?$row:null;
}

function v42_teacher_resource_save(string $classId,array $lesson,string $url,string $title='',string $note=''): array
{
    if(!in_array($classId,['class_1a','class_2a','class_3a','class_4a'],true)) throw new RuntimeException('Neplatná třída.');
    $url=trim($url);$title=trim($title);$note=trim($note);
    if($url==='') throw new RuntimeException('Doplňte URL videa.');
    $parts=parse_url($url);
    if(!is_array($parts)||!in_array(strtolower((string)($parts['scheme']??'')),['http','https'],true)||trim((string)($parts['host']??''))==='') throw new RuntimeException('Video musí mít platnou http/https URL.');
    if($title==='') $title='Video k lekci';
    $id=v42_lesson_key($classId,$lesson);
    return (array)storage_map_update(adaptive_store_path('lesson_resources'),$id,static fn(?array $current): array => [
        'id'=>$id,'class_id'=>$classId,'lesson_number'=>(int)($lesson['number']??0),
        'type'=>'video','title'=>u_substr($title,0,180),'url'=>u_substr($url,0,1500),
        'note'=>u_substr($note,0,600),'teacher_override'=>true,'updated_at'=>date(DATE_ATOM),
    ]);
}

function v42_teacher_resource_remove(string $classId,array $lesson): void
{
    storage_map_update(adaptive_store_path('lesson_resources'),v42_lesson_key($classId,$lesson),static fn(?array $current): ?array => null);
}

function v42_lesson_videos(string $classId,array $lesson,array $learningResources): array
{
    $classMap=is_array($learningResources[$classId]??null)?$learningResources[$classId]:[];$found=[];
    $override=v42_lesson_resource_override($classId,$lesson);
    if(is_array($override)&&!empty($override['url'])) $found[(string)$override['url']]=$override;
    foreach((array)($lesson['knowledge']??[]) as $topic){foreach((array)($classMap[(string)$topic]??[]) as $r){if(!is_array($r)||(string)($r['type']??'')!=='video'||empty($r['url']))continue;$found[(string)$r['url']]=$r;}}
    if(!$found){foreach((array)($classMap['_default']??[]) as $r){if(is_array($r)&&(string)($r['type']??'')==='video'&&!empty($r['url']))$found[(string)$r['url']]=$r;}}
    $out=[];foreach(array_slice(array_values($found),0,3) as $r){$r['watch_for']='Sleduj pouze části, které přímo souvisejí s cílem lekce: '.trim((string)($lesson['goal']??''));$r['before']='Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.';$r['during']='Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.';$r['after']='Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.';$out[]=$r;}
    return $out;
}

function v42_microvideo_storyboard(string $classId,array $lesson,array $module): array
{
    $topics=v42_topic_rows($lesson,$module);$family=in_array($classId,['class_3a','class_4a'],true)?'ops':'design';$scenes=[];
    $scenes[]=['time'=>'0:00–0:12','title'=>'Situace','voice'=>'Začínáme konkrétním problémem, ne definicí. '.trim((string)($lesson['goal']??'')),'visual'=>'Jedna realistická situace z Reality Demo.'];
    foreach(array_slice($topics,0,2) as $i=>$t){$scenes[]=['time'=>$i===0?'0:12–0:35':'0:35–0:58','title'=>$t['title'],'voice'=>$t['summary']!==''?$t['summary']:'Pojmenuj, co se v této části mění a co tím ověřujeme.','visual'=>$family==='ops'?'Symptom → test → důkaz.':'Před → jedna změna → po.'];}
    $scenes[]=['time'=>'0:58–1:15','title'=>'Typická chyba','voice'=>$family==='ops'?'Neměň několik věcí současně. Každý test má zúžit jednu nejistotu.':'Nehodnoť jen podle vkusu. Každá změna má řešit konkrétní cíl uživatele.','visual'=>'Kontrast správného a slepého postupu.'];
    $scenes[]=['time'=>'1:15–1:30','title'=>'Zkus si','voice'=>'Teď princip přenes do nového případu bez kopírování hotového řešení.','visual'=>'Krátká výzva + stop frame před odpovědí.'];
    return $scenes;
}

function v42_slide_deck(string $classId,array $lesson,array $module): array
{
    $topics=v42_topic_rows($lesson,$module);$family=in_array($classId,['class_3a','class_4a'],true)?'ops':'design';$slides=[];
    $slides[]=['kicker'=>'Start','title'=>(string)($lesson['title']??'Lekce'),'body'=>(string)($lesson['goal']??''),'note'=>'Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.'];
    $slides[]=['kicker'=>'Proč to řešíme','title'=>'Reálný problém','body'=>$family==='ops'?'V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.':'V praxi nestačí, aby návrh „vypadal dobře“. Musí splnit cíl uživatele a fungovat v reálném kontextu.','note'=>'Použij krátkou situaci z Reality Demo.'];
    foreach(array_slice($topics,0,3) as $t)$slides[]=['kicker'=>'Mentální model','title'=>$t['title'],'body'=>$t['summary']!==''?$t['summary']:'Pojmenuj vstup, změnu a očekávaný výsledek.','note'=>'Nech studenty předpovědět výsledek před animací.'];
    $slides[]=['kicker'=>'Worked example','title'=>'Nejdřív celý vzor','body'=>$family==='ops'?'Symptom → hypotéza → nejmenší test → evidence → bezpečná změna → validace.':'Cíl → konkrétní problém → pravidlo → jedna změna → ověření před/po.','note'=>'Další příklad už nech s jedním chybějícím krokem.'];
    $slides[]=['kicker'=>'Typická chyba','title'=>'Co se často plete','body'=>$family==='ops'?'Pozitivní test jedné vrstvy neznamená, že celý systém funguje end-to-end.':'Silnější barva, větší text nebo více efektů nejsou automaticky lepší hierarchy/UX.','note'=>'Použij misconception data třídy, pokud existují.'];
    $slides[]=['kicker'=>'Practice','title'=>'Teď to udělej ty','body'=>'Vyřeš jeden nový případ. Zapiš předpověď, zásah a evidence, která výsledek potvrzuje nebo vyvrací.','note'=>'Učitel obchází třídu a ptá se „co tímto krokem dokazuješ?“.'];
    $slides[]=['kicker'=>'Transfer','title'=>'Stejný princip, jiný kontext','body'=>'Neopakuj povrchový postup. Rozpoznej stejný princip v jiné situaci a znovu jej ověř.','note'=>'Silná evidence mastery vzniká až při přenosu do nového kontextu.'];
    $slides[]=['kicker'=>'Exit ticket','title'=>'Jedna věta, jeden důkaz','body'=>'Vysvětli vlastními slovy nejdůležitější princip lekce a uveď, jak bys ověřil/a, že opravdu funguje.','note'=>'Z odpovědí vytvoř starter příští hodiny.'];
    return $slides;
}

function v42_prompt_pack(string $classId,array $lesson,array $module): array
{
    $topics=array_map(static fn($r)=>(string)$r['title'],v42_topic_rows($lesson,$module));$topicText=$topics?implode(', ',$topics):'téma lekce';$title=(string)($lesson['title']??'lekce');$goal=trim((string)($lesson['goal']??''));$subject=in_array($classId,['class_3a','class_4a'],true)?'operační systémy a počítačové sítě':'grafika a webdesign';
    $context="Jsem student EDUCANETu. Probírám {$title} v předmětu {$subject}. Cíl lekce: {$goal}. Klíčová témata: {$topicText}.";
    $guard='Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.';
    return [
      ['id'=>'simple','title'=>'Vysvětli mi to jednoduše','prompt'=>$context."\nVysvětli mi princip co nejjednodušeji v 5–7 větách. Použij jeden konkrétní příklad a na konci mi polož jednu kontrolní otázku. {$guard}"],
      ['id'=>'analogy','title'=>'Přirovnání + hranice přirovnání','prompt'=>$context."\nVysvětli téma pomocí přirovnání z běžného života. Pak výslovně napiš, kde přirovnání přestává platit, abych si nevytvořil chybný mentální model. {$guard}"],
      ['id'=>'socratic','title'=>'Sokratický tutor','prompt'=>$context."\nBuď můj sokratický tutor. Nepřednášej. Ptej se vždy jen na jednu otázku, vycházej z mé odpovědi a veď mě k tomu, abych princip odvodil sám. {$guard}"],
      ['id'=>'debug','title'=>'Najdi díru v mém postupu','prompt'=>$context."\nPošlu ti svůj postup/řešení. Najdi první místo, kde můj závěr není podložený důkazem nebo kde měním příliš mnoho věcí najednou. Neopravuj vše za mě; polož otázku, která mě dovede k lepšímu testu. {$guard}"],
      ['id'=>'transfer','title'=>'Vytvoř mi nový transfer úkol','prompt'=>$context."\nVytvoř jeden nový realistický problém, který používá stejný princip, ale v jiném kontextu. Nedávej řešení. Nejdřív chtěj moji hypotézu, potom důkaz a až nakonec mi dej zpětnou vazbu. {$guard}"],
      ['id'=>'oral','title'=>'Vyzkoušej mě ústně','prompt'=>$context."\nSimuluj ústní zkoušení. Polož 5 otázek od základní po aplikační. Po každé mé odpovědi stručně řekni, co bylo přesné, co chybělo, a polož navazující otázku. Známku navrhni až na konci. {$guard}"],
      ['id'=>'practical','title'=>'Praktická zkouška nanečisto','prompt'=>$context."\nPřiprav krátkou praktickou zkoušku nanečisto na 15–20 minut. Dej mi realistické zadání, success criteria a 3 kontrolní body. Během řešení mi neposkytuj hotový postup; reaguj jen na konkrétní evidence, které ti pošlu. {$guard}"],
      ['id'=>'review','title'=>'Zkontroluj moje vysvětlení','prompt'=>$context."\nPošlu ti své vysvětlení tématu. Ohodnoť pouze: správnost mentálního modelu, práci s důkazem, schopnost přenést princip a jednu největší mezeru. Pak mi dej jediný další krok k opravě. {$guard}"],
    ];
}

function v42_exam_pack(string $classId,array $lesson,array $module): array
{
    $topics=v42_topic_rows($lesson,$module);$family=in_array($classId,['class_3a','class_4a'],true)?'ops':'design';$questions=[];
    foreach(array_slice($topics,0,3) as $t)$questions[]='Vysvětli vlastními slovy „'.$t['title'].'“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.';
    foreach((array)($lesson['steps']??[]) as $step){if(is_array($step)&&!empty($step['question'])){$questions[]=(string)$step['question'];if(count($questions)>=5)break;}}
    while(count($questions)<5)$questions[]=$family==='ops'?'Dostaneš nový symptom. Jaký nejmenší bezpečný test bys provedl/a jako první a proč?':'Dostaneš nový návrh. Jaký konkrétní cíl uživatele bys ověřil/a jako první a jak?';
    $practical=$family==='ops'?'Vyřeš nový incident bez restartu „naslepo“. Odevzdej symptom, hypotézu, nejmenší test, evidence, jednu bezpečnou změnu a pozitivní + negativní validaci.':'Uprav nový návrh pro jiný obsah/viewport. Odevzdej před/po, popis cíle, jednu hlavní změnu, použitý princip a způsob ověření s uživatelem nebo checklistem.';
    return [
      'oral'=>array_slice($questions,0,5),
      'practical'=>$practical,
      'self_check'=>['Umím princip vysvětlit bez opisování definice.','Umím poznat typickou chybu.','Umím uvést důkaz, ne jen výsledek.','Umím princip použít v novém kontextu.','Vím, co bych ověřil/a jako další krok.'],
      'rubric'=>[
        ['level'=>'Rozvíjí se','text'=>'Zná pojmy, ale závěr ještě často stojí na intuici nebo kopírování postupu.'],
        ['level'=>'Kompetentní','text'=>'Použije princip ve známé situaci a dokáže uvést základní evidence.'],
        ['level'=>'Silný výkon','text'=>'Samostatně volí vhodný postup, vysvětluje důvod a validuje výsledek.'],
        ['level'=>'Mastery','text'=>'Přenese princip do nové situace, rozpozná limity důkazu a umí postup obhájit.'],
      ],
    ];
}

function v42_material_pack(string $classId,array $lesson,array $module): array
{
    $topics=v42_topic_rows($lesson,$module);$worksheet=array_values(array_filter(array_map('strval',(array)($lesson['worksheet']??[]))));
    if(!$worksheet)$worksheet=['Předpověď: co očekávám před změnou/testem?','Evidence: co přesně jsem pozoroval/a?','Závěr: co z evidence plyne a co ještě ne?','Transfer: kde jinde se stejný princip objeví?'];
    $glossary=[];foreach(array_slice($topics,0,6) as $t)$glossary[]=['term'=>$t['title'],'definition'=>$t['summary']!==''?$t['summary']:'Pojem si doplň vlastní jednou větou po praktické ukázce.'];
    $family=in_array($classId,['class_3a','class_4a'],true)?'ops':'design';
    return [
      'one_pager'=>['goal'=>(string)($lesson['goal']??''),'principles'=>array_map(static fn($t)=>$t['title'],array_slice($topics,0,4)),'definition_of_done'=>$family==='ops'?['Mám pozorovaný symptom.','Každý test má hypotézu.','Změnil/a jsem jednu věc.','Výsledek jsem ověřil/a z pohledu klienta.']:['Mám jasný cíl uživatele.','Umím pojmenovat konkrétní problém.','Změna používá konkrétní princip.','Ověřil/a jsem výsledek před/po nebo na jiném viewportu.']],
      'worksheet'=>$worksheet,
      'glossary'=>$glossary,
      'extension'=>$family==='ops'?'Vytvoř podobný incident s jinou kořenovou příčinou a napiš, jaké evidence mají spolužáka navést bez prozrazení řešení.':'Vytvoř druhou variantu řešení stejného cíle jiným principem a porovnej obě varianty podle stejného checklistu.',
      'teacher_checks'=>array_values(array_filter(array_map('strval',(array)($lesson['teacher_notes']??[])))),
    ];
}

function v42_lesson_pack(string $classId,array $lesson,array $module,array $learningResources,string $studentKey=''): array
{
    $lane=$studentKey!==''?v42_learning_lane($classId,$studentKey,$lesson):['lane'=>'standard','label'=>'Vyvážená cesta','tag'=>'STANDARD','description'=>'Ukázka → practice → transfer.','reason'=>'Teacher/preview mode.','signals'=>[],'preference'=>'auto'];
    return [
      'lesson'=>$lesson,
      'lane'=>$lane,
      'videos'=>v42_lesson_videos($classId,$lesson,$learningResources),
      'microvideo'=>v42_microvideo_storyboard($classId,$lesson,$module),
      'slides'=>v42_slide_deck($classId,$lesson,$module),
      'materials'=>v42_material_pack($classId,$lesson,$module),
      'prompts'=>v42_prompt_pack($classId,$lesson,$module),
      'exam'=>v42_exam_pack($classId,$lesson,$module),
    ];
}
