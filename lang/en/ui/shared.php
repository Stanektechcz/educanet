<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „shared“ (angličtina).
 * Sdílené soubory bootstrap.php, index.php, student_v55.php, sw.js (integrátor); připravil
 * builder SHARED-I18N pro apply_shared_i18n.php. 12 msgid je totožných s jinými doménami
 * (auth/learn/hubs/study) – zde MUSÍ mít identický překlad (audit hlídá konflikty).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // --- bootstrap.php: password rules ------------------------------------
    'Heslo musí mít alespoň {min} znaků.' => 'The password must be at least {min} characters long.',
    'Heslo je příliš dlouhé.' => 'The password is too long.',
    'Heslo musí obsahovat alespoň jedno písmeno a jednu číslici.' => 'The password must contain at least one letter and one digit.',
    'Bývalé společné školní heslo použít nejde. Vymysli si vlastní.' => "You can't use the old shared school password. Come up with your own.",
    'Tohle heslo je moc běžné a snadno se uhodne. Zkus delší spojení slov a čísel.' => 'This password is too common and easy to guess. Try a longer combination of words and numbers.',
    'Heslo nesmí obsahovat tvoje jméno ani část e-mailu.' => 'The password must not contain your name or part of your email.',
    'Nové heslo musí být jiné než jednorázové heslo z kartičky.' => 'The new password must be different from the one-time password on your card.',

    // --- bootstrap.php: emails ---------------------------------------------
    'Ahoj {jmeno},' => 'Hi {jmeno},',
    'Ahoj,' => 'Hi,',
    'Pro dokončení registrace do EDUCANET Learning Lab ověř školní e-mail:' => 'To finish registering for EDUCANET Learning Lab, verify your school email:',
    'Odkaz platí 24 hodin. Pokud jsi registraci nevytvářel/a, zprávu ignoruj.' => "The link is valid for 24 hours. If you didn't create this registration, ignore this message.",
    'Ověření EDUCANET Learning Lab účtu' => 'EDUCANET Learning Lab account verification',
    'Pro nastavení nového hesla do EDUCANET Learning Lab otevři:' => 'To set a new password for EDUCANET Learning Lab, open:',
    'Odkaz platí {min} minut a funguje jen jednou. Pokud jsi reset nepožadoval/a, zprávu ignoruj.' => "The link is valid for {min} minutes and works only once. If you didn't request a reset, ignore this message.",
    'Obnovení hesla EDUCANET Learning Lab' => 'EDUCANET Learning Lab password reset',

    // --- bootstrap.php: Google sign-in -------------------------------------
    'Neplatný Google token.' => 'Invalid Google token.',
    'Google token nelze přečíst.' => 'The Google token could not be read.',
    'Google token má neplatnou hlavičku.' => 'The Google token has an invalid header.',
    'Nelze ověřit podpis Google účtu. Zkus přihlášení znovu.' => 'The Google account signature could not be verified. Try signing in again.',
    'Podpis Google účtu není platný.' => 'The Google account signature is not valid.',
    'Neplatný vydavatel Google tokenu.' => 'Invalid Google token issuer.',
    'Google token není určen pro tuto aplikaci.' => 'The Google token is not intended for this application.',
    'Google token expiroval.' => 'The Google token has expired.',
    'Použij školní Google Workspace účet @{domain}.' => 'Use your school Google Workspace account @{domain}.',

    // --- bootstrap.php: CSRF -------------------------------------------------
    'Neplatný nebo expirovaný formulář. Obnov stránku a zkus to znovu.' => 'The form is invalid or has expired. Reload the page and try again.',

    // --- bootstrap.php: submission status labels ----------------------------
    'Čeká na hodnocení' => 'Awaiting grading',
    'Ohodnoceno' => 'Graded',
    'Vráceno k dopracování' => 'Returned for revision', // shodné s learn.php
    'Dopracování odevzdáno' => 'Revision submitted',
    'Zrušeno' => 'Cancelled',
    'Neznámý stav' => 'Unknown status',

    // --- bootstrap.php: levels/badges/achievements --------------------------
    'Level {level} Milestone' => 'Level {level} Milestone',
    'Dosažen level {level} dlouhodobým studiem.' => 'Reached level {level} through long-term study.',
    'Dosáhni levelu {level}.' => 'Reach level {level}.',
    'Bezchybný test' => 'Flawless Test',
    'Dokončen celý startovní test bez jediné chyby.' => 'Completed the entire starting test without a single mistake.',
    '100 % správných odpovědí v celém startovním testu.' => '100% correct answers in the whole starting test.',
    'Challenge Distinction' => 'Challenge Distinction',
    'Dobrovolná rozšiřující challenge byla ohodnocena známkou 1.' => 'The voluntary extension challenge was graded with a 1.',
    'Získej jedničku z dobrovolné Extra challenge.' => 'Get a 1 in the voluntary Extra challenge.',
    'Masterpiece' => 'Masterpiece',
    'Publikované projektové hodnocení alespoň 95 % se známkou 1.' => 'A published project evaluation of at least 95% with a grade of 1.',
    'Projekt alespoň 95 % bodů a výsledná známka 1.' => 'A project with at least 95% of points and a final grade of 1.',
    'Triple Distinction' => 'Triple Distinction',
    'Tři publikované projekty za 1 s výsledkem alespoň 90 %.' => 'Three published projects graded 1 with a result of at least 90%.',
    '3 výborné projektové výsledky (≥90 %, známka 1).' => '3 excellent project results (≥90%, grade 1).',
    'Knowledge Grandmaster' => 'Knowledge Grandmaster',
    'Dokončena celá Knowledge Base aktuálního ročníku.' => 'Completed the entire Knowledge Base for the current year.',
    'Dokonči všechny Knowledge materiály aktuálního ročníku.' => 'Complete all the Knowledge materials for the current year.',
    'Course Mastery' => 'Course Mastery',
    'Dokončena celá 28lekcová strukturovaná učební cesta aktuálního ročníku.' => 'Completed the entire 28-lesson structured learning path for the current year.',
    'Dokonči lekce 1–28 aktuálního ročníku.' => 'Complete lessons 1–28 of the current year.',
    'Prestige Certified' => 'Prestige Certified',
    'Úspěšně dokončena první prestižní certifikační zkouška.' => 'Successfully completed your first prestige certification exam.',
    'Splň libovolnou prestižní zkoušku.' => 'Pass any prestige exam.',
    'Perfect Certification' => 'Perfect Certification',
    'Prestižní zkouška dokončena bez jediné chyby.' => 'A prestige exam completed without a single mistake.',
    'Získej 100 % v libovolné prestižní zkoušce.' => 'Get 100% in any prestige exam.',
    'Double Certified' => 'Double Certified',
    'Úspěšně dokončeny dvě různé prestižní zkoušky.' => 'Successfully completed two different prestige exams.',
    'Splň dvě odlišné prestižní zkoušky.' => 'Pass two different prestige exams.',
    'Momentum' => 'Momentum',
    'Specialista' => 'Specialist',
    'Expert' => 'Expert', // shodné s hubs.php
    'Master' => 'Master',
    'Elite' => 'Elite',
    'Architekt' => 'Architect',
    'Mentor' => 'Mentor',
    'Vanguard' => 'Vanguard',
    'Legenda' => 'Legend',
    'EDUCANET Icon' => 'EDUCANET Icon',
    'Rozjezd' => 'Kickoff',
    'Získej prvních 250 XP.' => 'Get your first 250 XP.',
    'Tisícovka' => 'The Thousand',
    'Nasbírej 1 000 XP.' => 'Collect 1,000 XP.',
    'Tah na branku' => 'Shot on Goal',
    'Nasbírej 2 500 XP.' => 'Collect 2,500 XP.',
    'Vytrvalec' => 'Endurance',
    'Nasbírej 5 000 XP.' => 'Collect 5,000 XP.',
    'Znalostní start' => 'Knowledge Kickstart',
    'Dokonči 5 Knowledge lekcí.' => 'Complete 5 Knowledge lessons.',
    'Knowledge Explorer' => 'Knowledge Explorer',
    'Dokonči 15 Knowledge lekcí.' => 'Complete 15 Knowledge lessons.',
    'Deep Learner' => 'Deep Learner',
    'Dokonči 30 Knowledge lekcí.' => 'Complete 30 Knowledge lessons.',
    'Experimentátor' => 'Experimenter',
    'Dokonči 5 interaktivních simulací.' => 'Complete 5 interactive simulations.',
    'Lab Regular' => 'Lab Regular',
    'Dokonči 15 interaktivních simulací.' => 'Complete 15 interactive simulations.',
    'Pět aktivních dnů' => 'Five Active Days',
    'Studuj v pěti různých dnech.' => 'Study on five different days.',
    'Studijní rytmus' => 'Study Rhythm',
    'Studuj v deseti různých dnech.' => 'Study on ten different days.',
    'Konzistence' => 'Consistency',
    'Studuj ve dvaceti různých dnech.' => 'Study on twenty different days.',
    'Diagnostika hotová' => 'Diagnostic Done',
    'Dokonči celý startovní test.' => 'Complete the entire starting test.',
    'První hodnocený projekt' => 'First Graded Project',
    'Získej první publikované projektové hodnocení.' => 'Get your first published project evaluation.',
    'Projektová série' => 'Project Streak',
    'Získej tři publikovaná projektová hodnocení.' => 'Get three published project evaluations.',
    'Výborný projekt' => 'Excellent Project',
    'Získej z projektu známku 1.' => 'Get a grade of 1 on a project.',
    'Tři lekce za tebou' => 'Three Lessons Down',
    'Dokonči tři navazující lekce.' => 'Complete three consecutive lessons.',
    'První třetina' => 'First Third',
    'Dokonči šest navazujících lekcí.' => 'Complete six consecutive lessons.',
    'První polovina kurzu' => 'First Half of the Course',
    'Dokonči devět lekcí a uzavři první polovinu cesty.' => 'Complete nine lessons and close out the first half of the path.',
    'Druhá etapa' => 'Second Stage',
    'Dokonči dvanáct lekcí.' => 'Complete twelve lessons.',
    'Core Path' => 'Core Path',
    'Dokonči prvních osmnáct lekcí ročníku.' => 'Complete the first eighteen lessons of the year.',
    'Deep Path' => 'Deep Path',
    'Dokonči dvacet čtyři strukturovaných lekcí.' => 'Complete twenty-four structured lessons.',
    'Celá cesta' => 'The Whole Path',
    'Dokonči všech 28 strukturovaných lekcí ročníku.' => 'Complete all 28 structured lessons of the year.',
    'Study Buddy' => 'Study Buddy',
    'Propoj se s prvním spolužákem v rámci třídy.' => 'Connect with your first classmate.',
    'Studijní kruh' => 'Study Circle',
    'Měj pět vzájemně potvrzených propojení se spolužáky.' => 'Have five mutually confirmed connections with classmates.',
    'Team Player' => 'Team Player',
    'Staň se členem prvního uzamčeného projektového týmu.' => 'Become a member of your first locked project team.',
    'Team Builder' => 'Team Builder',
    'Založ lobby, které se promění v oficiální projektový tým.' => 'Found a lobby that turns into an official project team.',

    // --- bootstrap.php: team/lobby/profile exceptions -----------------------
    'Tým musí mít alespoň dva studenty.' => 'A team must have at least two students.',
    'Student může být v jednom projektu pouze v jednom týmu. Upravte členství existujícího týmu.' => 'A student can be in only one team per project. Edit the membership of the existing team.',
    'Tým už má historii v Project Workspace. Kvůli auditu jej nemažte; upravte členství nebo vytvořte nový tým.' => "The team already has history in Project Workspace. Don't delete it for audit reasons; edit its membership or create a new team.",
    'Studentský profil nebyl nalezen.' => 'The student profile was not found.', // shodné s hubs.php
    'Můžeš upravovat pouze svůj profil.' => 'You can only edit your own profile.',
    'Neplatná žádost o propojení.' => 'Invalid connection request.',
    'Propojit lze pouze spolužáky ze stejné třídy.' => 'You can only connect with classmates from the same class.',
    'Tato žádost už není dostupná.' => 'This request is no longer available.',
    'Toto propojení ti nepatří.' => "This connection doesn't belong to you.",
    'Neplatná varianta rozdělení.' => 'Invalid grouping option.',
    'Tento projekt není skupinový.' => 'This project is not a group project.',
    'Student nebyl nalezen.' => 'The student was not found.',
    'U tohoto projektu už jsi v týmu nebo aktivním lobby.' => "You're already in a team or an active lobby for this project.",
    'Vyber jednu z doporučených velikostí týmu.' => 'Choose one of the recommended team sizes.',
    'Zadej invite kód.' => 'Enter the invite code.',
    'U tohoto projektu už jsi v týmu nebo lobby.' => "You're already in a team or a lobby for this project.",
    'Toto lobby už je uzavřené.' => 'This lobby is already closed.',
    'Lobby je plné.' => 'The lobby is full.',
    'Invite kód nebyl nalezen pro tento projekt a třídu.' => 'No invite code was found for this project and class.',
    'Do tohoto lobby nepatříš.' => "You don't belong to this lobby.",
    'Lobby už neexistuje.' => 'The lobby no longer exists.',
    'Invite kód může změnit jen zakladatel lobby.' => "Only the lobby's founder can change the invite code.",
    'Lobby nebylo nalezeno.' => 'The lobby was not found.',
    'Tým může uzamknout jen zakladatel lobby.' => "Only the lobby's founder can lock the team.",
    'Pro tým jsou potřeba alespoň dva studenti.' => 'A team needs at least two students.',
    'Před uzamčením naplň zvolenou kapacitu týmu ({members} / {capacity}).' => 'Before locking, fill the chosen team capacity ({members} / {capacity}).',

    // --- index.php -----------------------------------------------------------
    'Nejdřív si nastav vlastní heslo.' => 'First set your own password.', // shodné s auth/events/games/teamgames.php

    // --- student_v55.php: nav labels (trm, byte-identical cs array) --------
    'Dnešní hodina' => "Today's class", // shodné s auth/learn.php
    'Materiály' => 'Materials', // shodné s learn/study.php
    'Kalendář' => 'Calendar', // shodné s auth/learn.php
    'Výsledky' => 'Results', // shodné s auth/hubs/learn.php
    'Profil' => 'Profile', // shodné s auth/hubs.php
    'Linux Lab' => 'Linux Lab',
    'Závod!' => 'Race!',
    'Hra!' => 'Game!',

    // --- student_v55.php: badge progress ------------------------------------
    'Level {level} z {target}' => 'Level {level} of {target}',

    // --- student_v55.php: bonus assignment ----------------------------------
    'Rozšiřující zadání' => 'Extension assignment',
    'Máš dost času na celé rozšiřující zadání i na jeho obhájení.' => 'You have enough time for the whole extension assignment and its defence.',
    'Zkrácené rozšiřující zadání' => 'Shortened extension assignment',
    'Zadání je zkrácené tak, abys ho stihl/a do konce hodiny.' => 'The assignment is shortened so you can finish it before the end of class.',
    'Krátký test' => 'Short test',
    'Do konce hodiny zbývá málo času, dostáváš krátkou verzi.' => "There's little time left until the end of class, so you get the short version.",
    'Mikroúkol' => 'Micro-task',
    'Stihneš jednu otázku na rozmyšlenou.' => "You'll have time for one question to think over.",
    'Dnes už jen odevzdej' => 'Today, just submit',
    'Do konce hodiny zbývá méně než {min} minut. Bonus se dnes nezadává.' => 'There are fewer than {min} minutes left until the end of class. No bonus is assigned today.',
    'Varianta navíc' => 'Extra variant',
    'Vytvoř druhou variantu dnešního výstupu s jiným kontrastem nebo jinou hierarchií a napiš, která funguje líp a proč.' => "Create a second variant of today's output with different contrast or a different hierarchy, and write which one works better and why.",
    'Mobilní verze' => 'Mobile version',
    'Uprav výstup na šířku 375 px tak, aby zůstala čitelná hierarchie i hlavní CTA.' => 'Adjust the output to a width of 375 px so the hierarchy and the main CTA stay readable.',
    'Kontrola přístupnosti' => 'Accessibility check',
    'Ověř kontrast textu (min. 4,5:1) a velikost klikacích prvků; zapiš, co jsi musel/a změnit.' => 'Check the text contrast (min. 4.5:1) and the size of clickable elements; write down what you had to change.',
    'Krátká obhajoba' => 'Short defence',
    'Ve 3 větách vysvětli hlavní designové rozhodnutí tak, aby mu rozuměl i zadavatel bez znalosti grafiky.' => 'In 3 sentences, explain the main design decision so that even a client with no design knowledge understands it.',
    'Druhý scénář' => 'Second scenario',
    'Zopakuj dnešní postup na jiném zadání (jiná síť, jiná služba) a zapiš rozdíly v konfiguraci.' => "Repeat today's procedure on a different task (a different network, a different service) and write down the differences in configuration.",
    'Diagnostika chyby' => 'Fault diagnosis',
    'Rozbij si vlastní konfiguraci jednou změnou, popiš příznak, najdi ho a oprav. Zapiš postup krok za krokem.' => 'Break your own configuration with one change, describe the symptom, find it and fix it. Write down the procedure step by step.',
    'Důkaz funkčnosti' => 'Proof it works',
    'Dolož výstup příkazu nebo screenshot, který prokazuje, že řešení opravdu funguje.' => 'Attach the command output or a screenshot that proves the solution really works.',
    'Ve 3 větách vysvětli, proč jsi zvolil/a právě tohle řešení a co by se stalo při jiné volbě.' => 'In 3 sentences, explain why you chose this particular solution and what would happen with a different choice.',
    'Téma: {tema}.' => 'Topic: {tema}.',

    // --- student_v55.php: primary tasks / lesson plan -----------------------
    'Vstupní diagnostika' => 'Initial diagnostic',
    'Krátký test bez známky. Ukáže, co už umíš, a nastaví ti tempo.' => 'A short ungraded test. It shows what you already know and sets your pace.',
    'Projdi tutoriál lekce' => 'Go through the lesson tutorial', // shodné s auth.php
    'Animovaná ukázka, vysvětlení a interaktivní úkoly za body.' => 'An animated demo, explanation and interactive tasks for points.',
    'Hlavní praktický blok' => 'Main practical block',
    'Vytvoř dnešní grafický výstup podle zadání a ulož ho.' => "Create today's graphic output according to the assignment and save it.",
    'Odevzdej výstup' => 'Submit the output',
    'Vlož odkaz na Canvu/Figmu a krátce popiš svá rozhodnutí.' => 'Add a link to Canva/Figma and briefly describe your decisions.',
    'Projdi praktickou laboratoř dnešní lekce a dokonči všechny kroky.' => "Go through today's lesson's practical lab and complete all the steps.",
    'Odevzdej důkaz' => 'Submit proof',
    'Vlož výstup příkazu nebo screenshot a krátce popiš postup.' => 'Add the command output or a screenshot and briefly describe the procedure.',
    'Krátký test bez známky. Ukáže ti, co už umíš, a podle toho se přizpůsobí tempo.' => 'A short ungraded test. It shows you what you already know and adjusts the pace accordingly.',
    'Spustit test' => 'Start the test', // shodné s auth/learn.php
    'Test už mám hotový' => "I've already done the test",
    'Tutoriál lekce {n}' => 'Lesson {n} tutorial',
    'Animovaná ukázka situace, vysvětlení a interaktivní úkoly za body.' => 'An animated demo of the situation, explanation and interactive tasks for points.',
    'Otevřít tutoriál' => 'Open the tutorial',
    'Samostatná práce' => 'Independent work',
    'Odškrtávej si body, jak postupuješ. Postup se ti průběžně ukládá.' => 'Tick off the points as you go. Your progress is saved continuously.',
    'Odevzdání' => 'Submission', // shodné s hubs/learn.php
    'Krátký popis a odkaz na práci. Učitel ti dá známku a body.' => 'A short description and a link to your work. The teacher will give you a grade and points.',
];
