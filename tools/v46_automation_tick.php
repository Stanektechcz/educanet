<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }
require dirname(__DIR__).'/bootstrap.php';
require_once dirname(__DIR__).'/teacher_class_dashboard.php';
require_once dirname(__DIR__).'/teacher_operations_v46.php';
$result=teacher_ops_automation_tick();
fwrite(STDOUT,'EDUCANET v46 automation tick'.PHP_EOL);
fwrite(STDOUT,'Checked: '.(int)$result['checked'].PHP_EOL);
fwrite(STDOUT,'Notifications: '.(int)$result['notifications'].PHP_EOL);
