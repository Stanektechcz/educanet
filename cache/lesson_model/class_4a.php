<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 2,
  'class' => 'class_4a',
  'lessons' => 
  array (
    1 => 
    array (
      'number' => 1,
      'lm71_source' => 'primary',
    ),
    2 => 
    array (
      'id' => 'production_change_window',
      'title' => 'Lekce 2 · Produkční change window: proxy, TLS, logy a rollback',
      'subtitle' => 'Dalších 2 × 45 minut',
      'goal' => 'Navázat na produkční incident a naučit se řízenou změnu: precheck, reverse proxy upstream, TLS certifikát, korelace logů, validace a rollback.',
      'unlock_note' => 'Odemkne se po dokončení prvního praktického incidentního labu.',
      'knowledge' => 
      array (
        0 => 'reverse-proxy',
        1 => 'tls-certificates',
        2 => 'logs-monitoring',
        3 => 'change-management',
        4 => 'diagnostics',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Change plan + baseline',
          'text' => 'Definuj success criteria, precheck a rollback dřív než změnu.',
        ),
        1 => 
        array (
          'time' => '12–27',
          'title' => 'Reverse proxy',
          'text' => 'Ověř Nginx → upstream a čti 502 jako důkaz vrstvy.',
        ),
        2 => 
        array (
          'time' => '27–42',
          'title' => 'TLS renewal',
          'text' => 'Ověř SNI, SAN, chain a skutečně prezentovaný certifikát.',
        ),
        3 => 
        array (
          'time' => '42–58',
          'title' => 'Log correlation',
          'text' => 'Propoj monitoring, access/error log a app log podle času.',
        ),
        4 => 
        array (
          'time' => '58–76',
          'title' => 'Řízený deploy',
          'text' => 'Proveď change a validuj user path + metriky.',
        ),
        5 => 
        array (
          'time' => '76–86',
          'title' => 'Failure injection',
          'text' => 'Nový upstream port je špatně — rozhodni rollback vs. oprava.',
        ),
        6 => 
        array (
          'time' => '86–90',
          'title' => 'Post-change note',
          'text' => '4 věty: změna, důkaz, validace, rollback stav.',
        ),
      ),
      'topology' => 
      array (
        'title' => 'Production path',
        'lines' => 
        array (
          0 => 'Client → DNS → LB/Nginx :443',
          1 => '                  │ TLS termination',
          2 => '                  └→ app-v2 127.0.0.1:9000',
          3 => '                         │',
          4 => '                       database',
          5 => 'Monitoring → /health + /metrics',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'change_plan',
          'time' => '12 min',
          'title' => '01 · Change plan před změnou',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'change-management',
          ),
          'intro' => 'Změna: přesun proxy upstreamu z app-v1:8000 na app-v2:9000 + nový TLS certifikát.',
          'tasks' => 
          array (
            0 => 'Dokonči Change Management Knowledge Tour.',
            1 => 'Napiš 3 prechecky.',
            2 => 'Napiš success criteria.',
            3 => 'Napiš jednoznačný rollback krok.',
          ),
        ),
        1 => 
        array (
          'id' => 'proxy',
          'time' => '15 min',
          'title' => '02 · Reverse proxy a upstream',
          'kind' => 'quiz',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
          ),
          'demo' => 
          array (
            'label' => 'Evidence',
            'lines' => 
            array (
              0 => 'curl https://app.example.cz → HTTP/2 502',
              1 => 'nginx error.log → connect() failed (111) while connecting to upstream',
              2 => 'curl http://127.0.0.1:9000/health → connection refused',
            ),
          ),
          'question' => 'Kde je teď nejsilnější hypotéza?',
          'options' => 
          array (
            0 => 'DNS resolver klienta.',
            1 => 'Upstream aplikace / port mezi Nginx a app-v2.',
            2 => 'TLS certifikát klienta.',
          ),
          'correct' => 1,
          'explanation' => 'Klient dostal HTTP odpověď od Nginx. Proxy ale nedokáže spojit upstream a lokální health test to potvrzuje.',
        ),
        2 => 
        array (
          'id' => 'tls',
          'time' => '15 min',
          'title' => '03 · TLS renewal bez falešného pocitu bezpečí',
          'kind' => 'quiz',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'tls-certificates',
          ),
          'demo' => 
          array (
            'label' => 'openssl s_client',
            'lines' => 
            array (
              0 => 'subject=CN=app.example.cz',
              1 => 'notAfter=Sep 10 12:00:00 2026 GMT',
              2 => 'Verify return code: 0 (ok)',
            ),
          ),
          'question' => 'Datum je 11. září 2026. Jaký je závěr?',
          'options' => 
          array (
            0 => 'Certifikát je podle notAfter expirovaný a je potřeba nasadit nový.',
            1 => 'Certifikát je určitě platný dalších 30 dní.',
            2 => 'Stačí restartovat DNS.',
          ),
          'correct' => 0,
          'explanation' => 'notAfter je v minulosti. Po nasazení nového certifikátu je nutné ověřit, co služba skutečně prezentuje.',
          'tasks' => 
          array (
            0 => 'Po renew/reload znovu spusť externí TLS test.',
            1 => 'Zkontroluj SAN pro app.example.cz.',
          ),
        ),
        3 => 
        array (
          'id' => 'logs',
          'time' => '16 min',
          'title' => '04 · Korelace monitoringu a logů',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'logs-monitoring',
          ),
          'intro' => 'Ve 14:05 po deployi vyskočí 502. Nečti celý log — začni časem a requestem.',
          'tasks' => 
          array (
            0 => 'Dokonči Logs & Monitoring Knowledge Tour.',
            1 => 'Najdi 14:05 v access logu.',
            2 => 'Najdi odpovídající error log.',
            3 => 'Porovnej app log ve stejném čase.',
            4 => 'Sepiš jednu hypotézu, kterou data vylučují.',
          ),
        ),
        4 => 
        array (
          'id' => 'deploy',
          'time' => '18 min',
          'title' => '05 · Deploy + validační matice',
          'kind' => 'manual',
          'xp' => 35,
          'knowledge' => 
          array (
            0 => 'diagnostics',
          ),
          'intro' => 'Upstream už odpovídá. Proveď change jako řízený postup.',
          'tasks' => 
          array (
            0 => 'nginx -t / syntax check',
            1 => 'reload bez zbytečného restartu',
            2 => 'curl externí URL a očekávej 200',
            3 => 'ověř TLS certifikát',
            4 => 'ověř /health a monitoring',
            5 => 'zkontroluj error rate 5–10 minut',
          ),
          'done_label' => 'Všech 6 validačních bodů je hotových',
        ),
        5 => 
        array (
          'id' => 'failure',
          'time' => '14 min',
          'title' => '06 · Failure injection: špatný port po deployi',
          'kind' => 'quiz',
          'xp' => 35,
          'intro' => 'Po změně se error rate zvedne na 35 %. Nginx config ukazuje proxy_pass http://127.0.0.1:9001, app poslouchá na 9000. Rollback je připravený a trvá 20 sekund.',
          'question' => 'Jaký je nejbezpečnější postup v produkci?',
          'options' => 
          array (
            0 => 'Nechat chybu běžet a dlouze zkoumat.',
            1 => 'Okamžitě obnovit známý funkční stav rollbackem, stabilizovat službu a teprve potom analyzovat/opravit change.',
            2 => 'Vypnout monitoring.',
          ),
          'correct' => 1,
          'explanation' => 'Při výrazném dopadu a připraveném rychlém rollbacku je prioritou obnova služby. Analýza může pokračovat po stabilizaci.',
          'tasks' => 
          array (
            0 => 'Po rollbacku ověř user path.',
            1 => 'Ověř návrat error rate k baseline.',
            2 => 'Zapiš chybný parametr do post-change note.',
          ),
        ),
      ),
      'finisher' => 
      array (
        'title' => 'Rychlík · Canary 10 %',
        'duration' => '15–25 min',
        'text' => 'Navrhni, jak bys stejnou změnu nasadil nejdřív pro 10 % provozu a podle jakých metrik bys rozhodl pokračovat/rollback.',
        'deliverables' => 
        array (
          0 => '3 success metrics',
          1 => '2 stop conditions',
          2 => 'krátký validační plán',
        ),
      ),
      'number' => 2,
      'lm71_source' => 'next',
    ),
    3 => 
    array (
      'id' => 'prod_observability',
      'number' => 3,
      'title' => 'Lekce 3 · Observability: logy, metriky, health',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Získat jistotu v tom, jak z monitoringu a logů vytvořit důkaz místo intuice.',
      'knowledge' => 
      array (
        0 => 'logs-monitoring',
        1 => 'diagnostics',
        2 => 'reverse-proxy',
        3 => 'tls-certificates',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'SLI/SLO mindset',
        ),
        1 => 
        array (
          'time' => '12–30',
          'title' => 'Access/error log',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Health + metrics',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Proxy evidence',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Incident correlation',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Postmortem mini',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'signals',
          'time' => '12 min',
          'title' => '01 · Tři signály',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'logs-monitoring',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vyber latency, error rate a availability.',
            1 => 'Definuj baseline.',
          ),
        ),
        1 => 
        array (
          'id' => 'logs',
          'time' => '18 min',
          'title' => '02 · Korelace logů',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Najdi request v access logu.',
            1 => 'Najdi stejný čas v error logu.',
            2 => 'Najdi odpovídající app log.',
          ),
        ),
        2 => 
        array (
          'id' => 'health',
          'time' => '15 min',
          'title' => '03 · Health + metrics',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni /health.',
            1 => 'Navrhni 3 metriky.',
            2 => 'Nezveřejňuj /metrics všem.',
          ),
        ),
        3 => 
        array (
          'id' => 'proxy',
          'time' => '17 min',
          'title' => '04 · Proxy evidence',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Rozliš 502 od 404.',
            1 => 'Ověř upstream lokálně.',
          ),
        ),
        4 => 
        array (
          'id' => 'incident',
          'time' => '18 min',
          'title' => '05 · Incident correlation',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Nginx vrací 502 a app health na upstream portu neodpovídá. Co je nejsilnější hypotéza?',
          'options' => 
          array (
            0 => 'Upstream aplikace/port mezi proxy a backendem.',
            1 => 'DNS klienta, protože HTTP odpověď už přišla.',
            2 => 'Barva terminálu.',
          ),
          'correct' => 0,
          'explanation' => '502 a nefunkční upstream health ukazují mezi proxy a backend.',
        ),
        5 => 
        array (
          'id' => 'postmortem',
          'time' => '10 min',
          'title' => '06 · Mini postmortem',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Co se stalo.',
            1 => 'Jaký byl důkaz.',
            2 => 'Jak tomu příště předejít.',
          ),
        ),
      ),
      'lm71_source' => 'extended',
    ),
    4 => 
    array (
      'id' => 'prod_release_drill',
      'number' => 4,
      'title' => 'Lekce 4 · Release drill: bezpečný deploy a rollback',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Projít release jako řízenou změnu: precheck, canary, validace, stop condition a rollback.',
      'knowledge' => 
      array (
        0 => 'change-management',
        1 => 'reverse-proxy',
        2 => 'tls-certificates',
        3 => 'logs-monitoring',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Change ticket',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Precheck',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Canary',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Validace',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Failure injection',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Rozhodnutí',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'ticket',
          'time' => '12 min',
          'title' => '01 · Change ticket',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'change-management',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Definuj scope.',
            1 => 'Success criteria.',
            2 => 'Rollback krok.',
          ),
        ),
        1 => 
        array (
          'id' => 'precheck',
          'time' => '16 min',
          'title' => '02 · Precheck',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Config syntax.',
            1 => 'Disk/CPU baseline.',
            2 => 'Health aktuální verze.',
            3 => 'Backup/rollback artefakt.',
          ),
        ),
        2 => 
        array (
          'id' => 'canary',
          'time' => '17 min',
          'title' => '03 · Canary 10 %',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Navrhni 10% routing.',
            1 => 'Definuj 3 metriky.',
            2 => 'Definuj 2 stop conditions.',
          ),
        ),
        3 => 
        array (
          'id' => 'validate',
          'time' => '17 min',
          'title' => '04 · Validace user path',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
            1 => 'tls-certificates',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'DNS.',
            1 => 'TLS.',
            2 => 'HTTP status.',
            3 => 'User flow.',
            4 => 'Monitoring.',
          ),
        ),
        4 => 
        array (
          'id' => 'failure',
          'time' => '18 min',
          'title' => '05 · Failure injection',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Po canary error rate stoupne z 0,5 % na 12 % a stop condition je 3 %. Co uděláš?',
          'options' => 
          array (
            0 => 'Zastavím rollout a vrátím canary do známého funkčního stavu.',
            1 => 'Pokračuji na 100 %, protože už jsme začali.',
            2 => 'Vypnu alert.',
          ),
          'correct' => 0,
          'explanation' => 'Stop condition existuje právě proto, aby se rozhodnutí nedělalo pod tlakem intuitivně.',
        ),
        5 => 
        array (
          'id' => 'decision',
          'time' => '10 min',
          'title' => '06 · Release note',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Rozhodnutí continue/rollback.',
            1 => 'Důkaz.',
            2 => 'Co změnit před dalším pokusem.',
          ),
        ),
      ),
      'lm71_source' => 'extended',
    ),
    5 => 
    array (
      'id' => 'ops_http_lb',
      'number' => 5,
      'title' => 'Lekce 5 · HTTP observability + load balancing',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Číst HTTP odpověď jako diagnostický důkaz a pochopit základní chování load balanceru při zdravém i degradovaném backendu.',
      'knowledge' => 
      array (
        0 => 'http-observability',
        1 => 'load-balancing',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'HTTP evidence',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Headers',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Load balancer',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Health checks',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Degradace',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Postmortem',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'http',
          'time' => '15 min',
          'title' => '01 · Status není jen číslo',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'http-observability',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Rozliš 2xx/3xx/4xx/5xx.',
            1 => 'Přečti Server, Location a Retry-After.',
            2 => 'Urči, co odpověď dokazuje.',
          ),
        ),
        1 => 
        array (
          'id' => 'headers',
          'time' => '15 min',
          'title' => '02 · curl -I jako rychlá sonda',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Interpretuj status.',
            1 => 'Najdi redirect.',
            2 => 'Najdi cache/trace header.',
          ),
        ),
        2 => 
        array (
          'id' => 'lb',
          'time' => '15 min',
          'title' => '03 · Rozdělení provozu',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'load-balancing',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přepínej round-robin/weighted.',
            1 => 'Sleduj distribuci requestů.',
            2 => 'Vyřaď unhealthy backend.',
          ),
        ),
        3 => 
        array (
          'id' => 'health',
          'time' => '17 min',
          'title' => '04 · Health check',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni /health check.',
            1 => 'Urči interval a fail threshold.',
            2 => 'Nespoléhej jen na otevřený port.',
          ),
        ),
        4 => 
        array (
          'id' => 'degrade',
          'time' => '18 min',
          'title' => '05 · Jeden backend padá',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Load balancer stále posílá provoz na backend, který vrací 500. Co je potřeba zlepšit?',
          'options' => 
          array (
            0 => 'Health check a pravidla vyřazení unhealthy backendu.',
            1 => 'DNS TTL klienta.',
            2 => 'Velikost SSH klíče.',
          ),
          'correct' => 0,
          'explanation' => 'Load balancer musí mít signál, podle kterého nezdravý backend přestane dostávat běžný provoz.',
        ),
        5 => 
        array (
          'id' => 'post',
          'time' => '10 min',
          'title' => '06 · Mini postmortem',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Napiš symptom.',
            1 => 'Napiš důkaz.',
            2 => 'Napiš preventivní kontrolu.',
          ),
        ),
      ),
      'lm71_source' => 'plus',
    ),
    6 => 
    array (
      'id' => 'ops_canary_restore',
      'number' => 6,
      'title' => 'Lekce 6 · Canary release + backup/restore drill',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Provést řízené nasazení na malou část provozu a nacvičit rozhodnutí rollback vs. restore na základě měřitelných signálů.',
      'knowledge' => 
      array (
        0 => 'canary-release',
        1 => 'backup-restore',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Baseline',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Canary 10 %',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Metriky',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Decision gate',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Restore drill',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Runbook',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'baseline',
          'time' => '12 min',
          'title' => '01 · Než změníš produkci',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Zapiš baseline error rate.',
            1 => 'Zapiš latency p95.',
            2 => 'Ověř poslední použitelný backup.',
          ),
        ),
        1 => 
        array (
          'id' => 'canary',
          'time' => '16 min',
          'title' => '02 · Canary místo big-bang',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'canary-release',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Nastav 10 % provozu na v2.',
            1 => 'Porovnej v1 vs. v2.',
            2 => 'Definuj stop podmínku.',
          ),
        ),
        2 => 
        array (
          'id' => 'metrics',
          'time' => '17 min',
          'title' => '03 · Rozhodnutí podle dat',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sleduj error rate.',
            1 => 'Sleduj latency.',
            2 => 'Sleduj business/user check.',
          ),
        ),
        3 => 
        array (
          'id' => 'gate',
          'time' => '17 min',
          'title' => '04 · Promote nebo rollback',
          'kind' => 'quiz',
          'xp' => 35,
          'question' => 'Canary v2 má 4× vyšší error rate než baseline, latency je horší a trend trvá 5 minut. Co je nejbezpečnější další krok?',
          'options' => 
          array (
            0 => 'Zastavit rollout a rollbackovat canary podle předem připraveného plánu.',
            1 => 'Rozšířit v2 na 100 %, aby bylo více dat.',
            2 => 'Smazat monitoring alert.',
          ),
          'correct' => 0,
          'explanation' => 'Canary existuje právě proto, aby problém zastavil před plošným dopadem.',
        ),
        4 => 
        array (
          'id' => 'restore',
          'time' => '18 min',
          'title' => '05 · Restore drill',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'backup-restore',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Vyber správný backup.',
            1 => 'Ověř integritu.',
            2 => 'Obnov do testovacího cíle.',
            3 => 'Proveď funkční kontrolu.',
          ),
        ),
        5 => 
        array (
          'id' => 'runbook',
          'time' => '10 min',
          'title' => '06 · Runbook',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Sepiš 5 kroků rollout/rollback.',
            1 => 'Uveď ownera a success criteria.',
            2 => 'Uveď restore checkpoint.',
          ),
        ),
      ),
      'lm71_source' => 'plus',
    ),
    7 => 
    array (
      'id' => 'ops_slo_postmortem',
      'number' => 7,
      'title' => 'Lekce 7 · SLO, error budget a postmortem',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést provozní data na rozhodnutí: kdy pokračovat v release, kdy stabilizovat a jak z incidentu vytvořit konkrétní follow-up.',
      'knowledge' => 
      array (
        0 => 'slo-postmortem',
        1 => 'http-observability',
        2 => 'canary-release',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'SLI/SLO',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Error budget',
        ),
        2 => 
        array (
          'time' => '30–47',
          'title' => 'Incident impact',
        ),
        3 => 
        array (
          'time' => '47–64',
          'title' => 'Timeline',
        ),
        4 => 
        array (
          'time' => '64–82',
          'title' => 'Postmortem',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Decision gate',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'slo',
          'time' => '15 min',
          'title' => '01 · SLI → SLO',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'slo-postmortem',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči SLO Lab.',
            1 => 'Vyber user-facing SLI.',
            2 => 'Nastav 30denní SLO.',
          ),
        ),
        1 => 
        array (
          'id' => 'budget',
          'time' => '15 min',
          'title' => '02 · Error budget',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Spočítej tolerovaný čas.',
            1 => 'Porovnej s incidentem.',
            2 => 'Rozhodni, zda je budget vyčerpán.',
          ),
        ),
        2 => 
        array (
          'id' => 'impact',
          'time' => '17 min',
          'title' => '03 · Dopad není jen uptime',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'http-observability',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči počet chybných requestů.',
            1 => 'Urči délku dopadu.',
            2 => 'Urči uživatelský symptom.',
          ),
        ),
        3 => 
        array (
          'id' => 'timeline',
          'time' => '17 min',
          'title' => '04 · Evidence timeline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď alert, deploy, symptom, rollback.',
            1 => 'Odděl fakta od domněnek.',
            2 => 'Označ decision point.',
          ),
        ),
        4 => 
        array (
          'id' => 'postmortem',
          'time' => '18 min',
          'title' => '05 · Blameless postmortem',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Root cause.',
            1 => 'Contributing factors.',
            2 => '3 follow-up akce s vlastníkem a termínem.',
          ),
        ),
        5 => 
        array (
          'id' => 'gate',
          'time' => '8 min',
          'title' => '06 · Release gate',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Error budget je vyčerpaný a nový release není urgentní. Jaký je nejlepší default?',
          'options' => 
          array (
            0 => 'Zpomalit rizikové změny a nejdřív obnovit spolehlivost.',
            1 => 'Přidat traffic na canary na 100 %.',
            2 => 'Vypnout SLO alert.',
          ),
          'correct' => 0,
          'explanation' => 'Error budget má ovlivnit tempo změn a dát prostor stabilizaci.',
        ),
      ),
      'lm71_source' => 'more',
    ),
    8 => 
    array (
      'id' => 'ops_iac_drift',
      'number' => 8,
      'title' => 'Lekce 8 · Infrastructure as Code + drift',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Řídit produkční konfiguraci jako verzovaný desired state, kontrolovat diff před změnou a bezpečně řešit configuration drift.',
      'knowledge' => 
      array (
        0 => 'infrastructure-as-code',
        1 => 'config-drift',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Desired state',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Plan',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Review',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Drift',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Reconcile',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Runbook',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'desired',
          'time' => '15 min',
          'title' => '01 · Desired state',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'infrastructure-as-code',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Plan → Apply Lab.',
            1 => 'Popiš desired state.',
            2 => 'Urči source of truth.',
          ),
        ),
        1 => 
        array (
          'id' => 'plan',
          'time' => '15 min',
          'title' => '02 · Plan / diff',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přečti diff.',
            1 => 'Urči blast radius.',
            2 => 'Označ neočekávanou změnu.',
          ),
        ),
        2 => 
        array (
          'id' => 'review',
          'time' => '15 min',
          'title' => '03 · Review gate',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozděl změnu na malý scope.',
            1 => 'Přidej success criteria.',
            2 => 'Připrav rollback.',
          ),
        ),
        3 => 
        array (
          'id' => 'drift',
          'time' => '17 min',
          'title' => '04 · Configuration drift',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'config-drift',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Drift Detection Lab.',
            1 => 'Porovnej desired/actual.',
            2 => 'Zjisti původ driftu.',
          ),
        ),
        4 => 
        array (
          'id' => 'reconcile',
          'time' => '18 min',
          'title' => '05 · Reconcile',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Vyber source of truth.',
            1 => 'Sjednoť runtime a repo.',
            2 => 'Validuj po změně.',
          ),
        ),
        5 => 
        array (
          'id' => 'runbook',
          'time' => '10 min',
          'title' => '06 · IaC runbook',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Jaký je bezpečný default před apply?',
          'options' => 
          array (
            0 => 'Zkontrolovat plan/diff, scope, rollback a success criteria.',
            1 => 'Spustit apply přímo v produkci bez review.',
            2 => 'Vypnout monitoring.',
          ),
          'correct' => 0,
          'explanation' => 'IaC snižuje riziko jen tehdy, když workflow obsahuje kontrolní body.',
        ),
      ),
      'lm71_source' => 'ecosystem',
    ),
    9 => 
    array (
      'id' => 'ops_performance_capacity',
      'number' => 9,
      'title' => 'Lekce 9 · Performance + capacity engineering',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Najít bottleneck pomocí latency/throughput/saturation a převést měření na realistický plán kapacity s headroomem.',
      'knowledge' => 
      array (
        0 => 'performance-engineering',
        1 => 'capacity-planning',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Baseline',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Percentiles',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Saturation',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Load test',
        ),
        4 => 
        array (
          'time' => '62–82',
          'title' => 'Capacity decision',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'baseline',
          'time' => '15 min',
          'title' => '01 · Performance baseline',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'performance-engineering',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Latency & Saturation Lab.',
            1 => 'Zapiš p50/p95/error rate.',
            2 => 'Najdi jeden resource signal.',
          ),
        ),
        1 => 
        array (
          'id' => 'percentiles',
          'time' => '15 min',
          'title' => '02 · Percentiles',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Porovnej p50 a p95.',
            1 => 'Najdi tail latency.',
            2 => 'Vysvětli uživatelský dopad.',
          ),
        ),
        2 => 
        array (
          'id' => 'saturation',
          'time' => '15 min',
          'title' => '03 · Bottleneck',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sleduj CPU/I/O/queue.',
            1 => 'Najdi korelaci s latency.',
            2 => 'Formuluj hypotézu.',
          ),
        ),
        3 => 
        array (
          'id' => 'load',
          'time' => '17 min',
          'title' => '04 · Capacity lab',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'capacity-planning',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Capacity & Headroom Lab.',
            1 => 'Najdi bod degradace.',
            2 => 'Definuj cílovou zátěž.',
          ),
        ),
        4 => 
        array (
          'id' => 'decision',
          'time' => '20 min',
          'title' => '05 · Scale decision',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Přidej headroom.',
            1 => 'Porovnej scale up/out/optimize.',
            2 => 'Otestuj failover scénář.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Systém splňuje SLO do 700 RPS, cíl je 900 RPS a potřebuješ 25 % rezervu. Co plyne?',
          'options' => 
          array (
            0 => 'Současná kapacita nestačí a je potřeba scale/optimalizace před cílovým provozem.',
            1 => 'Stačí ignorovat headroom.',
            2 => 'Snížit monitoring interval na nulu.',
          ),
          'correct' => 0,
          'explanation' => 'Kapacita musí pokrýt cíl i bezpečnou rezervu.',
        ),
      ),
      'lm71_source' => 'ecosystem',
    ),
    10 => 
    array (
      'id' => 'ops_systemd_dependencies',
      'number' => 10,
      'title' => 'Lekce 10 · systemd do hloubky: dependencies, restart policy, failure',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.',
      'knowledge' => 
      array (
        0 => 'systemd-advanced',
        1 => 'logs-monitoring',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Unit anatomy',
          'text' => 'Rozliš Unit/Service/Install.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Dependencies',
          'text' => 'Rozliš After/Wants/Requires konceptuálně.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Restart loop',
          'text' => 'Z logu zjisti proč proces padá.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Ordering',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Drop-in/override mindset',
          'text' => 'Navrhni minimální override místo kopie celého unit souboru.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Validate',
          'text' => 'daemon-reload konceptuálně.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'unit',
          'time' => '15 min',
          'title' => '01 · Unit anatomy',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'systemd-advanced',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš Unit/Service/Install.',
            1 => 'Najdi ExecStart, User a Restart.',
          ),
        ),
        1 => 
        array (
          'id' => 'deps',
          'time' => '15 min',
          'title' => '02 · Dependencies',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš After/Wants/Requires konceptuálně.',
            1 => 'Nakresli dependency chain služby.',
          ),
        ),
        2 => 
        array (
          'id' => 'failure',
          'time' => '15 min',
          'title' => '03 · Restart loop',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Z logu zjisti proč proces padá.',
            1 => 'Nezvyšuj restart aggressiveness před opravou příčiny.',
          ),
        ),
        3 => 
        array (
          'id' => 'after',
          'time' => '12 min',
          'title' => '04 · Ordering',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co typicky vyjadřuje After=?',
          'options' => 
          array (
            0 => 'Pořadí startu vůči jiné unit; samo o sobě nemusí vytvářet tvrdou závislost.',
            1 => 'Firewall allow rule.',
            2 => 'DNS priority.',
          ),
          'correct' => 0,
          'explanation' => 'Ordering a dependency nejsou totéž.',
        ),
        4 => 
        array (
          'id' => 'override',
          'time' => '15 min',
          'title' => '05 · Drop-in/override mindset',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni minimální override místo kopie celého unit souboru.',
            1 => 'Zapiš rollback.',
          ),
        ),
        5 => 
        array (
          'id' => 'validate',
          'time' => '15 min',
          'title' => '06 · Validate',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'daemon-reload konceptuálně.',
            1 => 'Status + log + health check po změně.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Unit anatomy.',
        1 => 'Dependency diagram.',
        2 => 'Failure evidence.',
        3 => 'Override + rollback.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej testovací unit.',
        1 => 'Důraz na evidence před restartem.',
      ),
      'lm71_source' => 'yearpack',
    ),
    11 => 
    array (
      'id' => 'ops_storage_filesystems',
      'number' => 11,
      'title' => 'Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.',
      'knowledge' => 
      array (
        0 => 'storage-filesystems',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Storage layers',
          'text' => 'Device → partition/LV → filesystem → mount point.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'df/du mindset',
          'text' => 'Zjisti, který filesystem je plný.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Log growth',
          'text' => 'Najdi podezřelý růst logu.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Inodes',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Mount validation',
          'text' => 'Ověř správný mount point po reboot scénáři.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Prevence',
          'text' => 'Navrhni monitoring kapacity + retention.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'layers',
          'time' => '15 min',
          'title' => '01 · Storage layers',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'storage-filesystems',
          ),
          'tasks' => 
          array (
            0 => 'Device → partition/LV → filesystem → mount point.',
            1 => 'Rozliš kapacitu a inode.',
          ),
        ),
        1 => 
        array (
          'id' => 'measure',
          'time' => '15 min',
          'title' => '02 · df/du mindset',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zjisti, který filesystem je plný.',
            1 => 'Teprve potom hledej velké adresáře.',
          ),
        ),
        2 => 
        array (
          'id' => 'logs',
          'time' => '15 min',
          'title' => '03 · Log growth',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Najdi podezřelý růst logu.',
            1 => 'Nevymaž náhodně aktivní log bez pochopení služby.',
          ),
        ),
        3 => 
        array (
          'id' => 'inode',
          'time' => '12 min',
          'title' => '04 · Inodes',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Filesystem hlásí volné GB, ale nelze vytvořit soubor. Co může být problém?',
          'options' => 
          array (
            0 => 'Vyčerpané inodes / příliš mnoho souborů.',
            1 => 'DNS cache.',
            2 => 'TLS SAN.',
          ),
          'correct' => 0,
          'explanation' => 'Kapacita v bajtech není jediný limit filesystemu.',
        ),
        4 => 
        array (
          'id' => 'mount',
          'time' => '15 min',
          'title' => '05 · Mount validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř správný mount point po reboot scénáři.',
            1 => 'Zapiš bezpečný recovery postup.',
          ),
        ),
        5 => 
        array (
          'id' => 'prevent',
          'time' => '15 min',
          'title' => '06 · Prevence',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni monitoring kapacity + retention.',
            1 => 'Definuj threshold a akci.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Storage diagram.',
        1 => 'df vs du vs inode vysvětlení.',
        2 => 'Incident disk full – 5 kroků.',
        3 => 'Preventivní monitoring.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nedávej studentům mazat skutečné systémové logy.',
        1 => 'Pracujte s připravenou strukturou dat.',
      ),
      'lm71_source' => 'yearpack',
    ),
    12 => 
    array (
      'id' => 'ops_backup_restore',
      'number' => 12,
      'title' => 'Lekce 12 · Backup strategie: RPO/RTO a restore drill',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.',
      'knowledge' => 
      array (
        0 => 'backup-strategy',
        1 => 'backup-restore',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'RPO/RTO',
          'text' => 'RPO = kolik dat smíš ztratit.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Co zálohovat',
          'text' => 'Data, konfigurace, metadata/secrets odděleně podle rizika.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Restore drill',
          'text' => 'Obnov do testovacího cíle.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Backup vs restore',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Integrity + app validation',
          'text' => 'Ověř soubory/databázi podle typu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Runbook',
          'text' => 'Napiš restore kroky, odpovědnosti a stop conditions.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'rpo',
          'time' => '15 min',
          'title' => '01 · RPO/RTO',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'backup-strategy',
          ),
          'tasks' => 
          array (
            0 => 'RPO = kolik dat smíš ztratit.',
            1 => 'RTO = jak dlouho může trvat obnova.',
          ),
        ),
        1 => 
        array (
          'id' => 'strategy',
          'time' => '15 min',
          'title' => '02 · Co zálohovat',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Data, konfigurace, metadata/secrets odděleně podle rizika.',
            1 => 'Definuj retention.',
          ),
        ),
        2 => 
        array (
          'id' => 'restore',
          'time' => '15 min',
          'title' => '03 · Restore drill',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Obnov do testovacího cíle.',
            1 => 'Nenič produkční data během testu.',
          ),
        ),
        3 => 
        array (
          'id' => 'backup',
          'time' => '12 min',
          'title' => '04 · Backup vs restore',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Kdy je backup skutečně důvěryhodný?',
          'options' => 
          array (
            0 => 'Když byl obnoven a výsledek ověřen.',
            1 => 'Když job skončil zelenou ikonou.',
            2 => 'Když je soubor velký.',
          ),
          'correct' => 0,
          'explanation' => 'Bez restore testu neznáš reálnou obnovitelnost.',
        ),
        4 => 
        array (
          'id' => 'integrity',
          'time' => '15 min',
          'title' => '05 · Integrity + app validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř soubory/databázi podle typu.',
            1 => 'Spusť aplikační health test.',
          ),
        ),
        5 => 
        array (
          'id' => 'runbook',
          'time' => '15 min',
          'title' => '06 · Runbook',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Napiš restore kroky, odpovědnosti a stop conditions.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'RPO/RTO.',
        1 => 'Backup matrix.',
        2 => 'Restore evidence.',
        3 => 'Runbook.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Vhodné pro simulovaný dataset.',
        1 => 'Uč rozdíl backup success vs business recovery.',
      ),
      'lm71_source' => 'yearpack',
    ),
    13 => 
    array (
      'id' => 'ops_hardening',
      'number' => 13,
      'title' => 'Lekce 13 · Hardening Linux služby: SSH, firewall, aktualizace a minimální práva',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout bezpečné minimum služby bez „security by checkbox“ a současně zachovat ověřitelný přístup a rollback.',
      'knowledge' => 
      array (
        0 => 'ssh-hardening',
        1 => 'backup-strategy',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Attack surface',
          'text' => 'Sepiš vystavené služby.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'SSH baseline',
          'text' => 'Klíče, omezené účty a bezpečná práva.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Firewall scope',
          'text' => 'Povol management pouze z potřebné zóny/IP rozsahu.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Patch plan',
          'text' => 'Zjisti dopad aktualizace.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Lockout risk',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Security validation',
          'text' => 'Pozitivní test oprávněného přístupu.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'surface',
          'time' => '15 min',
          'title' => '01 · Attack surface',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'ssh-hardening',
          ),
          'tasks' => 
          array (
            0 => 'Sepiš vystavené služby.',
            1 => 'Odstraň/omez nepotřebné cesty přístupu.',
          ),
        ),
        1 => 
        array (
          'id' => 'ssh',
          'time' => '15 min',
          'title' => '02 · SSH baseline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Klíče, omezené účty a bezpečná práva.',
            1 => 'Nesdílej privátní klíče.',
          ),
        ),
        2 => 
        array (
          'id' => 'fw',
          'time' => '15 min',
          'title' => '03 · Firewall scope',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Povol management pouze z potřebné zóny/IP rozsahu.',
            1 => 'Připrav console/rollback cestu.',
          ),
        ),
        3 => 
        array (
          'id' => 'patch',
          'time' => '15 min',
          'title' => '04 · Patch plan',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zjisti dopad aktualizace.',
            1 => 'Definuj restart potřebu a validační test.',
          ),
        ),
        4 => 
        array (
          'id' => 'lockout',
          'time' => '12 min',
          'title' => '05 · Lockout risk',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co musíš řešit před zpřísněním vzdáleného SSH/firewall přístupu?',
          'options' => 
          array (
            0 => 'Ověřený alternativní přístup/rollback, aby ses nezamkl venku.',
            1 => 'Jen barvu promptu.',
            2 => 'TTL webového obrázku.',
          ),
          'correct' => 0,
          'explanation' => 'Hardening bez recovery plánu může vytvořit vlastní incident.',
        ),
        5 => 
        array (
          'id' => 'verify',
          'time' => '15 min',
          'title' => '06 · Security validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Pozitivní test oprávněného přístupu.',
            1 => 'Negativní test zakázané cesty.',
            2 => 'Audit log změny.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Attack surface list.',
        1 => 'SSH baseline.',
        2 => 'Firewall scope.',
        3 => 'Patch+rollback plán.',
        4 => 'Positive/negative validation.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Defenzivní lab; nepracovat s cizími systémy.',
        1 => 'Důraz na minimální scope a recovery.',
      ),
      'lm71_source' => 'yearpack',
    ),
    14 => 
    array (
      'id' => 'ops_containers',
      'number' => 14,
      'title' => 'Lekce 14 · Containers: proces, image, volume a síť',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.',
      'knowledge' => 
      array (
        0 => 'containers-basics',
        1 => 'binding',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Container mental model',
          'text' => 'Image ≠ container.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Port mapping',
          'text' => 'Rozliš container port a host port.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Persistent data',
          'text' => 'Urči, která data musí přežít nový container.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Recreate',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Health + logs',
          'text' => 'Ověř status/health.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Incident',
          'text' => 'Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '15 min',
          'title' => '01 · Container mental model',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'containers-basics',
          ),
          'tasks' => 
          array (
            0 => 'Image ≠ container.',
            1 => 'Volume ≠ image layer.',
            2 => 'Port publish ≠ aplikace automaticky poslouchá.',
          ),
        ),
        1 => 
        array (
          'id' => 'ports',
          'time' => '15 min',
          'title' => '02 · Port mapping',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš container port a host port.',
            1 => 'Ověř bind/listener uvnitř služby.',
          ),
        ),
        2 => 
        array (
          'id' => 'volume',
          'time' => '15 min',
          'title' => '03 · Persistent data',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči, která data musí přežít nový container.',
            1 => 'Neukládej secrets do image.',
          ),
        ),
        3 => 
        array (
          'id' => 'restart',
          'time' => '12 min',
          'title' => '04 · Recreate',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co se typicky stane s daty uloženými jen ve writable layer containeru po jeho odstranění/recreate?',
          'options' => 
          array (
            0 => 'Mohou být ztracena; persistentní data patří do vhodného volume/storage.',
            1 => 'Automaticky se přesunou do DNS.',
            2 => 'Vždy se uloží do image registry.',
          ),
          'correct' => 0,
          'explanation' => 'Ephemeral runtime a persistent storage jsou oddělené koncepty.',
        ),
        4 => 
        array (
          'id' => 'health',
          'time' => '15 min',
          'title' => '05 · Health + logs',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř status/health.',
            1 => 'Přečti aplikační log před změnou.',
          ),
        ),
        5 => 
        array (
          'id' => 'incident',
          'time' => '15 min',
          'title' => '06 · Incident',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu.',
            1 => 'Navrhni nejmenší opravu + validaci.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Image/container/volume/network diagram.',
        1 => 'Port mapping.',
        2 => 'Persistent data decision.',
        3 => 'Container incident evidence.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Lze realizovat v Dockeru/Podmanu nebo čistě simulovat.',
        1 => 'Nezaváděj orchestraci dřív, než studenti chápou jednu instanci.',
      ),
      'lm71_source' => 'yearpack',
    ),
    15 => 
    array (
      'id' => 'ops_automation_consistency',
      'number' => 15,
      'title' => 'Lekce 15 · Automatizace + configuration consistency',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.',
      'knowledge' => 
      array (
        0 => 'automation-shell',
        1 => 'config-drift',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Idempotentní změna',
          'text' => 'Nejdřív zjisti current state.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Preconditions',
          'text' => 'Ověř host, config a backup/rollback.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Drift',
          'text' => 'Porovnej deklarovaný a skutečný stav.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Automatizace',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Dry-run/plan',
          'text' => 'Vygeneruj plán změn.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Apply + validate',
          'text' => 'Po změně změř desired outcome.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'idempotent',
          'time' => '15 min',
          'title' => '01 · Idempotentní změna',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'automation-shell',
          ),
          'tasks' => 
          array (
            0 => 'Nejdřív zjisti current state.',
            1 => 'Pokud je desired state už splněn, nic zbytečně neměň.',
          ),
        ),
        1 => 
        array (
          'id' => 'guard',
          'time' => '15 min',
          'title' => '02 · Preconditions',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř host, config a backup/rollback.',
            1 => 'Při nesplněné podmínce bezpečně skonči.',
          ),
        ),
        2 => 
        array (
          'id' => 'drift',
          'time' => '15 min',
          'title' => '03 · Drift',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Porovnej deklarovaný a skutečný stav.',
            1 => 'Rozhodni, který je source of truth.',
          ),
        ),
        3 => 
        array (
          'id' => 'script',
          'time' => '12 min',
          'title' => '04 · Automatizace',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je horší než ruční postup?',
          'options' => 
          array (
            0 => 'Automatizace, která rychle a opakovaně provádí chybnou změnu bez guardů.',
            1 => 'Skript s dry-runem.',
            2 => 'Validace po změně.',
          ),
          'correct' => 0,
          'explanation' => 'Automatizace násobí dobré i špatné rozhodnutí.',
        ),
        4 => 
        array (
          'id' => 'dry',
          'time' => '15 min',
          'title' => '05 · Dry-run/plan',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vygeneruj plán změn.',
            1 => 'Zkontroluj scope před apply.',
          ),
        ),
        5 => 
        array (
          'id' => 'evidence',
          'time' => '15 min',
          'title' => '06 · Apply + validate',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Po změně změř desired outcome.',
            1 => 'Zapiš idempotentní druhý průchod bez změny.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Current vs desired state.',
        1 => 'Preconditions.',
        2 => 'Pseudo-script.',
        3 => 'Dry-run output.',
        4 => 'Validation.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Zadání drž defenzivní a v sandboxu.',
        1 => 'Hodnoť safe failure a idempotenci.',
      ),
      'lm71_source' => 'yearpack',
    ),
    16 => 
    array (
      'id' => 'ops_packet_diagnostics',
      'number' => 16,
      'title' => 'Lekce 16 · Pokročilá síťová diagnostika: packet evidence + socket state',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Propojit packet capture, TCP stavy a serverový listener do jedné incidentní hypotézy.',
      'knowledge' => 
      array (
        0 => 'packet-diagnostics-advanced',
        1 => 'tcp',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Capture with question',
          'text' => 'Formuluj hypotézu před filtrem.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'SYN patterns',
          'text' => 'Rozliš timeout, RST a SYN-ACK.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Server socket',
          'text' => 'Porovnej capture s ss/lsof.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'RST',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Po TCP',
          'text' => 'Když TCP funguje, pokračuj TLS/HTTP podle symptomu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Incident timeline',
          'text' => 'Seřaď packet + server evidence podle času.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'capture',
          'time' => '15 min',
          'title' => '01 · Capture with question',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'packet-diagnostics-advanced',
          ),
          'tasks' => 
          array (
            0 => 'Formuluj hypotézu před filtrem.',
            1 => 'Zachyť jen potřebný provoz.',
          ),
        ),
        1 => 
        array (
          'id' => 'syn',
          'time' => '15 min',
          'title' => '02 · SYN patterns',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš timeout, RST a SYN-ACK.',
            1 => 'Propoj s firewallem/listenerem.',
          ),
        ),
        2 => 
        array (
          'id' => 'server',
          'time' => '15 min',
          'title' => '03 · Server socket',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Porovnej capture s ss/lsof.',
            1 => 'Ověř správný bind.',
          ),
        ),
        3 => 
        array (
          'id' => 'rst',
          'time' => '12 min',
          'title' => '04 · RST',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'SYN → okamžitý RST typicky znamená?',
          'options' => 
          array (
            0 => 'Cíl je dosažitelný, ale port/spojení je aktivně odmítnuté.',
            1 => 'DNS dotaz se nikdy neposlal.',
            2 => 'Klient nemá MAC adresu gateway.',
          ),
          'correct' => 0,
          'explanation' => 'RST je explicitní TCP odpověď.',
        ),
        4 => 
        array (
          'id' => 'tls',
          'time' => '15 min',
          'title' => '05 · Po TCP',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Když TCP funguje, pokračuj TLS/HTTP podle symptomu.',
            1 => 'Neskákej zpět k DHCP bez evidence.',
          ),
        ),
        5 => 
        array (
          'id' => 'timeline',
          'time' => '15 min',
          'title' => '06 · Incident timeline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď packet + server evidence podle času.',
            1 => 'Napiš root-cause boundary: co víš a co ještě ne.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Hypotéza.',
        1 => 'Packet pattern.',
        2 => 'Socket evidence.',
        3 => 'Layer boundary.',
        4 => 'Root cause nebo další test.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej předpřipravené pcapy nebo izolovaný lab.',
        1 => 'Nezachytávej citlivý provoz třetích osob.',
      ),
      'lm71_source' => 'yearpack',
    ),
    17 => 
    array (
      'id' => 'ops_incident_runbook',
      'number' => 17,
      'title' => 'Lekce 17 · Incident response: runbook, komunikace a blameless postmortem',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Řídit incident podle severity, rolí, timeline, mitigation a následného postmortemu bez chaosu a hledání viníka.',
      'knowledge' => 
      array (
        0 => 'incident-runbook',
        1 => 'slo-postmortem',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Incident roles',
          'text' => 'Incident lead, investigator, communicator.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Severity + impact',
          'text' => 'Popiš uživatelský dopad.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Mitigation first',
          'text' => 'Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Timeline',
          'text' => 'Zapisuj fakta s časem.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Postmortem',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Follow-up',
          'text' => 'Každá akce má ownera, prioritu a ověřitelný výsledek.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'roles',
          'time' => '15 min',
          'title' => '01 · Incident roles',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'incident-runbook',
          ),
          'tasks' => 
          array (
            0 => 'Incident lead, investigator, communicator.',
            1 => 'Odděl koordinaci od hluboké diagnostiky.',
          ),
        ),
        1 => 
        array (
          'id' => 'severity',
          'time' => '15 min',
          'title' => '02 · Severity + impact',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Popiš uživatelský dopad.',
            1 => 'Nastav prioritu podle dopadu, ne technické zajímavosti.',
          ),
        ),
        2 => 
        array (
          'id' => 'mitigate',
          'time' => '15 min',
          'title' => '03 · Mitigation first',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby.',
            1 => 'Root cause může pokračovat po stabilizaci.',
          ),
        ),
        3 => 
        array (
          'id' => 'timeline',
          'time' => '15 min',
          'title' => '04 · Timeline',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zapisuj fakta s časem.',
            1 => 'Odděl fakta a hypotézy.',
          ),
        ),
        4 => 
        array (
          'id' => 'blame',
          'time' => '12 min',
          'title' => '05 · Postmortem',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je cílem blameless postmortemu?',
          'options' => 
          array (
            0 => 'Pochopit systémové faktory a definovat konkrétní preventivní akce.',
            1 => 'Najít jednoho člověka k potrestání.',
            2 => 'Vyhnout se technickým detailům.',
          ),
          'correct' => 0,
          'explanation' => 'Postmortem má zlepšit systém a proces.',
        ),
        5 => 
        array (
          'id' => 'actions',
          'time' => '15 min',
          'title' => '06 · Follow-up',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Každá akce má ownera, prioritu a ověřitelný výsledek.',
            1 => 'Vyber 2 nejdůležitější.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Severity/impact.',
        1 => 'Role assignment.',
        2 => 'Timeline.',
        3 => 'Mitigation.',
        4 => 'Postmortem: 2 follow-up actions.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Použij Project Workspace role Leader/Developer/QA/Presenter pro týmový incident.',
        1 => 'Hodnoť rozhodování a komunikaci stejně jako techniku.',
      ),
      'lm71_source' => 'yearpack',
    ),
    18 => 
    array (
      'id' => 'ops_reliability_capstone',
      'number' => 18,
      'title' => 'Lekce 18 · Capstone: produkční reliability drill',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.',
      'knowledge' => 
      array (
        0 => 'systemd-advanced',
        1 => 'storage-filesystems',
        2 => 'backup-strategy',
        3 => 'ssh-hardening',
        4 => 'containers-basics',
        5 => 'automation-shell',
        6 => 'packet-diagnostics-advanced',
        7 => 'incident-runbook',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Triage',
          'text' => 'Urči impact/severity.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Evidence matrix',
          'text' => 'DNS/TCP/TLS/HTTP.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Mitigation',
          'text' => 'Rozhodni rollback/fix/failover podle evidence.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Recovery validation',
          'text' => 'Ověř user path, health, error rate a security boundary.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Prevent repeat',
          'text' => 'Navrhni guard/monitor/automation, který problém zachytí nebo omezí.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Postmortem defense',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'triage',
          'time' => '15 min',
          'title' => '01 · Triage',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Urči impact/severity.',
            1 => 'Zmraz riskantní změny.',
            2 => 'Rozděl role týmu.',
          ),
        ),
        1 => 
        array (
          'id' => 'evidence',
          'time' => '15 min',
          'title' => '02 · Evidence matrix',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'DNS/TCP/TLS/HTTP.',
            1 => 'Service/containers/logs/storage.',
            2 => 'Vyber nejmenší test pro každou hypotézu.',
          ),
        ),
        2 => 
        array (
          'id' => 'mitigation',
          'time' => '15 min',
          'title' => '03 · Mitigation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozhodni rollback/fix/failover podle evidence.',
            1 => 'Zapiš stop condition.',
          ),
        ),
        3 => 
        array (
          'id' => 'recovery',
          'time' => '15 min',
          'title' => '04 · Recovery validation',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř user path, health, error rate a security boundary.',
            1 => 'Při obnově dat proveď integrity check.',
          ),
        ),
        4 => 
        array (
          'id' => 'automation',
          'time' => '15 min',
          'title' => '05 · Prevent repeat',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni guard/monitor/automation, který problém zachytí nebo omezí.',
            1 => 'Neautomatizuj neověřený fix.',
          ),
        ),
        5 => 
        array (
          'id' => 'final',
          'time' => '12 min',
          'title' => '06 · Postmortem defense',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co nejlépe dokazuje zvládnutí capstone?',
          'options' => 
          array (
            0 => 'Konzistentní evidence chain, bezpečná obnova, validace a konkrétní preventivní kroky.',
            1 => 'Co nejvíc spuštěných příkazů.',
            2 => 'Jedna správná náhodná změna.',
          ),
          'correct' => 0,
          'explanation' => 'Reliability je opakovatelný rozhodovací proces.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Incident timeline.',
        1 => 'Evidence matrix.',
        2 => 'Mitigation + rollback.',
        3 => 'Validation matrix.',
        4 => 'Postmortem.',
        5 => '2 preventive actions.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Závěrečný týmový drill 4.A.',
        1 => 'Učitel může během scénáře injectovat další symptom, ale musí zachovat řešitelnost z evidence.',
      ),
      'lm71_source' => 'yearpack',
    ),
    19 => 
    array (
      'id' => 'v30_4a_19',
      'number' => 19,
      'title' => 'Lekce 19 · Golden signals lab',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.',
      'knowledge' => 
      array (
        0 => 'golden-signals',
        1 => 'logs-monitoring',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'golden-signals',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'golden-signals',
            1 => 'logs-monitoring',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    20 => 
    array (
      'id' => 'v30_4a_20',
      'number' => 20,
      'title' => 'Lekce 20 · Reverse proxy evidence',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Odlišit DNS/TLS/proxy/upstream závadu pomocí minimálního test chainu.',
      'knowledge' => 
      array (
        0 => 'reverse-proxy',
        1 => 'http-observability',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'reverse-proxy',
            1 => 'http-observability',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    21 => 
    array (
      'id' => 'v30_4a_21',
      'number' => 21,
      'title' => 'Lekce 21 · Container networking',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.',
      'knowledge' => 
      array (
        0 => 'container-networking',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'container-networking',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'container-networking',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    22 => 
    array (
      'id' => 'v30_4a_22',
      'number' => 22,
      'title' => 'Lekce 22 · Restore game day',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.',
      'knowledge' => 
      array (
        0 => 'backup-restore',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'backup-restore',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'backup-restore',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    23 => 
    array (
      'id' => 'v30_4a_23',
      'number' => 23,
      'title' => 'Lekce 23 · Hardening audit',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.',
      'knowledge' => 
      array (
        0 => 'ssh-hardening',
        1 => 'ssh-hardening',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'ssh-hardening',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'ssh-hardening',
            1 => 'ssh-hardening',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    24 => 
    array (
      'id' => 'v30_4a_24',
      'number' => 24,
      'title' => 'Lekce 24 · Safe change & rollback',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Definovat stop conditions, rollback trigger a následnou end-to-end validaci.',
      'knowledge' => 
      array (
        0 => 'rollback-strategy',
        1 => 'change-management',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'rollback-strategy',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'rollback-strategy',
            1 => 'change-management',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    25 => 
    array (
      'id' => 'v30_4a_25',
      'number' => 25,
      'title' => 'Lekce 25 · Drift & automation',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.',
      'knowledge' => 
      array (
        0 => 'config-drift',
        1 => 'infrastructure-as-code',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'config-drift',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'config-drift',
            1 => 'infrastructure-as-code',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    26 => 
    array (
      'id' => 'v30_4a_26',
      'number' => 26,
      'title' => 'Lekce 26 · Performance capacity',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.',
      'knowledge' => 
      array (
        0 => 'performance-engineering',
        1 => 'capacity-planning',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'performance-engineering',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'performance-engineering',
            1 => 'capacity-planning',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    27 => 
    array (
      'id' => 'v30_4a_27',
      'number' => 27,
      'title' => 'Lekce 27 · Incident command',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.',
      'knowledge' => 
      array (
        0 => 'incident-command',
        1 => 'slo-postmortem',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'incident-command',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'incident-command',
            1 => 'slo-postmortem',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
    28 => 
    array (
      'id' => 'v30_4a_28',
      'number' => 28,
      'title' => 'Lekce 28 · Reliability mastery',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Propojit observability, safe change, incident response a recovery do závěrečného reliability drill.',
      'knowledge' => 
      array (
        0 => 'golden-signals',
        1 => 'rollback-strategy',
        2 => 'incident-command',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'golden-signals',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'golden-signals',
            1 => 'rollback-strategy',
            2 => 'incident-command',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup je nejlepší při diagnostice?',
          'options' => 
          array (
            0 => 'Symptom → hypotéza → test → důkaz → nejmenší změna → validace.',
            1 => 'Restartovat vše a sledovat, zda problém zmizí.',
            2 => 'Měnit firewall, DNS a službu současně.',
          ),
          'correct' => 0,
          'explanation' => 'Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
      'lm71_source' => 'v30',
    ),
  ),
  'conflicts' => 
  array (
  ),
  'template_tasks' => 
  array (
    'fcfaee0c8cf0' => 10,
    '9840f242be1c' => 10,
    '29bcf19a752f' => 10,
    'ecdd13e619ae' => 10,
    '3da4ec25375b' => 10,
    'de9dbe43c2ef' => 10,
    'faa9348a9583' => 10,
    '2c621dfcc048' => 10,
    '6342205f34e7' => 10,
    'e16e397d010e' => 10,
  ),
  'overlay' => 
  array (
    'lessons' => 
    array (
      5 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 5 · Pozorovatelnost HTTP a vyvažování zátěže',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím číst odpověď HTTP jako diagnostický důkaz a vysvětlit, jak vyvažovač zátěže (load balancer) rozděluje provoz a vyřazuje nezdravý backend.',
          'success_criteria' => 
          array (
            0 => 'Ze stavového kódu a hlaviček (Server, Location, Retry-After) odvodím, co odpověď dokazuje.',
            1 => 'Porovnám rozdělování round-robin a vážené a popíšu jejich dopad.',
            2 => 'Navrhnu kontrolu zdraví /health s intervalem a prahem vyřazení.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže tři odpovědi HTTP (200, 301, 503) bez kontextu.',
            'student' => 'Odhadnou, co se na serveru děje.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Stav není jen číslo',
            'teacher' => 'Vysvětlí třídy 2xx–5xx a hlavičky Location a Retry-After (RFC 9110) jako základ pozorovatelnosti (observability).',
            'student' => 'Ke každé třídě napíšou, co dokazuje.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 38,
            'phase' => 'Rychlá sonda curl -I',
            'teacher' => 'Předvede curl -I v Linux Labu.',
            'student' => 'Přečtou stav a hlavičky odpovědi.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 55,
            'phase' => 'Rozdělení provozu',
            'teacher' => 'Předvede lab vyvažování (round-robin, vážené).',
            'student' => 'Sledují rozložení požadavků a vyřadí nezdravý backend.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Kontrola zdraví',
            'teacher' => 'Ukáže rozdíl „port otevřen“ a „/health vrací 200“.',
            'student' => 'Navrhnou kontrolu zdraví s intervalem a prahem.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Mini postmortem a exit ticket',
            'teacher' => 'Zadá formát příznak → důkaz → prevence.',
            'student' => 'Sepíšou mini postmortem a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Ke třídám stavů 2xx, 3xx, 4xx a 5xx napiš, co odpověď dokazuje o klientovi a serveru.',
            'output' => 'Tabulka čtyř tříd s významem.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'V Linux Labu spusť curl -I http://localhost a zapiš stav a hlavičku Server.',
            'output' => '„HTTP/1.1 200 OK“ a „Server: nginx/1.22.1“.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => 'HTTP/1.1 200 OK',
              ),
              1 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => 'Server: nginx/1.22.1',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'V labu vyvažování porovnej round-robin a vážené rozdělení a vyřaď backend, který vrací 500.',
            'output' => 'Záznam rozložení požadavků před a po vyřazení.',
            'time' => '17 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni kontrolu zdraví (health check): adresa /health, interval, timeout, počet neúspěchů do vyřazení a do návratu.',
            'output' => 'Specifikace kontroly zdraví.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku stavových kódů s příklady a šablonu kontroly zdraví.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Navrhne, co má /health kontrolovat uvnitř aplikace (např. databázi) a proč ne všechno.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Karty stavů: učitel řekne kód, třída ukáže „klient / server / přesměrování“.',
            1 => 'Kontrola návrhu: vyřadí tvoje kontrola backend, který má otevřený port, ale vrací 500?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Čtení HTTP',
              'levels' => 
              array (
                0 => 'Jen „funguje / nefunguje“.',
                1 => 'Zná třídy, ne hlavičky.',
                2 => 'Stav + hlavičky = konkrétní závěr.',
                3 => 'Navrhne další sondu podle odpovědi.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Vyvažování zátěže',
              'levels' => 
              array (
                0 => 'Nerozumí.',
                1 => 'Popíše jen round-robin.',
                2 => 'Porovná algoritmy a vyřazení.',
                3 => 'Zdůvodní volbu pro konkrétní situaci.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Kontrola zdraví',
              'levels' => 
              array (
                0 => 'Jen port.',
                1 => 'HTTP bez prahů.',
                2 => 'HTTP /health s intervalem a prahy.',
                3 => 'Řeší i návrat backendu a falešné poplachy.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Provoz webových služeb',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Odpověď 503 s hlavičkou Retry-After: 120 říká:',
              'options' => 
              array (
                0 => 'Stránka neexistuje.',
                1 => 'Služba je dočasně nedostupná a klient to může zkusit znovu zhruba za 120 sekund.',
                2 => 'Klient nemá oprávnění.',
              ),
              'correct' => 1,
              'explanation' => '503 = dočasná nedostupnost, Retry-After = kdy zkusit znovu.',
            ),
            1 => 
            array (
              'question' => 'Proč nestačí kontrola zdraví „port 443 je otevřený“?',
              'options' => 
              array (
                0 => 'Port může přijímat spojení, i když aplikace vrací chyby.',
                1 => 'Port 443 se nedá testovat.',
                2 => 'Protože kontrola portu je pomalejší.',
              ),
              'correct' => 0,
              'explanation' => 'Otevřený port ≠ zdravá aplikace.',
            ),
            2 => 
            array (
              'question' => 'Backend A má váhu 3, backend B váhu 1. Kolik ze 100 požadavků dostane zhruba B?',
              'options' => 
              array (
                0 => '50',
                1 => '75',
                2 => '25',
              ),
              'correct' => 2,
              'explanation' => 'Váhy 3 : 1 → B dostane 1/4.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v nástrojích vývojáře prohlížeče (záložka Síť) najdi u libovolného webu jeden požadavek se stavem 3xx a zapiš hlavičku Location.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Sondy (curl) posíláme jen na lab nebo na servery, které spravujeme; žádné zátěžové testy cizích služeb.',
          1 => 'Nástroje vývojáře jen ke čtení.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: 5xx = vždy chyba sítě. Je to chyba na straně serveru/aplikace.',
          1 => 'Otázka do třídy: Co tahle odpověď dokazuje a co ještě ne?',
          2 => 'Tempo: lab vyvažování je atraktivní, hlídej 17 minut – hlavní výstup je kontrola zdraví.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkol 2 v Linux Labu (výstup v zadání), úkoly 1, 3 a 4 na papír.',
          1 => 'Plán B offline: karty odpovědí HTTP a papírová simulace rozdělování požadavků (kartičky).',
        ),
        'glossary' => 
        array (
          0 => 'load-balancer',
          1 => 'round-robin',
          2 => 'health-check',
          3 => 'observability',
        ),
        '_file' => 'lesson_content_v72_4a_a.php',
      ),
      6 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 6 · Postupné nasazení a nácvik obnovy ze zálohy',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím naplánovat postupné (kanárkové) nasazení s podmínkou zastavení a rozhodnout mezi návratem verze a obnovou ze zálohy podle měřitelných signálů.',
          'success_criteria' => 
          array (
            0 => 'Před změnou zapíšu výchozí chybovost, p95 latence a stav poslední použitelné zálohy.',
            1 => 'Definuji podmínku zastavení kanárku předem, ne až během problému.',
            2 => 'Popíšu nácvik obnovy do testovacího cíle s kontrolou funkčnosti.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše nasazení „všem najednou“, které shodilo web.',
            'student' => 'Navrhnou, jak riziko zmenšit.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Než změníš produkci',
            'teacher' => 'Vysvětlí výchozí hodnoty (baseline).',
            'student' => 'Zapíší chybovost, p95 a poslední zálohu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 38,
            'phase' => 'Kanárek místo velkého třesku',
            'teacher' => 'Předvede lab: 10 % provozu na v2.',
            'student' => 'Porovnají v1 a v2 a definují podmínku zastavení.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 55,
            'phase' => 'Rozhodnutí podle dat',
            'teacher' => 'Ukáže tři scénáře metrik.',
            'student' => 'Rozhodnou: rozšířit, držet, nebo vrátit.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Nácvik obnovy',
            'teacher' => 'Vysvětlí výběr zálohy, kontrolu integrity a obnovu do testu.',
            'student' => 'Projdou nácvik obnovy v labu a ověří funkčnost.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Runbook a exit ticket',
            'teacher' => 'Zadá runbook nasazení a návratu.',
            'student' => 'Sepíšou 5 kroků a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Před změnou zapiš výchozí chybovost, p95 latence a datum poslední zálohy, kterou umíš obnovit.',
            'output' => 'Tabulka výchozích hodnot.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Nastav v labu kanárkové nasazení (canary) 10 % provozu na v2 a předem zapiš podmínku zastavení (např. chybovost 2× nad výchozí po 5 minut).',
            'output' => 'Podmínka zastavení + porovnání v1/v2.',
            'time' => '16 min',
          ),
          2 => 
          array (
            'text' => 'U tří scénářů metrik rozhodni: rozšířit, držet, nebo vrátit (rollback), a zdůvodni podle dat.',
            'output' => 'Tři rozhodnutí se zdůvodněním.',
            'time' => '17 min',
          ),
          3 => 
          array (
            'text' => 'Projdi nácvik obnovy (restore): vyber zálohu, ověř integritu, obnov do testovacího cíle a proveď funkční kontrolu.',
            'output' => 'Záznam nácviku ve 4 krocích.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku „signál → rozhodnutí“ a šablonu runbooku.',
          'standard' => 'Úkoly 1–4 a runbook podle zadání.',
          'challenge' => 'Popíše situaci, kdy návrat verze nestačí (změněná data) a je potřeba obnova ze zálohy.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Hlasování: scénář metrik na tabuli – rozšířit / držet / vrátit?',
            1 => 'Kontrola podmínky: je podmínka zastavení měřitelná?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Výchozí stav',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen jedna metrika.',
                2 => 'Chybovost, p95, poslední obnovitelná záloha.',
                3 => 'Zdůvodní, proč právě tyto metriky.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Kanárek a rozhodnutí',
              'levels' => 
              array (
                0 => 'Velký třesk.',
                1 => 'Kanárek bez podmínky.',
                2 => 'Měřitelná podmínka a správná rozhodnutí.',
                3 => 'Odliší šum od trendu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Obnova',
              'levels' => 
              array (
                0 => 'Jen „máme zálohu“.',
                1 => 'Obnova bez kontroly.',
                2 => 'Výběr, integrita, test, funkční kontrola.',
                3 => 'Odliší rollback od restore.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Řízené změny',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč se podmínka zastavení kanárku píše předem?',
              'options' => 
              array (
                0 => 'Aby se pod tlakem nerozhodovalo podle dojmu a zastavení bylo rychlé.',
                1 => 'Protože to vyžaduje zákon.',
                2 => 'Aby kanárek běžel déle.',
              ),
              'correct' => 0,
              'explanation' => 'Předem dohodnuté kritérium = rychlé a nestranné rozhodnutí.',
            ),
            1 => 
            array (
              'question' => 'Nová verze poškodila data v databázi. Co pomůže víc?',
              'options' => 
              array (
                0 => 'Jen návrat na starou verzi aplikace.',
                1 => 'Obnova dat ze zálohy (a návrat verze).',
                2 => 'Restart serveru.',
              ),
              'correct' => 1,
              'explanation' => 'Rollback vrátí kód, ne data.',
            ),
            2 => 
            array (
              'question' => 'Kdy je záloha opravdu důvěryhodná?',
              'options' => 
              array (
                0 => 'Když je soubor dost velký.',
                1 => 'Když úloha zálohy skončila bez chyby.',
                2 => 'Když byla obnovena a výsledek ověřen.',
              ),
              'correct' => 2,
              'explanation' => 'Důkazem je úspěšná obnova.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zjisti, jak často se zálohuje tvůj telefon, a zkus odhadnout, kolik dat bys při ztrátě přišel.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Nasazení a obnova jen v labu; produkční data školy se nikdy neobnovují pro cvičení.',
          1 => 'Zálohy mohou obsahovat osobní údaje – pracujeme jen s vymyšlenými daty.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „rozšíříme na 100 %, ať máme víc dat“. Data máme – rozhodujeme podle podmínky.',
          1 => 'Otázka do třídy: Co vrátí rollback a co ne?',
          2 => 'Tempo: nácvik obnovy je nejdelší – scénáře metrik mohou být rychlé hlasování.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1 a 3 na papír, úkoly 2 a 4 v labu podle zadání.',
          1 => 'Plán B offline: scénáře metrik na kartách a nácvik obnovy jako papírový checklist.',
        ),
        'glossary' => 
        array (
          0 => 'canary',
          1 => 'rollback',
          2 => 'restore',
          3 => 'p95',
        ),
        '_file' => 'lesson_content_v72_4a_a.php',
      ),
      7 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 7 · Cíle spolehlivosti, rozpočet chyb a rozbor incidentu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést cíl spolehlivosti (SLO) na rozpočet chyb v minutách, rozhodnout podle něj o dalších změnách a sepsat rozbor incidentu bez hledání viníka.',
          'success_criteria' => 
          array (
            0 => 'Spočítám rozpočet chyb pro SLO 99,9 % za 30 dní (43,2 minuty).',
            1 => 'Seřadím časovou osu incidentu a oddělím fakta od domněnek.',
            2 => 'Navrhnu tři opatření s vlastníkem a termínem.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Zeptá se: je 99 % dostupnost hodně, nebo málo?',
            'student' => 'Odhadnou, kolik hodin výpadku to je za měsíc.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'SLI → SLO',
            'teacher' => 'Předvede SLO Lab a ukazatel z pohledu uživatele.',
            'student' => 'Vyberou ukazatel (SLI) a nastaví 30denní SLO.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Rozpočet chyb',
            'teacher' => 'Spočítá s třídou rozpočet pro 99,9 %.',
            'student' => 'Spočítají rozpočet a porovnají s incidentem.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 57,
            'phase' => 'Časová osa důkazů',
            'teacher' => 'Rozdá kartičky událostí incidentu.',
            'student' => 'Seřadí upozornění, nasazení, příznak a návrat a označí rozhodovací bod.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 80,
            'phase' => 'Rozbor bez viníka',
            'teacher' => 'Vysvětlí blameless postmortem.',
            'student' => 'Sepíšou příčinu, přispívající faktory a 3 opatření.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Rozhodnutí o vydání a exit ticket',
            'teacher' => 'Zeptá se: pustíme další změnu?',
            'student' => 'Rozhodnou podle rozpočtu a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vyber ukazatel spolehlivosti z pohledu uživatele (SLI) pro školní web a nastav 30denní cíl (SLO).',
            'output' => 'SLI (např. podíl úspěšných požadavků) a SLO v %.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Spočítej rozpočet chyb (error budget) pro SLO 99,9 % a 99,5 % za 30 dní v minutách.',
            'output' => '99,9 % → 43,2 min; 99,5 % → 216 min.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Seřaď kartičky incidentu na časovou osu, odděl fakta od domněnek a označ rozhodovací bod.',
            'output' => 'Časová osa s označenými fakty a domněnkami.',
            'time' => '17 min',
          ),
          3 => 
          array (
            'text' => 'Sepiš rozbor incidentu bez hledání viníka (blameless postmortem): příčina, přispívající faktory a 3 opatření s vlastníkem a termínem.',
            'output' => 'Rozbor na 1 stranu.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzorec rozpočtu (1 − SLO) × 30 × 24 × 60 a šablonu rozboru.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Navrhne pravidlo „když je spotřebováno 50 % rozpočtu do poloviny měsíce, zpomalíme změny“ a zdůvodní ho.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlý výpočet na tabuli: 99,9 % za 30 dní = ? minut.',
            1 => 'Fakt, nebo domněnka? Učitel čte věty z rozboru.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'SLI a SLO',
              'levels' => 
              array (
                0 => 'Jen „uptime“.',
                1 => 'SLI bez pohledu uživatele.',
                2 => 'Uživatelský SLI a rozumné SLO.',
                3 => 'Zdůvodní cíl potřebami uživatelů.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Rozpočet chyb',
              'levels' => 
              array (
                0 => 'Nespočítá.',
                1 => 'Výpočet s chybou.',
                2 => 'Správně pro 99,9 % i 99,5 %.',
                3 => 'Navrhne pravidlo podle spotřeby rozpočtu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rozbor incidentu',
              'levels' => 
              array (
                0 => 'Hledá viníka.',
                1 => 'Jen popis.',
                2 => 'Příčina, faktory, 3 opatření s vlastníkem.',
                3 => 'Opatření jsou měřitelná.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Spolehlivost a incidenty',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kolik minut výpadku za 30 dní dovoluje SLO 99,9 %?',
              'options' => 
              array (
                0 => '43,2 minuty',
                1 => '4,32 minuty',
                2 => '432 minut',
              ),
              'correct' => 0,
              'explanation' => '0,001 × 30 × 24 × 60 = 43,2.',
            ),
            1 => 
            array (
              'question' => 'Co je hlavní myšlenka rozboru incidentu „bez viníka“?',
              'options' => 
              array (
                0 => 'Nikdo nic nezapisuje.',
                1 => 'Hledáme, co v systému a postupech chybu umožnilo, ne koho potrestat.',
                2 => 'Rozbor dělá jen vedení.',
              ),
              'correct' => 1,
              'explanation' => 'Lidé pak mluví otevřeně a opatření míří na systém.',
            ),
            2 => 
            array (
              'question' => 'Který ukazatel je nejblíž zkušenosti uživatele?',
              'options' => 
              array (
                0 => 'Vytížení procesoru serveru.',
                1 => 'Počet restartů za týden.',
                2 => 'Podíl požadavků, které uživatel dostal úspěšně a dost rychle.',
              ),
              'correct' => 2,
              'explanation' => 'SLI měří to, co uživatel zažívá.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: spočítej, kolik minut výpadku za rok dovoluje SLO 99,95 %.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Incident je vymyšlený; v rozboru nepoužíváme jména skutečných lidí.',
          1 => 'Rozbory se sdílejí jen ve třídě.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: 99 % zní skvěle – přepočítej na hodiny (7,2 h za 30 dní).',
          1 => 'Otázka do třídy: Je tohle fakt, nebo náš výklad?',
          2 => 'Tempo: výpočty jsou rychlé, nech čas na rozbor ve dvojicích.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: celá hodina jde na papíře (výsledky výpočtů jsou v zadání).',
          1 => 'Plán B offline: kartičky incidentu a kalkulačka.',
        ),
        'glossary' => 
        array (
          0 => 'slo',
          1 => 'sli',
          2 => 'error-budget',
          3 => 'postmortem',
        ),
        '_file' => 'lesson_content_v72_4a_a.php',
      ),
      8 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 8 · Infrastruktura jako kód a odchylka konfigurace',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím popsat konfiguraci jako verzovaný požadovaný stav (infrastruktura jako kód, IaC), přečíst plán změn před aplikací a bezpečně vyřešit odchylku konfigurace (drift).',
          'success_criteria' => 
          array (
            0 => 'Určím zdroj pravdy (repozitář) a požadovaný stav.',
            1 => 'V plánu změn najdu neočekávanou změnu a odhadnu dosah (blast radius).',
            2 => 'U driftu rozhodnu, zda platí repozitář, nebo běžící stav, a navrhnu sjednocení s ověřením.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše dva servery, které „mají být stejné“, a nejsou.',
            'student' => 'Odhadnou, jak to vzniklo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Požadovaný stav',
            'teacher' => 'Předvede lab Plan → Apply.',
            'student' => 'Popíšou požadovaný stav a zdroj pravdy.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Plán a rozdíl',
            'teacher' => 'Ukáže, jak číst plán změn.',
            'student' => 'Najdou neočekávanou změnu a odhadnou dosah.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Kontrola před aplikací',
            'teacher' => 'Vysvětlí malý rozsah, kritéria úspěchu a návrat.',
            'student' => 'Rozdělí změnu na menší kroky.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Odchylka a sjednocení',
            'teacher' => 'Předvede lab Drift Detection.',
            'student' => 'Porovnají požadovaný a skutečný stav a navrhnou sjednocení.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Shrnutí a exit ticket',
            'teacher' => 'Shrne bezpečné výchozí chování.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'V labu Plan → Apply popiš požadovaný stav jedné služby a urči zdroj pravdy (source of truth).',
            'output' => 'Popis požadovaného stavu + zdroj pravdy.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Přečti plán změn (diff), najdi neočekávanou změnu a odhadni její dosah (blast radius).',
            'output' => 'Označená změna + odhad dosahu.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Rozděl velkou změnu na menší kroky, ke každému napiš kritérium úspěchu a postup návratu.',
            'output' => 'Plán kroků s kritérii a návratem.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'V labu Drift Detection porovnej požadovaný a skutečný stav, zjisti původ odchylky a navrhni sjednocení s ověřením.',
            'output' => 'Rozhodnutí o zdroji pravdy + kroky sjednocení + test.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane plán změn s barevně odlišenými řádky (přidat / změnit / smazat) a otázky k němu.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Navrhne kontrolu, která bude drift hlásit pravidelně (např. denně), a co s nálezem udělat.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž změnu: učitel promítne plán, třída ukáže řádek „smazat“.',
            1 => 'Otázka: kdo ručně změnil server a jak to zjistíme?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Požadovaný stav',
              'levels' => 
              array (
                0 => 'Nerozumí.',
                1 => 'Popíše, chybí zdroj pravdy.',
                2 => 'Požadovaný stav + zdroj pravdy.',
                3 => 'Vysvětlí výhodu verzování.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Čtení plánu',
              'levels' => 
              array (
                0 => 'Aplikuje bez čtení.',
                1 => 'Čte, nevidí riziko.',
                2 => 'Najde neočekávanou změnu a dosah.',
                3 => 'Navrhne rozdělení změny.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Drift',
              'levels' => 
              array (
                0 => 'Přepíše naslepo.',
                1 => 'Sjednotí bez ověření.',
                2 => 'Rozhodne o zdroji pravdy a ověří.',
                3 => 'Navrhne pravidelnou detekci.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Řízené změny',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co je odchylka konfigurace (drift)?',
              'options' => 
              array (
                0 => 'Rozdíl mezi požadovaným stavem v repozitáři a skutečným stavem serveru.',
                1 => 'Pomalé síťové připojení.',
                2 => 'Nová verze aplikace.',
              ),
              'correct' => 0,
              'explanation' => 'Drift vzniká typicky ruční změnou mimo repozitář.',
            ),
            1 => 
            array (
              'question' => 'V plánu změn vidíš „smazat databázi“, i když jsi měnil jen DNS záznam. Co uděláš?',
              'options' => 
              array (
                0 => 'Aplikuji, plán se nemýlí.',
                1 => 'Zastavím, zjistím příčinu a změnu neaplikuji, dokud ji nevysvětlím.',
                2 => 'Aplikuji v noci, kdy nikdo nepracuje.',
              ),
              'correct' => 1,
              'explanation' => 'Neočekávaná změna = stop.',
            ),
            2 => 
            array (
              'question' => 'Co znamená „dosah změny“ (blast radius)?',
              'options' => 
              array (
                0 => 'Rychlost aplikace změny.',
                1 => 'Velikost repozitáře.',
                2 => 'Kolik systémů a uživatelů změna ovlivní, když se pokazí.',
              ),
              'correct' => 2,
              'explanation' => 'Malý dosah = menší riziko.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zapiš, co je „požadovaný stav“ tvého pokoje nebo stolu a jaké odchylky obvykle vznikají.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Změny aplikujeme jen v labu; produkční konfigurace školy se nemění.',
          1 => 'Do repozitáře nepatří hesla ani klíče – jen odkazy na bezpečné úložiště.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „IaC = skript, který jednou spustím“. Zdůrazni opakovatelnost a review.',
          1 => 'Otázka do třídy: Kdo a kdy změnil server mimo repozitář?',
          2 => 'Tempo: lab Drift Detection je klíčový – úkol 3 může být kratší.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: vytištěný plán změn a tabulka požadovaného/skutečného stavu, úkoly 2–4 na papír.',
          1 => 'Plán B offline: porovnání dvou vytištěných konfigurací.',
        ),
        'glossary' => 
        array (
          0 => 'iac',
          1 => 'drift',
          2 => 'blast-radius',
          3 => 'source-of-truth',
        ),
        '_file' => 'lesson_content_v72_4a_a.php',
      ),
      9 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 9 · Výkon a plánování kapacity',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím najít úzké hrdlo z latence, propustnosti (throughput) a vytížení a převést měření na plán kapacity s rezervou.',
          'success_criteria' => 
          array (
            0 => 'Porovnám p50 a p95 a vysvětlím, co znamená „dlouhý ocas“ latence.',
            1 => 'Najdu zdroj, jehož vytížení roste spolu s latencí.',
            2 => 'Spočítám potřebnou kapacitu s rezervou (cíl × (1 + rezerva)).',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže průměrnou odezvu 200 ms a stížnosti uživatelů.',
            'student' => 'Odhadnou, proč průměr klame.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Výchozí měření',
            'teacher' => 'Předvede Latency & Saturation Lab.',
            'student' => 'Zapíší p50, p95 a chybovost.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Percentily',
            'teacher' => 'Vysvětlí percentily na příkladu 20 požadavků.',
            'student' => 'Spočítají p50 a p95 z malého vzorku.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Úzké hrdlo',
            'teacher' => 'Ukáže grafy CPU, I/O a fronty.',
            'student' => 'Najdou korelaci s latencí a napíšou hypotézu.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Kapacita a rezerva',
            'teacher' => 'Předvede Capacity & Headroom Lab.',
            'student' => 'Najdou bod degradace a spočítají potřebnou kapacitu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Rozhodnutí a exit ticket',
            'teacher' => 'Porovná škálování nahoru, do šířky a optimalizaci.',
            'student' => 'Rozhodnou pro svou situaci a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'V labu zapiš výchozí p50, p95 a chybovost a jeden signál vytížení zdroje.',
            'output' => 'Tabulka výchozího měření.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Z 20 seřazených doby odezvy urči p50 (10.–11. hodnota) a p95 (19. hodnota) a vysvětli rozdíl.',
            'output' => 'p50, p95 a věta o dlouhém ocasu (tail latency).',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Najdi zdroj (CPU, disk, fronta), jehož vytížení roste spolu s latencí, a napiš hypotézu úzkého hrdla.',
            'output' => 'Hypotéza + důkaz z grafu.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Spočítej potřebnou kapacitu pro cíl 800 požadavků/s s rezervou (headroom) 30 % a porovnej s bodem degradace z labu.',
            'output' => '800 × 1,3 = 1040 požadavků/s + rozhodnutí, zda stačí současný stav.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane seřazený vzorek s vyznačenými pozicemi percentilů a vzorec kapacity.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Navrhne test výpadku jednoho z N serverů a spočítá, zda zbylé servery cíl unesou.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Tabule: p95 z deseti hodnot – kdo to zvládne?',
            1 => 'Hypotéza nahlas: co ji vyvrátí?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Percentily',
              'levels' => 
              array (
                0 => 'Jen průměr.',
                1 => 'Spočítá s chybou.',
                2 => 'Správně p50 a p95 a význam.',
                3 => 'Vysvětlí dopad dlouhého ocasu na uživatele.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Úzké hrdlo',
              'levels' => 
              array (
                0 => 'Hádá.',
                1 => 'Ukáže graf bez vztahu.',
                2 => 'Hypotéza s korelací.',
                3 => 'Navrhne test, který hypotézu ověří.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Kapacita',
              'levels' => 
              array (
                0 => 'Bez výpočtu.',
                1 => 'Bez rezervy.',
                2 => 'Cíl × (1 + rezerva) a porovnání.',
                3 => 'Zahrne i výpadek jednoho serveru.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Výkon a kapacita',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč sledujeme p95 latence, a ne jen průměr?',
              'options' => 
              array (
                0 => 'Průměr schová pomalé požadavky, které část uživatelů opravdu zažívá.',
                1 => 'p95 se počítá rychleji.',
                2 => 'Průměr je vždy vyšší než p95.',
              ),
              'correct' => 0,
              'explanation' => 'Dlouhý ocas = reálná bolest části uživatelů.',
            ),
            1 => 
            array (
              'question' => 'Cíl je 600 požadavků/s a chceš rezervu 50 %. Jakou kapacitu potřebuješ?',
              'options' => 
              array (
                0 => '650 požadavků/s',
                1 => '900 požadavků/s',
                2 => '1200 požadavků/s',
              ),
              'correct' => 1,
              'explanation' => '600 × 1,5 = 900.',
            ),
            2 => 
            array (
              'question' => 'Latence roste přesně ve chvílích, kdy roste fronta zápisů na disk. Co je nejlepší hypotéza?',
              'options' => 
              array (
                0 => 'Pomalé DNS.',
                1 => 'Málo paměti v prohlížeči klienta.',
                2 => 'Úzkým hrdlem je disk (I/O).',
              ),
              'correct' => 2,
              'explanation' => 'Korelace vytížení a latence ukazuje na hrdlo.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: změř stopkami 10× dobu načtení jedné stránky, seřaď hodnoty a urči medián.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zátěžové testy jen v labu – nikdy proti školním nebo cizím serverům.',
          1 => 'Měření nesbírá osobní údaje uživatelů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: rezerva se přičítá k současné, ne k cílové zátěži.',
          1 => 'Otázka do třídy: Co zažívá těch 5 % nejpomalejších požadavků?',
          2 => 'Tempo: výpočet percentilů ve dvojicích drž na 15 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2 a 4 na papír (výsledky v zadání), labové úkoly podle návodu.',
          1 => 'Plán B offline: vzorek časů na kartičkách, ruční seřazení.',
        ),
        'glossary' => 
        array (
          0 => 'p95',
          1 => 'tail-latency',
          2 => 'headroom',
          3 => 'throughput',
        ),
        '_file' => 'lesson_content_v72_4a_a.php',
      ),
      10 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 10 · systemd do hloubky: závislosti, restart a selhání',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím rozebrat soubor jednotky služby, odlišit pořadí startu od závislosti a bezpečně řešit opakované selhání bez smyčky restartů.',
          'success_criteria' => 
          array (
            0 => 'V jednotce najdu sekce [Unit], [Service], [Install] a klíče ExecStart, User a Restart.',
            1 => 'Vysvětlím rozdíl After= (pořadí) a Requires= / Wants= (závislost).',
            2 => 'Navrhnu minimální úpravu (drop-in) s návratem a ověřením po daemon-reload.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže službu, která padá a systemd ji každou sekundu restartuje.',
            'student' => 'Odhadnou, proč restart nepomáhá.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Anatomie jednotky',
            'teacher' => 'Rozebere ukázkovou jednotku (systemd.unit, systemd.service).',
            'student' => 'Najdou sekce a klíčové řádky.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Závislosti',
            'teacher' => 'Vysvětlí After=, Wants= a Requires=.',
            'student' => 'Nakreslí řetěz závislostí webové služby.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 57,
            'phase' => 'Smyčka restartů (restart loop)',
            'teacher' => 'Předvede čtení statusu a logu v Linux Labu.',
            'student' => 'Z logu zjistí, proč proces padá.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 78,
            'phase' => 'Minimální úprava',
            'teacher' => 'Ukáže drop-in místo kopie celé jednotky.',
            'student' => 'Navrhnou drop-in s návratem.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Ověření a exit ticket',
            'teacher' => 'Shrne: daemon-reload → status → log → kontrola zdraví.',
            'student' => 'Zapíší postup ověření a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'V Linux Labu zjisti ze systemctl status nginx, kde leží soubor jednotky a zda je služba povolená po startu.',
            'output' => '„Loaded: loaded (/lib/systemd/system/nginx.service; enabled …)“.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'Loaded: loaded (/lib/systemd/system/nginx.service; enabled',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'V ukázkové jednotce označ sekce [Unit], [Service], [Install] a klíče ExecStart, User a Restart a vysvětli je.',
            'output' => 'Popsaná jednotka.',
            'time' => '13 min',
          ),
          2 => 
          array (
            'text' => 'Nakresli řetěz závislostí webové služby a u každé vazby rozliš, zda jde o pořadí (After=), nebo závislost (Wants= / Requires=).',
            'output' => 'Diagram závislostí.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni minimální úpravu jako drop-in (např. Restart=on-failure s omezením počtu pokusů), postup návratu a ověření po systemctl daemon-reload.',
            'output' => 'Drop-in, návrat a 3 ověřovací kroky.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane ukázkovou jednotku s barevně odlišenými sekcemi a slovníček klíčů.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Vysvětlí, k čemu slouží StartLimitBurst a StartLimitIntervalSec a jak brání nekonečné smyčce.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž sekci: učitel řekne klíč, třída řekne sekci.',
            1 => 'Pořadí, nebo závislost? (situace na tabuli)',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Anatomie jednotky',
              'levels' => 
              array (
                0 => 'Nezná sekce.',
                1 => 'Zná sekce, ne klíče.',
                2 => 'Sekce i klíče s významem.',
                3 => 'Najde chybu v ukázkové jednotce.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Závislosti',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Plete After a Requires.',
                2 => 'Správně rozliší pořadí a závislost.',
                3 => 'Diagram celého řetězu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Bezpečná úprava',
              'levels' => 
              array (
                0 => 'Kopie celé jednotky.',
                1 => 'Drop-in bez návratu.',
                2 => 'Drop-in + návrat + ověření.',
                3 => 'Limity restartů proti smyčce.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Linux: služby a systemd',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jednotka má jen „After=network-online.target“. Co to zajistí?',
              'options' => 
              array (
                0 => 'Že síťový cíl se spustí vždy, i když ho nic nevyžaduje.',
                1 => 'Pořadí: služba se spustí až po něm, pokud se spouští také.',
                2 => 'Že se služba restartuje při výpadku sítě.',
              ),
              'correct' => 1,
              'explanation' => 'After= určuje pořadí, ne závislost.',
            ),
            1 => 
            array (
              'question' => 'Proč dělat úpravu jako drop-in, a ne kopii celé jednotky?',
              'options' => 
              array (
                0 => 'Drop-in mění jen potřebné řádky a aktualizace balíčku původní jednotku dál opravuje.',
                1 => 'Kopie je zakázaná.',
                2 => 'Drop-in nemusí projít daemon-reload.',
              ),
              'correct' => 0,
              'explanation' => 'Minimální změna = menší riziko a jednodušší návrat.',
            ),
            2 => 
            array (
              'question' => 'Služba padá kvůli chybě v konfiguraci. Pomůže zvýšit četnost restartů?',
              'options' => 
              array (
                0 => 'Ano, služba se časem chytí.',
                1 => 'Ano, když se restartuje každou sekundu.',
                2 => 'Ne – nejdřív je potřeba z logu najít a opravit příčinu.',
              ),
              'correct' => 2,
              'explanation' => 'Restart neopraví chybnou konfiguraci.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v manuálové stránce systemd.service (online dokumentace systemd) najdi popis Restart= a zapiš tři možné hodnoty.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Jednotky upravujeme jen v labu nebo testovacím virtuálním počítači.',
          1 => 'Na serveru vždy nejdřív záloha jednotky a plán návratu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Používej testovací jednotku; důraz na důkaz před restartem.',
          1 => 'Otázka do třídy: Je tohle pořadí, nebo závislost?',
          2 => 'Tempo: simulátor neumí systemctl cat – ukázkovou jednotku rozdej vytištěnou.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkol 1 v Linux Labu (výstup v zadání), úkoly 2–4 na vytištěné jednotce.',
          1 => 'Plán B offline: vytištěná jednotka a diagram závislostí na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'systemd-unit',
          1 => 'drop-in',
          2 => 'restart-loop',
        ),
        '_file' => 'lesson_content_v72_4a_a.php',
      ),
      11 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 11 · Úložiště: disk, souborový systém, připojení a plný disk',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím rozlišit zařízení, oddíl, souborový systém a bod připojení a bezpečně diagnostikovat „plný disk“ včetně vyčerpaných inodů.',
          'success_criteria' => 
          array (
            0 => 'Nakreslím vrstvy zařízení → oddíl → souborový systém → bod připojení podle lsblk a df.',
            1 => 'Nejdřív zjistím, který souborový systém je plný (df), teprve pak hledám velké adresáře (du).',
            2 => 'Vysvětlím, proč může disk s volnými GB odmítnout nový soubor.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše incident: web hlásí „No space left on device“.',
            'student' => 'Navrhnou první tři kroky.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Vrstvy úložiště',
            'teacher' => 'Předvede lsblk a df -h v Linux Labu.',
            'student' => 'Nakreslí vrstvy úložiště lab-pc.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'df, pak du',
            'teacher' => 'Ukáže pořadí: který souborový systém → který adresář.',
            'student' => 'Najdou zaplnění kořenového systému a velikost /var/log.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Růst logů a inody',
            'teacher' => 'Vysvětlí inody na připraveném výpisu df -i ze skutečného serveru.',
            'student' => 'Rozhodnou, zda jde o kapacitu, nebo inody.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 75,
            'phase' => 'Bezpečná náprava',
            'teacher' => 'Zdůrazní: aktivní log nemazat naslepo.',
            'student' => 'Sepíšou postup nápravy v 5 krocích.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Prevence a exit ticket',
            'teacher' => 'Zadá monitoring kapacity a retenci.',
            'student' => 'Navrhnou práh a akci a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spusť lsblk a df -h a nakresli vrstvy: disk sda → oddíl sda1 → souborový systém → bod připojení /.',
            'output' => 'sda1 20G připojený jako /, zaplněno 31 %.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'lsblk',
                'expect' => 'sda1',
              ),
              1 => 
              array (
                'cmd' => 'df -h',
                'expect' => '31% /',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zjisti velikost adresáře logů příkazem du -sh /var/log a vysvětli, proč se du spouští až po df.',
            'output' => '28K /var/log + zdůvodnění pořadí.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'du -sh /var/log',
                'expect' => '/var/log',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Na připraveném výpisu df -i (sloupec IUse% 100 %) rozhodni, zda chybí místo, nebo inody (mount point /var/spool).',
            'output' => 'Vyčerpané inody – příliš mnoho malých souborů.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Sepiš bezpečný postup nápravy plného disku v 5 krocích a navrhni monitoring kapacity (práh varování 80 %, kritický 90 %) a retenci logů (retention).',
            'output' => 'Postup + práh + retence.',
            'time' => '30 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane diagram vrstev k doplnění a vzorový výpis df s vyznačeným řádkem.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Vysvětlí, proč smazání aktivního logu nemusí uvolnit místo, dokud ho služba drží otevřený.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž vrstvu: učitel řekne „sda1“, třída řekne, co to je.',
            1 => 'Kapacita, nebo inody? (dva výpisy na tabuli)',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Vrstvy úložiště',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Plete oddíl a bod připojení.',
                2 => 'Správný diagram vrstev.',
                3 => 'Propojí s konfigurací připojení po restartu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Diagnostika',
              'levels' => 
              array (
                0 => 'Maže naslepo.',
                1 => 'du bez df.',
                2 => 'df → du, kapacita vs. inody.',
                3 => 'Vysvětlí otevřený smazaný soubor.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Prevence',
              'levels' => 
              array (
                0 => 'Žádná.',
                1 => 'Práh bez akce.',
                2 => 'Práh, akce a retence.',
                3 => 'Zdůvodní hodnoty podle růstu dat.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Úložiště a zálohy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který příkaz použiješ jako první při hlášce „No space left on device“?',
              'options' => 
              array (
                0 => 'du -sh /*',
                1 => 'df -h',
                2 => 'rm -rf /var/log/*',
              ),
              'correct' => 1,
              'explanation' => 'df ukáže, který souborový systém je plný.',
            ),
            1 => 
            array (
              'question' => 'df -h ukazuje volných 14G, ale nový soubor nejde vytvořit. Co ověříš?',
              'options' => 
              array (
                0 => 'Zda nejsou vyčerpané inody (df -i).',
                1 => 'DNS server.',
                2 => 'Rychlost sítě.',
              ),
              'correct' => 0,
              'explanation' => 'Každý soubor potřebuje inode.',
            ),
            2 => 
            array (
              'question' => 'Co je bod připojení (mount point)?',
              'options' => 
              array (
                0 => 'Název disku.',
                1 => 'Velikost oddílu.',
                2 => 'Adresář, ve kterém je souborový systém zpřístupněný.',
              ),
              'correct' => 2,
              'explanation' => 'Např. sda1 je připojený jako /.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v nastavení telefonu nebo počítače zjisti, kolik místa zabírají fotky a kolik aplikace.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Nikdy nemažeme skutečné systémové logy; pracujeme v simulátoru a s připravenými výpisy.',
          1 => 'Příkaz rm -rf nad systémovými cestami je zakázaný i v labu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Simulátor neumí df -i (ukazuje bloky) – inody vysvětli na připraveném výpisu.',
          1 => 'Otázka do třídy: Který souborový systém je plný, a víme to jistě?',
          2 => 'Tempo: postup nápravy je hlavní výstup – nech na něj 30 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na vytištěném výpisu.',
          1 => 'Plán B offline: vytištěné výpisy lsblk, df a df -i.',
        ),
        'glossary' => 
        array (
          0 => 'inode',
          1 => 'mount-point',
          2 => 'retention',
        ),
        '_file' => 'lesson_content_v72_4a_b.php',
      ),
      12 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 12 · Strategie záloh: RPO, RTO a nácvik obnovy',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout zálohování podle požadované ztráty dat (RPO) a doby obnovy (RTO) a doložit obnovitelnost testovací obnovou.',
          'success_criteria' => 
          array (
            0 => 'Vysvětlím RPO a RTO a odvodím z nich četnost záloh.',
            1 => 'Sestavím tabulku záloh (data, konfigurace, tajné údaje odděleně) s retencí.',
            2 => 'Popíšu obnovu do testovacího cíle s kontrolou integrity a funkčnosti.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Zeptá se: kolik práce bys snesl ztratit – den, hodinu, minutu?',
            'student' => 'Odpoví pro školní systém známek.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'RPO a RTO',
            'teacher' => 'Vysvětlí oba pojmy na příkladu.',
            'student' => 'Určí RPO a RTO pro tři systémy.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Co zálohovat',
            'teacher' => 'Ukáže rozdělení data / konfigurace / tajné údaje.',
            'student' => 'Sestaví tabulku záloh s retencí.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 62,
            'phase' => 'Nácvik obnovy',
            'teacher' => 'Zdůrazní testovací cíl, nikdy produkce.',
            'student' => 'Projdou obnovu v labu a zapíší čas.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 78,
            'phase' => 'Integrita a funkčnost',
            'teacher' => 'Ukáže kontrolní součet a aplikační test.',
            'student' => 'Ověří obnovená data a funkčnost.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Runbook a exit ticket',
            'teacher' => 'Zadá kroky, odpovědnosti a podmínky zastavení.',
            'student' => 'Sepíšou runbook obnovy a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Urči RPO a RTO pro tři systémy (známky, školní web, archiv fotek) a z RPO odvoď četnost záloh.',
            'output' => 'Tabulka RPO, RTO a četnosti.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Sestav tabulku záloh: co (data, konfigurace, tajné údaje), jak často, kam, jak dlouho držet (retence, retention).',
            'output' => 'Tabulka záloh.',
            'time' => '17 min',
          ),
          2 => 
          array (
            'text' => 'Proveď nácvik obnovy (restore drill) do testovacího cíle a změř čas obnovy proti RTO.',
            'output' => 'Záznam nácviku + naměřený čas.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Ověř integritu (kontrolní součet sha256sum) a funkčnost obnovené služby a sepiš runbook obnovy.',
            'output' => 'Výsledek kontroly + runbook.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane příklad RPO/RTO pro jeden systém a šablonu tabulky záloh.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Navrhne pravidlo 3-2-1 pro školu (3 kopie, 2 typy médií, 1 mimo budovu) a jeho rizika.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlý příklad: zálohujeme jednou denně – jaké je nejhorší RPO?',
            1 => 'Kontrola tabulky: jsou tajné údaje oddělené?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'RPO a RTO',
              'levels' => 
              array (
                0 => 'Nezná.',
                1 => 'Plete pojmy.',
                2 => 'Správně určí a odvodí četnost.',
                3 => 'Zdůvodní podle dopadu na školu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Tabulka záloh',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez retence.',
                2 => 'Data, konfigurace, tajné údaje + retence.',
                3 => 'Pravidlo 3-2-1 s riziky.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Obnova',
              'levels' => 
              array (
                0 => 'Jen „máme zálohu“.',
                1 => 'Obnova bez kontroly.',
                2 => 'Obnova, integrita, funkčnost.',
                3 => 'Runbook s podmínkami zastavení.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Úložiště a zálohy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Zálohuje se jednou denně o půlnoci. Jaké je nejhorší RPO?',
              'options' => 
              array (
                0 => 'Až 24 hodin dat.',
                1 => '1 hodina.',
                2 => '0 – nic se neztratí.',
              ),
              'correct' => 0,
              'explanation' => 'Výpadek těsně před půlnocí = ztráta celého dne.',
            ),
            1 => 
            array (
              'question' => 'Co vyjadřuje RTO?',
              'options' => 
              array (
                0 => 'Kolik dat smíme ztratit.',
                1 => 'Jak dlouho smí trvat obnova provozu.',
                2 => 'Kolik kopií zálohy máme.',
              ),
              'correct' => 1,
              'explanation' => 'RPO = data, RTO = čas.',
            ),
            2 => 
            array (
              'question' => 'Kam obnovujeme při nácviku?',
              'options' => 
              array (
                0 => 'Přímo na produkční server.',
                1 => 'Na počítač spolužáka.',
                2 => 'Do odděleného testovacího cíle.',
              ),
              'correct' => 2,
              'explanation' => 'Nácvik nesmí ohrozit produkci.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zjisti, zda a jak se zálohuje tvůj školní nebo osobní e-mail, a odhadni jeho RPO.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Obnovujeme jen vymyšlená data do testovacího cíle; produkční data školy se nikdy nepoužívají.',
          1 => 'Tajné údaje (hesla, klíče) se zálohují odděleně a šifrovaně – v labu je jen simulujeme.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Uč rozdíl „záloha doběhla“ vs. „podnik se obnovil“.',
          1 => 'Otázka do třídy: Kdy jsme naposledy zálohu opravdu obnovili?',
          2 => 'Tempo: výpočty RPO jsou rychlé – čas dej nácviku obnovy.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1, 2 a runbook na papír, nácvik podle návodu v labu.',
          1 => 'Plán B offline: nácvik obnovy jako papírový checklist s časovačem.',
        ),
        'glossary' => 
        array (
          0 => 'rpo',
          1 => 'rto',
          2 => 'restore',
          3 => 'retention',
        ),
        '_file' => 'lesson_content_v72_4a_b.php',
      ),
      13 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 13 · Zabezpečení služby: SSH, firewall, aktualizace a práva',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím zabezpečit (hardening) službu: zmenšit plochu útoku, nastavit bezpečné minimum SSH a firewallu a připravit cestu návratu, abych se nezamkl venku.',
          'success_criteria' => 
          array (
            0 => 'Sepíšu vystavené služby podle ss -tln a navrhnu, co omezit.',
            1 => 'Navrhnu SSH bez přihlášení roota, s klíči a omezenými účty.',
            2 => 'Před zpřísněním přístupu mám ověřený náhradní přístup a plán návratu.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše správce, který zpřísnil firewall a zamkl se venku (lockout).',
            'student' => 'Navrhnou, co měl udělat předem.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Plocha útoku',
            'teacher' => 'Předvede ss -tln v Linux Labu.',
            'student' => 'Sepíšou vystavené služby a kdo je potřebuje.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'SSH minimum',
            'teacher' => 'Ukáže sshd_config v simulátoru.',
            'student' => 'Navrhnou nastavení SSH (klíče, bez roota, omezení účtů).',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Rozsah firewallu',
            'teacher' => 'Vysvětlí správu jen ze zóny správy.',
            'student' => 'Navrhnou pravidla a náhradní přístup přes konzoli.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 70,
            'phase' => 'Plán aktualizací',
            'teacher' => 'Ukáže dopad aktualizace a potřebu restartu.',
            'student' => 'Sepíšou plán aktualizace s ověřením.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 70,
            'to' => 90,
            'phase' => 'Ověření zabezpečení a exit ticket',
            'teacher' => 'Zadá pozitivní a negativní testy a záznam změny.',
            'student' => 'Napíšou testy a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spusť ss -tln, sepiš vystavené služby a ke každé napiš, kdo ji opravdu potřebuje (plocha útoku, attack surface).',
            'output' => '0.0.0.0:22 (SSH – jen správa), 0.0.0.0:80 (web – všichni).',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:22',
              ),
              1 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Přečti sudo cat /etc/ssh/sshd_config a navrhni bezpečné minimum SSH (přihlášení klíčem, bez roota, omezení účtů).',
            'output' => 'Ověřené „PermitRootLogin no“ + 3 doporučení.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cat /etc/ssh/sshd_config',
                'expect' => 'PermitRootLogin no',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Navrhni pravidla firewallu: SSH jen ze zóny správy, web pro všechny; připiš náhradní přístup (konzole) a postup návratu.',
            'output' => 'Pravidla + náhradní přístup + návrat.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Sepiš plán aktualizace (dopad, restart, ověření) a pozitivní i negativní test zabezpečení se záznamem změny.',
            'output' => 'Plán aktualizace a 4 testy.',
            'time' => '30 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kontrolní seznam zabezpečení (služby, SSH, firewall, aktualizace, ověření).',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Navrhne, jak by změnu firewallu na vzdáleném serveru automaticky vrátil, pokud se do 5 minut nepotvrdí.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kdo službu potřebuje? (učitel čte řádky z ss)',
            1 => 'Kontrola plánu: kde je náhradní přístup?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Plocha útoku',
              'levels' => 
              array (
                0 => 'Neví, co běží.',
                1 => 'Seznam bez zdůvodnění.',
                2 => 'Služby a kdo je potřebuje.',
                3 => 'Navrhne konkrétní omezení.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'SSH a firewall',
              'levels' => 
              array (
                0 => 'Bez změn.',
                1 => 'Změny bez návratu.',
                2 => 'Bezpečné minimum + náhradní přístup.',
                3 => 'Automatický návrat při zamčení.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Ověření',
              'levels' => 
              array (
                0 => 'Bez testu.',
                1 => 'Jen pozitivní.',
                2 => 'Pozitivní i negativní + záznam.',
                3 => 'Testy ze správných zón.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Bezpečnost provozu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co musíš mít připravené před zpřísněním vzdáleného přístupu SSH?',
              'options' => 
              array (
                0 => 'Ověřený náhradní přístup (konzoli) a plán návratu.',
                1 => 'Nové pozadí plochy.',
                2 => 'Delší TTL v DNS.',
              ),
              'correct' => 0,
              'explanation' => 'Jinak se můžeš zamknout venku.',
            ),
            1 => 
            array (
              'question' => 'Co znamená „zmenšit plochu útoku“?',
              'options' => 
              array (
                0 => 'Zmenšit disk serveru.',
                1 => 'Snížit počet uživatelů webu.',
                2 => 'Omezit vystavené služby a cesty přístupu na nezbytné minimum.',
              ),
              'correct' => 2,
              'explanation' => 'Co neběží a není vystavené, nejde napadnout.',
            ),
            2 => 
            array (
              'question' => 'Proč se nastavuje PermitRootLogin no?',
              'options' => 
              array (
                0 => 'Root se tím smaže.',
                1 => 'Útočník nemůže zkoušet přihlášení přímo jako nejvyšší správce; správci se přihlásí svým účtem.',
                2 => 'Zrychlí to SSH.',
              ),
              'correct' => 1,
              'explanation' => 'Vlastní účty + sudo = dohledatelnost a menší riziko.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zkontroluj na svém telefonu, které aplikace mají přístup k poloze, a jednu, která ho nepotřebuje, omez.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Defenzivní lab: pracujeme jen na vlastních testovacích systémech, nikdy s cizími.',
          1 => 'Žádné skenování ani testování cizích serverů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Defenzivní lab; nepracovat s cizími systémy. Důraz na minimální rozsah a návrat.',
          1 => 'Otázka do třídy: Kdo všechno se teď dostane k portu 22?',
          2 => 'Tempo: plán aktualizace a testy jsou hlavní výstup – nech na ně 30 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.',
          1 => 'Plán B offline: kontrolní seznam zabezpečení na vytištěném výpisu ss.',
        ),
        'glossary' => 
        array (
          0 => 'attack-surface',
          1 => 'hardening',
          2 => 'lockout',
        ),
        '_file' => 'lesson_content_v72_4a_b.php',
      ),
      14 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 14 · Kontejnery: proces, obraz, svazek a síť',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vysvětlit kontejner jako izolovaný proces s obrazem, konfigurací, svazkem a mapováním portů a diagnostikovat jeho základní selhání.',
          'success_criteria' => 
          array (
            0 => 'Rozliším obraz (image) a kontejner a vím, co zmizí při znovuvytvoření.',
            1 => 'Odliším port hostitele a port v kontejneru a ověřím, na jaké adrese aplikace poslouchá.',
            2 => 'Určím, která data patří do svazku (volume), a vím, že tajné údaje nepatří do obrazu.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše: po aktualizaci kontejneru zmizela všechna nahraná data.',
            'student' => 'Odhadnou proč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Mentální model',
            'teacher' => 'Vysvětlí obraz ≠ kontejner, svazek ≠ vrstva obrazu.',
            'student' => 'Nakreslí diagram obraz → kontejner → svazek → síť.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Mapování portů',
            'teacher' => 'Ukáže zápis 8080:80 (hostitel:kontejner).',
            'student' => 'Rozeberou tři zápisy mapování.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Trvalá data',
            'teacher' => 'Ukáže, co přežije znovuvytvoření.',
            'student' => 'Rozhodnou, co patří do svazku a co do proměnných.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 75,
            'phase' => 'Zdraví a logy',
            'teacher' => 'Ukáže stav, kontrolu zdraví a log kontejneru (vytištěný výpis).',
            'student' => 'Najdou v logu příčinu selhání.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Incident a exit ticket',
            'teacher' => 'Zadá incident: port hostitele otevřený, aplikace poslouchá jinde.',
            'student' => 'Navrhnou nejmenší opravu a ověření; exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Nakresli diagram: obraz (image) → kontejner (container) → svazek (volume) → síť a mapování portů (port mapping).',
            'output' => 'Diagram se čtyřmi částmi.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'U zápisů 8080:80, 127.0.0.1:8080:80 a 443:8443 urči port hostitele, port v kontejneru a odkud je služba dostupná.',
            'output' => 'Tabulka tří mapování.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Rozhodni pro databázi, nahrané soubory, konfiguraci a heslo k databázi, kam patří (svazek, proměnná prostředí, tajné úložiště, obraz).',
            'output' => 'Tabulka rozhodnutí se zdůvodněním.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'V incidentu (hostitel 8080 otevřený, aplikace v kontejneru poslouchá jen na 127.0.0.1:3000) navrhni nejmenší opravu a ověření.',
            'output' => 'Oprava (poslouchat na 0.0.0.0:3000 a mapovat 8080:3000) + test.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane diagram k doplnění a tabulku „přežije znovuvytvoření? ano/ne“.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Vysvětlí, proč se orchestrace (více instancí) zavádí až po pochopení jedné instance.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Přežije, nebo zmizí? (učitel jmenuje data)',
            1 => 'Mapování na tabuli: odkud se k 127.0.0.1:8080:80 dostanu?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Model kontejneru',
              'levels' => 
              array (
                0 => 'Kontejner = virtuál.',
                1 => 'Plete obraz a kontejner.',
                2 => 'Obraz, kontejner, svazek, síť správně.',
                3 => 'Vysvětlí izolaci procesu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Porty a data',
              'levels' => 
              array (
                0 => 'Nerozliší porty.',
                1 => 'Porty ano, data ne.',
                2 => 'Porty i trvalá data správně.',
                3 => 'Tajné údaje mimo obraz se zdůvodněním.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Diagnostika',
              'levels' => 
              array (
                0 => 'Restartuje.',
                1 => 'Najde příčinu bez opravy.',
                2 => 'Nejmenší oprava + ověření.',
                3 => 'Navrhne kontrolu zdraví, která chybu odhalí.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Kontejnery',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co se typicky stane s daty uloženými jen uvnitř kontejneru po jeho znovuvytvoření?',
              'options' => 
              array (
                0 => 'Uloží se do obrazu.',
                1 => 'Ztratí se – trvalá data patří do svazku.',
                2 => 'Přesunou se do DNS.',
              ),
              'correct' => 1,
              'explanation' => 'Zapisovatelná vrstva kontejneru je dočasná.',
            ),
            1 => 
            array (
              'question' => 'Zápis mapování 8080:80 znamená:',
              'options' => 
              array (
                0 => 'Port 8080 na hostiteli vede na port 80 v kontejneru.',
                1 => 'Port 80 na hostiteli vede na 8080 v kontejneru.',
                2 => 'Kontejner má dva webové servery.',
              ),
              'correct' => 0,
              'explanation' => 'Pořadí je hostitel:kontejner.',
            ),
            2 => 
            array (
              'question' => 'Kam patří heslo k databázi?',
              'options' => 
              array (
                0 => 'Přímo do obrazu kontejneru.',
                1 => 'Do veřejného repozitáře.',
                2 => 'Do tajného úložiště nebo bezpečně předané konfigurace, ne do obrazu.',
              ),
              'correct' => 2,
              'explanation' => 'Obraz se sdílí – tajné údaje by unikly.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přečti úvodní stránku oficiální dokumentace Docker nebo Podman o svazcích (volumes) a zapiš jednu větu vlastními slovy.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Kontejnery spouštíme jen v labu (Docker/Podman) nebo čistě simulujeme; žádné obrazy z neověřených zdrojů.',
          1 => 'Tajné údaje nikdy do obrazu ani do repozitáře.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Lze realizovat v Dockeru/Podmanu nebo čistě simulovat; simulátor Linux Labu kontejnery nemá.',
          1 => 'Otázka do třídy: Co přežije, když kontejner smažu a vytvořím znovu?',
          2 => 'Tempo: nezaváděj orchestraci dřív, než žáci chápou jednu instanci.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: celá hodina jde na papíře (diagram, tabulky, incident).',
          1 => 'Plán B offline: karty „obraz / kontejner / svazek / síť“ a skládání diagramu.',
        ),
        'glossary' => 
        array (
          0 => 'container',
          1 => 'image',
          2 => 'volume',
          3 => 'port-mapping',
        ),
        '_file' => 'lesson_content_v72_4a_b.php',
      ),
      15 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 15 · Automatizace a jednotná konfigurace',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout idempotentní správcovský postup: zjistí aktuální stav, změní jen potřebné, při nesplněné podmínce bezpečně skončí a výsledek doloží.',
          'success_criteria' => 
          array (
            0 => 'Postup nejdřív zjistí aktuální stav a při splněném cíli nic nemění.',
            1 => 'Před změnou ověří podmínky (správný počítač, záloha) a jinak bezpečně skončí.',
            2 => 'Druhý průchod postupu proběhne bez změny – to doložím.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže skript, který při každém spuštění přidá stejný řádek do konfigurace.',
            'student' => 'Popíšou, co se stane po 10 spuštěních.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Idempotentní změna',
            'teacher' => 'Vysvětlí: zjistit stav → porovnat → změnit jen rozdíl.',
            'student' => 'Přepíšou postup tak, aby byl idempotentní.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Podmínky',
            'teacher' => 'Ukáže kontrolu počítače a zálohy před změnou.',
            'student' => 'Doplní podmínky a bezpečné ukončení.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Odchylka',
            'teacher' => 'Připomene drift z lekce 8.',
            'student' => 'Porovnají deklarovaný a skutečný stav.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 72,
            'phase' => 'Nanečisto',
            'teacher' => 'Ukáže výstup režimu nanečisto (dry-run).',
            'student' => 'Zkontrolují rozsah plánu před aplikací.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 72,
            'to' => 90,
            'phase' => 'Aplikace, ověření a exit ticket',
            'teacher' => 'Zadá důkaz druhého průchodu.',
            'student' => 'Popíšou ověření a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přepiš postup „přidej řádek do konfigurace“ tak, aby byl idempotentní (nejdřív zjistí, zda řádek existuje).',
            'output' => 'Pseudoskript se zjištěním stavu.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Doplň podmínky: správný počítač, existující záloha, kontrola konfigurace; při nesplnění bezpečně skonči s hláškou.',
            'output' => 'Pseudoskript s podmínkami.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Porovnej deklarovaný a skutečný stav (připravená tabulka) a urči zdroj pravdy.',
            'output' => 'Seznam odchylek + rozhodnutí.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Popiš výstup režimu nanečisto (dry-run), aplikaci, měření výsledku a důkaz, že druhý průchod nic nezmění.',
            'output' => 'Plán, ověření a záznam druhého průchodu.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kostru pseudoskriptu s komentáři „zjisti / porovnej / změň / ověř“.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Napíše skutečný krátký skript v bashi s grep -q a ověří ho v Linux Labu dvojím spuštěním.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Myšlenkový pokus: co udělá tvůj postup při druhém spuštění?',
            1 => 'Kontrola podmínek: co se stane na špatném počítači?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Idempotence',
              'levels' => 
              array (
                0 => 'Mění vždy.',
                1 => 'Zjistí stav, ale mění i tak.',
                2 => 'Mění jen rozdíl.',
                3 => 'Doloží druhý průchod bez změny.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Podmínky a bezpečné selhání',
              'levels' => 
              array (
                0 => 'Žádné.',
                1 => 'Podmínky bez ukončení.',
                2 => 'Podmínky + bezpečné ukončení s hláškou.',
                3 => 'Ověří i zálohu a návrat.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Nanečisto a ověření',
              'levels' => 
              array (
                0 => 'Rovnou aplikuje.',
                1 => 'Nanečisto bez kontroly rozsahu.',
                2 => 'Nanečisto, aplikace, měření.',
                3 => 'Funkční skript ověřený v labu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Automatizace',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co znamená, že je postup idempotentní?',
              'options' => 
              array (
                0 => 'Opakované spuštění vede ke stejnému výsledku a nic navíc nemění.',
                1 => 'Postup běží jen jednou za den.',
                2 => 'Postup se nedá zastavit.',
              ),
              'correct' => 0,
              'explanation' => 'Druhý průchod = žádná změna.',
            ),
            1 => 
            array (
              'question' => 'Který skript je pro produkční server nejrizikovější?',
              'options' => 
              array (
                0 => 'Skript s režimem nanečisto a kontrolou podmínek.',
                1 => 'Skript, který po změně ověří výsledek.',
                2 => 'Skript, který rychle a opakovaně mění konfiguraci bez kontrol.',
              ),
              'correct' => 2,
              'explanation' => 'Automat bez pojistek násobí chyby.',
            ),
            2 => 
            array (
              'question' => 'Skript zjistí, že běží na jiném počítači, než má. Co má udělat?',
              'options' => 
              array (
                0 => 'Pokračovat, změna je stejná.',
                1 => 'Bezpečně skončit s jasnou hláškou a nic nezměnit.',
                2 => 'Restartovat počítač.',
              ),
              'correct' => 1,
              'explanation' => 'Nesplněná podmínka = bezpečné ukončení.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš pseudoskript „ranní rutina“ tak, aby se při druhém spuštění nic neopakovalo (např. snídaně už snědená).',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zadání je defenzivní a probíhá v sandboxu (Linux Lab); skripty nespouštíme na školních serverech.',
          1 => 'Ve skriptech nejsou hesla – jen odkazy na bezpečné úložiště.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Hodnoť bezpečné selhání a idempotenci, ne délku skriptu.',
          1 => 'Otázka do třídy: Co udělá tvůj skript, když ho omylem spustíš dvakrát?',
          2 => 'Tempo: výzvu (skutečný skript) nabídni jen rychlejším.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: celá hodina na papíře (pseudoskripty a tabulky).',
          1 => 'Plán B offline: postup jako vývojový diagram na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'idempotence',
          1 => 'dry-run',
          2 => 'drift',
        ),
        '_file' => 'lesson_content_v72_4a_b.php',
      ),
      16 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 16 · Pokročilá diagnostika sítě: pakety a stav spojení',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím spojit záznam provozu, stavy TCP a naslouchání na serveru do jedné hypotézy incidentu a jasně říct, co už vím a co ještě ne.',
          'success_criteria' => 
          array (
            0 => 'Rozliším vzorce SYN bez odpovědi, SYN → RST a SYN → SYN-ACK.',
            1 => 'Porovnám důkaz z provozu s naslouchajícími sockety na serveru (ss).',
            2 => 'Napíšu hranici příčiny: co dokazuji a jaký test přijde dál.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Popíše incident: aplikace se občas nepřipojí k API.',
            'student' => 'Formulují první hypotézu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Záznam s otázkou',
            'teacher' => 'Ukáže, jak hypotéza určuje filtr.',
            'student' => 'Navrhnou filtr jen na potřebný provoz.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Vzorce SYN',
            'teacher' => 'Ukáže tři vzorce na připraveném záznamu.',
            'student' => 'V simulátoru vyzkouší nc na tři cíle a přiřadí vzorce.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Socket na serveru',
            'teacher' => 'Předvede ss -tln.',
            'student' => 'Porovnají naslouchání s výsledky testů.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 72,
            'phase' => 'Po TCP',
            'teacher' => 'Zdůrazní: když TCP funguje, pokračuj TLS/HTTP.',
            'student' => 'Navrhnou další vrstvu testů podle příznaku.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 72,
            'to' => 90,
            'phase' => 'Časová osa a exit ticket',
            'teacher' => 'Zadá hranici příčiny.',
            'student' => 'Seřadí důkazy podle času, napíšou hranici a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'K incidentu napiš hypotézu a z ní odvoď filtr záznamu provozu (display filter) jen na potřebný port a cíl.',
            'output' => 'Hypotéza + filtr (např. tcp.port == 8443 and ip.addr == 10.0.0.10).',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'V Linux Labu vyzkoušej nc -zv 10.0.0.10 80, nc -zv 10.0.0.10 443 a nc -zv 10.0.0.99 443 a přiřaď vzorce SYN-ACK, RST a „bez odpovědi“.',
            'output' => 'succeeded = SYN-ACK; Connection refused = RST; Connection timed out = bez odpovědi.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 80',
                'expect' => 'succeeded',
              ),
              1 => 
              array (
                'cmd' => 'nc -zv 10.0.0.10 443',
                'expect' => 'Connection refused',
              ),
              2 => 
              array (
                'cmd' => 'nc -zv 10.0.0.99 443',
                'expect' => 'Connection timed out',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Porovnej výpis ss -tln s výsledky testů a vysvětli, proč port 443 vrací RST.',
            'output' => 'Na lab-pc poslouchají jen 22 a 80; na 443 nikdo nenaslouchá → RST.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Seřaď důkazy z provozu a serveru podle času a napiš hranici příčiny: co víš, co nevíš a jaký test přijde dál.',
            'output' => 'Časová osa + hranice příčiny + další test.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu tří vzorců SYN s ilustrací a tabulku výsledků nc.',
          'standard' => 'Úkoly 1–4 podle zadání.',
          'challenge' => 'Popíše, jak by v záznamu poznal ztrátu paketů (opakované SYN) a jak ji odlišit od filtrování.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Vzorec na tabuli: SYN → RST – kdo odpověděl?',
            1 => 'Hranice příčiny: co tvůj důkaz nevylučuje?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Vzorce TCP',
              'levels' => 
              array (
                0 => 'Nerozliší.',
                1 => 'Rozliší jen úspěch.',
                2 => 'Tři vzorce správně.',
                3 => 'Pozná i opakované SYN (ztráta).',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Propojení se serverem',
              'levels' => 
              array (
                0 => 'Bez ss.',
                1 => 'ss bez vztahu k testům.',
                2 => 'Naslouchání vysvětlí výsledky.',
                3 => 'Navrhne test navázání na adresu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Hranice příčiny',
              'levels' => 
              array (
                0 => 'Tvrdí bez důkazu.',
                1 => 'Jen co ví.',
                2 => 'Co ví, co neví, další test.',
                3 => 'Časová osa z více zdrojů.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Diagnostika sítě',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Klient posílá SYN a nepřichází žádná odpověď. Co to nejspíš znamená?',
              'options' => 
              array (
                0 => 'Port je otevřený.',
                1 => 'Cíl je nedostupný nebo provoz zahazuje filtr cestou.',
                2 => 'Server spojení aktivně odmítl.',
              ),
              'correct' => 1,
              'explanation' => 'Ticho ≠ odmítnutí.',
            ),
            1 => 
            array (
              'question' => 'TCP spojení se naváže (SYN-ACK), ale aplikace hlásí chybu. Kam pokračuješ?',
              'options' => 
              array (
                0 => 'Do vyšších vrstev: TLS a HTTP podle příznaku.',
                1 => 'Zpět k DHCP.',
                2 => 'K výměně síťové karty.',
              ),
              'correct' => 0,
              'explanation' => 'Vrstva TCP je ověřená – pokračuj nahoru.',
            ),
            2 => 
            array (
              'question' => 'Co je „hranice příčiny“ v záznamu incidentu?',
              'options' => 
              array (
                0 => 'Seznam viníků.',
                1 => 'Čas konce směny.',
                2 => 'Jasné oddělení toho, co důkazy dokazují, od toho, co je ještě potřeba ověřit.',
              ),
              'correct' => 2,
              'explanation' => 'Chrání před ukvapeným závěrem.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: nakresli časovou osu jednoho TCP spojení (SYN, SYN-ACK, ACK, data, FIN) a popiš každý krok jednou větou.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Používáme předpřipravené záznamy nebo izolovaný lab; nezachytáváme citlivý provoz třetích osob.',
          1 => 'Záznamy provozu se nesdílejí mimo třídu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Používej předpřipravené záznamy nebo izolovaný lab; nezachytávej citlivý provoz třetích osob.',
          1 => 'Otázka do třídy: Kdo poslal RST – a co to říká o síti?',
          2 => 'Tempo: simulátor nemá záchyt paketů – vzorce ukazuj na připraveném záznamu, testy dělej přes nc.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.',
          1 => 'Plán B offline: vytištěný záznam provozu a karty vzorců.',
        ),
        'glossary' => 
        array (
          0 => 'syn-ack',
          1 => 'rst',
          2 => 'display-filter',
        ),
        '_file' => 'lesson_content_v72_4a_b.php',
      ),
      17 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 17 · Reakce na incident: runbook, komunikace a rozbor bez viníka',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vést incident podle runbooku – určit závažnost, rozdělit role, pravidelně informovat a po vyřešení sepsat rozbor (postmortem), který hledá příčiny v systému, ne viníka.',
          'success_criteria' => 
          array (
            0 => 'Incident zařadím do stupně závažnosti podle dopadu na uživatele.',
            1 => 'Stavová zpráva obsahuje dopad, co děláme a čas další zprávy.',
            2 => 'Rozbor má časovou osu, příčinu, co pomohlo, co ne a měřitelná opatření s vlastníkem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_incident',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: dva incidenty',
            'teacher' => 'Porovná incident, kde všichni opravovali najednou, s incidentem vedeným podle runbooku.',
            'student' => 'Zapíší, co v prvním chybělo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Závažnost a role',
            'teacher' => 'Představí stupně závažnosti a role (vedoucí, technik, komunikace, zapisovatel).',
            'student' => 'Zařadí čtyři scénáře do stupňů.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 50,
            'phase' => 'Cvičný incident',
            'teacher' => 'V simulátoru zastaví webovou službu a hraje uživatele.',
            'student' => 'Ve čtveřici vedou incident podle runbooku.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 62,
            'phase' => 'Komunikace',
            'teacher' => 'Ukáže vzor stavové zprávy.',
            'student' => 'Napíší dvě stavové zprávy.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 82,
            'phase' => 'Rozbor bez viníka',
            'teacher' => 'Vede rozbor otázkami „proč systém dovolil…“.',
            'student' => 'Sepíší rozbor s opatřeními.',
            'form' => 've skupinách',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne cyklus incidentu.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zařaď čtyři scénáře (nefunguje celý web, pomalý export, překlep v patičce, výpadek přihlášení pro polovinu uživatelů) do stupňů závažnosti a zdůvodni podle dopadu.',
            'output' => 'Tabulka scénář → stupeň → dopad.',
            'time' => '13 min',
          ),
          1 => 
          array (
            'text' => 'Jako technik v cvičném incidentu zjisti stav webové služby a záznam o jejím zastavení v logu.',
            'output' => 'is-active vrací inactive, journal obsahuje Stopped nginx.service.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx',
                'expect' => 'inactive',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; journalctl -u nginx -n 5',
                'expect' => 'Stopped nginx.service',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Obnov službu a ověř ji z pohledu uživatele; výsledek zapiš jako závěrečnou stavovou zprávu.',
            'output' => 'curl -I vrací 200 OK a zpráva „vyřešeno, ověřeno v …, sledujeme do …“.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; curl -I http://localhost',
                'expect' => '200 OK',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Sepište rozbor incidentu: časová osa, dopad, příčina, co pomohlo, co ne a tři opatření s vlastníkem a termínem – bez jmen viníků.',
            'output' => 'Rozbor na jednu stranu se třemi měřitelnými opatřeními.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu stavové zprávy a rozboru s otázkami.',
          'standard' => 'Závažnost, cvičný incident, komunikace a rozbor.',
          'challenge' => 'Navrhne metriku (např. doba do zjištění, doba do obnovy), kterou bude tým sledovat po opatřeních.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte scénář, skupiny ukážou stupeň závažnosti.',
            1 => 'Kontrola rozboru: najdi v něm větu, která hledá viníka, a přepiš ji.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Závažnost a role',
              'levels' => 
              array (
                0 => 'Bez rozlišení.',
                1 => 'Stupně bez zdůvodnění.',
                2 => 'Stupně podle dopadu a role dodržené.',
                3 => 'Eskalace podle pravidel.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Komunikace',
              'levels' => 
              array (
                0 => 'Žádná.',
                1 => 'Nepravidelná.',
                2 => 'Dopad, postup a čas další zprávy.',
                3 => 'Srozumitelná i pro vedení.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rozbor',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Hledá viníka.',
                2 => 'Časová osa, příčina, opatření s vlastníkem.',
                3 => 'Opatření měřitelná a ověřitelná.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_incident',
          'competence_label' => 'Řízení incidentu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Podle čeho určíš závažnost incidentu?',
              'options' => 
              array (
                0 => 'Podle toho, jak dlouho oprava potrvá.',
                1 => 'Podle dopadu na uživatele a služby.',
                2 => 'Podle toho, kdo incident nahlásil.',
              ),
              'correct' => 1,
              'explanation' => 'Závažnost = dopad, ne obtížnost opravy.',
            ),
            1 => 
            array (
              'question' => 'Co je cílem rozboru incidentu bez hledání viníka?',
              'options' => 
              array (
                0 => 'Najít, co v systému a procesech chybu umožnilo, a zabránit opakování.',
                1 => 'Najít, kdo má dostat trest.',
                2 => 'Rychle incident zapomenout.',
              ),
              'correct' => 0,
              'explanation' => 'Lidé chybují; systém má chybám bránit.',
            ),
            2 => 
            array (
              'question' => 'Které opatření z rozboru je nejlepší?',
              'options' => 
              array (
                0 => 'Dávat větší pozor.',
                1 => 'Lépe komunikovat.',
                2 => 'Do pátku přidat kontrolu dostupnosti webu s upozorněním (vlastník: správce webu).',
              ),
              'correct' => 2,
              'explanation' => 'Konkrétní, s vlastníkem a termínem.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přepiš jedno opatření z rozboru tak, aby mělo vlastníka, termín a způsob ověření.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Incident se odehrává jen v simulátoru; žádné skutečné služby se nezastavují.',
          1 => 'Rozbor neobsahuje jména ani hodnocení lidí; zápisy zůstávají ve třídě.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: všichni ve skupině opravují a nikdo nekomunikuje – trvej na rolích.',
          1 => 'Otázka do třídy: Které opatření by tento incident zkrátilo nejvíc?',
          2 => 'Tempo: cvičný incident drž na 25 minut; rozbor je výstup hodiny.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: skupiny plní úkoly 1–4, simulátor podle zadání (výstupy jsou v zadání).',
          1 => 'Plán B offline: incident podle vytištěných výpisů, role a rozbor na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'postmortem',
          1 => 'runbook',
        ),
        '_file' => 'lesson_content_v76_4a_a.php',
      ),
      18 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 18 · Závěrečné cvičení spolehlivosti: služba, data a přístup',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím v jednom cvičení ověřit spolehlivost serveru ve čtyřech oblastech – služba, místo na disku, záloha s obnovou a zabezpečený přístup – a každou oblast doložit důkazem.',
          'success_criteria' => 
          array (
            0 => 'U každé oblasti mám příkaz, výstup a závěr „v pořádku / riziko“.',
            1 => 'Zálohu ověřím skutečnou obnovou, ne jen existencí archivu.',
            2 => 'Výsledky shrnu do tabulky rizik s návrhem opatření.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_services',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'ops_backup',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Zadání cvičení',
            'teacher' => 'Představí čtyři oblasti a hodnoticí tabulku.',
            'student' => 'Rozdělí si oblasti ve dvojici.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Služba',
            'teacher' => 'Připomene kontrolu konfigurace a stavu.',
            'student' => 'Ověří konfiguraci a stav webové služby.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 38,
            'phase' => 'Místo na disku',
            'teacher' => 'Připomene df a du.',
            'student' => 'Ověří zaplnění a největší adresáře.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 58,
            'phase' => 'Záloha a obnova',
            'teacher' => 'Hlídá, aby obnova proběhla do jiného místa a byla ověřená.',
            'student' => 'Vytvoří zálohu, obnoví ji a porovnají obsah.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 72,
            'phase' => 'Přístup',
            'teacher' => 'Připomene zpevnění SSH.',
            'student' => 'Ověří nastavení SSH a naslouchající porty.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 72,
            'to' => 90,
            'phase' => 'Tabulka rizik a exit ticket',
            'teacher' => 'Ukáže tabulku oblast – důkaz – riziko – opatření.',
            'student' => 'Vyplní tabulku a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Ověř konfiguraci webového serveru a jeho stav; obojí doložit výstupem.',
            'output' => 'nginx -t: syntax is ok; služba active (running).',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo nginx -t',
                'expect' => 'test is successful',
              ),
              1 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'active (running)',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zjisti zaplnění kořenového oddílu a velikost logů v /var/log.',
            'output' => '/dev/sda1 zaplněn na 31 %, logy v řádu kB.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'df -h',
                'expect' => '31%',
              ),
              1 => 
              array (
                'cmd' => 'du -sh /var/log/*',
                'expect' => '/var/log/nginx',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Vytvoř zálohu adresáře web, adresář smaž, obnov ho z archivu a ověř obsah souboru.',
            'output' => 'Po obnově soubor web/index.html obsahuje původní text „ahoj“.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'mkdir web; echo ahoj > web/index.html; tar -czf zaloha.tar.gz web; rm -r web; tar -xzf zaloha.tar.gz; cat web/index.html',
                'expect' => 'ahoj',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Ověř nastavení SSH (port, přihlášení roota) a naslouchající porty a vyplň tabulku rizik pro všechny čtyři oblasti.',
            'output' => 'Port 22, PermitRootLogin no, porty 22 a 80; tabulka oblast – důkaz – riziko – opatření.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep -E \'Port|PermitRootLogin\' /etc/ssh/sshd_config',
                'expect' => 'PermitRootLogin no',
              ),
              1 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:22',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu se čtyřmi oblastmi a prvním příkazem každé oblasti.',
          'standard' => 'Všechny čtyři oblasti s důkazy a tabulka rizik.',
          'challenge' => 'Doplní k záloze výpočet RPO a RTO pro zálohu jednou denně a obnovu za 20 minut.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel se zeptá: existuje archiv – je záloha v pořádku? (ne, dokud neproběhne obnova)',
            1 => 'Dvojice si vymění tabulky rizik a doplní chybějící důkaz.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Důkazy',
              'levels' => 
              array (
                0 => 'Bez důkazů.',
                1 => '1–2 oblasti.',
                2 => 'Všechny 4 oblasti s výstupem.',
                3 => 'Důkazy srozumitelné i bez autora.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Záloha',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Archiv bez obnovy.',
                2 => 'Obnova ověřená obsahem.',
                3 => 'Obnova do jiného místa a výpočet RPO/RTO.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Tabulka rizik',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez opatření.',
                2 => 'Riziko a opatření u každé oblasti.',
                3 => 'Opatření seřazená podle dopadu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_backup',
          'competence_label' => 'Záloha a obnova',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kdy je záloha opravdu ověřená?',
              'options' => 
              array (
                0 => 'Když archiv existuje.',
                1 => 'Když má archiv velikost větší než 0.',
                2 => 'Když z ní proběhla obnova a obsah sedí.',
              ),
              'correct' => 2,
              'explanation' => 'Důvěryhodná je jen ověřená obnova.',
            ),
            1 => 
            array (
              'question' => 'Co ověří nginx -t?',
              'options' => 
              array (
                0 => 'Syntaxi konfigurace webového serveru.',
                1 => 'Rychlost webu.',
                2 => 'Platnost certifikátu.',
              ),
              'correct' => 0,
              'explanation' => 'Test konfigurace před restartem.',
            ),
            2 => 
            array (
              'question' => 'PermitRootLogin no v konfiguraci SSH znamená:',
              'options' => 
              array (
                0 => 'SSH je vypnuté.',
                1 => 'Přihlásit se přímo jako root přes SSH nelze.',
                2 => 'Root nemá heslo.',
              ),
              'correct' => 1,
              'explanation' => 'Správce se přihlásí svým účtem a použije sudo.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: spočítej RPO a RTO pro zálohu každých 6 hodin a obnovu trvající 45 minut.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zálohy, mazání a obnovu zkoušíme jen v simulátoru Linux Labu.',
          1 => 'Tabulka rizik neobsahuje skutečné adresy ani přístupové údaje.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „archiv existuje = máme zálohu“ – nech žáky obnovu skutečně provést.',
          1 => 'Otázka do třídy: Které riziko z tabulky byste řešili jako první a proč?',
          2 => 'Tempo: oblasti mohou dvojice dělat souběžně; tabulka rizik je povinný výstup.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: dvojice plní úkoly 1–4 v Linux Labu (výstupy v zadání).',
          1 => 'Plán B offline: vytištěné výpisy čtyř oblastí a tabulka rizik.',
        ),
        'glossary' => 
        array (
          0 => 'restore',
          1 => 'hardening',
        ),
        '_file' => 'lesson_content_v76_4a_a.php',
      ),
      19 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 19 · Čtyři zlaté signály: provoz, chyby, latence, saturace',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím ze záznamů služby spočítat provoz a podíl chyb, vysvětlit latenci pomocí p95 a ze stavu systému odhadnout saturaci – a navrhnout, které signály hlídat upozorněním.',
          'success_criteria' => 
          array (
            0 => 'Z přístupového logu spočítám počet požadavků a podíl odpovědí 4xx/5xx v procentech.',
            1 => 'Vysvětlím, proč se latence sleduje percentilem p95, ne průměrem.',
            2 => 'Z load average a počtu CPU odhadnu, jestli je server přetížený.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_observability',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: „je to pomalé“',
            'teacher' => 'Přečte stížnost bez čísel a zeptá se, co potřebujeme změřit.',
            'student' => 'Navrhnou měřitelné signály.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Čtyři signály',
            'teacher' => 'Vysvětlí provoz, chyby, latenci a saturaci.',
            'student' => 'Ke každému signálu napíší otázku, na kterou odpovídá.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 42,
            'phase' => 'Provoz a chyby z logu',
            'teacher' => 'Předvede počítání požadavků a chybových kódů.',
            'student' => 'Spočítají provoz a podíl chyb.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 58,
            'phase' => 'Latence a p95',
            'teacher' => 'Na deseti časech odezvy ukáže průměr a p95.',
            'student' => 'Spočítají p95 z připravených hodnot.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 80,
            'phase' => 'Saturace a upozornění',
            'teacher' => 'Ukáže load average a počet CPU.',
            'student' => 'Odhadnou saturaci a navrhnou dvě upozornění.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: měř, než začneš opravovat.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spočítej počet požadavků v přístupovém logu a počet odpovědí s kódem 400 a vyšším; vyjádři podíl chyb v procentech.',
            'output' => '30 požadavků, 15 odpovědí ≥ 400 → 50 % chyb.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep -c . /var/log/nginx/access.log',
                'expect' => '30',
              ),
              1 => 
              array (
                'cmd' => 'sudo awk \'$9 >= 400\' /var/log/nginx/access.log | wc -l',
                'expect' => '15',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zjisti, ze které adresy přišlo nejvíc požadavků (provoz podle klienta).',
            'output' => 'Nejvíc 10.0.0.31 (13 požadavků).',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo awk \'{print $1}\' /var/log/nginx/access.log | sort | uniq -c | sort -rn | head -n 3',
                'expect' => '13 10.0.0.31',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Z deseti časů odezvy (40, 42, 45, 41, 44, 43, 46, 40, 300, 42 ms) spočítej průměr a p95 a vysvětli, proč se liší.',
            'output' => 'Průměr 68,3 ms, p95 = 300 ms – jeden pomalý požadavek se v průměru schová.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Zjisti zatížení (load average) a počet CPU a odhadni saturaci; navrhni dvě upozornění s prahem a dobou trvání.',
            'output' => 'Load 0,08 při 2 CPU = nízká saturace; např. „chyby > 5 % po 5 min“, „p95 > 500 ms po 10 min“.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'uptime',
                'expect' => 'load average: 0.08',
              ),
              1 => 
              array (
                'cmd' => 'lscpu | head -n 6',
                'expect' => 'CPU(s):                  2',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku čtyř signálů s příkazy a seřazené časy odezvy.',
          'standard' => 'Provoz, chyby, p95, saturace a dvě upozornění.',
          'challenge' => 'Navrhne SLI a SLO pro chyby (např. 99 % úspěšných požadavků za 30 dní) a spočítá rozpočet chyb.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže čísla, třída určí, který signál popisují.',
            1 => 'Dvojice: proč průměr 68 ms neznamená, že je web rychlý?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Provoz a chyby',
              'levels' => 
              array (
                0 => 'Nespočítá.',
                1 => 'Počty bez podílu.',
                2 => 'Počty i podíl v procentech.',
                3 => 'Rozdělení podle klienta i stránky.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Latence',
              'levels' => 
              array (
                0 => 'Nezná.',
                1 => 'Jen průměr.',
                2 => 'p95 s vysvětlením.',
                3 => 'Vysvětlí dopad pomalého konce (tail latency).',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Saturace a upozornění',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Upozornění bez prahu.',
                2 => 'Prah i doba trvání.',
                3 => 'Upozornění vázané na dopad na uživatele.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_observability',
          'competence_label' => 'Pozorovatelnost a SLO',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč se latence sleduje p95, a ne průměrem?',
              'options' => 
              array (
                0 => 'p95 je vždy menší.',
                1 => 'Průměr schová pomalé požadavky, které uživatelé cítí.',
                2 => 'Průměr nejde spočítat.',
              ),
              'correct' => 1,
              'explanation' => 'p95 ukazuje zkušenost i „pomalého konce“.',
            ),
            1 => 
            array (
              'question' => 'Z 200 požadavků skončilo 10 chybou 5xx. Jaký je podíl chyb?',
              'options' => 
              array (
                0 => '5 %',
                1 => '10 %',
                2 => '20 %',
              ),
              'correct' => 0,
              'explanation' => '10 / 200 = 0,05 = 5 %.',
            ),
            2 => 
            array (
              'question' => 'Server má 2 CPU a load average 6. Co to naznačuje?',
              'options' => 
              array (
                0 => 'Server je nevyužitý.',
                1 => 'Je málo místa na disku.',
                2 => 'Procesy čekají na CPU – saturace.',
              ),
              'correct' => 2,
              'explanation' => 'Load výrazně nad počtem CPU = fronta čekajících.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: změř 10× dobu načtení oblíbeného webu a spočítej průměr a nejpomalejší hodnotu.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Logy jsou ze simulátoru; skutečné logy obsahují adresy uživatelů a čte je jen oprávněná osoba.',
          1 => 'Upozornění navrhujeme, nenastavujeme na skutečných systémech.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: průměr jako „rychlost webu“ – ukaž jeden pomalý požadavek, který p95 odhalí.',
          1 => 'Otázka do třídy: Který signál byste hlídali, kdybyste mohli jen jeden?',
          2 => 'Tempo: p95 počítejte ručně na deseti hodnotách – seřadit, vzít hodnotu na 95. percentilu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1, 2 a 4 v Linux Labu (výstupy v zadání), úkol 3 na papír.',
          1 => 'Plán B offline: vytištěný výřez logu a časy odezvy; výpočty ručně.',
        ),
        'glossary' => 
        array (
          0 => 'p95',
          1 => 'observability',
        ),
        '_file' => 'lesson_content_v76_4a_a.php',
      ),
      20 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 20 · Reverzní proxy: důkazy z hlaviček a konfigurace',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vysvětlit cestu požadavku přes reverzní proxy (reverse proxy) k upstream službě, přečíst důkazy z hlaviček odpovědi a konfigurace a navrhnout kontrolu zdraví (health check) upstreamu.',
          'success_criteria' => 
          array (
            0 => 'Nakreslím cestu klient → proxy → upstream a u každého článku řeknu, kde hledat důkaz.',
            1 => 'Z hlaviček odpovědi přečtu stav, server a typ obsahu.',
            2 => 'Navrhnu health check s cestou, očekávanou odpovědí a intervalem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_proxy_tls',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: 502 Bad Gateway',
            'teacher' => 'Ukáže chybu 502 a zeptá se, kdo ji vrátil.',
            'student' => 'Odhadnou, kde v cestě je problém.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Cesta požadavku',
            'teacher' => 'Nakreslí klient → proxy → upstream a vysvětlí 502 a 504.',
            'student' => 'Doplní schéma o místa důkazů.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Hlavičky odpovědi',
            'teacher' => 'Předvede curl -I a rozbor hlaviček.',
            'student' => 'Přečtou stav, server a typ obsahu.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 60,
            'phase' => 'Konfigurace',
            'teacher' => 'Ukáže konfiguraci serveru a kde by byl proxy_pass na upstream.',
            'student' => 'Najdou v konfiguraci listen a location.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Health check',
            'teacher' => 'Vysvětlí aktivní kontrolu upstreamu.',
            'student' => 'Navrhnou health check a reakci na selhání.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: kdo vrátil chybu, tam hledej.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Nakresli cestu požadavku klient → reverzní proxy → upstream a ke každému článku zapiš, kde najdeš důkaz (hlavička, log proxy, log aplikace).',
            'output' => 'Schéma tří článků s místy důkazů a významem 502 a 504.',
            'time' => '13 min',
          ),
          1 => 
          array (
            'text' => 'Přečti hlavičky odpovědi webového serveru a zapiš stav, server a typ obsahu.',
            'output' => 'HTTP/1.1 200 OK, Server: nginx/1.22.1, Content-Type: text/html.',
            'time' => '13 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => 'Server: nginx/1.22.1',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Najdi v konfiguraci výchozího webu direktivy listen a location a vysvětli, kam bys doplnil(a) přesměrování na upstream.',
            'output' => 'listen 80 default_server; location / { try_files … } – proxy_pass by nahradil try_files v location.',
            'time' => '17 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'cat /etc/nginx/sites-enabled/default',
                'expect' => 'location / {',
              ),
              1 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Navrhni health check upstreamu: cesta (např. /zdravi), očekávaná odpověď, interval, počet selhání do vyřazení a co uvidí uživatel.',
            'output' => 'Tabulka parametrů health checku a reakce proxy.',
            'time' => '17 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane schéma cesty se třemi rámečky a rozepsaný výstup curl -I.',
          'standard' => 'Schéma, hlavičky, konfigurace a návrh health checku.',
          'challenge' => 'Navrhne rozložení zátěže mezi dva upstreamy (round-robin) a popíše, co se stane při výpadku jednoho.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže kód 502 / 504 / 404 a třída řekne, který článek ho nejspíš vrátil.',
            1 => 'Dvojice: ukaž v konfiguraci místo pro proxy_pass.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Cesta požadavku',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez důkazů.',
                2 => 'Tři články s místy důkazů.',
                3 => 'Vysvětlí 502 vs. 504.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Hlavičky a konfigurace',
              'levels' => 
              array (
                0 => 'Nečte.',
                1 => 'Jen stav.',
                2 => 'Stav, server, typ obsahu a direktivy.',
                3 => 'Navrhne změnu konfigurace s testem.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Health check',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen cesta.',
                2 => 'Cesta, odpověď, interval, prah.',
                3 => 'Reakce proxy i informace pro uživatele.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_proxy_tls',
          'competence_label' => 'Reverzní proxy a TLS',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Uživatel dostane 502 Bad Gateway. Kdo tu odpověď vytvořil?',
              'options' => 
              array (
                0 => 'Prohlížeč uživatele.',
                1 => 'DNS server.',
                2 => 'Reverzní proxy, protože nedostala platnou odpověď od upstreamu.',
              ),
              'correct' => 2,
              'explanation' => '502 vrací brána, když upstream selže.',
            ),
            1 => 
            array (
              'question' => 'K čemu je health check upstreamu?',
              'options' => 
              array (
                0 => 'Proxy pozná nefunkční upstream a přestane na něj posílat požadavky.',
                1 => 'Zrychlí odpověď DNS.',
                2 => 'Zašifruje provoz.',
              ),
              'correct' => 0,
              'explanation' => 'Aktivní kontrola vyřadí nemocný upstream.',
            ),
            2 => 
            array (
              'question' => 'Co je rozdíl mezi 502 a 504?',
              'options' => 
              array (
                0 => 'Žádný.',
                1 => '502 = neplatná odpověď upstreamu, 504 = upstream neodpověděl včas.',
                2 => '504 je chyba klienta.',
              ),
              'correct' => 1,
              'explanation' => 'Bad Gateway vs. Gateway Timeout.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v Linux Labu přečti hlavičky odpovědi intranetu a porovnej je s localhost – co se liší?',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Proxy a hlavičky zkoumáme jen v simulátoru a na laboratorních jménech.',
          1 => 'Hlavičky skutečných služeb mohou prozradit verze softwaru – v produkci je omezujeme.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „502 je chyba proxy“ – proxy jen hlásí, že upstream selhal.',
          1 => 'Otázka do třídy: Kde v cestě byste hledali, kdyby chyba přišla jen u každého druhého požadavku?',
          2 => 'Tempo: konfigurace bez skutečného proxy_pass – jde o porozumění, ne o nastavení.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.',
          1 => 'Plán B offline: vytištěná konfigurace a hlavičky, schéma cesty na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'reverse-proxy',
          1 => 'health-check',
        ),
        '_file' => 'lesson_content_v76_4a_a.php',
      ),
      21 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 21 · Síť kontejnerů: porty, sítě a data mimo kontejner',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím popsat, jak kontejner komunikuje s hostitelem a s ostatními kontejnery, navrhnout mapování portů bez kolize a vysvětlit, proč data patří do svazku (volume), ne do kontejneru.',
          'success_criteria' => 
          array (
            0 => 'Zapíšu mapování portů (port mapping) hostitel:kontejner a odhalím kolizi s již naslouchající službou.',
            1 => 'Rozliším komunikaci kontejnerů ve vlastní síti podle jména a zvenku přes publikovaný port.',
            2 => 'Vysvětlím, co se stane s daty po smazání kontejneru bez svazku.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_services',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: zmizelá databáze',
            'teacher' => 'Popíše situaci: po aktualizaci kontejneru zmizela data.',
            'student' => 'Hádají proč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Obraz, kontejner, svazek',
            'teacher' => 'Vysvětlí image, container a volume.',
            'student' => 'Doplní schéma tří pojmů.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Mapování portů',
            'teacher' => 'Ukáže zápis 8080:80 a kolizi portů na hostiteli.',
            'student' => 'Zjistí obsazené porty hostitele a navrhnou mapování.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Síť kontejnerů',
            'teacher' => 'Nakreslí vlastní síť: web → api → db podle jmen.',
            'student' => 'Navrhnou síť pro tři kontejnery, ven jen web.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Kontrolní scénáře',
            'teacher' => 'Zadá tři chybové scénáře.',
            'student' => 'Určí příčinu a opravu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: ven jen to, co musí.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Doplň schéma: obraz (image) → kontejner (container) → svazek (volume) a ke každému napiš, co se stane při aktualizaci.',
            'output' => 'Schéma s poznámkou: kontejner se nahradí, svazek zůstane.',
            'time' => '13 min',
          ),
          1 => 
          array (
            'text' => 'Zjisti, které porty hostitel už poslouchá, a navrhni mapování portů pro webový kontejner tak, aby nekolidovalo.',
            'output' => 'Hostitel poslouchá 22 a 80 → mapování např. 8080:80 se zdůvodněním.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Navrhni síť pro tři kontejnery (web, api, db): kdo s kým mluví podle jména a který jediný port se publikuje ven.',
            'output' => 'Schéma sítě s publikovaným portem jen u webu a db bez portu ven.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'U tří scénářů (port už obsazen, api nevidí db, data zmizela po aktualizaci) urči příčinu a opravu.',
            'output' => 'Tabulka scénář → příčina → oprava → ověření.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane schéma s prázdnými rámečky a tabulku mapování portů s příkladem.',
          'standard' => 'Schéma pojmů, mapování portů, síť a scénáře.',
          'challenge' => 'Popíše, jak by kontejner db omezil(a), aby byl dostupný jen z kontejneru api (síť, žádný publikovaný port).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel napíše 8080:80, třída řekne, který port je na hostiteli.',
            1 => 'Dvojice: kde jsou data po smazání kontejneru?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Pojmy',
              'levels' => 
              array (
                0 => 'Zaměňuje.',
                1 => 'Zná dva.',
                2 => 'Image, container, volume správně.',
                3 => 'Vysvětlí životní cyklus při aktualizaci.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Porty a síť',
              'levels' => 
              array (
                0 => 'Bez návrhu.',
                1 => 'Kolize portů.',
                2 => 'Bez kolize, ven jen web.',
                3 => 'Zdůvodní menší útočnou plochu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Scénáře',
              'levels' => 
              array (
                0 => 'Neřeší.',
                1 => 'Jeden.',
                2 => 'Tři s příčinou a opravou.',
                3 => 'I s ověřením opravy.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_services',
          'competence_label' => 'Služby a kontejnery',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co znamená mapování portů 8080:80?',
              'options' => 
              array (
                0 => 'Port 8080 na hostiteli vede na port 80 v kontejneru.',
                1 => 'Port 80 na hostiteli vede na 8080 v kontejneru.',
                2 => 'Kontejner má dva porty pro rychlost.',
              ),
              'correct' => 0,
              'explanation' => 'Zápis je hostitel:kontejner.',
            ),
            1 => 
            array (
              'question' => 'Proč databázový kontejner nemá publikovaný port ven?',
              'options' => 
              array (
                0 => 'Protože databáze port nepotřebuje.',
                1 => 'Aby k ní přistupovaly jen kontejnery ve stejné síti – menší útočná plocha.',
                2 => 'Protože porty jsou pomalé.',
              ),
              'correct' => 1,
              'explanation' => 'Ven jen to, co musí.',
            ),
            2 => 
            array (
              'question' => 'Kam patří data databáze v kontejneru?',
              'options' => 
              array (
                0 => 'Do vrstvy kontejneru.',
                1 => 'Do obrazu.',
                2 => 'Do svazku (volume) mimo životní cyklus kontejneru.',
              ),
              'correct' => 2,
              'explanation' => 'Svazek přežije nahrazení kontejneru.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: nakresli síť kontejnerů pro blog (web, databáze, zálohy) a označ, co se publikuje ven.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Kontejnery v hodině navrhujeme na papíře a v simulátoru; nic se nespouští na školních počítačích.',
          1 => 'Obrazy kontejnerů jen z důvěryhodných zdrojů; hesla nepatří do obrazu ani do konfigurace v repozitáři.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: zaměněné pořadí hostitel:kontejner – nech žáky číst mapování nahlas.',
          1 => 'Otázka do třídy: Co všechno se ztratí, když smažete kontejner bez svazku?',
          2 => 'Tempo: simulátor kontejnery nespouští – praktická část je zjištění portů hostitele, zbytek je návrh.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkol 2 v Linux Labu (výstup v zadání), ostatní úkoly na papíře.',
          1 => 'Plán B offline: kartičky kontejnerů, sítí a portů skládané na lavici.',
        ),
        'glossary' => 
        array (
          0 => 'container',
          1 => 'port-mapping',
        ),
        '_file' => 'lesson_content_v76_4a_a.php',
      ),
      22 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 22 · Den obnovy: záloha nestačí, obnova rozhoduje',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím provést cvičnou obnovu ze zálohy, ověřit shodu dat otiskem SHA-256, změřit dobu obnovy a porovnat výsledek s cíli RPO a RTO.',
          'success_criteria' => 
          array (
            0 => 'Obnovím data z archivu do jiného místa a ověřím je otiskem.',
            1 => 'Spočítám skutečné RPO a RTO cvičení a porovnám je s cíli.',
            2 => 'Zapíšu do runbooku obnovy krok, který cvičení ukázalo jako slabý.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_backup',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: záloha z minulého roku',
            'teacher' => 'Popíše firmu, která zjistila nefunkční zálohy až při havárii.',
            'student' => 'Navrhnou, jak tomu předejít.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Cíle RPO a RTO',
            'teacher' => 'Připomene RPO a RTO na příkladu.',
            'student' => 'Zapíší cíle pro cvičnou službu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 45,
            'phase' => 'Záloha a obnova',
            'teacher' => 'Spouští časovač cvičení.',
            'student' => 'Vytvoří zálohu, „ztratí“ data a obnoví je.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 60,
            'phase' => 'Ověření otiskem',
            'teacher' => 'Ukáže sha256sum před a po.',
            'student' => 'Porovnají otisky originálu a obnovy.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Vyhodnocení',
            'teacher' => 'Vede porovnání s cíli.',
            'student' => 'Spočítají skutečné RPO/RTO a upraví runbook.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: neověřená záloha je jen naděje.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zapiš cíle cvičné služby: RPO 1 hodina, RTO 30 minut, a vysvětli, co každý cíl znamená pro četnost záloh a rychlost obnovy.',
            'output' => 'Dvě věty: záloha aspoň každou hodinu; obnova do 30 minut včetně ověření.',
            'time' => '10 min',
          ),
          1 => 
          array (
            'text' => 'Vytvoř zálohu adresáře web, adresář smaž a obnov ho z archivu; ověř obsah obnoveného souboru.',
            'output' => 'Po rozbalení web/index.html obsahuje „ahoj“.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'mkdir web; echo ahoj > web/index.html; tar -czf zaloha.tar.gz web; rm -r web; tar -xzf zaloha.tar.gz; cat web/index.html',
                'expect' => 'ahoj',
              ),
              1 => 
              array (
                'cmd' => 'mkdir web; echo ahoj > web/index.html; tar -czf zaloha.tar.gz web; tar -tzf zaloha.tar.gz',
                'expect' => 'web/index.html',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř shodu originálu a kopie otiskem SHA-256 a vysvětli, proč nestačí porovnat velikost.',
            'output' => 'Oba soubory mají stejný otisk 0b4f321b…; velikost se může shodovat i u jiného obsahu.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'echo ahoj > a.txt; cp a.txt b.txt; sha256sum a.txt b.txt',
                'expect' => '0b4f321bf090fcf3792f5fffc4fcd1801fab37e0470c66d46cb0f0181b316a73  b.txt',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Spočítej skutečné RPO a RTO cvičení (poslední záloha před „havárií“, čas obnovy včetně ověření) a doplň do runbooku obnovy jeden zlepšený krok.',
            'output' => 'Porovnání se cíli a upravený krok runbooku.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu kroků obnovy a vzorec pro RPO a RTO.',
          'standard' => 'Cíle, záloha a obnova, otisk a vyhodnocení.',
          'challenge' => 'Navrhne pravidlo 3-2-1 pro cvičnou službu a vysvětlí, proti kterým hrozbám chrání.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel řekne „záloha každých 6 hodin“, třída řekne nejhorší možnou ztrátu dat.',
            1 => 'Dvojice: ukažte otisk originálu a obnovy.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'RPO a RTO',
              'levels' => 
              array (
                0 => 'Nezná.',
                1 => 'Zaměňuje.',
                2 => 'Správně vysvětlí a spočítá.',
                3 => 'Navrhne zálohování podle cílů.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Obnova',
              'levels' => 
              array (
                0 => 'Neprovede.',
                1 => 'Bez ověření.',
                2 => 'Obnova ověřená obsahem a otiskem.',
                3 => 'Obnova do jiného místa s měřením času.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Runbook',
              'levels' => 
              array (
                0 => 'Bez úprav.',
                1 => 'Obecná poznámka.',
                2 => 'Konkrétní zlepšený krok.',
                3 => 'Krok s ověřením a vlastníkem.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_backup',
          'competence_label' => 'Záloha a obnova',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Záloha běží každých 6 hodin. Jaké je nejhorší RPO?',
              'options' => 
              array (
                0 => '6 hodin ztracených dat.',
                1 => '6 minut.',
                2 => 'Žádná ztráta.',
              ),
              'correct' => 0,
              'explanation' => 'Havárie těsně před další zálohou = ztráta až 6 hodin.',
            ),
            1 => 
            array (
              'question' => 'Proč se obnova ověřuje otiskem (např. SHA-256)?',
              'options' => 
              array (
                0 => 'Otisk zrychlí obnovu.',
                1 => 'Otisk zašifruje data.',
                2 => 'Shodný otisk potvrdí, že obnovená data jsou stejná jako originál.',
              ),
              'correct' => 2,
              'explanation' => 'Velikost se může shodovat i u poškozeného obsahu.',
            ),
            2 => 
            array (
              'question' => 'Co přesně udává cíl RTO u služby?',
              'options' => 
              array (
                0 => 'Kolik dat smíme ztratit.',
                1 => 'Za jak dlouho musí být služba po výpadku zase dostupná.',
                2 => 'Kolik záloh uchováváme.',
              ),
              'correct' => 1,
              'explanation' => 'Recovery Time Objective = cílová doba obnovy.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zjisti, jak často se zálohuje tvůj telefon nebo počítač, a odhadni své RPO.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zálohu a mazání zkoušíme jen v simulátoru; skutečné obnovy se dělají do odděleného prostředí.',
          1 => 'Zálohy osobních dat jsou citlivé – šifrují se a uchovávají jen po nutnou dobu (retence).',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: obnova přímo přes originál – učte obnovu do jiného místa a pak porovnání.',
          1 => 'Otázka do třídy: Kdy jste naposledy viděli, že záloha opravdu jde obnovit?',
          2 => 'Tempo: časovač cvičení je důležitý – bez měření nejde spočítat skutečné RTO.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.',
          1 => 'Plán B offline: kartičky kroků obnovy seřazené na lavici a výpočty RPO/RTO.',
        ),
        'glossary' => 
        array (
          0 => 'rpo',
          1 => 'rto',
        ),
        '_file' => 'lesson_content_v76_4a_a.php',
      ),
      23 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 23 · Audit zpevnění serveru: přístup, porty a práva',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím provést audit zpevnění serveru – konfigurace SSH, naslouchající služby, práva citlivých souborů a členství ve skupině sudo – a navrhnout opatření bez rizika, že se správce sám zamkne.',
          'success_criteria' => 
          array (
            0 => 'Ke každé kontrole mám příkaz, výstup a hodnocení (v pořádku / riziko).',
            1 => 'Každá naslouchající služba má důvod, jinak ji navrhnu vypnout (menší útočná plocha, attack surface).',
            2 => 'Před změnou SSH mám otevřené druhé připojení a plán návratu.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_hardening',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: zamčený správce',
            'teacher' => 'Popíše správce, který zakázal hesla dřív, než nahrál klíč, a zamkl se.',
            'student' => 'Navrhnou, jak tomu předejít.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Kontrolní seznam',
            'teacher' => 'Představí oblasti auditu: přístup, služby, práva, účty.',
            'student' => 'Ke každé oblasti zapíší jednu kontrolu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 50,
            'phase' => 'Audit v simulátoru',
            'teacher' => 'Hlídá, aby každá kontrola měla důkaz.',
            'student' => 'Provedou kontroly a výstupy zapíší.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 65,
            'phase' => 'Hodnocení rizik',
            'teacher' => 'Vysvětlí útočnou plochu a nejmenší práva.',
            'student' => 'Ohodnotí nálezy a seřadí je.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Bezpečný plán změn',
            'teacher' => 'Ukáže postup bez zamčení: druhé připojení, test, návrat.',
            'student' => 'Napíší plán dvou změn s návratem.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: zpevňuj s plánem návratu.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přečti konfiguraci SSH serveru a ohodnoť každé nastavení (port, přihlášení roota, přesměrování X11, SFTP).',
            'output' => 'PermitRootLogin no a X11Forwarding no jsou v pořádku; chybí výslovné zakázání přihlášení heslem.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cat /etc/ssh/sshd_config',
                'expect' => 'X11Forwarding no',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Zjisti naslouchající služby a práva konfiguračního souboru SSH; ke každé službě napiš, proč na serveru běží.',
            'output' => 'Porty 22 a 80; sshd_config -rw-r--r-- root root (zapisuje jen root).',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ss -tln',
                'expect' => '0.0.0.0:80',
              ),
              1 => 
              array (
                'cmd' => 'ls -l /etc/ssh/sshd_config',
                'expect' => '-rw-r--r-- 1 root root',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř, kteří uživatelé mají práva správce přes skupinu sudo, a vysvětli princip nejmenších práv.',
            'output' => 'Uživatel student je ve skupině 27(sudo) – oprávnění má mít jen ten, kdo server spravuje.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'id student',
                'expect' => '27(sudo)',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Napiš plán dvou změn zpevnění (např. zakázat přihlášení heslem, omezit SSH na síť správců) s ověřením a plánem návratu, aby ses nezamkl(a).',
            'output' => 'Plán: druhé připojení otevřené → změna → sshd -t → reload → test nového připojení → případný návrat.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kontrolní seznam s příkazy a tabulku hodnocení.',
          'standard' => 'Audit čtyř oblastí, hodnocení a plán dvou změn.',
          'challenge' => 'Navrhne ochranu proti hádání hesel (omezení pokusů, lockout) a popíše riziko zablokování legitimních uživatelů.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte nastavení, třída zvedne kartu „v pořádku / riziko“.',
            1 => 'Dvojice: kde v plánu změny je krok návratu?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Audit',
              'levels' => 
              array (
                0 => 'Bez důkazů.',
                1 => '1–2 oblasti.',
                2 => 'Čtyři oblasti s výstupem.',
                3 => 'Nálezy s odkazem na princip.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Hodnocení rizik',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez pořadí.',
                2 => 'Seřazeno podle dopadu.',
                3 => 'Zdůvodněno útočnou plochou.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Plán změn',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez návratu.',
                2 => 'Ověření a plán návratu.',
                3 => 'Bez rizika zamčení, s testem konfigurace.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_hardening',
          'competence_label' => 'Zabezpečení služby',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co uděláš před zakázáním přihlášení heslem přes SSH?',
              'options' => 
              array (
                0 => 'Restartuji server.',
                1 => 'Ověřím, že funguje přihlášení klíčem, a nechám otevřené druhé připojení.',
                2 => 'Smažu všechny klíče.',
              ),
              'correct' => 1,
              'explanation' => 'Jinak se můžeš zamknout.',
            ),
            1 => 
            array (
              'question' => 'Co znamená „zmenšit útočnou plochu“?',
              'options' => 
              array (
                0 => 'Vypnout služby a otevřené porty, které server nepotřebuje.',
                1 => 'Zmenšit disk serveru.',
                2 => 'Používat kratší hesla.',
              ),
              'correct' => 0,
              'explanation' => 'Co neběží, nejde napadnout.',
            ),
            2 => 
            array (
              'question' => 'Proč nemá být každý uživatel ve skupině sudo?',
              'options' => 
              array (
                0 => 'Protože sudo je pomalé.',
                1 => 'Protože skupina má limit členů.',
                2 => 'Kvůli principu nejmenších práv – správcovská oprávnění jen pro toho, kdo je potřebuje.',
              ),
              'correct' => 2,
              'explanation' => 'Méně oprávnění = menší škoda při zneužití účtu.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zkontroluj na svém telefonu, které aplikace mají přístup k poloze, a odeber jednu nepotřebnou.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Audit provádíme jen v simulátoru; audit cizího serveru bez povolení je zakázaný.',
          1 => 'Výsledky auditu jsou citlivé – nesdílíme je mimo hodinu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: zpevnění bez plánu návratu – připomeň zamčeného správce z úvodu.',
          1 => 'Otázka do třídy: Která služba na vašem serveru nemusí poslouchat na všech rozhraních?',
          2 => 'Tempo: plán změn je hlavní výstup; audit drž v časovém limitu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), plán na papír.',
          1 => 'Plán B offline: vytištěná konfigurace a výpisy s kontrolním seznamem.',
        ),
        'glossary' => 
        array (
          0 => 'attack-surface',
          1 => 'lockout',
        ),
        '_file' => 'lesson_content_v76_4a_b.php',
      ),
      24 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 24 · Bezpečná změna a rollback: plán návratu předem',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím provést změnu konfigurace služby bezpečně: záloha, změna, test konfigurace, ověření – a když test selže, vrátit předchozí stav (rollback) a ověřit ho.',
          'success_criteria' => 
          array (
            0 => 'Před změnou zazálohuji konfiguraci a zapíšu kritéria úspěchu.',
            1 => 'Změnu ověřím testem konfigurace a porovnáním (diff) se zálohou.',
            2 => 'Při selhání provedu rollback a doložím, že služba je ve výchozím stavu.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_change',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: páteční změna',
            'teacher' => 'Popíše změnu bez zálohy, která shodila web na celý víkend.',
            'student' => 'Vyjmenují chybějící kroky.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Plán změny',
            'teacher' => 'Ukáže šablonu: co, proč, kritéria úspěchu, rollback, okno.',
            'student' => 'Vyplní plán změny portu webu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 45,
            'phase' => 'Změna s ověřením',
            'teacher' => 'Předvede zálohu, změnu a diff.',
            'student' => 'Provedou změnu a porovnají ji se zálohou.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Chybná změna a rollback',
            'teacher' => 'Zadá změnu s překlepem v konfiguraci.',
            'student' => 'Test selže, provedou rollback a ověří ho.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Postupné nasazení',
            'teacher' => 'Vysvětlí postupné nasazení (canary) a podmínku zastavení.',
            'student' => 'Doplní do plánu kritérium zastavení.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: rollback se plánuje před změnou.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vyplň plán změny „web na portu 8080“: co, proč, kritéria úspěchu, postup rollbacku a časové okno.',
            'output' => 'Plán změny na půl strany s měřitelným kritériem úspěchu.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Zazálohuj konfiguraci webu, změň port 80 na 8080 a porovnej změnu se zálohou příkazem diff.',
            'output' => 'diff ukáže řádek listen 80 → listen 8080.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/sites-enabled/default /tmp/default.bak; sudo sed -i \'s/listen 80 default_server/listen 8080 default_server/\' /etc/nginx/sites-enabled/default; diff /tmp/default.bak /etc/nginx/sites-enabled/default',
                'expect' => 'listen 8080 default_server',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Proveď chybnou změnu (překlep v direktivě), spusť test konfigurace a po selhání vrať zálohu a test zopakuj.',
            'output' => 'Test hlásí unknown directive a test failed; po rollbacku syntax is ok.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo sed -i \'s/try_files/try_filez/\' /etc/nginx/sites-enabled/default; sudo nginx -t',
                'expect' => 'test failed',
              ),
              1 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/sites-enabled/default /tmp/default.bak; sudo sed -i \'s/try_files/try_filez/\' /etc/nginx/sites-enabled/default; sudo cp /tmp/default.bak /etc/nginx/sites-enabled/default; sudo nginx -t',
                'expect' => 'syntax is ok',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Doplň do plánu postupné nasazení na 10 % provozu s podmínkou zastavení (např. podíl chyb nad 2 % po 10 minutách) a kdo rozhoduje o rollbacku.',
            'output' => 'Kroky nasazení s měřitelnou podmínkou zastavení a odpovědnou rolí.',
            'time' => '17 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu plánu s vyplněným příkladem a kartu příkazů.',
          'standard' => 'Plán, změna s diff, chybná změna s rollbackem a postupné nasazení.',
          'challenge' => 'Navrhne, jak změnu automatizovat tak, aby test konfigurace a rollback proběhly bez ručního zásahu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže plán bez rollbacku, třída doplní chybějící krok.',
            1 => 'Dvojice: ukaž důkaz, že rollback vrátil výchozí stav.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Plán změny',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez kritérií.',
                2 => 'Kritéria úspěchu a rollback.',
                3 => 'Podmínka zastavení a role.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Provedení',
              'levels' => 
              array (
                0 => 'Bez zálohy.',
                1 => 'Záloha bez ověření.',
                2 => 'Záloha, diff, test konfigurace.',
                3 => 'Ověření i z pohledu uživatele.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rollback',
              'levels' => 
              array (
                0 => 'Neumí.',
                1 => 'Bez ověření.',
                2 => 'Rollback ověřený testem.',
                3 => 'Rollback v časovém limitu okna.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_change',
          'competence_label' => 'Řízená změna s plánem návratu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kdy se má připravit postup rollbacku?',
              'options' => 
              array (
                0 => 'Až když změna selže.',
                1 => 'Před provedením změny jako součást plánu.',
                2 => 'Nikdy, změny se nevracejí.',
              ),
              'correct' => 1,
              'explanation' => 'V krizi není čas vymýšlet návrat.',
            ),
            1 => 
            array (
              'question' => 'nginx -t hlásí „test failed“ po tvé změně. Co uděláš?',
              'options' => 
              array (
                0 => 'Vrátím zálohu konfigurace a test zopakuji.',
                1 => 'Restartuji službu, třeba to projde.',
                2 => 'Smažu konfiguraci.',
              ),
              'correct' => 0,
              'explanation' => 'Chybnou konfiguraci nenasazujeme; vracíme ověřený stav.',
            ),
            2 => 
            array (
              'question' => 'Co je postupné (kanárkové) nasazení?',
              'options' => 
              array (
                0 => 'Nasazení jen v noci.',
                1 => 'Nasazení bez testů.',
                2 => 'Nová verze nejdřív pro malou část provozu s podmínkou zastavení.',
              ),
              'correct' => 2,
              'explanation' => 'Chyba zasáhne jen malou část uživatelů.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš plán změny pro aktualizaci aplikace v telefonu rodiče (záloha, kritérium úspěchu, návrat).',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Konfiguraci měníme jen v simulátoru Linux Labu.',
          1 => 'Na skutečném serveru se změny dělají v domluveném okně a se schváleným plánem.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: restart po neúspěšném testu „pro jistotu“ – shodí službu úplně.',
          1 => 'Otázka do třídy: Podle jakého čísla poznáte, že máte nasazení zastavit (canary)?',
          2 => 'Tempo: chybná změna a rollback jsou jádro – plán vyplňuj stručně.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.',
          1 => 'Plán B offline: vytištěná konfigurace před a po, žáci najdou chybu a napíší postup návratu.',
        ),
        'glossary' => 
        array (
          0 => 'rollback',
          1 => 'canary',
        ),
        '_file' => 'lesson_content_v76_4a_b.php',
      ),
      25 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 25 · Odchylka konfigurace a infrastruktura jako kód',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím odhalit odchylku skutečné konfigurace od požadovaného stavu porovnáním otisků a příkazem diff a vysvětlit, proč je repozitář s konfigurací jediný zdroj pravdy a proč musí být automatizace idempotentní.',
          'success_criteria' => 
          array (
            0 => 'Porovnám otisk SHA-256 požadované a skutečné konfigurace a odchylku doložím výpisem diff.',
            1 => 'Vysvětlím, proč se ruční změna na serveru musí vrátit do repozitáře, nebo zahodit.',
            2 => 'Rozliším idempotentní krok (opakování nic nezmění) od neidempotentního.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_iac',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: „u mě to funguje“',
            'teacher' => 'Popíše dva servery se „stejnou“ konfigurací, z nichž jeden selhává.',
            'student' => 'Hádají, co se liší.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Požadovaný vs. skutečný stav',
            'teacher' => 'Vysvětlí zdroj pravdy a odchylku.',
            'student' => 'Zapíší, kde je požadovaný stav jejich služby.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Otisky a diff',
            'teacher' => 'Předvede sha256sum a diff.',
            'student' => 'Najdou odchylku po ruční změně.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 62,
            'phase' => 'Idempotence',
            'teacher' => 'Porovná krok „přidej řádek“ a „zajisti, že řádek existuje“.',
            'student' => 'Roztřídí kroky na idempotentní a ne.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Proces změn',
            'teacher' => 'Ukáže postup: změna v repozitáři → revize → plán → nasazení.',
            'student' => 'Navrhnou, jak odchylku řešit.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: změna patří do repozitáře.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spočítej otisk SHA-256 konfigurace webu a zapiš ho jako „požadovaný stav“.',
            'output' => 'Otisk začínající 317e7529….',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sha256sum /etc/nginx/sites-enabled/default',
                'expect' => '317e7529728a0f8b',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Proveď ruční změnu na serveru (index.html → index.htm), spočítej otisk znovu a odchylku dolož výpisem diff proti záloze.',
            'output' => 'Nový otisk 101fd394… a diff s řádkem index index.htm.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo sed -i \'s/index index.html/index index.htm/\' /etc/nginx/sites-enabled/default; sha256sum /etc/nginx/sites-enabled/default',
                'expect' => '101fd39451648f77',
              ),
              1 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/sites-enabled/default /tmp/pozadovany; sudo sed -i \'s/index index.html/index index.htm/\' /etc/nginx/sites-enabled/default; diff /tmp/pozadovany /etc/nginx/sites-enabled/default',
                'expect' => 'index index.htm;',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Roztřiď pět kroků automatizace na idempotentní a neidempotentní (např. „přidej řádek na konec souboru“ vs. „zajisti, že soubor obsahuje řádek“).',
            'output' => 'Tabulka kroků se zdůvodněním.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni postup, jak odchylku vyřešit: vrátit server do požadovaného stavu, nebo změnu převzít do repozitáře přes revizi – a kdo rozhoduje.',
            'output' => 'Postup ve 4–6 krocích s rolí a ověřením otiskem.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku požadovaný/skutečný stav a pět kroků s nápovědou.',
          'standard' => 'Otisky, diff, idempotence a postup řešení odchylky.',
          'challenge' => 'Popíše, jak by se odchylka hlídala automaticky každou noc a co by se stalo při nálezu (blast radius změny).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže dva otisky, třída řekne, jestli je odchylka.',
            1 => 'Dvojice: je krok „mkdir -p“ idempotentní?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Odhalení odchylky',
              'levels' => 
              array (
                0 => 'Neumí.',
                1 => 'Jen otisk.',
                2 => 'Otisk a diff s vysvětlením.',
                3 => 'Navrhne automatickou kontrolu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Idempotence',
              'levels' => 
              array (
                0 => 'Nezná.',
                1 => 'Zaměňuje.',
                2 => 'Správné roztřídění.',
                3 => 'Přepíše neidempotentní krok.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Proces',
              'levels' => 
              array (
                0 => 'Ruční opravy.',
                1 => 'Bez revize.',
                2 => 'Repozitář, revize, ověření.',
                3 => 'Rozhodnutí s rolí a záznamem.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_iac',
          'competence_label' => 'Infrastruktura jako kód a odchylka',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co znamená, že server má odchylku konfigurace (drift)?',
              'options' => 
              array (
                0 => 'Rozdíl mezi požadovaným stavem v repozitáři a skutečným stavem serveru.',
                1 => 'Pomalé načítání konfigurace.',
                2 => 'Chyba v názvu souboru.',
              ),
              'correct' => 0,
              'explanation' => 'Vzniká typicky ruční změnou mimo proces.',
            ),
            1 => 
            array (
              'question' => 'Který krok je idempotentní?',
              'options' => 
              array (
                0 => 'Přidej řádek na konec souboru.',
                1 => 'Zvyš číslo verze o 1.',
                2 => 'Zajisti, že adresář /srv/web existuje (mkdir -p).',
              ),
              'correct' => 2,
              'explanation' => 'Opakované spuštění nic dalšího nezmění.',
            ),
            2 => 
            array (
              'question' => 'Někdo opravil konfiguraci ručně na serveru. Co je správně?',
              'options' => 
              array (
                0 => 'Nechat to tak, funguje to.',
                1 => 'Změnu buď převzít do repozitáře přes revizi, nebo server vrátit do požadovaného stavu.',
                2 => 'Smazat repozitář.',
              ),
              'correct' => 1,
              'explanation' => 'Repozitář musí zůstat jediným zdrojem pravdy.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi doma „ruční odchylku“ (např. nastavení, které jsi změnil a nikde nezapsal) a zapiš ho.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Změny konfigurace jen v simulátoru Linux Labu.',
          1 => 'Do repozitáře s konfigurací nepatří hesla ani klíče – patří do správce tajemství.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „otisk se liší, tak je to rozbité“ – odchylka může být i chtěná, rozhoduje proces.',
          1 => 'Otázka do třídy: Proč je IaC bez revize jen rychlejší způsob, jak udělat chybu?',
          2 => 'Tempo: otisk a diff jsou rychlé; víc času dej idempotenci a procesu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.',
          1 => 'Plán B offline: dva vytištěné konfigurační soubory, hledání odchylky ručně.',
        ),
        'glossary' => 
        array (
          0 => 'drift',
          1 => 'idempotence',
        ),
        '_file' => 'lesson_content_v76_4a_b.php',
      ),
      26 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 26 · Výkon a kapacita: měřit, spočítat rezervu, rozhodnout',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím přečíst využití CPU a paměti, z měření zátěžového testu spočítat propustnost a kapacitní rezervu a rozhodnout, jestli server škálovat, nebo optimalizovat.',
          'success_criteria' => 
          array (
            0 => 'Z free a top přečtu dostupnou paměť a zatížení CPU.',
            1 => 'Spočítám kapacitní rezervu: maximální propustnost proti očekávané špičce.',
            2 => 'Rozhodnutí o škálování zdůvodním čísly a zvolím cílovou rezervu (např. 30 %).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_performance',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: přijímačky na webu',
            'teacher' => 'Popíše den, kdy se na web přihlásí 10× víc lidí než obvykle.',
            'student' => 'Odhadnou, co musíme vědět předem.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Měření systému',
            'teacher' => 'Předvede free, top a lscpu.',
            'student' => 'Přečtou paměť, zatížení a počet CPU.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Výsledky zátěžového testu',
            'teacher' => 'Rozdá tabulku: požadavky za sekundu vs. p95 a chybovost.',
            'student' => 'Najdou bod, kde latence prudce roste.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 62,
            'phase' => 'Rezerva',
            'teacher' => 'Ukáže výpočet rezervy a cílové hodnoty.',
            'student' => 'Spočítají rezervu pro očekávanou špičku.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Rozhodnutí',
            'teacher' => 'Porovná škálování a optimalizaci.',
            'student' => 'Napíší doporučení se zdůvodněním.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: kapacita se plánuje čísly.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zjisti dostupnou paměť, zatížení a počet CPU serveru a zapiš, jestli je server teď vytížený.',
            'output' => 'Dostupné 3,0 GiB z 3,8 GiB, load 0,08 při 2 CPU – nevytížený.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'free -h',
                'expect' => '3.0Gi',
              ),
              1 => 
              array (
                'cmd' => 'top -b -n 1 | head -n 3',
                'expect' => 'Tasks:   9 total',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'V tabulce zátěžového testu (100, 200, 300, 400 požadavků/s; p95 80, 90, 140, 900 ms) najdi bod, kde latence prudce roste, a urči maximální použitelnou propustnost.',
            'output' => 'Zlom mezi 300 a 400 req/s; použitelné maximum ≈ 300 req/s.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Spočítej kapacitní rezervu pro očekávanou špičku 240 požadavků/s při maximu 300 požadavků/s a porovnej ji s cílem 30 %.',
            'output' => 'Rezerva (300 − 240) / 300 = 20 % – pod cílem 30 %.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Napiš doporučení: škálovat (víc instancí), nebo optimalizovat (např. cache) – s čísly, rizikem a způsobem ověření novým testem.',
            'output' => 'Doporučení na půl strany se zdůvodněním a plánem ověření.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzorec rezervy a tabulku testu s vyznačenými řádky.',
          'standard' => 'Měření, zlom v testu, rezerva a doporučení.',
          'challenge' => 'Spočítá, kolik instancí je potřeba pro špičku 600 req/s při rezervě 30 % a maximu 300 req/s na instanci.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže řádek free, třída řekne dostupnou paměť.',
            1 => 'Dvojice: kde v tabulce testu začíná latence růst prudce?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Měření',
              'levels' => 
              array (
                0 => 'Nečte.',
                1 => 'Jen jedna hodnota.',
                2 => 'Paměť, zatížení, CPU s výkladem.',
                3 => 'Spojí s latencí a chybami.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Výpočet rezervy',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'S chybou.',
                2 => 'Správně s porovnáním s cílem.',
                3 => 'I počet instancí pro růst.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rozhodnutí',
              'levels' => 
              array (
                0 => 'Bez zdůvodnění.',
                1 => 'Dojem.',
                2 => 'Čísla, riziko, ověření.',
                3 => 'Srovnání dvou variant s náklady.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_performance',
          'competence_label' => 'Výkon a kapacita',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Maximum je 500 req/s, očekávaná špička 400 req/s. Jaká je rezerva?',
              'options' => 
              array (
                0 => '25 %',
                1 => '80 %',
                2 => '20 %',
              ),
              'correct' => 2,
              'explanation' => '(500 − 400) / 500 = 0,2 = 20 %.',
            ),
            1 => 
            array (
              'question' => 'Při 400 req/s skočí p95 z 140 ms na 900 ms. Co to znamená?',
              'options' => 
              array (
                0 => 'Server se blíží saturaci a fronty rostou.',
                1 => 'Test je chybný.',
                2 => 'Server je rychlejší.',
              ),
              'correct' => 0,
              'explanation' => 'Prudký růst latence = blízko kapacitního limitu.',
            ),
            2 => 
            array (
              'question' => 'Proč nechávat kapacitní rezervu (headroom)?',
              'options' => 
              array (
                0 => 'Aby server nic nedělal.',
                1 => 'Pro nečekané špičky a výpadek části systému.',
                2 => 'Protože to vyžaduje DNS.',
              ),
              'correct' => 1,
              'explanation' => 'Rezerva absorbuje špičky a selhání.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: odhadni, kolik lidí najednou otevře web školy v den zveřejnění výsledků přijímaček, a zdůvodni odhad.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zátěžové testy se pouštějí jen na vlastní testovací prostředí; zátěž cizí služby je útok.',
          1 => 'Měříme v simulátoru a z připravených dat.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: rezerva počítaná z očekávané zátěže místo z maxima – ukaž vzorec na tabuli.',
          1 => 'Otázka do třídy: Kdy je lepší optimalizovat než přidat server?',
          2 => 'Tempo: výpočty jsou krátké; víc času dej doporučení s čísly.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkol 1 v Linux Labu (výstup v zadání), úkoly 2–4 z vytištěné tabulky.',
          1 => 'Plán B offline: vytištěná tabulka testu a výpočty ručně.',
        ),
        'glossary' => 
        array (
          0 => 'headroom',
          1 => 'throughput',
        ),
        '_file' => 'lesson_content_v76_4a_b.php',
      ),
      27 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 27 · Velení incidentu: rozpočet chyb a rozhodování pod tlakem',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím jako velitel incidentu rozhodovat podle dat – spočítat, kolik rozpočtu chyb incident spotřeboval, zvolit mezi rychlou nápravou a rollbackem a vést komunikaci s vedením.',
          'success_criteria' => 
          array (
            0 => 'Spočítám měsíční rozpočet chyb (error budget) z SLO (např. 99,5 % za 30 dní = 216 minut).',
            1 => 'Rozhodnutí (náprava, rollback, eskalace) zdůvodním dopadem a rozpočtem chyb.',
            2 => 'Zpráva pro vedení má dopad, stav, rozhodnutí a čas další zprávy.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_incident',
            'level' => 4,
          ),
          1 => 
          array (
            'id' => 'ops_observability',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: tlak vedení',
            'teacher' => 'Hraje vedení, které chce „hned opravit“.',
            'student' => 'Zapíší, co velitel potřebuje vědět.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'SLO a rozpočet chyb',
            'teacher' => 'Vysvětlí SLI, SLO a rozpočet chyb.',
            'student' => 'Spočítají rozpočet pro dvě SLO.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 48,
            'phase' => 'Cvičný incident',
            'teacher' => 'Postupně odhaluje fakta incidentu.',
            'student' => 'Velitel rozhoduje, tým sbírá důkazy.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 48,
            'to' => 62,
            'phase' => 'Rozhodnutí',
            'teacher' => 'Nabídne volby: náprava, rollback, eskalace.',
            'student' => 'Zvolí a zdůvodní daty.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Zpráva pro vedení',
            'teacher' => 'Ukáže vzor krátké zprávy.',
            'student' => 'Napíší zprávu a spotřebu rozpočtu.',
            'form' => 've skupinách',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: rozhoduj daty, komunikuj pravidelně.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Spočítej měsíční rozpočet chyb pro SLO 99,5 % a 99,9 % dostupnosti za 30 dní v minutách.',
            'output' => '99,5 % → 216 min; 99,9 % → 43,2 min.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Jako tým zjisti z přístupového logu, na kterou stránku mířily odpovědi 404, a rozhodni, jestli jde o incident, nebo chybný odkaz.',
            'output' => 'Požadavky GET /stara-stranka.html z 10.0.0.31 – starý odkaz, ne výpadek služby.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep \' 404 \' /var/log/nginx/access.log | head -n 2',
                'expect' => 'GET /stara-stranka.html',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Incident trval 45 minut s úplným výpadkem. Spočítej, kolik procent měsíčního rozpočtu (SLO 99,5 %) spotřeboval, a rozhodni, jestli zmrazit nové změny.',
            'output' => '45 / 216 ≈ 21 % rozpočtu; zdůvodněné rozhodnutí o zmrazení.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Napiš zprávu pro vedení do 5 vět: dopad, stav, rozhodnutí, spotřeba rozpočtu chyb a čas další zprávy.',
            'output' => 'Zpráva bez technického žargonu a bez jmen.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzorec rozpočtu chyb a šablonu zprávy.',
          'standard' => 'Rozpočet, analýza logu, spotřeba rozpočtu a zpráva.',
          'challenge' => 'Navrhne pravidlo: při jaké spotřebě rozpočtu se zastaví nasazování a kdo může udělit výjimku.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel řekne SLO, třída spočítá rozpočet v minutách.',
            1 => 'Skupiny si přečtou zprávy navzájem: je jasné, co se rozhodlo?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Rozpočet chyb',
              'levels' => 
              array (
                0 => 'Nespočítá.',
                1 => 'S chybou.',
                2 => 'Správně pro obě SLO.',
                3 => 'Spočítá i spotřebu incidentu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Rozhodnutí',
              'levels' => 
              array (
                0 => 'Bez důvodu.',
                1 => 'Podle pocitu.',
                2 => 'Podle dopadu a rozpočtu.',
                3 => 'Zváží i riziko rozhodnutí.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Zpráva pro vedení',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Technický žargon.',
                2 => 'Dopad, stav, rozhodnutí, čas.',
                3 => 'Stručná a srozumitelná do 5 vět.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_observability',
          'competence_label' => 'Pozorovatelnost a SLO',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'SLO dostupnosti je 99,9 % za 30 dní. Kolik minut výpadku si služba smí dovolit?',
              'options' => 
              array (
                0 => '432 minut',
                1 => '43,2 minuty',
                2 => '4,3 minuty',
              ),
              'correct' => 1,
              'explanation' => '0,1 % z 43 200 minut = 43,2 minuty.',
            ),
            1 => 
            array (
              'question' => 'Rozpočet chyb je na konci měsíce skoro vyčerpaný. Co je rozumné?',
              'options' => 
              array (
                0 => 'Zrychlit nasazování nových funkcí.',
                1 => 'Vypnout monitoring.',
                2 => 'Omezit riskantní změny a soustředit se na spolehlivost.',
              ),
              'correct' => 2,
              'explanation' => 'Rozpočet chyb řídí tempo změn.',
            ),
            2 => 
            array (
              'question' => 'Co je hlavní úkol velitele incidentu?',
              'options' => 
              array (
                0 => 'Koordinovat tým, rozhodovat a zajistit komunikaci.',
                1 => 'Sám opravit všechny chyby.',
                2 => 'Najít viníka.',
              ),
              'correct' => 0,
              'explanation' => 'Velitel řídí, technici opravují.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: spočítej rozpočet chyb pro SLO 99 % za týden a porovnej ho s 99,9 %.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Incident a logy jsou ze simulátoru; žádné skutečné služby se nezastavují.',
          1 => 'Zprávy neobsahují jména, adresy uživatelů ani technické tajnosti.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: velitel incidentu sám píše příkazy – drž ho v roli koordinátora.',
          1 => 'Otázka do třídy: Kdy je rollback lepší než rychlá oprava?',
          2 => 'Tempo: výpočty rozpočtu na začátku, ať je mají při rozhodování; vedení hraj s mírným tlakem.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkol 2 v Linux Labu (výstup v zadání), ostatní na papír podle vzorců.',
          1 => 'Plán B offline: incident podle karet s fakty, výpočty a zpráva na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'error-budget',
          1 => 'slo',
        ),
        '_file' => 'lesson_content_v76_4a_b.php',
      ),
      28 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 28 · Ověření dovedností: spolehlivý provoz v kostce',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím v jednom scénáři propojit pozorovatelnost, bezpečnou změnu a řízení incidentu – každou část doložit důkazem – a podle kompetencí ročníku určit své silné stránky a mezery.',
          'success_criteria' => 
          array (
            0 => 'Ve scénáři doložím stav služby, test konfigurace a ověření z pohledu uživatele.',
            1 => 'Mapa jedenácti kompetencí ročníku má u každé důkaz, nebo pojmenovanou mezeru.',
            2 => 'Napíšu plán na rozvoj jedné kompetence s měřitelným výsledkem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_change',
            'level' => 4,
          ),
          1 => 
          array (
            'id' => 'ops_observability',
            'level' => 4,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: mapa roku',
            'teacher' => 'Promítne kompetence ročníku a scénář ověření.',
            'student' => 'Odhadnou své nejsilnější oblasti.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 45,
            'phase' => 'Scénář',
            'teacher' => 'Hlídá důkazy a čas.',
            'student' => 'Projdou scénář: signály, změna s rollbackem, ověření.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 45,
            'to' => 60,
            'phase' => 'Krátký rozbor',
            'teacher' => 'Připomene osnovu rozboru.',
            'student' => 'Sepíší rozbor scénáře.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 60,
            'to' => 75,
            'phase' => 'Mapa kompetencí',
            'teacher' => 'Vysvětlí hodnocení podle důkazů.',
            'student' => 'Vyplní mapu kompetencí.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 75,
            'to' => 85,
            'phase' => 'Plán rozvoje',
            'teacher' => 'Ukáže příklad měřitelného plánu.',
            'student' => 'Napíší plán rozvoje.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 85,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne rok.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zjisti stav webové služby, otestuj konfiguraci a ověř odpověď z pohledu uživatele; ke každému kroku zapiš důkaz.',
            'output' => 'active (running), test is successful, HTTP/1.1 200 OK.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'active (running)',
              ),
              1 => 
              array (
                'cmd' => 'sudo nginx -t',
                'expect' => 'test is successful',
              ),
              2 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => 'HTTP/1.1 200 OK',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Proveď chybnou změnu konfigurace s rollbackem a zapiš, jak dlouho by služba byla ohrožená, kdybys změnu nasadil(a) bez testu.',
            'output' => 'Rollback ověřený testem syntax is ok a odhad dopadu.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/sites-enabled/default /tmp/default.bak; sudo sed -i \'s/try_files/try_filez/\' /etc/nginx/sites-enabled/default; sudo cp /tmp/default.bak /etc/nginx/sites-enabled/default; sudo nginx -t',
                'expect' => 'test is successful',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Sepiš krátký rozbor scénáře: co signály ukázaly, jak test změny zabránil výpadku a jedno opatření.',
            'output' => 'Rozbor do 8 vět s jedním měřitelným opatřením.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Vyplň mapu jedenácti kompetencí ročníku (důkaz nebo mezera) a napiš plán rozvoje jedné kompetence s měřitelným výsledkem.',
            'output' => 'Mapa kompetencí a plán: co, jak, do kdy, jak poznám zlepšení.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartu scénáře s pořadím příkazů a šablonu mapy kompetencí.',
          'standard' => 'Scénář s důkazy, rozbor, mapa a plán.',
          'challenge' => 'Doplní do scénáře návrh SLO a upozornění, které by problém odhalily dřív než uživatel.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Dvojice si navzájem potvrzují důkazy ze scénáře.',
            1 => 'Učitel přečte plán a třída posoudí, jestli je měřitelný.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Scénář s důkazy',
              'levels' => 
              array (
                0 => 'Bez důkazů.',
                1 => 'Část kroků.',
                2 => 'Všechny kroky s důkazem.',
                3 => 'Důkazy seřazené a vysvětlené.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Rozbor',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez opatření.',
                2 => 'Signály, změna, opatření.',
                3 => 'Opatření měřitelné s vlastníkem.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Mapa a plán',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Podle pocitu.',
                2 => 'Podle důkazů s plánem.',
                3 => 'Plán s měřítkem a termínem.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_change',
          'competence_label' => 'Řízená změna s plánem návratu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jaké pořadí kroků je při změně konfigurace správné?',
              'options' => 
              array (
                0 => 'Záloha → změna → test konfigurace → nasazení → ověření.',
                1 => 'Změna → nasazení → záloha.',
                2 => 'Nasazení → test → změna.',
              ),
              'correct' => 0,
              'explanation' => 'Záloha a test chrání před výpadkem.',
            ),
            1 => 
            array (
              'question' => 'Který důkaz nejlépe potvrdí, že služba po změně funguje?',
              'options' => 
              array (
                0 => 'Že příkaz proběhl bez chyby.',
                1 => 'Že je server zapnutý.',
                2 => 'Odpověď 200 z pohledu uživatele a čistý log.',
              ),
              'correct' => 2,
              'explanation' => 'Ověřujeme to, co cítí uživatel.',
            ),
            2 => 
            array (
              'question' => 'Který plán rozvoje je měřitelný?',
              'options' => 
              array (
                0 => 'Zlepším se v provozu.',
                1 => 'Do konce května vyřeším 5 incidentů v Linux Labu s rozborem a ověřím se exit ticketem.',
                2 => 'Budu víc číst.',
              ),
              'correct' => 1,
              'explanation' => 'Konkrétní výstup, termín a ověření.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: splň první krok plánu rozvoje a zapiš, co ti ukázal.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Scénář běží v simulátoru Linux Labu; žádné skutečné systémy.',
          1 => 'Mapa kompetencí je osobní – vidí ji žák a jeho učitel.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: sebehodnocení bez důkazu – každá kompetence potřebuje odkaz na práci nebo výstup.',
          1 => 'Otázka do třídy: Který návyk z provozu si odnesete i mimo IT?',
          2 => 'Tempo: scénář drž do 45. minuty; mapa a plán jsou povinný výstup.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.',
          1 => 'Plán B offline: scénář podle vytištěných výpisů, mapa a plán na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'rollback',
          1 => 'sli',
        ),
        '_file' => 'lesson_content_v76_4a_b.php',
      ),
      1 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 1 · Start: řízený rozbor výpadku od vrstvy k vrstvě',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím projít výpadek webu od linky po službu, ke každé hypotéze najít jeden ověřovací příkaz a příčinu doložit výstupem.',
          'success_criteria' => 
          array (
            0 => 'Sestavím aspoň tři hypotézy seřazené od nejnižší vrstvy.',
            1 => 'Ke každé hypotéze uvedu příkaz a výstup, který ji potvrdil nebo vyloučil.',
            2 => 'Příčinu popíšu jednou větou s opravou a prevencí.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_network_diag',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'ops_incident',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Hlášení výpadku',
            'teacher' => 'Přečte hlášení uživatele „intranet nejde“ a rozdá role řešitel a zapisovatel.',
            'student' => 'Zapíší příznak, čas a rozsah dopadu.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Hypotézy po vrstvách',
            'teacher' => 'Připomene pořadí linka → adresace → směrování → DNS → služba.',
            'student' => 'Sepíší tři hypotézy a ke každé jeden ověřovací příkaz.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 45,
            'phase' => 'Spodní vrstvy',
            'teacher' => 'Hlídá, aby každý závěr měl výstup.',
            'student' => 'Ověří adresu, trasu, bránu a překlad jména.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Služba',
            'teacher' => 'Nasimuluje výpadek služby a nechá dvojice najít důkaz.',
            'student' => 'Najdou příčinu, službu obnoví a ověří odpověď.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Rozbor bez obviňování',
            'teacher' => 'Ukáže osnovu rozboru: příznak, příčina, důkaz, oprava, prevence.',
            'student' => 'Sepíší krátký rozbor výpadku.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: hypotéza bez důkazu se nepočítá.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zapiš příznak výpadku tak, jak ho nahlásil uživatel, urči rozsah dopadu a sestav tři hypotézy od nejnižší vrstvy nahoru, každou s jedním ověřovacím příkazem.',
            'output' => 'Tabulka hypotéza → vrstva → příkaz → co výstup potvrdí nebo vyloučí.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Ověř spodní vrstvy: trasu a výchozí bránu – gateway (ip r) a dosažitelnost brány (ping -c 2); zapiš, kterou hypotézu výstupy vyloučily.',
            'output' => 'Výstupy s default via 10.0.0.1 a 0 % ztrát a vyškrtnutá hypotéza o síti.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'ip r',
                'expect' => 'default via 10.0.0.1',
              ),
              1 => 
              array (
                'cmd' => 'ping -c 2 10.0.0.1',
                'expect' => '0% packet loss',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř překlad jména a odpověď služby: dig +short intranet.skola.test a curl -v http://localhost; u každého výstupu napiš, co dokazuje.',
            'output' => 'Adresa 10.0.0.10 a odpověď HTTP/1.1 200 OK s komentářem.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig +short intranet.skola.test',
                'expect' => '10.0.0.10',
              ),
              1 => 
              array (
                'cmd' => 'curl -v http://localhost',
                'expect' => 'HTTP/1.1 200 OK',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Nasimuluj výpadek: zastav nginx, ukaž důkaz příčiny (spojení na port 80 selže), službu znovu spusť a ověř, že je aktivní; pak sepiš krátký postmortem.',
            'output' => 'Výstup „Couldn\'t connect to server“, po startu stav active a rozbor: příznak, příčina, důkaz, oprava, prevence.',
            'time' => '25 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; curl -I http://localhost',
                'expect' => 'Couldn\'t connect to server',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; systemctl is-active nginx',
                'expect' => 'active',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku hypotéz s předvyplněnými vrstvami a seznam příkazů k výběru.',
          'standard' => 'Hypotézy po vrstvách, ověření sítě, DNS a služby, nácvik výpadku a rozbor.',
          'challenge' => 'Doplní do rozboru metriku, která by výpadek odhalila dřív než uživatel, a práh upozornění.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: DNS odpovídá a brána také – kam pokračuješ?',
            1 => 'Kontrola souseda: má každá hypotéza výstup, který ji rozhodl?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Hypotézy',
              'levels' => 
              array (
                0 => 'Bez hypotéz.',
                1 => 'Hypotézy bez pořadí.',
                2 => 'Tři hypotézy od nejnižší vrstvy.',
                3 => 'U každé předem napíše, co ji vyvrátí.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Důkazy',
              'levels' => 
              array (
                0 => 'Bez výstupů.',
                1 => 'Výstupy bez závěru.',
                2 => 'Ke každé hypotéze výstup a závěr.',
                3 => 'Rozliší potvrzení a vyloučení hypotézy.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rozbor',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen popis příznaku.',
                2 => 'Příznak, příčina, důkaz, oprava, prevence.',
                3 => 'Prevence je měřitelná (metrika, práh).',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_incident',
          'competence_label' => 'Provoz: vedení incidentu a rozbor',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Brána odpovídá a jméno se přeloží. Kterou vrstvu ověříš jako další?',
              'options' => 
              array (
                0 => 'Kabel ke stanici.',
                1 => 'Masku sítě.',
                2 => 'Službu: jestli naslouchá na portu a odpovídá.',
              ),
              'correct' => 2,
              'explanation' => 'Spodní vrstvy jsou vyloučené – zbývá služba.',
            ),
            1 => 
            array (
              'question' => 'curl na port 80 vrací „Couldn\'t connect to server“. Co je nejsilnější hypotéza?',
              'options' => 
              array (
                0 => 'Na portu 80 nic nenaslouchá – služba neběží.',
                1 => 'Chybí záznam DNS.',
                2 => 'Server má málo paměti.',
              ),
              'correct' => 0,
              'explanation' => 'Odmítnuté spojení znamená, že na portu není služba.',
            ),
            2 => 
            array (
              'question' => 'Co do rozboru výpadku nepatří?',
              'options' => 
              array (
                0 => 'Důkaz, který potvrdil příčinu.',
                1 => 'Jméno člověka, který udělal chybu.',
                2 => 'Opatření, aby se výpadek neopakoval.',
              ),
              'correct' => 1,
              'explanation' => 'Rozbor je bez obviňování – řeší systém, ne viníka.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyber výpadek známé služby z médií a podle osnovy z hodiny odhadni hypotézy po vrstvách (bez jmen lidí).',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Výpadek simulujeme jen v Linux Labu; na školních serverech služby nezastavujeme.',
          1 => 'Rozbory píšeme bez jmen a bez obviňování.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: skok rovnou na restart služby bez důkazu – trvej na výstupu před opravou.',
          1 => 'Otázka do třídy: Jak poznáš, že hypotéza je vyloučená, a ne jen neověřená?',
          2 => 'Tempo: nácvik výpadku je nejdelší úsek; rozbor lze dopsat doma.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2–4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání), úkol 1 na papír.',
          1 => 'Plán B offline: vytištěné výstupy příkazů, žáci podle nich rozhodují hypotézy.',
        ),
        'glossary' => 
        array (
          0 => 'postmortem',
          1 => 'gateway',
        ),
        '_file' => 'lesson_content_v77_4a.php',
      ),
      2 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 2 · Změnové okno: proxy, TLS, logy a plán návratu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím připravit řízenou změnu webového serveru: zálohu a kontrolu konfigurace, reload bez výpadku, ověření a jednoznačný krok návratu.',
          'success_criteria' => 
          array (
            0 => 'Před změnou zálohuji konfiguraci a ověřím syntaxi.',
            1 => 'Rozliším reload od restartu a po změně ověřím službu z pohledu uživatele.',
            2 => 'Plán návratu má jeden jednoznačný krok a podmínku, kdy ho použiji.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_change',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'ops_proxy_tls',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 12,
            'phase' => 'Změnový plán',
            'teacher' => 'Ukáže vzor změnového tiketu: rozsah, prechecky, kritéria úspěchu, návrat.',
            'student' => 'Doplní tiket pro změnu „web za reverzní proxy“.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 12,
            'to' => 27,
            'phase' => 'Záloha a syntaxe',
            'teacher' => 'Předvede zálohu konfigurace a nginx -t.',
            'student' => 'Zálohují konfiguraci a ověří syntaxi.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 27,
            'to' => 42,
            'phase' => 'Reverzní proxy',
            'teacher' => 'Rozebere server blok a location s proxy_pass na upstream.',
            'student' => 'Navrhnou blok location pro aplikaci na 127.0.0.1:8080.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 58,
            'phase' => 'Reload a ověření',
            'teacher' => 'Vysvětlí, proč reload nepřeruší spojení.',
            'student' => 'Provedou reload a ověří stav služby a odpověď 200.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 80,
            'phase' => 'Logy a TLS',
            'teacher' => 'Ukáže korelaci access a error logu a kontrolu certifikátu (platnost, SAN).',
            'student' => 'Najdou chybové požadavky a sepíší kontrolu certifikátu a krok návratu.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: změna bez plánu návratu není připravená.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Před změnou zazálohuj konfiguraci nginx do souboru .bak a ověř syntaxi příkazem nginx -t.',
            'output' => 'Soubor nginx.conf.bak ve výpisu /etc/nginx a hláška „syntax is ok“.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak; ls /etc/nginx',
                'expect' => 'nginx.conf.bak',
              ),
              1 => 
              array (
                'cmd' => 'nginx -t',
                'expect' => 'syntax is ok',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Přečti konfiguraci webu v sites-enabled/default a navrhni blok location s proxy_pass, který pošle požadavky na upstream aplikace na 127.0.0.1:8080.',
            'output' => 'Blok location / { proxy_pass http://127.0.0.1:8080; } a věta, co se změní pro uživatele (reverse proxy).',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'cat /etc/nginx/sites-enabled/default',
                'expect' => 'try_files',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Proveď reload služby, ověř, že je aktivní, a že web odpovídá stavem 200; vysvětli, proč jsi nepoužil/a restart.',
            'output' => 'Stav active, HTTP 200 a věta o rozdílu reload / restart.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl reload nginx; systemctl is-active nginx',
                'expect' => 'active',
              ),
              1 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => '200 OK',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Najdi v access logu požadavky s chybou 404 a v error logu poslední záznam; sepiš kontrolu certifikátu TLS (platnost, SAN pro app.example.cz) a jednoznačný krok rollback.',
            'output' => 'Chybné požadavky s časem, poslední řádek error logu, kontrolní seznam TLS a krok návratu „obnov nginx.conf.bak, nginx -t, reload“.',
            'time' => '25 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep 404 /var/log/nginx/access.log',
                'expect' => '404 162',
              ),
              1 => 
              array (
                'cmd' => 'sudo tail -n 3 /var/log/nginx/error.log',
                'expect' => 'signal process started',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane předvyplněný změnový tiket a vzor bloku location; doplňuje adresu upstreamu a kroky ověření.',
          'standard' => 'Záloha a kontrola syntaxe, návrh proxy, reload s ověřením, logy, kontrola TLS a návrat.',
          'challenge' => 'Doplní do tiketu kritérium úspěchu s číslem (chybovost, doba odpovědi) a podmínku, kdy návrat spustí.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: nginx -t hlásí chybu – smíš udělat reload?',
            1 => 'Kontrola souseda: dá se krok návratu provést bez přemýšlení?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Příprava změny',
              'levels' => 
              array (
                0 => 'Bez zálohy.',
                1 => 'Záloha bez kontroly syntaxe.',
                2 => 'Záloha i kontrola syntaxe.',
                3 => 'Prechecky s očekávaným výsledkem.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Provedení a ověření',
              'levels' => 
              array (
                0 => 'Restart bez ověření.',
                1 => 'Reload bez ověření.',
                2 => 'Reload, stav služby a HTTP 200.',
                3 => 'Ověří i z pohledu uživatele a logu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Plán návratu',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Obecný („vrátit to“).',
                2 => 'Jednoznačný krok.',
                3 => 'Krok s podmínkou spuštění a ověřením.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_change',
          'competence_label' => 'Provoz: řízená změna s plánem návratu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'nginx -t po úpravě hlásí chybu syntaxe. Co uděláš?',
              'options' => 
              array (
                0 => 'Přesto provedu reload, nginx chybu opraví.',
                1 => 'Restartuji server.',
                2 => 'Změnu neprovedu, opravím konfiguraci nebo vrátím zálohu.',
              ),
              'correct' => 2,
              'explanation' => 'S chybnou konfigurací se služba nemusí znovu spustit.',
            ),
            1 => 
            array (
              'question' => 'Proč při změně konfigurace volíš reload místo restartu?',
              'options' => 
              array (
                0 => 'Reload načte konfiguraci bez přerušení běžících spojení.',
                1 => 'Reload smaže logy.',
                2 => 'Restart nejde vrátit.',
              ),
              'correct' => 0,
              'explanation' => 'Reload je šetrnější – uživatelé výpadek nepoznají.',
            ),
            2 => 
            array (
              'question' => 'Který krok návratu je dobře napsaný?',
              'options' => 
              array (
                0 => '„Když se něco pokazí, vrátíme to.“',
                1 => '„Obnov nginx.conf.bak, ověř nginx -t a proveď reload.“',
                2 => '„Zavolej správce.“',
              ),
              'correct' => 1,
              'explanation' => 'Krok návratu musí jít provést přesně a bez přemýšlení.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš změnový tiket pro libovolnou změnu, kterou jsi doma dělal/a (aktualizace, nový router) – rozsah, kontrola a krok návratu.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Konfiguraci měníme jen v simulátoru Linux Labu; adresy a domény jsou ukázkové.',
          1 => 'Soukromé klíče certifikátů nikdy nekopírujeme do sešitu ani do chatu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „reload a restart jsou totéž“ – ukaž rozdíl na běžícím spojení.',
          1 => 'Otázka do třídy: Kdy je správné rozhodnutí změnu vůbec neprovést?',
          2 => 'Tempo: proxy_pass jen navrhují na papír – simulátor upstream na 8080 nespouští.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1, 3 a 4 v Linux Labu podle zadání, úkol 2 na papír podle vzoru.',
          1 => 'Plán B offline: vytištěný změnový tiket a výpis konfigurace k doplnění.',
        ),
        'glossary' => 
        array (
          0 => 'reverse-proxy',
          1 => 'upstream',
          2 => 'reload',
          3 => 'rollback',
          4 => 'tls',
        ),
        '_file' => 'lesson_content_v77_4a.php',
      ),
      3 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 3 · Pozorovatelnost: logy, metriky a kontrola zdraví',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím z logů a kontroly zdraví sestavit důkaz o stavu služby, spočítat chybovost a navrhnout tři ukazatele s cílem SLO.',
          'success_criteria' => 
          array (
            0 => 'Ke třem ukazatelům (latence, chybovost, dostupnost) zapíšu výchozí hodnotu a cíl.',
            1 => 'Z access logu spočítám podíl chybových odpovědí.',
            2 => 'Navrhnu endpoint /health a rozhodnu, kdo ho smí vidět.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_observability',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'ops_incident',
            'level' => 1,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 12,
            'phase' => 'Start: pocit vs. důkaz',
            'teacher' => 'Ukáže dvě hlášení „web je pomalý“ – jedno s čísly, jedno bez – a zeptá se, se kterým se dá pracovat.',
            'student' => 'Navrhnou, co by měli měřit.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 12,
            'to' => 28,
            'phase' => 'SLI a SLO',
            'teacher' => 'Vysvětlí ukazatel (SLI), cíl (SLO) a výchozí hodnotu.',
            'student' => 'Zapíší tři ukazatele s výchozí hodnotou a cílem.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 28,
            'to' => 48,
            'phase' => 'Chybovost z logu',
            'teacher' => 'Předvede počítání stavových kódů jedním řádkem.',
            'student' => 'Spočítají podíl odpovědí jiných než 200.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 48,
            'to' => 65,
            'phase' => 'Kontrola zdraví',
            'teacher' => 'Ukáže, že / vrací 200, ale /health zatím neexistuje.',
            'student' => 'Navrhnou /health a jeho odpověď a zabezpečení.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Korelace',
            'teacher' => 'Ukáže, jak spojit čas chyby v access logu se záznamem služby.',
            'student' => 'Najdou opakovanou chybu 403 a porovnají ji s žurnálem služby.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: měříme, než začneme opravovat.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vyber tři ukazatele (latence, chybovost, dostupnost), ke každému zapiš výchozí hodnotu a cíl SLO a vysvětli, čím se liší SLI od SLO.',
            'output' => 'Tabulka ukazatel → výchozí hodnota → cíl (např. 99,5 % dostupnost za měsíc) a věta o SLI a SLO.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Spočítej v access logu stavové kódy (sudo awk … | sort | uniq -c) a urči podíl odpovědí jiných než 200.',
            'output' => 'Počty 200: 15, 403: 6, 404: 9 → 15 z 30 požadavků (50 %) není úspěšných, s komentářem, které chyby jsou „naše“.',
            'time' => '18 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo awk \'{print $9}\' /var/log/nginx/access.log | sort | uniq -c',
                'expect' => '15 200',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověř kontrolu zdraví: curl na / vrací 200, ale /health vrací 404; navrhni, co má /health vracet a proč nesmí být veřejný s detaily.',
            'output' => 'Dva výstupy (200 a 404 Not Found) a návrh health check: stav 200 a krátký text, detaily jen pro monitoring.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => '200 OK',
              ),
              1 => 
              array (
                'cmd' => 'curl -s http://localhost/health',
                'expect' => '404 Not Found',
              ),
            ),
          ),
          3 => 
          array (
            'text' => 'Najdi v access logu opakované požadavky s chybou 403, zapiš jejich časy a porovnej je se žurnálem služby; rozhodni, zda jde o chybu služby, nebo o zamítnutý přístup.',
            'output' => 'Šest požadavků GET /admin se stavem 403 a závěr: služba běží, jde o zamítnutý přístup, ne výpadek.',
            'time' => '20 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo grep 403 /var/log/nginx/access.log',
                'expect' => 'GET /admin',
              ),
              1 => 
              array (
                'cmd' => 'journalctl -u nginx -n 5',
                'expect' => 'Started nginx.service',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku ukazatelů s vyplněnými názvy a kalkulačku podílu; doplňuje čísla.',
          'standard' => 'Tři ukazatele s cílem, chybovost z logu, návrh /health a korelace chyb.',
          'challenge' => 'Spočítá rozpočet chyb pro cíl 99,5 % za 30 dní v minutách a navrhne práh upozornění.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: 403 a 404 – jsou to výpadky služby?',
            1 => 'Kartičky: je „web je pomalý“ ukazatel, nebo pocit?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Ukazatele a cíle',
              'levels' => 
              array (
                0 => 'Bez ukazatelů.',
                1 => 'Ukazatele bez cíle.',
                2 => 'Tři ukazatele s výchozí hodnotou a cílem.',
                3 => 'Vysvětlí volbu cíle a rozpočet chyb.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Chybovost z logu',
              'levels' => 
              array (
                0 => 'Nespočítá.',
                1 => 'Spočítá kódy.',
                2 => 'Spočítá podíl chybových odpovědí.',
                3 => 'Rozliší chyby klienta a serveru.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Kontrola zdraví',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen ping.',
                2 => 'Návrh /health s odpovědí.',
                3 => 'Návrh i se zabezpečením a tím, co /health nekontroluje.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_observability',
          'competence_label' => 'Provoz: logy, metriky a cíle SLO',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Z 30 požadavků v logu je 15 se stavem 200. Jaká je chybovost?',
              'options' => 
              array (
                0 => '15 %',
                1 => '50 %',
                2 => '30 %',
              ),
              'correct' => 1,
              'explanation' => '15 z 30 požadavků nebylo úspěšných = 50 %.',
            ),
            1 => 
            array (
              'question' => 'Co je SLO?',
              'options' => 
              array (
                0 => 'Cílová hodnota ukazatele, např. 99,5 % dostupnost za měsíc.',
                1 => 'Program, který sbírá logy.',
                2 => 'Chybová hláška serveru.',
              ),
              'correct' => 0,
              'explanation' => 'SLI je měřený ukazatel, SLO jeho cíl.',
            ),
            2 => 
            array (
              'question' => 'V logu je šestkrát 403 pro /admin a služba běží. Co z toho plyne?',
              'options' => 
              array (
                0 => 'Služba padá a je třeba ji restartovat.',
                1 => 'Došlo místo na disku.',
                2 => 'Přístup byl zamítnut – nejde o výpadek, ale ověř, kdo se o /admin pokouší.',
              ),
              'correct' => 2,
              'explanation' => '403 = zakázáno; služba funguje, zamítla přístup.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyber aplikaci, kterou používáš, a navrhni pro ni tři ukazatele a cíl SLO (bez přihlašování a osobních údajů).',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Logy čteme jen v Linux Labu; ve skutečných logech jsou osobní údaje a ty bez důvodu neprocházíme ani nesdílíme.',
          1 => 'Endpoint /health nesmí veřejně ukazovat verze, cesty ani hesla.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: každá chyba v logu = výpadek – rozliš 4xx (klient) a 5xx (server).',
          1 => 'Otázka do třídy: Proč je cíl 100 % dostupnosti špatný cíl?',
          2 => 'Tempo: pojem observability stačí ukázat na příkladu, definice až na konci.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2–4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání), úkol 1 na papír.',
          1 => 'Plán B offline: vytištěný výřez access logu, žáci počítají chybovost ručně.',
        ),
        'glossary' => 
        array (
          0 => 'observability',
          1 => 'sli',
          2 => 'slo',
          3 => 'health-check',
        ),
        '_file' => 'lesson_content_v77_4a.php',
      ),
      4 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 4 · Nácvik vydání: kanárek, ověření a rozhodnutí o návratu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím provést vydání jako řízenou změnu: prechecky, postupné nasazení na část provozu, ověření podle podmínek zastavení a rozhodnutí pokračovat nebo vrátit.',
          'success_criteria' => 
          array (
            0 => 'Změnový tiket má rozsah, kritéria úspěchu, dvě podmínky zastavení a krok návratu.',
            1 => 'Prechecky ověřím výstupy (syntaxe, disk, paměť) před nasazením.',
            2 => 'Rozhodnutí pokračovat / vrátit podložím čísly z ověření.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'ops_change',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'ops_observability',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 12,
            'phase' => 'Změnový tiket',
            'teacher' => 'Zadá vydání nové verze webu a ukáže tiket s podmínkami zastavení.',
            'student' => 'Doplní rozsah, kritéria úspěchu a dvě podmínky zastavení.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 12,
            'to' => 28,
            'phase' => 'Prechecky',
            'teacher' => 'Předvede kontrolu syntaxe, disku a paměti.',
            'student' => 'Spustí prechecky a zapíší výchozí stav.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 28,
            'to' => 45,
            'phase' => 'Kanárek',
            'teacher' => 'Vysvětlí nasazení na 10 % provozu (canary) a proč se nespouští na všechny najednou.',
            'student' => 'Spočítají, kolik požadavků jde na novou verzi a kdy se překročí podmínka zastavení.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 62,
            'phase' => 'Ověření',
            'teacher' => 'Ukáže pořadí ověření: DNS, HTTP, služba, cesta uživatele.',
            'student' => 'Ověří nasazení z pohledu uživatele.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 62,
            'to' => 80,
            'phase' => 'Nácvik návratu',
            'teacher' => 'Vyhlásí „chybovost kanárka 12 %“ a nechá dvojice rozhodnout.',
            'student' => 'Provedou návrat konfigurace a ověří syntaxi i službu.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Rozhodnutí a exit ticket',
            'teacher' => 'Shrne: rozhoduje předem dané číslo, ne nálada.',
            'student' => 'Zapíší rozhodnutí s důkazem a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš změnový tiket vydání: rozsah, tři kritéria úspěchu, dvě podmínky zastavení (např. chybovost nad 3 %) a jednoznačný krok rollback.',
            'output' => 'Tiket s čísly v kritériích i podmínkách zastavení a krokem návratu.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Spusť prechecky před nasazením: syntaxe konfigurace, volné místo na disku a paměť; zapiš výchozí hodnoty do tiketu.',
            'output' => 'Výstupy „test is successful“, využití disku 31 % a volná paměť v MB.',
            'time' => '12 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nginx -t',
                'expect' => 'test is successful',
              ),
              1 => 
              array (
                'cmd' => 'df -h',
                'expect' => '/dev/sda1',
              ),
              2 => 
              array (
                'cmd' => 'free -m',
                'expect' => 'Mem:',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Kanárek dostane 10 % z 2 000 požadavků; 24 z nich skončí chybou. Spočítej chybovost kanárka, porovnej ji s podmínkou zastavení 3 % a rozhodni.',
            'output' => '200 požadavků, chybovost 12 % > 3 % → zastavit a vrátit; zápis rozhodnutí s čísly.',
            'time' => '13 min',
          ),
          3 => 
          array (
            'text' => 'Ověř nasazení z pohledu uživatele (DNS, HTTP stav intranetu) a pak nacvič návrat: obnov konfiguraci ze zálohy a ověř syntaxi.',
            'output' => 'Adresa 10.0.0.10, HTTP 200 a po návratu „syntax is ok“.',
            'time' => '25 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'dig +short intranet.skola.test',
                'expect' => '10.0.0.10',
              ),
              1 => 
              array (
                'cmd' => 'curl -I http://intranet.skola.test',
                'expect' => '200 OK',
              ),
              2 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak; sudo cp /etc/nginx/nginx.conf.bak /etc/nginx/nginx.conf; nginx -t',
                'expect' => 'syntax is ok',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tiket s vyplněným rozsahem a vzorec pro výpočet chybovosti.',
          'standard' => 'Tiket, prechecky, výpočet kanárka, ověření a nácvik návratu.',
          'challenge' => 'Navrhne druhý stupeň kanárka (25 %) a podmínku, kdy smí pokračovat, a zdůvodní dobu sledování.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: chybovost kanárka je 2,5 % a podmínka zastavení 3 % – co uděláš?',
            1 => 'Kontrola souseda: dá se každá podmínka v tiketu změřit?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Tiket',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Bez čísel.',
                2 => 'Kritéria a podmínky zastavení s čísly.',
                3 => 'Podmínky odvozené z výchozího stavu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Prechecky a ověření',
              'levels' => 
              array (
                0 => 'Bez kontroly.',
                1 => 'Jen syntaxe.',
                2 => 'Syntaxe, disk, paměť a ověření uživatele.',
                3 => 'Zapíše výchozí stav pro srovnání.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rozhodnutí',
              'levels' => 
              array (
                0 => 'Bez rozhodnutí.',
                1 => 'Rozhodnutí bez důkazu.',
                2 => 'Rozhodnutí podle podmínky s čísly.',
                3 => 'Návrat provede a ověří.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'ops_change',
          'competence_label' => 'Provoz: řízené vydání a návrat',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Chybovost kanárka vzroste na 12 %, podmínka zastavení je 3 %. Co uděláš?',
              'options' => 
              array (
                0 => 'Počkám, jestli se to samo zlepší.',
                1 => 'Nasadím na všechny, ať je víc dat.',
                2 => 'Zastavím vydání a provedu návrat podle tiketu.',
              ),
              'correct' => 2,
              'explanation' => 'Překročená podmínka zastavení = návrat, bez diskuse.',
            ),
            1 => 
            array (
              'question' => 'Proč se nová verze nejdřív nasazuje jen na část provozu?',
              'options' => 
              array (
                0 => 'Chyba zasáhne jen malou část uživatelů a dá se včas zastavit.',
                1 => 'Je to rychlejší než nasazení na všechny.',
                2 => 'Server nezvládne víc verzí.',
              ),
              'correct' => 0,
              'explanation' => 'Kanárek omezuje dosah případné chyby.',
            ),
            2 => 
            array (
              'question' => 'Který precheck patří před nasazení nové konfigurace?',
              'options' => 
              array (
                0 => 'Smazání starých logů.',
                1 => 'Kontrola syntaxe konfigurace (nginx -t).',
                2 => 'Restart celého serveru.',
              ),
              'correct' => 1,
              'explanation' => 'Chybnou konfiguraci odhalíš dřív, než způsobí výpadek.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: navrhni podmínky zastavení pro vydání aplikace, kterou znáš (chybovost, doba odpovědi), a zdůvodni čísla.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Vydání i návrat zkoušíme jen v simulátoru Linux Labu.',
          1 => 'Čísla a domény v úlohách jsou vymyšlené; skutečné provozní údaje školy nepoužíváme.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „počkáme, ono se to zlepší“ – podmínka zastavení se dodržuje vždy.',
          1 => 'Otázka do třídy: Kdo smí rozhodnout o návratu a musí se ptát?',
          2 => 'Tempo: výpočet kanárka je krátký; čas nech na ověření a návrat.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 2 a 4 v Linux Labu podle zadání, úkoly 1 a 3 na papír (výsledek výpočtu je v zadání).',
          1 => 'Plán B offline: kartičky s čísly z ověření, dvojice rozhodují „pokračovat / vrátit“.',
        ),
        'glossary' => 
        array (
          0 => 'canary',
          1 => 'rollback',
        ),
        '_file' => 'lesson_content_v77_4a.php',
      ),
    ),
    'days' => 
    array (
      0 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Seznamovací blok · provoz, pravidla změn a diagnostika',
        'goal' => 'Žáci znají pravidla posledního ročníku (změny jen řízeně, incidenty bez obviňování), přihlásí se do aplikace a Linux Labu a ve vstupní diagnostice ukážou, jak postupují při výpadku.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 15,
            'phase' => 'Pravidla',
            'teacher' => 'Představí rok: změnová okna, pozorovatelnost, zálohy, incidenty; vše jen v simulátoru.',
            'student' => 'Zapíší tři pravidla provozu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 15,
            'to' => 30,
            'phase' => 'Přihlášení',
            'teacher' => 'Rozdá kartičky s heslem.',
            'student' => 'Přihlásí se do aplikace a Linux Labu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 30,
            'to' => 65,
            'phase' => 'Diagnostika',
            'teacher' => 'Zadá mini incident: zjisti stav webové služby a zapiš důkaz.',
            'student' => 'Vyplní dotazník a mini incident v Linux Labu.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Sdílení',
            'teacher' => 'Ukáže nejčastější postupy bez jmen.',
            'student' => 'Porovnají postup se sousedem.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Domluva',
            'teacher' => 'Řekne, co bude na lekci 1.',
            'student' => 'Zapíší si první krok.',
            'form' => 'frontálně',
          ),
        ),
        'materials' => 
        array (
          0 => 'Kartičku s jednorázovým heslem.',
          1 => 'Notebook nebo místo v učebně.',
        ),
        'assessment' => 
        array (
          0 => 'Všichni se přihlásili.',
          1 => 'Mini incident má zapsaný důkaz.',
          2 => 'Mám přehled, kdo potřebuje podporu v diagnostice.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přihlas se a v Linux Labu zjisti stav webové služby.',
            'output' => 'Výstup se stavem active (running).',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'systemctl status nginx',
                'expect' => 'active (running)',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Ověř odpověď webu z pohledu uživatele a zapiš, co výstup dokazuje.',
            'output' => 'HTTP 200 a jedna věta.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => '200 OK',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Vyplň seznamovací dotazník.',
            'output' => 'Odeslaný dotazník.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Karta s příkazy diagnostiky služby.',
          'standard' => 'Přihlášení, mini incident a dotazník.',
          'challenge' => 'Doplní k mini incidentu log služby a co v něm hledat.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Pravidlo „jen simulátor“ platí i pro maturanty – zopakuj ho.',
          1 => 'Diagnostika je bez známky.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: přihlášení a dotazník podle návodu; mini incident v Linux Labu podle zadání.',
          1 => 'Plán B offline: výpisy příkazů na kartičkách.',
        ),
        'safety' => 
        array (
          0 => 'Hesla nikomu neukazujeme; příkazy jen v Linux Labu.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      29 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Projektový checkpoint 1 · cvičení spolehlivosti: rozsah, role a SLO',
        'goal' => 'Tým má zadání cvičení spolehlivosti (služba, uživatelé, cíl SLO), rozdělené role a první runbook s kroky obnovy.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Stand-up',
            'teacher' => 'Představí cíl checkpointu.',
            'student' => 'Týmy si zopakují zadání.',
            'form' => 'v týmech',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 35,
            'phase' => 'Služba a cíl',
            'teacher' => 'Ptá se: Co je pro uživatele „funguje“ a jak to změříte?',
            'student' => 'Sepíší službu, ukazatele (SLI) a cíl (SLO).',
            'form' => 'v týmech',
          ),
          2 => 
          array (
            'from' => 35,
            'to' => 50,
            'phase' => 'Role',
            'teacher' => 'Vysvětlí role: vedoucí incidentu, řešitel, komunikace, zapisovatel.',
            'student' => 'Rozdělí role a definici hotovo.',
            'form' => 'v týmech',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 80,
            'phase' => 'Runbook obnovy',
            'teacher' => 'Ukáže runbook s kroky, příkazy a očekávaným výstupem.',
            'student' => 'Sepíší runbook obnovy a ověří kroky v Linux Labu.',
            'form' => 'v týmech',
          ),
          4 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Checkpoint',
            'teacher' => 'Projde zadání, cíl a runbook.',
            'student' => 'Zapíší další krok.',
            'form' => 'v týmech',
          ),
        ),
        'materials' => 
        array (
          0 => 'Zadání projektu ze záložky Projekty.',
          1 => 'Poznámky z lekcí o pozorovatelnosti a změnách.',
        ),
        'assessment' => 
        array (
          0 => 'Zadání má službu, ukazatele a cíl SLO.',
          1 => 'Role jsou rozdělené.',
          2 => 'Runbook má kroky s příkazem a očekávaným výstupem.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Sepište službu, tři ukazatele (SLI) a cíl spolehlivosti (SLO) včetně rozpočtu chyb za 30 dní.',
            'output' => 'Tabulka ukazatelů a cíle.',
            'time' => '25 min',
          ),
          1 => 
          array (
            'text' => 'Napište runbook obnovy webové služby: kontrola konfigurace, stav, naslouchající port, odpověď.',
            'output' => 'Runbook se 4–6 kroky.',
            'time' => '25 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'nginx -t',
                'expect' => 'test is successful',
              ),
              1 => 
              array (
                'cmd' => 'ss -tlnp',
                'expect' => '0.0.0.0:80',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Ověřte runbook v Linux Labu krok za krokem.',
            'output' => 'Ověřený runbook.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://localhost',
                'expect' => '200 OK',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Vzor tabulky SLI/SLO a runbooku.',
          'standard' => 'Zadání, role, cíl a ověřený runbook.',
          'challenge' => 'Spočítá rozpočet chyb v minutách a navrhne práh upozornění.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Cíl 100 % odmítni – nechte tým zdůvodnit realistické číslo.',
          1 => 'Runbook musí jít použít bez autora.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: zadání a runbook podle šablony; ověření v Linux Labu podle příkazů.',
          1 => 'Plán B offline: runbook na papír.',
        ),
        'safety' => 
        array (
          0 => 'Cvičení jen v Linux Labu.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      30 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Projektový checkpoint 2 · nácvik výpadku a obnovy ze zálohy',
        'goal' => 'Tým provede nácvik výpadku: zálohu konfigurace, chybu, obnovu ze zálohy a ověření; zápis má časovou osu a dobu obnovy.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Stand-up',
            'teacher' => 'Připomene cíl: měřit dobu obnovy.',
            'student' => 'Každý řekne stav a blokace.',
            'form' => 'v týmech',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 30,
            'phase' => 'Záloha',
            'teacher' => 'Hlídá, aby záloha vznikla před změnou.',
            'student' => 'Zazálohují konfiguraci a ověří zálohu.',
            'form' => 'v týmech',
          ),
          2 => 
          array (
            'from' => 30,
            'to' => 65,
            'phase' => 'Výpadek a obnova',
            'teacher' => 'Spustí výpadek a měří čas.',
            'student' => 'Najdou příčinu, obnoví službu a ověří ji.',
            'form' => 'v týmech',
          ),
          3 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Doba obnovy',
            'teacher' => 'Ukáže porovnání s cílem RTO.',
            'student' => 'Zapíší skutečnou dobu obnovy a porovnají s cílem.',
            'form' => 'v týmech',
          ),
          4 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Checkpoint',
            'teacher' => 'Zkontroluje časovou osu.',
            'student' => 'Zapíší, co zrychlí obnovu.',
            'form' => 'v týmech',
          ),
        ),
        'materials' => 
        array (
          0 => 'Runbook z checkpointu 1.',
          1 => 'Šablonu časové osy.',
        ),
        'assessment' => 
        array (
          0 => 'Záloha vznikla před změnou a je ověřená.',
          1 => 'Časová osa má dobu obnovy.',
          2 => 'Oprava je ověřená z pohledu uživatele.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zazálohujte konfiguraci a ověřte, že záloha existuje.',
            'output' => 'Soubor nginx.conf.bak ve výpisu.',
            'time' => '10 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak; ls /etc/nginx',
                'expect' => 'nginx.conf.bak',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Při výpadku najděte důkaz příčiny, obnovte službu a ověřte stav a odpověď.',
            'output' => 'Důkaz výpadku, stav active a HTTP 200.',
            'time' => '30 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; curl -I http://localhost',
                'expect' => 'Couldn\'t connect to server',
              ),
              1 => 
              array (
                'cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; systemctl is-active nginx',
                'expect' => 'active',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Zapište dobu obnovy (RTO skutečnost) a porovnejte ji s cílem; navrhněte jedno zrychlení.',
            'output' => 'Časová osa s dobou obnovy a návrh.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Časová osa s předvyplněnými kroky.',
          'standard' => 'Záloha, nácvik, obnova a doba obnovy.',
          'challenge' => 'Navrhne ověřování zálohy obnovou na zkušebním stroji.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Záloha, která nebyla ověřená, se nepočítá.',
          1 => 'Měřte čas od hlášení po ověření, ne po restart.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: nácvik podle runbooku v Linux Labu; zastupující měří čas.',
          1 => 'Plán B offline: incident na kartičkách s výpisy.',
        ),
        'safety' => 
        array (
          0 => 'Služby zastavujeme a obnovujeme jen v simulátoru.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      31 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Projektový checkpoint 3 · peer review runbooku a rozboru',
        'goal' => 'Runbook a rozbor incidentu jsou hotové na úrovni „hotovo, ale neuhlazené“; jiný tým je otestuje a dá připomínky.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Pravidla review',
            'teacher' => 'Vysvětlí test runbooku cizím týmem.',
            'student' => 'Vymění si runbooky a rozbory.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 45,
            'phase' => 'Test runbooku',
            'teacher' => 'Sleduje, kde se týmy zaseknou.',
            'student' => 'Projdou cizí runbook v Linux Labu a přečtou rozbor.',
            'form' => 've dvojicích týmů',
          ),
          2 => 
          array (
            'from' => 45,
            'to' => 82,
            'phase' => 'Opravy',
            'teacher' => 'Koučuje opravy.',
            'student' => 'Opraví runbook a rozbor podle připomínek.',
            'form' => 'v týmech',
          ),
          3 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Checkpoint',
            'teacher' => 'Zkontroluje opravy.',
            'student' => 'Odškrtnou opravené body.',
            'form' => 'v týmech',
          ),
        ),
        'materials' => 
        array (
          0 => 'Runbook a rozbor incidentu (postmortem).',
        ),
        'assessment' => 
        array (
          0 => 'Cizí tým runbook použil.',
          1 => 'Rozbor je bez obviňování a má prevenci.',
          2 => 'Připomínky jsou opravené.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Projděte cizí runbook v Linux Labu přesně podle zápisu a zapište, kde nešel použít.',
            'output' => 'Seznam míst se selháním a návrh opravy.',
            'time' => '35 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo systemctl reload nginx; systemctl is-active nginx',
                'expect' => 'active',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Přečtěte cizí postmortem a ověřte: příznak, příčina, důkaz, oprava, prevence, bez jmen.',
            'output' => 'Připomínky k rozboru.',
            'time' => '10 min',
          ),
          2 => 
          array (
            'text' => 'Opravte vlastní runbook a rozbor podle připomínek.',
            'output' => 'Opravené dokumenty.',
            'time' => '30 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Kontrolní seznam runbooku a rozboru.',
          'standard' => 'Test, připomínky a opravy.',
          'challenge' => 'Doplní runbook o větev „když obnova selže“.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Rozbor se jmény vrať – bez obviňování je pravidlo.',
          1 => 'Runbook je dobrý, když ho použije jiný tým.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: výměna dokumentů a test v Linux Labu.',
          1 => 'Plán B offline: kontrola dokumentů podle seznamu.',
        ),
        'safety' => 
        array (
          0 => 'Jen simulátor; rozbory bez jmen.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      32 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Mastery clinic · dorovnání kompetencí provozu',
        'goal' => 'Každý žák dorovná jednu kompetenci provozu (směrování, proxy a TLS, diagnostika, zabezpečení, pozorovatelnost, změna, záloha) a doloží ji důkazem.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Mapa mezer',
            'teacher' => 'Ukáže stav kompetencí v profilu.',
            'student' => 'Vyberou kompetenci k dorovnání.',
            'form' => 'jednotlivě',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 65,
            'phase' => 'Stanoviště',
            'teacher' => 'Vede stanoviště s nejvíc žáky.',
            'student' => 'Procvičují: podsítě a směrování, logy a chybovost, změna s návratem, záloha a obnova.',
            'form' => 've skupinách',
          ),
          2 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Ověření',
            'teacher' => 'Zadá krátkou zkoušku.',
            'student' => 'Splní zkoušku a uloží výstup.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Zápis',
            'teacher' => 'Ukáže zápis důkazu.',
            'student' => 'Zapíší, co dorovnali.',
            'form' => 'jednotlivě',
          ),
        ),
        'materials' => 
        array (
          0 => 'Profil s kompetencemi.',
        ),
        'assessment' => 
        array (
          0 => 'Každý má vybranou kompetenci.',
          1 => 'Ověření má výstup nebo výpočet.',
          2 => 'Pozorování zapisuji, jen když je katalog 4.A schválený.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vyber kompetenci se stavem „učím se“ nebo „zatím ne“.',
            'output' => 'Vybraná kompetence.',
            'time' => '8 min',
          ),
          1 => 
          array (
            'text' => 'Vyřeš kolo úloh s variantami (podsíť, maska, směrování, porty) nebo úkol stanoviště.',
            'output' => 'Dokončené kolo úlohy.',
            'time' => '40 min',
          ),
          2 => 
          array (
            'text' => 'Splň zkoušku: spočítej z access logu stavové kódy a urči chybovost.',
            'output' => 'Počty kódů a chybovost v procentech.',
            'time' => '15 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo awk \'{print $9}\' /var/log/nginx/access.log | sort | uniq -c',
                'expect' => '200',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Stanoviště s nápovědou.',
          'standard' => 'Úloha a zkouška.',
          'challenge' => 'Tutor na stanovišti.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Katalog 4.A je návrh – důkazy se počítají až po schválení.',
          1 => 'Ukaž skutečnou mezeru, ne nejlehčí stanoviště.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: úlohy s variantami podle profilu.',
          1 => 'Plán B offline: karty stanovišť.',
        ),
        'safety' => 
        array (
          0 => 'Příkazy jen v Linux Labu.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      33 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Capstone sprint 1 · cvičení spolehlivosti: scénáře a měření',
        'goal' => 'Týmy souvisle pracují na cvičení spolehlivosti, připravují scénáře výpadků s měřením a rizika zapisují včas.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Plán sprintu',
            'teacher' => 'Připomene termín obhajob.',
            'student' => 'Zapíší cíl sprintu a úkoly.',
            'form' => 'v týmech',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 75,
            'phase' => 'Souvislá práce',
            'teacher' => 'Koučuje podle potřeby.',
            'student' => 'Připravují scénáře (výpadek služby, chybná změna, plný disk) s měřením.',
            'form' => 'v týmech',
          ),
          2 => 
          array (
            'from' => 75,
            'to' => 85,
            'phase' => 'Stav a rizika',
            'teacher' => 'Projde posun a rizika.',
            'student' => 'Ukážou posun a rizika.',
            'form' => 'v týmech',
          ),
          3 => 
          array (
            'from' => 85,
            'to' => 90,
            'phase' => 'Úklid',
            'teacher' => 'Připomene uložení.',
            'student' => 'Uloží práci.',
            'form' => 'v týmech',
          ),
        ),
        'materials' => 
        array (
          0 => 'Runbook, rozbor a připomínky z checkpointu 3.',
        ),
        'assessment' => 
        array (
          0 => 'Každý scénář má měření (čas, chybovost) a krok návratu.',
          1 => 'Rizika mají vlastníka.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zapište cíl sprintu a úkoly s vlastníkem.',
            'output' => 'Plán sprintu.',
            'time' => '10 min',
          ),
          1 => 
          array (
            'text' => 'Připravte scénář „chybná změna konfigurace“ s kontrolou syntaxe a návratem ze zálohy.',
            'output' => 'Scénář s kroky a ověřením.',
            'time' => '30 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak; sudo cp /etc/nginx/nginx.conf.bak /etc/nginx/nginx.conf; nginx -t',
                'expect' => 'syntax is ok',
              ),
            ),
          ),
          2 => 
          array (
            'text' => 'Připravte scénář „málo místa na disku“ s výchozím stavem a prahem upozornění.',
            'output' => 'Scénář s výstupem využití disku a prahem.',
            'time' => '30 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'df -h',
                'expect' => 'Use%',
              ),
            ),
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Jeden scénář místo dvou.',
          'standard' => 'Plán sprintu a dva scénáře.',
          'challenge' => 'Třetí scénář s kanárkovým nasazením a podmínkou zastavení.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Ptej se: Jak poznáte, že je scénář vyřešený – podle čísla?',
          1 => 'Rizika kontroluj v polovině hodiny.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: práce podle plánu sprintu v Linux Labu.',
          1 => 'Plán B offline: scénáře na papír.',
        ),
        'safety' => 
        array (
          0 => 'Jen simulátor; čísla a domény jsou vymyšlené.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      34 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Capstone sprint 2 · kontrola, dokumentace a příprava obhajoby',
        'goal' => 'Cvičení spolehlivosti prošlo kontrolou (scénáře jdou zopakovat), má dokumentaci a postmortem a tým má připravenou obhajobu.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Stand-up',
            'teacher' => 'Ukáže kontrolní seznam a osnovu obhajoby.',
            'student' => 'Rozdělí práci.',
            'form' => 'v týmech',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 45,
            'phase' => 'Kontrola',
            'teacher' => 'Ověřuje opakovatelnost scénářů.',
            'student' => 'Zopakují scénáře podle dokumentace a opraví rozdíly.',
            'form' => 'v týmech',
          ),
          2 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Dokumentace',
            'teacher' => 'Ukáže osnovu.',
            'student' => 'Sepíší dokumentaci a postmortem nejzávažnějšího scénáře.',
            'form' => 'v týmech',
          ),
          3 => 
          array (
            'from' => 65,
            'to' => 85,
            'phase' => 'Zkouška obhajoby',
            'teacher' => 'Poslouchá a radí.',
            'student' => 'Předvedou scénář nanečisto.',
            'form' => 've dvojicích týmů',
          ),
          4 => 
          array (
            'from' => 85,
            'to' => 90,
            'phase' => 'Odevzdání',
            'teacher' => 'Zkontroluje odevzdání.',
            'student' => 'Odevzdají dokumentaci.',
            'form' => 'v týmech',
          ),
        ),
        'materials' => 
        array (
          0 => 'Scénáře, runbook a měření.',
        ),
        'assessment' => 
        array (
          0 => 'Scénáře jdou zopakovat.',
          1 => 'Postmortem je bez obviňování a má prevenci.',
          2 => 'Obhajoba se vejde do času.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zopakujte scénáře podle dokumentace a zapište rozdíly.',
            'output' => 'Odškrtnutý seznam scénářů.',
            'time' => '35 min',
            'sim' => 
            array (
              0 => 
              array (
                'cmd' => 'curl -I http://intranet.skola.test',
                'expect' => '200 OK',
              ),
            ),
          ),
          1 => 
          array (
            'text' => 'Sepište dokumentaci a postmortem nejzávažnějšího scénáře (příznak, dopad, příčina, důkaz, oprava, prevence).',
            'output' => 'Dokumentace a postmortem.',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Předveďte scénář nanečisto do 5 minut.',
            'output' => 'Odzkoušená obhajoba.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Šablona postmortemu.',
          'standard' => 'Kontrola, dokumentace a zkouška.',
          'challenge' => 'Doplní dopad na cíl SLO a čerpání rozpočtu chyb.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Scénář, který nejde zopakovat, do obhajoby nepatří.',
          1 => 'Postmortem bez prevence je neúplný.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: kontrola scénářů v Linux Labu podle dokumentace.',
          1 => 'Plán B offline: postmortem na papír.',
        ),
        'safety' => 
        array (
          0 => 'Dokumentace bez hesel a skutečných adres.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      35 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Obhajoby / demo · výpadek, obnova a postmortem',
        'goal' => 'Týmy předvedou výpadek, obnovu a rozbor bez obviňování; hodnotí se důkazy, měření a role podle známé rubriky.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Pravidla obhajob',
            'teacher' => 'Představí pořadí, čas, rubriku a otázky publika.',
            'student' => 'Připraví se.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 75,
            'phase' => 'Obhajoby',
            'teacher' => 'Hodnotí podle rubriky.',
            'student' => 'Týmy předvádějí scénář a postmortem; publikum se ptá.',
            'form' => 'frontálně',
          ),
          2 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Zpětná vazba',
            'teacher' => 'Silná stránka a další krok pro každý tým.',
            'student' => 'Zapíší zpětnou vazbu.',
            'form' => 'frontálně',
          ),
        ),
        'materials' => 
        array (
          0 => 'Scénáře, měření, dokumentaci a postmortem.',
        ),
        'assessment' => 
        array (
          0 => 'Každý člen týmu mluví o své roli.',
          1 => 'Doba obnovy a dopad jsou změřené.',
          2 => 'Rubrika je u všech stejná.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Předveďte výpadek, obnovu a postmortem s měřením (doba obnovy, dopad).',
            'output' => 'Obhajoba do 5 minut.',
            'time' => '5 min na tým',
          ),
          1 => 
          array (
            'text' => 'Jako publikum se zeptej: Jak víte, že se to nebude opakovat?',
            'output' => 'Otázka pro tým.',
            'time' => 'průběžně',
          ),
          2 => 
          array (
            'text' => 'Zapiš zpětnou vazbu a další krok svého týmu.',
            'output' => 'Zápis zpětné vazby.',
            'time' => '10 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Možnost představit svou část u stolu před učitelem.',
          'standard' => 'Obhajoba a otázka.',
          'challenge' => 'Odpoví s odkazem na měření.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Rubriku promítni předem.',
          1 => 'Hodnotí se důkazy a postup, ne „hrdinství“.',
        ),
        'substitution' => 
        array (
          0 => 'Obhajoby bez učitele předmětu přesunout výjimkou v Kalendáři.',
          1 => 'Plán B offline: obhajoba nad vytištěnými výpisy.',
        ),
        'safety' => 
        array (
          0 => 'Na projektoru jen laboratorní adresy, žádná hesla.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      36 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Retrospektiva + peer feedback · provoz bez obviňování',
        'goal' => 'Žáci pojmenují, co se naučili o provozu a incidentech, co týmu pomohlo a co příště změní; dají si konkrétní zpětnou vazbu.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 15,
            'phase' => 'Osobní ohlédnutí',
            'teacher' => 'Ukáže první a poslední incident roku (bez jmen).',
            'student' => 'Zapíší tři posuny.',
            'form' => 'jednotlivě',
          ),
          1 => 
          array (
            'from' => 15,
            'to' => 45,
            'phase' => 'Týmová retrospektiva',
            'teacher' => 'Vede Keep / Improve / Try.',
            'student' => 'Sepíší body retrospektivy.',
            'form' => 'v týmech',
          ),
          2 => 
          array (
            'from' => 45,
            'to' => 80,
            'phase' => 'Peer feedback',
            'teacher' => 'Ukáže vzor zpětné vazby.',
            'student' => 'Napíší zpětnou vazbu dvěma spolužákům.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Shrnutí',
            'teacher' => 'Sesbírá nejčastější „Try“.',
            'student' => 'Vyberou jednu věc pro sebe.',
            'form' => 'frontálně',
          ),
        ),
        'materials' => 
        array (
          0 => 'Své výstupy z roku a postmortemy.',
        ),
        'assessment' => 
        array (
          0 => 'Zpětná vazba je konkrétní a respektující.',
          1 => 'Každý tým má Keep / Improve / Try.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zapiš tři posuny roku a výstup, který každý dokazuje.',
            'output' => 'Tři posuny.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Sepište Keep / Improve / Try k vedení incidentu a změnám.',
            'output' => 'Tabulka retrospektivy.',
            'time' => '25 min',
          ),
          2 => 
          array (
            'text' => 'Napiš dvěma spolužákům zpětnou vazbu: co se povedlo a jeden tip.',
            'output' => 'Dvě zpětné vazby.',
            'time' => '30 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Začátky vět pro zpětnou vazbu.',
          'standard' => 'Ohlédnutí, retrospektiva a zpětná vazba.',
          'challenge' => 'Vede retrospektivu týmu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Retrospektiva nevstupuje do hodnocení.',
          1 => 'Bez obviňování platí i tady.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: retrospektiva podle osnovy.',
          1 => 'Plán B offline: lístečky.',
        ),
        'safety' => 
        array (
          0 => 'Zpětnou vazbu dostane jen adresát.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      37 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Portfolio & showcase · postmortem a runbook pro budoucí praxi',
        'goal' => 'Každý žák vybere 2–3 ověřené výstupy (runbook, postmortem, změnový tiket), popíše svůj podíl a krátce je ukáže.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Kritéria',
            'teacher' => 'Ukáže, co zaměstnavatele zajímá: postup, důkaz, prevence.',
            'student' => 'Vyberou kandidáty.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 60,
            'phase' => 'Výběr a popis',
            'teacher' => 'Ptá se na důkaz a roli.',
            'student' => 'Připnou výstupy a napíší roli, rozhodnutí a důkaz.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 60,
            'to' => 85,
            'phase' => 'Showcase',
            'teacher' => 'Organizuje ukázky ve dvojicích.',
            'student' => 'Ukážou portfolio a odpoví na otázku.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 85,
            'to' => 90,
            'phase' => 'Export',
            'teacher' => 'Připomene stažení.',
            'student' => 'Stáhnou portfolio jako HTML.',
            'form' => 'jednotlivě',
          ),
        ),
        'materials' => 
        array (
          0 => 'Projekty, runbooky a postmortemy.',
        ),
        'assessment' => 
        array (
          0 => 'Výstupy jsou ověřené.',
          1 => 'Popis role je pravdivý.',
          2 => 'V portfoliu nejsou hesla ani skutečné adresy.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Připni 2–3 výstupy a ke každému napiš roli, rozhodnutí a důkaz.',
            'output' => 'Připnuté výstupy s reflexí.',
            'time' => '45 min',
          ),
          1 => 
          array (
            'text' => 'Zkontroluj, že ve výstupech nejsou hesla, klíče ani skutečné adresy.',
            'output' => 'Odškrtnutá kontrola.',
            'time' => '5 min',
          ),
          2 => 
          array (
            'text' => 'Ukaž portfolio spolužákovi a stáhni ho jako HTML.',
            'output' => 'Ukázka a stažený soubor.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Vzor popisu role.',
          'standard' => 'Výběr, popis a showcase.',
          'challenge' => 'Případová studie incidentu s měřením.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Postmortem v portfoliu ukazuje zralost víc než seznam nástrojů.',
          1 => 'Ověř popis role u týmových výstupů.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: výběr a popis podle návodu v Moje portfolio.',
          1 => 'Plán B offline: popis na papír.',
        ),
        'safety' => 
        array (
          0 => 'Žádná hesla, klíče ani skutečné adresy v portfoliu.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
      38 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Uzavření roku · kompetence provozu a další cesta',
        'goal' => 'Uzavřít rok: přehled zvládnutých kompetencí provozu, osobní souhrn růstu, anonymní zpětná vazba a doporučení pro další studium nebo praxi.',
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 20,
            'phase' => 'Přehled roku',
            'teacher' => 'Projde, co třída zvládla: od rozboru výpadku po cvičení spolehlivosti.',
            'student' => 'Najdou zvládnuté kompetence v profilu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 20,
            'to' => 50,
            'phase' => 'Osobní souhrn',
            'teacher' => 'Ukáže osnovu souhrnu.',
            'student' => 'Zapíší tři posuny a další krok po škole.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 50,
            'to' => 75,
            'phase' => 'Zpětná vazba ke kurzu',
            'teacher' => 'Vysvětlí anonymitu.',
            'student' => 'Vyplní anonymní zpětnou vazbu.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Uzavření',
            'teacher' => 'Poděkuje a doporučí cesty dál (studium, certifikace, praxe).',
            'student' => 'Zapíší jeden konkrétní další krok.',
            'form' => 'frontálně',
          ),
        ),
        'materials' => 
        array (
          0 => 'Profil s kompetencemi a portfoliem.',
        ),
        'assessment' => 
        array (
          0 => 'Zpětnou vazbu odevzdala většina třídy.',
          1 => 'Každý má osobní souhrn a další krok.',
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zapiš tři největší posuny roku s výstupem, který je dokazuje.',
            'output' => 'Osobní souhrn.',
            'time' => '20 min',
          ),
          1 => 
          array (
            'text' => 'Napiš jeden konkrétní další krok po škole (studium, praxe, certifikace).',
            'output' => 'Další krok.',
            'time' => '10 min',
          ),
          2 => 
          array (
            'text' => 'Vyplň anonymní zpětnou vazbu ke kurzu.',
            'output' => 'Odeslaná zpětná vazba.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Osnova souhrnu.',
          'standard' => 'Souhrn, další krok a zpětná vazba.',
          'challenge' => 'Plán vlastní laboratoře ve virtuálu s cílem a měřením.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Zpětnou vazbu shrň pro další ročník.',
          1 => 'Kdo chyběl, dostane souhrn jako dobrovolný úkol.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup: souhrn a zpětná vazba podle osnovy.',
          1 => 'Plán B offline: souhrn na papír.',
        ),
        'safety' => 
        array (
          0 => 'Zpětná vazba je anonymní; doma jen vlastní virtuál, žádné cizí sítě.',
        ),
        '_file' => 'lesson_content_v77_days_4a.php',
      ),
    ),
    'files' => 
    array (
      0 => 'lesson_content_v72_1a_a.php',
      1 => 'lesson_content_v72_1a_b.php',
      2 => 'lesson_content_v72_2a_a.php',
      3 => 'lesson_content_v72_2a_b.php',
      4 => 'lesson_content_v72_3a_a.php',
      5 => 'lesson_content_v72_3a_b.php',
      6 => 'lesson_content_v72_4a_a.php',
      7 => 'lesson_content_v72_4a_b.php',
      8 => 'lesson_content_v76_1a_a.php',
      9 => 'lesson_content_v76_1a_b.php',
      10 => 'lesson_content_v76_2a_a.php',
      11 => 'lesson_content_v76_2a_b.php',
      12 => 'lesson_content_v76_3a_a.php',
      13 => 'lesson_content_v76_3a_b.php',
      14 => 'lesson_content_v76_4a_a.php',
      15 => 'lesson_content_v76_4a_b.php',
      16 => 'lesson_content_v77_1a.php',
      17 => 'lesson_content_v77_2a.php',
      18 => 'lesson_content_v77_3a.php',
      19 => 'lesson_content_v77_4a.php',
      20 => 'lesson_content_v77_days_1a.php',
      21 => 'lesson_content_v77_days_2a.php',
      22 => 'lesson_content_v77_days_3a.php',
      23 => 'lesson_content_v77_days_4a.php',
    ),
  ),
  'sig' => '6e69cead0b8f06220a2c21cfa85e8b5ddfca90e7',
  'hash' => '9fe86c35b73f946cdd9ffff592c0c40d82af8be9198f8bd14ca40bc22adb8ab1',
  'built_at' => '2026-10-13T04:15:44+02:00',
);
