<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root=dirname(__DIR__);$errors=[];$warnings=[];
$js=filesize($root.'/assets/app.js')?:0;$css=filesize($root.'/assets/app.css')?:0;$mlCss=filesize($root.'/assets/mastery.css')?:0;
// app.css is legacy accumulated curriculum UI. New v41 styles have their own hard budget.
if($js>240*1024)$warnings[]='app.js exceeds 240 KB ('.$js.')';
if($css>430*1024)$warnings[]='legacy app.css exceeds 430 KB ('.$css.')';
if($mlCss>24*1024)$errors[]='mastery.css exceeds 24 KB ('.$mlCss.')';
$index=edu_app_source()?:'';$app=(file_get_contents($root.'/assets/app.css')?:'').(file_get_contents($root.'/assets/mastery.css')?:'');$sw=file_get_contents($root.'/sw.js')?:'';
foreach(['aria-label','role="tablist"','meta name="viewport"'] as $n)if(!str_contains($index,$n))$warnings[]='index weak accessibility marker '.$n;
if(!str_contains($app,'prefers-reduced-motion'))$errors[]='missing reduced motion';if(!str_contains($app,':focus')&&!str_contains($app,'focus-visible'))$warnings[]='no explicit focus style marker';if(!str_contains($sw,"request.method!=='GET'"))$errors[]='service worker must not cache POST';if(!str_contains($index,'assets/mastery.css'))$errors[]='mastery.css not linked';
if($errors){foreach($errors as $e)fwrite(STDERR,"ERROR: $e\n");exit(1);}foreach($warnings as $w)fwrite(STDERR,"WARN: $w\n");echo 'v41 performance/a11y OK · app.js='.round($js/1024,1).'KB · legacy-css='.round($css/1024,1).'KB · mastery.css='.round($mlCss/1024,1)."KB\n";
