<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

$graphicsFoundation = [
    'id'=>'graphics_cert_1','title'=>'Visual Foundations Certification','unlock_level'=>10,'pass_score'=>5,
    'summary'=>'Prestižní průřezová zkouška z hierarchie, kontrastu, gridu, typografie, rastrové/vektorové grafiky a exportu.',
    'questions'=>[
        ['q'=>'Která změna nejspolehlivěji posílí vizuální hierarchii bez přidávání další barvy?','options'=>['Zvětšit rozdíl velikostí, vah a prostoru mezi rolemi.','Použít stejnou velikost textu všude.','Přidat více dekorací kolem každého prvku.'],'correct'=>0],
        ['q'=>'Co je hlavní funkcí gridu?','options'=>['Vytvářet společné zarovnávací linie a rytmus.','Automaticky zvolit nejlepší font.','Zaručit, že návrh bude vždy symetrický.'],'correct'=>0],
        ['q'=>'Pro běžný webový text je důležité především…','options'=>['ověřit dostatečný kontrast textu vůči pozadí.','použít co nejvíce barev.','vyhnout se jakémukoli bílému prostoru.'],'correct'=>0],
        ['q'=>'Kdy je výhodnější SVG než PNG?','options'=>['U loga nebo ikony, která se má škálovat bez ztráty kvality.','U fotografie s tisíci barevnými přechody.','Když potřebujeme vždy největší datový soubor.'],'correct'=>0],
        ['q'=>'Co je správný princip responzivního redesignu?','options'=>['Zachovat obsahové priority, ale změnit layout pro menší šířku.','Pouze zmenšit celý desktop návrh na 25 %.','Na mobilu skrýt všechny důležité informace.'],'correct'=>0],
        ['q'=>'Dobrý export pro web znamená…','options'=>['nejmenší soubor, který stále splní vizuální cíl.','vždy PNG v maximálním rozlišení.','vždy stejný formát bez ohledu na obsah.'],'correct'=>0],
    ],
];

$graphicsSystems = [
    'id'=>'graphics_cert_2','title'=>'Design Systems & Product UI Exam','unlock_level'=>20,'pass_score'=>5,
    'summary'=>'Pokročilá zkouška z komponent, stavů, tokenů, přístupnosti, prototypování a argumentace návrhových rozhodnutí.',
    'questions'=>[
        ['q'=>'Design token má smysl hlavně proto, že…','options'=>['sjednocuje opakované hodnoty a usnadňuje změny napříč systémem.','nahrazuje veškeré komponenty jedním obrázkem.','slouží jen k pojmenování barev v prezentaci.'],'correct'=>0],
        ['q'=>'Komponenta tlačítka by měla mít…','options'=>['definované stavy jako default, hover, focus a disabled.','pouze jeden vizuální stav.','jiný font v každém použití.'],'correct'=>0],
        ['q'=>'Co je vhodné ověřit při usability testu?','options'=>['Zda uživatel dokončí konkrétní scénář a kde se zasekne.','Zda se návrh líbí autorovi.','Kolik efektů lze přidat na jednu obrazovku.'],'correct'=>0],
        ['q'=>'Přístupný focus stav má být…','options'=>['viditelný a konzistentní pro ovládání klávesnicí.','záměrně neviditelný.','nahrazen pouze hoverem.'],'correct'=>0],
        ['q'=>'Proč dokumentovat komponenty?','options'=>['Aby tým chápal účel, varianty, stavy a pravidla použití.','Aby se zvýšil počet souborů v projektu.','Aby se zabránilo opakovanému použití.'],'correct'=>0],
        ['q'=>'Silná obhajoba návrhu stojí na…','options'=>['cíli, důkazech, omezeních a vysvětlení trade-offů.','osobním vkusu bez kontextu.','počtu použitých pluginů.'],'correct'=>0],
    ],
];

$networkFoundation = [
    'id'=>'network_cert_1','title'=>'Network Troubleshooting Certification','unlock_level'=>10,'pass_score'=>5,
    'summary'=>'Prestižní zkouška z adresace, DNS, DHCP, portů, evidence-first diagnostiky a bezpečného ověřování.',
    'questions'=>[
        ['q'=>'Klient pingne 1.1.1.1, ale nslookup timeoutuje. Kde začít?','options'=>['U DNS serveru, jeho dosažitelnosti a konfigurace.','U grafické karty.','U reinstalace celého OS.'],'correct'=>0],
        ['q'=>'Co typicky poskytuje DHCP?','options'=>['IP adresu, masku/prefix, gateway a často DNS.','TLS certifikát webu.','Obsah DNS zóny autoritativního serveru.'],'correct'=>0],
        ['q'=>'Proč je lepší měnit jednu věc a znovu měřit?','options'=>['Udrží se kauzalita a lze určit, která změna měla efekt.','Je to pomalejší, proto se tomu máme vyhnout.','Protože logy pak nejsou potřeba.'],'correct'=>0],
        ['q'=>'K čemu primárně slouží port?','options'=>['K rozlišení síťové služby/procesu na hostiteli.','K identifikaci výrobce kabelu.','K určení MAC adresy routeru.'],'correct'=>0],
        ['q'=>'Co je dobrý důkaz funkční služby?','options'=>['Úspěšný test konkrétního protokolu/služby, ne jen ping hostitele.','Pouze to, že počítač svítí.','To, že byl restartován.'],'correct'=>0],
        ['q'=>'Princip minimálních oprávnění znamená…','options'=>['povolit jen to, co je pro úkol skutečně potřeba.','povolit všechny porty a později případně blokovat.','sdílet jeden administrátorský účet pro všechny.'],'correct'=>0],
    ],
];

$networkProduction = [
    'id'=>'network_cert_2','title'=>'Production Reliability Exam','unlock_level'=>20,'pass_score'=>5,
    'summary'=>'Pokročilá zkouška z observability, reverse proxy, změnového řízení, canary release, rollbacku a incident komunikace.',
    'questions'=>[
        ['q'=>'Canary error rate překročí předem danou stop condition. Co udělat?','options'=>['Zastavit rollout a vrátit canary do známého funkčního stavu.','Pokračovat na 100 %, protože rollout už začal.','Vypnout alert.'],'correct'=>0],
        ['q'=>'HTTP 502 z reverse proxy nejčastěji ukazuje na problém…','options'=>['mezi proxy a upstream backendem nebo v backendu.','v rozlišení monitoru klienta.','v lokální klávesnici.'],'correct'=>0],
        ['q'=>'Co má existovat před rizikovou změnou?','options'=>['Success criteria, stop condition a rollback plán.','Jen seznam lidí na směně.','Pouze screenshot původní stránky.'],'correct'=>0],
        ['q'=>'Co je SLI?','options'=>['Měřitelný indikátor chování služby, například dostupnost nebo latency.','Název firewall pravidla.','Typ datového kabelu.'],'correct'=>0],
        ['q'=>'Dobrý incident report obsahuje…','options'=>['symptom, časovou osu/evidence, příčinu, opravu a ověření.','pouze větu „už to funguje“.','jen seznam příkazů bez závěru.'],'correct'=>0],
        ['q'=>'Nejbezpečnější rollback je…','options'=>['předem připravený, reprodukovatelný a ověřený postup návratu.','improvizace až po výpadku.','smazání logů a nový deploy.'],'correct'=>0],
    ],
];

return [
    'class_1a'=>[$graphicsFoundation,$graphicsSystems],
    'class_2a'=>[$graphicsFoundation,$graphicsSystems],
    'class_3a'=>[$networkFoundation,$networkProduction],
    'class_4a'=>[$networkFoundation,$networkProduction],
];
