<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(400); exit("Spouštěj pouze přes SSH/CLI.\n"); }
$root=dirname(__DIR__);
$start=microtime(true);
require $root.'/bootstrap.php';
$bootstrapMs=(microtime(true)-$start)*1000;
require_once $root.'/runtime_content.php';

$classes=['class_1a','class_2a','class_3a','class_4a'];
$cacheOk=true;$cacheBytes=0;
foreach($classes as $cid){$p=runtime_content_cache_path($cid);$cacheOk=$cacheOk&&is_file($p);if(is_file($p))$cacheBytes+=(int)filesize($p);}
$t=microtime(true);$bundle=runtime_content_load_classes(['class_3a']);$bundleMs=(microtime(true)-$t)*1000;
$t=microtime(true);for($i=0;$i<15;$i++) load_php_json(STORAGE_DIR.'/student_accounts.json.php');$jsonMs=(microtime(true)-$t)*1000;

$opcacheLoaded=extension_loaded('Zend OPcache');
$opcacheEnabled=(bool)ini_get('opcache.enable');

echo "EDUCANET performance check\n===========================\n";
echo sprintf("Bootstrap:              %.2f ms\n",$bootstrapMs);
echo sprintf("Runtime class cache:    %s · %.1f KB · load %.2f ms\n",$cacheOk?'OK':'CHYBÍ',$cacheBytes/1024,$bundleMs);
echo sprintf("JSON request cache:     15 reads %.2f ms\n",$jsonMs);
echo "OPcache extension:      ".($opcacheLoaded?'ano':'ne')."\n";
echo "OPcache CLI enabled:    ".($opcacheEnabled?'ano':'ne')." (PHP-FPM může mít jiné nastavení)\n";
echo "PHP:                    ".PHP_VERSION."\n\n";
if(!$cacheOk){echo "[ACTION] Spusť: php tools/build_runtime_cache.php\n";}
if(!$opcacheLoaded){echo "[ACTION] Na serveru zapni Zend OPcache pro PHP-FPM.\n";}
echo "Doporučení pro ISPConfig/PHP-FPM:\n";
echo "- ověř opcache.enable=1, opcache.memory_consumption alespoň 128 MB\n";
echo "- po nasazení v33 reloadni příslušný PHP-FPM pool\n";
echo "- volitelný Apache cache/gzip příklad je v tools/apache_performance.htaccess.example\n";
