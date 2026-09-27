<?php

declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(400);exit("CLI only\n");}
$root=dirname(__DIR__);$errors=[];
$css=file_get_contents($root.'/assets/visual-simulation-v45.css')?:'';$js=file_get_contents($root.'/assets/visual-simulation-v45.js')?:'';$views=file_get_contents($root.'/visual_simulation_views_v45.php')?:'';$sw=file_get_contents($root.'/sw.js')?:'';
if(!str_contains($css,'prefers-reduced-motion'))$errors[]='CSS reduced-motion fallback missing';
if(!str_contains($js,'prefers-reduced-motion'))$errors[]='JS reduced-motion detection missing';
if(!str_contains($views,'aria-live="polite"'))$errors[]='live feedback aria-live missing';
if(!str_contains($views,'role="img"'))$errors[]='system visualization role=img missing';
if(!str_contains($js,'requestFullscreen'))$errors[]='projection fullscreen support missing';
if(!str_contains($sw,'visual-simulation-v45.css')||!str_contains($sw,'visual-simulation-v45.js'))$errors[]='v45 assets missing from PWA shell';
if(str_contains($js,'WebGL')||str_contains($js,'three.js'))$errors[]='unexpected heavyweight rendering dependency';
$cssBytes=strlen($css);$jsBytes=strlen($js);if($cssBytes>42000)$errors[]='CSS budget exceeded';if($jsBytes>30000)$errors[]='JS budget exceeded';
echo "v45 Performance / Accessibility Audit\nCSS: ".round($cssBytes/1024,1)." KB\nJS: ".round($jsBytes/1024,1)." KB\n";
if($errors){foreach($errors as $e)echo "[FAIL] $e\n";exit(1);}echo "[OK] lightweight progressive simulation layer, reduced-motion support, semantic live feedback and offline shell coverage.\n";
