<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** EDUCANET v50 · Independent Growth + PHP Learning Lab
 * Optional learning paths outside the standard curriculum.
 */

function v50_growth_path(string $id,string $title,string $domain,array $classes,array $modules,array $requires=[],string $capstone=''): array
{
    $rows=[];foreach($modules as $i=>$m){
        if(is_string($m))$m=['title'=>$m];
        $slug=(string)($m['slug']??($id.'-'.($i+1)));$rows[]=[
            'slug'=>$slug,'title'=>(string)($m['title']??('Modul '.($i+1))),
            'minutes'=>(int)($m['minutes']??45),'goal'=>(string)($m['goal']??'Pochop princip, vyzkoušej ho a vytvoř vlastní malý důkaz.'),
            'practice'=>(string)($m['practice']??'Vyzkoušej princip v bezpečném sandboxu.'),
            'build'=>(string)($m['build']??'Vytvoř malý výstup, který princip používá.'),
            'reflect'=>(string)($m['reflect']??'Napiš, co bylo rozhodující a co bys příště ověřil/a dřív.'),
        ];
    }
    return ['id'=>$id,'title'=>$title,'domain'=>$domain,'classes'=>$classes,'modules'=>$rows,'requires'=>$requires,'capstone'=>$capstone,'optional'=>true,'outside_standard_lesson'=>true,'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'blocks_curriculum'=>false,'can_be_required'=>false];
}

function v50_growth_catalog(): array
{
    static $catalog=null;if(is_array($catalog))return $catalog;
    $graphics=['class_1a','class_2a'];$infra=['class_3a','class_4a'];
    $catalog=[];
    $catalog['webdesign']=v50_growth_path('webdesign','Webdesign','design',$graphics,[
        ['slug'=>'wd-content','title'=>'Content-first layout','goal'=>'Navrhni stránku podle obsahu a hierarchie, ne podle dekorací.'],
        ['slug'=>'wd-responsive','title'=>'Responsive thinking','goal'=>'Přemýšlej v omezeních a přechodech místo tří pevných screenshotů.'],
        ['slug'=>'wd-components','title'=>'Komponenty a stavy','goal'=>'Rozlož UI na opakovatelné komponenty a jejich stavy.'],
        ['slug'=>'wd-a11y','title'=>'Přístupnost jako design constraint','goal'=>'Navrhni klávesnici, focus, kontrast a čitelnost už ve Figmě.'],
        ['slug'=>'wd-tokens','title'=>'Design tokens','goal'=>'Převáděj barvy, spacing a typografii na pojmenované tokeny.'],
        ['slug'=>'wd-handoff','title'=>'Developer handoff','goal'=>'Připrav návrh, který lze přesně a efektivně implementovat.'],
    ],[['type'=>'branches','branches'=>['Design','Typography','UI/UX'],'min'=>45]],'Navrhni a zdokumentuj přístupnou responzivní landing page včetně komponent a handoffu.');
    $catalog['frontend-foundations']=v50_growth_path('frontend-foundations','Frontend Foundations','development',$graphics,[
        ['slug'=>'fe-html','title'=>'Semantic HTML'],['slug'=>'fe-css','title'=>'Modern CSS layout'],['slug'=>'fe-responsive','title'=>'Responsive CSS'],['slug'=>'fe-js','title'=>'JavaScript fundamentals'],['slug'=>'fe-dom','title'=>'DOM, events a formuláře'],['slug'=>'fe-git','title'=>'Git + deployment workflow'],
    ],[['type'=>'path','path'=>'webdesign','min'=>0.60],['type'=>'branches','branches'=>['UI/UX'],'min'=>50]],'Přepiš vlastní webdesign do semantického, responzivního a přístupného webu bez frameworku.');
    $catalog['modern-frontend']=v50_growth_path('modern-frontend','Modern Frontend','development',$graphics,[
        ['slug'=>'mf-typescript','title'=>'TypeScript 6'],['slug'=>'mf-react','title'=>'React 19.3'],['slug'=>'mf-vite','title'=>'Vite 8.1 + Rolldown'],['slug'=>'mf-tailwind','title'=>'Tailwind CSS 4.3'],['slug'=>'mf-async','title'=>'Async UI a data fetching'],['slug'=>'mf-testing','title'=>'Frontend testing + accessibility'],
    ],[['type'=>'path','path'=>'frontend-foundations','min'=>0.65]],'Postav produkčně působící TypeScript/React frontend s přístupností, testy a deployem.');
    $catalog['ux-engineering']=v50_growth_path('ux-engineering','UX Engineering','development',$graphics,[
        'Design system as code','Accessible interaction states','Performance-aware UI','Component API design','Visual regression & usability evidence',
    ],[['type'=>'path','path'=>'modern-frontend','min'=>0.60]],'Vytvoř malý design system s dokumentovanými komponentami, stavem a accessibility kontraktem.');
    $catalog['fullstack-product']=v50_growth_path('fullstack-product','Full-stack Product','development',$graphics,[
        'Product slice & data model','Frontend ↔ API contract','Authentication thinking','Observability & deployment','End-to-end product polish',
    ],[['type'=>'path','path'=>'modern-frontend','min'=>0.60],['type'=>'path','path'=>'php-foundations','min'=>0.50]],'Dodej malý full-stack produkt od návrhu přes API a databázi až po deploy a provozní checklist.');

    $catalog['php-foundations']=v50_growth_path('php-foundations','PHP Foundations','backend',$graphics,[
        ['slug'=>'php-request','title'=>'Co se stane po zadání URL?','goal'=>'Pochop browser → HTTP request → webserver → PHP → response.'],
        ['slug'=>'php-vars','title'=>'Proměnné, typy a hodnoty'],['slug'=>'php-flow','title'=>'Podmínky a větvení'],['slug'=>'php-loops','title'=>'Pole a cykly'],['slug'=>'php-functions','title'=>'Funkce a rozdělení odpovědnosti'],['slug'=>'php-forms','title'=>'Formuláře, GET/POST a validace'],['slug'=>'php-session','title'=>'Session, cookies a stav'],['slug'=>'php-db','title'=>'PDO, databáze a prepared statements'],
    ],[['type'=>'path','path'=>'frontend-foundations','min'=>0.50]],'Postav malou stateful PHP aplikaci s formulářem, validací, session a bezpečným databázovým zápisem.');
    $catalog['modern-php']=v50_growth_path('modern-php','Modern PHP 8.5','backend',$graphics,[
        ['slug'=>'php-types','title'=>'strict_types a type declarations'],['slug'=>'php-oop','title'=>'Objekty, value objects a služby'],['slug'=>'php-exceptions','title'=>'Exceptions a error boundaries'],['slug'=>'php-composer','title'=>'Composer, autoloading a packages'],['slug'=>'php-security','title'=>'Security: output, CSRF, auth, uploads'],['slug'=>'php-api','title'=>'JSON API a HTTP status'],['slug'=>'php-tests','title'=>'Automatické testy a refactoring'],
    ],[['type'=>'path','path'=>'php-foundations','min'=>0.75]],'Vytvoř typované JSON API s Composerem, bezpečnostním kontraktem a automatickými testy.');
    $catalog['laravel-backend']=v50_growth_path('laravel-backend','Laravel 13 Backend','backend',$graphics,[
        ['slug'=>'laravel-routing','title'=>'Routing, controllers a request lifecycle'],['slug'=>'laravel-validation','title'=>'Validation + Form Requests'],['slug'=>'laravel-eloquent','title'=>'Eloquent, migrations a relationships'],['slug'=>'laravel-auth','title'=>'Authentication & authorization'],['slug'=>'laravel-api','title'=>'API Resources + queues'],['slug'=>'laravel-tests','title'=>'Feature tests + factories'],['slug'=>'laravel-deploy','title'=>'Config, cache, jobs a production deploy'],
    ],[['type'=>'path','path'=>'modern-php','min'=>0.70],['type'=>'path','path'=>'frontend-foundations','min'=>0.50]],'Postav Laravel 13 backend s databází, auth, API, testy a produkčním deployment checklistem.');

    $catalog['network-engineering']=v50_growth_path('network-engineering','Network Engineering','infrastructure',$infra,[
        ['slug'=>'net-models','title'=>'Model sítě a packet journey','goal'=>'Rozlož komunikaci do vrstev, zařízení a konkrétních důkazů.'],
        ['slug'=>'net-addressing','title'=>'Adresace, subnetting a VLSM','goal'=>'Navrhni adresaci bez překryvů a uměj ji rychle ověřit.'],
        ['slug'=>'net-routing','title'=>'VLAN, routing a cesta paketu','goal'=>'Urči, kudy paket skutečně projde a kde se může zastavit.'],
        ['slug'=>'net-services','title'=>'DNS, DHCP a síťové služby','goal'=>'Odděl konfiguraci klienta, resolver, službu a cache.'],
        ['slug'=>'net-security','title'=>'Firewall, NAT a segmentace','goal'=>'Použij least privilege pravidla a ověř pozitivní i negativní scénář.'],
        ['slug'=>'net-troubleshoot','title'=>'Evidence-first troubleshooting','goal'=>'Diagnostikuj od symptomu přes důkaz k minimální opravě a retestu.'],
    ],[['type'=>'branches','branches'=>['Networking'],'min'=>45]],'Navrhni malou segmentovanou síť, zdokumentuj packet path a vyřeš v ní řízený incident pomocí důkazů.');

    $catalog['linux-automation']=v50_growth_path('linux-automation','Linux Automation','infrastructure',$infra,[
        'Bash safety: set -euo pipefail','Idempotent scripts','systemd timers místo slepého cronu','Log parsing & reporting','Backup/restore automation',
    ],[['type'=>'branches','branches'=>['Linux'],'min'=>50]],'Napiš bezpečnou idempotentní údržbovou automatizaci s dry-run, logem a rollbackem.');
    $catalog['containers-devops']=v50_growth_path('containers-devops','Containers & DevOps','infrastructure',$infra,[
        'OCI images & rootless containers','Compose a service dependencies','CI/CD pipeline','Reverse proxy, health checks & deploy strategy',
    ],[['type'=>'path','path'=>'linux-automation','min'=>0.65]],'Dodej reprodukovatelný kontejnerový stack s health checkem, reverse proxy a bezpečným deployem.');
    $catalog['cloud-sre']=v50_growth_path('cloud-sre','Cloud / SRE','infrastructure',$infra,[
        'SLO, SLI a error budget','Observability + incident signals','Capacity, resilience a postmortem',
    ],[['type'=>'path','path'=>'containers-devops','min'=>0.70]],'Vytvoř reliability dossier: SLO, dashboard signálů, runbook, capacity risk a postmortem šablonu.');
    $catalog['blue-team']=v50_growth_path('blue-team','Blue Team','security',$infra,[
        'Hardening & least privilege','Log/IOC investigation','Containment, remediation & lessons learned',
    ],[['type'=>'branches','branches'=>['Security'],'min'=>50],['type'=>'path','path'=>'linux-automation','min'=>0.50]],'Vyřeš obranný incident od log evidence přes containment po remediation a stručný postmortem.');
    return $catalog;
}

function v50_php_learning_cards(): array
{
    static $cards=null;if(is_array($cards))return $cards;
    $definitions=[
        'php-request'=>['model'=>'Browser pošle HTTP request. Webserver předá PHP soubor interpreteru. PHP vytvoří response body; browser nikdy nevidí PHP zdroj.','flow'=>['Browser','HTTP request','Nginx/Apache','PHP 8.5','HTTP response','Browser render'],'code'=>"<?php\n\$name = 'Ada';\necho '<h1>Ahoj '.htmlspecialchars(\$name).'</h1>';",'trace'=>['$name vznikne jako string','htmlspecialchars() vytvoří bezpečný výstup','echo přidá HTML do response'],'mistake'=>'PHP neběží v browseru. View source ukazuje pouze výsledné HTML.','security'=>'Výstup z nedůvěryhodných dat escapuj podle cílového kontextu.','check'=>'Která část řetězce skutečně vykoná PHP kód?'],
        'php-vars'=>['model'=>'Proměnná je pojmenovaná reference na hodnotu s runtime typem.','flow'=>['literal','variable','expression','result'],'code'=>"<?php\n\$price = 199;\n\$vat = 0.21;\n\$total = \$price * (1 + \$vat);",'trace'=>['$price = int 199','$vat = float 0.21','$total = float 240.79'],'mistake'=>'Řetězec "199" není totéž co číslo 199.','security'=>'Validuj vstup dřív, než z něj vytvoříš doménovou hodnotu.','check'=>'Jaký typ a hodnotu bude mít $total?'],
        'php-flow'=>['model'=>'Podmínka vybírá větev podle boolean výrazu.','flow'=>['input','condition','true/false branch','output'],'code'=>"<?php\n\$age = 17;\nif (\$age >= 18) { echo 'adult'; } else { echo 'minor'; }",'trace'=>['$age = 17','17 >= 18 je false','vykoná se else'],'mistake'=>'= přiřazuje, === porovnává hodnotu i typ.','security'=>'Authorization nesmí být jen skrytí tlačítka; musí se ověřit server-side.','check'=>'Proč je bezpečnější === než volné == v citlivé logice?'],
        'php-loops'=>['model'=>'Pole drží více hodnot; cyklus opakuje stejnou transformaci.','flow'=>['array','foreach','one item','accumulator/output'],'code'=>"<?php\n\$nums = [2,4,6];\n\$sum = 0;\nforeach (\$nums as \$n) { \$sum += \$n; }",'trace'=>['sum 0 → 2','2 → 6','6 → 12'],'mistake'=>'Neměň kolekci naslepo během iterace.','security'=>'U velkých datasetů nečti vše do paměti, pokud stačí stránkování/stream.','check'=>'Kolikrát se vykoná tělo foreach a jaká bude $sum?'],
        'php-functions'=>['model'=>'Funkce převádí vstupy na výstup a skrývá jednu odpovědnost.','flow'=>['arguments','function boundary','local state','return value'],'code'=>"<?php\nfunction gross(float \$net, float \$vat): float { return \$net * (1 + \$vat); }\n\$total = gross(100, 0.21);",'trace'=>['100 a 0.21 vstoupí do funkce','lokální proměnné $net/$vat','return 121.0'],'mistake'=>'Funkce, která současně validuje, ukládá DB a renderuje HTML, má příliš mnoho odpovědností.','security'=>'Typy pomáhají, ale nenahrazují validaci nedůvěryhodného vstupu.','check'=>'Která data jsou uvnitř funkce lokální?'],
        'php-forms'=>['model'=>'Formulář posílá nedůvěryhodná data. Server je musí načíst, validovat a teprve pak použít.','flow'=>['form','POST','validation','domain action','PRG redirect'],'code'=>"<?php\n\$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);\nif (\$email === false) { http_response_code(422); }",'trace'=>['POST email vstoupí jako untrusted','$email je validní string nebo false','422 při chybě'],'mistake'=>'required v HTML není bezpečnostní validace.','security'=>'Mutující formuláře chraň CSRF tokenem.','check'=>'Proč musí validace proběhnout znovu na serveru?'],
        'php-session'=>['model'=>'HTTP je stateless; session propojí více requestů přes náhodný identifikátor cookie.','flow'=>['request + cookie','session id','server session data','response'],'code'=>"<?php\nsession_start();\n\$_SESSION['cart_count'] = (int)(\$_SESSION['cart_count'] ?? 0) + 1;",'trace'=>['session_start načte stav','čte starou hodnotu','uloží novou hodnotu'],'mistake'=>'Session není globální databáze a nemá obsahovat nekonečné množství dat.','security'=>'Po loginu regeneruj session ID a používej secure/httponly/samesite cookies.','check'=>'Co propojuje dva HTTP requesty se stejnou serverovou session?'],
        'php-db'=>['model'=>'PDO prepared statement oddělí SQL strukturu od dat.','flow'=>['input','validation','prepare SQL','bind/execute','database'],'code'=>"<?php\n\$stmt = \$pdo->prepare('SELECT id,name FROM users WHERE email = :email');\n\$stmt->execute(['email' => \$email]);",'trace'=>['SQL template je připraven','email je poslán zvlášť jako data','DB vrátí řádky'],'mistake'=>'Skládání SQL pomocí konkatenace uživatelských dat vytváří SQL injection.','security'=>'Používej prepared statements a DB účet s minimálními právy.','check'=>'Která část prepared statementu brání tomu, aby email změnil strukturu SQL?'],
        'php-types'=>['model'=>'strict_types zpřesňuje kontrakty funkcí a snižuje skryté konverze.','flow'=>['typed input','contract','implementation','typed return'],'code'=>"<?php\ndeclare(strict_types=1);\nfunction add(int \$a, int \$b): int { return \$a + \$b; }",'trace'=>['volající poskytne int','PHP kontroluje kontrakt','return musí být int'],'mistake'=>'Typový systém neříká, zda je číslo například validní cena.','security'=>'Doménová validace zůstává nutná i s typy.','check'=>'Co strict_types řeší a co naopak neřeší?'],
        'php-oop'=>['model'=>'Objekt spojuje stav a chování kolem jedné doménové odpovědnosti.','flow'=>['raw data','value object','invariant','service use'],'code'=>"<?php\nfinal class Email { public function __construct(public readonly string \$value) { if (!filter_var(\$value, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException(); } }",'trace'=>['constructor dostane string','ověří invariant','vznikne validní Email objekt'],'mistake'=>'OOP není cíl; objekt bez odpovědnosti je jen složitější pole.','security'=>'Validní value object snižuje počet míst, kde musíš kontrolovat stejné pravidlo.','check'=>'Jakou chybu znemožňuje Email value object po úspěšném vytvoření?'],
        'php-exceptions'=>['model'=>'Exception přeruší běžný tok a přesune chybu k vrstvě, která umí rozhodnout o response/logu.','flow'=>['operation','throw','boundary catch','safe response + log'],'code'=>"<?php\ntry { \$order = \$service->create(\$input); } catch (DomainException \$e) { http_response_code(422); }",'trace'=>['service detekuje chybu','throw přeskočí běžný return','boundary zvolí HTTP response'],'mistake'=>'Nezachytávej Throwable všude a chybu potichu neignoruj.','security'=>'Uživateli neukazuj stack trace ani secrets.','check'=>'Proč má exception zachytit vrstva, která zná kontext odpovědi?'],
        'php-composer'=>['model'=>'Composer mapuje packages a autoloading; zdrojový kód nemusí ručně require každý class file.','flow'=>['composer.json','dependency resolution','vendor/autoload.php','class loading'],'code'=>"<?php\nrequire __DIR__.'/vendor/autoload.php';\n\$client = new Vendor\\Package\\Client();",'trace'=>['autoload registruje mapování','class se načte při prvním použití','objekt vznikne'],'mistake'=>'Neupravuj kód přímo ve vendor/.','security'=>'Commituj lockfile aplikace a sleduj security advisories.','check'=>'Jakou roli má composer.lock v aplikaci?'],
        'php-security'=>['model'=>'Bezpečnost je řetězec: validace vstupu, autorizace, CSRF, bezpečný výstup a bezpečné soubory.','flow'=>['untrusted input','validate','authorize','mutate','encode output'],'code'=>"<?php\nif (!hash_equals(\$_SESSION['csrf'], \$_POST['csrf'] ?? '')) { http_response_code(403); exit; }",'trace'=>['server zná token','request posílá token','hash_equals ověří shodu','mutace pokračuje až poté'],'mistake'=>'Escaping vstupu při uložení není náhradou za contextual output encoding.','security'=>'Upload validuj MIME, velikost, název a ukládej mimo executable web path.','check'=>'Proč CSRF token nenahrazuje autorizaci?'],
        'php-api'=>['model'=>'API response je smlouva: status, headers a konzistentní JSON shape.','flow'=>['request','route/service','result/error','HTTP status + JSON'],'code'=>"<?php\nhttp_response_code(201);\nheader('Content-Type: application/json');\necho json_encode(['data' => ['id' => \$id]], JSON_THROW_ON_ERROR);",'trace'=>['operace vytvoří resource','status 201 popisuje výsledek','JSON nese data'],'mistake'=>'Nevracej 200 pro každou chybu.','security'=>'API autorizuj na serveru a nevracej interní fields bez explicitního resource mappingu.','check'=>'Kdy je vhodnější 201 než 200?'],
        'php-tests'=>['model'=>'Test popíše očekávané chování a umožní měnit implementaci bez ztráty kontraktu.','flow'=>['arrange','act','assert','refactor safely'],'code'=>"<?php\nit('rejects invalid email', function () { expect(fn() => new Email('x'))->toThrow(InvalidArgumentException::class); });",'trace'=>['připrav invalid input','spusť chování','ověř konkrétní výsledek'],'mistake'=>'Test, který jen kopíruje implementaci, nechrání uživatelské chování.','security'=>'Testuj i authorization, CSRF a invalid data paths.','check'=>'Jaká produkční změna by měla tento test rozbít?'],
        'laravel-routing'=>['model'=>'Laravel request lifecycle vede request přes middleware, route a controller/service k response.','flow'=>['HTTP','middleware','route','controller/service','response'],'code'=>"Route::get('/projects/{project}', [ProjectController::class, 'show']);",'trace'=>['router najde route','middleware ověří podmínky','controller koordinuje use-case'],'mistake'=>'Controller nemá obsahovat celou business logiku.','security'=>'Middleware není jediná autorizace; používej policies/gates pro resource.','check'=>'Kde má být doménová logika, aby šla dobře testovat?'],
        'laravel-validation'=>['model'=>'Form Request oddělí validaci a autorizaci vstupu od controlleru.','flow'=>['request','FormRequest','validated data','use-case'],'code'=>"public function rules(): array { return ['title' => ['required','string','max:180']]; }",'trace'=>['Laravel vytvoří request','provede authorize/rules','controller dostane validated data'],'mistake'=>'$request->all() posílá dál i data, která jsi nevalidoval.','security'=>'Používej validated()/safe() a explicitní mass-assignment pravidla.','check'=>'Proč je validated() bezpečnější než all()?'],
        'laravel-eloquent'=>['model'=>'Migration definuje strukturu, model mapuje doménová data a relationship vyjadřuje vazbu.','flow'=>['migration','table','model','relationship/query'],'code'=>"\$projects = Project::query()->with('owner')->where('status', 'active')->get();",'trace'=>['query builder skládá SQL','with přednačte owner','DB vrátí modely'],'mistake'=>'N+1 query vzniká při lazy načítání vztahu v cyklu.','security'=>'Nepropouštěj klientská data přímo do orderBy/column names bez allowlistu.','check'=>'Co řeší eager loading with()?'],
        'laravel-auth'=>['model'=>'Authentication říká kdo jsi; authorization rozhoduje, zda smíš provést konkrétní akci.','flow'=>['identity','authenticated user','policy/gate','allowed/denied'],'code'=>"\$this->authorize('update', \$project);",'trace'=>['Laravel zná user','policy dostane user + project','vrátí allow/deny'],'mistake'=>'Přihlášený uživatel automaticky nesmí upravovat každý resource.','security'=>'Preferuj policies pro object-level authorization.','check'=>'Jaký je rozdíl authN vs authZ?'],
        'laravel-api'=>['model'=>'API Resource mapuje interní model na veřejný response kontrakt; queue přesune pomalou práci mimo request.','flow'=>['controller','resource','JSON contract','queue side effect'],'code'=>"return new ProjectResource(\$project);",'trace'=>['controller má model','resource vybírá veřejná pole','serializer vytvoří JSON'],'mistake'=>'Nevracej celý Eloquent model jen proto, že to je pohodlné.','security'=>'Resource explicitně chrání citlivá interní pole.','check'=>'Proč je Resource užitečný i bez změny dat?'],
        'laravel-tests'=>['model'=>'Feature test prochází HTTP boundary a ověřuje routing, validation, auth i persistence dohromady.','flow'=>['factory/user','HTTP request','application','assert response + DB'],'code'=>"\$this->actingAs(\$user)->post('/projects', ['title'=>'Lab'])->assertRedirect();",'trace'=>['test vytvoří identity','pošle reálný app request','ověří response a vedlejší efekt'],'mistake'=>'Příliš mnoho mocků může obejít přesně tu integraci, kterou chceš ověřit.','security'=>'Přidej test „jiný uživatel nesmí změnit resource“.','check'=>'Kterou část stacku feature test pokrývá navíc oproti unit testu?'],
        'laravel-deploy'=>['model'=>'Production deploy je řízená změna: config, migrations, cache, workers, health check a rollback.','flow'=>['build','deploy artifact','migrate/cache','restart workers','health check','rollback if needed'],'code'=>"php artisan config:cache\nphp artisan route:cache\nphp artisan migrate --force",'trace'=>['nový release je připraven','produkční config se cacheuje','migrace proběhnou explicitně','health check potvrdí release'],'mistake'=>'Deploy není git pull + naděje.','security'=>'Secrets nepatří do repozitáře a produkční debug musí být vypnutý.','check'=>'Jaký důkaz potřebuješ před tím, než označíš deploy za úspěšný?'],
    ];
    $cards=[];foreach($definitions as $slug=>$d)$cards[$slug]=array_replace(['slug'=>$slug],$d);return $cards;
}

function v50_growth_rows(): array { return v50_rows('growth_progress'); }
function v50_growth_capstone_rows(): array { return v50_rows('growth_capstones'); }
function v50_growth_control_rows(): array { return v50_rows('growth_controls'); }

function v50_growth_completed_modules(string $classId,string $studentKey,string $pathId): array
{
    $out=[];foreach(v50_growth_rows() as $row){if(!is_array($row))continue;if((string)($row['class_id']??'')===$classId&&(string)($row['student_key']??'')===$studentKey&&(string)($row['path_id']??'')===$pathId&&!empty($row['completed']))$out[(string)$row['module_slug']]=true;}return $out;
}
function v50_growth_progress_ratio(string $classId,string $studentKey,string $pathId): float
{
    $path=v50_growth_catalog()[$pathId]??null;if(!is_array($path))return 0.0;$total=max(1,count((array)$path['modules']));return min(1.0,count(v50_growth_completed_modules($classId,$studentKey,$pathId))/$total);
}
function v50_adaptive_to_skill_key(string $classId,string $studentKey): string
{
    $label=adaptive_student_label($classId,$studentKey);return $label!==''?skill_student_key_for_label($classId,$label):$studentKey;
}
function v50_branch_score(string $classId,string $studentKey,string $branch): float
{
    $skillKey=v50_adaptive_to_skill_key($classId,$studentKey);$rows=skill_branch_snapshot_map($classId,$skillKey);$row=$rows[$branch]??null;return is_array($row)?(float)($row['mastery_percent']??0):0.0;
}
function v50_growth_unlock(string $classId,string $studentKey,string $pathId): array
{
    $path=v50_growth_catalog()[$pathId]??null;if(!is_array($path)||!in_array($classId,(array)$path['classes'],true))return ['unlocked'=>false,'reasons'=>[tr('Cesta není určená pro tuto třídu.')]];
    $reasons=[];foreach((array)$path['requires'] as $req){if(!is_array($req))continue;$type=(string)($req['type']??'');
        if($type==='path'){$need=(float)($req['min']??0);$actual=v50_growth_progress_ratio($classId,$studentKey,(string)$req['path']);if($actual+1e-9<$need)$reasons[]=tr('Nejdřív dokonči alespoň {percent}% cesty „{path}“. ',['percent'=>(int)round($need*100),'path'=>(string)(v50_growth_catalog()[(string)$req['path']]['title']??$req['path'])]);}
        elseif($type==='branches'){$need=(float)($req['min']??0);$scores=[];foreach((array)($req['branches']??[]) as $branch)$scores[] = v50_branch_score($classId,$studentKey,(string)$branch);$actual=$scores?array_sum($scores)/count($scores):0;if($actual+1e-9<$need)$reasons[]=tr('Standardní základy {branches} musí mít průměrnou mastery alespoň {percent} %.',['branches'=>implode(' + ',(array)$req['branches']),'percent'=>(int)$need]);}
    }
    return ['unlocked'=>$reasons===[],'reasons'=>$reasons];
}
function v50_growth_state(string $classId,string $studentKey,string $pathId): array
{
    $path=v50_growth_catalog()[$pathId]??null;if(!is_array($path))return ['exists'=>false,'unlocked'=>false,'progress'=>0,'completed'=>[],'capstone_ready'=>false,'reasons'=>[tr('Cesta neexistuje.')]];
    $unlock=v50_growth_unlock($classId,$studentKey,$pathId);$completed=v50_growth_completed_modules($classId,$studentKey,$pathId);$ratio=v50_growth_progress_ratio($classId,$studentKey,$pathId);
    return ['exists'=>true,'unlocked'=>(bool)$unlock['unlocked'],'reasons'=>(array)$unlock['reasons'],'progress'=>$ratio,'completed'=>$completed,'capstone_ready'=>(bool)$unlock['unlocked']&&$ratio>=0.50,'path'=>$path];
}
function v50_growth_live_session_active(string $classId): bool
{
    if (!function_exists('v48_orchestration_rows')) return false;
    foreach (v48_orchestration_rows() as $row) {
        if (!is_array($row)) continue;
        if ((string)($row['class_id'] ?? '') === $classId && !empty($row['active'])) return true;
    }
    return false;
}

function v50_growth_complete_module(string $classId,string $studentKey,string $pathId,string $moduleSlug,string $reflection=''): array
{
    if(v50_growth_live_session_active($classId))throw new RuntimeException(tr('Během právě řízené hodiny je dobrovolný rozvoj jen ke čtení. Dokončení si nech na samostatný čas.'));
    $state=v50_growth_state($classId,$studentKey,$pathId);if(empty($state['unlocked']))throw new RuntimeException(tr('Tato rozvojová cesta ještě není odemčená.'));$path=(array)$state['path'];$found=null;foreach((array)$path['modules'] as $m)if(is_array($m)&&(string)$m['slug']===$moduleSlug){$found=$m;break;}if(!$found)throw new RuntimeException(tr('Modul nebyl nalezen.'));
    $key=$classId.'|'.$studentKey.'|'.$pathId.'|'.$moduleSlug;return (array)v50_update_row('growth_progress',$key,static fn(?array $current): array => ['class_id'=>$classId,'student_key'=>$studentKey,'path_id'=>$pathId,'module_slug'=>$moduleSlug,'completed'=>true,'reflection'=>v50_clean_text($reflection,1200),'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'completed_at'=>date(DATE_ATOM)]);
}
function v50_growth_submit_capstone(string $classId,string $studentKey,string $pathId,string $title,string $reflection,string $url=''): array
{
    if(v50_growth_live_session_active($classId))throw new RuntimeException(tr('Během právě řízené hodiny nelze odevzdat dobrovolný capstone.'));
    $state=v50_growth_state($classId,$studentKey,$pathId);if(empty($state['capstone_ready']))throw new RuntimeException(tr('Capstone se odemkne až po alespoň 50 % cesty.'));$title=v50_clean_text($title,180);$reflection=v50_clean_text($reflection,1800);if(u_strlen($title)<3||u_strlen($reflection)<30)throw new RuntimeException(tr('Doplň název a stručnou reflexi projektu.'));$url=trim($url);if($url!==''&&!preg_match('~^https://~i',$url))throw new RuntimeException(tr('Odkaz na projekt musí používat HTTPS.'));
    $id='cap_'.bin2hex(random_bytes(7));$row=['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'path_id'=>$pathId,'title'=>$title,'reflection'=>$reflection,'url'=>$url,'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'submitted_at'=>date(DATE_ATOM)];v50_update_row('growth_capstones',$id,static fn(?array $current): array => $row);return $row;
}
function v50_growth_capstones_for_student(string $classId,string $studentKey): array
{
    $out=[];foreach(v50_growth_capstone_rows() as $row)if(is_array($row)&&(string)($row['class_id']??'')===$classId&&(string)($row['student_key']??'')===$studentKey)$out[]=$row;return $out;
}
function v50_growth_control_save(string $classId,string $studentKey,string $pathId,string $mode,string $note=''): array
{
    $mode=in_array($mode,['recommend','pin','hide','clear'],true)?$mode:'recommend';$key=$classId.'|'.$studentKey.'|'.$pathId;if($mode==='clear'){v50_update_row('growth_controls',$key,static fn(?array $current): ?array => null);return [];}$row=['class_id'=>$classId,'student_key'=>$studentKey,'path_id'=>$pathId,'mode'=>$mode,'note'=>v50_clean_text($note,600),'updated_at'=>date(DATE_ATOM)];v50_update_row('growth_controls',$key,static fn(?array $current): array => $row);return $row;
}
function v50_growth_controls_for_student(string $classId,string $studentKey): array
{
    $out=[];foreach(v50_growth_control_rows() as $row)if(is_array($row)&&(string)($row['class_id']??'')===$classId&&(string)($row['student_key']??'')===$studentKey)$out[(string)$row['path_id']]=$row;return $out;
}
