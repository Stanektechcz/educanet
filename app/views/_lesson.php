<?php

declare(strict_types=1);

/**
 * Doporučené zdroje, vizuální přehled lekce a strukturovaná stránka lekce.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function learning_resource_cards(string $classId, array $topics = []): array
{
    global $learningResources;
    $classMap = is_array($learningResources[$classId] ?? null) ? $learningResources[$classId] : [];
    $out = [];
    foreach ($topics as $topic) {
        foreach ((array)($classMap[(string)$topic] ?? []) as $resource) {
            if (!is_array($resource) || empty($resource['url'])) continue;
            $out[(string)$resource['url']] = $resource;
        }
    }
    if (!$out) {
        foreach ((array)($classMap['_default'] ?? []) as $resource) {
            if (!is_array($resource) || empty($resource['url'])) continue;
            $out[(string)$resource['url']] = $resource;
        }
    }
    return array_slice(array_values($out), 0, 4);
}

function render_learning_resources(array $resources, ?string $title = null) : void
{
    if (!$resources) return;
    $title = $title ?? tr('Doporučené zdroje a video');
    ?>
    <section class="learning-resource-panel">
      <div class="section-heading compact-heading"><div><div class="eyebrow"><?= e(tr('Rozšíření')) ?></div><h2><?= e($title) ?></h2><p><?= e(tr('Volitelné externí materiály pro jiné vysvětlení stejného principu. Otevírají se v nové kartě.')) ?></p></div></div>
      <div class="learning-resource-grid">
        <?php foreach($resources as $r): $type=(string)($r['type']??'reference'); $yt=(string)($r['youtube_id']??''); ?>
          <?php if($type==='video' && $yt!==''): ?>
            <a class="learning-resource-card video" href="<?= e((string)$r['url']) ?>" target="_blank" rel="noopener noreferrer"><span class="video-thumb"><img loading="lazy" src="<?= e('https://i.ytimg.com/vi/'.$yt.'/hqdefault.jpg') ?>" alt="<?= e(tr('Náhled videa {nazev}', ['nazev' => (string)($r['title']??'')])) ?>"<?= edu_content_lang_attr() ?>><i aria-hidden="true"></i><?php if(!empty($r['duration'])):?><em class="video-duration"><?= e((string)$r['duration']) ?></em><?php endif;?></span><span class="video-copy"><span><?= tr_html('Video · {jazyk}', ['jazyk' => e((string)($r['lang']??'CZ'))]) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)($r['title']??tr('Video'))) ?></strong><div class="video-meta-row"><?php if(!empty($r['duration'])):?><small>⏱ <?= e((string)$r['duration']) ?></small><?php endif;?><?php if(!empty($r['level'])):?><small>↗ <?= e((string)$r['level']) ?></small><?php endif;?></div><small class="video-description"<?= edu_content_lang_attr() ?>><?= e((string)($r['description']??$r['meta']??'')) ?></small><b><?= e(tr('Přehrát na YouTube ↗')) ?></b></span></a>
          <?php else: ?>
            <a class="learning-resource-card" href="<?= e((string)$r['url']) ?>" target="_blank" rel="noopener noreferrer"><i><?= $type==='course'?'▶':($type==='activity'?'◎':($type==='video'?'▶':'↗')) ?></i><div><span><?= e($type==='video'?tr('Video'):ucfirst($type)) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)($r['title']??tr('Externí zdroj'))) ?></strong><small<?= edu_content_lang_attr() ?>><?= e((string)($r['meta']??'')) ?></small></div><b><?= e(tr('Otevřít ↗')) ?></b></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
}

function lesson_demo_topics(array $lesson, array $module, string $classId): array
{
    $candidates = [];
    foreach ((array)($lesson['knowledge'] ?? []) as $order => $topic) {
        $topic = (string)$topic;
        $article = $module['knowledgebase'][$topic] ?? null;
        if (!is_array($article)) continue;
        $scenario = reality_demo_spec($classId, $topic, $article);
        $candidates[] = ['key'=>$topic,'article'=>$article,'scene'=>(string)($scenario['scene']??''),'order'=>(int)$order];
    }
    if (count($candidates) <= 3) return $candidates;
    // Prefer visual diversity: one concrete problem of each different scene first.
    $out=[]; $seen=[];
    foreach($candidates as $row){$scene=(string)$row['scene'];if($scene!=='' && !isset($seen[$scene])){$out[]=$row;$seen[$scene]=true;if(count($out)>=3)break;}}
    if(count($out)<3){foreach($candidates as $row){$exists=false;foreach($out as $x){if($x['key']===$row['key']){$exists=true;break;}}if(!$exists){$out[]=$row;if(count($out)>=3)break;}}}
    return array_slice($out,0,3);
}

function render_lesson_visual_overview(array $lesson, string $classId, array $module): void
{
    $topics = lesson_demo_topics($lesson, $module, $classId);
    if (!$topics) return;
    $goal = trim((string)($lesson['goal'] ?? ''));
    ?>
    <section class="lesson-story-demo" data-lesson-story-demo>
      <header class="lesson-story-head">
        <div><div class="eyebrow"><?= e(tr('Reality demo · nejdřív situace, potom princip')) ?></div><h2><?= e(tr('Co budeš v této lekci skutečně řešit')) ?></h2><p><?= e($goal !== '' ? $goal : tr('Sleduj konkrétní principy této lekce a potom si je vyzkoušej na vlastní práci.')) ?></p></div>
        <div class="lesson-story-controls"><button type="button" class="btn secondary small" data-lesson-story-prev>←</button><button type="button" class="btn secondary small" data-lesson-story-play aria-pressed="false"><?= e(tr('Přehrát vše')) ?></button><button type="button" class="btn secondary small" data-lesson-story-next>→</button></div>
      </header>
      <div class="lesson-story-nav" role="tablist" aria-label="<?= e(tr('Klíčové principy lekce')) ?>">
        <?php foreach ($topics as $i=>$row): ?>
          <button type="button" role="tab" class="<?= $i===0?'active':'' ?>" data-lesson-story-tab="<?= $i ?>" aria-selected="<?= $i===0?'true':'false' ?>"><i><?= $i+1 ?></i><span<?= edu_content_lang_attr() ?>><?= e((string)($row['article']['title'] ?? $row['key'])) ?></span></button>
        <?php endforeach; ?>
      </div>
      <div class="lesson-story-scenes">
        <?php foreach ($topics as $i=>$row): $article=$row['article']; $summary=trim((string)($article['summary']??'')); $body=is_array($article['body']??null)?$article['body']:[]; $takeaway=trim((string)($body[0]??$summary)); ?>
          <article class="lesson-story-scene <?= $i===0?'active':'' ?>" data-lesson-story-scene="<?= $i ?>" <?= $i===0?'':'hidden' ?>>
            <div class="lesson-story-copy"><span><?= e(tr('Krok {n} / {total}', ['n' => $i+1, 'total' => count($topics)])) ?></span><h3<?= edu_content_lang_attr() ?>><?= e((string)($article['title'] ?? $row['key'])) ?></h3><p<?= edu_content_lang_attr() ?>><?= e($summary) ?></p><?php if($takeaway!==''): ?><div class="lesson-story-takeaway"><b><?= e(tr('Po této části máš poznat:')) ?></b><span<?= edu_content_lang_attr() ?>><?= e($takeaway) ?></span></div><?php endif; ?></div>
            <div class="lesson-story-visual"><?php render_reality_demo($classId, (string)$row['key'], $article); ?></div>
          </article>
        <?php endforeach; ?>
      </div>
      <footer class="lesson-story-footer"><span><?= tr_html('Každá ukázka je malý model reality: {retezec}.', ['retezec' => '<strong>' . e(tr('symptom → důkaz → rozhodnutí → výsledek')) . '</strong>']) ?></span><a href="#lesson-work" class="text-link"><?= e(tr('Přejít na práci ↓')) ?></a></footer>
    </section>
    <?php
}

function render_structured_lesson_page(array $lesson, string $classId, array $module, array $progress, string $stepAction, string $lessonId): void
{
    $steps = is_array($lesson['steps'] ?? null) ? $lesson['steps'] : [];
    $doneCount = count(array_filter($progress));
    $total = count($steps);
    $percent = $total ? (int)round(($doneCount / $total) * 100) : 0;
    $currentIndex = null;
    $currentStep = null;
    foreach($steps as $i=>$candidate){
        if(!is_array($candidate)) continue;
        $sid=(string)($candidate['id']??'');
        if($sid!=='' && empty($progress[$sid])){$currentIndex=$i;$currentStep=$candidate;break;}
    }
    $allComplete = $total > 0 && $doneCount >= $total;
    $supportTopics=[];
    if(is_array($currentStep['knowledge']??null)) $supportTopics=$currentStep['knowledge'];
    if(!$supportTopics && is_array($lesson['knowledge']??null)) $supportTopics=array_slice($lesson['knowledge'],0,2);
    $lessonNo=(int)($lesson['number']??1);
    $oneTaskLessonRef=$stepAction==='next_lesson_step'?'next':$lessonId;
    $oneTaskReturn=$stepAction==='next_lesson_step'?'?view=next_lesson':module_url('course_lesson',['lesson'=>$lessonId]);
    $oneTaskHref=v505_task_url('course',['lesson'=>$oneTaskLessonRef],$oneTaskReturn);
    render_header((string)($lesson['title'] ?? tr('Lekce')), $module);
    ?>
    <section class="hero lesson-course-hero guided-lesson-hero">
        <div><div class="eyebrow"><?= e(tr('Lekce {n} · {podtitul}', ['n' => str_pad((string)$lessonNo,2,'0',STR_PAD_LEFT), 'podtitul' => (string)($lesson['subtitle'] ?? tr('90 minut'))])) ?></div><h1<?= edu_content_lang_attr() ?>><?= e((string)($lesson['title'] ?? tr('Lekce'))) ?></h1><p<?= edu_content_lang_attr() ?>><?= e((string)($lesson['goal'] ?? '')) ?></p></div>
        <div class="lesson-progress-card"><span><?= e(tr('Hotovo')) ?></span><strong><?= $doneCount ?> / <?= $total ?></strong><i><em style="width:<?= $percent ?>%"></em></i><small><?= e(tr('{n} % lekce', ['n' => $percent])) ?></small></div>
    </section>

    <section class="guided-lesson-focus <?= $allComplete?'complete':'' ?>" data-guided-lesson-focus>
      <?php if(!$allComplete && is_array($currentStep)): ?>
        <div class="guided-focus-index"><span><?= e(tr('TEĎ')) ?></span><strong><?= (int)$currentIndex+1 ?></strong><small><?= e(tr('z {total}', ['total' => $total])) ?></small></div>
        <div class="guided-focus-copy"><div class="eyebrow"><?= e(tr('Jediný úkol, který teď řešíš')) ?></div><h2<?= edu_content_lang_attr() ?>><?=e((string)($currentStep['title']??tr('Aktuální krok')))?></h2><p<?= edu_content_lang_attr() ?>><?=e((string)($currentStep['intro']??$currentStep['question']??tr('Dokonči tento krok. Další část se odemkne automaticky.')))?></p>
          <div class="guided-focus-meta"><span><?=e((string)($currentStep['time']??tr('krátký krok')))?></span><span>+<?= (int)($currentStep['xp']??20) ?> XP</span><span><?= e(tr('další krok se odemkne sám')) ?></span></div>
        </div>
        <div class="guided-focus-actions"><a class="btn primary guided-main-cta" href="<?=e($oneTaskHref)?>"><?= e(tr('Pokračovat')) ?></a><button type="button" class="guided-help-trigger" data-guided-help-toggle aria-expanded="false"><?= e(tr('Nevím jak začít')) ?></button></div>
      <?php else: ?>
        <div class="guided-focus-index done"><span>✓</span><strong><?= $total ?></strong><small><?= e(tr('z {total}', ['total' => $total])) ?></small></div>
        <div class="guided-focus-copy"><div class="eyebrow"><?= e(tr('Lekce dokončena')) ?></div><h2><?= e(tr('Hotovo. Nemusíš tu nic dalšího hledat.')) ?></h2><p><?= e(tr('Vrať se na mapu kurzu. Systém ti ukáže nejbližší odemčenou lekci nebo další směr.')) ?></p></div>
        <div class="guided-focus-actions"><a class="btn primary guided-main-cta" href="?view=course"><?= e(tr('Pokračovat na další cíl')) ?> <span>→</span></a></div>
      <?php endif; ?>
    </section>

    <?php if(!$allComplete && is_array($currentStep)): ?>
    <section class="guided-inline-help" data-guided-help hidden>
      <div><span><?= e(tr('Potřebuješ nápovědu?')) ?></span><strong><?= e(tr('Nevracej se na začátek. Otevři jen podporu k tomuto kroku.')) ?></strong></div>
      <div class="guided-inline-help-links">
        <?php foreach($supportTopics as $topic): $topic=(string)$topic; $a=$module['knowledgebase'][$topic]??null; if(!is_array($a)) continue; $supportTitle=(string)($a['title']??$topic); ?><a href="<?=e(module_url('knowledgebase',['topic'=>$topic,'reference'=>1]))?>"><?= tr_html('Vysvětlit: {nazev} →', ['nazev' => e($supportTitle)]) ?></a><?php endforeach; ?>
        <a href="<?=e(module_url('visual_lab',['lesson'=>$lessonNo]))?>"><?= e(tr('Ukázat na modelu →')) ?></a>
        <a href="?view=study_loop"><?= e(tr('Procvičit slabé místo →')) ?></a>
      </div>
    </section>
    <?php endif; ?>

    <section class="guided-lesson-map" aria-label="<?= e(tr('Postup lekcí')) ?>">
      <header><div><span><?= e(tr('Tvůj postup')) ?></span><strong><?= e($allComplete?tr('Všechny kroky jsou hotové'):tr('Neřeš dopředu. Jen ať víš, co navazuje.')) ?></strong></div><button type="button" data-guided-show-steps><?= e($allComplete?tr('Zobrazit kroky'):tr('Zobrazit všechny kroky')) ?></button></header>
      <div class="guided-lesson-map-track">
        <?php foreach($steps as $i=>$step): if(!is_array($step))continue;$sid=(string)($step['id']??'');$done=!empty($progress[$sid]);$isCurrent=!$done&&$currentIndex===$i; ?>
          <div class="<?= $done?'done':($isCurrent?'current':'locked') ?>"><i><?= $done?'✓':($i+1) ?></i><span<?= edu_content_lang_attr() ?>><?=e((string)($step['title']??tr('Krok {n}', ['n' => $i+1])))?></span></div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="next-lesson-shell guided-next-lesson <?= $allComplete?'all-complete':'' ?>" id="lesson-work" data-next-lesson data-step-action="<?= e($stepAction) ?>" data-lesson-id="<?= e($lessonId) ?>">
      <div class="lesson-stepper" aria-label="<?= e(tr('Kroky lekce')) ?>">
      <?php foreach($steps as $i=>$step): if(!is_array($step)) continue; $sid=(string)($step['id']??''); $done=!empty($progress[$sid]); $prevDone=$i===0 || !empty($progress[(string)($steps[$i-1]['id']??'')]); $locked=!$done && !$prevDone; $isCurrent=!$done&&!$locked; ?>
        <article id="<?= $isCurrent?'guided-current-step':'' ?>" class="next-step-card <?= $done?'done':($locked?'locked':'current') ?>" data-next-step="<?= e($sid) ?>" data-kind="<?= e((string)($step['kind']??'manual')) ?>">
          <div class="next-step-index"><?= $done?'✓':str_pad((string)($i+1),2,'0',STR_PAD_LEFT) ?></div>
          <div class="next-step-content"><header><div><span><?= e((string)($step['time']??'')) ?></span><h2<?= edu_content_lang_attr() ?>><?= e((string)($step['title']??'')) ?></h2></div><small><?= e($locked?tr('později'):($done?tr('splněno'):tr('teď'))) ?></small></header>
          <?php if($locked): ?><p class="locked-copy"><?= e(tr('Tento krok se otevře automaticky, až dokončíš aktuální úkol.')) ?></p><?php elseif($done): ?><div class="completed-step-summary"><span>✓</span><p><?= e(tr('Splněno. Není potřeba se sem vracet, pokud si krok nechceš připomenout.')) ?></p></div><?php else: ?>
            <?php if(!empty($step['intro'])):?><p class="step-intro"<?= edu_content_lang_attr() ?>><?= e((string)$step['intro']) ?></p><?php endif; ?>
            <?php if(!empty($step['demo']['lines'])):?><div class="evidence-block"><strong><?= e((string)($step['demo']['label']??tr('Ukázka'))) ?></strong><pre<?= edu_content_lang_attr() ?>><?php foreach($step['demo']['lines'] as $line) echo e((string)$line)."\n"; ?></pre></div><?php endif; ?>
            <?php if(!empty($step['knowledge'])):?><details class="guided-step-support"><summary><?= e(tr('Potřebuji vysvětlení k tomuto kroku')) ?></summary><div class="step-kb-links"><?php foreach($step['knowledge'] as $topic): $topic=(string)$topic; $a=$module['knowledgebase'][$topic]??null; if(!is_array($a)) continue; ?><a href="<?= e(module_url('knowledgebase',['topic'=>$topic,'reference'=>1])) ?>"<?= edu_content_lang_attr() ?>>↗ <?= e((string)($a['title']??$topic)) ?></a><?php endforeach; ?></div></details><?php endif; ?>
            <?php if(!empty($step['question']) && is_array($step['options']??null)): $order=shuffled_keys($step['options']); $labels=balanced_quiz_option_labels($step['options'],$step['correct']??-1,subject_family($module)); ?><div class="next-quiz"><p<?= edu_content_lang_attr() ?>><strong><?= e((string)$step['question']) ?></strong></p><div class="assessment-answer-grid compact-answer-grid"><?php foreach($order as $display=>$key): ?><button type="button" class="assessment-answer" data-next-answer="<?= (int)$key ?>"><span><?= chr(65+$display) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)$labels[$key]) ?></strong></button><?php endforeach; ?></div></div><?php endif; ?>
            <?php if(!empty($step['tasks'])):?><div class="step-task-list"><?php foreach($step['tasks'] as $task): ?><label><input type="checkbox" data-next-task><span<?= edu_content_lang_attr() ?>><?= e((string)$task) ?></span></label><?php endforeach; ?></div><?php endif; ?>
            <div class="next-step-feedback" data-next-feedback hidden></div>
            <div class="auto-step-footer" data-auto-submit-status><span>✦</span><div><strong><?= e((($step['kind']??'')==='quiz') ? tr('Vyber jednu odpověď') : tr('Splň body shora dolů')) ?></strong><small><?= e((($step['kind']??'')==='quiz') ? tr('Nic dalšího nepotvrzuješ. Odpověď se vyhodnotí sama.') : tr('Jakmile splníš poslední bod, krok se uloží a další se odemkne.')) ?></small></div><em><?= (int)($step['xp']??20) ?> XP</em></div>
          <?php endif; ?></div>
        </article>
      <?php endforeach; ?>
      </div>
    </section>

    <details class="guided-support-library" data-guided-support>
      <summary><span><i>?</i><strong><?= e(tr('Potřebuji více vysvětlení, ukázku nebo materiály')) ?></strong><small><?= e(tr('Volitelné. Otevři jen když se u aktuálního kroku zasekneš.')) ?></small></span><b><?= e(tr('Otevřít podporu')) ?></b></summary>
      <div class="guided-support-body">
        <?php render_lesson_visual_overview($lesson, $classId, $module); ?>
        <?php render_v42_lesson_companion($classId,$lesson,$module,$GLOBALS['learningResources']??[],adaptive_student_key($classId)); ?>
        <section class="panel compact-panel v48-lesson-entry"><div class="section-heading compact-heading"><div><div class="eyebrow"><?= e(tr('Zkusit jinak')) ?></div><h2><?= e(tr('Vizualizace a praktické experimenty')) ?></h2><p><?= e(tr('Otevři jen pokud ti samotný krok nestačí k pochopení.')) ?></p></div><div class="v50-lesson-actions"><a class="btn primary" href="<?=e(module_url('visual_lab',['lesson'=>$lessonNo]))?>">Visual Lab →</a><?php if(in_array($classId,['class_3a','class_4a'],true)):?><a class="btn secondary" href="<?=e(module_url('hands_on',['lesson'=>$lessonNo]))?>">Linux Lab →</a><?php endif;?></div></div></section>
        <?php cv43_render_lab($classId,$lesson,$module,adaptive_student_key($classId),false); ?>
        <?php if (!empty($lesson['schedule'])): ?><section class="panel compact-panel"><div class="section-heading compact-heading"><div><div class="eyebrow"><?= e(tr('Plán lekce')) ?></div><h2><?= e(tr('Jak je blok poskládaný')) ?></h2></div></div><div class="timeline-grid compact-timeline"><?php foreach ($lesson['schedule'] as $segment): ?><div class="timeline-item"<?= edu_content_lang_attr() ?>><strong><?= e((string)($segment['time'] ?? '')) ?></strong><span><?= e((string)($segment['title'] ?? '')) ?></span><?php if(!empty($segment['text'])):?><small><?= e((string)$segment['text']) ?></small><?php endif;?></div><?php endforeach; ?></div></section><?php endif; ?>
        <?php if (!empty($lesson['knowledge'])): ?><section class="knowledge-dock"><div><div class="eyebrow"><?= e(tr('Materiály')) ?></div><strong><?= e(tr('Vysvětlení k této lekci')) ?></strong><p><?= e(tr('Nemusíš je číst všechny. Otevři jen téma, které právě potřebuješ.')) ?></p></div><div class="knowledge-dock-links"><?php foreach (($lesson['knowledge'] ?? []) as $topic): $topic=(string)$topic; $article=$module['knowledgebase'][$topic]??null; if(!is_array($article)) continue; $done=!empty(learning_kb_progress($classId,$topic)['complete']); ?><a class="<?= $done?'done':'' ?>" href="<?= e(module_url('knowledgebase',['topic'=>$topic,'reference'=>1])) ?>"<?= edu_content_lang_attr() ?>><span><?= $done?'✓':'○' ?></span><?= e((string)($article['title']??$topic)) ?></a><?php endforeach; ?></div></section><?php endif; ?>
        <?php render_learning_resources(learning_resource_cards($classId, is_array($lesson['knowledge'] ?? null) ? $lesson['knowledge'] : []), tr('Jiné vysvětlení stejného principu')); ?>
      </div>
    </details>
    <div class="guided-page-bottom"><a href="?view=course"><?= e(tr('← Kurz')) ?></a><span><?= e(tr('Nemusíš nic dalšího hledat. Po dokončení aktuálního kroku se další odemkne automaticky.')) ?></span></div>
    <?php render_footer();
}
