<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root=dirname(__DIR__);
$index=edu_app_source();
// ř. 7/11: student-design-system-v50-7-1.css se už samostatně nelinkuje – jeho pravidla žijí uvnitř
// konsolidovaného student-ui-v50-7-7.css (sekce "source: student-design-system-v50-7-1.css").
$activeCssFile = $root.'/assets/student-ui-v50-7-7.css';
$fullCss = (string)file_get_contents($activeCssFile);
if (preg_match('~/\* ---- source: student-design-system-v50-7-1\.css ---- \*/(.*?)(?:/\* ---- source: |\z)~s', $fullCss, $m)) {
    $css = $m[1];
} else {
    $css = '';
}
$sw=(string)file_get_contents($root.'/sw.js');
$checks=[];
$check=function(bool $ok,string $label) use (&$checks){$checks[]=[$ok,$label];echo ($ok?'PASS':'FAIL')." · $label\n";};
$check($css!=='','consolidated CSS keeps a student-design-system-v50-7-1 source section');
$check(!preg_match('~<link[^>]+assets/student-design-system-v50-7-1\.css~',$index),'student-design-system-v50-7-1.css is never linked on its own');
$check(str_contains($index,'assets/student-ui-v50-7-7.css?v=51.0'),'consolidated student stylesheet (successor of the v50.7.1 repair layer) is loaded');
$check(str_contains($css,'.view-dashboard .student-cockpit-main')&&str_contains($css,'grid-template-columns:1fr!important'),'dashboard legacy two-column regression removed');
$check(str_contains($css,'.student-main-menu>a>i')&&str_contains($css,'display:none!important'),'primary navigation decorative icons normalized');
$check(str_contains($css,'--edu-font:'),'single student font stack defined');
$check(str_contains($css,'.btn{min-height:42px;'),'button geometry normalized');
$check(str_contains($css,'.panel,body:not(.assessment-mode) .dashboard-panel'),'shared cards normalized');
$check(str_contains($css,'.account-chip span{display:none!important'),'duplicate account label removed');
$check(str_contains($css,'@media(max-width:560px)'),'mobile repair breakpoint present');
// ř. 19-20: sw.js už nemá samostatný v50.7.1 marker/precache záznam – ověřujeme, že aktivní SHELL
// obsahuje nástupnický konsolidovaný soubor a ne ten vyřazený.
if (preg_match('~const SHELL=(\[[^;]+\]);~s', $sw, $sm)) { $activeShell = $sm[1]; } else { $activeShell = ''; }
$check($activeShell !== '' && str_contains($activeShell,'student-ui-v50-7-7.css'),'PWA precaches the successor consolidated stylesheet');
$check($activeShell !== '' && !str_contains($activeShell,'student-design-system-v50-7-1'),'PWA precache list has no reference to the retired v50.7.1 file');
$fail=count(array_filter($checks,fn($x)=>!$x[0]));
echo "V50_7_1_UI_REPAIR_AUDIT ".($fail?'FAIL':'PASS')." ".(count($checks)-$fail).'/'.count($checks)."\n";
exit($fail?1:0);
