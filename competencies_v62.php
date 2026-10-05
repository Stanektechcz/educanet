<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · katalog kompetencí (páteř učení, fáze 1).
 *
 * Strom předmět → oblast → kompetence („Umím …“), úrovně 1–4 (pamatuje / použije / analyzuje / tvoří).
 * Každá kompetence má tagy, podle kterých se k ní automaticky přiřazují aktivity:
 *   cmd:<příkaz>   příkaz Linux Labu u úrovně        pack:<balíček>  balíček úrovní labu
 *   topic:<kb>     téma znalostní báze (test, lekce) bank:<id>       banka otázek (rezerva)
 *   tg:<linka>     linka týmové hry (networks/graphics)
 * Aktivita bez shody se nezapíše a audit ji hlásí jako WARN (ne FAIL). Ruční výjimky: COMP62_OVERRIDES.
 * Pilot: třída 3.A (od v62) a 1.A (od v63, rozhodnutí školy: výukové cesty 1.A zapisují důkazy); ostatní třídy tuto vrstvu nevidí a nic se jim nepočítá.
 * Katalog je čistá data bez zápisu do storage.
 */

/** Třídy, kde vrstva běží (rozhodnutí školy: pilot 3.A, od v63 i 1.A). */
const COMP62_PILOT_CLASSES = ['class_3a', 'class_1a'];
/** Retence důkazů: do konce studia, smazání 30 dní po stavu left/archived (rozhodnutí školy). */
const COMP62_RETENTION_GRACE_DAYS = 30;
/** Nejvýš kolik kompetencí dostane jedna aktivita (nejlepší shody podle počtu společných tagů). */
const COMP62_MATCH_LIMIT = 2;
/** Ruční výjimky: „ref“ artefaktu (např. lab:sit-4) → seznam kompetencí. Má přednost před tagy. */
const COMP62_OVERRIDES = [];

function comp62_enabled_for_class(string $classId): bool
{
    return in_array($classId, COMP62_PILOT_CLASSES, true);
}

/** @return array<int,string> úrovně kompetence */
function comp62_levels(): array
{
    return [1 => 'pamatuje', 2 => 'použije', 3 => 'analyzuje', 4 => 'tvoří'];
}

function comp62_level_label(int $level): string
{
    return comp62_levels()[$level] ?? comp62_levels()[1];
}

/**
 * Strom kompetencí. Klíč předmětu → ['label','classes','areas' => [oblast => ['label','competencies' => [id => ['label','level','tags']]]]].
 * @return array<string,array<string,mixed>>
 */
function comp62_catalog(): array
{
    return [
        'os_site' => [
            'label' => 'Operační systémy a sítě',
            'classes' => ['class_3a'],
            'areas' => [
                'site' => ['label' => 'Sítě', 'competencies' => [
                    'net_addressing' => ['label' => 'Umím určit síť, masku a adresu zařízení', 'level' => 2,
                        'tags' => ['topic:ip-addressing', 'topic:subnetting-vlsm', 'topic:ipv6-basics', 'topic:arp']],
                    'net_dns_dhcp' => ['label' => 'Umím vysvětlit DNS a DHCP a ověřit je v praxi', 'level' => 2,
                        'tags' => ['topic:dns', 'topic:dhcp', 'topic:dns-record-types', 'topic:dhcp-reservations', 'topic:dns-dhcp-operations', 'cmd:dig', 'cmd:getent']],
                    'net_diagnose' => ['label' => 'Umím krok za krokem diagnostikovat síťový problém', 'level' => 3,
                        'tags' => ['topic:troubleshooting', 'topic:icmp', 'topic:routing', 'topic:service-debug-chain', 'cmd:ping', 'cmd:traceroute', 'cmd:ip', 'pack:sit', 'tg:networks']],
                    'net_services' => ['label' => 'Umím rozlišit porty a služby a ověřit, co poslouchá', 'level' => 2,
                        'tags' => ['topic:ports', 'topic:service-matrix', 'topic:https', 'cmd:ss', 'cmd:nc', 'cmd:curl']],
                    'net_security' => ['label' => 'Umím popsat základy zabezpečení sítě a firewallu', 'level' => 2,
                        'tags' => ['topic:network-security-basics', 'topic:linux-firewall', 'topic:stateful-firewall', 'topic:nat', 'topic:vlan-basics', 'topic:packet-analysis', 'topic:monitoring-basics']],
                ]],
                'linux' => ['label' => 'Linux', 'competencies' => [
                    'lnx_navigation' => ['label' => 'Umím se pohybovat v shellu a pracovat se soubory', 'level' => 1,
                        'tags' => ['topic:linux-filesystem', 'pack:start', 'pack:quest', 'cmd:pwd', 'cmd:ls', 'cmd:cd', 'cmd:mkdir', 'cmd:cp', 'cmd:mv', 'cmd:rm', 'cmd:touch', 'cmd:find', 'cmd:less', 'cmd:head', 'cmd:file']],
                    'lnx_text' => ['label' => 'Umím filtrovat a zpracovat text v příkazové řádce', 'level' => 2,
                        'tags' => ['pack:golf', 'cmd:grep', 'cmd:sort', 'cmd:uniq', 'cmd:wc', 'cmd:cut', 'cmd:tr', 'cmd:sed', 'cmd:awk']],
                    'lnx_users' => ['label' => 'Umím spravovat uživatele a oprávnění', 'level' => 2,
                        'tags' => ['topic:users-permissions', 'pack:prava', 'cmd:chmod', 'cmd:chown', 'cmd:useradd', 'cmd:usermod', 'cmd:groupadd', 'cmd:id', 'cmd:umask', 'cmd:sudo', 'cmd:passwd', 'cmd:visudo']],
                    'lnx_services' => ['label' => 'Umím spravovat služby a číst systémové logy', 'level' => 3,
                        'tags' => ['topic:processes-systemd', 'topic:journal-logs', 'topic:web-service-linux', 'topic:linux-file-troubleshooting', 'pack:opravna', 'pack:incident', 'cmd:systemctl', 'cmd:journalctl', 'cmd:ps', 'cmd:top', 'cmd:kill', 'cmd:nginx', 'cmd:df', 'cmd:du', 'cmd:tail']],
                    'lnx_ssh' => ['label' => 'Umím se bezpečně připojit přes SSH a klíče', 'level' => 2,
                        'tags' => ['topic:ssh-sftp', 'topic:ssh-keys-ops', 'topic:ssh-key-operations', 'pack:klice', 'cmd:ssh', 'cmd:ssh-keygen', 'cmd:ssh-copy-id', 'cmd:scp']],
                    'lnx_automation' => ['label' => 'Umím automatizovat úlohy skriptem a cronem', 'level' => 3,
                        'tags' => ['topic:shell-cron', 'topic:bash-error-handling', 'pack:cron', 'cmd:crontab', 'robots:algo']],
                ]],
            ],
        ],
        // v63 · rozhodnutí školy: katalog pro grafiku a webdesign (1.A). Tagy topic: z témat znalostní báze 1.A/2.A,
        // bank: z kategorií banky týmových her gfx.* (color, type, formats, html, css, a11y, ux, license).
        'grafika_web' => [
            'label' => 'Grafika a webdesign',
            'classes' => ['class_1a'],
            'areas' => [
                'web' => ['label' => 'Web', 'competencies' => [
                    'web_html_structure' => ['label' => 'Umím postavit sémantickou kostru webové stránky', 'level' => 2,
                        'tags' => ['topic:web-layout-basics-i', 'topic:components-i', 'bank:gfx-html', 'tg:graphics']],
                    'web_css_layout' => ['label' => 'Umím rozmístit prvky pomocí CSS a přizpůsobit stránku úzkému displeji', 'level' => 2,
                        'tags' => ['topic:responsive-layout-i', 'topic:responsive-art-direction', 'topic:responsive-series', 'topic:spacing', 'topic:layout-rhythm-i', 'bank:gfx-css']],
                    'web_a11y' => ['label' => 'Umím posoudit přístupnost webu a opravit běžné chyby', 'level' => 3,
                        'tags' => ['topic:forms-a11y-i', 'bank:gfx-a11y']],
                    'web_ux' => ['label' => 'Umím navrhnout srozumitelné ovládání a text výzvy k akci', 'level' => 3,
                        'tags' => ['topic:cta', 'topic:microcopy-cta', 'topic:content-first-layout', 'topic:design-system-i', 'topic:design-feedback', 'topic:template-critique', 'bank:gfx-ux']],
                ]],
                'grafika' => ['label' => 'Grafika', 'competencies' => [
                    'gfx_color_contrast' => ['label' => 'Umím zvolit barvy a ověřit kontrast textu', 'level' => 2,
                        'tags' => ['topic:contrast-color', 'topic:color', 'topic:color-harmony', 'bank:gfx-color', 'tg:graphics']],
                    'gfx_typography' => ['label' => 'Umím zvolit a skloubit písma pro čitelný text', 'level' => 2,
                        'tags' => ['topic:typography', 'topic:editorial-typography-i', 'topic:web-typography-i', 'bank:gfx-type']],
                    'gfx_formats' => ['label' => 'Umím vybrat správný grafický formát a export', 'level' => 2,
                        'tags' => ['topic:raster-vector', 'topic:export', 'topic:assets', 'topic:image-web-i', 'topic:production-preflight-i', 'topic:preflight', 'bank:gfx-formats', 'bank:gfx-license']],
                    'gfx_composition' => ['label' => 'Umím vystavět kompozici s jasnou hierarchií', 'level' => 2,
                        'tags' => ['topic:composition', 'topic:hierarchy', 'topic:image-composition', 'topic:image-crop', 'topic:visual-story-i', 'topic:iconography']],
                ]],
            ],
        ],
    ];
}

/** Předmět kompetencí pro třídu, nebo null (třída mimo katalog). */
function comp62_subject_for_class(string $classId): ?string
{
    foreach (comp62_catalog() as $subject => $row) {
        if (in_array($classId, (array)$row['classes'], true)) return (string)$subject;
    }
    return null;
}

/**
 * Plochý seznam kompetencí předmětu v pořadí katalogu: id => ['id','area','area_label','label','level','tags'].
 * @return array<string,array<string,mixed>>
 */
function comp62_competencies(string $subject): array
{
    $out = [];
    foreach ((array)(comp62_catalog()[$subject]['areas'] ?? []) as $areaId => $area) {
        foreach ((array)$area['competencies'] as $id => $c) {
            $out[(string)$id] = ['id' => (string)$id, 'area' => (string)$areaId, 'area_label' => (string)$area['label'], 'label' => (string)$c['label'], 'level' => (int)$c['level'], 'tags' => array_values((array)$c['tags'])];
        }
    }
    return $out;
}

/** Otisk katalogu – mění se s jakoukoli úpravou kompetencí nebo tagů (invaliduje cache zvládnutí). */
function comp62_catalog_version(): string
{
    static $version = null;
    return $version ??= substr(sha1((string)json_encode([comp62_catalog(), COMP62_OVERRIDES])), 0, 12);
}

/**
 * Přiřadí aktivitu ke kompetencím: ruční výjimka podle $ref, jinak kompetence s největším počtem
 * společných tagů (shodné pořadí podle katalogu, nejvýš COMP62_MATCH_LIMIT). Prázdný výsledek = nenamapováno.
 * @param list<string> $tags
 * @return list<string> id kompetencí
 */
function comp62_match(string $subject, array $tags, string $ref = ''): array
{
    $competencies = comp62_competencies($subject);
    if ($ref !== '' && isset(COMP62_OVERRIDES[$ref])) {
        return array_values(array_filter((array)COMP62_OVERRIDES[$ref], static fn($id): bool => isset($competencies[(string)$id])));
    }
    $have = array_flip(array_map('strval', $tags));
    $scored = [];
    $order = 0;
    foreach ($competencies as $id => $c) {
        $hits = 0;
        foreach ($c['tags'] as $tag) if (isset($have[$tag])) $hits++;
        if ($hits > 0) $scored[] = [$hits, $order, $id];
        $order++;
    }
    usort($scored, static fn(array $a, array $b): int => $b[0] <=> $a[0] ?: $a[1] <=> $b[1]);
    return array_values(array_map(static fn(array $r): string => (string)$r[2], array_slice($scored, 0, COMP62_MATCH_LIMIT)));
}
