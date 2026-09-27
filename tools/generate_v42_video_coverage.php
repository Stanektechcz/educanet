<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__).'/bootstrap.php';
$resources=require dirname(__DIR__).'/learning_resources.php';
$lines=['# Video coverage · 112 strukturovaných lekcí','','Video je volitelná alternativní cesta. Povinný learning flow na externím videu nikdy nezávisí.',''];
foreach(['class_1a','class_2a','class_3a','class_4a'] as $cid){$lines[]='## '.($modules[$cid]['name']??$cid).' · '.($modules[$cid]['subject']??'');$lines[]='';foreach(v42_lessons_for_class($cid) as $l){$videos=v42_lesson_videos($cid,$l,$resources);$names=array_map(static fn($v)=>'['.(string)$v['title'].']('.(string)$v['url'].')',$videos);$lines[]='- **L'.str_pad((string)((int)$l['number']),2,'0',STR_PAD_LEFT).'** · '.(string)$l['title'].' — '.implode(' · ',$names);} $lines[]='';}
file_put_contents(dirname(__DIR__).'/materials/VIDEO_COVERAGE_V42.md',implode("\n",$lines)."\n");echo "Video coverage generated\n";
