<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$lessons = require $root . '/lessons_v30.php';
$school = require $root . '/school_year.php';

$classMeta = [
    'class_1a' => ['label'=>'1.A','subject'=>'Grafika a webdesign · základy'],
    'class_2a' => ['label'=>'2.A','subject'=>'Grafika a webdesign'],
    'class_3a' => ['label'=>'3.A','subject'=>'Seminář operačních systémů a počítačových sítí'],
    'class_4a' => ['label'=>'4.A','subject'=>'Seminář operačních systémů a počítačových sítí'],
];

function section_replace(string $path, string $marker, string $content): void {
    $start = "\n<!-- {$marker}:START -->\n";
    $end = "\n<!-- {$marker}:END -->\n";
    $old = is_file($path) ? (string)file_get_contents($path) : '';
    $block = $start . rtrim($content) . $end;
    $p1 = strpos($old, $start);
    $p2 = strpos($old, $end);
    if ($p1 !== false && $p2 !== false && $p2 > $p1) {
        $old = substr($old, 0, $p1) . $block . substr($old, $p2 + strlen($end));
    } else {
        $old = rtrim($old) . "\n" . $block;
    }
    file_put_contents($path, $old);
}

function short_title(array $lesson): string {
    $t = (string)($lesson['title'] ?? '');
    return preg_replace('/^Lekce\s+\d+\s*[·-]\s*/u', '', $t) ?: $t;
}

function list_md(array $items): string {
    $out=''; foreach ($items as $x) $out .= '- ' . trim((string)$x) . "\n"; return $out;
}

foreach ($classMeta as $cid => $meta) {
    $dir = $root . '/materials/' . $cid;
    $rows = (array)($lessons[$cid] ?? []);

    $course = "## v30 · Lekce 19–28 — hlubší pochopení a aplikace\n\n";
    $course .= "Tyto bloky používají společný model **animace → předpověď → alternativní vysvětlení → guided practice → samostatná aplikace → QA → exit ticket**. Help Ladder je kdykoli dostupný a jeho použití není penalizované.\n\n";
    foreach ($rows as $lesson) {
        $n=(int)($lesson['number']??0); $course .= "### {$n}. " . short_title($lesson) . "\n";
        $course .= trim((string)($lesson['goal']??'')) . "\n\n";
        $course .= '**Knowledge:** ' . implode(', ', (array)($lesson['knowledge']??[])) . "\n\n";
        $course .= "**Evidence / pracovní záznam:**\n" . list_md((array)($lesson['worksheet']??[])) . "\n";
    }
    section_replace($dir.'/COURSE_MAP.md','V30_LESSONS',$course);

    $teacher = "## v30 · Teacher Guide — lekce 19–28\n\n";
    $teacher .= "### Adaptivní rutina pro každou lekci\n\n";
    $teacher .= "1. Nejprve nech studenta **předpovědět**, co se stane.\n2. Ukaž animovaný model pouze jako krátkou mentální mapu.\n3. Pokud tápe, nepřeříkávej totéž: použij Help Ladder v pořadí **jednoduše → přirovnání → krok za krokem → konkrétní příklad → typická chyba → mini-pokus**.\n4. Po nápovědě musí vždy následovat malý samostatný krok.\n5. Hodnoť evidence a schopnost vysvětlit rozhodnutí, ne počet použitých nápověd.\n\n";
    foreach ($rows as $lesson) {
        $n=(int)($lesson['number']??0); $teacher .= "### Lekce {$n} — " . short_title($lesson) . "\n";
        $teacher .= '**Cíl:** ' . trim((string)($lesson['goal']??'')) . "\n\n**90 minut:**\n";
        foreach ((array)($lesson['schedule']??[]) as $s) {
            $teacher .= '- **'.($s['time']??'').' · '.($s['title']??'').'** — '.($s['text']??'')."\n";
        }
        $teacher .= "\n**Poznámky pro učitele:**\n" . list_md((array)($lesson['teacher_notes']??[]));
        $teacher .= "\n**Evidence k uzavření:**\n" . list_md((array)($lesson['worksheet']??[])) . "\n";
    }
    section_replace($dir.'/TEACHER_GUIDE.md','V30_LESSONS',$teacher);

    $workbook = "## v30 · Student Workbook — lekce 19–28\n\n";
    $workbook .= "> Když něčemu nerozumíš, použij **Vysvětli jinak**. Nápověda není selhání. Po každém vysvětlení ale udělej vlastní mini-pokus a zapiš, co jsi zjistil/a.\n\n";
    foreach ($rows as $lesson) {
        $n=(int)($lesson['number']??0); $workbook .= "### Lekce {$n} · " . short_title($lesson) . "\n\n";
        $workbook .= '**Cíl:** ' . trim((string)($lesson['goal']??'')) . "\n\n";
        $workbook .= "#### Než začnu\n- Moje předpověď / co si myslím, že se stane: ______________________________\n- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus\n\n";
        $workbook .= "#### Evidence\n";
        foreach ((array)($lesson['worksheet']??[]) as $x) $workbook .= '- [ ] '.trim((string)$x)."\n";
        $workbook .= "\n#### Umím to vysvětlit?\n- Princip vlastními slovy: ________________________________________________\n- Jeden konkrétní důkaz / příklad: _______________________________________\n- Co ještě potřebuji vysvětlit jinak: ____________________________________\n\n";
    }
    section_replace($dir.'/STUDENT_WORKBOOK.md','V30_LESSONS',$workbook);

    $bank = "## v30 · Assessment Bank — lekce 19–28\n\n";
    $bank .= "Otázky jsou určené hlavně jako **retrieval practice a exit tickets**. Pokud student odpoví špatně, vrať jej k jiné formě vysvětlení a potom nabídni novou aplikaci stejného principu, ne pouze stejnou otázku.\n\n";
    foreach ($rows as $lesson) {
        $n=(int)($lesson['number']??0);
        foreach ((array)($lesson['steps']??[]) as $step) {
            if (($step['kind']??'')!=='quiz' || !isset($step['question'])) continue;
            $bank .= "### Lekce {$n} · " . short_title($lesson) . ' / ' . ($step['title']??'Check') . "\n";
            $bank .= trim((string)$step['question'])."\n\n";
            foreach ((array)($step['options']??[]) as $i=>$opt) $bank .= '- '.chr(65+$i).'. '.trim((string)$opt)."\n";
            $correct=(int)($step['correct']??0); $opts=(array)($step['options']??[]);
            $bank .= "\n**Správně:** ".chr(65+$correct).' — '.trim((string)($step['explanation']??($opts[$correct]??'')))."\n\n";
        }
    }
    section_replace($dir.'/ASSESSMENT_BANK.md','V30_LESSONS',$bank);

    $tail = array_values(array_filter((array)($school['calendar']??[]), static fn($r)=>is_array($r) && ($r['status']??'')==='teaching' && !isset($r['lesson_number'])));
    $brief = "## v30 · Aplikované bloky 29–40\n\n";
    $brief .= "Tyto středy nejsou dalšími deseti novými tématy. Slouží k **přenesení znalostí do projektu, Mastery, obhajoby, reflexe, portfolia a individuální podpory**. Pokud výuka odpadne kvůli nepředvídané školní akci, použij nejbližší rezervní/aplikační blok místo přeskočení guided practice.\n\n";
    foreach ($tail as $i=>$row) {
        $date=(string)($row['date']??''); $title=(string)($row['title']??('Aplikovaný blok '.($i+29)));
        $focus=(string)(($row['class_focus'][$cid]??'') ?: 'Projekt / Mastery / reflexe');
        $brief .= '### '.($i+29).'. '.$date.' · '.$title."\n**Zaměření pro {$meta['label']}:** {$focus}\n\n";
    }
    section_replace($dir.'/PROJECT_BRIEFS.md','V30_APPLIED_BLOCKS',$brief);
}

$adaptive = <<<'MD'
# Adaptive Explanation Guide · EDUCANET v30

## Cíl

Každý student může stejný princip pochopit jinou cestou. EDUCANET proto neopakuje stále stejný odstavec, ale nabízí **více reprezentací téhož konceptu**. Nejde o pevné „learning styles“ ani o nálepkování studenta; student si v danou chvíli zvolí formu, která mu pomůže udělat další samostatný krok.

## Help Ladder

1. **Animovaná mentální mapa** — krátce ukaž, co se mění a co zůstává stejné.
2. **Jednoduše** — jedna myšlenka bez odborného balastu.
3. **Přirovnání** — známá situace ze života, která zachová podstatné vztahy.
4. **Krok za krokem** — problém se rozdělí na malé rozhodovací kroky.
5. **Konkrétní příklad** — jeden úplný případ od zadání po ověření.
6. **Typická chyba** — ukaž, proč intuitivní, ale chybný postup selže.
7. **Zkus si to** — malý samostatný úkol s okamžitou zpětnou vazbou.
8. **Jiný zdroj** — volitelný video/reference materiál, pokud interní vysvětlení nestačí.

## Pravidla

- Použití nápovědy **nesnižuje známku ani Skill Mastery**. Mastery se odvozuje od následné evidence, ne od počtu žádostí o pomoc.
- Nápověda nesmí rovnou prozradit odpověď do hodnocené úlohy.
- Po vysvětlení vždy následuje **retrieval / prediction / mini-pokus**, jinak student pouze pasivně sleduje.
- Pokud student třikrát střídá vysvětlení a stále neuspěje v jednoduchém checku, doporuč krátkou intervenci učitele nebo spolužáka.
- Učitel může stejný koncept demonstrovat fyzicky, na tabuli nebo v jiné aplikaci; digitální animace není jediná správná cesta.
- `prefers-reduced-motion` vypíná opakované animace; informace musí zůstat čitelná i ve statické podobě.

## Vzor pro grafiku / webdesign

**Cíl → rozhodnutí → test → iterace.** Student nejprve pojmenuje komunikační cíl, potom udělá jednu návrhovou změnu, otestuje ji a teprve podle evidence iteruje.

## Vzor pro OS / sítě

**Symptom → hypotéza → nejmenší test → důkaz → bezpečná změna → validace.** Student nemění konfiguraci naslepo; každá změna musí mít předchozí evidence a následné ověření.

## Jak poznat skutečné pochopení

Student dokáže:
- princip vysvětlit vlastními slovy,
- použít ho na nový příklad,
- rozpoznat typickou chybu,
- uvést důkaz, proč jeho řešení dává smysl,
- a při změně zadání upravit postup místo memorování jedné odpovědi.
MD;
file_put_contents($root.'/materials/ADAPTIVE_EXPLANATION_GUIDE.md', $adaptive."\n");

echo "v30 learning materials generated\n";
