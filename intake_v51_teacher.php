<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Učitelská část v51: dotazník z V1 – analytika, zasedací pořádek, profily, aktivační kódy, export. */

function intake_v51_teacher_url(array $params = []): string
{
    return 'teacher.php?' . http_build_query(array_merge(['tab' => 'intake'], $params));
}

function intake_v51_teacher_handle_post(string $action, array $modules): void
{
    if (!str_starts_with($action, 'intake_t_')) return;
    teacher_require_permission('students.manage');
    $classes = intake_v51_classes($modules);
    $classId = (string)($_POST['class_id'] ?? '');
    if ($action !== 'intake_t_sync' && !isset($classes[$classId])) throw new RuntimeException('Neznámá třída.');

    if ($action === 'intake_t_sync') {
        // v59 · AUTHZ58-07: import z V1 sahá na všechny třídy – jen administrátor (legacy beze změny).
        if (function_exists('teacher59_is_admin') && !teacher59_is_admin()) throw new RuntimeException('Import z V1 spouští jen administrátor.');
        $stats = intake_v51_sync_v1($modules, true);
        teacher_flash('Import z V1 dokončen: nově ' . $stats['imported'] . ' odpovědí, ' . $stats['files'] . ' souborů, ' . $stats['activations'] . ' nových aktivačních kódů.');
        teacher_redirect(['tab' => 'intake']);
    }
    if ($action === 'intake_t_toggle') {
        intake_v51_update('classes', static function (array $rows) use ($classId, $classes): array {
            $rows[$classId] = array_merge((array)($rows[$classId] ?? []), ['open' => empty($classes[$classId]['open'])]);
            return $rows;
        });
        teacher_flash(empty($classes[$classId]['open']) ? 'Dotazník je otevřený.' : 'Dotazník je uzavřený.');
        teacher_redirect(['tab' => 'intake', 'class' => $classId, 'view' => 'settings']);
    }
    if ($action === 'intake_t_save') {
        $rows = max(1, min(12, (int)($_POST['rows'] ?? 8)));
        $cols = max(1, min(10, (int)($_POST['cols'] ?? 6)));
        $raw = json_decode((string)($_POST['seat_map'] ?? '[]'), true);
        $active = [];
        foreach (is_array($raw) ? $raw : [] as $seat) {
            $r = (int)($seat['row'] ?? 0); $d = (int)($seat['desk'] ?? 0);
            if ($r >= 1 && $r <= $rows && $d >= 1 && $d <= $cols) $active[$r . ':' . $d] = ($active[$r . ':' . $d] ?? false) || !empty($seat['active']);
        }
        $seats = intake_v51_build_seats($rows, $cols, $active);
        if (!array_filter($seats, static fn(array $s): bool => $s['active'])) throw new RuntimeException('V učebně musí zůstat alespoň jedna lavice.');
        $intro = trim(u_substr((string)($_POST['intro'] ?? ''), 0, 1000));
        intake_v51_update('classes', static function (array $stored) use ($classId, $rows, $cols, $seats, $intro): array {
            $stored[$classId] = array_merge((array)($stored[$classId] ?? []), ['rows' => $rows, 'cols' => $cols, 'seats' => $seats, 'intro' => $intro, 'updated_at' => date(DATE_ATOM)]);
            return $stored;
        });
        teacher_flash('Nastavení dotazníku a učebny je uložené.');
        teacher_redirect(['tab' => 'intake', 'class' => $classId, 'view' => 'settings']);
    }
    if ($action === 'intake_t_delete') {
        $responseId = (string)($_POST['response_id'] ?? '');
        $removed = null;
        intake_v51_update('responses', static function (array $rows) use ($responseId, &$removed): array {
            return array_values(array_filter($rows, static function ($r) use ($responseId, &$removed): bool {
                if (is_array($r) && (string)($r['id'] ?? '') === $responseId) {
                    // v59: odpověď cizí třídy se nesmaže (výjimka zruší celý zápis).
                    if (function_exists('teacher59_can_class') && !teacher59_can_class((string)($r['class_id'] ?? ''))) throw new RuntimeException('K této odpovědi nemáte přístup.');
                    $removed = $r; return false;
                }
                return true;
            }));
        });
        if (is_array($removed)) {
            intake_v51_delete_upload($removed['assessment']['artifact']['storage_name'] ?? null);
            // Import z V1 by záznam při další synchronizaci vrátil – zapamatujeme si smazání.
            if (($removed['source'] ?? '') === 'V1') {
                intake_v51_update('import_state', static function (array $s) use ($responseId): array { $s['deleted'][] = $responseId; return $s; });
            }
        }
        teacher_flash('Odpověď byla smazána a místo je znovu volné.');
        teacher_redirect(['tab' => 'intake', 'class' => $classId, 'view' => 'students']);
    }
    if ($action === 'intake_t_regen') {
        $studentKey = (string)($_POST['student_key'] ?? '');
        $activation = intake_v51_activations()[$studentKey] ?? null;
        // v59: kód žáka cizí třídy se nepřegeneruje (chybějící záznam ohlásí intake_v51_regenerate_activation jako dosud).
        if (is_array($activation) && function_exists('teacher59_can_class') && !teacher59_can_class((string)($activation['class_id'] ?? ''))) throw new RuntimeException('K tomuto aktivačnímu kódu nemáte přístup.');
        $code = intake_v51_regenerate_activation($studentKey);
        teacher_flash('Nový aktivační kód: ' . $code . '. Původní kód přestal platit.');
        teacher_redirect(['tab' => 'intake', 'class' => $classId, 'view' => 'codes']);
    }
}

/** Export a otevření souborů – musí proběhnout před HTML výstupem. */
function intake_v51_teacher_handle_get(array $modules): void
{
    if ((string)($_GET['tab'] ?? '') !== 'intake') return;
    $classes = intake_v51_classes($modules);
    if (isset($_GET['artifact'])) {
        foreach (intake_v51_responses() as $response) {
            if ((string)($response['id'] ?? '') !== (string)$_GET['artifact']) continue;
            // v59: soubor jen z třídy v rozsahu učitele.
            if (function_exists('teacher59_can_class') && !teacher59_can_class((string)($response['class_id'] ?? ''))) { http_response_code(403); exit('K tomuto souboru nemáte přístup.'); }
            intake_v51_stream_artifact($response);
        }
        http_response_code(404); exit('Soubor nebyl nalezen.');
    }
    $classId = (string)($_GET['class'] ?? '');
    $format = (string)($_GET['export'] ?? '');
    if ($format === '' || !isset($classes[$classId])) return;
    if (function_exists('teacher59_can_class') && !teacher59_can_class($classId)) { http_response_code(403); exit('K této třídě nemáte přístup.'); } // v59
    $class = $classes[$classId];
    $rows = intake_v51_responses_for_class($classId);
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$class['name']);
    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="dotaznik_' . $safe . '.json"');
        echo json_encode(['class' => array_diff_key($class, ['seats' => 1]), 'responses' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    if ($format === 'csv') {
        $labels = intake_v51_labels((string)$class['course_type']);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="dotaznik_' . $safe . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'wb');
        fputcsv($out, array_merge(['Jméno', 'Příjmení', 'Oslovení', 'Místo', 'Odesláno', 'Body', 'Max. bodů'], array_values($labels)), ';', '"', '');
        foreach ($rows as $r) {
            $a = (array)($r['answers'] ?? []);
            $isQuiz = ($r['assessment']['type'] ?? '') === 'knowledge_quiz';
            $line = [$r['student']['first_name'] ?? '', $r['student']['last_name'] ?? '', $r['student']['preferred_name'] ?? '', $r['student']['seat_label'] ?? '', $r['submitted_at'] ?? '', $isQuiz ? (string)($r['assessment']['score'] ?? '') : '', $isQuiz ? (string)($r['assessment']['max_score'] ?? '') : ''];
            foreach (array_keys($labels) as $k) { $v = $a[$k] ?? ''; $line[] = is_array($v) ? implode(', ', $v) : (string)$v; }
            fputcsv($out, intake_v51_teacher_csv_row($line), ';', '"', '');
        }
        fclose($out);
        exit;
    }
    if ($format === 'codes') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="aktivacni_kody_' . $safe . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'wb');
        fputcsv($out, ['Žák', 'Třída', 'Aktivační kód', 'Stav', 'E-mail'], ';', '"', '');
        foreach (intake_v51_activations() as $row) {
            if (!is_array($row) || (string)($row['class_id'] ?? '') !== $classId) continue;
            fputcsv($out, intake_v51_teacher_csv_row([$row['label'] ?? '', $class['name'], $row['code'] ?? '', empty($row['used_at']) ? 'čeká na aktivaci' : 'aktivováno', $row['used_email'] ?? '']), ';', '"', '');
        }
        fclose($out);
        exit;
    }
}

/** v59 · SEC59-08: každá textová buňka exportu přes csv_safe_cell() (ochrana před formula injection v Excelu). */
function intake_v51_teacher_csv_row(array $cells): array
{
    return array_map(static fn($cell): string => csv_safe_cell(is_scalar($cell) ? (string)$cell : ''), array_values($cells));
}

function intake_v51_teacher_bars(array $data, int|float $max, string $suffix = ''): void
{
    if (!$data) { echo '<p class="u51-empty">Zatím bez dat.</p>'; return; }
    echo '<div class="u51-bars">';
    foreach ($data as $label => $value) {
        $pct = $max > 0 && $value !== null ? max(0, min(100, $value / $max * 100)) : 0;
        $shown = $value === null ? '—' : (is_float($value) ? number_format($value, 1, ',', ' ') : (string)$value) . $suffix;
        echo '<div><span>' . e((string)$label) . '</span><b>' . e($shown) . '</b><i><em style="width:' . round($pct, 1) . '%"></em></i></div>';
    }
    echo '</div>';
}

function intake_v51_render_teacher_tab(array $modules): void
{
    $classes = intake_v51_classes($modules);
    $all = intake_v51_responses();
    $activations = intake_v51_activations();
    $classId = (string)($_GET['class'] ?? '');
    $class = $classes[$classId] ?? null;
    $view = in_array((string)($_GET['view'] ?? ''), ['students', 'room', 'codes', 'settings'], true) ? (string)$_GET['view'] : 'students';
    $countFor = static fn(string $id): int => count(array_filter($all, static fn(array $r): bool => (string)($r['class_id'] ?? '') === $id));
    $csrf = e(csrf_token());
    $canManage = teacher_permission('students.manage');
    ?>
    <div class="u51-t-wrap">
      <header class="u51-t-head">
        <div><span class="u51-kicker">Seznamovací dotazník</span><h1><?= $class ? e($class['name'] . ' · ' . $class['subject']) : 'Třídy a odpovědi' ?></h1><p><?= $class ? 'Kód třídy ' . e($class['code']) . ' · ' . ($class['open'] ? 'dotazník otevřený' : 'dotazník uzavřený') : 'Odpovědi z původního dotazníku (V1) i nové odpovědi žáků na jednom místě.' ?></p></div>
        <div class="u51-t-actions u51-noprint">
          <?php if ($class): ?>
            <a class="btn secondary" href="<?= e(intake_v51_teacher_url(['class' => $classId, 'export' => 'csv'])) ?>">CSV</a>
            <a class="btn secondary" href="<?= e(intake_v51_teacher_url(['class' => $classId, 'export' => 'json'])) ?>">JSON</a>
          <?php elseif ($canManage): ?>
            <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="intake_t_sync"><button class="btn secondary" type="submit">Znovu načíst data z V1</button></form>
          <?php endif; ?>
        </div>
      </header>

      <nav class="u51-tabs u51-noprint" aria-label="Třídy">
        <a class="<?= $class ? '' : 'active' ?>" href="<?= e(intake_v51_teacher_url()) ?>">Přehled</a>
        <?php foreach ($classes as $c): ?><a class="<?= $classId === $c['id'] ? 'active' : '' ?>" href="<?= e(intake_v51_teacher_url(['class' => $c['id']])) ?>"><?= e($c['name']) ?> <b><?= $countFor($c['id']) ?></b></a><?php endforeach; ?>
      </nav>

    <?php if (!$class): ?>
      <?php
        $usedTotal = count(array_filter($activations, static fn($a): bool => is_array($a) && !empty($a['used_at'])));
      ?>
      <div class="u51-kpis">
        <div class="u51-kpi"><b><?= count($all) ?></b><span>vyplněných dotazníků</span></div>
        <div class="u51-kpi"><b><?= count(array_filter($all, static fn($r): bool => ($r['source'] ?? '') === 'V1')) ?></b><span>převzato z V1</span></div>
        <div class="u51-kpi"><b><?= $usedTotal ?> / <?= count($activations) ?></b><span>aktivovaných účtů</span></div>
        <div class="u51-kpi"><b><?= count(array_filter($classes, static fn($c): bool => !empty($c['open']))) ?></b><span>otevřené dotazníky</span></div>
      </div>
      <section class="u51-box">
        <h2>Třídy</h2>
        <p>Kliknutím na třídu otevřete odpovědi, zasedací pořádek, aktivační kódy a nastavení.</p>
        <div class="u51-table-wrap"><table class="u51-table">
          <thead><tr><th>Třída</th><th>Typ</th><th>Vyplněno</th><th>Aktivované účty</th><th>Stav</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($classes as $c):
              $n = $countFor($c['id']);
              $seats = count(array_filter($c['seats'], static fn($s): bool => !empty($s['active'])));
              $acts = array_filter($activations, static fn($a): bool => is_array($a) && ($a['class_id'] ?? '') === $c['id']);
              $used = count(array_filter($acts, static fn($a): bool => !empty($a['used_at'])));
          ?>
            <tr>
              <td><strong><?= e($c['name']) ?></strong><br><small><?= e($c['subject']) ?></small></td>
              <td><?= e(match ($c['course_type']) { 'graphics' => 'Grafika · plakát', 'networks' => 'SOSaPS · test 10 b.', 'networks_advanced' => 'SOSaPS navazující · test 10 b.', default => 'Obecný' }) ?></td>
              <td><strong><?= $n ?></strong> <small>/ <?= $seats ?> míst</small><?= $n === 0 && $c['id'] === 'class_1a' ? '<br><small>zatím bez dat – žáci vyplní po přihlášení</small>' : '' ?></td>
              <td><?= $acts ? $used . ' / ' . count($acts) : '<small>—</small>' ?></td>
              <td><span class="u51-pill <?= $c['open'] ? 'ok' : '' ?>"><?= $c['open'] ? 'Otevřený' : 'Uzavřený' ?></span></td>
              <td><a class="btn secondary" href="<?= e(intake_v51_teacher_url(['class' => $c['id']])) ?>">Otevřít</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </section>
    <?php else:
        $responses = intake_v51_responses_for_class($classId);
        $type = (string)$class['course_type'];
        $seatCount = count(array_filter($class['seats'], static fn($s): bool => !empty($s['active'])));
        $classActs = array_filter($activations, static fn($a): bool => is_array($a) && ($a['class_id'] ?? '') === $classId);
        uasort($classActs, static fn(array $a, array $b): int => strnatcasecmp((string)$a['label'], (string)$b['label']));
        $usedActs = count(array_filter($classActs, static fn($a): bool => !empty($a['used_at'])));
        $quizScores = array_values(array_filter(array_map(static fn($r) => ($r['assessment']['type'] ?? '') === 'knowledge_quiz' ? (int)$r['assessment']['score'] : null, $responses), static fn($v): bool => $v !== null));
    ?>
      <div class="u51-kpis">
        <div class="u51-kpi"><b><?= count($responses) ?></b><span>odpovědí · <?= $seatCount ? round(count($responses) / $seatCount * 100) : 0 ?> % míst</span></div>
        <div class="u51-kpi"><b><?= $classActs ? $usedActs . ' / ' . count($classActs) : '—' ?></b><span>aktivovaných účtů</span></div>
        <div class="u51-kpi"><b><?= ($v = intake_v51_avg($responses, 'subject_confidence')) === null ? '—' : number_format($v, 1, ',', ' ') ?></b><span>průměrná jistota v předmětu / 10</span></div>
        <div class="u51-kpi"><?php if ($quizScores): ?><b><?= number_format(array_sum($quizScores) / count($quizScores), 1, ',', ' ') ?> b.</b><span>průměr testu z 10 bodů</span><?php else: ?><b><?= count(array_filter($responses, static fn($r): bool => !empty($r['assessment']['artifact']))) ?></b><span>odevzdaných plakátů</span><?php endif; ?></div>
      </div>

      <nav class="u51-tabs u51-noprint" aria-label="Sekce třídy">
        <?php foreach (['students' => 'Žáci a analytika', 'room' => 'Zasedací pořádek', 'codes' => 'Aktivační kódy', 'settings' => 'Nastavení'] as $key => $label): ?>
          <a class="<?= $view === $key ? 'active' : '' ?>" href="<?= e(intake_v51_teacher_url(['class' => $classId, 'view' => $key])) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <?php if ($view === 'students'): ?>
        <?php if ($responses): ?>
        <div class="u51-charts">
          <section class="u51-box"><h2>Sebehodnocení třídy</h2><p>Průměr na škále 1–10</p><?php intake_v51_teacher_bars(['Jistota v předmětu' => intake_v51_avg($responses, 'subject_confidence'), 'Samostatnost' => intake_v51_avg($responses, 'independence'), 'Týmová práce' => intake_v51_avg($responses, 'team_confidence'), 'Prezentování' => intake_v51_avg($responses, 'presentation_confidence'), 'Řešení problémů' => intake_v51_avg($responses, 'problem_solving'), 'Digitální jistota' => intake_v51_avg($responses, 'digital_confidence')], 10); ?></section>
          <section class="u51-box"><h2>Zájmy</h2><p>Nejčastější odpovědi</p><?php $d = intake_v51_ranked($responses, 'interests'); intake_v51_teacher_bars($d, $d ? max($d) : 1, '×'); ?></section>
          <section class="u51-box"><h2>Jak se chtějí učit</h2><p>Nejčastější preference</p><?php $d = intake_v51_ranked($responses, 'learning_styles'); intake_v51_teacher_bars($d, $d ? max($d) : 1, '×'); ?></section>
        </div>
        <?php endif; ?>
        <section class="u51-box">
          <div class="u51-t-head"><div><h2>Žáci</h2><p><?= count($responses) ?> odpovědí · profil otevřete tlačítkem</p></div><input class="u51-search u51-noprint" type="search" placeholder="Hledat jméno, místo, cíl…" data-u51-search></div>
          <div class="u51-table-wrap"><table class="u51-table">
            <thead><tr><th>Žák</th><th>Místo</th><th>Jistota</th><th>Ověření</th><th>Cíl na rok</th><th>Účet</th><th></th></tr></thead>
            <tbody>
            <?php if (!$responses): ?><tr><td colspan="7">Zatím nikdo neodeslal odpovědi.</td></tr><?php endif; ?>
            <?php foreach ($responses as $r):
                $key = intake_v51_response_key($r);
                $act = $activations[$key] ?? null;
                $as = (array)($r['assessment'] ?? []);
                $goal = trim((string)($r['answers']['year_goal'] ?? ''));
            ?>
              <tr data-u51-row>
                <td><strong><?= e(intake_v51_student_label($r)) ?></strong><?php if (!empty($r['student']['preferred_name'])): ?><br><small><?= e((string)$r['student']['preferred_name']) ?></small><?php endif; ?></td>
                <td><small><?= e((string)($r['student']['seat_label'] ?? '—')) ?></small></td>
                <td><?= (int)($r['answers']['subject_confidence'] ?? 0) ?><small>/10</small></td>
                <td><?php if (($as['type'] ?? '') === 'knowledge_quiz'): ?><span class="u51-pill accent"><?= (int)$as['score'] ?> / <?= (int)$as['max_score'] ?> b.</span><?php elseif (!empty($as['artifact'])): ?><span class="u51-pill ok">Plakát</span><?php else: ?>—<?php endif; ?></td>
                <td><small><?= e($goal === '' ? '—' : (u_strlen($goal) > 70 ? u_substr($goal, 0, 69) . '…' : $goal)) ?></small></td>
                <td><?php if (is_array($act)): ?><span class="u51-pill <?= empty($act['used_at']) ? 'warn' : 'ok' ?>"><?= empty($act['used_at']) ? 'čeká' : 'aktivní' ?></span><?php else: ?><span class="u51-pill ok">aktivní</span><?php endif; ?></td>
                <td><button type="button" class="btn secondary" data-u51-open="u51p-<?= e((string)$r['id']) ?>">Profil</button></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table></div>
        </section>
        <?php foreach ($responses as $r): ?>
          <dialog class="u51-dialog" id="u51p-<?= e((string)$r['id']) ?>">
            <header><div><h2><?= e(intake_v51_student_label($r)) ?></h2><p><?= e($class['name']) ?> · <?= e((string)($r['student']['seat_label'] ?? '')) ?> · odesláno <?= e(date('j. n. Y H:i', strtotime((string)($r['submitted_at'] ?? 'now')) ?: time())) ?><?= ($r['source'] ?? '') === 'V1' ? ' · z V1' : '' ?></p></div><button type="button" class="u51-x" data-u51-close aria-label="Zavřít">×</button></header>
            <div>
              <?php intake_v51_render_assessment($r, $type, intake_v51_teacher_url(['artifact' => (string)$r['id']]), true); ?>
              <?php intake_v51_render_answers($r, $type); ?>
              <?php if ($canManage): ?>
              <form method="post" onsubmit="return confirm('Opravdu smazat tuto odpověď? Místo se znovu uvolní.')"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="intake_t_delete"><input type="hidden" name="class_id" value="<?= e($classId) ?>"><input type="hidden" name="response_id" value="<?= e((string)$r['id']) ?>"><button class="btn secondary" type="submit">Smazat odpověď</button></form>
              <?php endif; ?>
            </div>
          </dialog>
        <?php endforeach; ?>

      <?php elseif ($view === 'room'):
          $occupied = [];
          foreach ($responses as $r) $occupied[(string)($r['student']['seat_id'] ?? '')] = $r;
          $map = intake_v51_seat_map($class);
      ?>
        <section class="u51-box">
          <h2>Zasedací pořádek</h2><p>Řada 1 je nejblíže tabuli. Každá lavice má levé a pravé místo.</p>
          <div class="u51-room u51-room-teacher"><div class="u51-board">Tabule</div>
            <div class="u51-desks" style="--u51-cols:<?= (int)$class['cols'] ?>; min-width: calc(<?= (int)$class['cols'] ?> * 130px)">
            <?php for ($row = 1; $row <= (int)$class['rows']; $row++): for ($desk = 1; $desk <= (int)$class['cols']; $desk++):
                $left = $map[$row][$desk]['left'] ?? null; $right = $map[$row][$desk]['right'] ?? null;
                if (empty($left['active']) && empty($right['active'])): ?><div class="u51-desk off"></div><?php continue; endif; ?>
              <div class="u51-desk">
                <?php foreach ([$left, $right] as $seat): if (!$seat) continue; $st = $occupied[(string)$seat['id']] ?? null; ?>
                  <span class="u51-seat<?= $st ? ' filled' : '' ?>"><?= $st ? e(intake_v51_student_label($st)) : '·' ?></span>
                <?php endforeach; ?>
              </div>
            <?php endfor; endfor; ?>
            </div>
          </div>
          <div class="u51-editor-tools u51-noprint"><button type="button" class="btn secondary" data-u51-print>Vytisknout</button></div>
        </section>

      <?php elseif ($view === 'codes'): ?>
        <section class="u51-box">
          <div class="u51-t-head"><div><h2>Aktivační kódy</h2><p>Žák na úvodní stránce zvolí „Aktivovat připravený účet“, zadá kód a nastaví si školní e-mail a heslo. Jeho odpovědi už na něj čekají.</p></div>
            <div class="u51-t-actions u51-noprint"><a class="btn secondary" href="<?= e(intake_v51_teacher_url(['class' => $classId, 'export' => 'codes'])) ?>">CSV</a><button type="button" class="btn primary" data-u51-print>Vytisknout kartičky</button></div></div>
          <?php if (!$classActs): ?>
            <p><?= $classId === 'class_1a' ? 'Třída 1.A zatím nemá data z V1. Žáci si vytvoří účet sami a dotazník vyplní jako první krok po přihlášení.' : 'Pro tuto třídu nejsou žádné předzaložené účty.' ?></p>
          <?php else: ?>
          <div class="u51-table-wrap"><table class="u51-table">
            <thead><tr><th>Žák</th><th>Kód</th><th>Stav</th><th class="u51-noprint"></th></tr></thead>
            <tbody>
            <?php foreach ($classActs as $act): ?>
              <tr>
                <td><strong><?= e((string)$act['label']) ?></strong></td>
                <td class="u51-code-cell"><?= empty($act['used_at']) ? e((string)$act['code']) : '<small>použit</small>' ?></td>
                <td><?php if (empty($act['used_at'])): ?><span class="u51-pill warn">čeká na aktivaci</span><?php else: ?><span class="u51-pill ok">aktivní</span> <small><?= e((string)($act['used_email'] ?? '')) ?></small><?php endif; ?></td>
                <td class="u51-noprint"><?php if ($canManage): ?><form method="post" onsubmit="return confirm('Vygenerovat nový kód? Starý přestane platit.')"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="intake_t_regen"><input type="hidden" name="class_id" value="<?= e($classId) ?>"><input type="hidden" name="student_key" value="<?= e((string)$act['student_key']) ?>"><button class="btn secondary" type="submit"><?= empty($act['used_at']) ? 'Nový kód' : 'Obnovit přístup' ?></button></form><?php endif; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table></div>
          <?php endif; ?>
        </section>

      <?php else: ?>
        <section class="u51-box">
          <div class="u51-t-head"><div><h2>Stav dotazníku</h2><p><?= $class['open'] ? 'Žáci třídy ho po přihlášení uvidí jako první krok.' : 'Dotazník je skrytý – žáci pokračují rovnou ve výuce.' ?></p></div>
          <?php if ($canManage): ?><form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="intake_t_toggle"><input type="hidden" name="class_id" value="<?= e($classId) ?>"><button class="btn <?= $class['open'] ? 'secondary' : 'primary' ?>" type="submit"><?= $class['open'] ? 'Uzavřít dotazník' : 'Otevřít dotazník' ?></button></form><?php endif; ?></div>
        </section>
        <?php if ($canManage): ?>
        <form class="u51-box" method="post">
          <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="intake_t_save"><input type="hidden" name="class_id" value="<?= e($classId) ?>">
          <input type="hidden" name="seat_map" value="<?= e((string)json_encode($class['seats'], JSON_UNESCAPED_UNICODE)) ?>" data-u51-seat-map>
          <h2>Úvod a učebna</h2><p>Kliknutím na lavici ji zapnete nebo vypnete. Každá lavice má vždy dvě místa.</p>
          <label class="u51-field"><span>Úvodní text pro žáky</span><textarea name="intro" rows="2"><?= e($class['intro']) ?></textarea></label>
          <div class="u51-grid-2">
            <label class="u51-field"><span>Počet řad</span><input type="number" name="rows" min="1" max="12" value="<?= (int)$class['rows'] ?>" data-u51-rows></label>
            <label class="u51-field"><span>Lavic v řadě</span><input type="number" name="cols" min="1" max="10" value="<?= (int)$class['cols'] ?>" data-u51-cols></label>
          </div>
          <div class="u51-editor-tools"><button type="button" class="btn secondary" data-u51-seat-all>Všechny lavice</button><button type="button" class="btn secondary" data-u51-seat-aisle>Ulička uprostřed</button></div>
          <div class="u51-room"><div class="u51-board">Tabule</div><div class="u51-desks" data-u51-seat-editor></div></div>
          <div class="u51-editor-tools"><button class="btn primary" type="submit">Uložit nastavení</button></div>
        </form>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
    </div>
    <script src="assets/ui-v51.js?v=51.0" defer></script>
    <?php
}
