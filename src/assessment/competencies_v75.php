<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · fáze E (docs/PROMPT_V71_V75.md): kompetence a výukové cesty pro 2.A a 4.A.
 *
 * Katalogy jsou NÁVRH (obsah navržený agentem). Třída se do pilotu kompetencí v62 a cest v63 zapojí až po schválení
 * učitelem s rozsahem třídy (záložka cockpitu `kompetence75`, akce kp75_approve / kp75_return). Do té doby se 2.A a 4.A
 * chovají jako dřív (žádné důkazy, žádná záložka žáka) a katalog vidí jen učitel v náhledu.
 *   - 2.A: předmět `grafika_web_ii` (Grafika a webdesign II, 8 kompetencí, úroveň II navazuje na 1.A),
 *   - 4.A: předmět `provoz_site` (Provoz sítí a služeb, 11 kompetencí: směrování II, proxy a TLS, pozorovatelnost a SLO,
 *     diagnostika, změna a rollback, záloha, IaC, výkon, incident, zabezpečení, služby).
 * Každá kompetence má tagy topic:<téma lekce> (všechna témata lekcí 2.A/4.A jsou namapovaná – hlídá audit) a vazbu na
 * výsledky ŠVP v72 (svp72_outcomes, jen existující id). Řízená změna verze: schválení si pamatuje otisk katalogu; změní-li se
 * katalog, cockpit ukáže „změněno od schválení“ a učitel změnu potvrdí znovu (třída zůstává zapojená – žáci o nic nepřijdou).
 * Stav: storage/competency_v75_status.json.php = {"v":1,"classes":{třída:{status,hash,at,by,note}}} – zápis jen storage_update,
 * bez dat žáků.
 */

const COMP75_CLASSES = ['class_2a', 'class_4a'];
const COMP75_STATUS = ['navrh' => 'návrh', 'schvaleno' => 'schváleno', 'vraceno' => 'vráceno k úpravě'];
const COMP75_ACTIONS = ['kp75_approve', 'kp75_return'];
const COMP75_NOTE_MAX = 300;
/** Obsah cest tříd v75 (načítá ho paths_v63.php až po schválení třídy). */
const COMP75_PATH_FILES = ['class_2a' => 'paths_v75_content_gfx2.php', 'class_4a' => 'paths_v75_content_ops.php'];

/** Návrh katalogů (stejný tvar jako comp62_catalog()). @return array<string,array<string,mixed>> */
function comp75_draft_catalog(): array
{
    return [
        'grafika_web_ii' => [
            'label' => 'Grafika a webdesign II',
            'classes' => ['class_2a'],
            'areas' => [
                'web' => ['label' => 'Web', 'competencies' => [
                    'w2_design_system' => ['label' => 'Umím sestavit design systém z tokenů, mezer a komponent', 'level' => 3,
                        'tags' => ['topic:ui-spacing-system', 'topic:component-consistency', 'topic:design-tokens-ii', 'topic:auto-layout-ii', 'topic:branding-system']],
                    'w2_css_responsive' => ['label' => 'Umím převést návrh do responzivního rozvržení v CSS', 'level' => 3,
                        'tags' => ['topic:css-responsive-ii', 'topic:html-css-handoff-ii', 'topic:responsive-adaptation', 'bank:gfx-css']],
                    'w2_a11y_audit' => ['label' => 'Umím provést audit přístupnosti návrhu a navrhnout opravy', 'level' => 3,
                        'tags' => ['topic:accessibility-design', 'topic:accessibility-audit-ii', 'topic:interaction-accessibility', 'bank:gfx-a11y']],
                    'w2_forms_states' => ['label' => 'Umím navrhnout formulář se stavy a srozumitelnými chybami', 'level' => 3,
                        'tags' => ['topic:form-states-ii', 'topic:error-recovery-ux', 'topic:interaction-patterns-ii', 'topic:microinteraction-storyboard']],
                    'w2_ux_research' => ['label' => 'Umím ověřit použitelnost testem a uspořádat obsah', 'level' => 3,
                        'tags' => ['topic:usability-test-ii', 'topic:card-sorting', 'topic:information-architecture-ii', 'bank:gfx-ux']],
                ]],
                'grafika' => ['label' => 'Grafika', 'competencies' => [
                    'g2_typography' => ['label' => 'Umím navrhnout responzivní typografickou škálu', 'level' => 3,
                        'tags' => ['topic:typography', 'topic:responsive-type-ii', 'bank:gfx-type']],
                    'g2_visual_system' => ['label' => 'Umím vytvořit vizuální kampaň s jasnou hierarchií a kontrastem', 'level' => 3,
                        'tags' => ['topic:hierarchy', 'topic:composition', 'topic:contrast-color', 'topic:photo-treatment', 'topic:export', 'bank:gfx-color', 'bank:gfx-formats', 'tg:graphics']],
                    'g2_handoff' => ['label' => 'Umím předat návrh k realizaci a obhájit ho v případové studii', 'level' => 3,
                        'tags' => ['topic:design-handoff-qa', 'topic:design-qa-handoff-ii', 'topic:design-critique-ii', 'topic:portfolio-case-study', 'topic:portfolio-presentation', 'bank:gfx-license']],
                ]],
            ],
        ],
        'provoz_site' => [
            'label' => 'Provoz sítí a služeb',
            'classes' => ['class_4a'],
            'areas' => [
                'site' => ['label' => 'Sítě', 'competencies' => [
                    'ops_routing' => ['label' => 'Umím navrhnout adresní plán a směrování sítě', 'level' => 3,
                        'tags' => ['topic:cidr', 'topic:binding', 'topic:dns-advanced', 'topic:container-networking', 'bank:net-ip']],
                    'ops_proxy_tls' => ['label' => 'Umím nastavit reverzní proxy a ověřit certifikát TLS', 'level' => 3,
                        'tags' => ['topic:reverse-proxy', 'topic:tls-certificates', 'topic:load-balancing', 'bank:net-ports']],
                    'ops_network_diag' => ['label' => 'Umím diagnostikovat síť podle paketů a stavu spojení', 'level' => 3,
                        'tags' => ['topic:packet-diagnostics-advanced', 'topic:tcp', 'topic:diagnostics']],
                    'ops_hardening' => ['label' => 'Umím zabezpečit službu: SSH, firewall, práva a aktualizace', 'level' => 3,
                        'tags' => ['topic:ssh-hardening', 'bank:net-security']],
                ]],
                'provoz' => ['label' => 'Provoz a spolehlivost', 'competencies' => [
                    'ops_observability' => ['label' => 'Umím sledovat službu pomocí logů, metrik a cílů SLO', 'level' => 3,
                        'tags' => ['topic:logs-monitoring', 'topic:http-observability', 'topic:golden-signals', 'topic:slo-postmortem']],
                    'ops_change' => ['label' => 'Umím provést řízenou změnu s plánem návratu', 'level' => 3,
                        'tags' => ['topic:change-management', 'topic:canary-release', 'topic:rollback-strategy']],
                    'ops_backup' => ['label' => 'Umím navrhnout zálohu podle RPO a RTO a ověřit obnovu', 'level' => 3,
                        'tags' => ['topic:backup-strategy', 'topic:backup-restore', 'topic:storage-filesystems', 'bank:ops-backup']],
                    'ops_iac' => ['label' => 'Umím popsat infrastrukturu jako kód a najít odchylku konfigurace', 'level' => 3,
                        'tags' => ['topic:infrastructure-as-code', 'topic:config-drift', 'topic:automation-shell']],
                    'ops_services' => ['label' => 'Umím spravovat služby a kontejnery včetně závislostí a restartu', 'level' => 3,
                        'tags' => ['topic:systemd-advanced', 'topic:containers-basics']],
                    'ops_performance' => ['label' => 'Umím změřit výkon a naplánovat kapacitu', 'level' => 3,
                        'tags' => ['topic:performance-engineering', 'topic:capacity-planning']],
                    'ops_incident' => ['label' => 'Umím vést incident podle runbooku a sepsat rozbor bez obviňování', 'level' => 4,
                        'tags' => ['topic:incident-runbook', 'topic:incident-command', 'topic:slo-postmortem']],
                ]],
            ],
        ],
    ];
}

/** Vazba kompetencí na výsledky ŠVP v72 (id ze svp72_outcomes()). @return array<string,list<string>> */
function comp75_svp_map(): array
{
    return [
        'w2_design_system' => ['ids26-dgd1-02', 'ids26-dgd2-01'], 'w2_css_responsive' => ['ids26-fev2-01', 'ids26-fev2-03'],
        'w2_a11y_audit' => ['ids26-fev2-01', 'ids26-fev2-02'], 'w2_forms_states' => ['ids26-dgd2-01', 'ids26-fev2-02'],
        'w2_ux_research' => ['ids26-dgd2-01'], 'g2_typography' => ['ids26-dgd1-02', 'ids26-fev2-03'],
        'g2_visual_system' => ['ids26-dgd1-02', 'ids26-dgd1-04'], 'g2_handoff' => ['ids26-as1-01', 'ids26-dgd2-02'],
        'ops_routing' => ['ids26-sbe4-03', 'ids26-sbe4-05', 'ids26-sbe4-06'], 'ops_proxy_tls' => ['ids26-sbe4-02', 'ids26-sav4-04'],
        'ops_network_diag' => ['ids26-sbe4-09', 'ids26-sbe4-10', 'ids26-sbe4-11'], 'ops_hardening' => ['ids26-sav4-01', 'ids26-sav4-03', 'ids26-sbe4-07', 'ids26-sbe4-08'],
        'ops_observability' => ['ids26-sav3-07'], 'ops_change' => ['ids26-sav3-03', 'ids26-sav3-07'],
        'ops_backup' => ['ids26-sav3-05', 'ids26-sav3-09', 'ids26-sav4-02'], 'ops_iac' => ['ids26-sav3-04', 'ids26-sav3-08'],
        'ops_services' => ['ids26-sav3-03', 'ids26-sav3-08'], 'ops_performance' => ['ids26-asy4-01', 'ids26-asy4-02', 'ids26-sav3-09'],
        'ops_incident' => ['ids26-sbe4-09', 'ids26-sbe4-11', 'ids26-sav3-07'],
    ];
}

function comp75_status_path(): string
{
    return STORAGE_DIR . '/competency_v75_status.json.php';
}

/** Předmět návrhu pro třídu v75, nebo null. */
function comp75_subject_for_class(string $classId): ?string
{
    foreach (comp75_draft_catalog() as $subject => $row) if (in_array($classId, (array)$row['classes'], true)) return (string)$subject;
    return null;
}

/** Otisk návrhu katalogu třídy včetně cest a vazby na ŠVP (řízená změna verze). */
function comp75_hash(string $classId): string
{
    $subject = comp75_subject_for_class($classId);
    if ($subject === null) return '';
    $catalog = comp75_draft_catalog()[$subject];
    $map = array_intersect_key(comp75_svp_map(), comp75_flat($subject));
    $paths = is_file(__DIR__ . '/' . (COMP75_PATH_FILES[$classId] ?? '')) ? comp75_draft_paths($classId) : [];
    return substr(sha1((string)json_encode([$catalog, $map, array_map(static fn(array $p): array => [$p['competency'], array_column((array)$p['steps'], 'id')], $paths)])), 0, 12);
}

/** Plochý seznam kompetencí návrhu (tvar comp62_competencies). @return array<string,array<string,mixed>> */
function comp75_flat(string $subject): array
{
    $out = [];
    foreach ((array)(comp75_draft_catalog()[$subject]['areas'] ?? []) as $areaId => $area) {
        foreach ((array)$area['competencies'] as $id => $c) {
            $out[(string)$id] = ['id' => (string)$id, 'area' => (string)$areaId, 'area_label' => (string)$area['label'], 'label' => (string)$c['label'], 'level' => (int)$c['level'], 'tags' => array_values((array)$c['tags'])];
        }
    }
    return $out;
}

/** Cesty návrhu třídy přímo ze souboru (náhled pro učitele i před schválením). @return array<string,array<string,mixed>> */
function comp75_draft_paths(string $classId): array
{
    static $memo = [];
    if (!isset(COMP75_PATH_FILES[$classId])) return [];
    if (isset($memo[$classId])) return $memo[$classId];
    $data = require __DIR__ . '/' . COMP75_PATH_FILES[$classId];
    $out = [];
    foreach ((array)$data as $id => $p) if (is_array($p)) $out[(string)$id] = ['id' => (string)$id, 'class' => $classId] + $p;
    return $memo[$classId] = $out;
}

function comp75_data(): array
{
    $d = storage_read(comp75_status_path(), false);
    return is_array($d) ? $d : [];
}

/** Stav třídy: status (navrh|schvaleno|vraceno), changed (katalog se od schválení změnil), at, by, note, hash. */
function comp75_status(string $classId, ?array $data = null): array
{
    $row = (array)((($data ?? comp75_data())['classes'] ?? [])[$classId] ?? []);
    $status = isset(COMP75_STATUS[(string)($row['status'] ?? '')]) ? (string)$row['status'] : 'navrh';
    return ['status' => $status, 'changed' => $status === 'schvaleno' && (string)($row['hash'] ?? '') !== comp75_hash($classId),
        'at' => (string)($row['at'] ?? ''), 'by' => (string)($row['by'] ?? ''), 'note' => (string)($row['note'] ?? ''), 'hash' => (string)($row['hash'] ?? '')];
}

/** Je třída v75 schválená (zapojená do kompetencí a cest)? Čte malý soubor stavu (paměť požadavku). */
function comp75_class_active(string $classId): bool
{
    static $memo = [];
    if (!in_array($classId, COMP75_CLASSES, true) || !defined('STORAGE_DIR')) return false;
    // Paměť do dalšího zápisu tímto procesem (storage_epoch): katalog v62 se čte mnohokrát za požadavek a stat() na Windows není zadarmo.
    $epoch = function_exists('storage_epoch') ? storage_epoch() : 0;
    if (isset($memo[$epoch][$classId])) return $memo[$epoch][$classId];
    $active = false;
    if (is_file(comp75_status_path())) {   // bez rozhodnutí žádné čtení úložiště (počty čtení v auditech)
        $data = function_exists('storage_read_request') ? storage_read_request(comp75_status_path(), false) : comp75_data();
        $active = (string)((($data['classes'] ?? [])[$classId] ?? [])['status'] ?? '') === 'schvaleno';
    }
    $memo = [$epoch => [$classId => $active] + ($memo[$epoch] ?? [])];
    return $active;
}

/** Předměty v75 schválených tříd (sloučí je comp62_catalog()). @return array<string,array<string,mixed>> */
function comp75_active_catalog(): array
{
    $out = [];
    foreach (comp75_draft_catalog() as $subject => $row) {
        $classes = array_values(array_filter((array)$row['classes'], 'comp75_class_active'));
        if ($classes !== []) $out[$subject] = ['classes' => $classes] + $row;
    }
    return $out;
}

/** Soubory cest schválených tříd v75 (sloučí je p63_class_files()). @return array<string,string> */
function comp75_active_path_files(): array
{
    return array_filter(COMP75_PATH_FILES, static fn(string $file, string $classId): bool => comp75_class_active($classId), ARRAY_FILTER_USE_BOTH);
}

/**
 * Pokrytí témat lekcí třídy tagy návrhu: [témata celkem, namapovaná, nenamapovaná (seznam), lekce → kompetence].
 * @param array<int,list<string>> $lessonTopics číslo lekce → klíče témat
 */
function comp75_coverage(string $classId, array $lessonTopics): array
{
    $subject = comp75_subject_for_class($classId) ?? comp62_subject_for_class($classId);
    $flat = $subject === null ? [] : (isset(comp75_draft_catalog()[$subject]) ? comp75_flat($subject) : comp62_competencies($subject));
    $byTag = [];
    foreach ($flat as $id => $c) foreach ($c['tags'] as $t) $byTag[$t][] = $id;
    $topics = [];
    $lessons = [];
    foreach ($lessonTopics as $n => $keys) {
        $ids = [];
        foreach ($keys as $k) {
            $topics[$k] = isset($byTag['topic:' . $k]);
            foreach ($byTag['topic:' . $k] ?? [] as $id) $ids[$id] = true;
        }
        $lessons[(int)$n] = array_keys($ids);
    }
    $missing = array_keys(array_filter($topics, static fn(bool $ok): bool => !$ok));
    return ['total' => count($topics), 'mapped' => count($topics) - count($missing), 'missing' => $missing, 'lessons' => $lessons];
}

/** Schválení / vrácení návrhu třídy (handler ověřil rozsah). Vrací nový stav. */
function comp75_decide(string $action, string $classId, string $note, string $by, ?int $now = null): array
{
    if (!in_array($action, COMP75_ACTIONS, true)) throw new RuntimeException('Neznámá akce.');
    if (!in_array($classId, COMP75_CLASSES, true)) throw new RuntimeException('Třída nemá návrh kompetencí v75.');
    $note = trim((string)preg_replace('/\s+/u', ' ', strip_tags($note)));
    if ($action === 'kp75_return' && $note === '') throw new RuntimeException('Napište krátce, co upravit – návrh se vrací s poznámkou.');
    $row = ['status' => $action === 'kp75_approve' ? 'schvaleno' : 'vraceno', 'hash' => comp75_hash($classId), 'at' => date(DATE_ATOM, $now ?? time()),
        'by' => mb_substr(trim($by) !== '' ? trim($by) : 'učitel', 0, 60), 'note' => mb_substr($note, 0, COMP75_NOTE_MAX)];
    storage_update(comp75_status_path(), static function (array $d) use ($classId, $row): array {
        $classes = array_filter((array)($d['classes'] ?? []), 'is_array');
        $classes[$classId] = $row;
        return ['v' => 1, 'classes' => $classes];
    });
    return comp75_status($classId);
}

/** POST kp75_* (CSRF, politiku a oprávnění content.manage ověřil teacher.php). */
function comp75_teacher_handle_post(string $action): void
{
    if (!in_array($action, COMP75_ACTIONS, true)) throw new RuntimeException('Neznámá akce kompetencí v75.');
    $classId = is_string($_POST['class_id'] ?? null) ? (string)$_POST['class_id'] : '';
    if (!in_array($classId, COMP75_CLASSES, true) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není v rozsahu.');
    if ($action === 'kp75_approve' && (string)($_POST['confirm'] ?? '') !== '1') throw new RuntimeException('Schválení je potřeba potvrdit zaškrtnutím.');
    $by = function_exists('teacher_display_name') ? teacher_display_name() : 'učitel';
    $st = comp75_decide($action, $classId, is_string($_POST['note'] ?? null) ? (string)$_POST['note'] : '', $by);
    $_SESSION['teacher_export_flash'] = ['type' => 'ok', 'message' => $st['status'] === 'schvaleno'
        ? 'Kompetence a cesty jsou schválené – třída je zapojená. Důkazy se začnou počítat při další práci žáků (nebo tlačítkem Přepočítat důkazy v záložce Kompetence).'
        : 'Návrh je vrácený k úpravě s vaší poznámkou. Žáci nic nevidí.'];
    $query = ['tab' => 'kompetence75', 'class' => $classId];
    if (function_exists('teacher_redirect')) teacher_redirect($query);
    redirect_to('teacher.php?' . http_build_query($query));
}
