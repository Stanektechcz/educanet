<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v56 · jedna cesta učení.
 *
 * Každá lekce má čtyři fáze v pevném pořadí:
 *   1. TEORIE   – témata lekce, jedno po druhém (výklad + ukázka)
 *   2. TEST     – kontrolní otázky právě k těmto tématům
 *   3. PROJEKT  – praktické zpracování krok za krokem
 *   4. ODEVZDÁNÍ– výstup učiteli, známka a body
 *
 * Fáze se odemykají postupně. „Dnešní hodina“ a „Materiály → lekce“ používají
 * stejný stroj, takže v aplikaci existuje jen jedna posloupnost, ne tři.
 */

const V56_TEST_PASS_PERCENT = 60;
const V56_XP = ['theory' => 10, 'test' => 40, 'project' => 15, 'submit' => 30];

// ---------------------------------------------------------------------------
// Fáze
// ---------------------------------------------------------------------------

function v56_phase_meta(): array
{
    return [
        'theory' => ['label' => tr('Teorie'), 'mark' => '1', 'lead' => tr('Projdi si témata lekce. Každé má vysvětlení, ukázku a příklad z praxe.')],
        'test' => ['label' => tr('Test'), 'mark' => '2', 'lead' => tr('Krátké ověření, že teorie sedí. Na postup stačí {percent} %.', ['percent' => V56_TEST_PASS_PERCENT])],
        'project' => ['label' => tr('Projekt'), 'mark' => '3', 'lead' => tr('Teď to použiješ. Postupuj po krocích a pracuj v doporučeném programu.')],
        'submit' => ['label' => tr('Odevzdání'), 'mark' => '4', 'lead' => tr('Odevzdej výstup. Učitel ti dá zpětnou vazbu, známku a body.')],
    ];
}

// ---------------------------------------------------------------------------
// Obsah lekce
// ---------------------------------------------------------------------------

/** Úvodní dvouhodinový blok (lekce 1) podle ročníku a předmětu. */
function v56_primary_lesson(string $classId, array $module): array
{
    $graphicsBasics = [
        'title' => 'Základy vizuální komunikace: první plakát',
        'goal' => 'Pochopit hierarchii, kontrast a kompozici natolik, aby divák pochopil sdělení do tří vteřin — a rovnou to použít na vlastním plakátu.',
        'topics' => ['hierarchy', 'contrast-color', 'composition', 'typography'],
        'steps' => [
            ['title' => 'Zadání a sběr podkladů', 'time' => '8 min', 'tasks' => [
                'Vyber si akci, na kterou plakát děláš (školní ples, turnaj, kroužek, brigáda).',
                'Zapiš si headline (max. 6 slov), datum, místo a jednu výzvu k akci.',
            ]],
            ['title' => 'Skica hierarchie', 'time' => '10 min', 'tasks' => [
                'Na papír nebo do Canvy nakresli tři obdélníky: co uvidí divák první, druhé a třetí.',
                'Hlavní prvek musí být aspoň 3× větší než nejmenší text.',
            ]],
            ['title' => 'Sestavení v Canvě', 'time' => '18 min', 'tasks' => [
                'Založ dokument A3 na výšku a rozvrhni okraje (min. 2 cm).',
                'Vlož headline, doplňkový text a CTA podle své skici.',
                'Použij maximálně dva fonty a tři barvy.',
            ]],
            ['title' => 'Kontrola a export', 'time' => '9 min', 'tasks' => [
                'Zkontroluj kontrast textu vůči pozadí (musí být čitelný i z 3 metrů).',
                'Exportuj do PDF pro tisk a do PNG pro web.',
            ]],
        ],
    ];
    $graphics = [
        'title' => 'Start: diagnostika a první grafický výstup',
        'goal' => 'Ověřit si, kde jsi, zopakovat principy hierarchie a kontrastu a vytvořit hotový plakát připravený k odevzdání.',
        'topics' => ['hierarchy', 'contrast-color', 'composition', 'export'],
        'steps' => [
            ['title' => 'Brief a cílová skupina', 'time' => '8 min', 'tasks' => [
                'Popiš jednou větou, komu plakát mluví a co má člověk udělat.',
                'Zapiš tři informace, které na plakátu být musí, a dvě, které být nemusí.',
            ]],
            ['title' => 'Kompoziční mřížka', 'time' => '10 min', 'tasks' => [
                'Rozvrhni plochu do mřížky a urči hlavní optický bod.',
                'Rozmísti headline, obraz a CTA tak, aby vedly oko shora dolů.',
            ]],
            ['title' => 'Vizuální zpracování', 'time' => '18 min', 'tasks' => [
                'Nastav typografickou škálu (headline / podnadpis / detail) s jasným rozdílem velikostí.',
                'Zvol barevnou dvojici s kontrastem alespoň 4,5:1 pro text.',
                'Dolaď mezery — prostor kolem hlavního prvku je součást hierarchie.',
            ]],
            ['title' => 'Preflight a export', 'time' => '9 min', 'tasks' => [
                'Zkontroluj překlepy, spadávku a rozlišení obrázků.',
                'Exportuj tiskové PDF a webové PNG a ulož odkaz na soubor.',
            ]],
        ],
    ];
    $networks = [
        'title' => 'Start: diagnostika a IP plán sítě',
        'goal' => 'Ověřit znalost adresace a služeb a navrhnout funkční IP plán malé sítě včetně důkazu, že funguje.',
        'topics' => ['ip-addressing', 'dns', 'dhcp', 'troubleshooting'],
        'steps' => [
            ['title' => 'Zadání sítě', 'time' => '8 min', 'tasks' => [
                'Navrhni síť pro učebnu: 20 stanic, 1 tiskárna, 1 server, 1 router.',
                'Zvol privátní rozsah a masku, která se do zadání vejde s rezervou.',
            ]],
            ['title' => 'IP plán', 'time' => '12 min', 'tasks' => [
                'Rozděl rozsah: statické adresy pro server, tiskárnu a bránu, zbytek pro DHCP pool.',
                'Zapiš tabulku: zařízení · adresa · maska · brána · DNS.',
            ]],
            ['title' => 'Ověření v praxi', 'time' => '16 min', 'tasks' => [
                'Zjisti konfiguraci svého stroje (ipconfig /all nebo ip a).',
                'Ověř bránu i DNS příkazy ping a nslookup a zapiš výstupy.',
                'Vysvětli, co by se stalo při špatné masce a při výpadku DNS.',
            ]],
            ['title' => 'Důkaz a závěr', 'time' => '9 min', 'tasks' => [
                'Ulož výstupy příkazů (screenshot nebo text) k IP plánu.',
                'Napiš dvě věty: co jsi ověřil/a a čím to prokazuješ.',
            ]],
        ],
    ];
    $networksAdvanced = [
        'title' => 'Start: diagnostika a řízený rozbor výpadku',
        'goal' => 'Ověřit pokročilou adresaci a služby a projít reálný výpadek metodou od vrstvy k vrstvě až k důkazu o příčině.',
        'topics' => ['cidr', 'dns-advanced', 'binding', 'diagnostics'],
        'steps' => [
            ['title' => 'Popis incidentu', 'time' => '8 min', 'tasks' => [
                'Zapiš příznak tak, jak by ho nahlásil uživatel, a doplň, co přesně nefunguje.',
                'Urči rozsah dopadu: jeden stroj, segment, nebo celá služba?',
            ]],
            ['title' => 'Hypotézy po vrstvách', 'time' => '12 min', 'tasks' => [
                'Sestav tři hypotézy od nejnižší vrstvy nahoru (linka → adresace → routing → DNS → služba).',
                'U každé napiš, jaký jeden příkaz ji potvrdí nebo vyvrátí.',
            ]],
            ['title' => 'Diagnostika', 'time' => '16 min', 'tasks' => [
                'Postupně hypotézy ověř (ip a, ip r, ss -tlnp, dig, curl -v, journalctl).',
                'Zapiš výstup, který hypotézu vyloučil, i ten, který příčinu potvrdil.',
            ]],
            ['title' => 'Nález a náprava', 'time' => '9 min', 'tasks' => [
                'Popiš příčinu jednou větou a navrhni opravu i prevenci.',
                'Přilož výstupy, které nález prokazují.',
            ]],
        ],
    ];

    return match ($classId) {
        'class_1a' => $graphicsBasics,
        'class_2a' => $graphics,
        'class_3a' => $networks,
        'class_4a' => $networksAdvanced,
        default => tut52_family($classId, $module) === 'graphics' ? $graphics : $networks,
    };
}

/** Témata lekce omezená na ta, která opravdu existují v knowledgebase třídy. */
function v56_topics_for(array $module, array $wanted): array
{
    $kb = (array)($module['knowledgebase'] ?? []);
    $out = [];
    foreach ($wanted as $key) {
        $key = (string)$key;
        if (!is_array($kb[$key] ?? null)) continue;
        $out[$key] = [
            'key' => $key,
            'title' => (string)($kb[$key]['title'] ?? $key),
            'summary' => (string)($kb[$key]['summary'] ?? ''),
            'body' => (array)($kb[$key]['body'] ?? []),
            'example' => (string)($kb[$key]['example'] ?? ''),
        ];
    }
    return $out;
}

/** Otázky k tématům lekce; když jich je málo, doplní se dalšími z ročníku. */
function v56_questions_for(array $module, array $topicKeys, int $min = 5, int $max = 8): array
{
    $all = array_values(array_filter((array)($module['questions'] ?? []), 'is_array'));
    $primary = [];
    $rest = [];
    foreach ($all as $q) {
        if (in_array((string)($q['kb'] ?? ''), $topicKeys, true)) $primary[] = $q; else $rest[] = $q;
    }
    $out = array_slice($primary, 0, $max);
    foreach ($rest as $q) {
        if (count($out) >= max($min, min($max, count($primary) + 2))) break;
        $out[] = $q;
    }
    return array_slice($out, 0, $max);
}

/** Kompletní obsah lekce: témata, test, kroky projektu a programy. */
function v56_lesson_bundle(string $classId, array $module, int $lessonNo, array $nextLessons, array $extendedLessons): array
{
    $lessonNo = max(1, $lessonNo);
    $source = null;
    if ($lessonNo === 2 && is_array($nextLessons[$classId] ?? null)) $source = $nextLessons[$classId];
    if ($lessonNo >= 3) {
        foreach ((array)($extendedLessons[$classId] ?? []) as $l) {
            if (is_array($l) && (int)($l['number'] ?? 0) === $lessonNo) { $source = $l; break; }
        }
    }

    if ($lessonNo === 1 || !is_array($source)) {
        $primary = v56_primary_lesson($classId, $module);
        $title = $lessonNo === 1 ? $primary['title'] : tr('Lekce {n}', ['n' => $lessonNo]);
        $goal = $lessonNo === 1 ? $primary['goal'] : (string)($module['lesson_note'] ?? '');
        $topicKeys = $primary['topics'];
        $steps = $primary['steps'];
    } else {
        $title = tut52_clean_title((string)($source['title'] ?? tr('Lekce {n}', ['n' => $lessonNo])));
        $goal = (string)($source['goal'] ?? '');
        $topicKeys = array_values(array_map('strval', (array)($source['knowledge'] ?? [])));
        $steps = [];
        foreach ((array)($source['steps'] ?? []) as $step) {
            if (!is_array($step)) continue;
            $steps[] = [
                'title' => trim((string)preg_replace('/^\d+\s*·\s*/u', '', (string)($step['title'] ?? ''))),
                'time' => (string)($step['time'] ?? ''),
                'tasks' => array_values(array_map('strval', (array)($step['tasks'] ?? []))),
            ];
        }
        foreach ((array)($source['worksheet'] ?? []) as $w) {
            $w = trim((string)$w);
            if ($w !== '') $steps[] = ['title' => $w, 'time' => '', 'tasks' => []];
        }
    }

    $topics = v56_topics_for($module, $topicKeys);
    if (!$topics) $topics = array_slice(v56_topics_for($module, array_keys((array)($module['knowledgebase'] ?? []))), 0, 4, true);
    $steps = array_values(array_filter($steps, static fn(array $s): bool => trim((string)$s['title']) !== ''));
    if (!$steps) $steps = v56_primary_lesson($classId, $module)['steps'];

    $family = tut52_family($classId, $module);
    return [
        'number' => $lessonNo,
        'title' => $title,
        'goal' => $goal,
        'topics' => $topics,
        'questions' => v56_questions_for($module, array_keys($topics)),
        'steps' => $steps,
        'tools' => array_slice(tut52_tools($family), 0, 3, true),
        'family' => $family,
    ];
}

// ---------------------------------------------------------------------------
// Postup žáka
// ---------------------------------------------------------------------------

function v56_progress_path(): string
{
    return STORAGE_DIR . '/progress_v56.json.php';
}

function v56_progress_key(string $classId, string $studentKey): string
{
    return $classId . '|' . $studentKey;
}

function v56_progress(string $classId, string $studentKey, int $lessonNo): array
{
    $all = load_php_json(v56_progress_path());
    $row = $all[v56_progress_key($classId, $studentKey)][(string)$lessonNo] ?? null;
    return array_replace(['theory' => [], 'test' => [], 'project' => [], 'submit' => []], is_array($row) ? $row : []);
}

function v56_progress_write(string $classId, string $studentKey, int $lessonNo, array $row): void
{
    $k = v56_progress_key($classId, $studentKey);
    // Pod zámkem se mění jen tato lekce tohoto žáka – souběžné zápisy spolužáků se neztratí.
    storage_update(v56_progress_path(), static function (array $all) use ($k, $lessonNo, $row): array {
        if (!is_array($all[$k] ?? null)) $all[$k] = [];
        $all[$k][(string)$lessonNo] = $row;
        return $all;
    });
}

function v56_mark_theory(string $classId, string $studentKey, int $lessonNo, string $topic): void
{
    $row = v56_progress($classId, $studentKey, $lessonNo);
    if (!isset($row['theory'][$topic])) {
        $row['theory'][$topic] = date(DATE_ATOM);
        v56_progress_write($classId, $studentKey, $lessonNo, $row);
        learning_award_once($classId, 'v56:l' . $lessonNo . ':theory:' . $topic, V56_XP['theory']);
        learning_set_kb_step($classId, $topic, 'visual', true);
    }
}

/** Vyhodnotí test lekce a uloží výsledek. */
function v56_submit_test(string $classId, string $studentKey, int $lessonNo, array $questions, array $answers): array
{
    $detail = [];
    $score = 0;
    foreach ($questions as $i => $q) {
        $given = (string)($answers[(string)$i] ?? $answers[$i] ?? '');
        $correct = (string)($q['correct'] ?? '');
        $ok = $given !== '' && $given === $correct;
        if ($ok) $score++;
        $detail[] = ['id' => (string)($q['id'] ?? $i), 'given' => $given, 'correct' => $correct, 'ok' => $ok];
    }
    $max = max(1, count($questions));
    $percent = (int)round($score / $max * 100);
    $passed = $percent >= V56_TEST_PASS_PERCENT;
    $row = v56_progress($classId, $studentKey, $lessonNo);
    $attempts = (int)($row['test']['attempts'] ?? 0) + 1;
    $best = max($percent, (int)($row['test']['percent'] ?? 0));
    $row['test'] = [
        'score' => $score, 'max' => $max, 'percent' => $percent, 'best' => $best,
        'passed' => $passed || !empty($row['test']['passed']), 'attempts' => $attempts,
        'detail' => $detail, 'at' => date(DATE_ATOM),
    ];
    v56_progress_write($classId, $studentKey, $lessonNo, $row);
    if ($row['test']['passed']) learning_award_once($classId, 'v56:l' . $lessonNo . ':test', V56_XP['test']);
    return $row['test'];
}

function v56_toggle_project_step(string $classId, string $studentKey, int $lessonNo, int $step, bool $done): void
{
    if ($done) {
        $gate = v56_lab_gate($classId, $lessonNo);
        if ($gate !== null && $gate['step'] === $step && v56_lab_gate_proof($classId, $studentKey, $gate) === null) {
            throw new RuntimeException(tr('Tenhle krok potřebuje vyřešenou úroveň Linux Labu „{title}“. Vyřeš ji a vrať se zpátky.', ['title' => (string)$gate['title']]));
        }
    }
    $row = v56_progress($classId, $studentKey, $lessonNo);
    if ($done) {
        $row['project'][(string)$step] = date(DATE_ATOM);
        learning_award_once($classId, 'v56:l' . $lessonNo . ':step:' . $step, V56_XP['project']);
    } else {
        unset($row['project'][(string)$step]);
    }
    v56_progress_write($classId, $studentKey, $lessonNo, $row);
}

// ---------------------------------------------------------------------------
// EDU-01: krok projektu vyžadující vyřešenou úroveň Linux Labu (3.A/4.A OS a sítě)
// ---------------------------------------------------------------------------

/**
 * Téma lekce → úroveň Linux Labu, která ho prakticky ověří. Pořadí v poli je priorita
 * (pokročilejší/diagnostická témata první), ne pořadí témat v lekci.
 */
const V56_LAB_GATE_TOPICS = [
    'dns-advanced' => 'sit-6',
    'diagnostics' => 'sit-8',
    'troubleshooting' => 'sit-4',
    'dns' => 'sit-6',
    'dhcp' => 'sit-5',
    'cidr' => 'sit-1',
    'ip-addressing' => 'sit-1',
];

/** Krok projektu (index v poli kroků), který v úvodní lekci prakticky ověřuje síťovou diagnostiku. */
const V56_LAB_GATE_STEP = 2;

/**
 * Zajistí, že jsou funkce Linux Labu k dispozici, i když aktuální trasa (např. POST odevzdání
 * projektu v app/actions/lesson_path.php) skupinu knihoven Labu líně nenačetla – zámek EDU-01 musí
 * platit vždy, ne jen na stránce Labu. require_once je bezpečné volat opakovaně v rámci requestu.
 */
function v56_lab_ensure_loaded(): void
{
    if (function_exists('lab57_level')) return;
    $file = __DIR__ . '/linux_v57_lab.php';
    if (is_file($file)) require_once $file;
}

/**
 * Úroveň Labu, kterou musí mít žák vyřešenou, aby mohl označit ověřovací krok projektu za hotový.
 * Zatím jen lekce 1 (diagnostický úvod, obsah je pevně daný v v56_primary_lesson) tříd s výukou
 * sítí – u dalších lekcí obsah pochází z tutorial_v52 a zámek by nebylo možné spolehlivě otestovat.
 * Bez souboru Lab vrstvy (nemělo by nastat, viz v56_lab_ensure_loaded) se vrací null – žádný zámek.
 */
function v56_lab_gate(string $classId, int $lessonNo): ?array
{
    if ($lessonNo !== 1 || !in_array($classId, ['class_3a', 'class_4a'], true)) return null;
    v56_lab_ensure_loaded();
    if (!function_exists('lab57_level')) return null;
    $topics = (array)v56_primary_lesson($classId, [])['topics'];
    foreach (V56_LAB_GATE_TOPICS as $topic => $levelId) {
        if (!in_array($topic, $topics, true)) continue;
        $level = lab57_level($levelId);
        if ($level === null) continue;
        return ['step' => V56_LAB_GATE_STEP, 'level' => $levelId, 'title' => (string)$level['title']];
    }
    return null;
}

/** Důkaz vyřešení úrovně Labu (bez kódu): úroveň a čas vyřešení, nebo null. */
function v56_lab_gate_proof(string $classId, string $studentKey, array $gate): ?array
{
    v56_lab_ensure_loaded();
    if (!function_exists('lab57_solved')) return null;
    $info = lab57_solved($classId, $studentKey, 'practice')[(string)$gate['level']] ?? null;
    if (!is_array($info)) return null;
    return ['level' => (string)$gate['level'], 'title' => (string)$gate['title'], 'at' => (string)($info['at'] ?? '')];
}

/** Volitelný ukazatel na balíček „Terminál pro grafiky“ (LAB-07) pro 1.A/2.A – null, pokud ještě neexistuje. */
function v56_lab_optional_pack(string $classId): ?array
{
    if (!in_array($classId, ['class_1a', 'class_2a'], true)) return null;
    v56_lab_ensure_loaded();
    if (!function_exists('lab58_pack_allows_class')) return null;
    $pack = function_exists('lab58_pack') ? lab58_pack('grafika') : null;
    if ($pack === null || !lab58_pack_allows_class('grafika', $classId)) return null;
    return ['id' => 'grafika', 'title' => (string)$pack['title'], 'description' => (string)$pack['description']];
}

function v56_save_submit(string $classId, string $studentKey, int $lessonNo, string $note, string $link, bool $final): array
{
    $row = v56_progress($classId, $studentKey, $lessonNo);
    $row['submit'] = [
        'note' => intake_v51_text($note, 4000),
        'link' => safe_url(intake_v51_text($link, 500)),
        'done' => $final || !empty($row['submit']['done']),
        'at' => date(DATE_ATOM),
    ];
    v56_progress_write($classId, $studentKey, $lessonNo, $row);
    if ($row['submit']['done']) learning_award_once($classId, 'v56:l' . $lessonNo . ':submit', V56_XP['submit']);
    return $row['submit'];
}

// ---------------------------------------------------------------------------
// Stav lekce = jediný zdroj pravdy o tom, co je další krok
// ---------------------------------------------------------------------------

function v56_lesson_state(string $classId, string $studentKey, array $bundle): array
{
    $lessonNo = (int)$bundle['number'];
    $p = v56_progress($classId, $studentKey, $lessonNo);
    $topics = array_keys((array)$bundle['topics']);
    $readTopics = array_values(array_filter($topics, static fn(string $t): bool => !empty($p['theory'][$t])));
    $steps = (array)$bundle['steps'];
    $doneSteps = 0;
    foreach (array_keys($steps) as $i) if (!empty($p['project'][(string)$i])) $doneSteps++;

    $phases = [];
    $phases['theory'] = [
        'id' => 'theory', 'done' => $topics !== [] && count($readTopics) >= count($topics),
        'progress' => count($readTopics), 'total' => max(1, count($topics)),
    ];
    $phases['test'] = [
        'id' => 'test', 'done' => !empty($p['test']['passed']),
        'progress' => (int)($p['test']['best'] ?? 0), 'total' => 100, 'result' => $p['test'],
    ];
    $phases['project'] = [
        'id' => 'project', 'done' => $steps !== [] && $doneSteps >= count($steps),
        'progress' => $doneSteps, 'total' => max(1, count($steps)),
    ];
    $phases['submit'] = [
        'id' => 'submit', 'done' => !empty($p['submit']['done']),
        'progress' => !empty($p['submit']['done']) ? 1 : 0, 'total' => 1, 'result' => $p['submit'],
    ];

    $current = null;
    foreach ($phases as $id => $phase) if ($current === null && empty($phase['done'])) $current = (string)$id;
    $doneCount = count(array_filter($phases, static fn(array $x): bool => !empty($x['done'])));
    $meta = v56_phase_meta();
    foreach ($phases as $id => $phase) {
        $phases[$id]['label'] = (string)$meta[$id]['label'];
        $phases[$id]['lead'] = (string)$meta[$id]['lead'];
        $phases[$id]['state'] = !empty($phase['done']) ? 'done' : ($id === $current ? 'current' : 'locked');
    }
    $gate = v56_lab_gate($classId, $lessonNo);
    $labGate = null;
    if ($gate !== null) {
        $proof = v56_lab_gate_proof($classId, $studentKey, $gate);
        $labGate = ['step' => (int)$gate['step'], 'level' => (string)$gate['level'], 'title' => (string)$gate['title'], 'locked' => $proof === null, 'proof' => $proof];
    }
    return [
        'lesson' => $lessonNo,
        'phases' => $phases,
        'current' => $current,
        'done' => $doneCount,
        'total' => count($phases),
        'complete' => $current === null,
        'percent' => (int)round($doneCount / count($phases) * 100),
        'progress' => $p,
        'lab_gate' => $labGate,
        'next_topic' => (static function () use ($topics, $p): string {
            foreach ($topics as $t) if (empty($p['theory'][$t])) return (string)$t;
            return '';
        })(),
    ];
}

/** Jediné „Pokračovat“ v celé aplikaci. */
function v56_next_step(string $classId, array $module, array $nextLessons, array $extendedLessons, array $schoolYear): array
{
    $studentKey = adaptive_student_key($classId);
    $today = sess53_for_class_date($classId, date('Y-m-d'));
    if (is_array($today) && !empty($today['open'])) {
        if ((string)($today['kind'] ?? '') === 'intake') {
            return ['href' => '?view=intake', 'label' => tr('Pokračovat v dnešní hodině'), 'detail' => tr('Seznamovací dotazník')];
        }
        return ['href' => '?view=hodina', 'label' => tr('Pokračovat v dnešní hodině'), 'detail' => (string)$today['title']];
    }
    $lessonNo = v56_current_lesson_number($classId, $schoolYear);
    $bundle = v56_lesson_bundle($classId, $module, $lessonNo, $nextLessons, $extendedLessons);
    $state = v56_lesson_state($classId, $studentKey, $bundle);
    $phase = $state['current'] ?? 'theory';
    $label = $state['complete'] ? tr('Otevřít další lekci') : tr('Pokračovat · {phase}', ['phase' => (string)$state['phases'][$phase]['label']]);
    $target = $state['complete'] ? min(28, $lessonNo + 1) : $lessonNo;
    return ['href' => v56_lesson_url($target, $state['complete'] ? null : $phase), 'label' => $label, 'detail' => tr('Lekce {n} · {title}', ['n' => $target, 'title' => (string)$bundle['title']])];
}

/** Číslo lekce, která je podle kalendáře aktuální. */
function v56_current_lesson_number(string $classId, array $schoolYear): int
{
    $today = date('Y-m-d');
    $last = 1;
    foreach (adaptive_school_year_rows($schoolYear, $classId) as $row) {
        if (!is_array($row) || (string)($row['status'] ?? '') !== 'teaching') continue;
        $n = (int)($row['lesson_number'] ?? 0);
        if ($n <= 0) continue;
        if ((string)($row['date'] ?? '') <= $today) $last = $n;
    }
    return max(1, min(28, $last));
}

function v56_lesson_url(int $lessonNo, ?string $phase = null): string
{
    $url = '?view=lekce&n=' . max(1, $lessonNo);
    return $phase !== null && $phase !== '' ? $url . '&faze=' . $phase : $url;
}

// ---------------------------------------------------------------------------
// Materiály
// ---------------------------------------------------------------------------

/** Přehled lekcí pro knihovnu materiálů: stav, termín, témata. */
function v56_materials_index(string $classId, array $module, array $nextLessons, array $extendedLessons, array $schoolYear): array
{
    $studentKey = adaptive_student_key($classId);
    $dates = tut52_lesson_dates($schoolYear, $classId);
    $currentNo = v56_current_lesson_number($classId, $schoolYear);
    $out = [];
    for ($n = 1; $n <= 28; $n++) {
        $bundle = v56_lesson_bundle($classId, $module, $n, $nextLessons, $extendedLessons);
        $state = v56_lesson_state($classId, $studentKey, $bundle);
        $out[$n] = [
            'number' => $n,
            'title' => (string)$bundle['title'],
            'goal' => (string)$bundle['goal'],
            'topics' => array_map(static fn(array $t): string => (string)$t['title'], (array)$bundle['topics']),
            'date' => (string)($dates[$n] ?? ''),
            'percent' => (int)$state['percent'],
            'complete' => (bool)$state['complete'],
            'current' => $n === $currentNo,
            'available' => $n <= $currentNo,
        ];
    }
    return $out;
}
