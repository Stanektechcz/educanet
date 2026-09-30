<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function social_role_label(string $role): string
{
    return [
        'flexible'=>tr('Flexibilní role'),'designer'=>tr('Designér'),'developer'=>tr('Vývojář'),'researcher'=>tr('Research / analýza'),
        'presenter'=>tr('Presenter'),'qa'=>tr('QA'),'leader'=>tr('Leader'),'tester'=>tr('QA'),'coordinator'=>tr('Leader'),'documentarian'=>tr('Research / dokumentace'),
    ][$role] ?? tr('Flexibilní role');
}

function social_team_status_label(string $status): string
{
    return ['available'=>tr('Hledám tým'),'ask_me'=>tr('Klidně se ozvi'),'full'=>tr('Tým už mám')][$status] ?? tr('Hledám tým');
}

function group_plan_human(array $sizes): string
{
    $counts=[]; foreach($sizes as $size){$size=(int)$size;$counts[$size]=($counts[$size]??0)+1;}
    ksort($counts); $parts=[]; foreach($counts as $size=>$count)$parts[]=tr('{count}× tým po {size}',['count'=>$count,'size'=>$size]);
    return implode(' + ',$parts);
}

function render_student_profile_view(string $classId, array $module, string $flash=''): void
{
    render_profile60_view($classId, $module, $flash);
}

/**
 * v60: Nastavení profilu (záložka Nastavení). Chování formuláře beze změny (akce save_student_profile,
 * CSRF, validace na serveru) – jen přehlednější rozvržení ve skupinách, počitadla znaků a lepivé tlačítko Uložit.
 * Kosmetika se nastavuje samostatnými formuláři (akce mkt60_cosmetic_set), proto stojí mimo hlavní formulář.
 */
function social_render_profile_editor(string $classId, string $target): void
{
    $profile = social_profile_get($classId, $target);
    echo '<section class="p60-settings" data-p60-settings><header class="p60-settings-head"><h2>' . e(tr('Nastavení profilu')) . '</h2>'
        . '<p>' . e(tr('Uprav, co o sobě ukážeš spolužákům. Profil je školní a třídní – nepřidávej telefon, adresu ani jiné citlivé údaje.')) . '</p></header>';
    echo '<form method="post" id="p60-profile-form" class="p60-form" data-p60-form data-dirty-text="' . e(tr('Máš neuložené změny.')) . '">'
        . '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="save_student_profile">';
    social_editor_group_about($profile);
    social_editor_group_skills($profile);
    social_editor_group_badges($classId, $profile);
    echo '</form>';
    social_editor_appearance($classId, $target);
    social_editor_privacy();
    echo '<div class="p60-savebar"><p class="p60-save-status" role="status" aria-live="polite" data-p60-status></p>'
        . '<button class="btn primary" type="submit" form="p60-profile-form">' . e(tr('Uložit profil')) . '</button></div></section>';
}

/** Jedno pole formuláře: popisek, nápověda, ovládací prvek a volitelné počitadlo znaků. */
function social_editor_field(string $id, string $label, string $hint, string $control, int $max = 0, int $len = 0): string
{
    $counter = $max > 0 ? '<span class="p60-count" data-p60-count-for="' . e($id) . '" data-max="' . $max . '">' . e(tr('{n} / {max} znaků', ['n' => $len, 'max' => $max])) . '</span>' : '';
    return '<div class="p60-field"><label for="' . e($id) . '">' . e($label) . '</label>' . $control
        . '<div class="p60-field-meta"><small id="' . e($id) . '-hint">' . e($hint) . '</small>' . $counter . '</div></div>';
}

function social_editor_group_about(array $profile): void
{
    $headline = (string)$profile['headline'];
    $bio = (string)$profile['bio'];
    echo '<fieldset class="p60-group"><legend>' . e(tr('O mně')) . '</legend>';
    echo social_editor_field('p60-f-headline', tr('Krátké motto / co teď dělám'), tr('Zobrazí se pod jménem v hlavičce profilu.'),
        '<input id="p60-f-headline" name="headline" maxlength="80" aria-describedby="p60-f-headline-hint" value="' . e($headline) . '" placeholder="' . e(tr('Např. baví mě motion design a prototypování')) . '">', 80, mb_strlen($headline));
    echo social_editor_field('p60-f-bio', tr('Krátké představení'), tr('Co tě baví a s čím můžeš pomoct týmu? Bez telefonu a adresy.'),
        '<textarea id="p60-f-bio" name="bio" maxlength="320" rows="4" aria-describedby="p60-f-bio-hint" placeholder="' . e(tr('Co tě baví, co se chceš naučit a s čím můžeš pomoct týmu?')) . '">' . e($bio) . '</textarea>', 320, mb_strlen($bio));
    echo '</fieldset>';
}

function social_editor_select(string $id, string $name, string $label, array $options, string $current): string
{
    $html = '<select id="' . e($id) . '" name="' . e($name) . '">';
    foreach ($options as $value => $text) {
        $html .= '<option value="' . e((string)$value) . '"' . ($current === (string)$value ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    return '<div class="p60-field"><label for="' . e($id) . '">' . e($label) . '</label>' . $html . '</select></div>';
}

function social_editor_group_skills(array $profile): void
{
    $roles = [];
    foreach (['flexible', 'leader', 'designer', 'researcher', 'developer', 'presenter', 'qa'] as $role) { $roles[$role] = social_role_label($role); }
    $statuses = [];
    foreach (['available', 'ask_me', 'full'] as $status) { $statuses[$status] = social_team_status_label($status); }
    $hint = tr('Odděl čárkou, nejvýše 8 položek.');
    echo '<fieldset class="p60-group"><legend>' . e(tr('Dovednosti a zájmy')) . '</legend><div class="p60-grid-2">';
    echo social_editor_field('p60-f-skills', tr('Dovednosti · odděl čárkou'), $hint,
        '<input id="p60-f-skills" name="skills" aria-describedby="p60-f-skills-hint" value="' . e(implode(', ', (array)$profile['skills'])) . '" placeholder="' . e(tr('Figma, CSS, prezentace')) . '">');
    echo social_editor_field('p60-f-interests', tr('Zájmy · odděl čárkou'), $hint,
        '<input id="p60-f-interests" name="interests" aria-describedby="p60-f-interests-hint" value="' . e(implode(', ', (array)$profile['interests'])) . '" placeholder="' . e(tr('UI, sítě, motion, fotografie')) . '">');
    echo social_editor_select('p60-f-role', 'preferred_role', tr('Preferovaná role v týmu'), $roles, (string)($profile['preferred_role'] ?? ''));
    echo social_editor_select('p60-f-status', 'team_status', tr('Stav pro týmové projekty'), $statuses, (string)($profile['team_status'] ?? ''));
    echo '</div></fieldset>';
}

/** Výběr vystavených odznaků (max 3) a ověřených skills (max 5) – karty s unikátním SVG odznaku. */
function social_editor_group_badges(string $classId, array $profile): void
{
    $badgeDefs = learning_badge_definitions();
    $featured = array_values(array_filter((array)$profile['featured_badges'], static fn($id): bool => is_string($id)));
    $earned = (array)(learning_profile($classId)['badges'] ?? []);
    $student = project_students_for_class($classId)[social_current_student_key($classId)] ?? ['label' => ''];
    $skillKey = skill_student_key_for_label($classId, (string)$student['label']);
    echo '<fieldset class="p60-group" data-p60-max="3"><legend>' . e(tr('Vystavit až 3 získané badge')) . '</legend>';
    if (!$earned) {
        echo '<p class="p60-empty">' . e(tr('První badge se odemkne až za významný milník — například level 10 nebo výjimečnou zkoušku.')) . '</p>';
    } else {
        echo '<div class="b60-picks">';
        foreach ($earned as $bid => $_) {
            if (!isset($badgeDefs[$bid])) continue;
            $b = (array)$badgeDefs[$bid];
            echo '<label class="b60-pick"><input type="checkbox" name="featured_badges[]" value="' . e((string)$bid) . '"' . (in_array($bid, $featured, true) ? ' checked' : '') . '>'
                . '<span class="b60-pick-card">' . badge60_svg((string)$bid, $b, true, 56, true) . '<strong>' . e((string)$b['title']) . '</strong></span></label>';
        }
        echo '</div>';
    }
    echo '</fieldset>';
    social_editor_group_skill_picker($classId, $profile, $skillKey);
}

function social_editor_group_skill_picker(string $classId, array $profile, string $skillKey): void
{
    $map = skill_progress_map($classId, $skillKey);
    $picked = (array)($profile['featured_skills'] ?? []);
    $rows = '';
    foreach (skill_relevant_skills($classId) as $skill) {
        $mastery = (float)(($map[(string)$skill['slug']] ?? [])['mastery_percent'] ?? 0);
        if ($mastery < 60) continue;
        $rows .= '<label class="b60-pick b60-pick-skill"><input type="checkbox" name="featured_skills[]" value="' . e((string)$skill['slug']) . '"' . (in_array((string)$skill['slug'], $picked, true) ? ' checked' : '') . '>'
            . '<span class="b60-pick-card"><b>' . edu_number($mastery, 0) . '%</b><strong>' . e((string)$skill['name']) . '</strong></span></label>';
    }
    echo '<fieldset class="p60-group" data-p60-max="5"><legend>' . e(tr('Vystavit až 5 ověřených skills')) . '</legend>';
    echo $rows !== '' ? '<div class="b60-picks">' . $rows . '</div>'
        : '<p class="p60-empty">' . e(tr('Jakmile u některé dovednosti dosáhneš alespoň Competent (60 %), můžeš ji vystavit v profilovém showcase.')) . '</p>';
    echo '</fieldset>';
}

/** Vzhled profilu: koupená kosmetika (rámeček/titulek) s náhledem; volba přes existující akci obchodu. */
function social_editor_appearance(string $classId, string $target): void
{
    echo '<section class="p60-group p60-appearance" aria-labelledby="p60-appearance-title"><h3 id="p60-appearance-title">' . e(tr('Vzhled profilu')) . '</h3>'
        . '<p class="p60-lead">' . e(tr('Rámeček a titulek z obchodu. Zapni ten, který se ti líbí.')) . '</p>';
    $owned = [];
    if (function_exists('mkt60_my_purchases') && function_exists('mkt60_item')) {
        foreach (mkt60_my_purchases($classId, $target) as $p) {
            $item = ($p['refunded'] || $p['type'] !== 'cosmetic') ? null : mkt60_item((string)$p['item_id']);
            if ($item !== null && in_array((string)($item['slot'] ?? ''), ['frame', 'title'], true)) { $owned[(string)$p['item_id']] = $item; }
        }
    }
    if ($owned === []) {
        echo '<p class="p60-empty">' . e(tr('Zatím nemáš žádnou kosmetiku.')) . ' <a href="?view=obchod">' . e(tr('Otevřít obchod →')) . '</a></p></section>';
        return;
    }
    $active = mkt60_cosmetics($classId, $target);
    $initial = u_substr((string)(project_students_for_class($classId)[$target]['label'] ?? '?'), 0, 1);
    echo '<ul class="p60-cosmetics">';
    foreach ($owned as $itemId => $item) {
        echo social_editor_cosmetic_row((string)$itemId, $item, in_array((string)$itemId, [(string)($active['frame'] ?? ''), (string)($active['title'] ?? '')], true), $initial);
    }
    echo '</ul></section>';
}

function social_editor_cosmetic_row(string $itemId, array $item, bool $isActive, string $initial): string
{
    $slot = (string)$item['slot'];
    if ($slot === 'frame') {
        [$a, $b] = badge60_frame_colors($itemId);
        $preview = '<span class="p60-avatar-frame has-frame" style="--p60-fa: ' . e($a) . '; --p60-fb: ' . e($b) . '"><span class="p60-avatar" aria-hidden="true">' . e($initial) . '</span></span>';
    } else {
        $preview = '<span class="p60-title-pill">' . e((string)$item['title']) . '</span>';
    }
    return '<li class="p60-cosmetic' . ($isActive ? ' is-active' : '') . '"><div class="p60-cosmetic-preview">' . $preview . '</div>'
        . '<div class="p60-cosmetic-info"><strong>' . e((string)$item['title']) . '</strong><small>' . e($slot === 'frame' ? tr('Rámeček avataru') : tr('Titulek u jména')) . ($isActive ? ' · ' . e(tr('Aktivní')) : '') . '</small></div>'
        . '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><input type="hidden" name="action" value="mkt60_cosmetic_set"><input type="hidden" name="return_tab" value="nastaveni">'
        . '<input type="hidden" name="slot" value="' . e($slot) . '"><input type="hidden" name="item_id" value="' . e($isActive ? '' : $itemId) . '">'
        . '<button class="btn secondary" type="submit">' . e($isActive ? tr('Vypnout') : tr('Použít na profilu')) . '</button></form></li>';
}

/** Soukromí: jen informace o tom, co je vidět spolužákům (žádná nová data se neukládají). */
function social_editor_privacy(): void
{
    echo '<section class="p60-group p60-privacy" aria-labelledby="p60-privacy-title"><h3 id="p60-privacy-title">' . e(tr('Soukromí')) . '</h3><div class="p60-grid-2">'
        . '<div><h4>' . e(tr('Vidí spolužáci ze třídy')) . '</h4><ul><li>' . e(tr('jméno, motto a představení')) . '</li><li>' . e(tr('dovednosti, zájmy, role a stav pro týmy')) . '</li>'
        . '<li>' . e(tr('level, vystavené odznaky a skills')) . '</li><li>' . e(tr('mastery po větvích')) . '</li></ul></div>'
        . '<div><h4>' . e(tr('Vidíš jen ty')) . '</h4><ul><li>' . e(tr('body a nákupy')) . '</li><li>' . e(tr('postup v Linux Labu')) . '</li><li>' . e(tr('tato nastavení')) . '</li></ul></div></div>'
        . '<p class="p60-lead">' . e(tr('Jména v žebříčcích Arény se řídí nastavením soukromí Arény.')) . '</p></section>';
}

function render_community_view(string $classId,array $module,string $flash=''): void
{
    $students=project_students_for_class($classId);$me=social_current_student_key($classId);$friends=array_fill_keys(student_friend_keys($classId,$me),true);$pending=student_pending_friend_requests($classId,$me);
    render_header(tr('Třída a spolužáci'),$module);?>
    <?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <section class="hero social-hero"><div class="eyebrow"><?=e(tr('Třída · spolupráce'))?></div><h1><?=e(tr('Spolužáci a studijní kontakty'))?></h1><p><?=e(tr('Propoj se se spolužáky, najdi dovednosti pro týmové projekty a otevři veřejný školní profil. Bez soukromých zpráv, e-mailů nebo veřejného sdílení mimo třídu.'))?></p><div class="hero-actions"><a class="btn primary" href="?view=profile"><?=e(tr('Můj profil'))?></a><a class="btn secondary" href="?view=project_lobbies"><?=e(tr('Najít tým'))?></a></div></section>
    <?php if($pending):?><section class="dashboard-panel friend-requests"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Čeká na tebe'))?></div><h2><?=e(tr('Žádosti o propojení'))?></h2></div></div><div class="friend-request-list"><?php foreach($pending as $req):$other=(string)($req['requester_key']??'');if(!isset($students[$other]))continue;?><article><div class="social-avatar"><?=e(u_substr((string)$students[$other]['label'],0,1))?></div><div><strong><?=edu_cs((string)$students[$other]['label'])?></strong><span><?=e(tr('chce přidat do školních kontaktů'))?></span></div><div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="friend_accept"><input type="hidden" name="student_key" value="<?=e($other)?>"><button class="btn primary" type="submit"><?=e(tr('Přijmout'))?></button></form><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="friend_reject"><input type="hidden" name="student_key" value="<?=e($other)?>"><button class="btn tertiary" type="submit"><?=e(tr('Odmítnout'))?></button></form></div></article><?php endforeach;?></div></section><?php endif;?>
    <section class="classmate-grid"><?php foreach($students as $key=>$student):if($key===$me)continue;$p=social_profile_get($classId,$key);$snap=learning_profile_snapshot_for_student($classId,(string)$student['label']);$rel=friendship_between($classId,$me,$key);?><article class="classmate-card"><div class="classmate-top"><div class="social-avatar"><?=e(u_substr((string)$student['label'],0,1))?></div><div><strong><?=edu_cs((string)$student['label'])?></strong><span><?=e(social_role_label((string)$p['preferred_role']))?> · <?=e(social_team_status_label((string)$p['team_status']))?></span></div><b><?=e(tr('LVL {level}',['level'=>(int)$snap['level']['level']]))?></b></div><p><?=(string)$p['headline']!==''?edu_cs((string)$p['headline']):e(tr('Zatím bez profilového motta.'))?></p><div class="social-tags compact"><?php foreach(array_slice((array)$p['skills'],0,4) as $tag):?><span><?=edu_cs((string)$tag)?></span><?php endforeach;?><?php if(!$p['skills']):?><span class="muted-tag"><?=e(tr('dovednosti neuvedeny'))?></span><?php endif;?></div><div class="classmate-meta"><span><?=e(tr('{count} badge',['count'=>count((array)$snap['badge_ids'])]))?></span><span><?=e(tr('{count} kontaktů',['count'=>student_friend_count($classId,$key)]))?></span></div><div class="classmate-actions"><a class="btn secondary" href="<?=e(module_url('profile',['student'=>$key]))?>"><?=e(tr('Profil'))?></a><?php if(!$rel):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="friend_request"><input type="hidden" name="student_key" value="<?=e($key)?>"><button class="btn tertiary" type="submit"><?=e(tr('+ Přítel'))?></button></form><?php elseif(($rel['status']??'')==='accepted'):?><span class="friend-state ok"><?=e(tr('✓ Přátelé'))?></span><?php else:?><span class="friend-state"><?=e(tr('čeká'))?></span><?php endif;?></div></article><?php endforeach;?></section>
    <?php render_footer();
}

function render_project_lobbies_view(string $classId,array $module,string $flash=''): void
{
    $projects=array_values(array_filter((array)(project_catalog()[$classId]??[]),static fn($p):bool=>is_array($p)&&(string)($p['type']??'')==='group'));
    $me=social_current_student_key($classId);$students=project_students_for_class($classId);
    $projectId=is_string($_GET['project']??null)?$_GET['project']:((string)($projects[0]['id']??''));$project=project_find($classId,$projectId);
    if(!$project||(string)($project['type']??'')!=='group'){$project=$projects[0]??null;$projectId=(string)($project['id']??'');}
    render_header(tr('Projektové týmy'),$module);?>
    <?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <section class="hero team-hero"><div class="eyebrow">Group Projects · Team Finder</div><h1><?=e(tr('Vytvořte si tým sami, ale bezpečně.'))?></h1><p><?=e(tr('Vyberte projekt, hlasujte o rozumném rozdělení třídy, založte lobby a sdílejte šestiznakový invite kód. Oficiální tým vznikne až po uzamčení lobby.'))?></p></section>
    <?php if(!$projects):?><section class="dashboard-panel"><h2><?=e(tr('Pro tuto třídu zatím není skupinový projekt.'))?></h2></section><?php render_footer();return;endif;?>
    <nav class="project-switcher"><?php foreach($projects as $pr):?><a class="<?=((string)$pr['id']===$projectId)?'active':''?>" href="<?=e(module_url('project_lobbies',['project'=>(string)$pr['id']]))?>"><span><?=e(tr('Skupinový projekt'))?></span><strong><?=edu_cs((string)$pr['title'])?></strong></a><?php endforeach;?></nav>
    <?php $plans=project_group_size_plans($classId);$votes=team_plan_vote_summary($classId,$projectId);$voteKey=$classId.'|'.$projectId.'|'.$me;$myVote=(string)((team_plan_votes()[$voteKey]['plan_id']??''));?>
    <section class="dashboard-panel team-planner"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Návrh podle {count} studentů',['count'=>count($students)]))?></div><h2><?=e(tr('Jak můžeme třídu rozdělit?'))?></h2><p><?=e(tr('Systém počítá jen varianty bez jednočlenného zbytku a drží týmy mezi 2–5 členy. Hlas je preference, ne automatické přiřazení.'))?></p></div></div><div class="team-plan-grid"><?php foreach($plans as $plan):?><article class="team-plan-card <?=!empty($plan['recommended'])?'recommended':''?> <?=($myVote===(string)$plan['id'])?'selected':''?>"><div><span><?=e(!empty($plan['recommended'])?tr('Doporučeno'):tr('Alternativa'))?></span><strong><?=e(group_plan_human((array)$plan['sizes']))?></strong><small><?=e(tr('{groups} týmů · {votes} hlasů',['groups'=>(int)$plan['groups'],'votes'=>(int)($votes[$plan['id']]??0)]))?></small></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="team_vote_plan"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="plan_id" value="<?=e((string)$plan['id'])?>"><button class="btn <?=($myVote===(string)$plan['id'])?'secondary':'tertiary'?>" type="submit"><?=e(($myVote===(string)$plan['id'])?tr('✓ Moje volba'):tr('Preferuji'))?></button></form></article><?php endforeach;?></div></section>
    <?php $group=project_group_for_student($classId,$projectId,$me);$lobby=team_active_lobby_for_student($classId,$projectId,$me);$projectSkillReqs=skill_project_requirements($classId,$projectId);$coverageMembers=$group?(array)$group['member_keys']:($lobby?(array)$lobby['member_keys']:($me!==''?[$me]:[]));$projectCoverage=$projectSkillReqs?skill_team_project_coverage($classId,$projectId,$coverageMembers):[];?>
    <?php if($projectSkillReqs):?><section class="dashboard-panel team-skill-coverage"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Skill coverage · doporučení'))?></div><h2><?=e(tr('Jaké kompetence se projektu hodí?'))?></h2><p><?=e(tr('Je to doporučení pro vyvážený tým, ne podmínka pro založení nebo uzamčení lobby.'))?></p></div></div><div class="team-skill-coverage-grid"><?php foreach($projectCoverage as $cov):$s=$cov['skill'];?><article class="<?=!empty($cov['covered'])?'covered':'missing'?>"><div><strong><?=edu_cs((string)$s['name'])?></strong><span><?=e(tr('doporučeno {percent} %',['percent'=>edu_number((float)$cov['recommended_mastery'],0)]))?></span></div><b><?=edu_number((float)$cov['team_mastery'],0)?> %</b><small><?=!empty($cov['covered'])?tr_html('✓ pokryto').((string)$cov['best_student']!==''?' · '.edu_cs((string)$cov['best_student']):''):e(tr('doplnit / rozvíjet'))?></small></article><?php endforeach;?></div></section><?php endif;?>
    <?php if($group):?><section class="dashboard-panel team-current formed"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Oficiální tým · uzamčeno'))?></div><h2><?=edu_cs((string)$group['name'])?></h2><p><?=e(tr('Pro tento projekt už je tvoje členství pevně uložené. Změny řeší učitel v administraci skupin.'))?></p></div><span class="team-state-pill formed"><?=e(tr('PŘIPRAVENO'))?></span></div><div class="team-member-grid"><?php foreach((array)$group['member_keys'] as $mk):if(!isset($students[$mk]))continue;$sp=social_profile_get($classId,(string)$mk);?><a href="<?=e(module_url('profile',['student'=>(string)$mk]))?>"><div class="social-avatar"><?=e(u_substr((string)$students[$mk]['label'],0,1))?></div><strong><?=edu_cs((string)$students[$mk]['label'])?></strong><span><?=e(social_role_label((string)$sp['preferred_role']))?></span></a><?php endforeach;?></div><div class="team-lobby-actions"><a class="btn primary" href="<?=e(project_workspace_url($projectId,'overview'))?>"><?=e(tr('Otevřít Project Workspace'))?></a></div></section>
    <?php elseif($lobby):$owner=((string)$lobby['owner_key']===$me);$members=(array)$lobby['member_keys'];?><section class="dashboard-panel team-current"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Tvoje lobby · {count} / {capacity}',['count'=>count($members),'capacity'=>(int)$lobby['capacity']]))?></div><h2><?=edu_cs((string)$lobby['name'])?></h2><p><?=e($owner?tr('Ty jsi zakladatel. Až bude sestava hotová, uzamkni tým.'):tr('Zakladatel lobby může po domluvě uzamknout finální sestavu.'))?></p></div><span class="team-state-pill <?=count($members)>=(int)$lobby['capacity']?'ready':''?>"><?=e(count($members)>=(int)$lobby['capacity']?tr('PLNÉ'):tr('OTEVŘENÉ'))?></span></div><div class="invite-code-box"><div><span><?=e(tr('Invite kód'))?></span><strong data-invite-code><?=e((string)$lobby['invite_code'])?></strong><small><?=tr_html('Platí pouze pro {project} a tuto třídu.',['project'=>edu_cs((string)$project['title'])])?></small></div><button type="button" class="btn secondary" data-copy-code="<?=e((string)$lobby['invite_code'])?>"><?=e(tr('Kopírovat kód'))?></button></div><div class="team-member-grid"><?php foreach($members as $mk):if(!isset($students[$mk]))continue;$sp=social_profile_get($classId,(string)$mk);?><a href="<?=e(module_url('profile',['student'=>(string)$mk]))?>"><div class="social-avatar"><?=e(u_substr((string)$students[$mk]['label'],0,1))?></div><strong><?=edu_cs((string)$students[$mk]['label'])?><?=((string)$lobby['owner_key']===(string)$mk)?e(tr(' · owner')):''?></strong><span><?=e(social_role_label((string)$sp['preferred_role']))?></span></a><?php endforeach;?><div class="team-empty-slot"><i>+</i><strong><?=e(tr('{count} volná místa',['count'=>max(0,(int)$lobby['capacity']-count($members))]))?></strong><span><?=e(tr('sdílej invite kód'))?></span></div></div><div class="team-lobby-actions"><?php if($owner):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="team_finalize_lobby"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="lobby_id" value="<?=e((string)$lobby['id'])?>"><button class="btn primary" type="submit" <?=count($members)<2?'disabled':''?>><?=e(tr('Uzamknout tým'))?></button></form><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="team_regenerate_code"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="lobby_id" value="<?=e((string)$lobby['id'])?>"><button class="btn tertiary" type="submit"><?=e(tr('Nový invite kód'))?></button></form><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="team_leave_lobby"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><input type="hidden" name="lobby_id" value="<?=e((string)$lobby['id'])?>"><button class="btn tertiary danger" type="submit"><?=e(tr('Opustit lobby'))?></button></form></div></section>
    <?php else:$capacities=[];foreach($plans as $pl)foreach((array)$pl['sizes'] as $sz)$capacities[(int)$sz]=true;ksort($capacities);?><div class="team-create-grid"><section class="dashboard-panel"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Založit tým'))?></div><h2><?=e(tr('Nové lobby'))?></h2><p><?=e(tr('Vyber cílovou velikost z variant, které dávají smysl pro aktuální počet studentů.'))?></p></div></div><form method="post" class="team-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="team_create_lobby"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><label><span><?=e(tr('Název týmu'))?></span><input name="lobby_name" maxlength="80" placeholder="<?=e(tr('Např. Pixel Patrol'))?>"></label><label><span><?=e(tr('Velikost týmu'))?></span><select name="capacity"><?php foreach(array_keys($capacities) as $cap):?><option value="<?=$cap?>"><?=e(tr('{count} studenti',['count'=>$cap]))?></option><?php endforeach;?></select></label><button class="btn primary" type="submit"><?=e(tr('Založit lobby + vytvořit kód'))?></button></form></section><section class="dashboard-panel"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Připojit se'))?></div><h2><?=e(tr('Mám invite kód'))?></h2><p><?=e(tr('Kód je šest znaků a funguje jen v rámci stejného projektu a třídy.'))?></p></div></div><form method="post" class="team-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="team_join_lobby"><input type="hidden" name="project_id" value="<?=e($projectId)?>"><label><span><?=e(tr('Invite kód'))?></span><input class="invite-input" name="invite_code" maxlength="6" autocomplete="off" placeholder="ABC234"></label><button class="btn secondary" type="submit"><?=e(tr('Připojit se do lobby'))?></button></form></section></div><?php endif;?>
    <section class="dashboard-panel team-finder"><div class="dashboard-panel-head"><div><div class="eyebrow"><?=e(tr('Team Finder'))?></div><h2><?=e(tr('Kdo ve třídě hledá tým?'))?></h2><p><?=e(tr('Profil ukazuje jen to, co si student sám vyplnil. Není to automatické párování ani žebříček.'))?></p></div><a class="text-link" href="?view=community"><?=e(tr('Všichni spolužáci →'))?></a></div><div class="team-finder-grid"><?php foreach($students as $key=>$st):if($key===$me)continue;$sp=social_profile_get($classId,$key);if(($sp['team_status']??'')==='full')continue;?><a href="<?=e(module_url('profile',['student'=>$key]))?>"><div class="social-avatar"><?=e(u_substr((string)$st['label'],0,1))?></div><div><strong><?=edu_cs((string)$st['label'])?></strong><span><?=e(social_role_label((string)$sp['preferred_role']))?></span><small><?=edu_cs(implode(' · ',array_slice((array)$sp['skills'],0,3)))?></small></div><b><?=e(social_team_status_label((string)$sp['team_status']))?></b></a><?php endforeach;?></div></section>
    <?php render_footer();
}

function render_prestige_exams_view(string $classId,array $module,string $flash=''): void
{
    $exams=special_assessments_for($classId);$best=special_exam_best_map($classId);$level=(int)(learning_level((int)(learning_profile($classId)['xp']??0))['level']??1);
    $selectedId=is_string($_GET['exam']??null)?$_GET['exam']:'';$selected=$selectedId!==''?special_assessment_find($classId,$selectedId):null;
    render_header(tr('Prestižní zkoušky'),$module);?>
    <?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <section class="hero prestige-exam-hero"><div class="eyebrow">Prestige Exams · <?=edu_cs('vzácné milníky')?></div><h1><?=e(tr('Badge se tady opravdu musí zasloužit.'))?></h1><p><?=e(tr('Prestižní zkoušky se odemykají až dlouhodobým postupem. Úspěch dává jednorázové XP; perfektní výsledek a dokončení více certifikací odemyká vzácné badge.'))?></p></section>
    <section class="prestige-exam-grid"><?php foreach($exams as $exam):$unlock=(int)($exam['unlock_level']??10);$locked=$level<$unlock;$r=$best[$exam['id']]??null;?><article class="prestige-exam-card <?=!empty($r['passed'])?'passed':($locked?'locked':'')?>"><div class="exam-level"><?=e(tr('LVL {level}',['level'=>$unlock]))?></div><span><?=e(!empty($r['passed'])?tr('✓ CERTIFIED'):($locked?tr('ZAMČENO'):tr('ODEMČENO')))?></span><h2><?=edu_cs((string)$exam['title'])?></h2><p><?=edu_cs((string)$exam['summary'])?></p><div class="exam-meta"><b><?=e(tr('{count} otázek',['count'=>count((array)$exam['questions'])]))?></b><b><?=e(tr('pass {score}/{total}',['score'=>(int)$exam['pass_score'],'total'=>count((array)$exam['questions'])]))?></b><?php if($r):?><b><?=e(tr('best {score}/{max}',['score'=>(int)$r['score'],'max'=>(int)$r['max_score']]))?></b><?php endif;?></div><?php if($locked):?><small><?=e(tr('Chybí {count} levelů.',['count'=>max(0,$unlock-$level)]))?></small><?php else:?><a class="btn <?=!empty($r['passed'])?'secondary':'primary'?>" href="<?=e(module_url('prestige_exams',['exam'=>(string)$exam['id']]))?>"><?=e(!empty($r['passed'])?tr('Zkusit znovu'):tr('Spustit zkoušku'))?></a><?php endif;?></article><?php endforeach;?></section>
    <?php if(is_array($selected)):$unlock=(int)($selected['unlock_level']??10);if($level>=$unlock):?>
    <section class="dashboard-panel prestige-exam-sheet"><div class="dashboard-panel-head"><div><div class="eyebrow">Certification attempt</div><h2><?=edu_cs((string)$selected['title'])?></h2><p><?=e(tr('Odpověz na všechny otázky. Výsledek se uloží do profilu; neúspěšný pokus ti nevezme XP ani badge.'))?></p></div><div class="exam-pass-badge"><span><?=e(tr('Minimum'))?></span><strong><?= (int)$selected['pass_score'] ?>/<?=count((array)$selected['questions'])?></strong></div></div><form method="post" class="prestige-exam-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="submit_prestige_exam"><input type="hidden" name="exam_id" value="<?=e((string)$selected['id'])?>"><?php foreach((array)$selected['questions'] as $qi=>$q):?><fieldset><legend><span><?=str_pad((string)($qi+1),2,'0',STR_PAD_LEFT)?></span><?=edu_cs((string)$q['q'])?></legend><div><?php foreach((array)$q['options'] as $oi=>$option):?><label><input type="radio" name="answers[<?=$qi?>]" value="<?=$oi?>" required><span><?=edu_cs((string)$option)?></span></label><?php endforeach;?></div></fieldset><?php endforeach;?><div class="exam-submit"><p><?=e(tr('Po odeslání se ukáže pouze skóre. Zkoušku můžeš opakovat; badge se udělí jen při splnění podmínky.'))?></p><button class="btn primary" type="submit"><?=e(tr('Odevzdat prestižní zkoušku'))?></button></div></form></section>
    <?php endif;endif; ?>
    <?php render_footer();
}
