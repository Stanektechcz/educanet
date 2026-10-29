<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Studentské obrazovky v51: aktivace účtu, krokový dotazník a „Moje odpovědi“. */

function intake_v51_old(string $name, string $default = ''): string
{
    $old = is_array($_SESSION['intake_old'] ?? null) ? $_SESSION['intake_old'] : [];
    $value = $old[$name] ?? $default;
    return is_scalar($value) ? (string)$value : $default;
}

function intake_v51_old_has(string $name, string $value): bool
{
    $old = is_array($_SESSION['intake_old'] ?? null) ? $_SESSION['intake_old'] : [];
    return in_array($value, (array)($old[$name] ?? []), true);
}

function intake_v51_chips(string $name, array $options): void
{
    echo '<div class="u51-chips">';
    foreach ($options as $option) {
        echo '<label class="u51-chip"><input type="checkbox" name="' . e($name) . '[]" value="' . e($option) . '"' . (intake_v51_old_has($name, $option) ? ' checked' : '') . '><span>' . e(tr($option)) . '</span></label>';
    }
    echo '</div>';
}

function intake_v51_textarea(string $name, string $label, string $placeholder = ''): void
{
    echo '<label class="u51-field"><span>' . e(tr($label)) . '</span><textarea name="' . e($name) . '" rows="3" placeholder="' . e($placeholder !== '' ? tr($placeholder) : '') . '">' . e(intake_v51_old($name)) . '</textarea></label>';
}

function intake_v51_select(string $name, string $label, array $options): void
{
    $current = intake_v51_old($name);
    echo '<label class="u51-field"><span>' . e(tr($label)) . '</span><select name="' . e($name) . '"><option value="">' . e(tr('Vyber…')) . '</option>';
    foreach ($options as $o) echo '<option value="' . e($o) . '"' . ($current === $o ? ' selected' : '') . '>' . e(tr($o)) . '</option>';
    echo '</select></label>';
}

function intake_v51_range(string $name, string $label): void
{
    $v = max(1, min(10, (int)intake_v51_old($name, '5')));
    echo '<div class="u51-field u51-range"><span>' . e(tr($label)) . '</span><div><input type="range" min="1" max="10" step="1" name="' . e($name) . '" value="' . $v . '" data-u51-range><output>' . $v . '</output></div><small>' . e(tr('1 = vůbec si nevěřím · 10 = zvládám velmi jistě')) . '</small></div>';
}

function intake_v51_step_open(string $id, string $title, string $lead, bool $required = false): void
{
    echo '<section class="u51-step" id="' . e($id) . '" data-u51-step data-title="' . e(tr($title)) . '"><header class="u51-step-head"><h2>' . e(tr($title)) . '</h2><p>' . e(tr($lead)) . '</p>' . ($required ? '<span class="u51-req-note">* ' . e(tr('povinné')) . '</span>' : '') . '</header>';
}

/** Karta na dashboardu: první krok, dokud žák nevyplní dotazník. */
function intake_v51_render_dashboard_card(string $classId, array $modules): void
{
    $classes = intake_v51_classes($modules);
    $class = $classes[$classId] ?? null;
    if (!$class) return;
    $done = intake_v51_student_has_response($classId);
    if ($done) {
        echo '<a class="u51-done-strip" href="?view=my_intake"><span class="u51-check">✓</span><span>' . e(tr('Seznamovací dotazník je vyplněný')) . '</span><b>' . e(tr('Moje odpovědi')) . '</b></a>';
        return;
    }
    if (empty($class['open'])) return;
    echo '<section class="u51-first-step"><div><span class="u51-kicker">' . e(tr('Krok 1 · cca 15 minut')) . '</span><h2>' . e(tr('Seznamovací dotazník')) . '</h2><p>' . tr_html('Než začneš, pomoz učiteli poznat, co tě baví a jak se ti nejlépe učí. Na konci je krátké {task}', ['task' => e($class['course_type'] === 'graphics' ? tr('praktické zadání (plakát).') : tr('ověření znalostí.'))]) . '</p></div><a class="btn primary" href="?view=intake">' . e(tr('Vyplnit dotazník')) . '</a></section>';
}

function intake_v51_render_activate_view(array $modules, string $flash): void
{
    $code = (string)($_SESSION['intake_activation_code'] ?? '');
    $row = $code !== '' ? intake_v51_find_activation($code) : null;
    if ($row && !empty($row['used_at'])) { $row = null; unset($_SESSION['intake_activation_code']); }
    $class = $row ? ($modules[(string)$row['class_id']] ?? null) : null;
    render_header(tr('Aktivace účtu'));
    ?>
    <section class="u51-narrow">
      <ol class="u51-progress-dots" aria-label="<?= e(tr('Postup aktivace')) ?>"><li class="<?= $row ? 'done' : 'current' ?>"><?= e(tr('Kód')) ?></li><li class="<?= $row ? 'current' : '' ?>"><?= e(tr('Přihlašovací údaje')) ?></li><li><?= e(tr('Hotovo')) ?></li></ol>
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <?php if (!$row || !is_array($class)): ?>
        <form class="u51-card" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="intake_activate"><input type="hidden" name="step" value="check">
          <span class="u51-kicker"><?= e(tr('Krok 1 ze 2')) ?></span>
          <h1><?= e(tr('Zadej aktivační kód')) ?></h1>
          <p class="u51-lead"><?= e(tr('Kód máš od učitele. Tvůj účet už je připravený i s odpověďmi ze seznamovacího dotazníku.')) ?></p>
          <label class="u51-field"><span><?= e(tr('Aktivační kód')) ?></span><input class="u51-code" name="code" required maxlength="12" autocomplete="off" autocapitalize="characters" placeholder="ABCD-1234" autofocus></label>
          <button class="btn primary wide" type="submit"><?= e(tr('Pokračovat')) ?></button>
          <a class="u51-link" href="?view=home"><?= e(tr('Zpět na přihlášení')) ?></a>
        </form>
      <?php else: ?>
        <form class="u51-card" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="intake_activate"><input type="hidden" name="step" value="create"><input type="hidden" name="code" value="<?= e((string)$row['code']) ?>">
          <span class="u51-kicker"><?= e(tr('Krok 2 ze 2')) ?></span>
          <h1><?= tr_html('Ahoj, {name}!', ['name' => e((string)(($row['preferred_name'] ?? '') !== '' ? $row['preferred_name'] : (preg_split('/\s+/u', (string)$row['label'])[0] ?? $row['label'])))]) ?></h1>
          <div class="u51-identity"><span><?= e((string)$row['label']) ?></span><span><?= e((string)($class['name'] ?? '')) ?> · <?= e((string)($class['subject'] ?? '')) ?></span></div>
          <p class="u51-lead"><?= e(tr('Nastav si školní e-mail a heslo. Příště se přihlásíš jen jimi.')) ?></p>
          <label class="u51-field"><span><?= e(tr('Školní e-mail')) ?></span><input type="email" name="email" required autocomplete="username" placeholder="jmeno@<?= e(google_workspace_domain()) ?>"></label>
          <label class="u51-field"><span><?= e(tr('Heslo')) ?></span><input type="password" name="password" required minlength="10" maxlength="200" autocomplete="new-password"><small><?= e(tr('Aspoň 10 znaků, písmeno i číslice. Ne jméno ani běžné heslo.')) ?></small></label>
          <label class="u51-field"><span><?= e(tr('Heslo znovu')) ?></span><input type="password" name="password_confirm" required minlength="10" maxlength="200" autocomplete="new-password"></label>
          <button class="btn primary wide" type="submit"><?= e(tr('Aktivovat účet')) ?></button>
          <a class="u51-link" href="?view=activate&reset=1"><?= e(tr('To nejsem já')) ?></a>
        </form>
      <?php endif; ?>
    </section>
    <?php
    render_footer();
}

function intake_v51_render_intake_view(string $classId, array $modules, string $flash): void
{
    $classes = intake_v51_classes($modules);
    $class = $classes[$classId] ?? null;
    if (!$class) redirect_to('?view=dashboard');
    if (intake_v51_student_has_response($classId)) redirect_to('?view=my_intake');
    $module = $modules[$classId];
    $type = (string)$class['course_type'];
    $label = intake_v51_current_label();
    render_header(tr('Seznamovací dotazník'), $module);
    if (empty($class['open'])) {
        echo '<section class="u51-narrow"><div class="u51-card"><h1>' . e(tr('Dotazník je uzavřený')) . '</h1><p class="u51-lead">' . e(tr('Učitel ho zatím pro tvou třídu neotevřel.')) . '</p><a class="btn primary" href="?view=dashboard">' . e(tr('Zpět')) . '</a></div></section>';
        render_footer();
        return;
    }
    $occupied = [];
    foreach (intake_v51_responses_for_class($classId) as $r) $occupied[(string)($r['student']['seat_id'] ?? '')] = true;
    $seatMap = intake_v51_seat_map($class);
    $oldSeat = intake_v51_old('seat_id');
    if ($oldSeat === '' && !empty($_SESSION['intake_seat'])) $oldSeat = (string)$_SESSION['intake_seat'];
    $chosen = intake_v51_find_seat($class, $oldSeat);
    $quiz = intake_v51_quiz_questions($type);
    ?>
    <div class="u51-wizard-wrap">
    <header class="u51-wizard-top">
      <div><span class="u51-kicker"<?= edu_content_lang_attr() ?>><?= e($class['name']) ?> · <?= e($class['subject']) ?></span><h1><?= e(tr('Seznamovací dotazník')) ?></h1><p<?= edu_content_lang_attr() ?>><?= e($class['intro']) ?></p></div>
      <div class="u51-wizard-progress" data-u51-progress><div class="u51-bar"><i></i></div><strong data-u51-counter><?= e(tr('Krok 1')) ?></strong></div>
    </header>
    <?php $u51SeatConflict = !empty($_SESSION['intake_seat_conflict']); unset($_SESSION['intake_seat_conflict']); ?>
    <?php if ($flash !== ''): ?><div class="u51-notice error" data-u51-server-error<?= $u51SeatConflict ? ' data-u51-error-kind="seat"' : '' ?>><?= e($flash) ?></div><?php endif; ?>

    <form class="u51-wizard" method="post" enctype="multipart/form-data" data-u51-wizard novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="intake_submit">
      <input type="hidden" name="seat_id" value="<?= e($chosen ? $oldSeat : '') ?>" data-u51-seat-input required>

      <?php intake_v51_step_open('s-basic', trm('Kdo jsi a kde sedíš'), trm('Spojím si tvé jméno s konkrétním místem v učebně.'), true); ?>
        <div class="u51-grid-2">
          <div class="u51-field"><span><?= e(tr('Jméno a příjmení')) ?></span><div class="u51-static"><?= e($label) ?></div><small><?= e(tr('Převzato z tvého účtu.')) ?></small></div>
          <label class="u51-field"><span><?= e(tr('Jak ti mám říkat?')) ?></span><input name="preferred_name" maxlength="100" value="<?= e(intake_v51_old('preferred_name')) ?>" placeholder="<?= e(tr('Dobrovolné')) ?>"></label>
        </div>
        <div class="u51-field"><span><?= e(tr('Vyber místo, kde sedíš')) ?> <b class="u51-req">*</b></span><small><?= e(tr('Řada 1 je nejblíž tabuli. L = levé, P = pravé místo při pohledu na tabuli.')) ?></small></div>
        <div class="u51-room">
          <div class="u51-board"><?= e(tr('Tabule')) ?></div>
          <div class="u51-desks" style="--u51-cols:<?= (int)$class['cols'] ?>">
          <?php for ($row = 1; $row <= (int)$class['rows']; $row++): for ($desk = 1; $desk <= (int)$class['cols']; $desk++):
              $left = $seatMap[$row][$desk]['left'] ?? null; $right = $seatMap[$row][$desk]['right'] ?? null;
              $active = !empty($left['active']) || !empty($right['active']); ?>
            <?php if (!$active): ?><div class="u51-desk off" aria-hidden="true"></div><?php else: ?>
            <div class="u51-desk" title="<?= e(tr('Řada {row} · Lavice {desk}', ['row' => $row, 'desk' => $desk])) ?>">
              <?php foreach ([[$left, 'L'], [$right, 'P']] as [$seat, $short]): if (!$seat) continue; $occ = !empty($occupied[(string)$seat['id']]);
                  $seatCol = isset($seat['col']) ? (int)$seat['col'] : ($short === 'L' ? $desk * 2 - 1 : $desk * 2);
                  $seatLabel = intake_v51_seat_label((int)($seat['row'] ?? $row), $seatCol);
              ?>
                <button type="button" class="u51-seat<?= $occ ? ' taken' : '' ?><?= $chosen && $oldSeat === $seat['id'] ? ' selected' : '' ?>" data-u51-seat="<?= e((string)$seat['id']) ?>" data-label="<?= e($seatLabel) ?>" <?= $occ ? 'disabled aria-label="' . e(tr('obsazeno')) . '"' : 'aria-label="' . e($seatLabel) . '"' ?>><?= $short ?></button>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          <?php endfor; endfor; ?>
          </div>
          <?php $chosenLabel = is_array($chosen) ? intake_v51_seat_label((int)($chosen['row'] ?? 0), isset($chosen['col']) ? (int)$chosen['col'] : 0) : ''; ?>
          <div class="u51-room-foot"><span><i class="u51-dot free"></i><?= e(tr('volné')) ?></span><span><i class="u51-dot taken"></i><?= e(tr('obsazené')) ?></span><strong data-u51-seat-label><?= $chosenLabel !== '' ? e($chosenLabel) : e(tr('Místo zatím nevybráno')) ?></strong></div>
        </div>
      </section>

      <?php intake_v51_step_open('s-about', trm('Něco o tobě'), trm('Nemusí jít jen o IT nebo grafiku. Hledám, na čem se dá stavět.')); ?>
        <div class="u51-field"><span><?= e(tr('Co tě baví?')) ?></span><?php intake_v51_chips('interests', [trm('Počítače a technologie'),trm('Hry'),trm('Grafika a design'),trm('Kreslení / ilustrace'),trm('Fotografie'),trm('Video / film'),trm('Hudba'),trm('Sport'),trm('Auta / technika'),trm('Programování'),trm('3D / tisk'),trm('Sociální sítě / obsah'),trm('Příroda'),trm('Čtení'),trm('Podnikání'),trm('Jiné')]); ?></div>
        <?php intake_v51_textarea('free_time', trm('Co nejčastěji děláš ve volném čase?'), trm('Hry, sport, tvoření, práce, hudba…')); ?>
        <?php intake_v51_textarea('favorite_school_things', trm('Co tě ve škole nebo při učení baví nejvíc?'), trm('Praktické úkoly, projekty, soutěže…')); ?>
        <div class="u51-grid-2"><?php intake_v51_textarea('strengths', trm('V čem jsi dobrý/á?')); intake_v51_textarea('improve', trm('V čem by ses chtěl/a zlepšit?')); ?></div>
        <?php intake_v51_textarea('projects', trm('Děláš něco vlastního mimo školu?'), trm('Web, grafika, server, hra, YouTube, brigáda…')); ?>
      </section>

      <?php intake_v51_step_open('s-goals', trm('Cíle'), trm('Nejde o správné odpovědi. Potřebuji vědět, co pro tebe dává smysl.')); ?>
        <?php intake_v51_textarea('why_subject', trm('Proč tě tento předmět zajímá?')); ?>
        <?php intake_v51_textarea('expectations', trm('Co od výuky letos očekáváš?')); ?>
        <?php intake_v51_textarea('year_goal', trm('Jeden konkrétní cíl do konce roku')); ?>
        <div class="u51-grid-2"><?php intake_v51_textarea('dream_project', trm('Jaký projekt bys chtěl/a vytvořit?')); intake_v51_textarea('practical_outcome', trm('Jakou dovednost chceš použít i mimo školu?')); ?></div>
        <div class="u51-field"><span><?= e(tr('Kam přibližně míříš po škole?')) ?></span><?php intake_v51_chips('future_direction', [trm('Práce v IT'),trm('Práce v grafice / designu'),trm('Vysoká škola – IT'),trm('Vysoká škola – jiný obor'),trm('Freelance / podnikání'),trm('Vlastní projekty'),trm('Jiný obor'),trm('Ještě nevím')]); ?></div>
        <?php intake_v51_textarea('future_detail', trm('Pokud už máš představu, napiš ji přesněji.')); ?>
      </section>

      <?php intake_v51_step_open('s-learning', trm('Jak se ti učí'), trm('Pomůže mi to namíchat výklad, praxi a týmovou práci.')); ?>
        <div class="u51-field"><span><?= e(tr('Co ti při učení nejvíc pomáhá?')) ?></span><?php intake_v51_chips('learning_styles', [trm('Krátký výklad a hned praxe'),trm('Ukázka krok za krokem'),trm('Video / vizuální ukázka'),trm('Textový návod'),trm('Zkoušet metodou pokus–omyl'),trm('Samostatný projekt'),trm('Práce ve dvojici'),trm('Týmový projekt'),trm('Diskuze a vysvětlení proč'),trm('Konkrétní příklady z praxe'),trm('Možnost pracovat vlastním tempem')]); ?></div>
        <div class="u51-grid-2"><?php intake_v51_select('work_mode', trm('Nejraději pracuji'), [trm('Samostatně'),trm('Ve dvojici'),trm('V malé skupině'),trm('Podle úkolu – nevadí mi kombinace')]); intake_v51_select('pace', trm('Tempo mi vyhovuje spíš'), [trm('Rychlejší – rád/a se posouvám dál'),trm('Střední – ukázat, procvičit, pokračovat'),trm('Pomalejší – potřebuji víc času na procvičení'),trm('Individuálně – základ rychle a pak vlastní tempo')]); ?></div>
        <div class="u51-field"><span><?= e(tr('Jakou zpětnou vazbu preferuješ?')) ?></span><?php intake_v51_chips('feedback_styles', [trm('Hned při práci'),trm('Na konci úkolu'),trm('Konkrétně říct chybu a jak ji opravit'),trm('Spíš otázkami mě dovést k řešení'),trm('Krátce a věcně'),trm('Podrobněji vysvětlit souvislosti'),trm('Ukázat dobrý příklad')]); ?></div>
        <div class="u51-field"><span><?= e(tr('Kdy se ti nejlépe soustředí?')) ?></span><?php intake_v51_chips('focus_conditions', [trm('Když je jasný cíl'),trm('Když mám termín'),trm('Když je úkol praktický'),trm('Když si můžu zvolit vlastní téma'),trm('Když pracuji sám/sama'),trm('Když pracuji s někým'),trm('Když učitel průběžně kontroluje postup'),trm('Když mám klid a minimum vyrušení')]); ?></div>
        <div class="u51-grid-2">
        <?php intake_v51_range('ask_help', trm('Jak snadno se zeptáš, když něčemu nerozumíš?')); intake_v51_range('team_confidence', trm('Spolupráce v týmu')); intake_v51_range('presentation_confidence', trm('Prezentování své práce')); intake_v51_range('independence', trm('Samostatná práce bez vedení')); intake_v51_range('problem_solving', trm('Řešení neznámého problému')); intake_v51_range('organization', trm('Organizace práce a termíny')); ?>
        </div>
      </section>

      <?php intake_v51_step_open('s-tech', trm('Technika'), trm('Jen praktické informace pro plánování úkolů.')); ?>
        <div class="u51-field"><span><?= e(tr('K jakým zařízením máš přístup?')) ?></span><?php intake_v51_chips('devices', [trm('Stolní PC'),trm('Notebook'),trm('Tablet'),trm('Telefon'),trm('Grafický tablet'),trm('Fotoaparát / kamera'),trm('3D tiskárna'),trm('Raspberry Pi / mini PC'),trm('Vlastní server / NAS')]); ?></div>
        <div class="u51-field"><span><?= e(tr('S jakými systémy se setkáváš?')) ?></span><?php intake_v51_chips('systems', [trm('Windows'),trm('Linux'),trm('macOS'),trm('Android'),trm('iOS'),trm('WSL'),trm('Virtuální stroje')]); ?></div>
        <?php intake_v51_range('digital_confidence', trm('Celková jistota při práci s počítačem')); ?>
      </section>

      <?php intake_v51_step_open('s-subject', $type === 'graphics' ? trm('Grafika') : trm('OS a sítě'), trm('Tahle část je přizpůsobená tvému předmětu.')); ?>
      <?php if ($type === 'graphics'): ?>
        <div class="u51-field"><span><?= e(tr('S čím už jsi pracoval/a?')) ?></span><?php intake_v51_chips('subject_tools', [trm('Adobe Photoshop'),trm('Adobe Illustrator'),trm('Adobe InDesign'),trm('Figma'),trm('Canva'),trm('Affinity Photo / Designer'),trm('Krita / GIMP'),trm('Blender'),trm('DaVinci Resolve / Premiere'),trm('After Effects'),trm('Procreate'),trm('Generativní AI pro obraz')]); ?></div>
        <div class="u51-field"><span><?= e(tr('Co už jsi zkoušel/a tvořit?')) ?></span><?php intake_v51_chips('subject_skills', [trm('Logo'),trm('Plakát'),trm('Sociální post'),trm('Leták / tiskovina'),trm('Fotomontáž'),trm('Retuš fotografie'),trm('Vektorová ilustrace'),trm('Digitální kresba'),trm('Branding / vizuální identita'),trm('UI / webdesign'),trm('Animace / motion'),trm('3D grafika'),trm('Video'),trm('AI obraz')]); ?></div>
        <?php intake_v51_range('subject_confidence', trm('Jak si věříš v grafice?')); ?>
        <div class="u51-field"><span><?= e(tr('Co tě letos láká nejvíc?')) ?></span><?php intake_v51_chips('subject_topics', [trm('Kompozice'),trm('Barvy'),trm('Typografie'),trm('Logo'),trm('Branding'),trm('Plakát'),trm('Tisková grafika'),trm('Fotografie a retuš'),trm('Ilustrace'),trm('UI/UX'),trm('3D'),trm('Motion design'),trm('Video'),trm('AI workflow'),trm('Portfolio a prezentace práce')]); ?></div>
        <?php intake_v51_textarea('subject_detail_1', trm('Styl, autor nebo značka, která tě baví')); intake_v51_textarea('subject_detail_2', trm('Máš vlastní tvorbu nebo portfolio?')); intake_v51_textarea('subject_detail_3', trm('Co je na grafice nejtěžší nebo nejvíc matoucí?')); ?>
      <?php else: ?>
        <div class="u51-field"><span><?= e(tr('S čím už jsi prakticky pracoval/a?')) ?></span><?php intake_v51_chips('subject_tools', [trm('Windows správa'),trm('Linux desktop'),trm('Linux server'),trm('PowerShell'),trm('Bash / terminál'),trm('WSL'),trm('VirtualBox / VMware'),trm('Docker'),trm('Git / GitHub'),trm('Webserver'),trm('Raspberry Pi'),trm('NAS / domácí server')]); ?></div>
        <div class="u51-field"><span><?= e(tr('Co už jsi nastavoval/a nebo řešil/a?')) ?></span><?php intake_v51_chips('subject_skills', [trm('IP adresa'),trm('DHCP'),trm('DNS'),trm('Ping'),trm('Traceroute'),trm('Porty'),trm('Firewall'),trm('SSH'),trm('SFTP / FTP'),trm('Síťové sdílení'),trm('Uživatelé a oprávnění'),trm('Apache / Nginx'),trm('Doména a DNS záznamy'),trm('Docker kontejner'),trm('Virtualizace'),trm('Troubleshooting nefunkční služby')]); ?></div>
        <?php intake_v51_range('subject_confidence', trm('Jak si věříš v OS, sítích a troubleshootingu?')); ?>
        <div class="u51-field"><span><?= e(tr('Co tě letos láká nejvíc?')) ?></span><?php intake_v51_chips('subject_topics', [trm('Linux server'),trm('Síťování'),trm('SSH a vzdálená správa'),trm('Firewall a bezpečnost'),trm('Virtualizace'),trm('Docker'),trm('Webhosting'),trm('DNS a domény'),trm('Monitoring'),trm('Automatizace / skripty'),trm('Domácí server / homelab'),trm('Kyberbezpečnost'),trm('Troubleshooting'),trm('Cloud / VPS')]); ?></div>
        <?php intake_v51_textarea('subject_detail_1', trm('Co bys chtěl/a letos prakticky zprovoznit?')); intake_v51_textarea('subject_detail_2', trm('Nejsložitější technický problém, který jsi řešil/a')); intake_v51_textarea('subject_detail_3', trm('Téma, kterému ses zatím vyhýbal/a')); ?>
      <?php endif; ?>
      </section>

      <?php if ($type === 'networks_advanced'): ?>
      <?php intake_v51_step_open('s-past', trm('Minulý rok'), trm('Co zachovat a co letos udělat lépe.')); ?>
        <?php intake_v51_textarea('last_year_best', trm('Co ti minulý rok fungovalo nejlépe?')); intake_v51_textarea('last_year_change', trm('Co bych měl letos změnit?')); ?>
      </section>
      <?php endif; ?>

      <?php intake_v51_step_open('s-support', trm('Podpora'), trm('Soukromá část. Piš jen to, co chceš, abych věděl.')); ?>
        <div class="u51-grid-2"><?php intake_v51_textarea('teacher_support', trm('Co může učitel dělat, aby se ti učilo lépe?')); intake_v51_textarea('teacher_avoid', trm('Co ti při výuce nepomáhá?')); ?></div>
        <?php intake_v51_textarea('learning_blockers', trm('Praktická okolnost, která může ovlivnit tvou práci'), trm('Např. nemám doma vhodný software… Neuváděj zdravotní ani citlivé informace.')); ?>
        <?php intake_v51_textarea('fun_fact', trm('Jedna zajímavost o tobě')); intake_v51_textarea('question_teacher', trm('Chceš se na něco zeptat ty mě?')); intake_v51_textarea('anything_else', trm('Ještě něco, co bych měl vědět?')); ?>
      </section>

      <?php intake_v51_step_open('s-check', $type === 'graphics' ? trm('Praktický úkol') : trm('Ověření znalostí'), $type === 'graphics' ? trm('Nejde o dokonalost. Chci vidět, jak teď přemýšlíš o vizuálu.') : trm('Není na známku. Za každou správnou odpověď je 1 bod.'), true); ?>
      <?php if ($type === 'graphics'): ?>
        <div class="u51-brief">
          <strong><?= e(tr('Vytvoř v Canvě jednoduchý plakát')) ?></strong>
          <p><?= e(tr('Na školní nebo volnočasovou akci – koncert, workshop, esport turnaj…')) ?></p>
          <ul><li><b><?= e(tr('Formát')) ?></b> <?= e(tr('A4 na výšku')) ?></li><li><b><?= e(tr('Obsah')) ?></b> <?= e(tr('název akce, datum a čas, místo, výzva k akci')) ?></li><li><b><?= e(tr('Zaměř se na')) ?></b> <?= e(tr('hierarchii, kontrast a písmo')) ?></li><li><b><?= e(tr('Export')) ?></b> <?= e(tr('PNG, JPG, WEBP nebo PDF · max. 12 MB')) ?></li></ul>
          <a class="btn secondary" href="https://www.canva.com/" target="_blank" rel="noopener noreferrer"><?= e(tr('Otevřít Canvu')) ?></a>
        </div>
        <label class="u51-upload"><input type="file" name="graphics_poster" accept="image/png,image/jpeg,image/webp,application/pdf,.png,.jpg,.jpeg,.webp,.pdf" required data-u51-file><span class="u51-upload-box"><strong data-u51-file-name><?= e(tr('Vybrat hotový plakát')) ?></strong><small><?= e(tr('Klikni nebo sem soubor přetáhni')) ?></small></span><img alt="" hidden data-u51-file-preview></label>
        <?php intake_v51_textarea('poster_note', trm('Jednou větou: co bylo hlavní myšlenkou návrhu?')); ?>
      <?php elseif ($quiz): ?>
        <div class="u51-quiz" data-u51-quiz data-max="<?= count($quiz) ?>"<?= edu_content_lang_attr() ?>>
          <div class="u51-quiz-status"><span><?= tr_html('Zodpovězeno {count} / {max}', ['count' => '<b data-u51-quiz-count>0</b>', 'max' => (string)count($quiz)]) ?></span><span><?= e(tr('Max. {n} bodů', ['n' => count($quiz)])) ?></span></div>
          <?php foreach ($quiz as $i => $q): ?>
          <fieldset class="u51-question"><legend><span><?= $i + 1 ?></span><?= e((string)$q['question']) ?><em><?= e(tr('1 b.')) ?></em></legend>
            <?php foreach ($q['options'] as $key => $text): ?>
              <label class="u51-option"><input type="radio" name="assessment_answers[<?= e((string)$q['id']) ?>]" value="<?= e((string)$key) ?>" required<?= (($_SESSION['intake_old']['assessment_answers'][$q['id']] ?? '') === $key) ? ' checked' : '' ?>><span><b><?= strtoupper(e((string)$key)) ?></b><?= e((string)$text) ?></span></label>
            <?php endforeach; ?>
          </fieldset>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
        <label class="u51-consent"><input type="checkbox" name="privacy_ack" value="1" required<?= intake_v51_old('privacy_ack') !== '' ? ' checked' : '' ?>><span><strong><?= e(tr('Beru na vědomí, k čemu odpovědi slouží.')) ?></strong> <?= e(tr('Dotazník není anonymní. Odpovědi a výsledek vidí jen učitel a slouží k plánování výuky.')) ?></span></label>
      </section>

      <nav class="u51-wizard-nav">
        <button type="button" class="btn secondary" data-u51-prev><?= e(tr('Zpět')) ?></button>
        <span class="u51-step-title" data-u51-title></span>
        <button type="button" class="btn primary" data-u51-next><?= e(tr('Pokračovat')) ?></button>
        <button type="submit" class="btn primary" data-u51-submit hidden><?= e(tr('Odeslat dotazník')) ?></button>
      </nav>
    </form>
    </div>
    <?php
    unset($_SESSION['intake_old']);
    render_footer();
}

function intake_v51_render_my_intake_view(string $classId, array $modules, string $flash): void
{
    $classes = intake_v51_classes($modules);
    $class = $classes[$classId] ?? null;
    $response = intake_v51_response_for_student($classId, intake_v51_current_label());
    if (!$class || !$response) redirect_to('?view=intake');
    render_header(tr('Moje odpovědi'), $modules[$classId]);
    $submitted = strtotime((string)($response['submitted_at'] ?? '')) ?: time();
    ?>
    <section class="u51-page">
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>
      <header class="u51-page-head">
        <div><span class="u51-kicker"><?= e(tr('Seznamovací dotazník')) ?> · <?= edu_cs((string)$class['name']) ?></span><h1><?= e(tr('Moje odpovědi')) ?></h1><p><?= e(tr('Vyplněno {date}', ['date' => edu_date($submitted, 'date')])) ?><?= ($response['source'] ?? '') === 'V1' ? ' ' . e(tr('v původním dotazníku')) : '' ?> · <?= edu_cs((string)($response['student']['seat_label'] ?? '')) ?></p></div>
        <a class="btn primary" href="?view=dashboard"><?= e(tr('Pokračovat ve výuce')) ?></a>
      </header>
      <?php intake_v51_render_assessment($response, (string)$class['course_type'], '?view=my_intake_file', true); ?>
      <?php intake_v51_render_answers($response, (string)$class['course_type']); ?>
    </section>
    <?php
    render_footer();
}
