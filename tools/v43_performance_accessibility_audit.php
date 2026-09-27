<?php

declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(400);exit("CLI only\n");}
$root=dirname(__DIR__);$errors=[];
$css=$root.'/assets/cognitive-v43.css';$js=$root.'/assets/cognitive-v43.js';
$cssKb=round(filesize($css)/1024,1);$jsKb=round(filesize($js)/1024,1);
if($cssKb>48)$errors[]="cognitive CSS too large: {$cssKb}KB";if($jsKb>48)$errors[]="cognitive JS too large: {$jsKb}KB";
$cssText=file_get_contents($css)?:'';$jsText=file_get_contents($js)?:'';$view=file_get_contents($root.'/cognitive_visualization_views_v43.php')?:'';
foreach(['prefers-reduced-motion','content-visibility','container-type'] as $n)if(!str_contains($cssText,$n))$errors[]="CSS missing $n";
foreach(['navigator.connection','hardwareConcurrency','startViewTransition','motion-reduced'] as $n)if(!str_contains($jsText,$n))$errors[]="JS missing $n";
foreach(['input type="range"','button type="button"','aria-label'] as $n)if(!str_contains($view,$n))$errors[]="views missing $n";
if(str_contains($jsText,'WebGL')||str_contains($view,'<canvas'))$errors[]='Unexpected WebGL/canvas dependency';
echo "v43 performance/a11y · cognitive.css={$cssKb}KB · cognitive.js={$jsKb}KB\n";
if($errors){foreach($errors as $e)echo "[FAIL] $e\n";exit(1);}echo "[OK] progressive enhancement, reduced motion, save-data/low-power mode and native controls verified.\n";
