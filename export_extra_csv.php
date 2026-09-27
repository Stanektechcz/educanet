<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!teacher_export_configured()) {
    http_response_code(503);
    exit('Učitelský export není nakonfigurovaný.');
}
if (!teacher_export_authenticated()) {
    http_response_code(403);
    exit('Přístup odmítnut. Nejprve se přihlas na teacher.php.');
}

$filter = isset($_GET['class']) && is_string($_GET['class']) ? $_GET['class'] : 'all';
if (!in_array($filter, ['all', 'class_3a', 'class_4a'], true)) {
    http_response_code(400);
    exit('Neplatný filtr třídy.');
}
// v59 · AUTHZ58-07: „all“ = třídy v rozsahu učitele ∩ {3.A, 4.A}; výslovně zvolená cizí třída → 403 (legacy = obě).
$inScope = static fn(string $classId): bool => !function_exists('teacher59_can_class') || teacher59_can_class($classId);
if ($filter !== 'all' && !$inScope($filter)) {
    http_response_code(403);
    exit('K této třídě nemáte přístup.');
}

$classNames = [
    'class_3a' => '3.A',
    'class_4a' => '4.A',
];
$records = storage_stream_rows('extra_submissions');
$rows = [];
foreach ($records as $record) {
    if (!is_array($record)) { continue; }
    $classId = (string)($record['class_id'] ?? '');
    if (!isset($classNames[$classId])) { continue; }
    if ($filter !== 'all' && $classId !== $filter) { continue; }
    if (!$inScope($classId)) { continue; }

    $points = extra_submission_points($record);
    $grade = extra_submission_grade($record);
    $status = (string)($record['status'] ?? '');
    $rows[] = [
        'student' => csv_safe_cell((string)($record['student_label'] ?? '')),
        'class' => $classNames[$classId],
        'points' => $points === null ? '' : (string)$points,
        'max_points' => (string)((int)($record['max_points'] ?? 25)),
        'grade' => $grade === null ? '' : (string)$grade,
        'status' => extra_status_label($status),
        'submitted_at' => (string)($record['submitted_at'] ?? ''),
        'challenge' => csv_safe_cell((string)($record['challenge_title'] ?? 'Extra challenge')),
    ];
}

usort($rows, static fn(array $a, array $b): int => strcmp($a['submitted_at'], $b['submitted_at']));

$slug = $filter === 'all' ? '3A-4A' : ($filter === 'class_3a' ? '3A' : '4A');
$filename = 'extra-challenge-' . $slug . '-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$out = fopen('php://output', 'wb');
if ($out === false) {
    http_response_code(500);
    exit('Nelze vytvořit CSV export.');
}
// UTF-8 BOM pro bezproblémové otevření českých znaků v Excelu.
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Jméno studenta', 'Třída', 'Body', 'Maximum', 'Známka', 'Stav hodnocení', 'Odevzdáno', 'Extra úkol'], ';', '"', '\\');
foreach ($rows as $row) {
    fputcsv($out, array_values($row), ';', '"', '\\');
}
fclose($out);
