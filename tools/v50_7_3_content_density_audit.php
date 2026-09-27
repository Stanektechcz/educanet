<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root=dirname(__DIR__);
$read=static fn(string $f):string=>(string)file_get_contents($root.'/'.$f);
$index=edu_app_source();
// Aktivní konsolidovaný soubor je dnes student-ui-v50-7-7.{css,js} (viz asset_url() volání
// v app/views/_layout.php), ne vyřazený student-ui-v50-7-3.{css,js}. v50.7.3 zůstává jako
// mezikrok jen v komentářích/markerech – audit už ho nesmí číst jako živý soubor.
$activeCssPath = 'assets/student-ui-v50-7-7.css';
$activeJsPath = 'assets/student-ui-v50-7-7.js';
$css=$read($activeCssPath);
$js=$read($activeJsPath);
$sw=$read('sw.js');
// PHP block comments obsahují historické markery pro staré audity (nejsou to živé značky) –
// odstraníme je, než hledáme skutečně vykreslované <link>/<script> tagy.
$indexNoComments = (string)preg_replace('~/\*.*?\*/~s', '', $index);
$ok=0;$total=0;
$check=static function(bool $pass,string $label)use(&$ok,&$total):void{$total++;echo ($pass?'PASS':'FAIL')."\t$label\n";if($pass)$ok++;};

// Rozpočet velikosti: dnešní velikost aktivního souboru + 10 % rezerva proti nekontrolovanému růstu.
// Základní hodnoty jsou zaznamenané k datu přepisu auditu (F4-1); pokud legitimně naroste o víc než
// 10 %, konstanty aktualizuj společně s odůvodněním v BUILD_MANIFEST.
const V50_7_3_AUDIT_CSS_BUDGET_BYTES = 109000; // ~99106 B + 10 %
const V50_7_3_AUDIT_JS_BUDGET_BYTES = 7600;    // ~6827 B + 10 %
$check(is_file($root.'/'.$activeCssPath),'active consolidated student CSS exists');
$check(is_file($root.'/'.$activeJsPath),'active consolidated student runtime exists');
$check(strlen($css) <= V50_7_3_AUDIT_CSS_BUDGET_BYTES,'active student CSS payload stays within its size budget (today + 10%)');
$check(strlen($js) <= V50_7_3_AUDIT_JS_BUDGET_BYTES,'active student runtime payload stays within its size budget (today + 10%)');
$check(substr_count($index,"assets/student-ui-v50-7-7.css?v=51.0")===1,'index loads exactly one consolidated student stylesheet');
$check(!preg_match('~<link[^>]+assets/(?:student-ux-v50-2|guided-flow-v50-3|goal-navigator-v50-4|zero-friction-v50-6|unified-page-shell-v50-7|student-design-system-v50-7-1|student-design-system-v50-7-2|student-ui-v50-7-3)\.css~',$indexNoComments),'legacy student stylesheets (incl. v50.7.3) are not active link tags');
$check(substr_count($index,"assets/student-ui-v50-7-7.js?v=51.0")===1,'index loads exactly one consolidated global student runtime');
$check(!preg_match('~<script[^>]+src="[^"]*(?:guided-flow-v50-3|student-ui-v50-7-3)\.js~',$indexNoComments),'retired Guided Flow / v50.7.3 JS is no longer globally loaded');
// v58 · F5: odkaz na asset může jít přes asset_url() (verze podle filemtime, DAT-05).
$check(str_contains($index,"==='goal_nav'): ?><script src=\"assets/goal-navigator-v50-4.js?v=50.4\"") || str_contains($index,"==='goal_nav'): ?><script src=\"<?= e(asset_url('assets/goal-navigator-v50-4.js?v=50.4')) ?>\""),'Goal Navigator micro-runtime is loaded only on its page');
$check(!str_contains($index, 'guided-flow-stage-'), 'dead guided-flow body state removed');
$check(!str_contains($index,'<div class="learning-hud"'),'hidden XP/badge HUD removed from DOM');
$check(!str_contains($index,'<div class="class-chip"'),'hidden class chip removed from DOM');
$check(!str_contains($index,'student-nav-home <?= $homeActive?\'active\':\'\' ?>" href="?view=dashboard"><i>'),'primary Home icon markup removed');
$check(!str_contains($index,'student-nav-continue" href="<?=e($continueUrl)?>"><i>'),'primary Continue icon markup removed');
$check(!str_contains($index,'<summary><i><?=e((string)$groupDef[\'mark\'])?></i>'),'group navigation hidden icon markup removed');
$check(str_contains($css,'/* ---- source: student-ux-v50-2.css ---- */')&&str_contains($css,'/* ---- source: student-design-system-v50-7-2.css ---- */'),'consolidated CSS keeps its compatibility source sections');
$check(!str_contains($css,'/* ---- source: guided-flow-v50-3.css ---- */'),'retired Guided Flow stylesheet section is excluded from active CSS');
$check(str_contains($css,'v50.7.3 density/purge refinements'),'v50.7.3 density layer is present');
$check(str_contains($css,'details:not(.student-nav-group)>summary{min-height:46px'),'secondary disclosure density is normalized');
$check(str_contains($css,'.student-do-now-card{padding:20px')||str_contains($css,'.student-do-now-card{display:grid'),'dashboard primary card density rules are present');
$check(str_contains($css,'.course-map-hero')&&str_contains($css,'.skill-hero')&&str_contains($css,'.school-calendar-hero'),'map surfaces share density rules');
$check(str_contains($css,'.adaptive-empty')&&str_contains($css,'.pw-empty-hero'),'empty states share compact component rules');
$check(str_contains($js,'[data-nav-dropdown]'),'consolidated runtime retains nav behavior');
$check(str_contains($js,'details.v506-progressive'),'consolidated runtime retains progressive disclosure behavior');
$check(str_contains($js,'[data-v507-page-shell]'),'consolidated runtime retains Unified Page Shell behavior');
$check(!str_contains($js,'[data-guided-help-toggle]'),'retired Guided Flow behavior is absent from active runtime');
if(preg_match('~const CACHE="(educanet-v\d+[^"]*)";~',$sw,$cm)){$activeCache=$cm[1];}else{$activeCache='';}
$check($activeCache!=='','PWA cache namespace is a versioned educanet-vNN identifier');
if(preg_match('~const SHELL=(\[[^;]+\]);~s',$sw,$m)){$activeShell=$m[1];}else{$activeShell='';}
$check($activeShell!==''&&str_contains($activeShell,'student-ui-v50-7-7.css')&&str_contains($activeShell,'student-ui-v50-7-7.js'),'PWA precaches the active consolidated student UI assets');
$check($activeShell!==''&&!str_contains($activeShell,'guided-flow-v50-3'),'retired Guided Flow assets are absent from active PWA shell');
$check($activeShell!==''&&!str_contains($activeShell,'student-design-system-v50-7-1')&&!str_contains($activeShell,'student-design-system-v50-7-2'),'legacy repair layers are absent from active PWA shell');
$check($activeShell!==''&&!str_contains($activeShell,'student-ui-v50-7-3'),'retired v50.7.3 consolidated assets are absent from active PWA shell');
$check(!preg_match('~(?:storage|private|uploads|cache)/~',$css.$js),'consolidated assets have no runtime-path coupling');
echo "V50_7_3_CONTENT_DENSITY_AUDIT $ok/$total\n";
exit($ok===$total?0:1);
