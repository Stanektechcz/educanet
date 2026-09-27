<?php

declare(strict_types=1);

if (!isset($modules['class_2a']) || !is_array($modules['class_2a'])) {
    return [];
}

$base = $modules['class_2a'];
$base['name'] = '1.A';
$base['subject'] = 'Grafika a webdesign · základy';
$base['course_type'] = 'graphics_intro';
$base['code'] = '1AGRAF';
$base['intro'] = 'Startovací vizuální laboratoř pro první ročník: co je hierarchie, kontrast, grid, typografie a jak z jednoduché konfigurace převést návrh do Canvy.';
$base['lesson_note'] = '2 × 45 minut: rychlá vstupní diagnostika → vizuální knowledge tour → návrhový sandbox → převod do Canvy → kontrola a mini-prezentace. Nejde o známku; cílem je bezpečně pochopit základní principy.';
$base['diagnostic'] = [
    'responses' => 0,
    'average' => null,
    'focus' => ['hierarchie', 'kontrast', 'grid', 'typografie', 'barvy', 'export', 'práce s briefem'],
    'warning' => 'Pro 1.A zatím nejsou v dodaných datech vlastní diagnostické odpovědi. Modul je proto připraven jako obecný vstupní blok pro začátečníky.',
];
// Pro první ročník držíme test kratší a přístupnější.
$base['questions'] = array_slice($base['questions'], 0, 12);

return $base;
