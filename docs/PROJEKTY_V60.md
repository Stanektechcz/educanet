# Projekty podle levelu (v60) – návod pro učitele a otázky pro školu

Modul umožňuje nabídnout žákům skutečnou zakázku od klienta (mimo systém) jako motivaci k dosažení
určité úrovně (levelu) v aplikaci. Modul **neřeší** smluvní, daňové ani pracovněprávní stránky věci –
ty musí posoudit vedení školy / právník školy dřív, než se cokoli reálně nabídne. Seznam otevřených
otázek je na konci tohoto dokumentu.

## Jak to funguje pro žáka

1. Žák si otevře `Projekty` v menu účtu (vedle Obchodu). Vidí seznam otevřených nabídek pro svou třídu.
2. U každé nabídky vidí vždy: název, štítek klienta (bez kontaktu), veřejné shrnutí, požadované
   dovednosti, minimální úroveň a svoji aktuální úroveň, typ odměny, volná místa, termín.
3. Pokud žák nemá požadovanou úroveň, vidí jen text „Potřebuješ úroveň N (máš M)“ – detail zakázky
   a formulář přihlášky se mu vůbec nezobrazí (server ho ani neposílá).
4. Jakmile úroveň dosáhne, odemkne se detail a tlačítko „Projevit zájem“ s krátkým polem pro motivaci
   (max. 500 znaků). Žák si může přihlášku kdykoli stáhnout, dokud není vyřízená.

## Jak to funguje pro učitele (záložka „Projekty“)

- **Nový projekt**: vyplň název, štítek klienta (jen orientační, žádný kontakt), veřejné shrnutí
  (vidí všichni), detail (jen po dosažení levelu), dovednosti, minimální úroveň, typ odměny (Kč /
  portfolio / certifikát / jiné), poznámku k odměně (bez konkrétní částky, ta se do systému neukládá
  jako číslo), kapacitu, nepovinný termín a třídy, kterým se projekt nabízí.
- **Peněžní odměna (Kč)** automaticky vyžaduje souhlas zákonného zástupce – zaškrtávátko v UI se u ní
  ignoruje, server to vynutí vždy.
- **Stavy projektu**: koncept → otevřený (žáci se mohou hlásit) → uzavřený/hotovo. Měnit může jen
  učitel, který má danou třídu v rozsahu (nebo admin).
- **Přihlášky**: seznam se jménem žáka, úrovní při přihlášení a motivací. U placených projektů lze
  schválit až po zaškrtnutí „Souhlas zákonného zástupce ověřen“ – bez toho schválení neprojde.
- Admin vidí a spravuje vše, učitel jen projekty a přihlášky ve svých třídách, asistent má jen náhled
  (nemůže nic uložit ani schválit).

## Ochrana soukromí žáků

- Klient **nemá přístup do systému** a nedostává žádné osobní údaje žáka. Kontakt s klientem vždy
  zajišťuje škola (např. e-mailem mimo aplikaci) – nikdy nepředávej jméno, kontakt ani jiné údaje žáka
  klientovi bez výslovného souhlasu vedení školy a zákonného zástupce.
- V úložišti se ukládá jen `class_id` + `student_key` (interní klíč, ne jméno) a text motivace – žádné
  jiné osobní údaje, žádné kontakty klienta, žádná částka jako číslo (jen textová poznámka).

## Otevřené otázky, které musí posoudit škola (NEJSOU vyřešené kódem)

1. **Souhlas zákonného zástupce** – jakou formou se souhlas reálně získává a archivuje (papírově?
   elektronicky?) a kdo za jeho ověření odpovídá; aplikace jen zaznamená, že učitel souhlas potvrdil,
   nekontroluje jeho existenci.
2. **Práce mladistvých / DPP** – zda a jak lze žáky 14–19 let legálně zaměstnat na DPP/DPČ nebo jinou
   formou, včetně omezení pracovní doby u mladistvých a případné nutnosti souhlasu úřadu práce.
3. **Daně** – kdo řeší zdanění odměny (žák/zákonný zástupce), zda jde o příležitostný příjem a v jaké
   výši je ještě osvobozený.
4. **Smlouva škola–klient** – právní vztah mezi školou a klientem (objednávka, smlouva o dílo, NDA),
   odpovědnost za kvalitu výstupu a za škodu.
5. **Autorská práva** – komu patří výsledek práce žáka (klientovi, škole, žákovi) a za jakých podmínek.
6. **GDPR** – právní titul pro zpracování údajů žáka v souvislosti s projektem, doba uchování dat
   přihlášek, informování zákonných zástupců.
7. **Prověření klienta** – jak škola ověří důvěryhodnost klienta předtím, než se nabídka zveřejní
   žákům (reference, IČO, kontakt na školu jako prostředníka).
8. **BOZP** – pokud by projekt vyžadoval cokoli mimo běžnou školní práci u počítače (např. práci u
   klienta), je potřeba řešit bezpečnost a pojištění.

Do doby, než škola tyto body vyjasní, doporučujeme používat modul jen pro odměny typu portfolio/
certifikát a peněžní odměny nezveřejňovat.
