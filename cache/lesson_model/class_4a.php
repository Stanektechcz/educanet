<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 1,
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
    ),
    'days' => 
    array (
    ),
    'files' => 
    array (
    ),
  ),
  'sig' => 'ac09530107d415acebf614c00c70b5113c87b14e',
  'hash' => '283715346b3baf7ca6021ab840773f1a0cb7c4b7b862c1e248e99979b28273e3',
  'built_at' => '2026-10-07T22:52:57+02:00',
);
