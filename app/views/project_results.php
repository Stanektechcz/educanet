<?php

declare(strict_types=1);

/**
 * ?view=project_result a ?view=project_results.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'project_result') {
    guarded_study_redirect();
    $studentName=(string)($_SESSION['student_label']??(auth_user()['name']??'Student'));
    $results=project_student_results((string)$classId,$studentName);
    $recordId=is_string($_GET['record']??null)?$_GET['record']:'';
    $item=null;
    foreach($results as $candidate){ if(is_array($candidate) && (string)($candidate['record']['id']??'')===$recordId){$item=$candidate;break;} }
    if(!is_array($item)){ $_SESSION['flash']=tr('Hodnocení nebylo nalezeno nebo zatím není publikované.'); redirect_to('?view=project_results'); }
    $record=$item['record']; $project=$item['project']; $returned=(string)($record['status']??'')==='returned';
    $mlStudentKey=adaptive_student_key((string)$classId);$mlNarrative=ml_portfolio_narrative_get((string)$classId,$mlStudentKey,(string)$record['id']);
    render_header(tr('Výsledek · {title}', ['title' => (string)$project['title']]),$module,true);
    ?>
    <section class="student-result-detail-head">
      <a class="student-result-back" href="?view=project_results">← <?= e(tr('Všechna projektová hodnocení')) ?></a><a class="btn secondary small" href="<?=e(v505_task_url('result',['record'=>(string)$record['id']], module_url('project_result',['record'=>(string)$record['id']])))?>"><?= e(tr('Projít feedback')) ?></a>
      <div class="student-result-detail-title"><div><span class="result-type"><?= ((string)$record['target_type']==='group'?e(tr('Skupinový projekt')):e(tr('Individuální projekt'))) ?><?= $item['group']?' · '.edu_cs((string)$item['group']['name']):'' ?></span><h1<?= edu_content_lang_attr() ?>><?= e((string)$project['title']) ?></h1><p<?= edu_content_lang_attr() ?>><?= e((string)$project['summary']) ?></p></div><div class="result-grade prominent"><span><?= $returned?e(tr('K dopracování')):e(tr('Známka')) ?></span><strong><?= $returned?'↻':e((string)$item['grade']) ?></strong><small><?= e(tr('{points} / {max} bodů', ['points' => (int)$item['points'], 'max' => (int)$item['max_points']])) ?><?php if((int)($item['points_delta']??0)!==0): ?> · <?= e(tr('individuální korekce {delta}', ['delta' => ((int)$item['points_delta']>0?'+':'').(int)$item['points_delta']])) ?><?php endif; ?></small></div></div>
    </section>
    <?php if($returned): ?><div class="student-return-banner"><i>↻</i><div><strong><?= e(tr('Projekt je vrácený k dopracování.')) ?></strong><span><?= e(tr('Projdi si další krok a komentář učitele. Po úpravě může vzniknout nová verze hodnocení.')) ?></span></div></div><?php endif; ?>
    <section class="student-project-result detail">
      <div class="student-rubric-view detail-rubric"<?= edu_content_lang_attr() ?>><?php foreach((array)$project['rubric'] as $criterion): $cid=(string)$criterion['id'];$score=(int)($record['rubric_scores'][$cid]??0);$max=(int)$criterion['max']; ?><div><span><strong><?= e((string)$criterion['title']) ?></strong><small><?= e((string)$criterion['description']) ?></small></span><b><?= $score ?>/<?= $max ?></b><i><em style="width:<?= (int)round($score/max(1,$max)*100) ?>%"></em></i></div><?php endforeach; ?></div>
      <div class="student-feedback-grid detail-feedback"><div><span><?= e(tr('Co se povedlo')) ?></span><p><?= trim((string)($record['strengths']??''))!==''?edu_cs((string)$record['strengths']):e(tr('Učitel zatím nepřidal samostatné shrnutí silných stránek.')) ?></p></div><div><span><?= e(tr('Další krok')) ?></span><p><?= trim((string)($record['next_step']??''))!==''?edu_cs((string)$record['next_step']):e(tr('Pokračuj podle rubriky a doporučení v komentáři.')) ?></p></div></div>
      <?php if(trim((string)($record['teacher_comment']??''))!=='' || trim((string)$item['personal_comment'])!==''): ?><div class="student-teacher-comment"><strong><?= e(tr('Komentář učitele')) ?></strong><?php if(trim((string)($record['teacher_comment']??''))!==''): ?><p<?= edu_content_lang_attr() ?>><?= e((string)$record['teacher_comment']) ?></p><?php endif; ?><?php if(trim((string)$item['personal_comment'])!==''): ?><div class="personal-note"<?= edu_content_lang_attr() ?>><span><?= e(tr('Jen pro tebe')) ?></span><?= e((string)$item['personal_comment']) ?></div><?php endif; ?></div><?php endif; ?>
      <div class="student-result-meta"><span><?= e((string)(($record['status']??'')==='returned'?tr('Vráceno k dopracování'):tr('Publikováno'))) ?></span><span><?= e(tr('Verze {n}', ['n' => (int)($record['version']??1)])) ?></span><time><?= e(date('d.m.Y H:i',strtotime((string)($record['updated_at']??'now')))) ?></time></div>
    </section>
    <details class="ml-card ml-portfolio-story"><summary><span>Portfolio narrative</span><strong><?=empty($mlNarrative)?e(tr('Doplň příběh práce')):e(tr('Příběh uložený'))?></strong></summary><form method="post" class="ml-inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="ml_portfolio_narrative"><input type="hidden" name="record_id" value="<?=e((string)$record['id'])?>"><label><?=e(tr('Problém'))?><textarea name="problem" rows="2" placeholder="<?=e(tr('Co jsme skutečně řešili?'))?>"><?=e((string)($mlNarrative['problem']??''))?></textarea></label><label><?=e(tr('Moje role'))?><textarea name="role" rows="2"><?=e((string)($mlNarrative['role']??''))?></textarea></label><label><?=e(tr('Klíčové rozhodnutí'))?><textarea name="decision" rows="2"><?=e((string)($mlNarrative['decision']??''))?></textarea></label><label><?=e(tr('Co se nepovedlo'))?><textarea name="failed" rows="2"><?=e((string)($mlNarrative['failed']??''))?></textarea></label><label><?=e(tr('Iterace'))?><textarea name="iteration" rows="2" placeholder="<?=e(tr('Co jsme změnili po feedbacku/testu?'))?>"><?=e((string)($mlNarrative['iteration']??''))?></textarea></label><label><?=e(tr('Co umím teď'))?><textarea name="now" rows="2"><?=e((string)($mlNarrative['now']??''))?></textarea></label><button class="btn secondary" type="submit"><?=e(tr('Uložit portfolio příběh'))?></button></form></details>
    <?php render_footer(); exit;
}

if ($view === 'project_results') {
    guarded_study_redirect();
    $studentName=(string)($_SESSION['student_label']??(auth_user()['name']??'Student'));
    $results=project_student_results((string)$classId,$studentName);
    $gradeValues=[]; foreach($results as $ri){ if((string)($ri['record']['status']??'')==='published')$gradeValues[]=(int)$ri['grade']; }
    $avg=$gradeValues?array_sum($gradeValues)/count($gradeValues):null;
    render_header(tr('Projektové výsledky'),$module);
    ?>
    <section class="student-results-hero"><div><div class="eyebrow"><?= e(tr('Výsledky · projekty')) ?></div><h1><?= e(tr('Tvoje projektové hodnocení')) ?></h1><p><?= e(tr('Každý projekt má vlastní detail s body, rubrikou a konkrétní zpětnou vazbou. Koncepty a soukromé poznámky učitele se ti nikdy nezobrazují.')) ?></p></div><div class="student-results-summary"><strong><?= count($results) ?></strong><span><?= e(tr('zveřejněných hodnocení')) ?></span><?php if($avg!==null): ?><small><?= e(tr('průměr {n}', ['n' => edu_number($avg,2)])) ?></small><?php endif; ?></div></section>
    <?php if(!$results): ?><section class="student-result-empty"><span>◎</span><h2><?= e(tr('Zatím tu není publikované hodnocení.')) ?></h2><p><?= e(tr('Jakmile učitel zveřejní individuální nebo týmový projekt, objeví se tady automaticky.')) ?></p><a class="btn secondary" href="?view=dashboard">← <?= e(tr('Zpět na přehled')) ?></a></section><?php endif; ?>
    <section class="student-results-card-grid">
    <?php foreach($results as $item): $record=$item['record'];$project=$item['project'];$returned=(string)($record['status']??'')==='returned'; $percent=(int)round((int)$item['points']/max(1,(int)$item['max_points'])*100); ?>
      <a class="student-result-card<?= $returned?' returned':'' ?>" href="<?=e(v505_task_url('result',['record'=>(string)$record['id']], '?view=project_results'))?>">
        <div class="student-result-card-top"><span><?= ((string)$record['target_type']==='group'?e(tr('Týmový projekt')):e(tr('Individuální projekt'))) ?></span><b class="<?= $returned?'returned':'' ?>"><?= $returned?'↻':e((string)$item['grade']) ?></b></div>
        <h2<?= edu_content_lang_attr() ?>><?= e((string)$project['title']) ?></h2><p<?= edu_content_lang_attr() ?>><?= e((string)$project['summary']) ?></p>
        <div class="student-result-card-score"><i><em style="width:<?= $percent ?>%"></em></i><span><?= e(tr('{points}/{max} bodů · {percent} %', ['points' => (int)$item['points'], 'max' => (int)$item['max_points'], 'percent' => $percent])) ?></span></div>
        <div class="student-result-card-foot"><span><?= $returned?e(tr('Dopracovat')):e(tr('Otevřít detail')) ?> →</span><time><?= e(date('d.m.Y',strtotime((string)($record['updated_at']??'now')))) ?></time></div>
      </a>
    <?php endforeach; ?>
    </section>
    <?php render_footer(); exit;
}
