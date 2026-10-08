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
    ),
    'days' => 
    array (
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
    ),
  ),
  'sig' => 'a91d3ceb5c7f0a2bf8dc3e13dec665db2f11bca6',
  'hash' => '5f5c1e10c2f18275fc1e2539d421c0d23a117e3049c3b66059d0e748d74a418b',
  'built_at' => '2026-10-08T07:14:26+02:00',
);
