<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCAnet v31 · adaptive learning layer.
 * Storage remains file based to match the current deployment model. Every
 * record is tied to class + stable student key and can later be migrated to SQL.
 */

function adaptive_store_path(string $name): string
{
    return STORAGE_DIR . '/adaptive_' . preg_replace('/[^a-z0-9_\-]/i', '', $name) . '.json.php';
}

/** Čtení kolekce; u registrovaných „pouze přidávaných“ proudů (help_events, retrieval_attempts) čte JSONL (v58). */
function adaptive_store(string $name): array
{
    return storage_rows(adaptive_store_path($name));
}

/** v58 (F2): read-modify-write kolekce pod jedním zámkem – $fn(array $rows): array. Náhrada za adaptive_store_save. */
function adaptive_store_update(string $name, callable $fn): array
{
    return storage_update(adaptive_store_path($name), $fn);
}

function adaptive_student_key(string $classId): string
{
    if (function_exists('social_current_student_key')) {
        $key = social_current_student_key($classId);
        if ($key !== '') return $key;
    }
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    return $label !== '' ? project_student_key($classId, $label) : '';
}

function adaptive_student_label(string $classId, string $studentKey): string
{
    $students = project_students_for_class($classId);
    return (string)($students[$studentKey]['label'] ?? 'Student');
}


function adaptive_class_schedule(array $schoolYear, string $classId): array
{
    $all = is_array($schoolYear['meta']['class_schedule'] ?? null) ? $schoolYear['meta']['class_schedule'] : [];
    $row = is_array($all[$classId] ?? null) ? $all[$classId] : [];
    return array_replace([
        'label' => strtoupper(str_replace(['class_','a'], ['', '.A'], $classId)),
        'subject' => '',
        'periods' => [],
        'start' => '',
        'end' => '',
        'room' => '',
    ], $row);
}

function adaptive_class_schedule_label(array $schoolYear, string $classId): string
{
    $s = adaptive_class_schedule($schoolYear, $classId);
    $time = trim((string)($s['start'] ?? '')) !== '' ? ((string)$s['start'] . '–' . (string)$s['end']) : '2 × 45 minut';
    return trim((string)($s['label'] ?? '') . ' · ' . $time);
}

function adaptive_school_year_rows(array $schoolYear, string $classId): array
{
    $baseRows = array_values(array_filter((array)($schoolYear['calendar'] ?? []), 'is_array'));
    $exceptions = adaptive_store('calendar_exceptions');
    $byDate = [];
    foreach ($exceptions as $row) {
        if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
        $date = (string)($row['date'] ?? '');
        if ($date !== '') $byDate[$date] = $row;
    }

    // Agenda that must survive a cancelled Wednesday. Reserve is deliberately not part
    // of the queue: it is the buffer that absorbs one lost lesson without dropping content.
    // The final school-year closure stays fixed on its date whenever that date is available.
    $agenda = [];
    foreach ($baseRows as $row) {
        if ((string)($row['status'] ?? '') !== 'teaching') continue;
        if (in_array((string)($row['kind'] ?? ''), ['reserve', 'close', 'intro'], true)) continue;
        $agenda[] = $row;
    }

    $agendaIndex = 0;
    $effective = [];
    foreach ($baseRows as $base) {
        $date = (string)($base['date'] ?? '');
        $ex = $byDate[$date] ?? null;
        $type = is_array($ex) ? (string)($ex['type'] ?? 'note') : '';
        $isCancellation = in_array($type, ['cancelled', 'trip', 'director_day'], true);
        $isTeaching = (string)($base['status'] ?? '') === 'teaching';
        $isClose = in_array((string)($base['kind'] ?? ''), ['close', 'intro'], true); // kotvené bloky: nespotřebovávají frontu obsahu

        if (!$isTeaching) {
            $row = $base;
            if (is_array($ex)) {
                $row['exception'] = $ex;
                $row['calendar_note'] = (string)($ex['note'] ?? '');
            }
            $effective[] = $row;
            continue;
        }

        if ($isCancellation) {
            $row = $base;
            $row['exception'] = $ex;
            $row['status'] = 'no_school';
            $row['kind'] = $type;
            $row['original_lesson_number'] = $base['lesson_number'] ?? null;
            unset($row['lesson_number']);
            $row['title'] = trim((string)($ex['title'] ?? '')) !== ''
                ? (string)$ex['title']
                : adaptive_calendar_exception_label($type);
            $row['calendar_note'] = (string)($ex['note'] ?? '');
            $effective[] = $row;
            continue;
        }

        // Keep the final closing block anchored. Everything before it can shift into reserve.
        if ($isClose) {
            $row = $base;
            if (is_array($ex)) {
                $row['exception'] = $ex;
                $row['calendar_note'] = (string)($ex['note'] ?? '');
                if ($type === 'shifted' && trim((string)($ex['title'] ?? '')) !== '') {
                    $row['calendar_label'] = (string)$ex['title'];
                }
            }
            $remaining = count($agenda) - $agendaIndex;
            if ($remaining > 0 && (string)($base['kind'] ?? '') === 'close') {
                $row['schedule_warning'] = $remaining . ' plánovaný blok' . ($remaining === 1 ? '' : 'y') . ' se nevešel do dostupných střed. Použij náhradní termín nebo uprav plán.';
                $row['unscheduled_agenda'] = array_slice($agenda, $agendaIndex);
            }
            $effective[] = $row;
            continue;
        }

        if ($agendaIndex < count($agenda)) {
            $item = $agenda[$agendaIndex++];
            $row = $base;
            foreach (['kind','title','description','slot','lesson_number'] as $key) {
                unset($row[$key]);
                if (array_key_exists($key, $item)) $row[$key] = $item[$key];
            }
            $sourceDate = (string)($item['date'] ?? '');
            if ($sourceDate !== '' && $sourceDate !== $date) {
                $row['auto_shifted'] = true;
                $row['shifted_from'] = $sourceDate;
            }
            if (is_array($ex)) {
                $row['exception'] = $ex;
                $row['calendar_note'] = (string)($ex['note'] ?? '');
                if ($type === 'shifted' && trim((string)($ex['title'] ?? '')) !== '') {
                    $row['calendar_label'] = (string)$ex['title'];
                }
            }
            $effective[] = $row;
            continue;
        }

        // No queued content remains: keep the date as a real recovery/reserve buffer.
        $row = $base;
        $row['kind'] = 'reserve';
        $row['title'] = 'Rezerva / individuální podpora';
        $row['description'] = 'Volná kapacita pro odpadlou hodinu, Recovery Path, Mastery clinic nebo rozšíření pro rychlejší studenty.';
        unset($row['lesson_number']);
        if (is_array($ex)) {
            $row['exception'] = $ex;
            $row['calendar_note'] = (string)($ex['note'] ?? '');
        }
        $effective[] = $row;
    }

    return $effective;
}

function adaptive_calendar_exception_label(string $type): string
{
    return match ($type) {
        'cancelled' => 'Výuka odpadá',
        'trip' => 'Exkurze / školní akce',
        'director_day' => 'Ředitelské volno',
        'shifted' => 'Upravený / přesunutý blok',
        default => 'Poznámka ke kalendáři',
    };
}

function adaptive_calendar_exception_save(string $classId, string $date, string $type, string $title, string $note = ''): void
{
    if (!teacher_export_authenticated()) throw new RuntimeException('Kalendář může měnit pouze učitel.');
    if (!preg_match('/^20\d{2}-\d{2}-\d{2}$/', $date)) throw new RuntimeException('Neplatné datum.');
    $allowed = ['cancelled','trip','director_day','shifted','note'];
    if (!in_array($type, $allowed, true)) $type = 'note';
    $id = $classId . '|' . $date;
    $row = [
        'id'=>$id,'class_id'=>$classId,'date'=>$date,'type'=>$type,
        'title'=>trim($title) !== '' ? trim(u_substr($title,0,140)) : adaptive_calendar_exception_label($type),
        'note'=>trim(u_substr($note,0,800)),'updated_by'=>teacher_display_name(),'updated_at'=>date(DATE_ATOM),
    ];
    storage_map_update(adaptive_store_path('calendar_exceptions'), $id, static fn(?array $current): array => $row);
}

function adaptive_calendar_exception_remove(string $classId, string $date): void
{
    if (!teacher_export_authenticated()) throw new RuntimeException('Kalendář může měnit pouze učitel.');
    storage_map_update(adaptive_store_path('calendar_exceptions'), $classId.'|'.$date, static fn(?array $current): ?array => null);
}

function adaptive_absence_rows(): array { return adaptive_store('absences'); }

function adaptive_absence_get(string $classId, string $studentKey, string $date): ?array
{
    $row = adaptive_absence_rows()[$classId.'|'.$studentKey.'|'.$date] ?? null;
    return is_array($row) ? $row : null;
}

function adaptive_absence_mark(string $classId, string $studentKey, string $date, int $lessonNumber, string $lessonTitle): array
{
    if ($studentKey === '') throw new RuntimeException('Nejdřív se přihlas do své třídy.');
    if ($lessonNumber <= 0) throw new RuntimeException('Recovery Path je dostupný pouze pro strukturovanou lekci.');
    $id = $classId.'|'.$studentKey.'|'.$date;
    return (array)storage_map_update(adaptive_store_path('absences'), $id, static fn(?array $existing): array => array_replace($existing ?? [], [
        'id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'date'=>$date,
        'lesson_number'=>$lessonNumber,'lesson_title'=>u_substr($lessonTitle,0,180),'status'=>'recovery',
        'marked_at'=>$existing['marked_at'] ?? date(DATE_ATOM),'updated_at'=>date(DATE_ATOM),
    ]));
}

function adaptive_absence_clear(string $classId, string $studentKey, string $date): void
{
    $id = $classId.'|'.$studentKey.'|'.$date;
    storage_update_many([adaptive_store_path('absences'), adaptive_store_path('recovery')], static function (array $data) use ($id): array {
        foreach ($data as $path => $rows) { unset($rows[$id]); $data[$path] = $rows; }
        return $data;
    });
}

function adaptive_lesson_map(array $extendedLessons, string $classId): array
{
    $map=[];
    foreach ((array)($extendedLessons[$classId] ?? []) as $lesson) {
        if (!is_array($lesson)) continue;
        $n=(int)($lesson['number']??0); if($n>0)$map[$n]=$lesson;
    }
    return $map;
}

function adaptive_recovery_plan(string $classId, string $studentKey, string $date, array $extendedLessons, array $nextLessons = [], array $module = []): ?array
{
    $absence = adaptive_absence_get($classId,$studentKey,$date); if(!$absence) return null;
    $lessonMap = adaptive_lesson_map($extendedLessons,$classId);
    if (is_array($nextLessons[$classId] ?? null)) {
        $lesson2=$nextLessons[$classId]; $lesson2['number']=2; $lessonMap[2]=$lesson2;
    }
    if (!isset($lessonMap[1])) {
        $lessonMap[1]=['number'=>1,'title'=>'Lekce 1 · Základní blok','goal'=>(string)($module['lesson_note']??'Základní orientace v předmětu.'),'knowledge'=>array_slice(array_keys((array)($module['knowledgebase']??[])),0,3)];
    }
    $lesson = $lessonMap[(int)($absence['lesson_number']??0)] ?? null;
    if (!is_array($lesson)) return null;
    $knowledge = array_values(array_filter(array_map('strval',(array)($lesson['knowledge']??[]))));
    $planId=$classId.'|'.$studentKey.'|'.$date;
    $state = adaptive_store('recovery')[$planId] ?? [];
    $steps = [
        ['id'=>'model','title'=>'Pochop hlavní myšlenku','text'=>'Projdi si stručný mentální model a animovanou ukázku.','topic'=>$knowledge[0]??''],
        ['id'=>'guided','title'=>'Projdi jeden vedený příklad','text'=>'Nedělej celou hodinu znovu. Zaměř se na jeden reprezentativní postup.','topic'=>$knowledge[1]??($knowledge[0]??'')],
        ['id'=>'practice','title'=>'Zkus mini úkol bez nápovědy','text'=>'Ověř si, že princip umíš použít sám/sama.','topic'=>$knowledge[2]??($knowledge[0]??'')],
        ['id'=>'retrieve','title'=>'Vybav si princip z paměti','text'=>'Odpověz na krátkou retrieval otázku bez procházení poznámek.','topic'=>$knowledge[0]??''],
        ['id'=>'ready','title'=>'Jsem připraven/a pokračovat','text'=>'Napiš jednou větou, co bylo nejdůležitější a co případně ještě potřebuješ dovysvětlit.','topic'=>''],
    ];
    foreach($steps as &$step){$step['done']=!empty($state['steps'][$step['id']]['done']);}$step=null;
    $done=count(array_filter($steps,fn($s)=>!empty($s['done'])));
    return ['id'=>$planId,'absence'=>$absence,'lesson'=>$lesson,'steps'=>$steps,'done'=>$done,'total'=>count($steps),'complete'=>$done===count($steps),'reflection'=>(string)($state['reflection']??'')];
}

function adaptive_recovery_step(string $classId,string $studentKey,string $date,string $step,bool $done,string $reflection=''): void
{
    $allowed=['model','guided','practice','retrieve','ready']; if(!in_array($step,$allowed,true))throw new RuntimeException('Neplatný recovery krok.');
    $id=$classId.'|'.$studentKey.'|'.$date;
    $row=(array)storage_map_update(adaptive_store_path('recovery'),$id,static function(?array $row) use($classId,$studentKey,$date,$step,$done,$reflection): array {$row=$row??['class_id'=>$classId,'student_key'=>$studentKey,'date'=>$date,'steps'=>[]];$row['steps'][$step]=['done'=>$done,'at'=>date(DATE_ATOM)]; if($reflection!=='')$row['reflection']=trim(u_substr($reflection,0,1000));$row['updated_at']=date(DATE_ATOM);return $row;});
    $absence=adaptive_absence_get($classId,$studentKey,$date);if($absence){$plan=adaptive_recovery_raw_complete($row);storage_map_update(adaptive_store_path('absences'),$id,static function(?array $abs) use($plan): ?array {if($abs===null)return null;$abs['status']=$plan?'complete':'recovery';$abs['updated_at']=date(DATE_ATOM);return $abs;});}
}

function adaptive_recovery_raw_complete(array $row): bool
{
    foreach(['model','guided','practice','retrieve','ready'] as $s) if(empty($row['steps'][$s]['done'])) return false;
    return true;
}

function adaptive_retrieval_state_rows(): array { return adaptive_store('retrieval'); }

function adaptive_retrieval_key(string $classId,string $studentKey,string $topic): string { return $classId.'|'.$studentKey.'|'.$topic; }

function adaptive_retrieval_queue(string $classId,string $studentKey,array $module,array $tourMap,int $limit=3): array
{
    $states=adaptive_retrieval_state_rows();$today=date('Y-m-d');$candidates=[];
    foreach((array)($module['knowledgebase']??[]) as $topic=>$article){
        $topic=(string)$topic;$tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];$check=is_array($tour['check']??null)?$tour['check']:null;if(!$check||empty($check['q'])||!is_array($check['options']??null))continue;
        $progress=learning_kb_progress($classId,$topic);$started=!empty($progress['visual'])||!empty($progress['steps'])||!empty($progress['check'])||!empty($progress['complete']);if(!$started)continue;
        $state=is_array($states[adaptive_retrieval_key($classId,$studentKey,$topic)]??null)?$states[adaptive_retrieval_key($classId,$studentKey,$topic)]:[];$due=(string)($state['due_date']??$today);$priority=0;if($due<=$today)$priority+=100;if(empty($progress['complete']))$priority+=15;$priority+=max(0,20-(int)($state['streak']??0)*3);$priority+=min(40,(int)($state['incorrect_count']??0)*8);
        $candidates[]=['topic'=>$topic,'article'=>$article,'check'=>$check,'state'=>$state,'priority'=>$priority];
    }
    usort($candidates,fn($a,$b)=>(int)$b['priority']<=>(int)$a['priority']);
    return function_exists('ml_interleave_candidates') ? ml_interleave_candidates($candidates,max(1,$limit)) : array_slice($candidates,0,max(1,$limit));
}

function adaptive_retrieval_submit(string $classId,string $studentKey,array $answers,array $module,array $tourMap,array $confidenceMap=[]): array
{
    $states=adaptive_retrieval_state_rows();$changedStates=[];$results=[];$score=0;$total=0;
    foreach($answers as $topic=>$answer){
        $topic=(string)$topic;if(!isset($module['knowledgebase'][$topic]))continue;$tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];$check=is_array($tour['check']??null)?$tour['check']:null;if(!$check)continue;$total++;
        $correct=(int)($check['correct']??-99);$selected=is_numeric($answer)?(int)$answer:-1;$ok=$selected===$correct;if($ok)$score++;
        $key=adaptive_retrieval_key($classId,$studentKey,$topic);$state=is_array($states[$key]??null)?$states[$key]:[];$streak=$ok?((int)($state['streak']??0)+1):0;$incorrect=(int)($state['incorrect_count']??0)+($ok?0:1);$confidence=max(0,min(3,(int)($confidenceMap[$topic]??0)));$intervals=[1,3,7,14,30,60];$days=$ok?$intervals[min($streak-1,count($intervals)-1)]:1;if($ok&&$confidence===1)$days=max(1,(int)ceil($days/2));if(!$ok&&$confidence===3)$days=1;$transferRows=function_exists('ml_store')?ml_store('transfer'):[];$tr=is_array($transferRows[$classId.'|'.$studentKey.'|'.$topic]??null)?$transferRows[$classId.'|'.$studentKey.'|'.$topic]:[];if($ok&&(int)($tr['best']??0)>=100)$days=min(60,$days+3);
        $states[$key]=$changedStates[$key]=['class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'streak'=>$streak,'incorrect_count'=>$incorrect,'last_correct'=>$ok,'last_confidence'=>$confidence,'last_answered_at'=>date(DATE_ATOM),'due_date'=>date('Y-m-d',strtotime('+'.$days.' days'))];if(function_exists('ml_record_learning_event'))ml_record_learning_event($classId,$studentKey,$topic,'retrieval',$ok,['confidence'=>$confidence,'selected'=>$selected,'correct_index'=>$correct]);
        $results[]=['topic'=>$topic,'correct'=>$ok,'why'=>(string)($check['why']??''),'title'=>(string)($module['knowledgebase'][$topic]['title']??$topic)];
    }
    // v58: do úložiště jdou jen změněné stavy (sloučení pod zámkem), pokusy jsou pouze přidávané → JSONL proud.
    if($changedStates)adaptive_store_update('retrieval',static fn(array $all): array => array_replace($all,$changedStates));
    storage_append('adaptive_retrieval_attempts',['id'=>'ret_'.bin2hex(random_bytes(6)),'class_id'=>$classId,'student_key'=>$studentKey,'score'=>$score,'total'=>$total,'results'=>$results,'at'=>date(DATE_ATOM)]);
    return ['score'=>$score,'total'=>$total,'results'=>$results];
}

function adaptive_journal_save(string $classId,string $studentKey,string $date,array $input): void
{
    $id=$classId.'|'.$studentKey.'|'.$date;storage_map_update(adaptive_store_path('journal'),$id,static fn(?array $current): array => ['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'date'=>$date,'understood'=>trim(u_substr((string)($input['understood']??''),0,900)),'unclear'=>trim(u_substr((string)($input['unclear']??''),0,900)),'next'=>trim(u_substr((string)($input['next']??''),0,900)),'updated_at'=>date(DATE_ATOM)]);
}

function adaptive_journal_get(string $classId,string $studentKey,string $date): array
{
    $row=adaptive_store('journal')[$classId.'|'.$studentKey.'|'.$date]??[];return is_array($row)?$row:[];
}

function adaptive_help_event(string $classId,string $studentKey,string $topic,string $mode): void
{
    $allowed=['simple','analogy','steps','example','mistake','try','other','tutor'];if(!in_array($mode,$allowed,true))return;storage_append('adaptive_help_events',['class_id'=>$classId,'student_key'=>$studentKey,'topic'=>$topic,'mode'=>$mode,'at'=>date(DATE_ATOM)]);
}

function adaptive_tutor_local_answer(string $classId,string $topic,string $question,array $module,array $tourMap,string $mode='guide'): string
{
    $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];$tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];$q=mb_strtolower(trim($question),'UTF-8');$summary=trim((string)($article['summary']??$tour['mental']??''));$steps=array_values(array_filter(array_map('strval',(array)($tour['steps']??[]))));$mistakes=array_values(array_filter(array_map('strval',(array)($tour['mistakes']??[]))));
    if(str_contains($q,'odpověď')||str_contains($q,'test')||str_contains($q,'správně')) return 'Hotovou odpověď k hodnocenému checku ti nedám. Můžu ale pomoct rozlišit principy. Napiš, mezi kterými dvěma možnostmi váháš a proč.';
    if($mode==='question') return 'Zkus odpovědět na jednu otázku: co přesně v této situaci pozoruješ a jaký jediný test by nejlépe odlišil dvě možné příčiny?';
    if($mode==='check') return 'Napiš své vysvětlení ve tvaru „Tvrdím…, protože důkaz…“. Zkontroluj, zda důkaz opravdu podporuje tvrzení a zda z něj nevyvozuješ víc, než ukazuje.';
    if($mode==='gap') return 'Projdi svůj postup a hledej největší mezeru: kde jsi přeskočil/a z pozorování rovnou k příčině bez rozlišovacího testu nebo validace?';
    if($mode==='explain') return ($summary!==''?$summary.' ':'').'Teď si to převeď na konkrétní příklad: co se změní, co zůstane stejné a jak to ověříš?';
    if(str_contains($q,'proč')) return ($summary!==''?$summary.' ':'').'Zkus si položit otázku: jaký konkrétní důkaz by ukázal, že tento princip funguje i v tvém příkladu?';
    if(str_contains($q,'jak')&&$steps){return "Rozděl to na malé kroky:\n• ".implode("\n• ",array_slice($steps,0,5))."\nPo prvním kroku se zastav a ověř výsledek.";}
    if(str_contains($q,'nejde')||str_contains($q,'chyba')||str_contains($q,'problém')){return ($mistakes[0]??'Nejdřív odděl symptom od domněnky o příčině.').' Změň jen jednu věc nebo proveď jeden test a napiš, co výsledek dokazuje.';}
    return ($summary!==''?$summary:'Začni tím, co přesně máš poznat nebo vytvořit.').' Zkus mi teď vlastními slovy napsat jednu větu: „Myslím, že princip znamená…“. Podle toho navážeme dalším malým krokem.';
}

function adaptive_tutor_remote_answer(string $classId,string $topic,string $question,array $module,array $tourMap,string $mode='guide'): ?string
{
    $endpoint=educanet_secret('tutor_endpoint');$token=educanet_secret('tutor_token');if($endpoint===''||!function_exists('curl_init'))return null;
    $article=is_array($module['knowledgebase'][$topic]??null)?$module['knowledgebase'][$topic]:[];$tour=is_array($tourMap[$topic]??null)?$tourMap[$topic]:[];
    $context=['class'=>$classId,'topic'=>$topic,'title'=>$article['title']??$topic,'summary'=>$article['summary']??'','steps'=>$tour['steps']??[],'mistakes'=>$tour['mistakes']??[]];
    $system="Jsi EDU Tutor. Režim: {$mode}.  Pracuj pouze s dodaným kontextem EDUCAnet. Vysvětluj česky, stručně, po malých krocích. Nikdy neprozrazuj správnou odpověď k hodnocenému testu, mastery challenge nebo zkoušce. Místo toho pokládej naváděcí otázky a používej konkrétní příklad. Když kontext nestačí, otevřeně řekni, že to z něj nelze určit.";
    $payload=['model'=>educanet_secret('tutor_model','educanet-tutor'),'messages'=>[['role'=>'system','content'=>$system],['role'=>'user','content'=>'KONTEXT: '.json_encode($context,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\nDOTAZ: ".$question]],'temperature'=>0.2,'max_tokens'=>450];
    $ch=curl_init($endpoint);if(!$ch)return null;$headers=['Content-Type: application/json'];if($token!=='')$headers[]='Authorization: Bearer '.$token;curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>$headers,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>12,CURLOPT_CONNECTTIMEOUT=>4]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if(!is_string($raw)||$code<200||$code>=300)return null;$data=json_decode($raw,true);$text=$data['choices'][0]['message']['content']??$data['output_text']??null;return is_string($text)&&trim($text)!==''?trim($text):null;
}

function adaptive_tutor_answer(string $classId,string $studentKey,string $topic,string $question,array $module,array $tourMap,string $mode='guide'): string
{
    $question=trim(u_substr($question,0,900));if($question==='')return 'Napiš, co přesně ti není jasné. Stačí jedna věta.';adaptive_help_event($classId,$studentKey,$topic,'tutor');$mode=in_array($mode,['guide','explain','question','check','gap'],true)?$mode:'guide';$remote=adaptive_tutor_remote_answer($classId,$topic,$question,$module,$tourMap,$mode);return $remote??adaptive_tutor_local_answer($classId,$topic,$question,$module,$tourMap,$mode);
}

function adaptive_intervention_feed(string $classId): array
{
    $students=project_students_for_class($classId);$feed=[];$retrieval=adaptive_retrieval_state_rows();$absences=adaptive_absence_rows();$recovery=adaptive_store('recovery');$journal=adaptive_store('journal');$help=adaptive_store('help_events');$now=time();
    foreach($students as $studentKey=>$student){$signals=[];
        $wrongTopics=[];foreach($retrieval as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(string)($r['student_key']??'')!==$studentKey)continue;if((int)($r['incorrect_count']??0)>=2&&!($r['last_correct']??false))$wrongTopics[]=(string)($r['topic']??'');}
        if($wrongTopics)$signals[]=['severity'=>'medium','label'=>'Opakování','text'=>'Opakované chyby v '.count(array_unique($wrongTopics)).' tématech.'];
        $incompleteRecovery=0;foreach($absences as $a){if(!is_array($a)||(string)($a['class_id']??'')!==$classId||(string)($a['student_key']??'')!==$studentKey||(string)($a['status']??'')==='complete')continue;$rid=$classId.'|'.$studentKey.'|'.(string)($a['date']??'');$rr=is_array($recovery[$rid]??null)?$recovery[$rid]:[];if(!$rr||!adaptive_recovery_raw_complete($rr))$incompleteRecovery++;}
        if($incompleteRecovery>0)$signals[]=['severity'=>'medium','label'=>'Absence','text'=>$incompleteRecovery.' recovery plánů není dokončeno.'];
        $recentUnclear=[];foreach($journal as $j){if(!is_array($j)||(string)($j['class_id']??'')!==$classId||(string)($j['student_key']??'')!==$studentKey||trim((string)($j['unclear']??''))==='')continue;$ts=strtotime((string)($j['updated_at']??$j['date']??''));if($ts&&$ts>=$now-14*86400)$recentUnclear[]=$j;}
        if(count($recentUnclear)>=2)$signals[]=['severity'=>'info','label'=>'Reflexe','text'=>'Ve více reflexích zůstává nejasnost.'];
        $recentHelp=[];foreach($help as $h){if(!is_array($h)||(string)($h['class_id']??'')!==$classId||(string)($h['student_key']??'')!==$studentKey)continue;$ts=strtotime((string)($h['at']??''));if($ts&&$ts>=$now-7*86400)$recentHelp[]=$h;}
        if(count($recentHelp)>=5&&$wrongTopics)$signals[]=['severity'=>'high','label'=>'Pomoc','text'=>'Student zkoušel více vysvětlení a stále chybuje. Doporučen krátký osobní zásah.'];
        if($signals)$feed[]=['student_key'=>$studentKey,'student_label'=>(string)$student['label'],'signals'=>$signals,'severity'=>in_array('high',array_column($signals,'severity'),true)?'high':(in_array('medium',array_column($signals,'severity'),true)?'medium':'info')];
    }
    usort($feed,static function($a,$b){$w=['high'=>3,'medium'=>2,'info'=>1];return ($w[$b['severity']]??0)<=>($w[$a['severity']]??0);});return $feed;
}

function adaptive_help_stats(string $classId): array
{
    $rows=adaptive_store('help_events');$modes=[];$count=0;foreach($rows as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId)continue;$count++;$mode=(string)($r['mode']??'other');$modes[$mode]=($modes[$mode]??0)+1;}arsort($modes);return ['count'=>$count,'modes'=>$modes];
}
