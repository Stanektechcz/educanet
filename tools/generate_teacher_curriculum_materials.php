<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';
if (!function_exists('teacher_class_label')) {
    function teacher_class_label(string $classId): string { return ['class_1a'=>'1.A','class_2a'=>'2.A','class_3a'=>'3.A','class_4a'=>'4.A'][$classId] ?? $classId; }
}
require dirname(__DIR__) . '/teacher_curriculum.php';

$root = dirname(__DIR__);
$specs = teacher_curriculum_spec();
$skill = require $root . '/skill_catalog.php';
$projects = project_catalog();

$esc = static fn(string $s): string => str_replace('|','\\|',$s);

$overview = "# EDUCANET · Učitelský přehled výuky\n\n";
$overview .= "Aktualizovaný přehled pro 1.A–4.A. Každý ročník obsahuje 28 strukturovaných dvouhodinových lekcí + 12 aplikovaných středečních bloků, návazný projektový systém a Skill Mastery.\n\n";
$overview .= "| Třída | Předmět | Tematické etapy | Hlavní capstone | Skill Mastery |\n|---|---|---|---|---|\n";
foreach ($specs as $cid=>$spec) {
    $blockText = implode(' → ', array_map(static fn($b)=>$b['range'].' '.$b['title'],$spec['blocks']));
    $overview .= '| '.teacher_class_label($cid).' | '.$esc($spec['subject']).' | '.$esc($blockText).' | '.$esc($spec['capstone']).' | '.$esc($spec['mastery_note'])." |\n";
}
$overview .= "\n## Doporučený učitelský rytmus\n\n1. **Před lekcí:** ověř cíl, evidence, Knowledge materiály, student output a diferenciaci.\n2. **Během lekce:** krátký model → práce studenta → iterace → QA/validace → exit ticket.\n3. **Před projektem:** brief, role, Definition of Done, QA gate, peer feedback a retrospektiva.\n4. **Při hodnocení:** odděluj týmový výsledek od individuální role; Mastery vzniká jen z ověřené evidence.\n5. **Po projektu:** publikuj konkrétní feedback, nech proběhnout retrospektivu a projdi Skill heatmap.\n\n## Třídy\n";
foreach ($specs as $cid=>$spec) {
    $overview .= "\n### ".teacher_class_label($cid)." · ".$spec['subject']."\n\n".$spec['focus']."\n\n";
    foreach ($spec['blocks'] as $b) $overview .= '- **'.$b['range'].' · '.$b['title'].':** '.$b['text']."\n";
    $overview .= "\n**Capstone:** ".$spec['capstone']."  \n**Mastery:** ".$spec['mastery_note']."\n";
}
file_put_contents($root.'/materials/TEACHER_OVERVIEW.md',$overview);

$matrix = "# EDUCANET · Assessment & Skill Mastery matrix\n\n";
$matrix .= "| Třída | Hlavní hodnocení | Mastery větve | Projektová evidence |\n|---|---|---|---|\n";
foreach ($specs as $cid=>$spec) {
    $matrix .= '| '.teacher_class_label($cid).' | '.$esc(implode(' · ',$spec['assessment'])).' | '.$esc($spec['mastery_note']).' | Teacher role evaluation → validovaná Project Skill Evidence → mastery recalculation |' . "\n";
}
$matrix .= "\n## Společná pravidla\n\n- počet kliknutí, tasků ani komentářů sám o sobě nezvyšuje Mastery;\n- opakovatelná evidence používá nejlepší validní výsledek / delta, ne nekonečné sčítání;\n- peer feedback je podklad pro reflexi a učitele, nikoli automatická známka;\n- týmový výsledek a individuální role se hodnotí odděleně;\n- povinné praktické gates mohou omezit maximální mastery, dokud student danou část skutečně neprokáže;\n- Prestige Badge vzniká až z významného mastery/certifikačního milníku.\n";
file_put_contents($root.'/materials/ASSESSMENT_MASTERY_MATRIX.md',$matrix);

$roles = <<<'MD'
# EDUCANET · Project Role Guide

| Role | Hlavní odpovědnost | Evidence | Učitel sleduje |
|---|---|---|---|
| Leader | plán, koordinace, dependencies, blockers | plán, decision log, průběžné priority | zda tým ví co dělat a Leader nepřebírá práci ostatních |
| Designer | vizuální systém, UI, typografie, handoff | návrhy, komponenty, states, design decisions | konzistenci, použitelnost a obhajitelnost rozhodnutí |
| Researcher | research, zdroje, analýza, syntéza | source list, findings, comparison, recommendation | kvalitu zdrojů a dopad research na rozhodnutí |
| Developer | technická realizace a integrace | implementace, konfigurace, debug evidence, dokumentace | funkčnost, správnost, udržovatelnost a vysvětlení |
| Presenter | struktura obhajoby, demo, Q&A | prezentace, demo plan, speaker notes | jasnost, přesnost, strukturu a reakci na otázky |
| QA | Definition of Done, testy, issue verification | test plan, issues, regression/final checklist | kvalitu testování, reprodukovatelnost a ověření opravy |

## Pravidla

- Každý student má jednu primární roli; podpůrné role lze kombinovat podle velikosti týmu.
- Role určuje odpovědnost, nikoli zákaz spolupráce.
- Studentům doporučuj role podle Skill Mastery i podle growth opportunity; doporučení nesmí roli zablokovat.
- Critical QA nález musí být před finálním odevzdáním ověřen jako opravený.
- Peer feedback hodnotí pozorovatelné pracovní chování, ne osobnost.
MD;
file_put_contents($root.'/materials/PROJECT_ROLE_GUIDE.md',$roles."\n");

$csv = fopen($root.'/materials/CURRICULUM_OVERVIEW.csv','wb');
fputcsv($csv,['class','subject','lesson','title','goal','knowledge_links','content_ready','teacher_prepared_state']);

foreach ($specs as $cid=>$spec) {
    $dir=$root.'/materials/'.str_replace('class_','class_',$cid);
    if (!is_dir($dir)) mkdir($dir,0770,true);
    $defs=teacher_curriculum_checklist_definitions($cid);
    $check="# ".teacher_class_label($cid)." · Teacher checklist\n\n**Předmět:** ".$spec['subject']."  \n**Mastery:** ".$spec['mastery_note']."  \n**Capstone:** ".$spec['capstone']."\n\n";
    foreach($defs as $section){$check.="## ".$section['title']."\n\n";foreach($section['items'] as $text)$check.="- [ ] ".$text."\n";$check.="\n";}
    file_put_contents($dir.'/TEACHER_CHECKLIST.md',$check);

    $read="# ".teacher_class_label($cid)." · Lesson readiness checklist\n\nPoužij před každou hodinou. Interaktivní verze je v **Teacher Cockpit → Výuka**.\n\n";
    $lessons=teacher_curriculum_lessons($cid);
    foreach($lessons as $lesson){$r=teacher_curriculum_lesson_readiness($lesson);$n=(int)$lesson['number'];$read.="## ".str_pad((string)$n,2,'0',STR_PAD_LEFT)." · ".$lesson['title']."\n\n";$read.="**Cíl:** ".($lesson['goal']??'')."\n\n";$read.="- [ ] Cíl a success/evidence criteria jsou jasné.\n- [ ] Knowledge / zdroje jsou dostupné.\n- [ ] Praktický studentský výstup je připravený.\n- [ ] Je připravená podpora / nápověda bez prozrazení řešení.\n- [ ] Je připravené rozšíření pro rychlejší studenty.\n- [ ] Exit ticket / validace pochopení je připravená.\n";if(!empty($lesson['teacher_notes'])){$read.="\n**Poznámky z balíčku:**\n";foreach($lesson['teacher_notes'] as $note)if(trim((string)$note)!=='')$read.='- '.$note."\n";}$read.="\n";
        fputcsv($csv,[teacher_class_label($cid),$spec['subject'],$n,$lesson['title']??'',preg_replace('/\s+/u',' ',(string)($lesson['goal']??'')),implode(';',(array)($lesson['knowledge']??[])),$r['ready']?'yes':'review','']);
    }
    file_put_contents($dir.'/LESSON_READINESS_CHECKLIST.md',$read);
}
fclose($csv);

echo "Teacher curriculum materials generated.\n";
