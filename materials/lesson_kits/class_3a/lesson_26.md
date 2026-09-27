# Lekce 26 · Service recovery drill

**Třída:** 3.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

## 1. Doporučená videa

### Nefunguje internet: jak najít příčinu výpadku připojení

- URL: https://www.youtube.com/watch?v=gGwsOpNOmr0
- Délka: 50 min
- Úroveň: Začátečník → středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Nginx v roli web serveru

- URL: https://www.youtube.com/watch?v=MRpKBh7J0eo
- Délka: cca 50 min
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · Diagnostika služby II · DNS → TCP → TLS → HTTP** — Spoj předchozí síťové znalosti do jedné rychlé diagnostické cesty a rozhoduj podle evidence, ve které vrstvě se problém nachází. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · Linux webová služba: end-to-end cesta** — End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 26 · Service recovery drill

**Start**  
Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · Diagnostika služby II · DNS → TCP → TLS → HTTP

**Mentální model**  
Spoj předchozí síťové znalosti do jedné rychlé diagnostické cesty a rozhoduj podle evidence, ve které vrstvě se problém nachází.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · Linux webová služba: end-to-end cesta

**Mentální model**  
End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 5 · Nejdřív celý vzor

**Worked example**  
Symptom → hypotéza → nejmenší test → evidence → bezpečná změna → validace.

> Poznámka pro výklad: Další příklad už nech s jedním chybějícím krokem.

### Slide 6 · Co se často plete

**Typická chyba**  
Pozitivní test jedné vrstvy neznamená, že celý systém funguje end-to-end.

> Poznámka pro výklad: Použij misconception data třídy, pokud existují.

### Slide 7 · Teď to udělej ty

**Practice**  
Vyřeš jeden nový případ. Zapiš předpověď, zásah a evidence, která výsledek potvrzuje nebo vyvrací.

> Poznámka pro výklad: Učitel obchází třídu a ptá se „co tímto krokem dokazuješ?“.

### Slide 8 · Stejný princip, jiný kontext

**Transfer**  
Neopakuj povrchový postup. Rozpoznej stejný princip v jiné situaci a znovu jej ověř.

> Poznámka pro výklad: Silná evidence mastery vzniká až při přenosu do nového kontextu.

### Slide 9 · Jedna věta, jeden důkaz

**Exit ticket**  
Vysvětli vlastními slovy nejdůležitější princip lekce a uveď, jak bys ověřil/a, že opravdu funguje.

> Poznámka pro výklad: Z odpovědí vytvoř starter příští hodiny.

## 3. One-pager / tahák

### Cíl

Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

### Klíčové principy
- Diagnostika služby II · DNS → TCP → TLS → HTTP
- Linux webová služba: end-to-end cesta

### Hotovo znamená
- [ ] Mám pozorovaný symptom.
- [ ] Každý test má hypotézu.
- [ ] Změnil/a jsem jednu věc.
- [ ] Výsledek jsem ověřil/a z pohledu klienta.

## 4. Pracovní list

- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

**Rozšíření:** Vytvoř podobný incident s jinou kořenovou příčinou a napiš, jaké evidence mají spolužáka navést bez prozrazení řešení.

## 5. Mini slovník

- **Diagnostika služby II · DNS → TCP → TLS → HTTP** — Spoj předchozí síťové znalosti do jedné rychlé diagnostické cesty a rozhoduj podle evidence, ve které vrstvě se problém nachází.
- **Linux webová služba: end-to-end cesta** — End-to-end diagnostika spojuje process, listener, proxy/firewall, DNS a HTTP.

## 6. AI prompty pro studenta

### Vysvětli mi to jednoduše

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Vysvětli mi princip co nejjednodušeji v 5–7 větách. Použij jeden konkrétní příklad a na konci mi polož jednu kontrolní otázku. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Přirovnání + hranice přirovnání

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Vysvětli téma pomocí přirovnání z běžného života. Pak výslovně napiš, kde přirovnání přestává platit, abych si nevytvořil chybný mentální model. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Sokratický tutor

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Buď můj sokratický tutor. Nepřednášej. Ptej se vždy jen na jednu otázku, vycházej z mé odpovědi a veď mě k tomu, abych princip odvodil sám. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Najdi díru v mém postupu

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Pošlu ti svůj postup/řešení. Najdi první místo, kde můj závěr není podložený důkazem nebo kde měním příliš mnoho věcí najednou. Neopravuj vše za mě; polož otázku, která mě dovede k lepšímu testu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Vytvoř mi nový transfer úkol

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Vytvoř jeden nový realistický problém, který používá stejný princip, ale v jiném kontextu. Nedávej řešení. Nejdřív chtěj moji hypotézu, potom důkaz a až nakonec mi dej zpětnou vazbu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Vyzkoušej mě ústně

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Simuluj ústní zkoušení. Polož 5 otázek od základní po aplikační. Po každé mé odpovědi stručně řekni, co bylo přesné, co chybělo, a polož navazující otázku. Známku navrhni až na konci. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Praktická zkouška nanečisto

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Připrav krátkou praktickou zkoušku nanečisto na 15–20 minut. Dej mi realistické zadání, success criteria a 3 kontrolní body. Během řešení mi neposkytuj hotový postup; reaguj jen na konkrétní evidence, které ti pošlu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Zkontroluj moje vysvětlení

```text
Jsem student EDUCANETu. Probírám Lekce 26 · Service recovery drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.. Klíčová témata: Diagnostika služby II · DNS → TCP → TLS → HTTP, Linux webová služba: end-to-end cesta.
Pošlu ti své vysvětlení tématu. Ohodnoť pouze: správnost mentálního modelu, práci s důkazem, schopnost přenést princip a jednu největší mezeru. Pak mi dej jediný další krok k opravě. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „Diagnostika služby II · DNS → TCP → TLS → HTTP“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „Linux webová služba: end-to-end cesta“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Který postup je nejlepší při diagnostice?
- Který postup je nejlepší při diagnostice?
- Dostaneš nový symptom. Jaký nejmenší bezpečný test bys provedl/a jako první a proč?

### Praktická část

Vyřeš nový incident bez restartu „naslepo“. Odevzdej symptom, hypotézu, nejmenší test, evidence, jednu bezpečnou změnu a pozitivní + negativní validaci.

### Self-check
- [ ] Umím princip vysvětlit bez opisování definice.
- [ ] Umím poznat typickou chybu.
- [ ] Umím uvést důkaz, ne jen výsledek.
- [ ] Umím princip použít v novém kontextu.
- [ ] Vím, co bych ověřil/a jako další krok.

### Rubrika
- **Rozvíjí se** — Zná pojmy, ale závěr ještě často stojí na intuici nebo kopírování postupu.
- **Kompetentní** — Použije princip ve známé situaci a dokáže uvést základní evidence.
- **Silný výkon** — Samostatně volí vhodný postup, vysvětluje důvod a validuje výsledek.
- **Mastery** — Přenese princip do nové situace, rozpozná limity důkazu a umí postup obhájit.

