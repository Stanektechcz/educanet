<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';
$resources = require dirname(__DIR__) . '/learning_resources.php';

$root = dirname(__DIR__) . '/materials/lesson_kits';
@mkdir($root, 0775, true);
$index = ["# EDUCANET · Lesson Kits v42", "", "Každá strukturovaná lekce má video guide, micro-video storyboard, slide deck, one-pager, worksheet, AI prompt pack a exam-prep část.", ""];
$csv = ["class,lesson,title,videos,slides,prompts,oral_questions"];

function md(string $s): string { return str_replace(["\r","\n"],[""," "],trim($s)); }

foreach (['class_1a','class_2a','class_3a','class_4a'] as $classId) {
    $module = $modules[$classId];
    $dir = $root . '/' . $classId; @mkdir($dir, 0775, true);
    $classLabel = (string)($module['name'] ?? $classId);
    $index[] = "## {$classLabel} · " . (string)($module['subject'] ?? '');
    $index[] = "";
    foreach (v42_lessons_for_class($classId) as $lesson) {
        $n=(int)($lesson['number']??0); if($n<1||$n>28) continue;
        $pack=v42_lesson_pack($classId,$lesson,$module,$resources,'');
        $file=sprintf('lesson_%02d.md',$n);$path=$dir.'/'.$file;
        $lines=[];
        $lines[]='# '.md((string)($lesson['title']??('Lekce '.$n)));
        $lines[]='';$lines[]='**Třída:** '.$classLabel.'  ';$lines[]='**Předmět:** '.md((string)($module['subject']??'')).'  ';$lines[]='**Cíl:** '.md((string)($lesson['goal']??''));$lines[]='';
        $lines[]='## 1. Doporučená videa';$lines[]='';
        foreach((array)$pack['videos'] as $v){$lines[]='### '.md((string)$v['title']);$lines[]='';$lines[]='- URL: '.(string)$v['url'];$lines[]='- Délka: '.md((string)($v['duration']??''));$lines[]='- Úroveň: '.md((string)($v['level']??''));$lines[]='- Před: '.md((string)$v['before']);$lines[]='- Během: '.md((string)$v['during']);$lines[]='- Po: '.md((string)$v['after']);$lines[]='';}
        $lines[]='### 90s micro-video storyboard';$lines[]='';
        foreach((array)$pack['microvideo'] as $s){$lines[]='- **'.md((string)$s['time']).' · '.md((string)$s['title']).'** — '.md((string)$s['voice']).' _Vizuál: '.md((string)$s['visual']).'_';}
        $lines[]='';$lines[]='## 2. Prezentace';$lines[]='';
        foreach((array)$pack['slides'] as $i=>$s){$lines[]='### Slide '.($i+1).' · '.md((string)$s['title']);$lines[]='';$lines[]='**'.md((string)$s['kicker']).'**  ';$lines[]=md((string)$s['body']);$lines[]='';$lines[]='> Poznámka pro výklad: '.md((string)$s['note']);$lines[]='';}
        $m=(array)$pack['materials'];$one=(array)$m['one_pager'];
        $lines[]='## 3. One-pager / tahák';$lines[]='';$lines[]='### Cíl';$lines[]='';$lines[]=md((string)$one['goal']);$lines[]='';$lines[]='### Klíčové principy';foreach((array)$one['principles'] as $x)$lines[]='- '.md((string)$x);$lines[]='';$lines[]='### Hotovo znamená';foreach((array)$one['definition_of_done'] as $x)$lines[]='- [ ] '.md((string)$x);$lines[]='';
        $lines[]='## 4. Pracovní list';$lines[]='';foreach((array)$m['worksheet'] as $x)$lines[]='- '.md((string)$x);$lines[]='';$lines[]='**Rozšíření:** '.md((string)$m['extension']);$lines[]='';
        $lines[]='## 5. Mini slovník';$lines[]='';foreach((array)$m['glossary'] as $g)$lines[]='- **'.md((string)$g['term']).'** — '.md((string)$g['definition']);$lines[]='';
        $lines[]='## 6. AI prompty pro studenta';$lines[]='';foreach((array)$pack['prompts'] as $p){$lines[]='### '.md((string)$p['title']);$lines[]='';$lines[]='```text';$lines[]=(string)$p['prompt'];$lines[]='```';$lines[]='';}
        $e=(array)$pack['exam'];$lines[]='## 7. Zkouška nanečisto';$lines[]='';$lines[]='### Ústní otázky';foreach((array)$e['oral'] as $q)$lines[]='- '.md((string)$q);$lines[]='';$lines[]='### Praktická část';$lines[]='';$lines[]=md((string)$e['practical']);$lines[]='';$lines[]='### Self-check';foreach((array)$e['self_check'] as $x)$lines[]='- [ ] '.md((string)$x);$lines[]='';$lines[]='### Rubrika';foreach((array)$e['rubric'] as $r)$lines[]='- **'.md((string)$r['level']).'** — '.md((string)$r['text']);$lines[]='';
        file_put_contents($path,implode("\n",$lines)."\n");
        $index[]='- [Lekce '.str_pad((string)$n,2,'0',STR_PAD_LEFT).' · '.md((string)($lesson['title']??'' )).']('.$classId.'/'.$file.')';
        $csv[]=implode(',',[
            $classId,$n,'"'.str_replace('"','""',md((string)($lesson['title']??''))).'"',count($pack['videos']),count($pack['slides']),count($pack['prompts']),count($pack['exam']['oral'])
        ]);
    }
    $index[]='';
}
file_put_contents($root.'/README.md',implode("\n",$index)."\n");
file_put_contents($root.'/LESSON_KITS_INDEX.csv',implode("\n",$csv)."\n");
echo "Generated lesson kits in {$root}\n";
