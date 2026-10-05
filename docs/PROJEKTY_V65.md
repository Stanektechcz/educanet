# Projekty, rubriky, peer review a portfolio (v65)

Jedna obrazovka pro žáka, jedna pro učitele. Technické detaily: `BUILD_MANIFEST_V65.md`, změny: `CHANGELOG_V65.md`.

## Žák

**Kde:** Projekty → „Cyklus projektu a portfolio“ (`?view=projekt65`), portfolio `?view=portfolio`.

1. **Začni projekt.** Malý projekt začíná rovnou (zadal ho učitel). U plného projektu pošleš nejdřív návrh (20–600 znaků) a čekáš na schválení.
2. **Pracuj.** Týmové projekty mají volitelné milníky (K udělání / Pracujeme / Hotovo), deník přínosu (max. 280 znaků, vidí ho tým a učitel)
   a rozdělení 100 bodů mezi ostatní členy podle přínosu. Rozdělení je jen návrh, potvrzuje ho učitel.
3. **Odevzdej** odkaz a/nebo krátkou poznámku. Po odevzdání čekáš na hodnocení.
4. **Peer review (dobrovolné, jen u plných projektů).** Dostaneš 2 práce k posouzení: úroveň 1–4 u kritérií, **1 silná stránka + 1 konkrétní návrh**
   (20–400 znaků, s nápovědou vět). Jsi anonymní, text před zobrazením schvaluje učitel a na známku nemá vliv. Když práce odevzdají méně než 3 žáci, peer review se přeskočí.
5. **Hodnocení.** Rubrika (3–5 kritérií × úrovně 1–4) navrhne známku, rozhoduje učitel. Pokud tvoje práce zvýší kompetenci, zapíše se důkaz (pilot 3.A a 1.A).
   Při novém hodnocení po přepracování platí nejnovější verze.
6. **Portfolio.** Vyber práce a ke každé napiš reflexi (max. 600 znaků). Stáhneš ji jako HTML soubor nebo vytiskneš do PDF (Ctrl+P). **Sdílení odkazem neexistuje.**
   Export neobsahuje poznámky učitele určené jen jemu ani texty spolužáků.

Zámky: některé projekty klientů vyžadují zvládnutou kompetenci (jen v pilotních třídách). Chybějící kompetence uvidíš přímo u projektu.

## Učitel

**Kde:** cockpit → Podpora → „Cyklus projektů“. Vyžaduje oprávnění `projects.manage` (asistent jen čte jiné záložky).

- **Nastavení projektu:** režim malý (výchozí) / plný, peer review a milníky zapni jen tam, kde se vyplatí. Rubriku naklonuješ a upravíš (texty úrovní, kompetence u kritérií).
- **Návrhy:** zaškrtni a schval hromadně (max. 50 najednou, rozsah tříd se kontroluje u každé položky), nebo vrať k úpravě.
- **Odevzdané práce:** vyber úroveň 1–4 u kritérií. Úroveň 1 vyžaduje komentář (aspoň 20 znaků). „Uložit koncept“ nic nezveřejní a nevytvoří důkaz,
  „Publikovat“ zveřejní hodnocení (nová verze) a zapíše důkaz kompetence. Známku můžeš přepsat.
- **Peer review:** otevři ho tlačítkem u projektu (min. 3 odevzdané práce). Vidíš recenzenta, text schválíš nebo zamítneš, vulgární texty jsou označené.
  U recenzenta vidíš kalibraci vůči tobě (Pearson od 6 párů, odchylka ≥ 2 úrovně nebo MAE > 1 se označí).
- **Týmy:** faktor přínosu 0,8–1,2 je jen návrh z rozdělení bodů; do hodnocení týmu ho zapíšeš tlačítkem „Potvrdit“. Tým do 2 členů faktor nemá.
- **Zámky projektů klientů:** ve formuláři projektu (záložka Projekty) vyber až 3 kompetence a minimální stav; mimo pilot se ignorují.

## Uchování dat
Pitch, poznámky k odevzdání, peer texty, deník, rozdělení bodů a reflexe portfolia se uchovají do konce studia a smažou se 30 dní po odchodu žáka
(`tools/v58_retention.php --apply`, spouští denní cron).
