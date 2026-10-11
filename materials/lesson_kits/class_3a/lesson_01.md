# Lekce 1 · Základní dvouhodinový blok

**Třída:** 3.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku.

## 1. Doporučená videa

### Jak fungují IP adresy?

- URL: https://www.youtube.com/watch?v=J1nudfAQCDE
- Délka: 35 min
- Úroveň: Začátečník
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Nefunguje internet: jak najít příčinu výpadku připojení

- URL: https://www.youtube.com/watch?v=gGwsOpNOmr0
- Délka: 50 min
- Úroveň: Začátečník → středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### SSH nejen pro vzdálenou správu Linuxu

- URL: https://www.youtube.com/watch?v=mWB2ralMAG0
- Délka: 50 min
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · IP adresy, maska a privátní rozsahy** — Jak poznat síť, hosta a rozdíl mezi privátní a veřejnou adresou. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · DNS bez magie** — Doménová jména, resolvery a základní záznamy. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 1 · Základní dvouhodinový blok

**Start**  
Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · IP adresy, maska a privátní rozsahy

**Mentální model**  
Jak poznat síť, hosta a rozdíl mezi privátní a veřejnou adresou.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · DNS bez magie

**Mentální model**  
Doménová jména, resolvery a základní záznamy.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 5 · DHCP a automatická konfigurace

**Mentální model**  
Jak klient získá IP, masku, bránu a DNS.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 6 · Nejdřív celý vzor

**Worked example**  
Symptom → hypotéza → nejmenší test → evidence → bezpečná změna → validace.

> Poznámka pro výklad: Další příklad už nech s jedním chybějícím krokem.

### Slide 7 · Co se často plete

**Typická chyba**  
Pozitivní test jedné vrstvy neznamená, že celý systém funguje end-to-end.

> Poznámka pro výklad: Použij misconception data třídy, pokud existují.

### Slide 8 · Teď to udělej ty

**Practice**  
Vyřeš jeden nový případ. Zapiš předpověď, zásah a evidence, která výsledek potvrzuje nebo vyvrací.

> Poznámka pro výklad: Učitel obchází třídu a ptá se „co tímto krokem dokazuješ?“.

### Slide 9 · Stejný princip, jiný kontext

**Transfer**  
Neopakuj povrchový postup. Rozpoznej stejný princip v jiné situaci a znovu jej ověř.

> Poznámka pro výklad: Silná evidence mastery vzniká až při přenosu do nového kontextu.

### Slide 10 · Jedna věta, jeden důkaz

**Exit ticket**  
Vysvětli vlastními slovy nejdůležitější princip lekce a uveď, jak bys ověřil/a, že opravdu funguje.

> Poznámka pro výklad: Z odpovědí vytvoř starter příští hodiny.

## 3. One-pager / tahák

### Cíl

Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku.

### Klíčové principy
- IP adresy, maska a privátní rozsahy
- DNS bez magie
- DHCP a automatická konfigurace
- Porty a služby

### Hotovo znamená
- [ ] Mám pozorovaný symptom.
- [ ] Každý test má hypotézu.
- [ ] Změnil/a jsem jednu věc.
- [ ] Výsledek jsem ověřil/a z pohledu klienta.

## 4. Pracovní list

- Předpověď: co očekávám před změnou/testem?
- Evidence: co přesně jsem pozoroval/a?
- Závěr: co z evidence plyne a co ještě ne?
- Transfer: kde jinde se stejný princip objeví?

**Rozšíření:** Vytvoř podobný incident s jinou kořenovou příčinou a napiš, jaké evidence mají spolužáka navést bez prozrazení řešení.

## 5. Mini slovník

- **IP adresy, maska a privátní rozsahy** — Jak poznat síť, hosta a rozdíl mezi privátní a veřejnou adresou.
- **DNS bez magie** — Doménová jména, resolvery a základní záznamy.
- **DHCP a automatická konfigurace** — Jak klient získá IP, masku, bránu a DNS.
- **Porty a služby** — Proč IP adresa nestačí a co znamená port.
- **SSH a SFTP** — Vzdálený shell, klíče a bezpečný přenos souborů.

## 6. AI prompty pro studenta

> **Dobrovolné procvičení s AI – než začneš:**
> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.
> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.
> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.
> - Výstup AI vždy ověř (v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele). AI se plete i s jistotou.
> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.

### 1. Vysvětli mi jednoduše: IP adresy, maska a privátní rozsahy

```text
Učím se téma „IP adresy, maska a privátní rozsahy“ (lekce „Start: diagnostika a IP plán sítě“, obor Linux a počítačové sítě). Z hodiny vím: Jak poznat síť, hosta a rozdíl mezi privátní a veřejnou adresou.
Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ze správy malé firemní nebo školní sítě. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.
```

### 2. Přirovnání a jeho hranice: DNS bez magie

```text
V lekci „Start: diagnostika a IP plán sítě“ jsme probírali téma „DNS bez magie“. Vysvětli mi ho pomocí přirovnání z běžného života. Z hodiny vím: Doménová jména, resolvery a základní záznamy.
Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.
```

### 3. Sokratický tutor k cíli lekce

```text
Cíl lekce „Start: diagnostika a IP plán sítě“: Ověřit znalost adresace a služeb a navrhnout funkční IP plán malé sítě včetně důkazu, že funguje.
Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.
```

### 4. Zkontroluj můj postup

```text
Řeším úkol z lekce „Start: diagnostika a IP plán sítě“: Zadání sítě: Navrhni síť pro učebnu: 20 stanic, 1 tiskárna, 1 server, 1 router. Zvol privátní rozsah a masku, která se do zadání vejde s rezervou.
Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.
```

### 5. Nový procvičovací úkol

```text
Vymysli mi jeden nový realistický úkol na stejný princip jako lekce „Start: diagnostika a IP plán sítě“ („IP adresy, maska a privátní rozsahy“, „DNS bez magie“, „DHCP a automatická konfigurace“, „Systematický troubleshooting sítě“), ale v jiné situaci – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).
Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.
```

### 6. Vyzkoušej mě

```text
Opakuji si lekci „Start: diagnostika a IP plán sítě“. Polož mi 5 otázek k tématům „IP adresy, maska a privátní rozsahy“, „DNS bez magie“, „DHCP a automatická konfigurace“, „Systematický troubleshooting sítě“ od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.
Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „IP adresy, maska a privátní rozsahy“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „DNS bez magie“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „DHCP a automatická konfigurace“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Dostaneš nový symptom. Jaký nejmenší bezpečný test bys provedl/a jako první a proč?
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

