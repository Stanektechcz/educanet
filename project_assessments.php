<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$rubrics = [
    'graphics_core' => [
        ['id'=>'brief','title'=>'Naplnění zadání','max'=>5,'description'=>'Výstup řeší brief, cílovou skupinu a požadovaný formát.'],
        ['id'=>'hierarchy','title'=>'Hierarchie a kompozice','max'=>5,'description'=>'Obsah má jasnou prioritu, rytmus, grid a čitelnou cestu oka.'],
        ['id'=>'craft','title'=>'Řemeslné zpracování','max'=>5,'description'=>'Typografie, barvy, obraz, zarovnání a export jsou technicky čisté.'],
        ['id'=>'reasoning','title'=>'Obhajoba rozhodnutí','max'=>5,'description'=>'Student umí stručně vysvětlit klíčová rozhodnutí a reagovat na zpětnou vazbu.'],
    ],
    'graphics_web' => [
        ['id'=>'ux','title'=>'Struktura a UX','max'=>5,'description'=>'Navigace, informační architektura a uživatelská cesta jsou srozumitelné.'],
        ['id'=>'visual','title'=>'Vizuální systém','max'=>5,'description'=>'Typografie, barvy, spacing a komponenty tvoří konzistentní systém.'],
        ['id'=>'responsive','title'=>'Responsive a přístupnost','max'=>5,'description'=>'Návrh funguje ve více šířkách a respektuje kontrast, focus a čitelnost.'],
        ['id'=>'prototype','title'=>'Prototyp / realizace','max'=>5,'description'=>'Interakce nebo implementace odpovídá návrhu a jde smysluplně otestovat.'],
        ['id'=>'presentation','title'=>'Case study a obhajoba','max'=>5,'description'=>'Student ukáže problém, proces, rozhodnutí, výsledek a reflexi.'],
    ],
    'network_core' => [
        ['id'=>'design','title'=>'Návrh řešení','max'=>5,'description'=>'Topologie, adresace a služby odpovídají zadání a jsou obhajitelné.'],
        ['id'=>'evidence','title'=>'Důkazy a diagnostika','max'=>5,'description'=>'Student používá měření, logy a testy místo náhodných změn.'],
        ['id'=>'security','title'=>'Bezpečnost a rozsah změny','max'=>5,'description'=>'Řešení používá princip minimálních oprávnění a omezuje dopady změn.'],
        ['id'=>'validation','title'=>'Validace a dokumentace','max'=>5,'description'=>'Je zřejmé, jak bylo řešení ověřeno a jak jej lze zopakovat.'],
    ],
    'production_core' => [
        ['id'=>'plan','title'=>'Plán a rizika','max'=>5,'description'=>'Scope, success criteria, stop condition a rollback jsou definované před změnou.'],
        ['id'=>'evidence','title'=>'Observability a evidence','max'=>5,'description'=>'Rozhodnutí vychází z logů, metrik, health checků a uživatelského dopadu.'],
        ['id'=>'execution','title'=>'Provedení změny','max'=>5,'description'=>'Změna je kontrolovaná, reprodukovatelná a technicky správná.'],
        ['id'=>'recovery','title'=>'Rollback / obnova','max'=>5,'description'=>'Student umí bezpečně stabilizovat službu a ověřit návrat do známého stavu.'],
        ['id'=>'communication','title'=>'Postmortem a komunikace','max'=>5,'description'=>'Výstup jasně popisuje dopad, časovou osu, příčinu a preventivní kroky.'],
    ],
];

$project = static function(string $id, string $classId, string $title, string $type, string $rubricKey, string $summary, array $deliverables, int $weight = 1) use ($rubrics): array {
    return [
        'id'=>$id,
        'class_id'=>$classId,
        'title'=>$title,
        'type'=>$type,
        'summary'=>$summary,
        'deliverables'=>$deliverables,
        'rubric'=>$rubrics[$rubricKey],
        'weight'=>$weight,
    ];
};

return [
    'class_1a' => [
        $project('1a_poster_system','class_1a','Plakát bez šablony','individual','graphics_core','Individuální plakát s vědomou hierarchií, typografií, obrazem a CTA.',['finální export','krátká obhajoba','zdrojový návrh']),
        $project('1a_identity','class_1a','Mini vizuální identita','individual','graphics_core','Malý konzistentní systém: paleta, typografie, jednoduchá značka/ikona a dvě aplikace.',['brand sheet','2 aplikace','reflexe']),
        $project('1a_landing_sprint','class_1a','Landing Page Sprint','group','graphics_web','Tým vytvoří strukturu, wireframe a vizuální návrh jednoduché landing page.',['sitemap/wireframe','desktop + mobile návrh','týmová prezentace']),
        $project('1a_portfolio_team_review','class_1a','Portfolio peer-review studio','group','graphics_web','Tým provede strukturovanou kritiku portfolií a navrhne konkrétní iterace.',['review board','seznam změn','before/after']),
    ],
    'class_2a' => [
        $project('2a_campaign','class_2a','Mini kampaň ve třech formátech','individual','graphics_core','Master vizuál rozšířený do feedu, square a story bez ztráty hierarchie.',['3 finální formáty','systém pravidel','obhajoba adaptací']),
        $project('2a_portfolio_web','class_2a','Portfolio web: návrh → prototyp','individual','graphics_web','Kompletní portfolio case study s responzivním návrhem a prototypem nebo webovou realizací.',['IA + wireframe','design system','desktop/mobile','prototyp/realizace','case study']),
        $project('2a_design_system_sprint','class_2a','Design System Sprint','group','graphics_web','Tým vytvoří mini UI systém se spacing tokeny, komponentami, stavy a dokumentací.',['tokens','komponenty + stavy','dokumentační stránka','demo']),
        $project('2a_usability_team','class_2a','Usability test + redesign','group','graphics_web','Tým otestuje prototyp, sesbírá evidence a provede prioritizovaný redesign.',['scénář testu','pozorování','prioritizace problémů','redesign']),
    ],
    'class_3a' => [
        $project('3a_office_network','class_3a','Návrh malé kancelářské sítě','individual','network_core','Adresace, VLANy, služby a servisní politika s validačním plánem.',['topologie','adresní plán','service matrix','test plan']),
        $project('3a_monitoring','class_3a','Monitoring a hardening služby','individual','network_core','Návrh monitoringu a bezpečného minima pro vybranou síťovou službu.',['health checks','alerty','firewall policy','SSH minimum','dokumentace']),
        $project('3a_incident_team','class_3a','Incident response tým','group','network_core','Skupina řeší propojený incident pomocí rolí, důkazů a sdíleného incident logu.',['incident timeline','evidence log','oprava','retrospektiva']),
        $project('3a_network_rollout','class_3a','Síťový rollout','group','network_core','Tým navrhne rozšíření sítě o novou zónu/VLAN a bezpečně jej odvaliduje.',['change plan','topologie','policy','rollback','validation']),
    ],
    'class_4a' => [
        $project('4a_change_window','class_4a','Produkční change window','individual','production_core','Řízená změna reverse proxy/TLS/backendu včetně prechecku, validace a rollbacku.',['change ticket','evidence','výsledek změny','rollback plán','post-change note']),
        $project('4a_observability','class_4a','Observability case study','individual','production_core','Student navrhne SLI/SLO, log/metric evidence a incident dashboard pro službu.',['SLI/SLO','dashboard návrh','alert policy','incident interpretation']),
        $project('4a_release_team','class_4a','Canary Release Drill','group','production_core','Tým provede canary release se stop conditions a rozhodnutím continue/rollback.',['release plan','canary evidence','decision log','rollback/continue','postmortem']),
        $project('4a_restore_team','class_4a','Disaster recovery drill','group','production_core','Skupina obnoví službu ze známého bodu a prokáže integritu a funkčnost po obnově.',['restore runbook','časová osa','integrity checks','service validation','lessons learned']),
    ],
];
