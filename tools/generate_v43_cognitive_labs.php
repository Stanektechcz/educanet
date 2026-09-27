<?php

declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(400);exit("CLI only\n");}
require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/runtime_content.php';
$runtime=runtime_content_load_classes(['class_1a','class_2a','class_3a','class_4a']);
$nextLessons=$runtime['nextLessons'];$extendedLessons=$runtime['extendedLessons'];
$base=dirname(__DIR__).'/materials/cognitive_labs';
if(!is_dir($base) && !mkdir($base,0775,true) && !is_dir($base)) throw new RuntimeException('Nelze vytvořit '.$base);
$csv=[['class','lesson','title','family','renderer','topic','layers','timeline','model_steps']];
$total=0;
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
  $dir=$base.'/'.$classId;if(!is_dir($dir))mkdir($dir,0775,true);
  foreach(v42_lessons_for_class($classId) as $lesson){
    $spec=cv43_lab_spec($classId,$lesson,$modules[$classId]);$n=(int)$lesson['number'];$total++;
    $lines=[];$lines[]='# Cognitive Lab · '.$spec['lesson_title'];$lines[]='';$lines[]='**Třída:** '.$classId.'  ';$lines[]='**Rodina:** '.$spec['family'].'  ';$lines[]='**Renderer:** '.$spec['renderer'].'  ';$lines[]='**Primary topic:** '.$spec['topic'];$lines[]='';
    $lines[]='## Situace';$lines[]=$spec['problem'];$lines[]='';$lines[]='## Freeze & predict';$lines[]=$spec['question'];foreach($spec['options'] as $i=>$o)$lines[]='- '.chr(65+$i).'. '.$o.($i===$spec['correct']?' **← očekávaný směr**':'');$lines[]='';
    $lines[]='## X-Ray vrstvy';foreach($spec['layers'] as $l)$lines[]='- **'.$l['label'].'** — '.$l['text'];$lines[]='';
    $lines[]='## Timeline scrubber';foreach($spec['timeline'] as $i=>$s)$lines[]=($i+1).'. '.$s;$lines[]='';
    if(!empty($spec['controls'])){$lines[]='## Cause → Effect controls';foreach($spec['controls'] as $c)$lines[]='- '.$c['label'].': '.$c['min'].'–'.$c['max'].$c['unit'].'; start '.$c['value'].$c['unit'].'; functional target '.$c['target'].$c['unit'];$lines[]='';}
    $lines[]='## Contrast case';$lines[]='**Typická past:** '.$spec['compare']['bad'];$lines[]='';$lines[]='**Funkční model:** '.$spec['compare']['good'];$lines[]='';$lines[]='**Proč:** '.$spec['compare']['why'];$lines[]='';
    $lines[]='## Build the model';$lines[]='`'.implode(' → ',$spec['model']['expected']).'`';$lines[]='';$lines[]='## Reverse engineering';$lines[]=$spec['reverse']['prompt'];foreach($spec['reverse']['clues'] as $c)$lines[]='- '.$c;$lines[]='';
    $lines[]='## Transfer';$lines[]=$spec['transfer'];$lines[]='';$lines[]='## Memory Snapshot';$lines[]='- **Jedna věta:** '.$spec['memory']['sentence'];$lines[]='- **Typická past:** '.$spec['memory']['trap'];$lines[]='- **Retrieval:** '.$spec['memory']['retrieval'];$lines[]='';
    $lines[]='## Teacher Live Explainer';$lines[]='- '.$spec['teacher']['pause'];$lines[]='- '.$spec['teacher']['ask'];$lines[]='- Misconception: '.$spec['teacher']['misconception'];$lines[]='';
    file_put_contents($dir.'/lesson_'.str_pad((string)$n,2,'0',STR_PAD_LEFT).'.md',implode("\n",$lines)."\n");
    $csv[]=[$classId,$n,$spec['lesson_title'],$spec['family'],$spec['renderer'],$spec['topic'],count($spec['layers']),count($spec['timeline']),count($spec['model']['expected'])];
  }
}
$f=fopen($base.'/LAB_CATALOG.csv','wb');foreach($csv as $row)fputcsv($f,$row,';', '"','\\');fclose($f);
echo "Generated $total cognitive labs in $base\n";
