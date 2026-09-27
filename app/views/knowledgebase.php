<?php

declare(strict_types=1);

/**
 * ?view=knowledgebase&classic=1 s tématem – klasická knowledgebase.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'knowledgebase') {
    guarded_study_redirect();
    $referenceMode = isset($_GET['reference']) && (string)$_GET['reference'] === '1';
    $returnToNext = isset($_GET['return']) && (string)$_GET['return'] === 'next';
    $allowedKbClasses = allowed_subject_class_ids((string)$classId, $modules);
    $requestedKbClass = isset($_GET['kb_class']) && is_string($_GET['kb_class']) ? $_GET['kb_class'] : (string)$classId;
    $kbClassId = isset($modules[$requestedKbClass]) && in_array($requestedKbClass, $allowedKbClasses, true) ? $requestedKbClass : (string)$classId;
    if ($kbClassId !== (string)$classId) {
        $referenceMode = true;
    }
    $kbModule = $modules[$kbClassId];
    $requestedTopic = isset($_GET['topic']) && is_string($_GET['topic']) ? $_GET['topic'] : '';
    if ($requestedTopic !== '' && isset($kbModule['knowledgebase'][$requestedTopic])) {
        $lessonParams = ['topic'=>$requestedTopic];
        if ($referenceMode) $lessonParams['reference'] = 1;
        if ($kbClassId !== (string)$classId) $lessonParams['kb_class'] = $kbClassId;
        if ($returnToNext) $lessonParams['return'] = 'next';
        redirect_to(module_url('kb_lesson', $lessonParams));
    }
    render_header('Knowledgebase', $module);

    $kbKeys = array_keys($kbModule['knowledgebase']);
    $topic = '';
    $tourMap = is_array($knowledgeTours[$kbClassId] ?? null) ? $knowledgeTours[$kbClassId] : [];
    $simulationMap = is_array($simulations[$kbClassId] ?? null) ? $simulations[$kbClassId] : [];
    $firstIncompleteTopic = learning_first_incomplete_topic($kbClassId, $kbModule, $simulationMap);
    $firstIncompleteIndex = $firstIncompleteTopic !== null ? array_search($firstIncompleteTopic, $kbKeys, true) : false;

    $requiredTopicsFor = static function (string $targetClass) use ($modules, $nextLessons, $extendedLessons): array {
        $required = [];
        if (in_array($targetClass, ['class_1a', 'class_2a'], true)) {
            foreach (['hierarchy','composition','contrast'] as $key) { $required[$key] = true; }
        }
        $practice = $modules[$targetClass]['practice']['tasks'] ?? [];
        if (is_array($practice)) {
            foreach ($practice as $task) {
                if (is_array($task) && !empty($task['kb'])) { $required[(string)$task['kb']] = true; }
            }
        }
        $lesson = $nextLessons[$targetClass] ?? null;
        if (is_array($lesson)) {
            foreach (($lesson['knowledge'] ?? []) as $key) { $required[(string)$key] = true; }
            foreach (($lesson['steps'] ?? []) as $step) {
                if (!is_array($step)) continue;
                foreach (($step['knowledge'] ?? []) as $key) { $required[(string)$key] = true; }
            }
        }
        foreach (($extendedLessons[$targetClass] ?? []) as $lesson) {
            if (!is_array($lesson)) continue;
            foreach (($lesson['knowledge'] ?? []) as $key) $required[(string)$key] = true;
            foreach (($lesson['steps'] ?? []) as $step) {
                if (!is_array($step)) continue;
                foreach (($step['knowledge'] ?? []) as $key) $required[(string)$key] = true;
            }
        }
        return $required;
    };
    $topicGroup = static function (string $key, array $article, string $targetClass): string {
        $hay = strtolower($key . ' ' . (string)($article['title'] ?? '') . ' ' . (string)($article['summary'] ?? ''));
        $isGraphics = in_array($targetClass, ['class_1a','class_2a'], true);
        if ($isGraphics) {
            if (preg_match('/typ|spacing|grid|kompoz|hierarch|layout|cta|component|ui/u', $hay)) return trm('Typografie & layout');
            if (preg_match('/barv|kontrast|rgb|cmyk|palet/u', $hay)) return trm('Barva & kontrast');
            if (preg_match('/foto|image|crop|raster|vektor|export|preflight|icon/u', $hay)) return trm('Obraz & export');
            if (preg_match('/brand|systém|portfolio|prezent|adapt|motion|microinteraction/u', $hay)) return trm('Workflow & prezentace');
            return trm('Základy designu');
        }
        if (preg_match('/dns|dhcp/u', $hay)) return trm('DNS & DHCP');
        if (preg_match('/ip|ipv6|cidr|subnet/u', $hay)) return trm('IP & adresace');
        if (preg_match('/route|routing|gateway|vlan|arp|nat/u', $hay)) return trm('Routing & segmentace');
        if (preg_match('/ssh|sftp|permission|firewall|key/u', $hay)) return trm('Bezpečnost & přístup');
        if (preg_match('/https|http|tls|proxy|nginx|web|port|tcp|load|balanc/u', $hay)) return trm('Web & služby');
        if (preg_match('/diagn|trouble|log|monitor|change|canary|backup|restore|observ/u', $hay)) return trm('Troubleshooting & provoz');
        return trm('Síťové základy');
    };

    $learningGoalsFor = static function (array $article, array $tour): array {
        $source = is_array($tour['goals'] ?? null) ? $tour['goals'] : (is_array($tour['steps'] ?? null) ? $tour['steps'] : []);
        $goals = [];
        foreach ($source as $goal) {
            $goal = trim((string)$goal);
            if ($goal === '') continue;
            $goals[] = $goal;
            if (count($goals) >= 3) break;
        }
        if (!$goals && !empty($article['summary'])) $goals[] = (string)$article['summary'];
        return $goals;
    };

    $catalog = [];
    $topicGroups = [];
    foreach ($modules as $catalogClassId => $catalogModule) {
        if (!in_array((string)$catalogClassId, $allowedKbClasses, true)) continue;
        if (empty($catalogModule['knowledgebase']) || !is_array($catalogModule['knowledgebase'])) continue;
        $catalogTourMap = is_array($knowledgeTours[$catalogClassId] ?? null) ? $knowledgeTours[$catalogClassId] : [];
        $catalogSimulationMap = is_array($simulations[$catalogClassId] ?? null) ? $simulations[$catalogClassId] : [];
        $catalogRequired = $requiredTopicsFor((string)$catalogClassId);
        $catalogKeys = array_keys($catalogModule['knowledgebase']);
        $catalogFirstIncomplete = learning_first_incomplete_topic((string)$catalogClassId, $catalogModule, $catalogSimulationMap);
        $catalogFirstIncompleteIndex = $catalogFirstIncomplete !== null ? array_search($catalogFirstIncomplete, $catalogKeys, true) : false;
        foreach ($catalogModule['knowledgebase'] as $key => $article) {
            $key = (string)$key;
            $idx = array_search($key, $catalogKeys, true);
            $idx = $idx === false ? 0 : (int)$idx;
            $hasSimulation = isset($catalogSimulationMap[$key]) && is_array($catalogSimulationMap[$key]);
            $complete = learning_kb_complete((string)$catalogClassId, $key, $hasSimulation);
            $progress = learning_kb_progress((string)$catalogClassId, $key);
            $started = !$complete && (bool)array_filter($progress, static fn($value, $name) => $name !== 'complete' && !empty($value), ARRAY_FILTER_USE_BOTH);
            $required = isset($catalogRequired[$key]) && $catalogClassId === (string)$classId;
            $group = $topicGroup($key, $article, (string)$catalogClassId);
            $topicGroups[$group] = true;
            $unlocked = $catalogClassId !== (string)$classId
                ? true
                : ($referenceMode || $catalogFirstIncompleteIndex === false || $idx <= (int)$catalogFirstIncompleteIndex);
            $tour = is_array($catalogTourMap[$key] ?? null) ? $catalogTourMap[$key] : [];
            $previousKey = $idx > 0 ? (string)$catalogKeys[$idx - 1] : '';
            $nextKey = $idx < count($catalogKeys) - 1 ? (string)$catalogKeys[$idx + 1] : '';
            $dependencyTitle = $previousKey !== '' ? (string)($catalogModule['knowledgebase'][$previousKey]['title'] ?? $previousKey) : tr('Startovní materiál');
            $nextTitle = $nextKey !== '' ? (string)($catalogModule['knowledgebase'][$nextKey]['title'] ?? $nextKey) : tr('Navazující praktická práce');
            $lockReason = '';
            if (!$unlocked) {
                if ($catalogClassId !== (string)$classId) {
                    $lockReason = tr('Tento materiál není součástí tvé povinné cesty.');
                } elseif ($catalogFirstIncomplete !== null) {
                    $lockReason = tr('Nejdřív dokonči: {nazev}', ['nazev' => (string)($catalogModule['knowledgebase'][$catalogFirstIncomplete]['title'] ?? $catalogFirstIncomplete)]);
                }
            }
            $catalog[] = [
                'class_id' => (string)$catalogClassId,
                'class_name' => (string)($catalogModule['name'] ?? $catalogClassId),
                'subject' => (string)($catalogModule['subject'] ?? ''),
                'key' => $key,
                'article' => $article,
                'tour' => $tour,
                'group' => $group,
                'required' => $required,
                'complete' => $complete,
                'started' => $started,
                'has_simulation' => $hasSimulation,
                'unlocked' => $unlocked,
                'dependency_title' => $dependencyTitle,
                'next_title' => $nextTitle,
                'goals' => $learningGoalsFor($article, $tour),
                'lock_reason' => $lockReason,
            ];
        }
    }
    ksort($topicGroups, SORT_NATURAL | SORT_FLAG_CASE);
    ?>
    <section class="hero compact kb-hero v5077-kb-hero">
        <div class="eyebrow"><?= e(tr('Materiály')) ?></div>
        <h1><?= e(tr('Najdi materiál.')) ?></h1>
        <p><?= e(tr('Hledej podle názvu nebo pojmu. Další možnosti otevři jen když je potřebuješ.')) ?></p>
        <div class="kb-mode-switch"><a class="<?= !$referenceMode && $kbClassId === (string)$classId ? 'active' : '' ?>" href="?view=knowledgebase"><?= e(tr('Moje cesta')) ?></a><a class="<?= $referenceMode ? 'active' : '' ?>" href="?view=knowledgebase&reference=1<?= $returnToNext ? '&return=next' : '' ?>"><?= e(tr('Knihovna')) ?></a><?php if ($returnToNext): ?><a class="back-to-task" href="?view=next_lesson"><?= e(tr('Zpět k úkolu')) ?></a><?php endif; ?></div>
        <div class="kb-overall-progress" data-kb-overall data-class="<?= e($kbClassId) ?>" data-total="<?= count($kbKeys) ?>">
            <?php $kbDoneCount=0; foreach ($kbKeys as $progressTopic) { $sim=isset($simulationMap[$progressTopic])&&is_array($simulationMap[$progressTopic]); if(learning_kb_complete($kbClassId,(string)$progressTopic,$sim)) $kbDoneCount++; } ?>
            <div><span><?= tr_html('Postup · {trida}', ['trida' => e((string)$kbModule['name'])]) ?></span><strong><b data-kb-done><?= $kbDoneCount ?></b> / <?= count($kbKeys) ?> <?= e(tr('témat')) ?></strong></div>
            <div class="progress"><span data-kb-progress-bar style="width:<?= count($kbKeys) ? (int)round(($kbDoneCount/count($kbKeys))*100) : 0 ?>%"></span></div>
        </div>
    </section>

    <details class="kb-subject-overview v5077-other-classes" aria-label="<?= e(tr('Materiály pro předmět')) ?>">
        <summary><?= e(tr('Další ročníky')) ?></summary>
        <div class="kb-browser-head"><div><h2><?= e(tr('Materiály dalších ročníků')) ?></h2></div><p><?= e(tr('Pro opakování nebo rozšíření.')) ?></p></div>
        <div class="kb-subject-class-grid">
            <?php foreach ($allowedKbClasses as $scopeClass): $scopeModule=$modules[$scopeClass]; $scopeKeys=array_keys((array)($scopeModule['knowledgebase']??[])); $scopeFirst=(string)($scopeKeys[0]??''); $scopeNext=$nextLessons[$scopeClass]??null; ?>
                <article class="kb-subject-class-card<?= $scopeClass===(string)$classId?' current':'' ?>">
                    <div<?= edu_content_lang_attr() ?>><span><?= e((string)$scopeModule['name']) ?></span><strong><?= e((string)$scopeModule['subject']) ?></strong></div>
                    <p><?= tr_html('{n} knowledge materiálů', ['n' => count($scopeKeys)]) ?><?php if(is_array($scopeNext)): ?><?= tr_html(' · navazující blok {nazev}', ['nazev' => e((string)($scopeNext['title']??tr('další 2 h')))]) ?><?php endif; ?></p>
                    <?php if($scopeFirst!==''): ?><a href="<?= e(module_url('knowledgebase',['topic'=>$scopeFirst,'reference'=>1,'kb_class'=>$scopeClass])) ?>"><?= e(tr('Otevřít materiály →')) ?></a><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </details>

    <section class="kb-topic-browser" aria-label="<?= e(tr('Katalog knowledgebase')) ?>" data-kb-catalog data-current-class="<?= e((string)$classId) ?>">
        <div class="kb-browser-head v5077-kb-browser-head"><div><h2><?= e(tr('Materiály')) ?></h2></div></div>
        <div class="kb-catalog-tools v5077-kb-tools">
            <label class="kb-search-field"><span><?= e(tr('Hledat')) ?></span><input type="search" placeholder="<?= e(tr('DNS, kontrast, routing, export…')) ?>" autocomplete="off" data-kb-search></label>
            <details class="v5077-kb-filters">
                <summary><?= e(tr('Filtry')) ?></summary>
                <div class="v5077-filter-grid">
                    <label><span><?= e(tr('Třída')) ?></span><select data-kb-filter="class"><option value="all" <?= $referenceMode?'selected':'' ?>><?= e(tr('Všechny dostupné ročníky')) ?></option><?php foreach($modules as $filterClassId=>$filterModule): if(!in_array((string)$filterClassId,$allowedKbClasses,true) || empty($filterModule['knowledgebase'])) continue; ?><option value="<?= e((string)$filterClassId) ?>" <?= (!$referenceMode && $filterClassId===$kbClassId)?'selected':'' ?><?= edu_content_lang_attr() ?>><?= e((string)$filterModule['name']) ?> · <?= e((string)$filterModule['subject']) ?></option><?php endforeach; ?></select></label>
                    <label><span><?= e(tr('Téma')) ?></span><select data-kb-filter="topic"><option value="all"><?= e(tr('Všechna témata')) ?></option><?php foreach(array_keys($topicGroups) as $group): ?><option value="<?= e($group) ?>"><?= e(tr($group)) ?></option><?php endforeach; ?></select></label>
                    <label><span><?= e(tr('Materiál')) ?></span><select data-kb-filter="priority"><option value="all"><?= e(tr('Všechny')) ?></option><option value="required"><?= e(tr('Povinné')) ?></option><option value="recommended"><?= e(tr('Doporučené')) ?></option></select></label>
                    <label><span><?= e(tr('Stav')) ?></span><select data-kb-filter="status"><option value="all"><?= e(tr('Všechny stavy')) ?></option><option value="complete"><?= e(tr('Dokončené')) ?></option><option value="started"><?= e(tr('Rozpracované')) ?></option><option value="not_started"><?= e(tr('Nezačaté')) ?></option></select></label>
                    <button type="button" class="text-button" data-kb-clear><?= e(tr('Vymazat filtry')) ?></button>
                </div>
            </details>
        </div>
        <div class="kb-filter-summary v5077-kb-summary"><div><strong data-kb-result-count><?= count($catalog) ?></strong><span><?= e(tr(' materiálů')) ?></span></div></div>
        <div class="kb-topic-grid" data-kb-results>
            <?php foreach ($catalog as $item):
                $article=$item['article']; $key=$item['key']; $targetClass=$item['class_id']; $t=$item['tour']; $groupLabel=$item['group'];
                $isActive=$targetClass===$kbClassId && $topic===$key;
                $linkAllowed=(bool)$item['unlocked'];
                $params=['topic'=>$key];
                if ($targetClass !== (string)$classId || $referenceMode) { $params['reference']=1; }
                if ($targetClass !== (string)$classId) { $params['kb_class']=$targetClass; }
                if ($returnToNext && $targetClass === (string)$classId) { $params['return']='next'; }
                $continueUrl = module_url('kb_lesson',$params);
                $searchText=(string)($article['title']??'').' '.(string)($article['summary']??'').' '.$item['group'].' '.$item['class_name'].' '.$item['subject'].' '.$key.' '.implode(' ', $item['goals']);
                $status=$item['complete']?'complete':($item['started']?'started':'not_started');
            ?>
                <article class="kb-topic-card kb-material-card<?= $isActive?' active':'' ?><?= $item['complete']?' completed':'' ?><?= !$linkAllowed?' locked':'' ?>" data-kb-topic-card="<?= e($key) ?>" data-kb-card-class="<?= e($targetClass) ?>" data-kb-card-topic="<?= e($item['group']) ?>" data-kb-card-priority="<?= $item['required']?'required':'recommended' ?>" data-kb-card-status="<?= e($status) ?>" data-kb-search-text="<?= e($searchText) ?>" <?= $targetClass===$kbClassId?'data-kb-progress-card="1"':'' ?>>
                    <div class="kb-topic-top"><span><?= e($item['class_name']) ?> · <?= e(tr($groupLabel)) ?></span><i data-kb-topic-status><?= $item['complete']?'✓':($linkAllowed?'○':'⌁') ?></i></div>
                    <div class="kb-card-badges"><span class="kb-priority <?= $item['required']?'required':'recommended' ?>"><?= e($item['required']?tr('Povinné'):tr('Doporučené')) ?></span><?php if($item['complete']): ?><span class="kb-priority completed"><?= e(tr('✓ Dokončeno')) ?></span><?php elseif($item['started']): ?><span class="kb-priority started"><?= e(tr('Rozpracováno')) ?></span><?php endif; ?><?php if($item['has_simulation']): ?><span class="kb-priority simulation"><?= e(tr('◉ Simulace')) ?></span><?php endif; ?></div>
                    <div class="kb-material-title"<?= edu_content_lang_attr() ?>><strong><?= e((string)$article['title']) ?></strong><p><?= e((string)$article['summary']) ?></p></div>
                    <div class="kb-material-facts">
                        <div><span><?= e(tr('Odhad času')) ?></span><strong><?= e((string)($t['time']??tr('5–10 min'))) ?></strong></div>
                        <div><span><?= e(tr('Úroveň')) ?></span><strong><?= e((string)($t['level']??tr('Základ'))) ?></strong></div>
                        <div class="wide"><span><?= e(tr('Navazuje na')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)$item['dependency_title']) ?></strong></div>
                    </div>
                    <div class="kb-material-goals"><span><?= e(tr('Cíle učení')) ?></span><ul<?= edu_content_lang_attr() ?>><?php foreach(array_slice($item['goals'],0,2) as $goal): ?><li><?= e((string)$goal) ?></li><?php endforeach; ?></ul></div>
                    <div class="kb-material-footer">
                        <small><?= tr_html('Potom: {nazev}', ['nazev' => edu_cs((string)$item['next_title'])]) ?></small>
                        <?php if($linkAllowed): ?><a class="btn primary kb-continue" href="<?= e($continueUrl) ?>"><?= e(tr('Pokračovat')) ?> <span>→</span></a><?php else: ?><div class="kb-lock-reason"><b><?= e(tr('⌁ Zamčeno')) ?></b><span><?= e((string)$item['lock_reason']) ?></span></div><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="kb-empty-state" data-kb-empty hidden><strong><?= e(tr('Nic jsme nenašli.')) ?></strong><p><?= e(tr('Zkus kratší výraz nebo zruš některý filtr.')) ?></p><button type="button" class="btn secondary" data-kb-clear><?= e(tr('Vymazat filtry')) ?></button></div>
    </section>


    <div class="button-row">
        <?php if ($returnToNext && $kbClassId === (string)$classId): ?><a class="btn primary" href="?view=next_lesson"><?= e(tr('← Zpět do navazující lekce')) ?></a><?php else: ?><a class="btn secondary" href="?view=dashboard"><?= e(tr('Zpět na přehled')) ?></a><?php endif; ?>
        <?php if (!empty($module['practice']) && !$returnToNext && $kbClassId === (string)$classId): ?><a class="btn secondary" href="?view=practice"><?= e(tr('Laboratoř')) ?></a><?php endif; ?>
    </div>
    <?php
    render_footer();
    exit;
}
