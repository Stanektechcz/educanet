<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · žákovské názvy kompetencí 2.A (grafika_web_ii) a 4.A (provoz_site) – msgid shodné s comp75_draft_catalog()
 * (hlídá tools/v75_competency_audit.php), překlad v lang/{en,uk}/ui/competency.php. Odděleně od comp62_label_msgids(),
 * aby audit v62 dál porovnával jen aktivní katalog pilotu.
 * @return array<string,string> id kompetence → msgid
 */
function comp75_label_msgids(): array
{
    return [
        'w2_design_system' => trm('Umím sestavit design systém z tokenů, mezer a komponent'),
        'w2_css_responsive' => trm('Umím převést návrh do responzivního rozvržení v CSS'),
        'w2_a11y_audit' => trm('Umím provést audit přístupnosti návrhu a navrhnout opravy'),
        'w2_forms_states' => trm('Umím navrhnout formulář se stavy a srozumitelnými chybami'),
        'w2_ux_research' => trm('Umím ověřit použitelnost testem a uspořádat obsah'),
        'g2_typography' => trm('Umím navrhnout responzivní typografickou škálu'),
        'g2_visual_system' => trm('Umím vytvořit vizuální kampaň s jasnou hierarchií a kontrastem'),
        'g2_handoff' => trm('Umím předat návrh k realizaci a obhájit ho v případové studii'),
        'ops_routing' => trm('Umím navrhnout adresní plán a směrování sítě'),
        'ops_proxy_tls' => trm('Umím nastavit reverzní proxy a ověřit certifikát TLS'),
        'ops_network_diag' => trm('Umím diagnostikovat síť podle paketů a stavu spojení'),
        'ops_hardening' => trm('Umím zabezpečit službu: SSH, firewall, práva a aktualizace'),
        'ops_observability' => trm('Umím sledovat službu pomocí logů, metrik a cílů SLO'),
        'ops_change' => trm('Umím provést řízenou změnu s plánem návratu'),
        'ops_backup' => trm('Umím navrhnout zálohu podle RPO a RTO a ověřit obnovu'),
        'ops_iac' => trm('Umím popsat infrastrukturu jako kód a najít odchylku konfigurace'),
        'ops_services' => trm('Umím spravovat služby a kontejnery včetně závislostí a restartu'),
        'ops_performance' => trm('Umím změřit výkon a naplánovat kapacitu'),
        'ops_incident' => trm('Umím vést incident podle runbooku a sepsat rozbor bez obviňování'),
    ];
}
