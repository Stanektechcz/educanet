<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(400); exit("Spouštěj pouze přes CLI.\n"); }
require dirname(__DIR__) . '/bootstrap.php';

$root = dirname(__DIR__);
$knowledgeTours = require $root . '/knowledge_tours.php';
foreach (['knowledge_tours_next.php','knowledge_tours_plus.php','knowledge_tours_more.php','knowledge_tours_ecosystem.php','knowledge_tours_yearpack.php','knowledge_tours_v30.php'] as $file) {
    $rows = require $root . '/' . $file;
    foreach ((array)$rows as $cid=>$articles) {
        if (!isset($knowledgeTours[$cid])) $knowledgeTours[$cid]=[];
        $knowledgeTours[$cid]=array_replace($knowledgeTours[$cid], is_array($articles)?$articles:[]);
    }
}
$nextLessons = require $root . '/next_lessons.php';
$extendedLessons = require $root . '/extended_lessons.php';
foreach (['lessons_plus.php','lessons_more.php','lessons_ecosystem.php','lessons_yearpack.php','lessons_v30.php'] as $file) {
    $rows = require $root . '/' . $file;
    foreach ((array)$rows as $cid=>$lessons) {
        if (!isset($extendedLessons[$cid])) $extendedLessons[$cid]=[];
        $extendedLessons[$cid]=array_merge($extendedLessons[$cid], is_array($lessons)?$lessons:[]);
    }
}
$simulations = require $root . '/simulations.php';
if (isset($knowledgeTours['class_2a'])) $knowledgeTours['class_1a']=array_replace($knowledgeTours['class_2a'],$knowledgeTours['class_1a']??[]);
if (isset($simulations['class_2a'])) $simulations['class_1a']=array_replace($simulations['class_2a'],$simulations['class_1a']??[]);
foreach (['simulations_extra.php','simulations_plus.php','simulations_more.php','simulations_ecosystem.php','simulations_yearpack.php'] as $file) {
    $rows = require $root . '/' . $file;
    foreach ((array)$rows as $cid=>$map) {
        if (!isset($simulations[$cid])) $simulations[$cid]=[];
        $simulations[$cid]=array_replace($simulations[$cid], is_array($map)?$map:[]);
    }
}
$learningResources = require $root . '/learning_resources.php';

$dir=$root.'/cache/runtime';
if (!is_dir($dir) && !mkdir($dir,0770,true) && !is_dir($dir)) throw new RuntimeException('Nelze vytvořit runtime cache.');
file_put_contents($dir.'/.htaccess', "Require all denied\n");
$classes=['class_1a','class_2a','class_3a','class_4a'];
foreach ($classes as $cid) {
    $payload=[
        'knowledgeTours'=>is_array($knowledgeTours[$cid]??null)?$knowledgeTours[$cid]:[],
        'nextLesson'=>is_array($nextLessons[$cid]??null)?$nextLessons[$cid]:null,
        'extendedLessons'=>is_array($extendedLessons[$cid]??null)?$extendedLessons[$cid]:[],
        'simulations'=>is_array($simulations[$cid]??null)?$simulations[$cid]:[],
        'learningResources'=>is_array($learningResources[$cid]??null)?$learningResources[$cid]:[],
    ];
    $php="<?php\n\ndeclare(strict_types=1);\n\nreturn ".var_export($payload,true).";\n";
    file_put_contents($dir.'/'.$cid.'.php',$php);
    echo sprintf("%-8s %7.1f KB\n",$cid,strlen($php)/1024);
}
echo "Runtime cache hotová: $dir\n";
