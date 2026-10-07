<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/lesson_model_v71.php';

function teacher_curriculum_spec(): array
{
    return [
        'class_1a' => [
            'subject' => 'Grafika a webdesign · základy',
            'focus' => 'Od vizuálních základů k prvnímu responzivnímu webovému návrhu.',
            'blocks' => [
                ['range'=>'1–4','title'=>'Vizuální základy','text'=>'Hierarchie, kompozice, typografie, obraz a první vizuální identita.'],
                ['range'=>'5–9','title'=>'Systém a produkce','text'=>'Konzistence, redesign, více formátů, layout, produkční workflow.'],
                ['range'=>'10–14','title'=>'Webové základy','text'=>'Anatomie webu, responsive, komponenty, webová typografie a obraz.'],
                ['range'=>'15–18','title'=>'Přístupný web + capstone','text'=>'Formuláře, accessibility, wireframe, UI kit a responzivní landing page.'],
                ['range'=>'19–23','title'=>'Vysvětlení, obsah a responsive praxe','text'=>'Visual audit, content-first layout, responsive hero, microcopy a přístupná typografie.'],
                ['range'=>'24–28','title'=>'Art direction + týmová aplikace','text'=>'Image art direction, komponentové states, critique, landing sprint a portfolio/mastery.'],
            ],
            'capstone' => 'Responsive Landing Sprint',
            'capstone_project_id' => '1a_landing_sprint',
            'assessment' => ['Knowledge/quick checks','Praktické vizuální výstupy','Responsive + accessibility QA','Role evaluation u týmového projektu','Krátká case study a reflexe'],
            'mastery_note' => 'Design 40 % · Typography 30 % · UI/UX 30 %',
            'checklist_extra' => [
                'Připrav Canva/Figma prostředí a ověř přístup studentů.',
                'Připrav legální obrazové zdroje nebo vlastní fotografie pro cvičení.',
                'U accessibility lekcí připrav grayscale/focus/contrast kontrolu.',
                'Před capstone připrav brief a success criteria pro landing page.',
            ],
        ],
        'class_2a' => [
            'subject' => 'Grafika a webdesign',
            'focus' => 'Od vizuálního systému k UI/UX, testování, handoffu a produktové case study.',
            'blocks' => [
                ['range'=>'1–4','title'=>'Vizuální systém + digitální formáty','text'=>'Kampaň, hero sekce, motion storyboard a adaptace designu.'],
                ['range'=>'5–9','title'=>'Design systém + interakce','text'=>'Tokeny, komponenty, portfolio case study, accessibility a profesionální handoff.'],
                ['range'=>'10–14','title'=>'UX architektura + implementovatelnost','text'=>'IA, user flow, Form UX, Auto Layout, accessibility audit a HTML/CSS handoff.'],
                ['range'=>'15–18','title'=>'Responsive implementace + ověřování','text'=>'Flex/Grid mindset, usability testing, Design QA a produktový capstone.'],
                ['range'=>'19–23','title'=>'Research + robustní UX','text'=>'Research framing, card sorting, component stress test, error recovery a accessible interaction.'],
                ['range'=>'24–28','title'=>'Handoff + evidence','text'=>'Design tokens, usability round 2, Design QA, product sprint a case study/mastery.'],
            ],
            'capstone' => 'Product Page Studio',
            'capstone_project_id' => '2a_usability_team',
            'assessment' => ['Knowledge/quick checks','Komponentové a stavové výstupy','Accessibility audit','Usability evidence','Design QA + handoff','Role evaluation + case study'],
            'mastery_note' => 'Design 40 % · Typography 30 % · UI/UX 30 %',
            'checklist_extra' => [
                'Připrav testovací scénář a vzor informovaného usability testu.',
                'Ověř, že studenti umí sdílet prototyp bez editorských oprávnění.',
                'Připrav příklad design handoffu s tokeny, states a breakpoint rules.',
                'Před capstone určete, které role jsou povinné a které lze slučovat.',
            ],
        ],
        'class_3a' => [
            'subject' => 'Seminář operačních systémů a počítačových sítí',
            'focus' => 'Od síťové diagnostiky k Linux administraci a kombinovanému incidentu.',
            'blocks' => [
                ['range'=>'1–4','title'=>'Síťové služby + diagnostika','text'=>'TCP/IP, VLAN, DNS/DHCP, monitoring, firewall a evidence-first troubleshooting.'],
                ['range'=>'5–9','title'=>'Adresace + packet/service debugging','text'=>'IPv6, DNS záznamy, reservations, Wireshark mindset, VLSM a service debugging.'],
                ['range'=>'10–14','title'=>'Linux administrace','text'=>'CLI/filesystem, users/groups/permissions, procesy, systemd, logy a SSH/SFTP.'],
                ['range'=>'15–18','title'=>'Služby + automatizace + incident','text'=>'Firewall/listeners, Linux web service, shell/cron a kombinovaný Linux + network incident.'],
                ['range'=>'19–23','title'=>'Linux troubleshooting do hloubky','text'=>'Filesystem incident, systemd dependencies, journal forensics, SSH klíče a DNS evidence chain.'],
                ['range'=>'24–28','title'=>'Firewall + automatizace + mastery','text'=>'Stateful firewall, bezpečný Bash, service recovery, team incident a mastery review.'],
            ],
            'capstone' => 'Linux + Network Incident Lab',
            'capstone_project_id' => '3a_incident_team',
            'assessment' => ['Startovní diagnostika','Evidence-first labs','Service matrix + packet evidence','Linux praktické ověření','Incident timeline + validation matrix','Role evaluation + retrospektiva'],
            'mastery_note' => 'Networking 40 % · Linux 35 % · Security 25 %',
            'checklist_extra' => [
                'Připrav izolovaný Linux lab nebo bezpečnou simulaci pro SSH/systemd/firewall.',
                'Ověř testovací DNS/DHCP a síťovou topologii bez závislosti na produkční síti školy.',
                'Připrav vzor „symptom → hypotéza → test → důkaz → změna → validace“.',
                'Pro incident capstone připrav minimálně jednu síťovou a jednu OS závadu.',
            ],
        ],
        'class_4a' => [
            'subject' => 'Seminář operačních systémů a počítačových sítí',
            'focus' => 'Produkční správa, bezpečné změny, recovery, observability a reliability.',
            'blocks' => [
                ['range'=>'1–4','title'=>'Production change + observability','text'=>'Change window, proxy/TLS, logy, metriky, health, release a rollback.'],
                ['range'=>'5–9','title'=>'Reliability engineering','text'=>'HTTP observability, canary, backup/restore, SLO/error budget, IaC/drift a capacity.'],
                ['range'=>'10–14','title'=>'Linux production operations','text'=>'systemd dependencies, storage, RPO/RTO, hardening a containers.'],
                ['range'=>'15–18','title'=>'Automatizace + incident response','text'=>'Idempotentní automatizace, packet/socket evidence, runbook, postmortem a reliability drill.'],
                ['range'=>'19–23','title'=>'Observability + recovery','text'=>'Golden signals, reverse proxy evidence, container networking, restore game day a hardening audit.'],
                ['range'=>'24–28','title'=>'Safe change + incident leadership','text'=>'Rollback, drift automation, performance/capacity, incident command a reliability mastery.'],
            ],
            'capstone' => 'Production Reliability Drill',
            'capstone_project_id' => '4a_restore_team',
            'assessment' => ['Scénářová diagnostika','Change plan + rollback','Observability evidence','Restore drill','Hardening validation','Runbook + blameless postmortem','Role evaluation'],
            'mastery_note' => 'Networking 35 % · Linux 35 % · Security 30 %',
            'checklist_extra' => [
                'Používej výhradně izolované laby / testovací služby pro produkční scénáře.',
                'Před change/release labem připrav stop condition a ověřitelný rollback.',
                'Před backup lekcí ověř, že existuje bezpečný testovací restore target.',
                'Pro capstone připrav timeline, observability evidence a více možných hypotéz, ne jedinou nápovědu.',
            ],
        ],
    ];
}

/**
 * v71: lekce 1–28 přes jednotný model lm71 (lesson_model_v71.php) – stejné zdroje a precedence jako Dnešní hodina
 * a Režim hodiny (dřív vlastní loader nad zdrojovými soubory). Tvar řádků je beze změny (L1 = lesson_1_primary).
 */
function teacher_curriculum_lessons(string $classId): array
{
    return lm71_curriculum_rows($classId);
}

function teacher_curriculum_lesson_readiness(array $lesson): array
{
    $number=(int)($lesson['number']??0);
    $checks = [
        'cíl'=>trim((string)($lesson['goal']??''))!=='',
        'kroky'=>count((array)($lesson['steps']??[]))>0,
        'knowledge'=>count((array)($lesson['knowledge']??[]))>0,
    ];
    if ($number >= 10) {
        $checks['evidence']=count((array)($lesson['worksheet']??[]))>0;
        $checks['teacher notes']=count(array_filter((array)($lesson['teacher_notes']??[]),static fn($v)=>trim((string)$v)!==''))>0;
    }
    $ok=count(array_filter($checks)); $total=count($checks);
    return ['ready'=>$ok===$total,'score'=>$ok,'total'=>$total,'checks'=>$checks];
}

function teacher_curriculum_checklist_definitions(string $classId): array
{
    $spec=teacher_curriculum_spec()[$classId]??[];
    $common = [
        'setup' => ['title'=>'Před začátkem období','items'=>[
            'course_map'=> 'Projít COURSE_MAP a ověřit pořadí 28 strukturovaných lekcí + 12 aplikovaných střed.',
            'accounts'=> 'Ověřit přihlášení studentů, třídu a studentské profily.',
            'materials'=> 'Ověřit dostupnost Teacher Guide, Workbooku, Assessment Bank a Project Briefs.',
            'mastery'=> 'Zkontrolovat Skill Mastery váhy a hlavní větve pro třídu.',
            'projects'=> 'Vybrat, které individuální a týmové projekty budou skutečně hodnocené.',
        ]],
        'lesson' => ['title'=>'Před každou lekcí','items'=>[
            'lesson_goal'=> 'Mít jasný cíl lekce a evidence, podle které poznám splnění.',
            'knowledge_ready'=> 'Ověřit Knowledge Tour / zdroje a případnou simulaci.',
            'student_output'=> 'Připravit konkrétní studentský výstup, ne pouze výklad.',
            'support'=> 'Připravit podporu pro studenty, kteří potřebují menší rozsah nebo nápovědu.',
            'extension'=> 'Připravit smysluplné rozšíření pro rychlejší studenty.',
            'exit'=> 'Mít exit ticket / krátkou validaci pochopení.',
        ]],
        'projects' => ['title'=>'Před týmovým projektem','items'=>[
            'brief'=> 'Publikovat brief, success criteria a povinné výstupy.',
            'roles'=> 'Nastavit role Leader / Designer / Researcher / Developer / Presenter / QA podle projektu.',
            'groups'=> 'Zvolit způsob tvorby týmů a doporučenou velikost skupiny.',
            'qa'=> 'Definovat Definition of Done a QA gate před finálním odevzdáním.',
            'feedback'=> 'Naplánovat self reflection, peer feedback a týmovou retrospektivu.',
        ]],
        'assessment' => ['title'=>'Hodnocení a Mastery','items'=>[
            'rubric'=> 'Zkontrolovat rubriku před zadáním, ne až při hodnocení.',
            'evidence'=> 'Hodnotit konkrétní evidence a rozhodnutí, ne množství kliknutí.',
            'role_eval'=> 'U týmové práce vyhodnotit individuální roli každého studenta.',
            'mastery_evidence'=> 'Publikovat pouze ověřenou Skill Evidence relevantní k projektu.',
            'feedback_publish'=> 'Studentovi publikovat silnou stránku a jeden konkrétní další krok.',
        ]],
        'close' => ['title'=>'Uzavření období','items'=>[
            'coverage'=> 'Zkontrolovat, že byly probrány všechny povinné tematické bloky.',
            'mastery_review'=> 'Projít class Skill heatmap a identifikovat společné mezery.',
            'portfolio'=> 'Dát studentům možnost vybrat ověřené projekty do profilu/portfolia.',
            'retro'=> 'Zapsat, co v kurzu zachovat, změnit a vyzkoušet příště.',
        ]],
    ];
    if (!empty($spec['checklist_extra'])) {
        $items=[]; foreach($spec['checklist_extra'] as $i=>$text)$items['class_'.($i+1)]=$text;
        $common['class_specific']=['title'=>'Specificky pro '.teacher_class_label($classId),'items'=>$items];
    }
    return $common;
}

function teacher_curriculum_store_path(): string { return STORAGE_DIR . '/teacher_curriculum_checklist.json.php'; }
function teacher_curriculum_store(): array { return load_php_json(teacher_curriculum_store_path()); }

function teacher_curriculum_context_key(string $classId): string
{
    $teacher=normalized_person_name(function_exists('teacher_display_name')?teacher_display_name():'teacher');
    return ($teacher!==''?$teacher:'teacher').'|'.$classId;
}

function teacher_curriculum_state(string $classId): array
{
    $all=teacher_curriculum_store(); $key=teacher_curriculum_context_key($classId);
    $state=is_array($all[$key]??null)?$all[$key]:[];
    return array_replace(['items'=>[],'lessons'=>[],'note'=>'','updated_at'=>null],$state);
}

function teacher_curriculum_save_state(string $classId,array $state): void
{
    $key=teacher_curriculum_context_key($classId);
    $state['updated_at']=date(DATE_ATOM); $state['updated_by']=function_exists('teacher_display_name')?teacher_display_name():'Učitel';
    storage_map_update(teacher_curriculum_store_path(),$key,static fn(?array $current): array => $state);
}

/** v58 (F2): úprava stavu třídy pod zámkem – $fn(array $state): array; souběžné změny jiných položek se neztratí. */
function teacher_curriculum_update_state(string $classId,callable $fn): void
{
    $key=teacher_curriculum_context_key($classId);$by=function_exists('teacher_display_name')?teacher_display_name():'Učitel';
    storage_map_update(teacher_curriculum_store_path(),$key,static function(?array $state) use($fn,$by): array {
        $state=$fn(array_replace(['items'=>[],'lessons'=>[],'note'=>'','updated_at'=>null],$state??[]));
        $state['updated_at']=date(DATE_ATOM);$state['updated_by']=$by;return $state;
    });
}

function teacher_curriculum_toggle_item(string $classId,string $itemId,bool $checked): void
{
    $defs=teacher_curriculum_checklist_definitions($classId); $allowed=[];
    foreach($defs as $section) foreach((array)($section['items']??[]) as $id=>$_)$allowed[(string)$id]=true;
    if(!isset($allowed[$itemId])) throw new RuntimeException('Neznámá položka checklistu.');
    $entry=['done'=>$checked,'at'=>date(DATE_ATOM),'by'=>teacher_display_name()];
    teacher_curriculum_update_state($classId,static function(array $state) use($itemId,$entry): array {$state['items'][$itemId]=$entry;return $state;});
}

function teacher_curriculum_toggle_lesson(string $classId,int $number,bool $checked): void
{
    if($number<1||$number>28) throw new RuntimeException('Neplatné číslo lekce.');
    $entry=['prepared'=>$checked,'at'=>date(DATE_ATOM),'by'=>teacher_display_name()];
    teacher_curriculum_update_state($classId,static function(array $state) use($number,$entry): array {$state['lessons'][(string)$number]=$entry;return $state;});
}

function teacher_curriculum_save_note(string $classId,string $note): void
{
    $note=trim(u_substr($note,0,5000)); teacher_curriculum_update_state($classId,static function(array $state) use($note): array {$state['note']=$note;return $state;});
}

function teacher_curriculum_materials(string $classId): array
{
    $dir='materials/'.str_replace('class_','class_',$classId).'/';
    return [
        ['label'=>'Mapa kurzu','file'=>$dir.'COURSE_MAP.md'],
        ['label'=>'Teacher Guide','file'=>$dir.'TEACHER_GUIDE.md'],
        ['label'=>'Student Workbook','file'=>$dir.'STUDENT_WORKBOOK.md'],
        ['label'=>'Assessment Bank','file'=>$dir.'ASSESSMENT_BANK.md'],
        ['label'=>'Project Briefs','file'=>$dir.'PROJECT_BRIEFS.md'],
        ['label'=>'Teacher Checklist','file'=>$dir.'TEACHER_CHECKLIST.md'],
        ['label'=>'Lesson Readiness','file'=>$dir.'LESSON_READINESS_CHECKLIST.md'],
    ];
}

function teacher_render_curriculum(string $classId): void
{
    $specs=teacher_curriculum_spec(); $spec=$specs[$classId]??reset($specs); $lessons=teacher_curriculum_lessons($classId); $state=teacher_curriculum_state($classId);
    $defs=teacher_curriculum_checklist_definitions($classId); $skill=require __DIR__.'/skill_catalog.php'; $curr=(array)($skill['curricula'][$classId]??[]); $branches=(array)($skill['branches']??[]);
    $ready=0; foreach($lessons as $lesson)if(teacher_curriculum_lesson_readiness($lesson)['ready'])$ready++;
    $prepared=count(array_filter((array)$state['lessons'],static fn($r)=>is_array($r)&&!empty($r['prepared'])));
    $allItems=[]; foreach($defs as $s)foreach((array)$s['items'] as $id=>$txt)$allItems[$id]=$txt;
    $doneItems=count(array_filter($allItems,fn($_,$id)=>!empty($state['items'][$id]['done']),ARRAY_FILTER_USE_BOTH));
    $projects=project_catalog()[$classId]??[];
    ?>
    <section class="teacher-page-head curriculum-page-head"><div><div class="eyebrow">Výuka · <?=e(teacher_class_label($classId))?></div><h1><?=e((string)$spec['subject'])?></h1><p><?=e((string)$spec['focus'])?></p></div><div class="curriculum-head-actions"><button class="btn secondary" type="button" onclick="window.print()">Tisk / PDF</button><a class="btn primary" href="#lesson-plan">28 lekcí ↓</a></div></section>
    <section class="teacher-kpi-grid curriculum-kpis"><article><span>Lekce</span><strong><?=count($lessons)?> / 28</strong><small>kurzovní bloky v aplikaci</small></article><article><span>Obsahově připraveno</span><strong><?=$ready?> / 28</strong><small>automatická kontrola struktury</small></article><article><span>Moje příprava</span><strong><?=$prepared?> / 28</strong><small>ručně potvrzené lekce</small></article><article><span>Checklist</span><strong><?=$doneItems?> / <?=count($allItems)?></strong><small>učitelská připravenost</small></article></section>

    <section class="curriculum-layout"><div class="curriculum-main">
      <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Roční mapa</span><h2>4 tematické etapy</h2></div><small>Každý blok má jasný posun kompetence.</small></div><div class="curriculum-block-grid"><?php foreach((array)$spec['blocks'] as $b): ?><article><i><?=e((string)$b['range'])?></i><h3><?=e((string)$b['title'])?></h3><p><?=e((string)$b['text'])?></p></article><?php endforeach; ?></div></section>
      <section class="teacher-panel"><div class="teacher-panel-head"><div><span>Skill Mastery</span><h2>Váhy kompetenčních větví</h2></div><small><?=e((string)$spec['mastery_note'])?></small></div><div class="curriculum-mastery-grid"><?php foreach((array)($curr['branches']??[]) as $slug=>$weight): $b=$branches[$slug]??['name'=>$slug,'icon'=>'•']; ?><article><div><i><?=e((string)($b['icon']??'•'))?></i><strong><?=e((string)($b['name']??$slug))?></strong></div><span><?= (int)round((float)$weight*100) ?> %</span><progress max="100" value="<?= (int)round((float)$weight*100) ?>"></progress></article><?php endforeach; ?></div></section>

      <section class="teacher-panel" id="lesson-plan"><div class="teacher-panel-head"><div><span>Plán výuky</span><h2><?=count($lessons)?> dvouhodinových bloků</h2></div><small>„Připraveno“ je osobní učitelský stav, ne stav studenta.</small></div><div class="curriculum-lesson-list"><?php foreach($lessons as $lesson): $n=(int)($lesson['number']??0);$r=teacher_curriculum_lesson_readiness($lesson);$isPrepared=!empty($state['lessons'][(string)$n]['prepared']); ?><article class="curriculum-lesson <?= $isPrepared?'prepared':'' ?>"><div class="lesson-number"><?=str_pad((string)$n,2,'0',STR_PAD_LEFT)?></div><div class="lesson-body"><div class="lesson-title-row"><div><span class="readiness-pill <?=$r['ready']?'ok':'warn'?>"><?=$r['ready']?'obsah OK':'zkontrolovat'?> · <?=$r['score']?>/<?=$r['total']?></span><h3><?=e((string)($lesson['title']??('Lekce '.$n)))?></h3></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_curriculum_lesson_toggle"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="lesson_number" value="<?=$n?>"><input type="hidden" name="checked" value="<?=$isPrepared?'0':'1'?>"><button class="lesson-ready-btn <?=$isPrepared?'done':''?>" type="submit"><?=$isPrepared?'✓ připraveno':'○ označit připraveno'?></button></form></div><p><?=e((string)($lesson['goal']??''))?></p><div class="lesson-meta"><span><?=count((array)($lesson['steps']??[]))?> kroků</span><span><?=count((array)($lesson['knowledge']??[]))?> knowledge vazeb</span><?php foreach((array)($lesson['knowledge']??[]) as $k): ?><code><?=e((string)$k)?></code><?php endforeach; ?><a class="mini-action" href="?tab=teach&class=<?=e($classId)?>&lesson=<?=$n?>">▶ Spustit hodinu</a></div><details><summary>Co má být připravené</summary><div class="readiness-checks"><?php foreach($r['checks'] as $label=>$ok): ?><span class="<?=$ok?'ok':'missing'?>"><?=$ok?'✓':'○'?> <?=e((string)$label)?></span><?php endforeach; ?></div><?php if(!empty($lesson['teacher_notes'])): ?><ul><?php foreach((array)$lesson['teacher_notes'] as $note): if(trim((string)$note)==='')continue; ?><li><?=e((string)$note)?></li><?php endforeach; ?></ul><?php endif; ?><?php if(!empty($lesson['worksheet'])): ?><strong>Evidence / výstup:</strong><ul><?php foreach((array)$lesson['worksheet'] as $out): ?><li><?=e((string)$out)?></li><?php endforeach; ?></ul><?php endif; ?></details></div></article><?php endforeach; ?></div></section>
    </div>

    <aside class="curriculum-side">
      <section class="teacher-panel curriculum-sticky"><div class="teacher-panel-head compact"><div><span>Capstone</span><h3><?=e((string)$spec['capstone'])?></h3></div></div><p>Hlavní syntetický projekt navazující na roční cestu.</p><?php $cap=project_find($classId,(string)$spec['capstone_project_id']); if($cap): ?><a class="btn secondary wide" href="?tab=workspace&class=<?=e($classId)?>&project=<?=e((string)$cap['id'])?>">Otevřít Project Workspace</a><?php endif; ?><div class="curriculum-assessment"><strong>Hodnocení</strong><?php foreach((array)$spec['assessment'] as $item): ?><span>✓ <?=e((string)$item)?></span><?php endforeach; ?></div></section>
      <section class="teacher-panel"><div class="teacher-panel-head compact"><div><span>Materiály</span><h3>Balíček pro třídu</h3></div></div><div class="curriculum-material-links"><?php foreach(teacher_curriculum_materials($classId) as $m): if(!is_file(__DIR__.'/'.$m['file']))continue; ?><a href="<?=e((string)$m['file'])?>" target="_blank"><strong><?=e((string)$m['label'])?></strong><span>↗</span></a><?php endforeach; ?></div><a class="text-link" href="materials/TEACHER_OVERVIEW.md" target="_blank">Souhrn všech tříd →</a></section>
    </aside></section>

    <section class="teacher-panel curriculum-checklist-panel"><div class="teacher-panel-head"><div><span>Interaktivní checklist</span><h2>Příprava <?=e(teacher_class_label($classId))?></h2></div><small>Stav se ukládá pro aktuální učitelský účet/jméno.</small></div><div class="curriculum-checklist-grid"><?php foreach($defs as $sectionId=>$section): ?><section><h3><?=e((string)$section['title'])?></h3><?php foreach((array)$section['items'] as $id=>$text): $checked=!empty($state['items'][$id]['done']); ?><form method="post" class="curriculum-check-row <?=$checked?'done':''?>"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_curriculum_toggle"><input type="hidden" name="class_id" value="<?=e($classId)?>"><input type="hidden" name="item_id" value="<?=e((string)$id)?>"><input type="hidden" name="checked" value="<?=$checked?'0':'1'?>"><button type="submit" aria-label="<?= $checked?'Označit jako nesplněné':'Označit jako splněné' ?>"><?=$checked?'✓':'○'?></button><span><?=e((string)$text)?></span></form><?php endforeach; ?></section><?php endforeach; ?></div></section>

    <section class="teacher-panel"><div class="teacher-panel-head compact"><div><span>Poznámka</span><h3>Co si chci pohlídat</h3></div></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="teacher_curriculum_note"><input type="hidden" name="class_id" value="<?=e($classId)?>"><textarea name="note" rows="5" placeholder="Např. 2.A potřebuje více času na Auto Layout; u 3.A zopakovat permissions před systemd."><?=e((string)$state['note'])?></textarea><div class="teacher-form-actions"><button class="btn primary" type="submit">Uložit poznámku</button><?php if(!empty($state['updated_at'])): ?><small>Naposledy aktualizováno <?=e(date('d.m.Y H:i',strtotime((string)$state['updated_at'])))?></small><?php endif; ?></div></form></section>
    <?php
}
