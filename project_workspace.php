<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET Project Workspace v2
 * ------------------------------
 * A single project/team domain layer shared by student and teacher UI.
 * JSON storage is intentionally kept compatible with the current application,
 * while every record has stable ids/audit data so it can be migrated to SQL later.
 */

function project_workspace_storage(string $name): string { return STORAGE_DIR . '/project_workspace_' . $name . '.json.php'; }
function project_workspace_rows(string $name): array { $r=load_php_json(project_workspace_storage($name)); return is_array($r)?$r:[]; }
/** v58 (F2): read-modify-write jedné kolekce pod jedním zámkem – $fn(array $rows): array. */
function project_workspace_update(string $name,callable $fn): array { return storage_update(project_workspace_storage($name),static fn(array $rows): array => array_values($fn($rows))); }
/** Přidá záznam do kolekce pod zámkem ($cap > 0 = jen posledních $cap záznamů). */
function project_workspace_push(string $name,array $row,int $cap=0): void { storage_list_push(project_workspace_storage($name),$row,$cap); }

function project_workspace_audit(string $classId,string $projectId,string $groupId,string $action,string $actorKey,array $meta=[]): void
{
    $actorLabel=$actorKey==='system'?'System':(teacher_export_authenticated()?teacher_display_name():project_workspace_student_label($classId,$actorKey));$auditRow=['id'=>'pwa_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'action'=>$action,'actor_key'=>$actorKey,'actor'=>$actorLabel,'meta'=>$meta,'at'=>date(DATE_ATOM)];
    project_workspace_push('audit',$auditRow,6000);
}

function project_workspace_student_label(string $classId,string $studentKey): string
{
    $students=project_students_for_class($classId);
    return (string)($students[$studentKey]['label']??tr('Student'));
}

function project_role_definitions(): array
{
    return [
        'leader'=>[
            'name'=>'Leader','icon'=>'◎','short'=>'Koordinace','description'=>'Drží projekt pohromadě, pomáhá týmu rozhodovat a řeší blokátory. Není nadřízený ostatním.',
            'responsibilities'=>['Ujasnit cíl a priority projektu','Pomoci rozdělit práci a hlídat závislosti','Včas zachytit a řešit blokátory','Udržovat důležitá týmová rozhodnutí','Před odevzdáním ověřit připravenost týmu'],
            'rubric'=>[
                'planning'=>['title'=>'Plánování','description'=>'Pomohl vytvořit realistický plán a jasné priority.'],
                'coordination'=>['title'=>'Koordinace','description'=>'Tým věděl, kdo na čem pracuje a co na co čeká.'],
                'communication'=>['title'=>'Komunikace','description'=>'Důležité informace a změny sdílel včas a srozumitelně.'],
                'blockers'=>['title'=>'Práce s blokátory','description'=>'Problémy nenechával ležet a pomohl najít další krok.'],
                'leadership'=>['title'=>'Podpora týmu','description'=>'Podporoval spolupráci a odpovědnost místo mikromanagementu.'],
            ],
        ],
        'designer'=>[
            'name'=>'Designer','icon'=>'◇','short'=>'Vizuální řešení','description'=>'Odpovídá za srozumitelný, konzistentní a použitelný vizuální návrh.',
            'responsibilities'=>['Navrhnout vizuální koncept a hierarchii','Udržet typografii, barvy a spacing konzistentní','Připravit potřebné komponenty a stavy','Ověřit responzivitu a základní přístupnost','Předat finální podklady týmu'],
            'rubric'=>[
                'concept'=>['title'=>'Vizuální koncept','description'=>'Řešení má jasný směr a odpovídá zadání.'],
                'consistency'=>['title'=>'Konzistence','description'=>'Komponenty, barvy, spacing a vizuální jazyk tvoří systém.'],
                'typography'=>['title'=>'Typografie a hierarchie','description'=>'Obsah je čitelný a má jasnou vizuální prioritu.'],
                'usability'=>['title'=>'Použitelnost','description'=>'Návrh respektuje kontext, stavy a potřeby uživatele.'],
                'handoff'=>['title'=>'Předání','description'=>'Výstupy jsou připravené tak, aby s nimi tým dokázal pokračovat.'],
            ],
        ],
        'researcher'=>[
            'name'=>'Researcher','icon'=>'⌕','short'=>'Výzkum','description'=>'Získává, ověřuje a shrnuje informace, které pomáhají týmu dělat lepší rozhodnutí.',
            'responsibilities'=>['Ujasnit otázky, které je potřeba zjistit','Pracovat s relevantními a důvěryhodnými zdroji','Ověřovat důležitá tvrzení','Porovnat možné varianty a jejich dopady','Předat závěry týmu ve srozumitelné podobě'],
            'rubric'=>[
                'sources'=>['title'=>'Kvalita zdrojů','description'=>'Použil relevantní a přiměřeně důvěryhodné zdroje.'],
                'relevance'=>['title'=>'Relevance','description'=>'Výzkum odpovídal skutečným otázkám projektu.'],
                'verification'=>['title'=>'Ověřování','description'=>'Důležitá tvrzení a předpoklady ověřoval.'],
                'synthesis'=>['title'=>'Syntéza','description'=>'Dokázal z informací vytáhnout užitečné závěry.'],
                'impact'=>['title'=>'Dopad na projekt','description'=>'Výzkum reálně pomohl rozhodování nebo výsledku.'],
            ],
        ],
        'developer'=>[
            'name'=>'Developer','icon'=>'⌘','short'=>'Realizace','description'=>'Staví a zprovozňuje technické řešení a reaguje na problémy objevené při integraci a QA.',
            'responsibilities'=>['Implementovat nebo nakonfigurovat technické řešení','Rozdělit technickou práci do ověřitelných kroků','Integrovat části projektu','Diagnostikovat a opravovat technické problémy','Zdokumentovat klíčová technická rozhodnutí'],
            'rubric'=>[
                'quality'=>['title'=>'Technická kvalita','description'=>'Řešení je funkční, správné a odpovídá zadání.'],
                'reliability'=>['title'=>'Spolehlivost','description'=>'Práci dokončoval v kvalitě, na které mohl tým stavět.'],
                'problem_solving'=>['title'=>'Řešení problémů','description'=>'Při chybách pracoval systematicky s evidencí.'],
                'integration'=>['title'=>'Integrace','description'=>'Řešení dobře navázal na ostatní části projektu.'],
                'documentation'=>['title'=>'Dokumentace','description'=>'Klíčová rozhodnutí a postup jsou reprodukovatelné.'],
            ],
        ],
        'presenter'=>[
            'name'=>'Presenter','icon'=>'▶','short'=>'Komunikace','description'=>'Pomáhá týmu srozumitelně vysvětlit problém, proces, rozhodnutí i výsledný produkt.',
            'responsibilities'=>['Navrhnout strukturu finální prezentace nebo dema','Vybrat důležité důkazy a rozhodnutí','Připravit tým na otázky','Koordinovat, kdo co představí','Ohlídat srozumitelnost finální obhajoby'],
            'rubric'=>[
                'structure'=>['title'=>'Struktura','description'=>'Prezentace má jasný začátek, logiku a závěr.'],
                'clarity'=>['title'=>'Srozumitelnost','description'=>'Publikum rozumí problému, řešení i výsledku.'],
                'accuracy'=>['title'=>'Přesnost','description'=>'Tvrzení odpovídají skutečnému průběhu a evidenci.'],
                'storytelling'=>['title'=>'Příběh projektu','description'=>'Dokáže propojit problém, rozhodnutí, iterace a výsledek.'],
                'qa'=>['title'=>'Otázky a obhajoba','description'=>'Tým je připravený vysvětlit rozhodnutí a reagovat na otázky.'],
            ],
        ],
        'qa'=>[
            'name'=>'QA','icon'=>'✓','short'=>'Kvalita','description'=>'Ověřuje, že výsledek opravdu splňuje zadání, funguje v důležitých scénářích a chyby se skutečně opravily.',
            'responsibilities'=>['Převést požadavky projektu na kontrolní body','Testovat průběžně, ne až na konci','Popsat nalezené problémy reprodukovatelně','Ověřit opravy a hlídat regresi','Provést finální quality check před odevzdáním'],
            'rubric'=>[
                'coverage'=>['title'=>'Pokrytí','description'=>'Kontrola pokrývá důležité scénáře a požadavky.'],
                'issues'=>['title'=>'Kvalita nálezů','description'=>'Nálezy jsou konkrétní, reprodukovatelné a prioritizované.'],
                'detail'=>['title'=>'Pozornost k detailu','description'=>'Zachytil problémy, které mají dopad na výsledek nebo uživatele.'],
                'verification'=>['title'=>'Ověření oprav','description'=>'Nejen nahlásil chybu, ale ověřil její skutečné vyřešení.'],
                'readiness'=>['title'=>'Finální připravenost','description'=>'Před odevzdáním dokázal říct, co je ověřené a co zůstává rizikem.'],
            ],
        ],
    ];
}

function project_role_definition(string $slug): ?array { $all=project_role_definitions(); return is_array($all[$slug]??null)?array_replace(['slug'=>$slug],$all[$slug]):null; }
function project_role_playbook(string $slug): array
{
    return [
        'leader'=>['before'=>'Ujasni s týmem cíl, priority a první konkrétní odpovědnosti.','during'=>'Sleduj závislosti a blokátory; pomáhej najít další krok místo přebírání práce.','done'=>'Ověř, že každá důležitá oblast má vlastníka a tým je připravený na odevzdání.'],
        'designer'=>['before'=>'Ujasni vizuální směr, hierarchii a omezení zadání.','during'=>'Navrhuj systémově, ukazuj stavy a průběžně předávej použitelné podklady.','done'=>'Projdi konzistenci, responsivitu, typografii a handoff.'],
        'researcher'=>['before'=>'Sepiš otázky, které musí tým rozhodnout na základě evidence.','during'=>'Ověřuj zdroje, porovnávej varianty a průběžně sdílej závěry.','done'=>'Odděl fakta, předpoklady a doporučení a uveď zdroje.'],
        'developer'=>['before'=>'Rozděl řešení na malé ověřitelné kroky a identifikuj technická rizika.','during'=>'Implementuj, integruj a při chybě diagnostikuj systematicky místo náhodných pokusů.','done'=>'Předej funkční řešení, klíčová rozhodnutí a reprodukovatelnou dokumentaci QA.'],
        'presenter'=>['before'=>'Ujasni, co musí publikum po prezentaci chápat.','during'=>'Sbírej důkazy, rozhodnutí a změny, které tvoří příběh projektu.','done'=>'Připrav strukturu, demo, role řečníků a otázky k obhajobě.'],
        'qa'=>['before'=>'Převeď zadání a Definition of Done na konkrétní kontrolní scénáře.','during'=>'Testuj průběžně, popisuj nálezy reprodukovatelně a rozlišuj závažnost.','done'=>'Ověř opravy, kritické scénáře a finální připravenost bez neověřených critical issues.'],
    ][$slug]??[];
}

function project_role_label(string $slug): string { $r=project_role_definition($slug); return (string)($r['name']??ucfirst($slug)); }

function project_role_requirements(string $classId,string $projectId): array
{
    $project=project_find($classId,$projectId); if(!$project)return [];
    $creative=in_array($classId,['class_1a','class_2a'],true);
    $req=[];
    foreach(array_keys(project_role_definitions()) as $role)$req[$role]='optional';
    foreach(['leader','researcher','presenter','qa'] as $role)$req[$role]='required';
    if($creative){$req['designer']='required';$req['developer']=str_contains($projectId,'landing')||str_contains($projectId,'system')||str_contains($projectId,'usability')?'recommended':'optional';}
    else{$req['developer']='required';$req['designer']='optional';}
    return $req;
}

function project_workspace_group_for_student(string $classId,string $projectId,string $studentKey): ?array
{
    return project_group_for_student($classId,$projectId,$studentKey);
}

function project_workspace_group_context(string $classId,string $projectId,string $studentKey): array
{
    $project=project_find($classId,$projectId); if(!$project||(string)($project['type']??'')!=='group')throw new RuntimeException(tr('Skupinový projekt nebyl nalezen.'));
    $group=project_workspace_group_for_student($classId,$projectId,$studentKey); if(!$group)throw new RuntimeException(tr('Nejdřív musíš být členem uzamčeného týmu pro tento projekt.'));
    return ['project'=>$project,'group'=>$group];
}

function project_workspace_is_member(array $group,string $studentKey): bool { return in_array($studentKey,array_map('strval',(array)($group['member_keys']??[])),true); }

function project_workspace_group_owner_key(string $groupId): string
{
    foreach(team_lobbies() as $l){if(is_array($l)&&(string)($l['group_id']??'')===$groupId&&(string)($l['status']??'')==='formed')return (string)($l['owner_key']??'');}
    $g=project_group_find($groupId); return (string)($g['member_keys'][0]??'');
}

function project_role_rows(): array { return project_workspace_rows('roles'); }
function project_roles_for_group(string $groupId,bool $activeOnly=true): array
{
    $rows=array_values(array_filter(project_role_rows(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId&&(!$activeOnly||empty($r['ended_at']))));
    usort($rows,static function($a,$b): int {
        $ta=(string)($a['role_type']??'secondary');$tb=(string)($b['role_type']??'secondary');
        if($ta!==$tb)return $ta==='primary'?-1:1;
        return strcmp((string)($a['started_at']??''),(string)($b['started_at']??''));
    });
    return $rows;
}
function project_roles_for_student(string $groupId,string $studentKey,bool $activeOnly=true): array
{
    return array_values(array_filter(project_roles_for_group($groupId,$activeOnly),static fn($r):bool=>(string)($r['student_key']??'')===$studentKey));
}
function project_student_has_role(string $groupId,string $studentKey,string $role): bool
{
    foreach(project_roles_for_student($groupId,$studentKey) as $r)if((string)($r['role']??'')===$role&&(string)($r['status']??'confirmed')==='confirmed')return true;
    return false;
}

function project_role_assign(string $classId,string $projectId,string $groupId,string $studentKey,string $role,string $roleType='primary',?string $actorKey=null): array
{
    $defs=project_role_definitions(); if(!isset($defs[$role]))throw new RuntimeException(tr('Neplatná týmová role.'));
    $group=project_group_find($groupId); if(!$group||(string)$group['class_id']!==$classId||(string)$group['project_id']!==$projectId)throw new RuntimeException(tr('Tým nebyl nalezen.'));
    if(!project_workspace_is_member($group,$studentKey))throw new RuntimeException(tr('Student není členem tohoto týmu.'));
    $actorKey=$actorKey??$studentKey;
    if(!(teacher_export_authenticated() && (!function_exists('teacher59_can_class') || teacher59_can_class($classId))) && !hash_equals($actorKey,$studentKey))throw new RuntimeException(tr('Můžeš nastavovat pouze své vlastní role.'));
    $roleType=in_array($roleType,['primary','secondary'],true)?$roleType:'secondary';
    $now=date(DATE_ATOM);
    $active=project_roles_for_student($groupId,$studentKey);
    foreach($active as $r)if((string)($r['role']??'')===$role)return $r;
    if(count($active)>=3)throw new RuntimeException(tr('Jeden student může mít maximálně tři aktivní role.'));
    if($roleType==='primary'){
        foreach($active as $r)if((string)($r['role_type']??'')==='primary')throw new RuntimeException(tr('Primární roli už máš. Další roli nastav jako podpůrnou.'));
        foreach(project_roles_for_group($groupId) as $r)if((string)($r['role']??'')===$role&&(string)($r['role_type']??'')==='primary'&&(string)($r['status']??'confirmed')==='confirmed')throw new RuntimeException(tr('Tato primární role už má vlastníka. Můžeš ji převzít jako podpůrnou.'));
    }
    $row=['id'=>'pr_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'student_key'=>$studentKey,'role'=>$role,'role_type'=>$roleType,'status'=>'confirmed','assigned_by'=>teacher_export_authenticated()?teacher_display_name():$studentKey,'started_at'=>$now,'ended_at'=>null,'created_at'=>$now,'updated_at'=>$now];
    project_workspace_update('roles',static function(array $rows) use($row,$groupId,$studentKey,$role): array {foreach($rows as $r)if(is_array($r)&&empty($r['ended_at'])&&(string)($r['group_id']??'')===$groupId&&(string)($r['student_key']??'')===$studentKey&&(string)($r['role']??'')===$role)return $rows;$rows[]=$row;return $rows;});
    project_workspace_audit($classId,$projectId,$groupId,'role.assigned',$actorKey,['student_key'=>$studentKey,'role'=>$role,'role_type'=>$roleType]);
    return $row;
}

function project_role_remove(string $classId,string $projectId,string $groupId,string $studentKey,string $role,string $actorKey): void
{
    $changed=false;project_workspace_update('roles',static function(array $rows) use($classId,$groupId,$studentKey,$role,$actorKey,&$changed): array {
    foreach($rows as &$r){if(!is_array($r)||!empty($r['ended_at'])||(string)($r['group_id']??'')!==$groupId||(string)($r['student_key']??'')!==$studentKey||(string)($r['role']??'')!==$role)continue;
        if(!(teacher_export_authenticated() && (!function_exists('teacher59_can_class') || teacher59_can_class($classId)))&&!hash_equals($actorKey,$studentKey))throw new RuntimeException(tr('Můžeš odebrat pouze svou vlastní roli.'));
        $r['ended_at']=date(DATE_ATOM);$r['updated_at']=date(DATE_ATOM);$changed=true;
    }unset($r);
    if(!$changed)throw new RuntimeException(tr('Aktivní role nebyla nalezena.'));
    return $rows;});project_workspace_audit($classId,$projectId,$groupId,'role.removed',$actorKey,['student_key'=>$studentKey,'role'=>$role]);
}

function project_workspace_sync_group_members(string $groupId,array $memberKeys,string $actor='system'): void
{
    $group=project_group_find($groupId);if(!$group)return;
    $members=array_values(array_unique(array_filter(array_map('strval',$memberKeys))));$memberSet=array_fill_keys($members,true);$now=date(DATE_ATOM);$changedRoles=false;
    project_workspace_update('roles',static function(array $roles) use($groupId,$memberSet,$now,&$changedRoles): array {
    foreach($roles as &$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId||!empty($r['ended_at']))continue;if(!isset($memberSet[(string)($r['student_key']??'')])){$r['ended_at']=$now;$r['updated_at']=$now;$changedRoles=true;}}unset($r);
    return $roles;});
    $changedTasks=false;$fallback=(string)($members[0]??'');project_workspace_update('tasks',static function(array $tasks) use($groupId,$memberSet,$now,$fallback,&$changedTasks): array {
    foreach($tasks as &$t){if(!is_array($t)||(string)($t['group_id']??'')!==$groupId)continue;$taskChanged=false;
        $owner=(string)($t['owner_key']??'');if($owner!==''&&!isset($memberSet[$owner])&&$fallback!==''){$t['owner_key']=$fallback;$changedTasks=true;$taskChanged=true;}
        $support=array_values(array_filter(array_map('strval',(array)($t['support_keys']??[])),static fn($k)=>isset($memberSet[$k])));if($support!==(array)($t['support_keys']??[])){$t['support_keys']=$support;$changedTasks=true;$taskChanged=true;}
        if($taskChanged)$t['updated_at']=$now;
    }unset($t);
    return $tasks;});
    if($changedRoles||$changedTasks)project_workspace_audit((string)$group['class_id'],(string)$group['project_id'],$groupId,'group.members.synced',$actor,['member_keys'=>$members,'roles_ended'=>$changedRoles,'tasks_reassigned'=>$changedTasks]);
}

function project_role_skill_candidates(string $classId,string $projectId,string $role): array
{
    if(!function_exists('skill_project_evidence_map'))return [];
    $mapped=array_values(array_filter(array_map('strval',(array)(skill_project_evidence_map($classId)[$projectId]??[]))));
    $pref=[
        'leader'=>['design-critique','user-needs','threat-modeling','incident-response','network-troubleshooting','logs-troubleshooting','art-direction'],
        'designer'=>['design','typography','ui-ux'],
        'researcher'=>['user-needs','usability-testing','threat-modeling','logs-monitoring','wireshark','design-critique','network-troubleshooting'],
        'developer'=>['networking','linux','security','components-ui','responsive-ui','prototyping','forms-states'],
        'presenter'=>['design-critique','visual-hierarchy','type-hierarchy','art-direction','user-needs','incident-response'],
        'qa'=>['usability-testing','accessibility-ui','type-accessibility','network-troubleshooting','logs-troubleshooting','logs-monitoring','incident-response','web-security'],
    ][$role]??[];
    $out=[];
    foreach($mapped as $slug){$s=skill_find($slug);if(!$s)continue;$branch=(string)($s['branch']??'');$match=false;foreach($pref as $p){if($p===$branch||$p===$slug||str_contains($slug,$p)){$match=true;break;}}if($match)$out[]=$slug;}
    if(!$out && in_array($role,['leader','researcher','presenter'],true))$out=array_slice($mapped,0,2);
    return array_values(array_unique(array_slice($out,0,4)));
}

function project_role_fit(string $classId,string $projectId,string $studentKey,string $role): array
{
    $label=project_workspace_student_label($classId,$studentKey);$skillKey=function_exists('skill_student_key_for_label')?skill_student_key_for_label($classId,$label):'';$skills=project_role_skill_candidates($classId,$projectId,$role);$vals=[];
    foreach($skills as $slug){$p=skill_progress($classId,$slug,$skillKey);$vals[]=(float)($p['mastery_percent']??0);}
    $mastery=$vals?array_sum($vals)/count($vals):50.0;
    $stats=project_role_history_stats($classId,$studentKey);$roleCount=(int)($stats['role_counts'][$role]??0);$recent=array_slice((array)($stats['recent_roles']??[]),0,4);$sameRecent=count(array_filter($recent,static fn($r)=>$r===$role));
    $fit=(int)round(max(20,min(98,$mastery*.75+25)));
    return ['fit'=>$fit,'mastery'=>round($mastery,1),'skills'=>$skills,'experience'=>$roleCount,'growth'=>$roleCount===0||$sameRecent>=3,'reason'=>$roleCount===0?tr('Tuto roli sis zatím nevyzkoušel/a.'):($sameRecent>=3?tr('Poslední projekty byly podobné – jiná role může rozšířit tvoje schopnosti.'):tr('Navazuje na tvoje ověřené dovednosti.'))];
}

function project_tasks(): array { return project_workspace_rows('tasks'); }
function project_tasks_for_group(string $groupId): array { $rows=array_values(array_filter(project_tasks(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId));usort($rows,static fn($a,$b)=>strcmp((string)($a['created_at']??''),(string)($b['created_at']??'')));return $rows; }
function project_task_find(string $taskId): ?array { foreach(project_tasks() as $r)if(is_array($r)&&(string)($r['id']??'')===$taskId)return $r;return null; }
function project_task_statuses(): array { return ['todo'=>tr('K udělání'),'in_progress'=>tr('Pracuji'),'review'=>'Review','qa'=>'QA','done'=>tr('Hotovo'),'blocked'=>tr('Blokováno')]; }
function project_workspace_task_status_label(string $status): string { return project_task_statuses()[$status]??$status; }
function project_workspace_severity_label(string $severity): string { return ['low'=>tr('Malá'),'medium'=>tr('Střední'),'critical'=>tr('Kritická')][$severity]??$severity; }
function project_workspace_qa_status_label(string $status): string { return ['open'=>tr('Otevřeno'),'needs_fix'=>'Needs fix','fixed'=>tr('Opraveno'),'verified'=>tr('Ověřeno')][$status]??$status; }

function project_clean_lines($raw,int $max=8,int $length=180): array
{
    if(is_string($raw))$raw=preg_split('/\r?\n/u',$raw)?:[];if(!is_array($raw))return [];$out=[];
    foreach($raw as $v){$v=trim(u_substr((string)$v,0,$length));if($v!=='')$out[]=$v;if(count($out)>=$max)break;}return $out;
}

function project_task_create(string $classId,string $projectId,string $groupId,string $actorKey,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$actorKey);$group=$ctx['group'];if((string)$group['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));
    $title=u_substr(trim((string)($input['title']??'')),0,120);if($title==='')throw new RuntimeException(tr('Doplň název úkolu.'));
    $owner=(string)($input['owner_key']??$actorKey);if(!project_workspace_is_member($group,$owner))$owner=$actorKey;
    $support=array_values(array_unique(array_filter(array_map('strval',(array)($input['support_keys']??[])),static fn($k)=>$k!==$owner&&$k!=='')));$support=array_values(array_filter($support,static fn($k)=>project_workspace_is_member($group,$k)));
    $role=(string)($input['role']??'');if(!isset(project_role_definitions()[$role]))$role='';
    $existing=project_tasks_for_group($groupId);$validIds=array_fill_keys(array_map(static fn($r)=>(string)$r['id'],$existing),true);$deps=[];foreach((array)($input['depends_on']??[]) as $d){$d=(string)$d;if(isset($validIds[$d]))$deps[]=$d;if(count($deps)>=5)break;}
    $due=trim((string)($input['due_at']??''));if($due!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$due))$due='';$now=date(DATE_ATOM);
    $row=['id'=>'pt_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'title'=>$title,'description'=>u_substr(trim((string)($input['description']??'')),0,1200),'owner_key'=>$owner,'support_keys'=>$support,'role'=>$role,'status'=>'todo','complexity'=>max(1,min(5,(int)($input['complexity']??2))),'definition_of_done'=>project_clean_lines($input['definition_of_done']??[],8,180),'depends_on'=>array_values(array_unique($deps)),'due_at'=>$due!==''?$due:null,'blocked_category'=>null,'blocked_reason'=>'','blocked_at'=>null,'completed_at'=>null,'created_by'=>$actorKey,'created_at'=>$now,'updated_at'=>$now];
    project_workspace_push('tasks',$row);project_workspace_audit($classId,$projectId,$groupId,'task.created',$actorKey,['task_id'=>$row['id'],'owner_key'=>$owner,'role'=>$role]);return $row;
}

function project_task_dependencies_complete(array $task): bool
{
    foreach((array)($task['depends_on']??[]) as $id){$d=project_task_find((string)$id);if($d&&(string)($d['status']??'')!=='done')return false;}return true;
}

function project_task_dependency_creates_cycle(string $taskId,array $newDependencies,string $groupId): bool
{
    $graph=[];foreach(project_tasks_for_group($groupId) as $t){$id=(string)($t['id']??'');if($id==='')continue;$graph[$id]=$id===$taskId?array_values(array_map('strval',$newDependencies)):array_values(array_map('strval',(array)($t['depends_on']??[])));}
    if(!isset($graph[$taskId]))$graph[$taskId]=array_values(array_map('strval',$newDependencies));
    $visiting=[];$visited=[];$walk=function(string $id)use(&$walk,&$graph,&$visiting,&$visited): bool{if(isset($visiting[$id]))return true;if(isset($visited[$id]))return false;$visiting[$id]=true;foreach((array)($graph[$id]??[]) as $dep)if(isset($graph[$dep])&&$walk((string)$dep))return true;unset($visiting[$id]);$visited[$id]=true;return false;};
    return $walk($taskId);
}

function project_task_update(string $classId,string $projectId,string $groupId,string $actorKey,string $taskId,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$actorKey);$group=$ctx['group'];if((string)$group['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));$found=null;project_workspace_update('tasks',static function(array $rows) use($taskId,$groupId,$input,$group,&$found): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['id']??'')!==$taskId||(string)($r['group_id']??'')!==$groupId)continue;
        $title=u_substr(trim((string)($input['title']??$r['title'])),0,120);if($title==='')throw new RuntimeException(tr('Název úkolu nesmí být prázdný.'));$owner=(string)($input['owner_key']??$r['owner_key']);if(!project_workspace_is_member($group,$owner))$owner=(string)$r['owner_key'];
        $role=(string)($input['role']??$r['role']);if(!isset(project_role_definitions()[$role]))$role='';$supportInput=array_key_exists('support_present',$input)?(array)($input['support_keys']??[]):(array)($input['support_keys']??$r['support_keys']??[]);$support=array_values(array_unique(array_map('strval',$supportInput)));$support=array_values(array_filter($support,static fn($k)=>$k!==$owner&&project_workspace_is_member($group,$k)));
        $due=trim((string)($input['due_at']??($r['due_at']??'')));if($due!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$due))$due='';
        $allGroupTasks=project_tasks_for_group($groupId);$validIds=[];foreach($allGroupTasks as $candidate){$cid=(string)($candidate['id']??'');if($cid!==''&&$cid!==$taskId)$validIds[$cid]=true;}$deps=[];
        $depInput=array_key_exists('dependencies_present',$input)?(array)($input['depends_on']??[]):(array_key_exists('depends_on',$input)?(array)$input['depends_on']:(array)($r['depends_on']??[]));foreach($depInput as $d){$d=(string)$d;if(isset($validIds[$d]))$deps[]=$d;if(count($deps)>=5)break;}if(project_task_dependency_creates_cycle($taskId,$deps,$groupId))throw new RuntimeException(tr('Závislosti úkolů by vytvořily cyklus. Jeden úkol nemůže nepřímo čekat sám na sebe.'));
        $r['title']=$title;$r['description']=u_substr(trim((string)($input['description']??$r['description'])),0,1200);$r['owner_key']=$owner;$r['support_keys']=$support;$r['role']=$role;$r['complexity']=max(1,min(5,(int)($input['complexity']??$r['complexity']??2)));$r['definition_of_done']=project_clean_lines($input['definition_of_done']??($r['definition_of_done']??[]),8,180);$r['depends_on']=array_values(array_unique($deps));$r['due_at']=$due!==''?$due:null;$r['updated_at']=date(DATE_ATOM);$rows[$i]=$r;$found=$r;break;
    }
    if(!$found)throw new RuntimeException(tr('Úkol nebyl nalezen.'));return $rows;});project_workspace_audit($classId,$projectId,$groupId,'task.updated',$actorKey,['task_id'=>$taskId]);return $found;
}

function project_task_set_status(string $classId,string $projectId,string $groupId,string $actorKey,string $taskId,string $status,string $blockedCategory='',string $blockedReason=''): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$actorKey);if((string)$ctx['group']['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));$statuses=project_task_statuses();if(!isset($statuses[$status]))throw new RuntimeException(tr('Neplatný stav úkolu.'));$found=null;project_workspace_update('tasks',static function(array $rows) use($taskId,$groupId,$status,$blockedCategory,$blockedReason,&$found): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['id']??'')!==$taskId||(string)($r['group_id']??'')!==$groupId)continue;
        if($status==='done'){
            foreach(project_qa_for_group($groupId) as $issue)if((string)($issue['task_id']??'')===$taskId&&(string)($issue['severity']??'')==='critical'&&(string)($issue['status']??'')!=='verified')throw new RuntimeException(tr('Úkol má neověřený kritický QA problém. Nejdřív jej opravte a ověřte.'));
        }
        if(in_array($status,['review','qa','done'],true)&&!project_task_dependencies_complete($r))throw new RuntimeException(tr('Nejdřív dokončete závislé úkoly.'));
        $r['status']=$status;$r['updated_at']=date(DATE_ATOM);
        if($status==='blocked'){$r['blocked_category']=in_array($blockedCategory,['team','information','technical','teacher','other'],true)?$blockedCategory:'other';$r['blocked_reason']=u_substr(trim($blockedReason),0,600);$r['blocked_at']=date(DATE_ATOM);}else{$r['blocked_category']=null;$r['blocked_reason']='';$r['blocked_at']=null;}
        $r['completed_at']=$status==='done'?date(DATE_ATOM):null;$rows[$i]=$r;$found=$r;break;
    }
    if(!$found)throw new RuntimeException(tr('Úkol nebyl nalezen.'));return $rows;});project_workspace_audit($classId,$projectId,$groupId,'task.status',$actorKey,['task_id'=>$taskId,'status'=>$status,'blocked_reason'=>$found['blocked_reason']]);return $found;
}

function project_tasks_for_student(string $groupId,string $studentKey): array
{
    return array_values(array_filter(project_tasks_for_group($groupId),static fn($t):bool=>(string)($t['owner_key']??'')===$studentKey||in_array($studentKey,(array)($t['support_keys']??[]),true)));
}

function project_next_action(string $groupId,string $studentKey): ?array
{
    $tasks=project_tasks_for_student($groupId,$studentKey);$rank=['blocked'=>0,'in_progress'=>1,'review'=>2,'qa'=>2,'todo'=>3,'done'=>9];
    $tasks=array_values(array_filter($tasks,static fn($t)=>(string)($t['status']??'todo')!=='done'));
    usort($tasks,static function($a,$b)use($rank){$ra=$rank[(string)($a['status']??'todo')]??8;$rb=$rank[(string)($b['status']??'todo')]??8;if($ra!==$rb)return $ra<=>$rb;$da=(string)($a['due_at']??'9999-12-31');$db=(string)($b['due_at']??'9999-12-31');return strcmp($da,$db);});
    return $tasks[0]??null;
}

function project_decisions(): array { return project_workspace_rows('decisions'); }
function project_decisions_for_group(string $groupId): array { $r=array_values(array_filter(project_decisions(),static fn($x):bool=>is_array($x)&&(string)($x['group_id']??'')===$groupId));usort($r,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));return $r; }
function project_decision_create(string $classId,string $projectId,string $groupId,string $actorKey,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$actorKey);if((string)$ctx['group']['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));$decision=u_substr(trim((string)($input['decision']??'')),0,500);$reason=u_substr(trim((string)($input['reason']??'')),0,1000);if($decision===''||$reason==='')throw new RuntimeException(tr('Doplňte rozhodnutí i krátké proč.'));$row=['id'=>'pd_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'decision'=>$decision,'reason'=>$reason,'created_by'=>$actorKey,'created_at'=>date(DATE_ATOM)];project_workspace_push('decisions',$row);project_workspace_audit($classId,$projectId,$groupId,'decision.created',$actorKey,['decision_id'=>$row['id']]);return $row;
}

function project_checkins(): array { return project_workspace_rows('checkins'); }
function project_checkins_for_group(string $groupId): array { return array_values(array_filter(project_checkins(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId)); }
function project_checkin_submit(string $classId,string $projectId,string $groupId,string $studentKey,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$studentKey);if((string)$ctx['group']['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));$mood=(string)($input['mood']??'green');if(!in_array($mood,['green','yellow','red'],true))$mood='green';$visibility=(string)($input['visibility']??'team');if(!in_array($visibility,['team','teacher'],true))$visibility='team';$today=date('Y-m-d');$row=null;project_workspace_update('checkins',static function(array $rows) use($classId,$projectId,$groupId,$studentKey,$input,$mood,$visibility,$today,&$row): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId||(string)($r['student_key']??'')!==$studentKey||(string)($r['date']??'')!==$today)continue;$row=$r;$row['mood']=$mood;$row['done']=u_substr(trim((string)($input['done']??'')),0,500);$row['next']=u_substr(trim((string)($input['next']??'')),0,500);$row['help']=u_substr(trim((string)($input['help']??'')),0,500);$row['visibility']=$visibility;$row['updated_at']=date(DATE_ATOM);$rows[$i]=$row;break;}
    if(!$row){$row=['id'=>'pc_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'student_key'=>$studentKey,'date'=>$today,'mood'=>$mood,'done'=>u_substr(trim((string)($input['done']??'')),0,500),'next'=>u_substr(trim((string)($input['next']??'')),0,500),'help'=>u_substr(trim((string)($input['help']??'')),0,500),'visibility'=>$visibility,'created_at'=>date(DATE_ATOM),'updated_at'=>date(DATE_ATOM)];$rows[]=$row;}
    return $rows;});project_workspace_audit($classId,$projectId,$groupId,'checkin.saved',$studentKey,['mood'=>$mood,'visibility'=>$visibility]);return $row;
}

function project_team_pulse(string $groupId): array
{
    $latest=[];foreach(project_checkins_for_group($groupId) as $r){$k=(string)($r['student_key']??'');if($k==='' )continue;if(!isset($latest[$k])||strcmp((string)($r['updated_at']??''),(string)($latest[$k]['updated_at']??''))>0)$latest[$k]=$r;}
    $counts=['green'=>0,'yellow'=>0,'red'=>0];$help=0;foreach($latest as $r){$m=(string)($r['mood']??'green');if(isset($counts[$m]))$counts[$m]++;if(trim((string)($r['help']??''))!=='')$help++;}return ['counts'=>$counts,'help_requests'=>$help,'responses'=>count($latest)];
}

function project_qa_rows(): array { return project_workspace_rows('qa'); }
function project_qa_for_group(string $groupId): array { $r=array_values(array_filter(project_qa_rows(),static fn($x):bool=>is_array($x)&&(string)($x['group_id']??'')===$groupId));usort($r,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));return $r; }
function project_qa_find(string $id): ?array { foreach(project_qa_rows() as $r)if(is_array($r)&&(string)($r['id']??'')===$id)return $r;return null; }
function project_qa_create(string $classId,string $projectId,string $groupId,string $actorKey,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$actorKey);$group=$ctx['group'];if((string)$group['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));$title=u_substr(trim((string)($input['title']??'')),0,140);if($title==='')throw new RuntimeException(tr('Doplň název QA nálezu.'));$taskId=(string)($input['task_id']??'');$task=$taskId!==''?project_task_find($taskId):null;if($task&&((string)$task['group_id']!==$groupId))$task=null;$assigned=(string)($input['assigned_to']??($task['owner_key']??''));if($assigned!==''&&!project_workspace_is_member($group,$assigned))$assigned='';$severity=(string)($input['severity']??'medium');if(!in_array($severity,['low','medium','critical'],true))$severity='medium';$now=date(DATE_ATOM);
    $row=['id'=>'qa_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'task_id'=>$task?(string)$task['id']:null,'title'=>$title,'description'=>u_substr(trim((string)($input['description']??'')),0,1200),'expected'=>u_substr(trim((string)($input['expected']??'')),0,600),'actual'=>u_substr(trim((string)($input['actual']??'')),0,600),'severity'=>$severity,'status'=>'open','assigned_to'=>$assigned?:null,'created_by'=>$actorKey,'verified_by'=>null,'created_at'=>$now,'updated_at'=>$now,'verified_at'=>null];project_workspace_push('qa',$row);project_workspace_audit($classId,$projectId,$groupId,'qa.created',$actorKey,['qa_id'=>$row['id'],'severity'=>$severity,'task_id'=>$row['task_id']]);return $row;
}
function project_qa_set_status(string $classId,string $projectId,string $groupId,string $actorKey,string $issueId,string $status): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$actorKey);if((string)$ctx['group']['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));if(!in_array($status,['open','needs_fix','fixed','verified'],true))throw new RuntimeException(tr('Neplatný stav QA.'));if($status==='verified'&&!project_student_has_role($groupId,$actorKey,'qa'))throw new RuntimeException(tr('Finální QA ověření může provést student s rolí QA.'));$found=null;project_workspace_update('qa',static function(array $rows) use($issueId,$groupId,$status,$actorKey,&$found): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['id']??'')!==$issueId||(string)($r['group_id']??'')!==$groupId)continue;if($status==='verified'&&(string)($r['status']??'')!=='fixed'&&(string)($r['status']??'')!=='verified')throw new RuntimeException(tr('QA nález lze finálně ověřit až po označení opravy jako Opraveno.'));$r['status']=$status;$r['updated_at']=date(DATE_ATOM);if($status==='verified'){$r['verified_by']=$actorKey;$r['verified_at']=date(DATE_ATOM);}else{$r['verified_by']=null;$r['verified_at']=null;}$rows[$i]=$r;$found=$r;break;}
    if(!$found)throw new RuntimeException(tr('QA nález nebyl nalezen.'));return $rows;});project_workspace_audit($classId,$projectId,$groupId,'qa.status',$actorKey,['qa_id'=>$issueId,'status'=>$status]);return $found;
}

function project_retro_settings_rows(): array { return project_workspace_rows('retro_settings'); }
function project_retro_setting(string $groupId): ?array { foreach(project_retro_settings_rows() as $r)if(is_array($r)&&(string)($r['group_id']??'')===$groupId)return $r;return null; }
function project_workspace_completion(string $groupId): int
{
    $tasks=project_tasks_for_group($groupId);if(!$tasks)return 0;$done=count(array_filter($tasks,static fn($t)=>(string)($t['status']??'')==='done'));return (int)round($done/count($tasks)*100);
}
function project_retro_status(string $groupId): string
{
    $s=project_retro_setting($groupId);if($s)return (string)($s['status']??'closed');return project_workspace_completion($groupId)>=80?'open':'closed';
}
function project_teacher_set_retro(string $groupId,string $status): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může měnit retrospektivu.');if(!in_array($status,['open','closed'],true))$status='closed';$group=project_group_find($groupId);if(!$group)throw new RuntimeException('Tým nebyl nalezen.');$row=null;project_workspace_update('retro_settings',static function(array $rows) use($groupId,$status,$group,&$row): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId)continue;$r['status']=$status;$r['updated_at']=date(DATE_ATOM);$rows[$i]=$r;$row=$r;break;}
    if(!$row){$row=['id'=>'rs_'.bin2hex(random_bytes(7)),'class_id'=>$group['class_id'],'project_id'=>$group['project_id'],'group_id'=>$groupId,'status'=>$status,'created_at'=>date(DATE_ATOM),'updated_at'=>date(DATE_ATOM)];$rows[]=$row;}
    return $rows;});project_workspace_audit((string)$group['class_id'],(string)$group['project_id'],$groupId,'retro.'.$status,'teacher',[]);return $row;
}

function project_self_retro_rows(): array { return project_workspace_rows('self_retro'); }
function project_self_retro_for_group(string $groupId): array { return array_values(array_filter(project_self_retro_rows(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId)); }
function project_self_retro_for(string $groupId,string $studentKey): ?array { foreach(project_self_retro_rows() as $r)if(is_array($r)&&(string)($r['group_id']??'')===$groupId&&(string)($r['student_key']??'')===$studentKey)return $r;return null; }
function project_self_retro_submit(string $classId,string $projectId,string $groupId,string $studentKey,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$studentKey);if((string)$ctx['group']['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));if(project_retro_status($groupId)!=='open')throw new RuntimeException(tr('Retrospektiva zatím není otevřená.'));$contrib=[];$validTaskIds=array_fill_keys(array_map(static fn($t)=>(string)$t['id'],project_tasks_for_student($groupId,$studentKey)),true);foreach((array)($input['contributions']??[]) as $id){$id=(string)$id;if(isset($validTaskIds[$id]))$contrib[]=$id;}
    $ratings=[];foreach(project_roles_for_student($groupId,$studentKey) as $rr){$role=(string)$rr['role'];$v=(int)($input['role_rating'][$role]??0);if($v>=1&&$v<=5)$ratings[$role]=$v;}
    $row=null;$now=date(DATE_ATOM);$data=['class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'student_key'=>$studentKey,'contributions'=>$contrib,'proud'=>u_substr(trim((string)($input['proud']??'')),0,900),'difficult'=>u_substr(trim((string)($input['difficult']??'')),0,900),'next_time'=>u_substr(trim((string)($input['next_time']??'')),0,900),'learned'=>u_substr(trim((string)($input['learned']??'')),0,900),'role_ratings'=>$ratings,'updated_at'=>$now];
    project_workspace_update('self_retro',static function(array $rows) use($groupId,$studentKey,$data,$now,&$row): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId||(string)($r['student_key']??'')!==$studentKey)continue;$row=array_replace($r,$data);$rows[$i]=$row;break;}
    if(!$row){$row=array_replace(['id'=>'sr_'.bin2hex(random_bytes(7)),'created_at'=>$now],$data);$rows[]=$row;}return $rows;});project_workspace_audit($classId,$projectId,$groupId,'retro.self_submitted',$studentKey,[]);return $row;
}

function project_team_retro_rows(): array { return project_workspace_rows('team_retro'); }
function project_team_retro_for_group(string $groupId): array { return array_values(array_filter(project_team_retro_rows(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId)); }
function project_team_retro_submit(string $classId,string $projectId,string $groupId,string $studentKey,array $input): array
{
    project_workspace_group_context($classId,$projectId,$studentKey);if(project_retro_status($groupId)!=='open')throw new RuntimeException(tr('Retrospektiva zatím není otevřená.'));$row=null;$now=date(DATE_ATOM);$data=['class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'student_key'=>$studentKey,'keep'=>u_substr(trim((string)($input['keep']??'')),0,600),'improve'=>u_substr(trim((string)($input['improve']??'')),0,600),'try_next'=>u_substr(trim((string)($input['try_next']??'')),0,600),'updated_at'=>$now];
    project_workspace_update('team_retro',static function(array $rows) use($groupId,$studentKey,$data,$now,&$row): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId||(string)($r['student_key']??'')!==$studentKey)continue;$row=array_replace($r,$data);$rows[$i]=$row;break;}if(!$row){$row=array_replace(['id'=>'tr_'.bin2hex(random_bytes(7)),'created_at'=>$now],$data);$rows[]=$row;}return $rows;});project_workspace_audit($classId,$projectId,$groupId,'retro.team_submitted',$studentKey,[]);return $row;
}

function project_feedback_flagged(string $text): bool
{
    $t=function_exists('mb_strtolower')?mb_strtolower($text,'UTF-8'):strtolower($text);$bad=['debil','idiot','retard','kokot','píč','pic','kurv','hovno','zmrd','stupid','idiot','moron','fuck','bitch'];foreach($bad as $w)if(str_contains($t,$w))return true;return false;
}
function project_peer_feedback_rows(): array { return project_workspace_rows('peer_feedback'); }
function project_peer_feedback_for_group(string $groupId): array { return array_values(array_filter(project_peer_feedback_rows(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId)); }
function project_peer_feedback_submit(string $classId,string $projectId,string $groupId,string $reviewer,string $reviewee,array $input): array
{
    $ctx=project_workspace_group_context($classId,$projectId,$reviewer);$group=$ctx['group'];if((string)$group['id']!==$groupId||!project_workspace_is_member($group,$reviewee)||$reviewer===$reviewee)throw new RuntimeException(tr('Neplatný peer feedback.'));if(project_retro_status($groupId)!=='open')throw new RuntimeException(tr('Peer feedback zatím není otevřený.'));$ratings=[];foreach(['reliability','communication','collaboration','contribution'] as $k){$v=(int)($input[$k]??0);if($v<0||$v>5)$v=0;$ratings[$k]=$v;}$praise=u_substr(trim((string)($input['praise']??'')),0,800);$improve=u_substr(trim((string)($input['improve']??'')),0,800);if(in_array(5,$ratings,true)&&u_strlen($praise)<20)throw new RuntimeException(tr('U hodnocení „Výjimečně“ doplň konkrétní příklad toho, co spolužák udělal dobře.'));if(in_array(1,$ratings,true)&&u_strlen($improve)<20)throw new RuntimeException(tr('U hodnocení „Zřídka“ doplň konkrétní příklad a konstruktivní doporučení.'));$flagged=project_feedback_flagged($praise.' '.$improve);$row=null;$now=date(DATE_ATOM);$data=['class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'reviewer_key'=>$reviewer,'reviewee_key'=>$reviewee,'ratings'=>$ratings,'praise'=>$praise,'improve'=>$improve,'status'=>$flagged?'flagged':'approved','moderated_by'=>null,'moderated_at'=>null,'updated_at'=>$now];
    project_workspace_update('peer_feedback',static function(array $rows) use($groupId,$reviewer,$reviewee,$data,$now,&$row): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId||(string)($r['reviewer_key']??'')!==$reviewer||(string)($r['reviewee_key']??'')!==$reviewee)continue;$row=array_replace($r,$data);$rows[$i]=$row;break;}if(!$row){$row=array_replace(['id'=>'pf_'.bin2hex(random_bytes(7)),'created_at'=>$now],$data);$rows[]=$row;}return $rows;});project_workspace_audit($classId,$projectId,$groupId,'peer_feedback.submitted',$reviewer,['reviewee_key'=>$reviewee,'flagged'=>$flagged]);return $row;
}
function project_teacher_moderate_feedback(string $feedbackId,string $status): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může moderovat feedback.');if(!in_array($status,['approved','hidden'],true))throw new RuntimeException('Neplatný stav feedbacku.');$found=null;project_workspace_update('peer_feedback',static function(array $rows) use($feedbackId,$status,&$found): array {foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['id']??'')!==$feedbackId)continue;$r['status']=$status;$r['moderated_by']=teacher_display_name();$r['moderated_at']=date(DATE_ATOM);$r['updated_at']=date(DATE_ATOM);$rows[$i]=$r;$found=$r;break;}if(!$found)throw new RuntimeException('Feedback nebyl nalezen.');return $rows;});project_workspace_audit((string)$found['class_id'],(string)$found['project_id'],(string)$found['group_id'],'peer_feedback.'.$status,'teacher',['feedback_id'=>$feedbackId]);return $found;
}

function project_peer_summary(string $groupId,string $studentKey): array
{
    $group=project_group_find($groupId);$teamSize=count((array)($group['member_keys']??[]));$rows=array_values(array_filter(project_peer_feedback_for_group($groupId),static fn($r):bool=>(string)($r['reviewee_key']??'')===$studentKey&&(string)($r['status']??'')==='approved'));
    $avg=[];foreach(['reliability','communication','collaboration','contribution'] as $k){$vals=[];foreach($rows as $r){$v=(int)($r['ratings'][$k]??0);if($v>0)$vals[]=$v;}$avg[$k]=$vals?round(array_sum($vals)/count($vals),1):null;}
    return ['count'=>count($rows),'ratings'=>$avg,'anonymous'=>$teamSize>=4,'praise'=>$teamSize>=4?array_values(array_filter(array_map(static fn($r)=>trim((string)($r['praise']??'')),$rows))):[],'improve'=>$teamSize>=4?array_values(array_filter(array_map(static fn($r)=>trim((string)($r['improve']??'')),$rows))):[]];
}

function project_role_level_values(): array { return ['not_demonstrated'=>0,'developing'=>50,'competent'=>70,'strong'=>85,'exceptional'=>100]; }
function project_role_level_label(string $level): string { return ['not_demonstrated'=>'Neprokázáno','developing'=>'Rozvíjí se','competent'=>'Kompetentní','strong'=>'Silný výkon','exceptional'=>'Výjimečný výkon'][$level]??'—'; }
function project_role_evaluation_rows(): array { return project_workspace_rows('role_evaluations'); }
function project_role_evaluations_for_group(string $groupId): array { return array_values(array_filter(project_role_evaluation_rows(),static fn($r):bool=>is_array($r)&&(string)($r['group_id']??'')===$groupId)); }
function project_role_evaluation_find(string $groupId,string $studentKey,string $role=''): ?array
{
    $rows=project_role_evaluations_for_group($groupId);$fallback=null;
    foreach($rows as $r){if((string)($r['student_key']??'')!==$studentKey)continue;if($role!==''&&(string)($r['role']??'')===$role)return $r;if($fallback===null)$fallback=$r;}
    return $role===''?$fallback:null;
}

function project_teacher_save_role_evaluation(string $classId,string $projectId,string $groupId,string $studentKey,string $role,array $input): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může hodnotit projektové role.');$group=project_group_find($groupId);if(!$group||(string)$group['class_id']!==$classId||(string)$group['project_id']!==$projectId||!project_workspace_is_member($group,$studentKey))throw new RuntimeException('Tým nebo student nebyl nalezen.');$def=project_role_definition($role);if(!$def)throw new RuntimeException('Neplatná role.');$hadRole=false;foreach(project_roles_for_student($groupId,$studentKey,false) as $assignedRole)if((string)($assignedRole['role']??'')===$role){$hadRole=true;break;}if(!$hadRole)throw new RuntimeException('Tuto roli student v projektu nemá ani ji dříve neměl.');$levels=project_role_level_values();$items=[];$points=[];foreach((array)$def['rubric'] as $key=>$criterion){$lv=(string)($input['criterion'][$key]??'not_demonstrated');if(!isset($levels[$lv]))$lv='not_demonstrated';$items[$key]=$lv;$points[]=$levels[$lv];}$score=$points?round(array_sum($points)/count($points),1):0;$status=(string)($input['status']??'draft');if(!in_array($status,['draft','published'],true))$status='draft';$now=date(DATE_ATOM);$row=null;
    project_workspace_update('role_evaluations',static function(array $rows) use($classId,$projectId,$groupId,$studentKey,$role,$items,$score,$input,$status,$now,&$row): array {
    foreach($rows as $i=>$r){if(!is_array($r)||(string)($r['group_id']??'')!==$groupId||(string)($r['student_key']??'')!==$studentKey||(string)($r['role']??'')!==$role)continue;$row=$r;$row['role']=$role;$row['criteria']=$items;$row['score']=$score;$row['strengths']=u_substr(trim((string)($input['strengths']??'')),0,1200);$row['next_focus']=u_substr(trim((string)($input['next_focus']??'')),0,1200);$row['teacher_comment']=u_substr(trim((string)($input['teacher_comment']??'')),0,1600);$row['status']=$status;$row['teacher']=teacher_display_name();$row['updated_at']=$now;if($status==='published'&&empty($row['published_at']))$row['published_at']=$now;$rows[$i]=$row;break;}
    if(!$row){$row=['id'=>'re_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'student_key'=>$studentKey,'role'=>$role,'criteria'=>$items,'score'=>$score,'strengths'=>u_substr(trim((string)($input['strengths']??'')),0,1200),'next_focus'=>u_substr(trim((string)($input['next_focus']??'')),0,1200),'teacher_comment'=>u_substr(trim((string)($input['teacher_comment']??'')),0,1600),'status'=>$status,'teacher'=>teacher_display_name(),'created_at'=>$now,'updated_at'=>$now,'published_at'=>$status==='published'?$now:null];$rows[]=$row;}
    return $rows;});project_workspace_audit($classId,$projectId,$groupId,'role_evaluation.saved','teacher',['student_key'=>$studentKey,'role'=>$role,'score'=>$score,'status'=>$status]);
    project_sync_role_mastery_evidence($row);project_refresh_student_gamification($classId,$studentKey);
    return $row;
}

function project_apply_role_mastery_evidence(array $evaluation): void
{
    if(!function_exists('skill_add_evidence'))return;$classId=(string)$evaluation['class_id'];$projectId=(string)$evaluation['project_id'];$role=(string)$evaluation['role'];$studentSocialKey=(string)$evaluation['student_key'];$label=project_workspace_student_label($classId,$studentSocialKey);if($label==='')return;$skillKey=skill_student_key_for_label($classId,$label);$score=max(0,min(100,(float)($evaluation['score']??0)));$skills=project_role_skill_candidates($classId,$projectId,$role);$changed=false;
    $meta=['project_id'=>$projectId,'group_id'=>$evaluation['group_id'],'role'=>$role,'role_evaluation_id'=>$evaluation['id']];$validator=teacher_display_name();$updates=[];
    $match=static fn($er,string $slug,string $source):bool=>is_array($er)&&(string)($er['class_id']??'')===$classId&&(string)($er['student_key']??'')===$skillKey&&(string)($er['skill']??'')===$slug&&(string)($er['type']??'')==='project'&&(string)($er['source_id']??'')===$source;
    foreach(array_slice($skills,0,3) as $slug){$slug=(string)$slug;$source='role-eval:'.(string)$evaluation['id'].':'.$slug;$exists=false;foreach(skill_evidence_rows() as $er){if($match($er,$slug,$source)){$exists=true;break;}}
        if(!$exists){skill_add_evidence($classId,$skillKey,$slug,'project',$source,$score,100,'approved',$meta,false);$changed=true;continue;}
        $skill=skill_find($slug);if(!$skill)continue;$rules=skill_evidence_rules($skill);$max=(float)($rules['project']??10);$updates[$source]=['slug'=>$slug,'points'=>round($max*$score/100,2)];
    }
    // v58 (F2): úprava existující evidence pod zámkem souboru (sloučení do aktuálních dat, ne přepsání starou kopií).
    if($updates){storage_update(skill_storage('evidence'),static function(array $rows) use($updates,$match,$score,$meta,$validator): array {
        foreach($rows as $i=>$er){foreach($updates as $source=>$u){if(!$match($er,(string)$u['slug'],(string)$source))continue;$rows[$i]['score']=$score;$rows[$i]['max_score']=100;$rows[$i]['normalized']=$score/100;$rows[$i]['points']=$u['points'];$rows[$i]['state']='approved';$rows[$i]['validated_at']=date(DATE_ATOM);$rows[$i]['validated_by']=$validator;$rows[$i]['meta']=$meta;break;}}
        return $rows;});$changed=true;}
    if($changed){skill_add_audit($classId,$skillKey,'evidence.project_role_synced',$role,['role_evaluation_id'=>$evaluation['id'],'score'=>$score]);}
    if($skills)skill_recalculate_all($classId,$skillKey);
}

function project_sync_role_mastery_evidence(array $evaluation): void
{
    if(!function_exists('skill_evidence_rows'))return;if((string)($evaluation['status']??'draft')==='published'){project_apply_role_mastery_evidence($evaluation);return;}
    $classId=(string)$evaluation['class_id'];$label=project_workspace_student_label($classId,(string)$evaluation['student_key']);if($label==='')return;$skillKey=skill_student_key_for_label($classId,$label);$prefix='role-eval:'.(string)$evaluation['id'].':';$changed=false;
    storage_update(skill_storage('evidence'),static function(array $rows) use($classId,$skillKey,$prefix,&$changed): array {
    foreach($rows as &$row){if(!is_array($row)||(string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$skillKey||!str_starts_with((string)($row['source_id']??''),$prefix))continue;if((string)($row['state']??'')!=='revoked'){$row['state']='revoked';$row['validated_at']=date(DATE_ATOM);$row['validated_by']=teacher_display_name();$row['reason']='Role evaluation changed back to draft.';$changed=true;}}unset($row);
    return $rows;});
    if($changed){skill_recalculate_all($classId,$skillKey);skill_add_audit($classId,$skillKey,'evidence.project_role_revoked',(string)$evaluation['role'],['role_evaluation_id'=>$evaluation['id']]);}
}

function project_portfolio_rows(): array { return project_workspace_rows('portfolio'); }
function project_portfolio_is_featured(string $groupId,string $studentKey): bool
{
    foreach(project_portfolio_rows() as $r)if(is_array($r)&&(string)($r['group_id']??'')===$groupId&&(string)($r['student_key']??'')===$studentKey&&!empty($r['featured']))return true;return false;
}
function project_portfolio_toggle(string $classId,string $projectId,string $groupId,string $studentKey): bool
{
    $ctx=project_workspace_group_context($classId,$projectId,$studentKey);if((string)$ctx['group']['id']!==$groupId)throw new RuntimeException(tr('Tento tým ti nepatří.'));$published=array_values(array_filter(project_role_evaluations_for_group($groupId),static fn($r):bool=>(string)($r['student_key']??'')===$studentKey&&(string)($r['status']??'')==='published'));if(!$published)throw new RuntimeException(tr('Projekt můžeš vystavit až po publikovaném hodnocení role.'));$featured=false;project_workspace_update('portfolio',static function(array $rows) use($classId,$projectId,$groupId,$studentKey,&$featured): array {$idx=null;foreach($rows as $i=>$r)if(is_array($r)&&(string)($r['group_id']??'')===$groupId&&(string)($r['student_key']??'')===$studentKey){$idx=$i;$featured=!empty($r['featured']);break;}
    if(!$featured){$active=count(array_filter($rows,static fn($r):bool=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey&&!empty($r['featured'])));if($active>=3)throw new RuntimeException(tr('Na profilu mohou být maximálně tři Featured projekty. Nejdřív jeden odeber.'));}
    $record=['id'=>$idx!==null?(string)$rows[$idx]['id']:'pp_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'project_id'=>$projectId,'group_id'=>$groupId,'student_key'=>$studentKey,'featured'=>!$featured,'updated_at'=>date(DATE_ATOM)];if($idx===null){$record['created_at']=date(DATE_ATOM);$rows[]=$record;}else{$rows[$idx]=array_replace($rows[$idx],$record);}return $rows;});project_workspace_audit($classId,$projectId,$groupId,'portfolio.'.(!$featured?'featured':'unfeatured'),$studentKey,[]);return !$featured;
}
function project_portfolio_entries(string $classId,string $studentKey,bool $featuredOnly=false): array
{
    $featureMap=[];foreach(project_portfolio_rows() as $r)if(is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey)$featureMap[(string)$r['group_id']]=!empty($r['featured']);$byGroup=[];
    foreach(project_role_evaluation_rows() as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId||(string)($r['student_key']??'')!==$studentKey||(string)($r['status']??'')!=='published')continue;$gid=(string)$r['group_id'];if($featuredOnly&&empty($featureMap[$gid]))continue;$byGroup[$gid][]=$r;}
    $out=[];foreach($byGroup as $gid=>$evals){$group=project_group_find($gid);if(!$group)continue;$project=project_find($classId,(string)$group['project_id']);if(!$project)continue;$tasks=project_tasks_for_student($gid,$studentKey);$done=array_values(array_filter($tasks,static fn($t)=>(string)($t['status']??'')==='done'));$roles=[];$skills=[];$scores=[];$latest='';foreach($evals as $ev){$role=(string)$ev['role'];$roles[]=$role;$scores[]=(float)$ev['score'];$latest=max($latest,(string)($ev['published_at']??$ev['updated_at']??''));foreach(project_role_skill_candidates($classId,(string)$group['project_id'],$role) as $slug)$skills[$slug]=true;}
        $out[]=['group_id'=>$gid,'project_id'=>(string)$group['project_id'],'project_title'=>(string)$project['title'],'group_name'=>(string)$group['name'],'roles'=>array_values(array_unique($roles)),'skills'=>array_keys($skills),'contributions'=>array_slice(array_map(static fn($t)=>(string)$t['title'],$done),0,5),'average_score'=>$scores?round(array_sum($scores)/count($scores),1):0,'featured'=>!empty($featureMap[$gid]),'published_at'=>$latest];}
    usort($out,static fn($a,$b)=>strcmp((string)$b['published_at'],(string)$a['published_at']));return $out;
}

function project_student_contribution_signals(string $classId,string $projectId,string $groupId,string $studentKey): array
{
    $signals=[];$tasks=project_tasks_for_student($groupId,$studentKey);$done=count(array_filter($tasks,static fn($t)=>(string)($t['status']??'')==='done'));$health=project_workspace_health($classId,$projectId,$groupId);$roles=project_roles_for_student($groupId,$studentKey);$peer=project_peer_summary($groupId,$studentKey);$self=project_self_retro_for($groupId,$studentKey);
    if($roles&&!$tasks)$signals[]=['severity'=>'medium','code'=>'no_tasks','text'=>'Má projektovou roli, ale zatím žádnou konkrétní task/support odpovědnost.'];
    if(count($tasks)>=3&&$health['completion']>=70&&$done/max(1,count($tasks))<.35)$signals[]=['severity'=>'medium','code'=>'low_completion','text'=>'Projekt je v pozdní fázi, ale většina jeho/jejích přiřazených úkolů není dokončena.'];
    $peerContribution=$peer['ratings']['contribution']??null;if($peer['count']>=2&&$peerContribution!==null&&(float)$peerContribution<2.5)$signals[]=['severity'=>'medium','code'=>'peer_contribution','text'=>'Peer evidence ukazuje na nízký vnímaný přínos; doporučený je rozhovor nad konkrétními výstupy.'];
    if($self&&$peer['count']>=2){$selfRatings=array_values(array_filter(array_map('intval',(array)($self['role_ratings']??[])),static fn($v)=>$v>0));$selfAvg=$selfRatings?array_sum($selfRatings)/count($selfRatings):0;if($selfAvg>=4.8&&$peerContribution!==null&&(float)$peerContribution<3)$signals[]=['severity'=>'low','code'=>'self_peer_gap','text'=>'Sebereflexe a peer evidence se výrazně liší; vhodné k reflexivnímu rozhovoru, ne k automatické penalizaci.'];}
    return $signals;
}

function project_class_collaboration_insights(string $classId): array
{
    $students=project_students_for_class($classId);$roleCounts=array_fill_keys(array_keys(project_role_definitions()),0);$strongCounts=array_fill_keys(array_keys(project_role_definitions()),0);$rotation=[];$evalCount=0;
    foreach($students as $studentKey=>$student){$stats=project_role_history_stats($classId,(string)$studentKey);$evalCount+=(int)$stats['evaluations'];foreach($roleCounts as $role=>$v){$roleCounts[$role]+=(int)($stats['role_counts'][$role]??0);$strongCounts[$role]+=(int)($stats['strong_counts'][$role]??0);}if((int)$stats['evaluations']>=3&&(int)$stats['distinct_roles']<=1)$rotation[]=['student_key'=>(string)$studentKey,'label'=>(string)$student['label'],'text'=>'Má 3+ ověřené role, ale zatím pouze jeden typ odpovědnosti.'];elseif(count((array)$stats['recent_roles'])>=3&&count(array_unique(array_slice((array)$stats['recent_roles'],0,3)))===1)$rotation[]=['student_key'=>(string)$studentKey,'label'=>(string)$student['label'],'text'=>'Poslední tři ověřené projekty má ve stejné roli.'];}
    $themes=['qa'=>['qa','test','kontrol','ověř'],'communication'=>['komunik','domluv','inform'],'planning'=>['plán','termín','čas','pozd'],'ownership'=>['odpověd','role','vlastník','úkol'],'documentation'=>['dokument','popis','zápis'],'research'=>['zdroj','výzkum','research']];$themeCounts=array_fill_keys(array_keys($themes),0);
    $retroRows=array_merge(project_self_retro_rows(),project_team_retro_rows());foreach($retroRows as $r){if(!is_array($r)||(string)($r['class_id']??'')!==$classId)continue;$parts=[];foreach(['difficult','next_time','improve','try_next'] as $field){$value=$r[$field]??'';if(is_scalar($value))$parts[]=(string)$value;} $joined=implode(' ',$parts);$text=function_exists('mb_strtolower')?mb_strtolower($joined,'UTF-8'):strtolower($joined);foreach($themes as $theme=>$words)foreach($words as $word)if(str_contains($text,$word)){$themeCounts[$theme]++;break;}}
    arsort($themeCounts);return ['role_counts'=>$roleCounts,'strong_counts'=>$strongCounts,'rotation_candidates'=>$rotation,'retro_themes'=>$themeCounts,'published_role_evaluations'=>$evalCount];
}

function project_role_history_stats(string $classId,string $studentKey): array
{
    $rows=array_values(array_filter(project_role_evaluation_rows(),static fn($r):bool=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey&&(string)($r['status']??'')==='published'));
    usort($rows,static fn($a,$b)=>strcmp((string)($b['published_at']??$b['updated_at']??''),(string)($a['published_at']??$a['updated_at']??'')));$counts=[];$strong=[];$best=[];$roles=[];$improved=0;$byRole=[];
    foreach($rows as $r){$role=(string)($r['role']??'');if($role==='')continue;$counts[$role]=($counts[$role]??0)+1;$score=(float)($r['score']??0);if($score>=85)$strong[$role]=($strong[$role]??0)+1;$best[$role]=max((float)($best[$role]??0),$score);$roles[]=$role;$byRole[$role][]=$r;}
    foreach($byRole as $rr){usort($rr,static fn($a,$b)=>strcmp((string)($a['published_at']??''),(string)($b['published_at']??'')));for($i=1;$i<count($rr);$i++)if((float)$rr[$i]['score']-(float)$rr[$i-1]['score']>=15){$improved++;break;}}
    $retros=count(array_filter(project_self_retro_rows(),static fn($r):bool=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey));$peerGiven=count(array_filter(project_peer_feedback_rows(),static fn($r):bool=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['reviewer_key']??'')===$studentKey));
    return ['evaluations'=>count($rows),'role_counts'=>$counts,'strong_counts'=>$strong,'best_scores'=>$best,'distinct_roles'=>count($counts),'recent_roles'=>$roles,'improved_roles'=>$improved,'retrospectives'=>$retros,'peer_given'=>$peerGiven];
}

function project_achievement_definitions(): array
{
    return [
        'role_first'=>['title'=>'První projektová role','mark'=>'R1','text'=>'Získej první publikované hodnocení týmové role.','target'=>1,'kind'=>'project_dynamic'],
        'role_three'=>['title'=>'Role Explorer','mark'=>'R3','text'=>'Vyzkoušej si tři různé projektové role.','target'=>3,'kind'=>'project_dynamic'],
        'role_all_six'=>['title'=>'Versatile','mark'=>'R6','text'=>'Vyzkoušej si všech šest týmových rolí.','target'=>6,'kind'=>'project_dynamic'],
        'role_leader_3'=>['title'=>'Taking the Lead','mark'=>'L3','text'=>'Dokonči tři ověřené projekty jako Leader.','target'=>3,'kind'=>'project_dynamic'],
        'role_developer_3'=>['title'=>'Ship It','mark'=>'D3','text'=>'Dokonči tři ověřené projekty jako Developer.','target'=>3,'kind'=>'project_dynamic'],
        'role_designer_3'=>['title'=>'Design Advocate','mark'=>'◇3','text'=>'Dokonči tři ověřené projekty jako Designer.','target'=>3,'kind'=>'project_dynamic'],
        'role_researcher_3'=>['title'=>'Research Driven','mark'=>'⌕3','text'=>'Dokonči tři ověřené projekty jako Researcher.','target'=>3,'kind'=>'project_dynamic'],
        'role_presenter_3'=>['title'=>'Storyteller','mark'=>'▶3','text'=>'Dokonči tři ověřené projekty jako Presenter.','target'=>3,'kind'=>'project_dynamic'],
        'role_qa_3'=>['title'=>'Quality Matters','mark'=>'QA3','text'=>'Dokonči tři ověřené projekty jako QA.','target'=>3,'kind'=>'project_dynamic'],
        'peer_feedback_5'=>['title'=>'Useful Feedback','mark'=>'FB5','text'=>'Odevzdej pět strukturovaných peer feedbacků.','target'=>5,'kind'=>'project_dynamic'],
        'retro_3'=>['title'=>'Growth Mindset','mark'=>'↻3','text'=>'Dokonči tři projektové sebereflexe.','target'=>3,'kind'=>'project_dynamic'],
        'role_growth'=>['title'=>'Visible Growth','mark'=>'↑','text'=>'Zlepši ověřené hodnocení ve stejné roli alespoň o 15 bodů.','target'=>1,'kind'=>'project_dynamic'],
    ];
}
function project_achievement_value(string $classId,string $achievementId,?string $studentKey=null): int
{
    $studentKey=$studentKey?:social_current_student_key($classId);if($studentKey==='')return 0;$s=project_role_history_stats($classId,$studentKey);
    return match($achievementId){
        'role_first'=>(int)$s['evaluations'],'role_three','role_all_six'=>(int)$s['distinct_roles'],'role_leader_3'=>(int)($s['role_counts']['leader']??0),'role_developer_3'=>(int)($s['role_counts']['developer']??0),'role_designer_3'=>(int)($s['role_counts']['designer']??0),'role_researcher_3'=>(int)($s['role_counts']['researcher']??0),'role_presenter_3'=>(int)($s['role_counts']['presenter']??0),'role_qa_3'=>(int)($s['role_counts']['qa']??0),'peer_feedback_5'=>(int)$s['peer_given'],'retro_3'=>(int)$s['retrospectives'],'role_growth'=>(int)$s['improved_roles'],default=>0};
}
function project_badge_definitions(): array
{
    return [
        'project_leadership_master'=>['title'=>'Project Leadership Master','mark'=>'LEAD','rarity'=>'legendary','text'=>'Dlouhodobě prokázané silné vedení týmových projektů.','condition'=>'5+ publikovaných projektů jako Leader, alespoň 4 hodnocení Strong+.'],
        'project_quality_champion'=>['title'=>'Quality Champion','mark'=>'Q★','rarity'=>'legendary','text'=>'Dlouhodobě prokázaná odpovědnost za kvalitu a ověřování výsledků.','condition'=>'5+ publikovaných projektů jako QA, alespoň 4 hodnocení Strong+.'],
        'project_versatile_master'=>['title'=>'Project Polymath','mark'=>'6R','rarity'=>'mythic','text'=>'Silný výkon napříč všemi týmovými rolemi.','condition'=>'Vyzkoušej všech 6 rolí a v každé dosáhni alespoň jednou Strong+.'],
    ];
}
function project_refresh_student_gamification(string $classId,string $studentKey): void
{
    $label=project_workspace_student_label($classId,$studentKey);if($label==='')return;$skillKey=function_exists('skill_student_key_for_label')?skill_student_key_for_label($classId,$label):'';$profile=$skillKey!==''&&function_exists('skill_learning_profile_for_student')?skill_learning_profile_for_student($classId,$skillKey):[];$profile=array_replace(['achievements'=>[],'badges'=>[]],$profile);$ach=(array)$profile['achievements'];foreach(project_achievement_definitions() as $id=>$def){if(project_achievement_value($classId,$id,$studentKey)>=(int)$def['target']&&empty($ach[$id]))$ach[$id]=['earned_at'=>date(DATE_ATOM)];}$stats=project_role_history_stats($classId,$studentKey);$badges=(array)$profile['badges'];if((int)($stats['role_counts']['leader']??0)>=5&&(int)($stats['strong_counts']['leader']??0)>=4)$badges['project_leadership_master']=$badges['project_leadership_master']??['earned_at'=>date(DATE_ATOM)];if((int)($stats['role_counts']['qa']??0)>=5&&(int)($stats['strong_counts']['qa']??0)>=4)$badges['project_quality_champion']=$badges['project_quality_champion']??['earned_at'=>date(DATE_ATOM)];$allStrong=true;foreach(array_keys(project_role_definitions()) as $role)if((int)($stats['strong_counts'][$role]??0)<1){$allStrong=false;break;}if($allStrong&&$stats['distinct_roles']>=6)$badges['project_versatile_master']=$badges['project_versatile_master']??['earned_at'=>date(DATE_ATOM)];$profile['achievements']=$ach;$profile['badges']=$badges;if($skillKey!==''&&function_exists('skill_save_learning_profile_for_student'))skill_save_learning_profile_for_student($classId,$skillKey,$profile);
}

function project_team_skill_coverage(string $classId,string $projectId,string $groupId): array
{
    if(!function_exists('skill_project_requirements'))return [];$group=project_group_find($groupId);if(!$group)return [];$students=project_students_for_class($classId);$out=[];
    foreach(skill_project_requirements($classId,$projectId) as $req){$skill=(array)($req['skill']??[]);$slug=(string)($skill['slug']??'');if($slug==='')continue;$values=[];$bestKey='';$best=-1.0;
        foreach((array)$group['member_keys'] as $mk){$mk=(string)$mk;$label=(string)($students[$mk]['label']??'');if($label==='')continue;$skillKey=skill_student_key_for_label($classId,$label);$progress=skill_progress($classId,$slug,$skillKey);$value=(float)($progress['mastery_percent']??0);$values[$mk]=$value;if($value>$best){$best=$value;$bestKey=$mk;}}
        $avg=$values?array_sum($values)/count($values):0.0;$recommended=(float)($req['recommended_mastery']??0);$status=$best>=$recommended?($avg>=max(40,$recommended-20)?'strong':'covered'):($best>=max(20,$recommended*.6)?'developing':'growth');
        $out[]=['skill'=>$skill,'recommended_mastery'=>$recommended,'average'=>round($avg,1),'best'=>round(max(0,$best),1),'best_student_key'=>$bestKey,'best_student_label'=>(string)($students[$bestKey]['label']??''),'status'=>$status,'member_mastery'=>$values];
    }
    return $out;
}

function project_previous_growth_note(string $classId,string $studentKey,string $excludeProjectId=''): ?array
{
    $rows=array_values(array_filter(project_self_retro_rows(),static fn($r):bool=>is_array($r)&&(string)($r['class_id']??'')===$classId&&(string)($r['student_key']??'')===$studentKey&&trim((string)($r['next_time']??''))!==''));
    usort($rows,static fn($a,$b)=>strcmp((string)($b['updated_at']??$b['created_at']??''),(string)($a['updated_at']??$a['created_at']??'')));
    foreach($rows as $r)if($excludeProjectId===''||(string)($r['project_id']??'')!==$excludeProjectId)return ['project_id'=>(string)($r['project_id']??''),'next_time'=>(string)$r['next_time'],'learned'=>(string)($r['learned']??''),'at'=>(string)($r['updated_at']??$r['created_at']??'')];
    return null;
}

function project_role_coverage(string $classId,string $projectId,string $groupId): array
{
    $req=project_role_requirements($classId,$projectId);$roles=project_roles_for_group($groupId);$out=[];foreach($req as $role=>$state){$members=[];foreach($roles as $r)if((string)($r['role']??'')===$role&&(string)($r['status']??'confirmed')==='confirmed')$members[]=(string)$r['student_key'];$out[$role]=['requirement'=>$state,'covered'=>!empty($members),'member_keys'=>array_values(array_unique($members))];}return $out;
}

function project_workspace_health(string $classId,string $projectId,string $groupId): array
{
    $tasks=project_tasks_for_group($groupId);$qa=project_qa_for_group($groupId);$coverage=project_role_coverage($classId,$projectId,$groupId);$now=time();$blocked=array_values(array_filter($tasks,static fn($t)=>(string)($t['status']??'')==='blocked'));$staleBlocked=array_values(array_filter($blocked,static fn($t)=>!empty($t['blocked_at'])&&$now-strtotime((string)$t['blocked_at'])>172800));$critical=array_values(array_filter($qa,static fn($q)=>(string)($q['severity']??'')==='critical'&&(string)($q['status']??'')!=='verified'));$missing=[];foreach($coverage as $role=>$c)if((string)$c['requirement']==='required'&&!$c['covered'])$missing[]=$role;$work=[];foreach($tasks as $t){if((string)($t['status']??'')==='done')continue;$o=(string)($t['owner_key']??'');if($o!=='')$work[$o]=($work[$o]??0)+max(1,(int)($t['complexity']??1));}$total=array_sum($work);$max=$work?max($work):0;$busFactor=$total>0&&$max/$total>.6;$signals=[];if($staleBlocked)$signals[]=['severity'=>'high','code'=>'blocked','text'=>count($staleBlocked).' blokované úkoly trvají déle než 2 dny.'];if($critical)$signals[]=['severity'=>'high','code'=>'critical_qa','text'=>count($critical).' kritické QA nálezy čekají na ověření.'];if($missing)$signals[]=['severity'=>'medium','code'=>'roles','text'=>'Chybí povinné role: '.implode(', ',array_map('project_role_label',$missing)).'.'];if($busFactor)$signals[]=['severity'=>'medium','code'=>'concentration','text'=>'Velká část zbývající práce je soustředěná u jednoho člena týmu.'];if(!$signals)$signals[]=['severity'=>'ok','code'=>'on_track','text'=>'Bez zásadních signálů vyžadujících zásah.'];return ['completion'=>project_workspace_completion($groupId),'tasks_total'=>count($tasks),'tasks_done'=>count(array_filter($tasks,static fn($t)=>(string)($t['status']??'')==='done')),'blocked'=>count($blocked),'qa_open'=>count(array_filter($qa,static fn($q)=>(string)($q['status']??'')!=='verified')),'critical_qa'=>count($critical),'coverage'=>$coverage,'missing_required_roles'=>$missing,'workload'=>$work,'signals'=>$signals,'pulse'=>project_team_pulse($groupId)];
}

function project_workspace_student_summary(string $classId,string $projectId,string $groupId,string $studentKey): array
{
    $roles=project_roles_for_student($groupId,$studentKey);$tasks=project_tasks_for_student($groupId,$studentKey);$done=count(array_filter($tasks,static fn($t)=>(string)($t['status']??'')==='done'));$peer=project_peer_summary($groupId,$studentKey);$evals=array_values(array_filter(project_role_evaluations_for_group($groupId),static fn($r):bool=>(string)($r['student_key']??'')===$studentKey));$primaryRole='';foreach($roles as $r)if((string)($r['role_type']??'')==='primary'){$primaryRole=(string)$r['role'];break;}$eval=$primaryRole!==''?project_role_evaluation_find($groupId,$studentKey,$primaryRole):($evals[0]??null);return ['roles'=>$roles,'tasks'=>$tasks,'tasks_done'=>$done,'tasks_total'=>count($tasks),'next_action'=>project_next_action($groupId,$studentKey),'peer'=>$peer,'evaluation'=>$eval,'evaluations'=>$evals,'history'=>project_role_history_stats($classId,$studentKey)];
}

