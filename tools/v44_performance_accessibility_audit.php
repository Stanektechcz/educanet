<?php

declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(400);exit("CLI only\n");}
$root=dirname(__DIR__);$errors=[];
$css=$root.'/assets/learning-studio-v44.css';$js=$root.'/assets/learning-studio-v44.js';$sw=$root.'/sw.js';
$cssSize=is_file($css)?filesize($css):0;$jsSize=is_file($js)?filesize($js):0;
if(!$cssSize)$errors[]='missing v44 css';if(!$jsSize)$errors[]='missing v44 js';if($cssSize>26000)$errors[]='v44 css >26KB';if($jsSize>18000)$errors[]='v44 js >18KB';
$cssText=is_file($css)?file_get_contents($css):'';$jsText=is_file($js)?file_get_contents($js):'';$swText=is_file($sw)?file_get_contents($sw):'';$core=file_get_contents($root.'/learning_studio_v44.php')?:'';
foreach(['prefers-reduced-motion','@media print','@container'] as $needle)if(!str_contains($cssText,$needle))$errors[]="CSS missing $needle";
foreach(['data-v44-rep-tab','data-v44-diff-range','data-v44-teachback-submit','data-v44-memory-save'] as $needle)if(!str_contains($jsText,$needle))$errors[]="JS missing $needle";
if(!str_contains($core,'static $cache=[]'))$errors[]='request-level student state cache missing';
// v59 F4: dřív kontrola komentářové značky „educanet-v5x-shell“ (odstraněna) – teď aktivní const CACHE.
if(!preg_match('/const\s+CACHE\s*=\s*["\']educanet-v(?:4[4-9]|[5-9][0-9])/',$swText)||!preg_match('/learning-studio-v44\.css\?v=(?:44|4[5-9]|[5-9][0-9])/',$swText))$errors[]='service worker cache no longer covers v44 studio assets';
if(!str_contains($jsText,"prefers-reduced-motion"))$errors[]='JS reduced-motion handling missing';
echo 'v44 performance/a11y · studio.css='.round($cssSize/1024,1).'KB · studio.js='.round($jsSize/1024,1)."KB\n";
if($errors){foreach($errors as $e)echo "[FAIL] $e\n";exit(1);}echo "[OK] small progressive layer, request cache, reduced-motion, keyboard/native controls, print memory card and v44 PWA cache verified.\n";
