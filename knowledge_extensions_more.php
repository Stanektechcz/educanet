<?php

declare(strict_types=1);

return [
 'class_1a'=>[
  'responsive-series'=>[
   'title'=>'Responzivní série: jeden nápad, více formátů',
   'summary'=>'Nauč se převést jeden vizuální koncept do postu, čtverce a story bez ztráty hierarchie, čitelnosti a identity.',
   'body'=>[
    'Responzivní grafika není prosté zmenšení nebo ořez. Každý formát má jiný poměr stran, jiný prostor pro text a jiný způsob rychlého čtení.',
    'Nejdřív zachovej informační pořadí: headline → klíčová informace → CTA. Teprve potom přeskupuj prvky podle prostoru.',
    'Pracuj se safe zone: důležité texty a CTA nedávej těsně k hraně. U story počítej i s překryvy rozhraní aplikace.',
   ],
   'example'=>'Master 1080×1350 → square 1080×1080 → story 1080×1920. Stejná paleta a typografické role, ale jiné rozmístění a crop.',
  ],
 ],
 'class_2a'=>[
  'accessibility-design'=>[
   'title'=>'Přístupný design: kontrast, velikost, focus',
   'summary'=>'Design musí zůstat čitelný a ovladatelný i v horších podmínkách, na menším displeji nebo při omezeném vnímání barev.',
   'body'=>[
    'Kontrast není estetický bonus. Je to podmínka, aby text a ovládací prvky zůstaly rozpoznatelné.',
    'Barevný stav nesmí být jediný nositel významu. Chybu doplň ikonou nebo textem; aktivní focus musí být viditelný.',
    'Při návrhu kontroluj běžný text, velký text, CTA a interaktivní stavy samostatně. Thumbnail a grayscale test odhalí problémy velmi rychle.',
   ],
   'example'=>'Primary button: dostatečný kontrast textu, min. 44px click target, viditelný focus ring a disabled stav rozlišený i bez barvy.',
  ],
 ],
 'class_3a'=>[
  'packet-analysis'=>[
   'title'=>'Paketová diagnostika: od ARP po TCP',
   'summary'=>'Nauč se číst jednoduchou packet journey a z minimální evidence určit, ve které vrstvě komunikace selhává.',
   'body'=>[
    'Packet capture není kouzelná odpověď. Nejprve formuluj hypotézu a teprve potom filtruj relevantní provoz.',
    'ARP řeší lokální mapování IP na MAC, DNS překlad jmen a TCP navázání spojení. Každá fáze vytváří jiný typ evidence.',
    'Sleduj směr paketů, odpovědi a čas. Chybějící odpověď je jiný důkaz než explicitní RST nebo DNS NXDOMAIN.',
   ],
   'example'=>'DNS query odejde, odpověď se nevrátí → zkoumej DNS cestu/server. SYN odejde a přijde RST → cíl je dosažitelný, ale port není otevřený.',
  ],
 ],
 'class_4a'=>[
  'slo-postmortem'=>[
   'title'=>'SLO, error budget a postmortem',
   'summary'=>'Provozní rozhodování musí stát na měřených cílech. SLO říká očekávanou spolehlivost, error budget prostor pro riziko a postmortem pomáhá zabránit opakování incidentu.',
   'body'=>[
    'SLO je měřitelný cíl služby, například 99,9 % úspěšných requestů za 30 dní. Není to totéž co jednorázový uptime screenshot.',
    'Error budget převádí SLO na tolerovatelný prostor pro chyby. Když se rozpočet rychle spotřebovává, je rozumné zpomalit změny a zaměřit se na stabilitu.',
    'Blameless postmortem popisuje dopad, časovou osu, technickou příčinu, přispívající faktory a konkrétní follow-up akce s vlastníkem.',
   ],
   'example'=>'SLO 99,9 % / 30 dní ≈ 43 min 12 s tolerovaného výpadku. Incident 55 min znamená, že budget je překročen a další riskantní rollout potřebuje silné zdůvodnění.',
  ],
 ],
];
