<?php

declare(strict_types=1);

return [
 'class_1a'=>[
  'responsive-series'=>[
   'time'=>'10–14 min','level'=>'Základ+','visual'=>['type'=>'sequence','items'=>[['Master','1080×1350'],['Square','1080×1080'],['Story','1080×1920'],['QA','thumbnail + safe zone']]],
   'mental'=>'Nepřenášej souřadnice. Přenášej hierarchii, role a vizuální systém.','steps'=>['Urči nezměnitelné prvky.','Založ cílový formát.','Přesuň hlavní hierarchii.','Uprav crop a spacing.','Ověř safe zone a thumbnail.'],
   'mistakes'=>['Pouze oříznout master.','Posunout CTA mimo bezpečnou oblast.','Změnit v každém formátu jiný font nebo paletu.'],
   'check'=>['q'=>'Co se má mezi formáty zachovat nejvíc?','options'=>['Hierarchie, role a vizuální identita.','Přesná pixelová pozice všech prvků.','Stejný crop za každou cenu.'],'correct'=>0,'why'=>'Formát se mění, ale systém a pořadí komunikace musí zůstat konzistentní.'],
  ],
 ],
 'class_2a'=>[
  'accessibility-design'=>[
   'time'=>'11–15 min','level'=>'Střední','visual'=>['type'=>'layers','items'=>[['Contrast','text/background','čitelnost'],['Size','text + target','rozpoznání'],['Focus','keyboard','orientace'],['State','icon + text','význam']]],
   'mental'=>'Přístupnost je funkční kvalita návrhu. Dobrý vizuál nesmí záviset na ideálním zraku, displeji nebo způsobu ovládání.','steps'=>['Změř kontrast.','Ověř velikost textu.','Přidej viditelný focus.','Ověř stav bez barvy.','Zkontroluj grayscale a zoom.'],
   'mistakes'=>['Šedý text na světle šedém pozadí.','Focus odstraněný bez náhrady.','Error = pouze červená barva.'],
   'check'=>['q'=>'Která úprava nejlépe zvyšuje přístupnost tlačítka?','options'=>['Dostatečný kontrast + viditelný focus + srozumitelný label.','Pouze výraznější gradient.','Menší text, aby se vešlo více obsahu.'],'correct'=>0,'why'=>'Přístupnost kombinuje čitelnost, ovladatelnost a srozumitelný stav.'],
  ],
 ],
 'class_3a'=>[
  'packet-analysis'=>[
   'time'=>'12–16 min','level'=>'Praktické','visual'=>['type'=>'sequence','items'=>[['ARP','kdo má gateway?'],['DNS','jaká je IP?'],['TCP','SYN/SYN-ACK'],['App','request/response']]],
   'mental'=>'Capture čti jako časovou osu důkazů. Nejdřív filtruj podle otázky, kterou chceš zodpovědět.','steps'=>['Definuj symptom.','Vyber relevantní filtr.','Najdi request.','Najdi odpověď nebo její absenci.','Propoj packet evidence s další diagnostikou.'],
   'mistakes'=>['Capture bez filtru a bez hypotézy.','Zaměnit RST za timeout.','Z jednoho paketu usoudit celý root cause.'],
   'check'=>['q'=>'SYN odejde a ihned přijde RST. Co to nejlépe znamená?','options'=>['Cíl je dosažitelný, ale daný port/spojení je odmítnuté.','DNS určitě nefunguje.','Klient nemá IP adresu.'],'correct'=>0,'why'=>'RST je aktivní TCP odpověď; síťová cesta alespoň do cíle funguje.'],
  ],
 ],
 'class_4a'=>[
  'slo-postmortem'=>[
   'time'=>'12–17 min','level'=>'Pokročilé','visual'=>['type'=>'flow','items'=>[['SLI','co měříme'],['SLO','cílová úroveň'],['Budget','tolerované chyby'],['Incident','dopad'],['Postmortem','follow-up']]],
   'mental'=>'SLO převádí „má to fungovat dobře“ na měřitelný cíl a rozhodovací pravidlo.','steps'=>['Vyber user-facing SLI.','Nastav SLO a okno.','Spočítej budget.','Vyhodnoť dopad incidentu.','Sepiš postmortem a follow-up.'],
   'mistakes'=>['SLO bez měřitelného SLI.','Postmortem hledající viníka místo systému.','Follow-up bez vlastníka a termínu.'],
   'check'=>['q'=>'Co je nejdůležitější výstup dobrého postmortemu?','options'=>['Konkrétní ověřitelné follow-up akce, které snižují pravděpodobnost/opad opakování.','Jméno člověka, který udělal poslední změnu.','Co nejdelší dokument bez priorit.'],'correct'=>0,'why'=>'Cílem je systémové zlepšení, ne hledání viníka.'],
  ],
 ],
];
