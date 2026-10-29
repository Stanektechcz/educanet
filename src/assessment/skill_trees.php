<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function skill_catalog_config(): array
{
    static $catalog = null;
    if (is_array($catalog)) return $catalog;
    $catalog = require __DIR__ . '/skill_catalog.php';
    return is_array($catalog) ? $catalog : [];
}
function skill_branches(): array { return (array)(skill_catalog_config()['branches'] ?? []); }
function skill_curriculum(string $classId): array { return (array)(skill_catalog_config()['curricula'][$classId] ?? ['branches'=>[],'primary'=>[]]); }
function skill_challenges(): array { return (array)(skill_catalog_config()['challenges'] ?? []); }
function skill_specializations(): array { return (array)(skill_catalog_config()['specializations'] ?? []); }

function skill_all(): array
{
    static $all = null;
    if (is_array($all)) return $all;
    $all = [];
    foreach ((array)(skill_catalog_config()['skills'] ?? []) as $branch => $rows) {
        foreach ((array)$rows as $i => $row) {
            if (!is_array($row)) continue;
            $slug = (string)($row['slug'] ?? '');
            if ($slug === '') continue;
            $all[$slug] = array_replace($row, ['branch'=>(string)$branch,'sort_order'=>$i]);
        }
    }
    return $all;
}
function skill_find(string $slug): ?array { $all=skill_all(); return is_array($all[$slug]??null)?$all[$slug]:null; }
function skill_for_branch(string $branch): array
{
    $rows=array_values(array_filter(skill_all(), static fn(array $s):bool=>(string)$s['branch']===$branch));
    usort($rows, static fn(array $a,array $b):int=>[(int)$a['tier'],(int)$a['sort_order']] <=> [(int)$b['tier'],(int)$b['sort_order']]);
    return $rows;
}
function skill_relevant_branches(string $classId): array { return array_keys((array)(skill_curriculum($classId)['branches'] ?? [])); }
function skill_relevant_skills(string $classId): array
{
    $branches=array_flip(skill_relevant_branches($classId));
    return array_values(array_filter(skill_all(), static fn(array $s):bool=>isset($branches[(string)$s['branch']])));
}
/** Exact links from Skill nodes to existing EDUCANET Knowledge Base topics.
 * Only semantically safe mappings are listed. Unmapped skills use their own skill learning step.
 */
function skill_knowledge_topic_map(string $classId): array
{
    $maps=[
        'class_3a'=>[
            'network-fundamentals'=>'ip-addressing','osi-tcpip'=>'packet-analysis','network-hardware'=>'vlan-basics','ports-protocols'=>'ports','ipv4'=>'ip-addressing','subnetting'=>'subnetting-vlsm','switching'=>'arp','vlans'=>'vlan-basics','dns-dhcp'=>'dns-dhcp-operations','routing'=>'routing','wireshark'=>'packet-analysis','network-troubleshooting'=>'troubleshooting',
            'linux-fundamentals'=>'linux-filesystem','cli-navigation'=>'linux-filesystem','filesystem'=>'linux-filesystem','files-permissions'=>'users-permissions','users-groups'=>'users-permissions','processes'=>'processes-systemd','packages'=>'linux-filesystem','pipes-shell'=>'shell-cron','systemd'=>'processes-systemd','ssh-linux'=>'ssh-keys-ops','logs-troubleshooting'=>'journal-logs','shell-automation'=>'shell-cron',
            'security-fundamentals'=>'network-security-basics','authentication'=>'ssh-keys-ops','access-control'=>'users-permissions','encryption'=>'https','backups-patching'=>'shell-cron','firewall-security'=>'linux-firewall','secure-ssh'=>'ssh-keys-ops','logs-monitoring'=>'journal-logs','web-security'=>'web-service-linux','threat-modeling'=>'network-security-basics','incident-response'=>'web-service-linux',
        ],
        'class_4a'=>[
            'network-fundamentals'=>'tcp','osi-tcpip'=>'tcp','ports-protocols'=>'tcp','ipv4'=>'cidr','subnetting'=>'cidr','dns-dhcp'=>'dns-advanced','routing'=>'routing-advanced','wireshark'=>'diagnostics','network-troubleshooting'=>'diagnostics',
            'linux-fundamentals'=>'storage-filesystems','cli-navigation'=>'incident-runbook','filesystem'=>'storage-filesystems','files-permissions'=>'ssh-hardening','users-groups'=>'ssh-hardening','processes'=>'systemd-advanced','packages'=>'ssh-hardening','pipes-shell'=>'automation-shell','systemd'=>'systemd-advanced','ssh-linux'=>'ssh-hardening','logs-troubleshooting'=>'incident-runbook','shell-automation'=>'automation-shell',
            'security-fundamentals'=>'ssh-hardening','authentication'=>'ssh-hardening','access-control'=>'ssh-hardening','encryption'=>'tls-certificates','backups-patching'=>'backup-strategy','firewall-security'=>'ssh-hardening','secure-ssh'=>'ssh-hardening','logs-monitoring'=>'incident-runbook','web-security'=>'tls-certificates','threat-modeling'=>'automation-shell','incident-response'=>'incident-runbook',
        ],
        'class_2a'=>[
            'design-principles'=>'hierarchy','composition'=>'composition','visual-hierarchy'=>'hierarchy','color-theory'=>'contrast-color','grid-systems'=>'ui-spacing-system','image-composition'=>'photo-treatment','brand-consistency'=>'branding-system','poster-design'=>'composition','digital-graphics'=>'raster-vector','design-critique'=>'design-critique-ii','visual-systems'=>'design-tokens-ii','art-direction'=>'portfolio-presentation',
            'type-fundamentals'=>'typography','type-anatomy'=>'typography','readability'=>'accessibility-design','type-hierarchy'=>'typography','font-pairing'=>'typography','spacing-type'=>'ui-spacing-system','editorial-type'=>'typography','web-type'=>'responsive-type-ii','poster-type'=>'typography','type-grid'=>'ui-spacing-system','variable-fonts'=>'responsive-type-ii','type-accessibility'=>'accessibility-design',
            'ux-fundamentals'=>'information-architecture-ii','user-needs'=>'usability-test-ii','accessibility-ui'=>'accessibility-audit-ii','wireframes'=>'information-architecture-ii','user-flows'=>'information-architecture-ii','information-architecture'=>'information-architecture-ii','components-ui'=>'auto-layout-ii','responsive-ui'=>'css-responsive-ii','forms-states'=>'form-states-ii','prototyping'=>'html-css-handoff-ii','usability-testing'=>'usability-test-ii','design-systems'=>'auto-layout-ii',
        ],
        'class_1a'=>[
            'design-principles'=>'layout-rhythm-i','composition'=>'image-composition','visual-hierarchy'=>'visual-story-i','color-theory'=>'color-harmony','grid-systems'=>'spacing','image-composition'=>'image-composition','brand-consistency'=>'template-critique','poster-design'=>'cta','digital-graphics'=>'production-preflight-i','design-critique'=>'template-critique','visual-systems'=>'responsive-series','art-direction'=>'visual-story-i',
            'type-fundamentals'=>'editorial-typography-i','type-anatomy'=>'editorial-typography-i','readability'=>'editorial-typography-i','type-hierarchy'=>'editorial-typography-i','font-pairing'=>'editorial-typography-i','spacing-type'=>'layout-rhythm-i','editorial-type'=>'editorial-typography-i','web-type'=>'web-typography-i','poster-type'=>'cta','type-grid'=>'layout-rhythm-i','type-accessibility'=>'forms-a11y-i',
            'ux-fundamentals'=>'web-layout-basics-i','user-needs'=>'web-layout-basics-i','accessibility-ui'=>'forms-a11y-i','wireframes'=>'web-layout-basics-i','user-flows'=>'web-layout-basics-i','information-architecture'=>'web-layout-basics-i','components-ui'=>'components-i','responsive-ui'=>'responsive-layout-i','forms-states'=>'forms-a11y-i','prototyping'=>'responsive-layout-i','usability-testing'=>'web-layout-basics-i','design-systems'=>'design-system-i',
        ],
    ];
    return (array)($maps[$classId]??[]);
}

function skill_additional_knowledge_evidence_map(string $classId): array
{
    $maps=[
        'class_1a'=>[
            'content-first-layout'=>'wireframes',
            'microcopy-cta'=>'forms-states',
            'responsive-art-direction'=>'responsive-ui',
            'design-feedback'=>'design-critique',
        ],
        'class_2a'=>[
            'card-sorting'=>'information-architecture',
            'error-recovery-ux'=>'forms-states',
            'interaction-accessibility'=>'accessibility-ui',
            'design-handoff-qa'=>'design-systems',
        ],
        'class_3a'=>[
            'linux-file-troubleshooting'=>'filesystem',
            'ssh-key-operations'=>'ssh-linux',
            'stateful-firewall'=>'firewall-security',
            'bash-error-handling'=>'shell-automation',
        ],
        'class_4a'=>[
            'golden-signals'=>'logs-monitoring',
            'container-networking'=>'network-troubleshooting',
            'rollback-strategy'=>'incident-response',
            'incident-command'=>'threat-modeling',
        ],
    ];
    return (array)($maps[$classId]??[]);
}

function skill_knowledge_topic(string $classId,string $skillSlug): ?string
{
    $map=skill_knowledge_topic_map($classId);$topic=$map[$skillSlug]??null;return is_string($topic)&&$topic!==''?$topic:null;
}

function skill_project_evidence_map(string $classId): array
{
    $maps=[
      'class_1a'=>[
        '1a_poster_system'=>['composition','visual-hierarchy','poster-design','type-hierarchy'],
        '1a_identity'=>['brand-consistency','color-theory','type-hierarchy','visual-systems'],
        '1a_landing_sprint'=>['wireframes','components-ui','responsive-ui','visual-hierarchy'],
        '1a_portfolio_team_review'=>['design-critique','user-needs','usability-testing','type-hierarchy'],
      ],
      'class_2a'=>[
        '2a_campaign'=>['brand-consistency','responsive-ui','visual-hierarchy','web-type'],
        '2a_portfolio_web'=>['wireframes','prototyping','responsive-ui','web-type'],
        '2a_design_system_sprint'=>['design-systems','components-ui','visual-systems','type-grid'],
        '2a_usability_team'=>['usability-testing','user-needs','forms-states','design-critique'],
      ],
      'class_3a'=>[
        '3a_office_network'=>['ipv4','subnetting','vlans','routing'],
        '3a_monitoring'=>['logs-monitoring','logs-troubleshooting','network-troubleshooting','secure-ssh'],
        '3a_incident_team'=>['incident-response','logs-monitoring','threat-modeling','network-troubleshooting'],
        '3a_network_rollout'=>['routing','vlans','network-troubleshooting','ssh-linux'],
      ],
      'class_4a'=>[
        '4a_change_window'=>['systemd','logs-troubleshooting','shell-automation','backups-patching'],
        '4a_observability'=>['logs-monitoring','logs-troubleshooting','processes','network-troubleshooting'],
        '4a_release_team'=>['systemd','shell-automation','logs-monitoring','threat-modeling'],
        '4a_restore_team'=>['backups-patching','incident-response','logs-troubleshooting','secure-ssh'],
      ],
    ];
    return (array)($maps[$classId]??[]);
}
function skill_project_requirements(string $classId,string $projectId): array
{
    $map=skill_project_evidence_map($classId);$out=[];
    foreach((array)($map[$projectId]??[]) as $slug){$skill=skill_find((string)$slug);if(!$skill)continue;$out[]=['skill'=>$skill,'recommended_mastery'=>(int)$skill['tier']>=4?65:55];}
    return $out;
}
function skill_team_project_coverage(string $classId,string $projectId,array $memberKeys): array
{
    $students=project_students_for_class($classId);$out=[];
    foreach(skill_project_requirements($classId,$projectId) as $req){$slug=(string)$req['skill']['slug'];$best=0.0;$bestLabel='';foreach($memberKeys as $memberKey){$st=$students[(string)$memberKey]??null;if(!is_array($st))continue;$skillKey=skill_student_key_for_label($classId,(string)$st['label']);$p=skill_progress($classId,$slug,$skillKey);$value=(float)($p['mastery_percent']??0);if($value>$best){$best=$value;$bestLabel=(string)$st['label'];}}$out[]=['skill'=>$req['skill'],'recommended_mastery'=>$req['recommended_mastery'],'team_mastery'=>$best,'best_student'=>$bestLabel,'covered'=>$best>=(float)$req['recommended_mastery']];}
    return $out;
}
function skill_student_key_for_label(string $classId,string $label): string
{
    return $classId . ':s:' . substr(hash('sha256', $classId . '|' . normalized_person_name($label)), 0, 24);
}
function skill_current_student_key(string $classId): string
{
    $label=trim((string)($_SESSION['student_label']??''));
    return $label!=='' ? skill_student_key_for_label($classId,$label) : learning_profile_key($classId);
}
function skill_student_label_from_key(string $classId,string $studentKey): string
{
    // Jméno odpovídá klíči jednoznačně (klíč = hash jména) → stačí pamatovat jen nalezená jména; prázdný výsledek se nepamatuje.
    $mk=$classId."|".$studentKey; $memo=$GLOBALS['skill_label_memo'][$mk]??null; if(is_string($memo))return $memo;
    $label=skill_student_label_lookup($classId,$studentKey); if($label!=="")$GLOBALS['skill_label_memo'][$mk]=$label;
    return $label;
}
function skill_student_label_lookup(string $classId,string $studentKey): string
{
    foreach(project_students_for_class($classId) as $student){
        $label=(string)($student['label']??'');
        if(skill_student_key_for_label($classId,$label)===$studentKey) return $label;
    }
    $fallback=trim((string)($_SESSION['student_label']??''));
    if($fallback!=='' && hash_equals(skill_student_key_for_label($classId,$fallback),$studentKey)) return $fallback;
    return '';
}
function skill_storage(string $name): string { return STORAGE_DIR . '/skill_' . $name . '.json.php'; }
function skill_evidence_rows(): array { $r=load_php_json(skill_storage('evidence')); return is_array($r)?$r:[]; }
function skill_progress_rows(): array { $r=load_php_json(skill_storage('progress')); return is_array($r)?$r:[]; }
function skill_branch_rows(): array { $r=load_php_json(skill_storage('branches')); return is_array($r)?$r:[]; }
// v58 (DAT-03): pokusy a audit jsou pouze přidávané → měsíční proudy JSONL (storage_append / storage_scan).
function skill_attempt_rows(): array { return storage_stream_rows('skill_attempts'); }
function skill_audit_rows(): array { return storage_stream_rows('skill_audit'); }
/** Zapíše jen řádky, které se opravdu změnily (bez ohledu na updated_at) – pod zámkem, sloučením do aktuálních dat. */
function skill_store_merge(string $name,array $rows): void
{
    $current=storage_read(skill_storage($name));$changed=[];
    foreach($rows as $k=>$row){$old=is_array($current[$k]??null)?$current[$k]:null;if($old===null||!is_array($row)||!storage_changes_empty($old,$row,['updated_at']))$changed[$k]=$row;}
    if(!$changed)return;
    storage_update(skill_storage($name),static function(array $all) use($changed): array {foreach($changed as $k=>$row)$all[$k]=$row;return $all;});
}

/** Zahodí paměť jednoho požadavku (progress, větve, úroveň, jména, počty pro úspěchy); po zápisu dat žáka. */
function skill_memo_reset(string $classId='',string $studentKey=''): void
{
    if($classId===''||$studentKey===''){unset($GLOBALS['skill_runtime_progress'],$GLOBALS['skill_runtime_branches'],$GLOBALS['skill_req_level'],$GLOBALS['skill_label_memo'],$GLOBALS['skill_ach_cache']);return;}
    $k=$classId.'|'.$studentKey;
    unset($GLOBALS['skill_runtime_progress'][$k],$GLOBALS['skill_runtime_branches'][$k],$GLOBALS['skill_req_level'][$k],$GLOBALS['skill_ach_cache']);
}

function skill_runtime_indexes(): array
{
    $key = 'skill:indexes';
    if (isset($GLOBALS['educanet_runtime_indexes'][$key]) && is_array($GLOBALS['educanet_runtime_indexes'][$key])) {
        return $GLOBALS['educanet_runtime_indexes'][$key];
    }
    $progressByStudent = [];
    foreach (skill_progress_rows() as $storageKey=>$row) {
        if (!is_array($row)) continue;
        $classId=(string)($row['class_id']??'');$studentKey=(string)($row['student_key']??'');$slug=(string)($row['skill']??'');
        if($classId===''||$studentKey===''||$slug==='') continue;
        $progressByStudent[$classId.'|'.$studentKey][$slug]=$row;
    }
    $branchesByStudent = [];
    foreach (skill_branch_rows() as $row) {
        if (!is_array($row)) continue;
        $classId=(string)($row['class_id']??'');$studentKey=(string)($row['student_key']??'');$branch=(string)($row['branch']??'');
        if($classId===''||$studentKey===''||$branch==='') continue;
        $branchesByStudent[$classId.'|'.$studentKey][$branch]=$row;
    }
    $evidenceByStudent=[];$evidenceByClass=[];$pendingByClass=[];
    foreach(skill_evidence_rows() as $row){
        if(!is_array($row))continue;
        $classId=(string)($row['class_id']??'');$studentKey=(string)($row['student_key']??'');$skill=(string)($row['skill']??'');
        if($classId!=='')$evidenceByClass[$classId][]=$row;
        if($classId!==''&&$studentKey!==''){
            $evidenceByStudent[$classId.'|'.$studentKey][]=$row;
            if($skill!=='')$evidenceByStudent[$classId.'|'.$studentKey.'|'.$skill][]=$row;
        }
        if((string)($row['state']??'')==='pending'&&$classId!=='')$pendingByClass[$classId][]=$row;
    }
    return $GLOBALS['educanet_runtime_indexes'][$key]=[
        'progress_by_student'=>$progressByStudent,
        'branches_by_student'=>$branchesByStudent,
        'evidence_by_student'=>$evidenceByStudent,
        'evidence_by_class'=>$evidenceByClass,
        'pending_by_class'=>$pendingByClass,
    ];
}

function skill_progress_snapshot_map(string $classId,string $studentKey): array
{
    $idx=skill_runtime_indexes();$rows=$idx['progress_by_student'][$classId.'|'.$studentKey]??[];
    return is_array($rows)?$rows:[];
}
function skill_branch_snapshot_map(string $classId,string $studentKey): array
{
    $idx=skill_runtime_indexes();$rows=$idx['branches_by_student'][$classId.'|'.$studentKey]??[];$rows=is_array($rows)?$rows:[];
    foreach(skill_relevant_branches($classId) as $branch){
        if(isset($rows[$branch]))continue;
        $total=count(skill_for_branch($branch));
        $rows[$branch]=['class_id'=>$classId,'student_key'=>$studentKey,'branch'=>$branch,'mastery_percent'=>0.0,'mastery_level'=>'unexplored','skills_total'=>$total,'skills_started'=>0,'skills_mastered'=>0,'curriculum_weight'=>(float)(skill_curriculum($classId)['branches'][$branch]??1),'updated_at'=>null];
    }
    return $rows;
}
function skill_overall_mastery_from_branches(array $branches): float
{
    $num=0.0;$den=0.0;foreach($branches as $b){if(!is_array($b))continue;$w=(float)($b['curriculum_weight']??1);$num+=(float)($b['mastery_percent']??0)*$w;$den+=$w;}return $den>0?round($num/$den,2):0.0;
}
function skill_specialization_from_branches(array $branches): ?array
{
    $best=null;$bestScore=-1.0;
    foreach(skill_specializations() as $slug=>$spec){$ratios=[];$met=true;foreach((array)($spec['requirements']??[]) as $branch=>$need){$have=(float)($branches[$branch]['mastery_percent']??0);$need=(float)$need;$ratios[]=$need>0?min(1,$have/$need):1;if($have<$need)$met=false;}$score=$ratios?array_sum($ratios)/count($ratios):0;if($score>$bestScore){$bestScore=$score;$best=['slug'=>$slug,'name'=>(string)($spec['name']??$slug),'unlocked'=>$met,'progress'=>(int)round($score*100),'requirements'=>(array)($spec['requirements']??[])];}}
    return $best;
}

function skill_evidence_rules(array $skill): array
{
    if (!empty($skill['mastery_node'])) return ['challenge'=>100.0];
    return ['lesson'=>10.0,'practice'=>15.0,'quiz'=>20.0,'lab'=>25.0,'project'=>10.0,'challenge'=>30.0];
}
function skill_mastery_level(float $p): string
{
    return $p>=100?'mastered':($p>=90?'expert':($p>=75?'advanced':($p>=60?'competent':($p>=40?'developing':($p>=20?'beginner':'unexplored')))));
}
function skill_mastery_level_label(string $state): string
{
    return match($state){'mastered'=>tr('Mastered'),'expert'=>tr('Expert'),'advanced'=>tr('Advanced'),'competent'=>tr('Competent'),'developing'=>tr('Developing'),'beginner'=>tr('Beginner'),default=>tr('Unexplored')};
}
function skill_add_audit(string $classId,string $studentKey,string $action,string $subject,array $meta=[]): void
{
    storage_append('skill_audit',['id'=>'sa_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'student_key'=>$studentKey,'actor'=>teacher_export_authenticated()?teacher_display_name():'student','action'=>$action,'subject'=>$subject,'meta'=>$meta,'at'=>date(DATE_ATOM)]);
}
function skill_add_evidence(string $classId,string $studentKey,string $skillSlug,string $type,string $sourceId,float $score=100,float $maxScore=100,string $state='automatic',array $meta=[],bool $recalculate=true): array
{
    $skill=skill_find($skillSlug); if(!$skill) throw new RuntimeException('Skill nebyl nalezen.');
    if(!in_array((string)$skill['branch'],skill_relevant_branches($classId),true)) throw new RuntimeException('Tento skill není součástí aktuálního kurikula.');
    $rules=skill_evidence_rules($skill); if(!isset($rules[$type])) throw new RuntimeException('Nepodporovaný typ mastery evidence.');
    $score=max(0,min($score,max(1,$maxScore))); $normalized=max(0,min(1,$score/max(1,$maxScore)));
    $points=round((float)$rules[$type]*$normalized,2);
    $key=hash('sha256',$classId.'|'.$studentKey.'|'.$skillSlug.'|'.$type.'|'.$sourceId);
    $rows=skill_evidence_rows();
    foreach($rows as $row){ if(is_array($row) && (string)($row['idempotency_key']??'')===$key) return $row; }
    $row=['id'=>'se_'.bin2hex(random_bytes(8)),'idempotency_key'=>$key,'class_id'=>$classId,'student_key'=>$studentKey,'student_label'=>skill_student_label_from_key($classId,$studentKey),'skill'=>$skillSlug,'branch'=>(string)$skill['branch'],'type'=>$type,'source_id'=>$sourceId,'score'=>$score,'max_score'=>$maxScore,'normalized'=>$normalized,'points'=>$points,'state'=>$state,'meta'=>$meta,'created_at'=>date(DATE_ATOM),'validated_at'=>in_array($state,['automatic','approved'],true)?date(DATE_ATOM):null,'validated_by'=>$state==='approved'?(teacher_export_authenticated()?teacher_display_name():'teacher'):null];
    $existing=null;
    storage_update(skill_storage('evidence'),static function(array $rows) use($key,$row,&$existing): array {
        foreach($rows as $r){ if(is_array($r) && (string)($r['idempotency_key']??'')===$key){ $existing=$r; return $rows; } }
        $rows[]=$row; return $rows;
    });
    if($existing!==null) return $existing;
    skill_add_audit($classId,$studentKey,'evidence.created',$skillSlug,['type'=>$type,'points'=>$points,'state'=>$state]);
    skill_memo_reset($classId,$studentKey);
    if($recalculate) skill_recalculate_all($classId,$studentKey);
    return $row;
}
function skill_update_evidence_state(string $evidenceId,string $state,string $reason=''): array
{
    if(!in_array($state,['approved','rejected','revoked'],true)) throw new RuntimeException('Neplatný stav evidence.');
    $found=null; $validator=teacher_display_name();
    storage_update(skill_storage('evidence'),static function(array $rows) use($evidenceId,$state,$reason,$validator,&$found): array {
        foreach($rows as $i=>$row){
            if(!is_array($row)||(string)($row['id']??'')!==$evidenceId) continue;
            $row['state']=$state; $row['validated_at']=date(DATE_ATOM); $row['validated_by']=$validator; $row['reason']=u_substr(trim($reason),0,600); $rows[$i]=$row; $found=$row; break;
        }
        if(!$found) throw new RuntimeException('Evidence nebyla nalezena.');
        return $rows;
    });
    skill_add_audit((string)$found['class_id'],(string)$found['student_key'],'evidence.'.$state,(string)$found['skill'],['evidence_id'=>$evidenceId,'reason'=>$reason]);
    skill_memo_reset((string)$found['class_id'],(string)$found['student_key']);
    skill_recalculate_all((string)$found['class_id'],(string)$found['student_key']);
    return $found;
}
function skill_student_evidence(string $classId,string $studentKey,?string $skillSlug=null): array
{
    $idx=skill_runtime_indexes();$key=$classId.'|'.$studentKey.($skillSlug!==null?'|'.$skillSlug:'');$rows=$idx['evidence_by_student'][$key]??[];
    return is_array($rows)?array_values($rows):[];
}
function skill_effective_points(array $skill,array $evidence): array
{
    $rules=skill_evidence_rules($skill); $best=[]; $pending=[];
    foreach($evidence as $row){
        if(!is_array($row))continue; $type=(string)($row['type']??''); if(!isset($rules[$type]))continue;
        $state=(string)($row['state']??''); if($state==='pending'){ $pending[$type]=true; continue; }
        if(!in_array($state,['automatic','approved'],true))continue;
        $points=min((float)$rules[$type],max(0,(float)($row['points']??0)));
        if(!isset($best[$type])||$points>$best[$type])$best[$type]=$points;
    }
    $total=array_sum($best); $total=min(100.0,$total);
    if(empty($skill['mastery_node'])){
        // Mastery caps: knowledge alone is not enough. Practical evidence and final mastery check are mandatory for 100 %.
        if(empty($best['quiz'])) $total=min($total,79.0);
        if(empty($best['lab']) && empty($best['project'])) $total=min($total,89.0);
        if(empty($best['challenge'])) $total=min($total,99.0);
    }
    return ['points'=>round($total,2),'by_type'=>$best,'pending'=>array_keys($pending)];
}
/** Level žáka pro podmínky skillů; během jednoho přepočtu (skill_req_level) se počítá jen jednou. */
function skill_student_level(string $classId,string $studentKey): int
{
    $memo=$GLOBALS['skill_req_level'][$classId.'|'.$studentKey]??null; if(is_int($memo))return $memo;
    return skill_student_level_compute($classId,$studentKey);
}
/** Level vždy z aktuálních dat (bez paměti) – přepočet si ho zapíše do skill_req_level sám. */
function skill_student_level_compute(string $classId,string $studentKey): int
{
    $label=skill_student_label_from_key($classId,$studentKey);
    if($studentKey===skill_current_student_key($classId)){ $xp=(int)(learning_profile($classId)['xp']??0); } else { $snap=$label!==''?learning_profile_snapshot_for_student($classId,$label):['xp'=>0]; $xp=(int)($snap['xp']??0); }
    return (int)(learning_level($xp)['level']??1);
}
function skill_requirement_status(string $classId,string $studentKey,array $skill,array $progressMap): array
{
    $missing=[];
    foreach((array)($skill['requires']??[]) as $req){
        if(!is_array($req))continue; $slug=(string)($req['skill']??''); $need=(float)($req['mastery']??60); $have=(float)($progressMap[$slug]['mastery_percent']??0);
        if($have+0.001<$need)$missing[]=['type'=>'skill','skill'=>$slug,'need'=>$need,'have'=>$have];
    }
    $requiredLevel=max(1,(int)($skill['tier']??1)*3-2); $level=skill_student_level($classId,$studentKey);
    if($level<$requiredLevel)$missing[]=['type'=>'level','need'=>$requiredLevel,'have'=>$level];
    return ['met'=>!$missing,'missing'=>$missing,'required_level'=>$requiredLevel,'level'=>$level];
}
function skill_recalculate_all(string $classId,string $studentKey=''): array
{
    if($studentKey==='')$studentKey=skill_current_student_key($classId);
    $skills=skill_relevant_skills($classId); usort($skills,static fn($a,$b)=>[(int)$a['tier'],(int)$a['sort_order']]<=>[(int)$b['tier'],(int)$b['sort_order']]);
    $evidence=skill_student_evidence($classId,$studentKey); $bySkill=[]; foreach($evidence as $r)$bySkill[(string)$r['skill']][]=$r;
    $GLOBALS['skill_req_level'][$classId.'|'.$studentKey]=skill_student_level_compute($classId,$studentKey);
    $storedProgress=skill_progress_rows();$map=[];
    // Multiple passes let same-tier dependency changes settle without recursion.
    try { for($pass=0;$pass<3;$pass++){
        foreach($skills as $skill){
            $slug=(string)$skill['slug']; $calc=skill_effective_points($skill,$bySkill[$slug]??[]); $req=skill_requirement_status($classId,$studentKey,$skill,$map);
            $p=(float)$calc['points'];
            if(!$req['met'])$unlock='locked'; elseif($p>=100)$unlock='mastered'; elseif($p>=70)$unlock='mastery_ready'; elseif($p>0)$unlock='in_progress'; else $unlock='available';
            $now=date(DATE_ATOM);$previous=is_array($storedProgress[$studentKey.'|'.$slug]??null)?$storedProgress[$studentKey.'|'.$slug]:[];$lastEvidence=null;foreach((array)($bySkill[$slug]??[]) as $ev){if(!is_array($ev)||!in_array((string)($ev['state']??''),['automatic','approved'],true))continue;$at=(string)($ev['validated_at']??$ev['created_at']??'');if($at!==''&&($lastEvidence===null||strcmp($at,$lastEvidence)>0))$lastEvidence=$at;}
            $map[$slug]=['class_id'=>$classId,'student_key'=>$studentKey,'skill'=>$slug,'branch'=>(string)$skill['branch'],'mastery_points'=>$p,'mastery_percent'=>$p,'mastery_level'=>skill_mastery_level($p),'unlock_state'=>$unlock,'missing_requirements'=>$req['missing'],'evidence_by_type'=>$calc['by_type'],'pending_types'=>$calc['pending'],'started_at'=>(string)($previous['started_at']??'')!==''?$previous['started_at']:($p>0?$now:null),'unlocked_at'=>(string)($previous['unlocked_at']??'')!==''?$previous['unlocked_at']:($unlock!=='locked'?$now:null),'mastered_at'=>(string)($previous['mastered_at']??'')!==''?$previous['mastered_at']:($unlock==='mastered'?$now:null),'last_evidence_at'=>$lastEvidence,'updated_at'=>$now];
        }
    } } finally { unset($GLOBALS['skill_req_level'][$classId.'|'.$studentKey]); }
    $mine=[]; foreach($map as $slug=>$row)$mine[$studentKey.'|'.$slug]=$row; skill_store_merge('progress',$mine);
    unset($GLOBALS['skill_ach_cache']); $GLOBALS['skill_runtime_progress'][$classId.'|'.$studentKey]=$map;
    skill_recalculate_branches($classId,$studentKey,$map); skill_refresh_gamification($classId,$studentKey,$map);
    return $map;
}
function skill_progress_map(string $classId,string $studentKey=''): array
{
    if($studentKey==='')$studentKey=skill_current_student_key($classId);$cacheKey=$classId.'|'.$studentKey;
    if(isset($GLOBALS['skill_runtime_progress'][$cacheKey])&&is_array($GLOBALS['skill_runtime_progress'][$cacheKey]))return $GLOBALS['skill_runtime_progress'][$cacheKey];
    return skill_recalculate_all($classId,$studentKey);
}
function skill_progress(string $classId,string $skillSlug,string $studentKey=''): array
{
    $map=skill_progress_map($classId,$studentKey); return $map[$skillSlug]??['mastery_percent'=>0,'mastery_level'=>'unexplored','unlock_state'=>'locked','missing_requirements'=>[],'evidence_by_type'=>[],'pending_types'=>[]];
}
function skill_recalculate_branches(string $classId,string $studentKey,array $progressMap): array
{
    $curr=skill_curriculum($classId); $out=[]; $store=skill_branch_rows();
    foreach((array)($curr['branches']??[]) as $branch=>$currWeight){
        $num=0.0;$den=0.0;$mastered=0;$started=0;$total=0;
        foreach(skill_for_branch((string)$branch) as $skill){$slug=(string)$skill['slug'];$p=(float)($progressMap[$slug]['mastery_percent']??0);$w=(float)($skill['weight']??1);$num+=$p*$w;$den+=$w;$total++;if($p>0)$started++;if($p>=100)$mastered++;}
        $mastery=$den>0?round($num/$den,2):0; $row=['class_id'=>$classId,'student_key'=>$studentKey,'branch'=>(string)$branch,'mastery_percent'=>$mastery,'mastery_level'=>skill_mastery_level($mastery),'skills_total'=>$total,'skills_started'=>$started,'skills_mastered'=>$mastered,'curriculum_weight'=>(float)$currWeight,'updated_at'=>date(DATE_ATOM)];
        $out[(string)$branch]=$row; $store[$studentKey.'|'.$branch]=$row;
    }
    skill_store_merge('branches',array_filter($store,static fn($k):bool=>str_starts_with((string)$k,$studentKey.'|'),ARRAY_FILTER_USE_KEY));$GLOBALS['skill_runtime_branches'][$classId.'|'.$studentKey]=$out; return $out;
}
function skill_branch_progress_map(string $classId,string $studentKey=''): array
{
    if($studentKey==='')$studentKey=skill_current_student_key($classId);$cacheKey=$classId.'|'.$studentKey;
    if(isset($GLOBALS['skill_runtime_branches'][$cacheKey])&&is_array($GLOBALS['skill_runtime_branches'][$cacheKey]))return $GLOBALS['skill_runtime_branches'][$cacheKey];
    $progress=skill_progress_map($classId,$studentKey);if(isset($GLOBALS['skill_runtime_branches'][$cacheKey]))return $GLOBALS['skill_runtime_branches'][$cacheKey];return skill_recalculate_branches($classId,$studentKey,$progress);
}
function skill_overall_mastery(string $classId,string $studentKey=''): float
{
    $branches=skill_branch_progress_map($classId,$studentKey);$num=0;$den=0;foreach($branches as $b){$w=(float)($b['curriculum_weight']??1);$num+=(float)$b['mastery_percent']*$w;$den+=$w;}return $den?round($num/$den,2):0;
}
function skill_specialization_for(string $classId,string $studentKey=''): ?array
{
    $branches=skill_branch_progress_map($classId,$studentKey); $best=null;$bestScore=-1;
    foreach(skill_specializations() as $slug=>$spec){$ratios=[];$met=true;foreach((array)$spec['requirements'] as $branch=>$need){$have=(float)($branches[$branch]['mastery_percent']??0);$ratios[]=$need>0?min(1,$have/$need):1;if($have<$need)$met=false;}$score=$ratios?array_sum($ratios)/count($ratios):0;if($score>$bestScore){$bestScore=$score;$best=['slug'=>$slug,'name'=>(string)$spec['name'],'unlocked'=>$met,'progress'=>(int)round($score*100),'requirements'=>$spec['requirements']];}}
    return $best;
}
function skill_next_recommendation(string $classId,string $studentKey=''): ?array
{
    $map=skill_progress_map($classId,$studentKey);$best=null;$score=-999;$assigned=[];foreach(skill_student_assignments($classId,$studentKey) as $a){$slug=(string)$a['skill'];if(!isset($assigned[$slug]))$assigned[$slug]=$a;}
    foreach(skill_relevant_skills($classId) as $skill){$p=$map[(string)$skill['slug']]??[];$state=(string)($p['unlock_state']??'locked');if(in_array($state,['locked','mastered'],true))continue;$s=0;$m=(float)($p['mastery_percent']??0);if(isset($assigned[(string)$skill['slug']]))$s+=(string)$assigned[(string)$skill['slug']]['assignment_type']==='required'?80:55;if($m>=70)$s+=40;elseif($m>0)$s+=25;else$s+=10;$s+=(5-(int)$skill['tier'])*2;if($s>$score){$score=$s;$reason=isset($assigned[(string)$skill['slug']])?(((string)$assigned[(string)$skill['slug']]['assignment_type']==='required')?tr('Zadané učitelem'):tr('Doporučené učitelem')):($m>=70?tr('Téměř zvládnuto'):($m>0?tr('Pokračuj v rozpracované dovednosti'):tr('Další dostupný krok')));$best=['skill'=>$skill,'progress'=>$p,'reason'=>$reason];}}
    return $best;
}
function skill_learning_mark(string $classId,string $skillSlug): void
{
    $key=skill_current_student_key($classId); $p=skill_progress($classId,$skillSlug,$key); if((string)$p['unlock_state']==='locked')throw new RuntimeException('Skill je zatím zamčený.');
    $topic=skill_knowledge_topic($classId,$skillSlug);
    if($topic!==null){$profile=learning_profile($classId);$kb=$profile['kb'][$topic]??null;if(!is_array($kb)||empty($kb['complete']))throw new RuntimeException('Tento skill je navázaný na Knowledge Tour. Nejdřív dokonči příslušný materiál.');skill_sync_existing_learning($classId);return;}
    skill_add_evidence($classId,$key,$skillSlug,'lesson','self-study',100,100,'automatic',['origin'=>'skill_detail']);
}
function skill_knowledge_tours(): array
{
    static $all=null;if(is_array($all))return $all;$all=[];
    foreach(['knowledge_tours.php','knowledge_tours_next.php','knowledge_tours_plus.php','knowledge_tours_more.php','knowledge_tours_ecosystem.php','knowledge_tours_yearpack.php'] as $file){$path=__DIR__.'/'.$file;if(!is_file($path))continue;$rows=require $path;if(!is_array($rows))continue;foreach($rows as $classId=>$topics){if(!isset($all[$classId]))$all[$classId]=[];$all[$classId]=array_replace($all[$classId],(array)$topics);}}
    if(isset($all['class_2a']))$all['class_1a']=array_replace($all['class_2a'],(array)($all['class_1a']??[]));
    return $all;
}
function skill_knowledge_checks_for_skill(string $classId,array $skill,int $limit=3): array
{
    $map=skill_knowledge_topic_map($classId);$topics=[];$slug=(string)$skill['slug'];
    if(isset($map[$slug]))$topics[]=(string)$map[$slug];
    foreach((array)($skill['requires']??[]) as $req){$r=(string)($req['skill']??'');if(isset($map[$r]))$topics[]=(string)$map[$r];}
    foreach(skill_for_branch((string)$skill['branch']) as $candidate){foreach((array)($candidate['requires']??[]) as $req){if((string)($req['skill']??'')===$slug && isset($map[(string)$candidate['slug']]))$topics[]=(string)$map[(string)$candidate['slug']];}}
    foreach(skill_for_branch((string)$skill['branch']) as $candidate){$cs=(string)$candidate['slug'];if(isset($map[$cs]))$topics[]=(string)$map[$cs];}
    $topics=array_values(array_unique($topics));$tours=skill_knowledge_tours();$out=[];
    foreach($topics as $topic){$check=$tours[$classId][$topic]['check']??null;if(!is_array($check)||empty($check['q'])||!is_array($check['options']??null))continue;$out[]=['q'=>(string)$check['q'],'options'=>array_values(array_map('strval',(array)$check['options'])),'correct'=>(int)($check['correct']??0),'why'=>(string)($check['why']??''),'topic'=>$topic];if(count($out)>=$limit)break;}
    return $out;
}
function skill_quick_questions(string $classId,array $skill,string $mode='practice'): array
{
    $branch=(string)$skill['branch'];$desc=(string)$skill['description'];$name=(string)$skill['name'];$pool=skill_knowledge_checks_for_skill($classId,$skill,6);$content=$mode==='mastery'&&count($pool)>=6?array_slice($pool,3,3):array_slice($pool,0,3);
    if(count($content)>=3)return $content;
    $branchPrompt=match($branch){
      'networking'=>'Nejdřív izoluj vrstvu problému a ověřuj cestu od lokální konfigurace ke konkrétní službě.',
      'linux'=>'Nejdřív zjisti aktuální stav, konfiguraci a logy; změnu proveď až z evidence a potom ji ověř.',
      'security'=>'Chraň důkazy a rozsah přístupu, používej least privilege a ověř dopad i nápravu.',
      'design'=>'Rozhodnutí odvozuj od briefu, hierarchie, kompozice, konzistence a cílového média.',
      'typography'=>'Rozhodnutí ověřuj čitelností, hierarchií, rytmem, délkou řádku a kontextem použití.',
      'ui-ux'=>'Začni potřebou uživatele a scénářem; navrhni stavy, prototypuj a ověřuj použitelnost.',
      default=>'Postupuj podle cíle, evidence a ověření výsledku.'};
    $fallback=[
      ['q'=>'Jaký pracovní přístup nejlépe odpovídá oblasti „'.$name.'“?','options'=>[$branchPrompt,'Začít náhodnou změnou a už výsledek nekontrolovat.','Kopírovat řešení bez pochopení kontextu.','Přeskočit ověření a dokumentaci.'],'correct'=>0],
      ['q'=>'Co je nejsilnější důkaz, že dovednost „'.$name.'“ skutečně ovládáš?','options'=>['Samostatně vyřešený praktický úkol s vysvětlením a ověřitelným výsledkem.','Pouhé otevření stránky.','Počet přihlášení do aplikace.','Čas strávený bez výstupu.'],'correct'=>0],
      ['q'=>'Co udělat po dokončení úkolu v oblasti „'.$name.'“?','options'=>['Výsledek zkontrolovat proti cíli a umět vysvětlit klíčová rozhodnutí.','Bez kontroly pokračovat dál.','Smazat evidence.','Změnit kritéria až podle výsledku.'],'correct'=>0],
    ];
    foreach($fallback as $q){if(count($content)>=3)break;$content[]=$q;}
    return array_slice($content,0,3);
}
function skill_submit_quick_check(string $classId,string $skillSlug,array $answers,string $mode='practice'): array
{
    $skill=skill_find($skillSlug);if(!$skill)throw new RuntimeException('Skill nebyl nalezen.');$key=skill_current_student_key($classId);$p=skill_progress($classId,$skillSlug,$key);if((string)$p['unlock_state']==='locked')throw new RuntimeException('Skill je zatím zamčený.');
    if($mode==='mastery' && (float)$p['mastery_percent']<70)throw new RuntimeException('Mastery check se odemkne od 70 % mastery.');
    $qs=skill_quick_questions($classId,$skill,$mode);$correct=0;foreach($qs as $i=>$q){if(isset($answers[$i])&&(int)$answers[$i]===(int)$q['correct'])$correct++;}$score=$qs?round($correct/count($qs)*100,2):0;
    if($mode==='mastery'){
        if($score>=80)skill_add_evidence($classId,$key,$skillSlug,'challenge','mastery-check',100,100,'automatic',['mode'=>'mastery','check_score'=>$score]);
    } else {
        skill_add_evidence($classId,$key,$skillSlug,'practice','quick-practice',$score,100,'automatic',['mode'=>'practice']);
        skill_add_evidence($classId,$key,$skillSlug,'quiz','quick-quiz',$score,100,'automatic',['mode'=>'practice']);
    }
    skill_recalculate_all($classId,$key); return ['score'=>$score,'correct'=>$correct,'total'=>count($qs),'passed'=>$score>=80,'mode'=>$mode];
}
function skill_request_validation(string $classId,string $skillSlug,string $note=''): array
{
    $key=skill_current_student_key($classId);$p=skill_progress($classId,$skillSlug,$key);if((string)$p['unlock_state']==='locked')throw new RuntimeException('Skill je zatím zamčený.');
    return skill_add_evidence($classId,$key,$skillSlug,'lab','validation-request:'.bin2hex(random_bytes(6)),100,100,'pending',['note'=>u_substr(trim($note),0,800)]);
}
function skill_challenge_for_skill(string $skillSlug): ?array { foreach(skill_challenges() as $c)if(is_array($c)&&(string)($c['skill']??'')===$skillSlug)return $c;return null; }
function skill_challenge_find(string $id): ?array { $c=skill_challenges();return is_array($c[$id]??null)?$c[$id]:null; }
function skill_submit_branch_challenge(string $classId,string $challengeId,array $answers,int $hints=0): array
{
    $challenge=skill_challenge_find($challengeId);if(!$challenge)throw new RuntimeException('Challenge nebyla nalezena.');$skillSlug=(string)$challenge['skill'];$p=skill_progress($classId,$skillSlug);if((string)$p['unlock_state']==='locked')throw new RuntimeException('Mastery challenge je zatím zamčená.');
    $qs=(array)$challenge['questions'];$correct=0;foreach($qs as $i=>$q){if(isset($answers[$i])&&(int)$answers[$i]===(int)($q['correct']??-1))$correct++;}$raw=$qs?($correct/count($qs)*100):0;$hints=max(0,min((int)($challenge['max_hints']??3),$hints));$mult=max(.7,1-($hints*.1));$score=round($raw*$mult,2);$passed=$score>=(float)($challenge['pass_score']??75);
    $key=skill_current_student_key($classId);$attempt=['id'=>'sat_'.bin2hex(random_bytes(8)),'class_id'=>$classId,'student_key'=>$key,'challenge'=>$challengeId,'skill'=>$skillSlug,'score'=>$score,'raw_score'=>round($raw,2),'hints'=>$hints,'passed'=>$passed,'created_at'=>date(DATE_ATOM)];storage_append('skill_attempts',$attempt);
    if($passed)skill_add_evidence($classId,$key,$skillSlug,'challenge','branch-challenge:'.$challengeId,100,100,'automatic',['attempt_id'=>$attempt['id'],'hints'=>$hints,'challenge_score'=>$score,'raw_score'=>round($raw,2)]);
    if($passed)learning_award_once($classId,'skill_challenge:'.$challengeId,250);
    return $attempt;
}
function skill_sync_existing_learning(string $classId): void
{
    $key=skill_current_student_key($classId);$profile=learning_profile($classId);$skills=skill_relevant_skills($classId);if(!$skills)return;
    // Knowledge Base completion maps only to explicitly related skill nodes. No arbitrary index-based awarding.
    $topicMap=skill_knowledge_topic_map($classId);
    foreach($skills as $skill){$slug=(string)$skill['slug'];if(!empty($skill['mastery_node']))continue;$topic=$topicMap[$slug]??null;if(!is_string($topic)||$topic==='')continue;$kbRow=$profile['kb'][$topic]??null;if(!is_array($kbRow)||empty($kbRow['complete']))continue;skill_add_evidence($classId,$key,$slug,'lesson','kb:'.$topic,100,100,'automatic',['synced'=>true,'knowledge_topic'=>$topic],false);if(!empty($kbRow['check']))skill_add_evidence($classId,$key,$slug,'quiz','kb-check:'.$topic,100,100,'automatic',['synced'=>true,'knowledge_topic'=>$topic],false);}
    foreach(skill_additional_knowledge_evidence_map($classId) as $topic=>$slug){$kbRow=$profile['kb'][(string)$topic]??null;if(!is_array($kbRow)||empty($kbRow['complete']))continue;skill_add_evidence($classId,$key,(string)$slug,'lesson','kb-v30:'.(string)$topic,100,100,'automatic',['synced'=>true,'knowledge_topic'=>(string)$topic,'supplemental'=>true],false);if(!empty($kbRow['check']))skill_add_evidence($classId,$key,(string)$slug,'quiz','kb-v30-check:'.(string)$topic,100,100,'automatic',['synced'=>true,'knowledge_topic'=>(string)$topic,'supplemental'=>true],false);}
    // Published project results award only semantically mapped competencies for that exact project.
    $label=trim((string)($_SESSION['student_label']??''));$projectMap=skill_project_evidence_map($classId);if($label!=='')foreach(project_student_results($classId,$label) as $result){$max=max(1,(int)($result['max_points']??1));$score=(float)($result['points']??0)/$max*100;$projectId=(string)($result['record']['project_id']??$result['project_id']??$result['id']??'project');foreach((array)($projectMap[$projectId]??[]) as $skillSlug){if(!skill_find((string)$skillSlug))continue;skill_add_evidence($classId,$key,(string)$skillSlug,'project','project:'.$projectId,$score,100,'automatic',['synced'=>true,'project_id'=>$projectId],false);}}
    skill_recalculate_all($classId,$key);
}
function skill_learning_profile_for_student(string $classId,string $studentKey): array
{
    if($studentKey===skill_current_student_key($classId)) return learning_profile($classId);
    return array_replace(learning_profile_default(),learning_profile_stored($studentKey)??[]);
}
function skill_save_learning_profile_for_student(string $classId,string $studentKey,array $profile): void
{
    if($studentKey===skill_current_student_key($classId)){ learning_save_profile($classId,$profile); return; }
    learning_profile_commit($studentKey,$profile); // v58 (Z2): sloučení změn, ne přepsání celého profilu
}

function skill_refresh_gamification(string $classId,string $studentKey,array $progressMap=[]): void
{
    if(!$progressMap)$progressMap=skill_progress_map($classId,$studentKey);$branches=skill_recalculate_branches($classId,$studentKey,$progressMap);$profile=skill_learning_profile_for_student($classId,$studentKey);$ach=(array)($profile['achievements']??[]);$badges=(array)($profile['badges']??[]);$originalAch=$ach;$originalBadges=$badges;
    $masteredSkills=count(array_filter($progressMap,static fn($p)=>(float)($p['mastery_percent']??0)>=100));$advancedSkills=count(array_filter($progressMap,static fn($p)=>(float)($p['mastery_percent']??0)>=75));
    $awardAch=function(string $id)use(&$ach){if(empty($ach[$id]))$ach[$id]=['earned_at'=>date(DATE_ATOM)];};
    if($masteredSkills>=1)$awardAch('skill_first_mastered');if($masteredSkills>=5)$awardAch('skill_master_5');if($masteredSkills>=10)$awardAch('skill_master_10');if($advancedSkills>=10)$awardAch('skill_advanced_10');
    $over60=count(array_filter($branches,static fn($b)=>(float)($b['mastery_percent']??0)>=60));if($over60>=2)$awardAch('skill_double_talent');if($over60>=3)$awardAch('skill_polymath');
    $badgeMap=['networking'=>['skill_network_master','NET'],'linux'=>['skill_linux_master','LIN'],'security'=>['skill_security_master','SEC'],'design'=>['skill_design_master','DES'],'typography'=>['skill_typography_master','TYPE'],'ui-ux'=>['skill_uiux_master','UX']];
    foreach($badgeMap as $branch=>[$id,$mark]){if((float)($branches[$branch]['mastery_percent']??0)>=90){$masteryNode=null;foreach(skill_for_branch($branch) as $s)if(!empty($s['mastery_node']))$masteryNode=$s;if($masteryNode&&(float)($progressMap[(string)$masteryNode['slug']]['mastery_percent']??0)>=100 && empty($badges[$id]))$badges[$id]=['earned_at'=>date(DATE_ATOM)];}}
    if(isset($badges['skill_network_master'],$badges['skill_linux_master'],$badges['skill_security_master'])&&empty($badges['skill_infrastructure_architect']))$badges['skill_infrastructure_architect']=['earned_at'=>date(DATE_ATOM)];
    if(isset($badges['skill_design_master'],$badges['skill_typography_master'],$badges['skill_uiux_master'])&&empty($badges['skill_product_designer']))$badges['skill_product_designer']=['earned_at'=>date(DATE_ATOM)];
    if(count(array_intersect(array_keys($badges),array_column($badgeMap,0)))>=6&&empty($badges['skill_renaissance']))$badges['skill_renaissance']=['earned_at'=>date(DATE_ATOM)];
    if($ach!==$originalAch||$badges!==$originalBadges){$profile['achievements']=$ach;$profile['badges']=$badges;skill_save_learning_profile_for_student($classId,$studentKey,$profile);}
}
function skill_pending_validations(?string $classId=null): array
{
    $idx=skill_runtime_indexes();
    if($classId!==null){$rows=$idx['pending_by_class'][$classId]??[];return is_array($rows)?array_values($rows):[];}
    $out=[];foreach((array)($idx['pending_by_class']??[]) as $rows)foreach((array)$rows as $row)$out[]=$row;return $out;
}
function skill_teacher_add_evidence(string $classId,string $studentKey,string $skillSlug,string $type,float $score,string $note=''): array
{
    if(!teacher_export_authenticated())throw new RuntimeException('Pouze učitel může přidat validovanou evidence.');
    if(!in_array($type,['lab','project','challenge'],true))$type='lab';
    return skill_add_evidence($classId,$studentKey,$skillSlug,$type,'teacher:'.bin2hex(random_bytes(6)),$score,100,'approved',['note'=>u_substr(trim($note),0,800)]);
}
function skill_class_matrix(string $classId): array
{
    $rows=[];
    foreach(project_students_for_class($classId) as $student){
        $label=(string)$student['label'];$key=skill_student_key_for_label($classId,$label);$branches=skill_branch_snapshot_map($classId,$key);
        $rows[]=['student_key'=>$key,'label'=>$label,'branches'=>$branches,'overall'=>skill_overall_mastery_from_branches($branches)];
    }
    return $rows;
}

function skill_badge_definitions(): array
{
    return [
        'skill_network_master'=>['title'=>'Network Master','mark'=>'NET','rarity'=>'legendary','text'=>'Prokázána pokročilá mastery celé větve Networking.','condition'=>'Networking ≥ 90 % a dokončen Network Mastery node.'],
        'skill_linux_master'=>['title'=>'Linux Master','mark'=>'LIN','rarity'=>'legendary','text'=>'Prokázána pokročilá mastery Linux administrace.','condition'=>'Linux ≥ 90 % a dokončen Linux Mastery node.'],
        'skill_security_master'=>['title'=>'Security Guardian','mark'=>'SEC','rarity'=>'legendary','text'=>'Prokázána pokročilá obranná security mastery.','condition'=>'Security ≥ 90 % a dokončen Security Mastery node.'],
        'skill_design_master'=>['title'=>'Visual Design Master','mark'=>'DES','rarity'=>'legendary','text'=>'Prokázána mastery vizuálního designu.','condition'=>'Design ≥ 90 % a dokončen Design Mastery node.'],
        'skill_typography_master'=>['title'=>'Typography Master','mark'=>'TYPE','rarity'=>'legendary','text'=>'Prokázána mastery typografie a sazby.','condition'=>'Typography ≥ 90 % a dokončen Typography Mastery node.'],
        'skill_uiux_master'=>['title'=>'Product Design Master','mark'=>'UX','rarity'=>'legendary','text'=>'Prokázána mastery UI/UX a produktového návrhu.','condition'=>'UI/UX ≥ 90 % a dokončen UI/UX Mastery node.'],
        'skill_infrastructure_architect'=>['title'=>'Infrastructure Architect','mark'=>'ARCH','rarity'=>'mythic','text'=>'Mastery v Networkingu, Linuxu i Security.','condition'=>'Získej Network Master, Linux Master a Security Guardian.'],
        'skill_product_designer'=>['title'=>'Digital Product Designer','mark'=>'PXD','rarity'=>'mythic','text'=>'Mastery v Designu, Typography i UI/UX.','condition'=>'Získej tři kreativní mastery badge.'],
        'skill_renaissance'=>['title'=>'Renaissance Student','mark'=>'∞','rarity'=>'mythic','text'=>'Mimořádná mastery ve všech šesti větvích.','condition'=>'Získej všech šest branch mastery badge.'],
    ];
}
function skill_achievement_definitions(): array
{
    return [
        'skill_first_mastered'=>['title'=>'First Mastery','mark'=>'M1','text'=>'Master první konkrétní dovednost.','target'=>1,'kind'=>'skill_dynamic'],
        'skill_master_5'=>['title'=>'Skill Collector','mark'=>'M5','text'=>'Master pět konkrétních dovedností.','target'=>5,'kind'=>'skill_dynamic'],
        'skill_master_10'=>['title'=>'Deep Specialist','mark'=>'M10','text'=>'Master deset konkrétních dovedností.','target'=>10,'kind'=>'skill_dynamic'],
        'skill_advanced_10'=>['title'=>'Advanced Toolkit','mark'=>'A10','text'=>'Dosáhni Advanced u deseti dovedností.','target'=>10,'kind'=>'skill_dynamic'],
        'skill_double_talent'=>['title'=>'Double Talent','mark'=>'2×','text'=>'Dosáhni alespoň 60 % ve dvou větvích.','target'=>2,'kind'=>'skill_dynamic'],
        'skill_polymath'=>['title'=>'Polymath','mark'=>'3×','text'=>'Dosáhni alespoň 60 % ve třech relevantních větvích.','target'=>3,'kind'=>'skill_dynamic'],
    ];
}

function skill_achievement_value(string $classId,string $achievementId): int
{
    $ck=$classId.'|'.skill_current_student_key($classId); $cache=$GLOBALS['skill_ach_cache'][$ck]??null;
    if(!is_array($cache)){
        // Nejdřív spočítat (přepočet pokroku paměť počtů zahazuje), teprve potom zapsat.
        $map=skill_progress_map($classId);$branches=skill_branch_progress_map($classId);
        $cache=[
            'mastered'=>count(array_filter($map,static fn($p)=>(float)($p['mastery_percent']??0)>=100)),
            'advanced'=>count(array_filter($map,static fn($p)=>(float)($p['mastery_percent']??0)>=75)),
            'branches60'=>count(array_filter($branches,static fn($b)=>(float)($b['mastery_percent']??0)>=60)),
        ];
        $GLOBALS['skill_ach_cache'][$ck]=$cache;
    }
    return match($achievementId){
        'skill_first_mastered','skill_master_5','skill_master_10'=>$cache['mastered'],
        'skill_advanced_10'=>$cache['advanced'],
        'skill_double_talent','skill_polymath'=>$cache['branches60'],
        default=>0,
    };
}

function skill_assignment_rows(): array { $r=load_php_json(skill_storage('assignments')); return is_array($r)?$r:[]; }
function skill_teacher_assign(string $classId,string $skillSlug,string $targetType,string $targetKey,string $assignmentType,?string $dueAt=null): array
{
    if(!teacher_export_authenticated()) throw new RuntimeException('Pouze učitel může zadávat Skill Assignment.');
    $skill=skill_find($skillSlug);if(!$skill||!in_array((string)$skill['branch'],skill_relevant_branches($classId),true))throw new RuntimeException('Vyberte skill z kurikula třídy.');
    if(!in_array($targetType,['class','student'],true))$targetType='class';
    if($targetType==='student' && skill_student_label_from_key($classId,$targetKey)==='')throw new RuntimeException('Student nebyl nalezen.');
    $assignmentType=in_array($assignmentType,['recommended','required'],true)?$assignmentType:'recommended';
    $resolvedTarget=$targetType==='class'?$classId:$targetKey;$now=date(DATE_ATOM);
    $row=['id'=>'as_'.bin2hex(random_bytes(7)),'class_id'=>$classId,'skill'=>$skillSlug,'target_type'=>$targetType,'target_key'=>$resolvedTarget,'assignment_type'=>$assignmentType,'due_at'=>$dueAt?:null,'status'=>'active','assigned_by'=>teacher_display_name(),'created_at'=>$now,'cancelled_at'=>null];
    storage_update(skill_storage('assignments'),static function(array $rows) use($classId,$skillSlug,$targetType,$resolvedTarget,$now,$row): array {foreach($rows as &$existing){if(!is_array($existing))continue;if((string)($existing['status']??'active')!=='active')continue;if((string)($existing['class_id']??'')===$classId&&(string)($existing['skill']??'')===$skillSlug&&(string)($existing['target_type']??'')===$targetType&&(string)($existing['target_key']??'')===$resolvedTarget){$existing['status']='cancelled';$existing['cancelled_at']=$now;}}unset($existing);$rows[]=$row;return $rows;});skill_add_audit($classId,$targetType==='student'?$targetKey:'class','assignment.created',$row['id'],['skill'=>$skillSlug,'target_type'=>$targetType,'assignment_type'=>$assignmentType]);return $row;
}
function skill_teacher_unassign(string $assignmentId): void
{
    if(!teacher_export_authenticated()) throw new RuntimeException('Pouze učitel může zrušit Skill Assignment.');
    $classId='';$target='';storage_update(skill_storage('assignments'),static function(array $rows) use($assignmentId,&$classId,&$target): array {$found=false;foreach($rows as &$row){if(!is_array($row)||(string)($row['id']??'')!==$assignmentId)continue;$found=true;$classId=(string)($row['class_id']??'');$target=(string)($row['target_key']??'');$row['status']='cancelled';$row['cancelled_at']=date(DATE_ATOM);break;}unset($row);if(!$found)throw new RuntimeException('Skill Assignment nebyl nalezen.');return $rows;});skill_add_audit($classId,$target,'assignment.cancelled',$assignmentId,[]);
}
function skill_student_assignments(string $classId,string $studentKey=''): array
{
    if($studentKey==='')$studentKey=skill_current_student_key($classId);
    $rows=array_values(array_filter(skill_assignment_rows(),static fn($r):bool=>is_array($r)&&(string)($r['status']??'active')==='active'&&(string)($r['class_id']??'')===$classId&&(((string)($r['target_type']??'')==='class')||((string)($r['target_type']??'')==='student'&&(string)($r['target_key']??'')===$studentKey))));
    usort($rows,static fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));return $rows;
}
