<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root=dirname(__DIR__);$checks=[];
$check=static function(bool $ok,string $label) use (&$checks):void{$checks[]=[$ok,$label];echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;};
$index=(string)edu_app_source();
$goal=(string)file_get_contents($root.'/goal_navigator_v50_4.php');
$growth=(string)file_get_contents($root.'/independent_growth_v50.php');
$views=(string)file_get_contents($root.'/independent_growth_views_v50.php');
$layout=(string)file_get_contents($root.'/app/views/_layout.php');
$activeCss=(string)file_get_contents($root.'/assets/student-ui-v50-7-7.css');
$sw=(string)file_get_contents($root.'/sw.js');
$routes=edu_app_routes();

$check(str_contains($index,"goal_navigator_v50_4.php"),'Goal Navigator runtime is loaded');
// v59 F4: kompaktní menu s „Můj cíl“ bylo mrtvé ($menuItems v _layout.php) a je odstraněné; stránka cíle je dostupná
// přes routu goal_nav (kontrola výš) a má nadpis „Můj cíl“ (i18n: tr('Můj cíl')).
$check((bool)preg_match('~render_header\(\s*(?:tr\(\s*)?\'Můj cíl\'~',$goal),'goal page is titled Můj cíl');
$check(!str_contains($index,'<section class="student-route-card"'),'old large dashboard route card is no longer rendered');
$check(!str_contains($index,'<section class="student-intent-panel"'),'old four-card intent panel is no longer rendered');
// ř. 20-21 (dřív): "v504-dashboard-route"/"v504-dashboard-help" žijí jen v mrtvém komentáři
// dashboard.php ř. 229 ("compatibility markers only") – v50.4 kompaktní trasa/nápověda byla od té
// doby nahrazena tichým odkazem na progres (v506-dashboard-quiet-links) a kontextovou lištou
// pozornosti (student-attention-bar); ověřujeme skutečný nástupnický markup.
$check(str_contains($index,'v506-dashboard-quiet-links'),'dashboard keeps a single quiet link to detailed progress (successor of the v50.4 compact route)');
$check(str_contains($index,'student-attention-bar'),'dashboard surfaces contextual help/attention items on demand (successor of v50.4 contextual help links)');
// ř. 17: goal_nav je skutečně napojen na router (dřív se testovala mrtvá proměnná render_header()).
$goalNavRouted=false;
foreach ((array)($routes['views_student'] ?? []) as $segment) {
    if (is_array($segment) && in_array('goal_nav', (array)($segment['match'] ?? []), true)) { $goalNavRouted = true; break; }
}
$check($goalNavRouted,'goal_nav view is wired into app/routes.php views_student');
$check(str_contains($index,'if ($view === \'goal_nav\')'),'Goal Navigator route is wired');
$check(str_contains($index,"v504_goal_select"),'goal selection POST action is wired');
$check(str_contains($index,"v504_goal_clear"),'goal clear POST action is wired');
// ř. 20-21/25: v504_render_goal_context() je mrtvá funkce (volaná jen z komentáře v _layout.php) – kontrolujeme
// skutečný na stránce vykreslený kontext cíle (v504-goal-context) místo jejího jména/mrtvé proměnné.
$check(!str_contains($goal,'function v504_render_goal_context'),'dead goal-context helper was removed (v59 F4)');
$check(!preg_match('~^(?!.*/\*).*\bv504_render_goal_context\s*\(~m', preg_replace('~/\*.*?\*/~s', '', $layout) ?? ''), 'v504_render_goal_context() is not called from active layout code (dead function)');
$check(str_contains($index,'assets/goal-navigator-v50-4.js?v=50.4'),'v50.4 JS is loaded only for goal_nav view');

// i18n (v59): student-facing texty ve views/logice se postupně obalují no-op markerem trm('…')
// (vykreslení pak jde přes tr($label)) – počítání literálů musí tolerovat 'title'=>'X',
// 'title'=>trm('X') i budoucí 'title'=>tr('X'), ne jen holý řetězec.
$v504TitleCount = static function (string $text, string $literal): int {
    return preg_match_all('~\'title\'\s*=>\s*(?:tr(?:m)?\(\s*)?\'' . preg_quote($literal, '~') . '\'~', $text);
};
// Czech UI text can be plain HTML content, a plain PHP string literal, or wrapped as trm('…')/tr('…')
// (single or double quotes) by the i18n builder – accept any of those forms.
$v504HasCzech = static function (string $haystack, string $literal): bool {
    if (str_contains($haystack, $literal)) return true;
    foreach (["trm('", 'trm("', "tr('", 'tr("'] as $prefix) if (str_contains($haystack, $prefix . $literal)) return true;
    return false;
};
$check(substr_count($goal,"'class_1a'=>[")===1 && substr_count($goal,"'class_2a'=>[")===1 && substr_count($goal,"'class_3a'=>[")===1 && substr_count($goal,"'class_4a'=>[")===1,'goal catalog covers all four classes');
$check($v504TitleCount($goal,'Webdesign')>=2,'graphics goal catalog includes Webdesign');
$check($v504TitleCount($goal,'PHP / backend')+$v504TitleCount($goal,'PHP / full-stack')>=2,'graphics goal catalog includes PHP direction');
$check($v504TitleCount($goal,'Sítě')>=2,'SOSaPS goal catalog includes Networking');
$check($v504TitleCount($goal,'Linux administrace')>=2,'SOSaPS goal catalog includes Linux');
$check($v504TitleCount($goal,'Security')>=2,'SOSaPS goal catalog includes Security');
$check(str_contains($goal,"v50_rows('goal_navigator')"),'goal preference uses isolated v50 runtime storage');
$check(str_contains($goal,"'grade_impact'=>false") && str_contains($goal,"'xp_impact'=>false") && str_contains($goal,"'mastery_impact'=>false"),'goal selection explicitly has no grade/XP/mastery impact');
$check(str_contains($goal,'array_slice($steps,0,3)'),'goal route exposes at most three visible steps');
$check(str_contains($goal,'v504_goal_course_step'),'goal route connects required curriculum');
$check(str_contains($goal,'v504_goal_skill_step'),'goal route connects Skill Trees');
$check(str_contains($goal,'v504_goal_growth_target'),'goal route connects Independent Growth');
$check(str_contains($goal,'skill_passport'),'goal route connects evidence/Skill Passport');
$check($v504HasCzech($goal,'Po dokončení se cesta automaticky přepočítá.'),'student is told progression recalculates automatically');
$check(str_contains($growth,"'network-engineering'"),'SOSaPS Networking has a dedicated growth path');
$check(str_contains($views,"'network-engineering'"),'new Networking growth path is visible in path order');
$check(str_contains($views,'goal-recommended'),'selected goal highlights its matching growth path');
// ř. 12/26: goal-navigator-v50-4.css se už nikde nelinkuje samostatně – styl žije v konsolidovaném
// student-ui-v50-7-7.css (sekce "source: goal-navigator-v50-4.css"). Ověřujeme chování tam a že
// samostatný soubor se nikde neodkazuje jako <link>.
$check(str_contains($activeCss,'/* ---- source: goal-navigator-v50-4.css ---- */'),'consolidated CSS keeps a goal-navigator source section');
$check(str_contains($activeCss,'.topbar .learning-hud{display:none}'),'topbar XP/badge chrome stays removed from primary navigation (consolidated CSS)');
$check(str_contains($activeCss,'.guided-flow-steps li span{display:none!important}'),'five-stage flow stays visually compressed (consolidated CSS)');
$check(!preg_match('~<link[^>]+assets/goal-navigator-v50-4\.css~',$index),'goal-navigator-v50-4.css is never linked on its own (superseded by consolidated CSS)');
$check(str_contains($activeCss,'@media(max-width:820px)'),'goal UX has mobile breakpoint');
$js=(string)file_get_contents($root.'/assets/goal-navigator-v50-4.js');
$check(str_contains($js,'.v504-goal-choice button'),'goal choice interaction is wired');
// ř. 50-52: PWA precache markery v sw.js jsou jen komentáře – JS se načítá vždy živě jen na goal_nav,
// takže v aktivním SHELL/OFFLINE_LAB být nemusí; ověřujeme, že precache seznam žádný z legacy assetů neobsahuje.
if (preg_match('~const SHELL=(\[[^;]+\]);~s', $sw, $m)) { $activeShell = $m[1]; } else { $activeShell = ''; }
$check($activeShell !== '' && !str_contains($activeShell, 'goal-navigator-v50-4'), 'PWA precache list does not force-cache the page-specific goal navigator JS');

$failed=array_values(array_filter($checks,static fn(array $r):bool=>!$r[0]));
if($failed){fwrite(STDERR,'V50_4_GOAL_NAV_AUDIT_FAILED='.count($failed).PHP_EOL);exit(1);}echo 'V50_4_GOAL_NAV_AUDIT_OK checks='.count($checks).PHP_EOL;
