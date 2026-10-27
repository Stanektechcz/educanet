<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'virtualni_dilna') {
    guarded_study_redirect();
    $vd82Class = (string)$classId;
    $vd82Student = adaptive_student_key($vd82Class);
    $vd82Track = is_string($_GET['mod'] ?? null) && $_GET['mod'] === 'sit' ? 'sit' : 'pc';
    $vd82Catalog = vd82_catalog();
    $vd82State = vd82_read($vd82Class, $vd82Student);
    $vd82Config = $vd82Catalog[$vd82Track];
    $vd82Progress = vd82_progress($vd82State,$vd82Track);
    $vd82Done = (array)($vd82State['done'][$vd82Track] ?? []);
    render_header('Virtuální dílna', $module);
    ?>
    <div class="vd82" data-vd82>
      <header class="vd82-intro">
        <div><span class="vd82-kicker">Interaktivní výuka · bezpečná simulace</span>
          <h1>Virtuální dílna</h1>
          <p>Praktické úkoly řešíš samostatně. Můžeš zkoušet různé postupy a dostaneš zpětnou vazbu. Pokrok se ukládá k tvému účtu, ale sám o sobě neurčuje známku.</p>
        </div>
        <div class="vd82-overview"><strong><?= $vd82Progress['done'] ?> / <?= $vd82Progress['total'] ?></strong><span>splněných kroků</span><progress max="<?= $vd82Progress['total'] ?>" value="<?= $vd82Progress['done'] ?>"><?= $vd82Progress['done'] ?> / <?= $vd82Progress['total'] ?></progress></div>
      </header>
      <nav class="vd82-tabs" aria-label="Moduly virtuální dílny">
        <a href="?view=virtualni_dilna&amp;mod=pc" <?= $vd82Track === 'pc' ? 'aria-current="page"' : '' ?>>Počítačová dílna <span><?= vd82_progress($vd82State,'pc')['done'] ?> / 7</span></a>
        <a href="?view=virtualni_dilna&amp;mod=sit" <?= $vd82Track === 'sit' ? 'aria-current="page"' : '' ?>>Síťová dílna <span><?= vd82_progress($vd82State,'sit')['done'] ?> / 8</span></a>
        <a href="?view=lab">Linuxová dílna <span>Otevřít Lab</span></a>
      </nav>
      <?php if ($flash !== ''): ?><div class="vd82-message" role="status"><?= e($flash) ?></div><?php endif; ?>
      <section class="vd82-workspace" aria-label="<?= e($vd82Config['label']) ?>">
        <div class="vd82-screen">
          <div class="vd82-screen-top"><span aria-hidden="true">● ● ●</span><strong><?= e($vd82Config['label']) ?> · simulace</strong><span>EDUCANET</span></div>
          <?php if ($vd82Track === 'pc'): ?>
            <div class="vd82-desktop">
              <div class="vd82-file-window">
                <div class="vd82-window-title">▣ Průzkumník souborů <span>─ □ ×</span></div>
                <div class="vd82-path">Tento počítač / Školní jednotky</div>
                <div class="vd82-filegrid">
                  <div class="vd82-folder"><span>📁</span><strong>Home</strong><small>Moje dokumenty</small></div>
                  <div class="vd82-folder"><span>📁</span><strong>Data/Projekt</strong><small><?= isset($vd82Done['copy']) ? 'obrazek.png · ' : '' ?><?= isset($vd82Done['move']) ? 'poznamky.txt · ' : '' ?><?= isset($vd82Done['pdf']) ? 'referat.pdf' : 'pracovní soubory' ?></small></div>
                  <div class="vd82-folder vd82-restricted"><span>🔒</span><strong>System</strong><small><?= isset($vd82Done['rights']) ? 'Respektována práva' : 'Zápis nepovolen' ?></small></div>
                  <div class="vd82-folder"><span>🛡️</span><strong>Antivirus</strong><small><?= isset($vd82Done['quarantine']) ? 'Hrozba v karanténě' : (isset($vd82Done['scan']) ? 'Hrozba nalezena' : 'Čeká na sken') ?></small></div>
                </div>
              </div>
              <div class="vd82-taskbar"><span>▦ Start</span><span><?= isset($vd82Done['slow']) ? '✓ Proces opraven' : '◌ Správce úloh' ?></span><span>◷ Simulace</span></div>
            </div>
          <?php else: ?>
            <div class="vd82-network">
              <div class="vd82-device"><span>☁</span><strong>Internet</strong><small>Poskytovatel</small></div>
              <div class="vd82-wire <?= isset($vd82Done['wan']) ? 'connected' : '' ?>"><?= isset($vd82Done['wan']) ? 'WAN připojeno' : 'WAN nepřipojeno' ?></div>
              <div class="vd82-router <?= isset($vd82Done['power']) ? 'is-on' : '' ?>"><span>▤</span><strong>Router</strong><small><?= isset($vd82Done['power']) ? 'Zapnuto' : 'Vypnuto' ?> · <?= isset($vd82Done['password']) ? 'WPA2/WPA3 chráněno' : 'Wi-Fi čeká' ?></small></div>
              <div class="vd82-wire <?= isset($vd82Done['lan']) ? 'connected' : '' ?>"><?= isset($vd82Done['lan']) ? 'LAN připojeno' : 'LAN nepřipojeno' ?></div>
              <div class="vd82-device"><span>▣</span><strong>Školní počítač</strong><small><?= isset($vd82Done['speed']) ? 'Test spojení dokončen' : 'Čeká na spojení' ?></small></div>
              <div class="vd82-network-note"><?= isset($vd82Done['isolate']) ? '✓ Neznámý klient zabezpečen' : (isset($vd82Done['foreign']) ? '⚠ Neznámý klient nalezen' : '● Sledování klientů připraveno') ?></div>
            </div>
          <?php endif; ?>
          <p class="vd82-simulation-note">Modelované prostředí. Žádné skutečné soubory ani síťová zařízení se nemění.</p>
        </div>
        <div class="vd82-missions">
          <header><h2><?= e($vd82Config['label']) ?></h2><p><?= e($vd82Config['intro']) ?></p></header>
          <ol>
          <?php foreach ($vd82Config['steps'] as $stepId=>$step): $isDone = isset($vd82Done[$stepId]); $missing = array_diff((array)$step['requires'], array_keys($vd82Done)); ?>
            <li class="vd82-mission <?= $isDone ? 'is-done' : '' ?>">
              <div class="vd82-step-head"><span aria-hidden="true"><?= $isDone ? '✓' : '○' ?></span><h3><?= e($step['title']) ?></h3></div>
              <p><?= e($step['task']) ?></p>
              <?php if ($isDone): ?><p class="vd82-completed">Splněno · ověřeno na serveru</p>
              <?php else: ?>
                <?php if ($missing): ?><p class="vd82-warning">Nejprve splň: <?= e(implode(', ', array_map(static fn(string $id): string => (string)$vd82Config['steps'][$id]['title'], $missing))) ?></p><?php endif; ?>
                <form method="post" class="vd82-choice-form">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="vd82_answer">
                  <input type="hidden" name="track" value="<?= e($vd82Track) ?>">
                  <input type="hidden" name="step" value="<?= e($stepId) ?>">
                  <label for="vd82-choice-<?= e($stepId) ?>">Zvol postup</label>
                  <select id="vd82-choice-<?= e($stepId) ?>" name="choice" required>
                    <option value="">Vyber řešení…</option>
                    <?php foreach ($step['options'] as $option=>$label): ?><option value="<?= e($option) ?>"><?= e($label) ?></option><?php endforeach; ?>
                  </select>
                  <button class="vd82-primary" type="submit">Provést a ověřit</button>
                </form>
                <?php if (($vd82State['hint_for'] ?? null) === [$vd82Track,$stepId]): ?><p class="vd82-hint">Nápověda: <?= e($step['hint']) ?></p><?php else: ?>
                <form method="post" class="vd82-hint-form">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="vd82_hint">
                  <input type="hidden" name="track" value="<?= e($vd82Track) ?>">
                  <input type="hidden" name="step" value="<?= e($stepId) ?>">
                  <button type="submit">Potřebuji nápovědu</button>
                </form>
                <?php endif; ?>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
          </ol>
          <p class="vd82-summary">Pokusy: <?= (int)($vd82State['attempts'] ?? 0) ?> · Nesprávné kroky: <?= (int)($vd82State['errors'] ?? 0) ?> · Nápovědy: <?= (int)($vd82State['hints'] ?? 0) ?>. Chyby a nápovědy nesnižují známku.</p>
        </div>
      </section>
    </div>
    <?php render_footer(); exit;
}
