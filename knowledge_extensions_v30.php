<?php

declare(strict_types=1);

return json_decode(<<<'JSON'
{
  "class_1a": {
    "content-first-layout": {
      "title": "Content-first layout",
      "summary": "Nejdřív urči obsahovou prioritu, až potom kresli boxy a dekorace.",
      "body": [
        "Layout nezačíná gridem ani barvou. Začíná otázkou, co má uživatel pochopit jako první, druhé a třetí.",
        "Když se změní délka textu nebo velikost obrazovky, dobrý návrh zachová významovou hierarchii i bez přesných souřadnic.",
        "Před návrhem si proto připrav content inventory: headline, supporting text, CTA, důkaz/doplněk a sekundární informace."
      ],
      "example": "Headline: „Den otevřených dveří“ → supporting text: datum + pro koho → CTA: „Rezervovat návštěvu“."
    },
    "microcopy-cta": {
      "title": "Microcopy a CTA",
      "summary": "Krátký text v rozhraní má říkat, co se stane a proč má uživatel pokračovat.",
      "body": [
        "CTA není dekorativní tlačítko. Je to slib dalšího kroku, proto má používat konkrétní sloveso a být srozumitelný mimo kontext.",
        "Microcopy pomáhá u formulářů, chyb, prázdných stavů a nejistých momentů. Dobrá microcopy snižuje potřebu dalšího vysvětlování.",
        "Text by měl být stručný, ale ne kryptický. „Pokračovat“ je horší než „Odeslat přihlášku“, pokud lze akci pojmenovat přesně."
      ],
      "example": "Místo „OK“ použij „Uložit změny“. Místo „Chyba“ použij „E-mail nemá platný formát“."
    },
    "responsive-art-direction": {
      "title": "Responsive art direction",
      "summary": "Na různých formátech nemusí být stejný crop, ale význam a focal point musí zůstat.",
      "body": [
        "Jedna fotografie se na širokém hero, čtverci a mobilu chová jinak. Prosté zmenšování často uřízne hlavní subjekt nebo prostor pro text.",
        "Art direction znamená zvolit pro každý breakpoint takový výřez nebo variantu obrazu, která zachová význam.",
        "Kontroluj focal point, kontrast textové zóny a datovou velikost; obraz má podporovat obsah, ne mu překážet."
      ],
      "example": "Desktop hero použije široký crop s osobou vpravo, mobil portrait crop posune subjekt výš a ponechá čistou zónu pod ním."
    },
    "design-feedback": {
      "title": "Jak dávat design feedback",
      "summary": "Užitečný feedback popisuje cíl, pozorování a dopad – ne osobní vkus.",
      "body": [
        "„Nelíbí se mi to“ nepomáhá. Začni cílem: co má návrh sdělit nebo umožnit.",
        "Potom popiš pozorování bez hodnocení a dopad na uživatele. Nakonec navrhni otázku nebo směr k ověření.",
        "Autor návrhu by měl umět feedback přijmout, oddělit vlastní ego od práce a rozhodnout, co změnit na základě cíle a evidence."
      ],
      "example": "„CTA v miniatuře zaniká vedle obrázku, takže další krok není jasný. Zkus vyšší kontrast nebo klidnější okolí tlačítka.“"
    }
  },
  "class_2a": {
    "card-sorting": {
      "title": "Card sorting a informační architektura",
      "summary": "Card sorting pomáhá ověřit, jak lidé přirozeně seskupují informace.",
      "body": [
        "Informační architektura nemá kopírovat interní strukturu organizace. Má odpovídat mentálním modelům uživatelů.",
        "Při card sortingu účastník třídí položky do skupin a pojmenovává je. Opakující se vzory odhalují očekávané kategorie.",
        "Výsledek není automatický návrh menu; je to evidence pro další návrh a následný tree test."
      ],
      "example": "Položky „Cena“, „Tarify“, „Platby“ mohou uživatelé spojit pod „Ceník“, i když je firma interně spravuje ve třech odděleních."
    },
    "error-recovery-ux": {
      "title": "Error recovery UX",
      "summary": "Chyba má vysvětlit problém, ukázat opravu a zachovat co nejvíc uživatelovy práce.",
      "body": [
        "Validace není trest. Uživatel musí vědět, co je špatně, kde to opravit a zda ostatní data zůstala zachovaná.",
        "Inline validace je vhodná pro lokální chyby; souhrn chyb pomáhá u dlouhých formulářů a po submitu.",
        "Recovery cesta je součást návrhu stejně jako happy path – zahrnuje retry, návrat, obnovu dat a kontakt/pomoc."
      ],
      "example": "„Heslo musí obsahovat alespoň 10 znaků“ je užitečnější než „Neplatná hodnota“ a nemá smazat ostatní pole."
    },
    "interaction-accessibility": {
      "title": "Přístupné interakce",
      "summary": "Interakce musí fungovat klávesnicí, mít viditelný focus a nespoléhat jen na barvu či gesto.",
      "body": [
        "Klikatelný prvek musí být rozpoznatelný, dosažitelný klávesnicí a mít stav focus.",
        "Drag & drop, hover nebo barva nesmí být jediná cesta k funkci nebo informaci. Nabídni ekvivalentní ovládání.",
        "Stavy loading, error, selected a disabled potřebují srozumitelnou textovou nebo ikonovou reprezentaci."
      ],
      "example": "Přesun karty lze dělat drag & drop, ale také tlačítkem „Přesunout do…“ ovladatelným klávesnicí."
    },
    "design-handoff-qa": {
      "title": "Design handoff a QA",
      "summary": "Handoff předává pravidla a chování; QA ověřuje implementaci proti cíli, ne proti jednomu screenshotu.",
      "body": [
        "Developer potřebuje strukturu komponent, states, spacing/tokeny, responsive pravidla a edge cases.",
        "Design QA kontroluje funkčnost a systémovou konzistenci: různé viewporty, dlouhý obsah, chyby, focus, prázdné stavy.",
        "Issue má být reprodukovatelné: kde, za jakých podmínek, co se děje, co se očekává a jak závažné to je."
      ],
      "example": "„Na 375 px se CTA překrývá s cenou při dlouhém názvu produktu; očekáváme zalomení titulku a viditelné tlačítko.“"
    }
  },
  "class_3a": {
    "linux-file-troubleshooting": {
      "title": "Diagnostika souborů a filesystemu",
      "summary": "Než měníš práva nebo mažeš data, zjisti cestu, vlastníka, mount, kapacitu a skutečný symptom.",
      "body": [
        "Chyba „Permission denied“ může být vlastník, mode bits, chybějící execute právo na parent adresáři nebo read-only filesystem.",
        "„No space left“ nemusí znamenat jen plný disk; může dojít i místo v inode tabulce nebo můžeš zapisovat na jiný mount.",
        "Systematický postup kombinuje pwd/realpath, ls -la, stat, df -h, df -i, findmnt a bezpečný test zápisu."
      ],
      "example": "Aplikace nemůže zapisovat do /var/lib/app: df -h má rezervu, ale ls -ld ukáže root:root 0755 a proces běží jako appuser."
    },
    "ssh-key-operations": {
      "title": "SSH klíče v provozu",
      "summary": "SSH klíč je dvojice identity; diagnostika odděluje síť, výběr klíče, oprávnění a serverovou autorizaci.",
      "body": [
        "Privátní klíč zůstává u klienta, veřejný klíč se autorizuje na serveru. Klient může mít více identit a vybrat nesprávnou.",
        "Příkaz ssh -v ukáže, které klíče klient nabízí a kde autentizace selhává.",
        "Oprávnění ~/.ssh a authorized_keys jsou součást bezpečnosti; řešením není plošně povolit čtení všem."
      ],
      "example": "TCP/22 funguje, ale ssh -v ukáže „Offering public key…“ a server jej odmítne – problém už není firewall."
    },
    "stateful-firewall": {
      "title": "Stavový firewall",
      "summary": "Stavový firewall rozlišuje nové a navazující spojení a umožňuje přesnější least-privilege pravidla.",
      "body": [
        "Firewall neřeší jen port. Rozhoduje source, destination, protocol, state a pořadí pravidel.",
        "Established/related provoz umožňuje odpovědím projít bez otevření všech příchozích portů.",
        "Po změně pravidla vždy proveď pozitivní i negativní test: to, co má fungovat, i to, co má zůstat blokované."
      ],
      "example": "USERS → WEB tcp/443 NEW allow; odpovědi WEB → USERS projdou jako ESTABLISHED, ale USERS → WEB tcp/22 zůstane deny."
    },
    "bash-error-handling": {
      "title": "Bezpečné shell skripty",
      "summary": "Skript má kontrolovat předpoklady, selhat čitelně, logovat a být bezpečně opakovatelný.",
      "body": [
        "Automatizace zrychlí správný i chybný postup. Proto před změnou kontroluj vstupy a current state.",
        "Používej návratové kódy, explicitní chybové větve, quoting proměnných a logování významných kroků.",
        "Idempotentní skript můžeš spustit znovu bez nechtěného zdvojení nebo poškození stavu."
      ],
      "example": "Před přidáním uživatele skript ověří id appuser; pokud už existuje, změnu přeskočí a zapíše „already present“."
    }
  },
  "class_4a": {
    "golden-signals": {
      "title": "Golden signals observability",
      "summary": "Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál.",
      "body": [
        "Jedna metrika nestačí. Vysoká CPU bez chyb nemusí být incident, zatímco malá CPU s 50% error rate je kritická.",
        "Golden signals kombinují chování služby a využití zdrojů. Sleduj je v čase a porovnávej s deployem či změnou.",
        "Alert by měl vést k akci: musí mít vlastník, kontext a rozumný threshold, ne jen produkovat hluk."
      ],
      "example": "Po deployi p95 latency roste z 180 ms na 2.4 s, 5xx z 0.2 % na 8 % a DB connection pool je 100 % – silná stop-condition."
    },
    "container-networking": {
      "title": "Container networking",
      "summary": "Kontejner má vlastní network namespace; dostupnost služby závisí na bindu, port mappingu, síti a policy.",
      "body": [
        "localhost uvnitř kontejneru není localhost hostitele ani jiného kontejneru.",
        "Port publish mapuje host port na kontejnerový port; aplikace ale stále musí naslouchat na vhodné adrese.",
        "Diagnostika postupuje od procesu/socketu v kontejneru přes container network až po host firewall a reverse proxy."
      ],
      "example": "App poslouchá 127.0.0.1:8000 uvnitř kontejneru; publish 8000:8000 nestačí, protože socket není dostupný přes container interface."
    },
    "rollback-strategy": {
      "title": "Rollback a safe change",
      "summary": "Rollback musí být připraven před změnou, mít jasný trigger a ověřitelný návrat do známého stavu.",
      "body": [
        "„Když to nepůjde, vrátíme to“ není plán. Potřebuješ artefakt/verzi, data migration strategy a stop conditions.",
        "Ne každou databázovou změnu lze jednoduše vrátit; někdy je bezpečnější roll-forward s kompatibilními kroky.",
        "Po rollbacku se musí znovu ověřit user-facing služba, data integrity a monitoring."
      ],
      "example": "Canary má stop condition 5xx > 2 % po 5 minut; rollback přepne traffic na předchozí image digest a validuje health + syntetický test."
    },
    "incident-command": {
      "title": "Incident command a komunikace",
      "summary": "Při větším incidentu odděl rozhodování, technickou práci a komunikaci, aby tým nesoutěžil o pozornost.",
      "body": [
        "Incident Commander drží cíle, priority a rozhodnutí; technický lead koordinuje diagnostiku a comms role aktualizuje stakeholdery.",
        "Timeline zaznamenává fakta a změny. Hypotézy musí být od důkazů jasně oddělené.",
        "Po stabilizaci následuje blameless postmortem: dopad, příčina, přispívající faktory, co fungovalo a konkrétní follow-up."
      ],
      "example": "IC rozhodne freeze dalších deployů, tech lead rozdělí DB/proxy analýzu a comms každých 15 minut publikuje stav bez spekulací."
    }
  }
}
JSON, true, 512, JSON_THROW_ON_ERROR);
