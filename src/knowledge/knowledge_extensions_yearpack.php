<?php

declare(strict_types=1);

return json_decode(<<<'JSON'
{
  "class_1a": {
    "web-layout-basics-i": {
      "title": "Anatomie webové stránky",
      "summary": "Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.",
      "body": [
        "Header pomáhá orientaci, hero rychle vysvětluje hodnotu a hlavní obsah vede uživatele k cíli.",
        "Nezačínej dekoracemi. Nejprve určuj priority obsahu a vztahy mezi bloky.",
        "Dobrá stránka zůstává pochopitelná i jako jednoduchý černobílý wireframe."
      ],
      "example": "Hero: headline → supporting text → primární CTA → důkaz/obraz."
    },
    "responsive-layout-i": {
      "title": "Responsive layout I",
      "summary": "Responzivní návrh zachovává informační prioritu a přeskupuje obsah podle dostupného prostoru.",
      "body": [
        "Mobile není zmenšený desktop. Často potřebuje jiné pořadí, kratší headline nebo změnu kolony na stack.",
        "Breakpoint dává smysl ve chvíli, kdy obsah nebo komponenta přestává fungovat.",
        "Testuj také mezilehlé šířky, nejen dvě předem připravené obrazovky."
      ],
      "example": "Desktop dvě kolony → tablet užší grid → mobile jeden sloupec se stejnou prioritou."
    },
    "components-i": {
      "title": "Komponenty I: opakovatelné UI vzory",
      "summary": "Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.",
      "body": [
        "Tlačítko má roli, variantu a stavy. Karta má jasnou vnitřní strukturu a opakovatelný spacing.",
        "Konzistence neznamená, že vše vypadá stejně; znamená, že podobné věci používají stejná pravidla.",
        "Stav focus, disabled nebo error je stejně důležitý jako default."
      ],
      "example": "Primary button: stejná výška/padding/radius, ale různé stavy a obsah."
    },
    "web-typography-i": {
      "title": "Webová typografie I",
      "summary": "Typografie na obrazovce musí být skenovatelná, čitelná a odolná vůči změně šířky.",
      "body": [
        "Používej malý počet pojmenovaných textových rolí.",
        "Čitelnost ovlivňuje velikost, line-height, délka řádku i kontrast.",
        "Headline musí být testovaný i na mobilu, kde se zalomení může dramaticky změnit."
      ],
      "example": "H1 48→36 px podle prostoru; body kolem čitelné základní velikosti s omezenou šířkou sloupce."
    },
    "image-web-i": {
      "title": "Obraz a assety pro web",
      "summary": "Webový obraz musí podporovat obsah, mít správný crop a odpovídat skutečnému použití.",
      "body": [
        "Jeden master může potřebovat více cropů pro různé poměry stran.",
        "Rozměr exportu má odpovídat zobrazované ploše; zbytečně velký originál zvyšuje datovou zátěž.",
        "Informační obraz potřebuje textovou alternativu; čistě dekorativní obraz nesmí nést jedinou důležitou informaci."
      ],
      "example": "Stejná fotografie může mít jiný crop pro desktop hero a mobile card."
    },
    "forms-a11y-i": {
      "title": "Formuláře a přístupnost I",
      "summary": "Formulář musí být srozumitelný v defaultu, při focusu i při chybě.",
      "body": [
        "Label popisuje význam pole a zůstává čitelný i po zadání hodnoty.",
        "Chyba má být konkrétní a nemá být signalizovaná jen barvou.",
        "Focus pomáhá uživateli chápat, který prvek právě ovládá."
      ],
      "example": "E-mail: label + helper; při chybě text „Zadej platný e-mail“ a viditelný error state."
    },
    "design-system-i": {
      "title": "Mini design systém I",
      "summary": "Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.",
      "body": [
        "Začni typografií, barvami a spacingem; teprve potom skládej komponenty.",
        "Token pojmenuj podle účelu, pokud má fungovat napříč produktem.",
        "Dokumentace má ukázat správné použití i typickou chybu."
      ],
      "example": "Primary action + surface + text colors, spacing 4/8/16/24/32 a Button/Card/Field."
    }
  },
  "class_2a": {
    "information-architecture-ii": {
      "title": "Informační architektura II",
      "summary": "IA organizuje obsah podle očekávání a cílů uživatele, ne podle interní struktury firmy.",
      "body": [
        "Content inventory je vstup; následně obsah seskupuj podle významu a úkolů.",
        "Sitemap popisuje hierarchii, user flow popisuje konkrétní cestu za cílem.",
        "Test struktury může proběhnout i bez finálního vizuálu."
      ],
      "example": "Úkol „najdi cenu a objednej“ má být řešitelný podle názvů a pořadí bez vizuálních nápověd."
    },
    "form-states-ii": {
      "title": "Form UX a stavový model",
      "summary": "Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.",
      "body": [
        "Error musí vést k opravě a zachovat správně vyplněná data.",
        "Loading chrání uživatele před nejistotou a dvojím odesláním.",
        "Focus order a keyboard flow jsou součást návrhu."
      ],
      "example": "Submit → loading → server error → zachovaný obsah + konkrétní náprava → retry."
    },
    "auto-layout-ii": {
      "title": "Auto Layout a varianty II",
      "summary": "Auto Layout vyjadřuje vztahy mezi obsahem a prostorem, takže komponenta lépe reaguje na změnu textu a viewportu.",
      "body": [
        "Rozliš hug, fill a fixed podle role prvku.",
        "Gap a padding jsou jiné veličiny.",
        "Varianty mají popisovat skutečné stavy/velikosti, ne nahodilé kopie komponent."
      ],
      "example": "Button reaguje na delší text změnou šířky, ne ručním posunem každého prvku."
    },
    "accessibility-audit-ii": {
      "title": "Accessibility audit II",
      "summary": "Audit hledá bariéry v kontrastu, ovládání, focusu, reflow, textech a stavových informacích.",
      "body": [
        "Barva nesmí být jediným nositelem významu.",
        "Keyboard focus musí být viditelný a ovládání musí mít smysluplné pořadí.",
        "Při zoom/reflow nesmí klíčový obsah zmizet nebo být zakrytý."
      ],
      "example": "Audit formuláře: label, focus, error text, target, pořadí a success feedback."
    },
    "html-css-handoff-ii": {
      "title": "Design → HTML/CSS handoff",
      "summary": "Handoff popisuje strukturu, pravidla a chování, které developer potřebuje k věrné a robustní implementaci.",
      "body": [
        "Statický screenshot nepopisuje responsive chování ani stavy.",
        "Tokeny snižují množství náhodných lokálních hodnot.",
        "Komponenta potřebuje obsahové hranice a edge cases, nejen ideální demo."
      ],
      "example": "Card: max-width, padding token, image aspect ratio, title wrap, CTA state, mobile stack."
    },
    "css-responsive-ii": {
      "title": "CSS layout mindset",
      "summary": "Flexbox a Grid jsou modely vztahů mezi prvky, které lze využít k implementaci responzivních pravidel.",
      "body": [
        "Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro dvourozměrné oblasti.",
        "Max-width chrání čitelnost a kompozici na velkých obrazovkách.",
        "Breakpoint vybírej podle chování obsahu."
      ],
      "example": "Card grid: 3 kolony → 2 → 1 podle minimální použitelné šířky karty."
    },
    "usability-test-ii": {
      "title": "Usability test II",
      "summary": "Krátký usability test sleduje, zda uživatel dokáže splnit úkol bez navádění a kde vzniká nejistota.",
      "body": [
        "Úkol popisuje cíl, ne postup.",
        "Pozorování zapisuj jako chování, interpretaci až následně.",
        "Prioritizuj problém podle dopadu a četnosti, ne podle osobního vkusu."
      ],
      "example": "Pozorování: „uživatel otevřel menu třikrát“. Interpretace: „název sekce možná neodpovídá očekávání“."
    },
    "design-qa-handoff-ii": {
      "title": "Design QA a handoff II",
      "summary": "Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.",
      "body": [
        "Issue popisuje steps, expected, actual a severity.",
        "Nejdřív řeš funkční a accessibility dopad, potom kosmetické odchylky.",
        "Po opravě zopakuj původní scénář a teprve potom issue uzavři."
      ],
      "example": "High: CTA není keyboard accessible. Low: stín je o něco silnější než ve specifikaci."
    }
  },
  "class_3a": {
    "linux-filesystem": {
      "title": "Linux filesystem a CLI",
      "summary": "Filesystem je hierarchie začínající v / a administrátor potřebuje rozumět cestám dřív, než začne měnit soubory.",
      "body": [
        "/etc obvykle obsahuje konfiguraci, /var proměnlivá provozní data a logy, /home uživatelská data.",
        "Absolutní cesta začíná v /, relativní v aktuálním pracovním adresáři.",
        "Před destruktivní operací kontroluj scope pomocí pwd a přesné cesty."
      ],
      "example": "/var/log/nginx/error.log je absolutní cesta; ../logs/error.log relativní."
    },
    "users-permissions": {
      "title": "Uživatelé, skupiny a oprávnění",
      "summary": "Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.",
      "body": [
        "r/w/x mají jiný význam pro soubor a adresář.",
        "Skupina je běžný způsob, jak sdílet přístup bez world-writable oprávnění.",
        "chmod mění mode, chown vlastnictví."
      ],
      "example": "640 = owner rw, group r, others nic."
    },
    "processes-systemd": {
      "title": "Procesy a systemd",
      "summary": "Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.",
      "body": [
        "systemctl status spojuje stav, PID a poslední logy.",
        "Restart ukončí a znovu spustí proces; reload může načíst konfiguraci bez plného restartu.",
        "Enable řeší boot-time activation, ne nutně okamžité spuštění."
      ],
      "example": "Webserver může být active, ale přesto nefunkční kvůli chybné konfiguraci nebo upstreamu."
    },
    "journal-logs": {
      "title": "journalctl a provozní logy",
      "summary": "Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.",
      "body": [
        "Začni časovým oknem a konkrétní unit.",
        "Severity je vodítko, ne absolutní pravda.",
        "Koreluj log s měřením portu, health checkem a změnovou historií."
      ],
      "example": "journalctl -u app --since „10 min ago“ je užitečnější než číst celý journal."
    },
    "ssh-keys-ops": {
      "title": "SSH klíče a vzdálená správa",
      "summary": "SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.",
      "body": [
        "Private key zůstává tajný, public key se instaluje na server.",
        "Příliš široká práva privátního klíče mohou být klientem odmítnuta.",
        "Timeout, connection refused a permission denied jsou různé evidence."
      ],
      "example": "TCP/22 ok + Permission denied → hledej user/key/auth config, ne DHCP."
    },
    "linux-firewall": {
      "title": "Linux firewall a service exposure",
      "summary": "Dostupnost služby vzniká kombinací listeneru, bind adresy, routingu a firewall policy.",
      "body": [
        "0.0.0.0 listener se váže na všechna IPv4 rozhraní; 127.0.0.1 jen lokálně.",
        "Firewall pravidlo má mít co nejmenší source/destination/service scope.",
        "Validuj povolený i zakázaný scénář."
      ],
      "example": "Web 443 z USERS allow; SSH 22 jen z MGMT; ostatní serverový provoz deny."
    },
    "web-service-linux": {
      "title": "Linux webová služba: end-to-end cesta",
      "summary": "End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.",
      "body": [
        "Lokální curl odlišuje problém aplikace od vzdálené síťové cesty.",
        "HTTP status je evidence aplikační/proxy vrstvy.",
        "Po opravě testuj původní uživatelskou cestu, ne pouze localhost."
      ],
      "example": "Local health 200 + remote timeout → zkoumej bind/firewall/routing před aplikací."
    },
    "shell-cron": {
      "title": "Shell automatizace a plánování",
      "summary": "Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.",
      "body": [
        "Cron/timer běží v jiném prostředí než interaktivní shell; PATH a working directory mohou být jiné.",
        "Secrets nepatří do zdrojového skriptu.",
        "Naplánování není důkaz úspěchu; ověř reálný výsledek."
      ],
      "example": "Health report skript zapíše timestamp, status služby a skončí nenulově při chybě."
    }
  },
  "class_4a": {
    "systemd-advanced": {
      "title": "systemd: dependencies a failure policy",
      "summary": "Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.",
      "body": [
        "After určuje pořadí, Requires/Wants vyjadřují různé síly závislosti.",
        "Restart loop může zatížit systém a skrýt původní chybu.",
        "Drop-in override bývá bezpečnější než kopie celé vendor unit."
      ],
      "example": "App requires network target, ale zároveň potřebuje validní config a dostupný storage mount."
    },
    "storage-filesystems": {
      "title": "Storage a filesystems",
      "summary": "Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.",
      "body": [
        "df ukazuje filesystem, du hledá využití v adresářích; výsledky nemusí být stejné kvůli otevřeným souborům/mountům.",
        "Inodes mohou dojít dřív než GB.",
        "Mazání bez pochopení ownera procesu a retention policy může způsobit další incident."
      ],
      "example": "/var plný kvůli logům → nejdřív identifikovat službu, retention a bezpečný cleanup."
    },
    "backup-strategy": {
      "title": "Backup strategie, RPO a RTO",
      "summary": "Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.",
      "body": [
        "RPO popisuje tolerovatelnou ztrátu dat v čase.",
        "RTO popisuje cílový čas obnovení služby.",
        "Restore drill má probíhat do bezpečného testovacího cíle a ověřit integritu i funkci."
      ],
      "example": "RPO 1h → záloha jednou denně pravděpodobně nesplní požadavek."
    },
    "ssh-hardening": {
      "title": "SSH hardening a bezpečný management",
      "summary": "Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.",
      "body": [
        "Preferuj klíče a omezený management scope.",
        "Firewall a SSH config měň s ověřeným rollbackem/console access.",
        "Security změna potřebuje pozitivní i negativní validační test."
      ],
      "example": "MGMT subnet → SSH allow, běžní uživatelé deny; oprávněný admin se stále přihlásí."
    },
    "containers-basics": {
      "title": "Containers: image, runtime, volume a network",
      "summary": "Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.",
      "body": [
        "Image je neměnný build artefakt, container konkrétní instance.",
        "Volume slouží pro data, která mají přežít recreate.",
        "Publikovaný host port neřeší špatný listener nebo nehealthy aplikaci uvnitř."
      ],
      "example": "host:8080 → container:80 funguje jen pokud aplikace uvnitř opravdu poslouchá."
    },
    "automation-shell": {
      "title": "Bezpečná automatizace a idempotence",
      "summary": "Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.",
      "body": [
        "Idempotentní druhý průchod nemá vytvářet další změny.",
        "Dry-run/plan umožňuje zkontrolovat scope.",
        "Automatizace bez guardů násobí chybu rychleji než ruční práce."
      ],
      "example": "Script nejdřív ověří config, teprve potom provede reload a následný health check."
    },
    "packet-diagnostics-advanced": {
      "title": "Pokročilá packet diagnostika",
      "summary": "Packet capture má odpovědět na konkrétní otázku a musí být korelovaný se socket state na serveru.",
      "body": [
        "SYN timeout, RST a SYN-ACK vedou k různým hypotézám.",
        "Capture bez filtru rychle vytváří šum.",
        "TCP úspěch neznamená automaticky funkční TLS/HTTP."
      ],
      "example": "SYN→RST + žádný listener na portu = silná evidence server-side service problem."
    },
    "incident-runbook": {
      "title": "Incident runbook a koordinace",
      "summary": "Incident response je strukturovaný proces: impact, role, evidence, mitigation, validation a follow-up.",
      "body": [
        "Mitigation může mít přednost před detailním root cause, pokud rychle obnoví službu bezpečným rollbackem.",
        "Timeline odděluje fakta od hypotéz.",
        "Postmortem má konkrétní akce s ownerem a výsledkem."
      ],
      "example": "P1 incident: incident lead koordinuje, investigator testuje, communicator poskytuje stav; změny jdou přes decision log."
    }
  }
}
JSON, true, 512, JSON_THROW_ON_ERROR);
