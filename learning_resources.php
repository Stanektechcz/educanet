<?php

declare(strict_types=1);

$canvaCourse = 'https://www.canva.com/design-school/courses/graphic-design-essentials?lesson=understanding-the-principles-of-design';
$canvaCourses = 'https://www.canva.com/design-school/courses/';
$cisco = 'https://skillsforall.com/';
$cfDns = 'https://www.cloudflare.com/learning/dns/what-is-dns/';
$cfTls = 'https://www.cloudflare.com/learning/ssl/what-happens-in-a-tls-handshake/';
$mdnHttp = 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status';

$videos = [
    // Grafika / design — ověřené české zdroje. Délky s „cca“ jsou orientační.
    'canva' => ['type'=>'video','title'=>'Canva – ovládni design v Canvě','url'=>'https://www.youtube.com/watch?v=ZLeahsLAw-U','youtube_id'=>'ZLeahsLAw-U','lang'=>'CZ','duration'=>'30 min','level'=>'Začátečník','description'=>'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.'],
    'figma-short' => ['type'=>'video','title'=>'Nejkratší Figma Kurz na Světě','url'=>'https://www.youtube.com/watch?v=2AN92es01YQ','youtube_id'=>'2AN92es01YQ','lang'=>'CZ','duration'=>'18 min','level'=>'Začátečník','description'=>'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.'],
    'figma-deep' => ['type'=>'video','title'=>'Figma do hloubky: Auto layout, komponenty a interakce','url'=>'https://www.youtube.com/watch?v=2MfzBQoTC1o','youtube_id'=>'2MfzBQoTC1o','lang'=>'CZ','duration'=>'90 min','level'=>'Středně pokročilý','description'=>'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.'],
    'vector' => ['type'=>'video','title'=>'Jak na vektorovou grafiku #1 – úvod a Inkscape','url'=>'https://www.youtube.com/watch?v=9gz3GIP6r5w','youtube_id'=>'9gz3GIP6r5w','lang'=>'CZ','duration'=>'cca 10–15 min','level'=>'Začátečník','description'=>'Srozumitelný český úvod do vektorové grafiky, jejích výhod, omezení a vhodného použití.'],
    'color' => ['type'=>'video','title'=>'Barevné prostory – Lab, CMYK a RGB','url'=>'https://www.youtube.com/watch?v=7qPLnHLaBRQ','youtube_id'=>'7qPLnHLaBRQ','lang'=>'CZ','duration'=>'cca 20–30 min','level'=>'Středně pokročilý','description'=>'České vysvětlení barevných prostorů a proč se stejné barvy mohou na obrazovce a ve výstupu chovat jinak.'],
    'composition' => ['type'=>'video','title'=>'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin','url'=>'https://www.youtube.com/watch?v=xq1KMC1I6DY','youtube_id'=>'xq1KMC1I6DY','lang'=>'CZ','duration'=>'cca 10–20 min','level'=>'Začátečník','description'=>'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.'],

    // Sítě / OS — české přednášky a praktické tutorialy.
    'ip' => ['type'=>'video','title'=>'Jak fungují IP adresy?','url'=>'https://www.youtube.com/watch?v=J1nudfAQCDE','youtube_id'=>'J1nudfAQCDE','lang'=>'CZ','duration'=>'35 min','level'=>'Začátečník','description'=>'České vysvětlení IP adres, veřejných a privátních adres, DNS, NAT a portů v jednom souvislém modelu.'],
    'troubleshoot' => ['type'=>'video','title'=>'Nefunguje internet: jak najít příčinu výpadku připojení','url'=>'https://www.youtube.com/watch?v=gGwsOpNOmr0','youtube_id'=>'gGwsOpNOmr0','lang'=>'CZ','duration'=>'50 min','level'=>'Začátečník → středně pokročilý','description'=>'Praktická česká přednáška Petra Krčmáře o systematické diagnostice domácí a malé sítě krok za krokem.'],
    'wireshark' => ['type'=>'video','title'=>'Wireshark a modely TCP/IP a ISO/OSI','url'=>'https://www.youtube.com/watch?v=VRhbOaw8sXA','youtube_id'=>'VRhbOaw8sXA','lang'=>'CZ','duration'=>'cca 39 min','level'=>'Středně pokročilý','description'=>'Česká praktická ukázka Wiresharku, packet capture a návaznosti síťových vrstev TCP/IP a ISO/OSI.'],
    'ssh' => ['type'=>'video','title'=>'SSH nejen pro vzdálenou správu Linuxu','url'=>'https://www.youtube.com/watch?v=mWB2ralMAG0','youtube_id'=>'mWB2ralMAG0','lang'=>'CZ','duration'=>'50 min','level'=>'Středně pokročilý','description'=>'Česká přednáška o SSH, vzdáleném terminálu, přenosu souborů, tunelování a praktickém použití.'],
    'tls' => ['type'=>'video','title'=>'Zámečky nikoho nezajímaj’ – HTTPS a certifikáty','url'=>'https://www.youtube.com/watch?v=8_qX6ZThwZI','youtube_id'=>'8_qX6ZThwZI','lang'=>'CZ','duration'=>'20 min','level'=>'Středně pokročilý','description'=>'Krátká česká přednáška o HTTPS, certifikátech a tom, co skutečně znamená zabezpečené spojení v prohlížeči.'],
    'firewall' => ['type'=>'video','title'=>'Moderní linuxový firewall s nftables','url'=>'https://www.youtube.com/watch?v=7h5PMWbmXIo','youtube_id'=>'7h5PMWbmXIo','lang'=>'CZ','duration'=>'49 min','level'=>'Pokročilý','description'=>'Česká přednáška o stavovém firewallu, filtrování provozu a moderním nftables — vhodná pro pravidla a service matrix.'],
    'advanced-net' => ['type'=>'video','title'=>'Pokročilejší síťování v Linuxu','url'=>'https://www.youtube.com/watch?v=KI4ojd68IMA','youtube_id'=>'KI4ojd68IMA','lang'=>'CZ','duration'=>'50 min','level'=>'Pokročilý','description'=>'Český výklad pokročilejšího linuxového síťování vhodný pro routing, rozhraní a provozní diagnostiku.'],
    'monitoring' => ['type'=>'video','title'=>'Automatizace monitoringu serverů snadno a rychle','url'=>'https://www.youtube.com/watch?v=sZajyA4vUOA','youtube_id'=>'sZajyA4vUOA','lang'=>'CZ','duration'=>'20 min','level'=>'Středně pokročilý','description'=>'Česká praktická přednáška o tom, co a jak sledovat, aby monitoring opravdu zachytil problém služby.'],
    'logs' => ['type'=>'video','title'=>'Centralized logs ElasticSearch way','url'=>'https://www.youtube.com/watch?v=m6zpzczf2p8','youtube_id'=>'m6zpzczf2p8','lang'=>'CZ','duration'=>'50 min','level'=>'Pokročilý','description'=>'Česká přednáška o centralizaci, parsování a vyhledávání logů z aplikací a infrastruktury.'],
    'nginx' => ['type'=>'video','title'=>'Nginx v roli web serveru','url'=>'https://www.youtube.com/watch?v=MRpKBh7J0eo','youtube_id'=>'MRpKBh7J0eo','lang'=>'CZ','duration'=>'cca 50 min','level'=>'Středně pokročilý','description'=>'Česká přednáška o Nginxu, webserveru a reverse proxy — vhodná pro binding, upstream a provozní kontext.'],
    'cache' => ['type'=>'video','title'=>'Kešování webu pro vyšší výkon','url'=>'https://www.youtube.com/watch?v=nsD81Y1Ed34','youtube_id'=>'nsD81Y1Ed34','lang'=>'CZ','duration'=>'50 min','level'=>'Pokročilý','description'=>'Česká přednáška o webovém provozu, cachování a vrstvě mezi klientem a aplikací; doplňuje reverse proxy a výkonové scénáře.'],
    'backup' => ['type'=>'video','title'=>'Proxmox Backup Server: obnova jednoho souboru','url'=>'https://www.youtube.com/watch?v=54Sc77n4pDQ','youtube_id'=>'54Sc77n4pDQ','lang'=>'CZ','duration'=>'krátké · cca 5–10 min','level'=>'Středně pokročilý','description'=>'Česká praktická ukázka skutečné obnovy souboru ze zálohy — důraz na restore, ne jen vytvoření backupu.'],
    'observability' => ['type'=>'video','title'=>'Minority Reports – chyby z prohlížeče jako provozní evidence','url'=>'https://www.youtube.com/watch?v=aB7i_jLWGFc','youtube_id'=>'aB7i_jLWGFc','lang'=>'CZ','duration'=>'50 min','level'=>'Pokročilý','description'=>'Česká přednáška Michala Špačka o sběru signálů z prohlížečů, chybách a provozních datech použitelných při incidentech.'],
    'security-monitoring' => ['type'=>'video','title'=>'Chyť mě, když to dokážeš! Bezpečnostní monitoring s FOSS','url'=>'https://www.youtube.com/watch?v=ATPBw6Dt6Ig','youtube_id'=>'ATPBw6Dt6Ig','lang'=>'CZ','duration'=>'50 min','level'=>'Pokročilý','description'=>'Česká přednáška o bezpečnostním monitoringu a skládání více signálů do použitelné provozní evidence.'],
    'modern-logs' => ['type'=>'video','title'=>'Moderní logování: Vector a ECS','url'=>'https://www.youtube.com/watch?v=lujVIbXpqLo','youtube_id'=>'lujVIbXpqLo','lang'=>'CZ','duration'=>'50 min','level'=>'Pokročilý','description'=>'Česká přednáška o moderním sběru a struktuře logů — vhodná pro korelaci, observability a incident response.'],
    'ip-allocation' => ['type'=>'video','title'=>'Odkud se berou IP adresy?','url'=>'https://www.youtube.com/watch?v=JYUpyOTnIYM','youtube_id'=>'JYUpyOTnIYM','lang'=>'CZ','duration'=>'50 min','level'=>'Začátečník → středně pokročilý','description'=>'Česká přednáška Ondřeje Caletky o adresním prostoru a přidělování IP adres; rozšiřuje pohled za lokální síť.'],
    'ipv6' => ['type'=>'video','title'=>'Seminář IPv6: deset let poté','url'=>'https://www.youtube.com/watch?v=JJvqBH02GiA','youtube_id'=>'JJvqBH02GiA','lang'=>'CZ','duration'=>'delší seminář','level'=>'Středně pokročilý','description'=>'Český odborný seminář k IPv6; pro tuto lekci stačí vybrané části věnované adresaci a provozu IPv6.'],
];

$graphicsMap = [
    'hierarchy'=>'canva','composition'=>'composition','contrast-color'=>'color','typography'=>'figma-short','raster-vector'=>'vector','color'=>'color','export'=>'canva','assets'=>'canva',
    'branding-system'=>'figma-deep','photo-treatment'=>'composition','responsive-adaptation'=>'figma-short','portfolio-presentation'=>'canva','ui-spacing-system'=>'figma-deep','component-consistency'=>'figma-deep','microinteraction-storyboard'=>'figma-deep','portfolio-case-study'=>'figma-deep','accessibility-design'=>'figma-short','responsive-type-ii'=>'figma-deep','design-tokens-ii'=>'figma-deep','interaction-patterns-ii'=>'figma-deep','design-critique-ii'=>'figma-deep',
];
$graphics1Extra = [
    'spacing'=>'figma-short','image-crop'=>'composition','cta'=>'canva','preflight'=>'canva','color-harmony'=>'color','iconography'=>'vector','image-composition'=>'composition','template-critique'=>'canva','responsive-series'=>'figma-short','editorial-typography-i'=>'figma-short','layout-rhythm-i'=>'canva','visual-story-i'=>'composition','production-preflight-i'=>'canva',
];
$network3Map = [
    'ip-addressing'=>'ip','dns'=>'ip','dhcp'=>'troubleshoot','ports'=>'ip','ssh-sftp'=>'ssh','https'=>'tls','icmp'=>'troubleshoot','routing'=>'troubleshoot','troubleshooting'=>'troubleshoot',
    'vlan-basics'=>'advanced-net','arp'=>'wireshark','nat'=>'ip','service-matrix'=>'firewall','ipv6-basics'=>'ipv6','dns-record-types'=>'ip','monitoring-basics'=>'monitoring','dhcp-reservations'=>'troubleshoot','packet-analysis'=>'wireshark','subnetting-vlsm'=>'ip','network-security-basics'=>'firewall','dns-dhcp-operations'=>'troubleshoot','service-debug-chain'=>'troubleshoot',
];
$network4Map = [
    'cidr'=>'ip-allocation','routing-advanced'=>'advanced-net','dns-advanced'=>'ip','binding'=>'nginx','tcp'=>'wireshark','ssh-keys'=>'ssh','permissions'=>'ssh','diagnostics'=>'troubleshoot',
    'reverse-proxy'=>'nginx','tls-certificates'=>'tls','logs-monitoring'=>'logs','change-management'=>'observability','http-observability'=>'observability','load-balancing'=>'nginx','canary-release'=>'observability','backup-restore'=>'backup','slo-postmortem'=>'observability','infrastructure-as-code'=>'observability','config-drift'=>'modern-logs','performance-engineering'=>'monitoring','capacity-planning'=>'monitoring',
];

$makeClass = static function(array $map, array $defaults = []) use ($videos): array {
    $result = ['_default'=>$defaults];
    foreach ($map as $topic=>$videoKey) {
        $result[$topic] = [$videos[$videoKey]];
    }
    return $result;
};

$class1 = $makeClass(array_merge($graphicsMap, $graphics1Extra), [
    $videos['canva'],
    ['type'=>'course','title'=>'Canva · Graphic Design Essentials','meta'=>'Volitelný kurz · principy designu','url'=>$canvaCourse],
]);
$class2 = $makeClass($graphicsMap, [
    $videos['canva'],
    ['type'=>'course','title'=>'Canva Design School · Courses','meta'=>'Volitelné kurzy · layout, typografie a brand systémy','url'=>$canvaCourses],
]);
$class3 = $makeClass($network3Map, [
    $videos['ip'],
    ['type'=>'course','title'=>'Cisco Skills for All · Networking','meta'=>'Volitelná doplňková praxe','url'=>$cisco],
]);
$class4 = $makeClass($network4Map, [
    $videos['tls'],
    ['type'=>'reference','title'=>'MDN · HTTP status codes','meta'=>'Volitelná reference pro HTTP diagnostiku','url'=>$mdnHttp],
]);

$yearpackResourceMap = json_decode((string)file_get_contents(__DIR__ . '/yearpack_resource_map.json'), true);
foreach ((array)($yearpackResourceMap['class_1a'] ?? []) as $topic => $videoKey) if (isset($videos[$videoKey])) $class1[$topic] = [$videos[$videoKey]];
foreach ((array)($yearpackResourceMap['class_2a'] ?? []) as $topic => $videoKey) if (isset($videos[$videoKey])) $class2[$topic] = [$videos[$videoKey]];
foreach ((array)($yearpackResourceMap['class_3a'] ?? []) as $topic => $videoKey) if (isset($videos[$videoKey])) $class3[$topic] = [$videos[$videoKey]];
foreach ((array)($yearpackResourceMap['class_4a'] ?? []) as $topic => $videoKey) if (isset($videos[$videoKey])) $class4[$topic] = [$videos[$videoKey]];

// Ke konkrétním tématům ponecháváme i pár kvalitních referencí. Video je vždy první a povinná lekce na něm nikdy nezávisí.
$class3['dns'][] = ['type'=>'reference','title'=>'Cloudflare · What is DNS?','meta'=>'Vizuální doplnění DNS lookupu','url'=>$cfDns];
$class3['dns-record-types'][] = ['type'=>'reference','title'=>'Cloudflare · What is DNS?','meta'=>'Typy DNS záznamů a lookup','url'=>$cfDns];
$class4['tls-certificates'][] = ['type'=>'reference','title'=>'Cloudflare · TLS handshake','meta'=>'Vizuální doplnění TLS handshaku','url'=>$cfTls];
$class4['http-observability'][] = ['type'=>'reference','title'=>'MDN · HTTP status codes','meta'=>'Rychlá diagnostická reference','url'=>$mdnHttp];


// v30 adaptive-learning topics: each gets a Czech video as an optional alternate explanation.
$class1['content-first-layout'] = [$videos['figma-short']];
$class1['microcopy-cta'] = [$videos['canva']];
$class1['responsive-art-direction'] = [$videos['figma-short']];
$class1['design-feedback'] = [$videos['canva']];
$class2['card-sorting'] = [$videos['figma-deep']];
$class2['error-recovery-ux'] = [$videos['figma-deep']];
$class2['interaction-accessibility'] = [$videos['figma-short']];
$class2['design-handoff-qa'] = [$videos['figma-deep']];
$class3['linux-file-troubleshooting'] = [$videos['troubleshoot']];
$class3['ssh-key-operations'] = [$videos['ssh']];
$class3['stateful-firewall'] = [$videos['firewall']];
$class3['bash-error-handling'] = [$videos['advanced-net']];
$class4['golden-signals'] = [$videos['monitoring']];
$class4['container-networking'] = [$videos['advanced-net']];
$class4['rollback-strategy'] = [$videos['observability']];
$class4['incident-command'] = [$videos['security-monitoring']];

return [
    'class_1a'=>$class1,
    'class_2a'=>$class2,
    'class_3a'=>$class3,
    'class_4a'=>$class4,
];
