// Lokální vizuální regrese: rtk proxy node tools/visual_qa_2026_10_29.cjs after [zdrojová kopie].
// Playwright z ~/Projects/prezentace; žádný balíček se nepřidává. Klíč žije pouze v paměti procesu.
// Celá aplikace včetně dat se kopíruje do /tmp; chráněné složky originálu kontroluje SHA-256.
// Sondy kontrastu, typografie a přetečení sdílíme s existujícím auditem v80.2.
const fs=require('fs'),path=require('path'),os=require('os'),crypto=require('crypto'),{spawn,spawnSync}=require('child_process');
const {chromium}=require(path.join(os.homedir(),'Projects/prezentace/node_modules/playwright'));
const root=process.cwd(), phase=process.argv[2]||'after', source=process.argv[3]||root, temp=fs.mkdtempSync('/tmp/quiet-study-');
function fingerprint(dir) {
 const hash=crypto.createHash('sha256');
 function visit(p) { for(const e of fs.readdirSync(p,{withFileTypes:true}).sort((a,b)=>a.name.localeCompare(b.name))) { const f=path.join(p,e.name);hash.update(path.relative(dir,f));if(e.isDirectory())visit(f);else if(e.isSymbolicLink())hash.update(fs.readlinkSync(f));else hash.update(fs.readFileSync(f)); } }
 visit(dir);return hash.digest('hex');
}
const protectedPaths=['storage','private','uploads','cache'].filter(p=>fs.existsSync(root+'/'+p));
const originalHashes=protectedPaths.map(p=>fingerprint(root+'/'+p));
fs.mkdirSync(root+'/docs/screens-2026-10-29',{recursive:true});
fs.cpSync(source,temp+'/app',{recursive:true,filter:s=>!['.git','docs'].includes(path.relative(source,s).split(path.sep)[0])});
const key=crypto.randomBytes(32).toString('hex');
const env={...process.env,PHP_INI_SCAN_DIR:':'+os.homedir()+'/.config/php/conf.d',EDUCANET_STORAGE_DIR:temp+'/app/storage',EDUCANET_TEACHER_EXPORT_KEY:key,EDUCANET_DEV_BYPASS:'1',EDUCANET_LM71_CACHE_DIR:temp+'/cache'};
let log='';const php=spawn('/usr/bin/php',['-S','127.0.0.1:8090'],{cwd:temp+'/app',env});php.stdout.on('data',d=>log+=d);php.stderr.on('data',d=>log+=d);
const probeCode=fs.readFileSync(root+'/tools/lib/v80_2_a11y_browser.cjs','utf8').split('function probe(detail) {')[1].split('\nfunction overlay()')[0];
const probe=eval('(function probe(detail) {'+probeCode+')');
(async()=>{let b;const out={phase,rows:[],interactions:[],errors:[],http:[],console:[],temp};try{
 for(let i=0;i<80;i++){try{await fetch('http://127.0.0.1:8090/');break}catch{await new Promise(r=>setTimeout(r,100))}}
 b=await chromium.launch({executablePath:'/usr/bin/chromium',headless:true});
 if(!['before-active','after-active'].includes(phase))for(const scheme of ['light','dark']){const s=await b.newContext({colorScheme:scheme,reducedMotion:'reduce'}),t=await b.newContext({colorScheme:scheme,reducedMotion:'reduce'});
 let pg=await s.newPage();await pg.goto('http://127.0.0.1:8090/?class=class_3a&student=Vizuální%20test');await pg.close();
 pg=await t.newPage();await pg.goto('http://127.0.0.1:8090/teacher.php');await pg.locator('[name=teacher_name]').fill('Vizuální test');await pg.locator('[name=teacher_key]').fill(key);await Promise.all([pg.waitForNavigation(),pg.locator('form button[type=submit]').click()]);await pg.close();
 const pages=[['s-domu',s,'/?view=dashboard'],['s-ucit',s,'/?view=materialy'],['s-cesta',s,'/?view=pokrok'],['s-knihovna',s,'/?view=knowledgebase'],['s-course',s,'/?view=course'],['s-trida',s,'/?view=community'],['s-dnes',s,'/?view=hodina'],['s-kalendar',s,'/?view=calendar'],['t-cockpit',t,'/teacher.php?tab=attention'],['t-dnes',t,'/teacher.php?tab=hodina&class=class_3a'],['t-priprava',t,'/teacher.php?tab=priprava79&class=class_3a'],['t-kalendar',t,'/teacher.php?tab=calendar&class=class_3a']];
 for(const [id,ctx,url] of pages){pg=await ctx.newPage();let at='';pg.on('pageerror',e=>out.errors.push({at,message:e.message}));pg.on('console',m=>{if(m.type()==='error')out.console.push({at,message:m.text()})});pg.on('response',r=>{if(r.status()>=400)out.http.push({at,url:r.url(),status:r.status()})});
 for(const w of [1440,390]){at=id+'-'+scheme+'-'+w;await pg.setViewportSize({width:w,height:w===390?844:1000});await pg.goto('http://127.0.0.1:8090'+url,{waitUntil:'networkidle'});const data=await pg.evaluate(probe,true);out.rows.push({id,scheme,w,url:pg.url(),...data});
 const dir=root+'/docs/screens-2026-10-29';fs.mkdirSync(dir,{recursive:true});await pg.screenshot({path:dir+'/'+phase+'-'+at+'.jpg',type:'jpeg',quality:65,fullPage:true});
 }
 if(phase!=='before' && ['s-domu','s-kalendar','t-priprava'].includes(id)){
  for(const w of [1440,390]){
   await pg.setViewportSize({width:w,height:900});
   await pg.goto('http://127.0.0.1:8090'+url,{waitUntil:'networkidle'});
   const check=(name,ok)=>{out.interactions.push({id,scheme,w,name,ok});if(!ok)throw Error('Interaction failed: '+name)};
   if(id==='s-domu'){
    check('jeden aktuální krok',await pg.locator('.student-primary-cta:visible').count()===1);
    check('analytika až na vyžádání',await pg.locator('.h81-ring:visible,.h81-goals:visible').count()===0);
    check('akce na první obrazovce',await pg.locator('.student-primary-cta').evaluate(e=>e.getBoundingClientRect().bottom<innerHeight-64));
    await pg.locator('.h81-help summary').click();check('nápověda se otevře',await pg.locator('.h81-help').getAttribute('open')!==null);
    await pg.locator('.h81-help summary').click();
    await pg.locator('.student-primary-cta').click();check('aktuální krok vede do práce',!pg.url().endsWith('view=dashboard') && await pg.locator('main').count()===1);
    await pg.goto('http://127.0.0.1:8090/?view=dashboard',{waitUntil:'networkidle'});
    await Promise.all([pg.waitForURL('**details=1'),pg.locator('a[href="?view=dashboard&details=1"]').click()]);
    await pg.locator('.v5076-core-metrics').waitFor({state:'visible'});
    check('první detail: lekce témata projekty',await pg.locator('.v5076-core-metrics article').count()===3 && await pg.locator('.h81-ring:visible').count()===0);
    await pg.locator('.v5076-progress-advanced > summary').click();
    check('další detail: analytika dostupná',await pg.locator('.h81-ring:visible').count()===1);
    const p=await pg.evaluate(probe,true);check('rozbalený detail bez posunu',!p.hscroll);
    await pg.screenshot({path:root+'/docs/screens-2026-10-29/'+(phase.startsWith('structure-')?phase:'after')+'-detail-'+scheme+'-'+w+'.jpg',type:'jpeg',quality:55,fullPage:true});
   }
   if(id==='s-kalendar'){
    const first=pg.locator('[data-t52-tab]').first();await first.click();await first.focus();
    for(const [key,index] of [['ArrowRight',1],['End',9],['Home',0],['ArrowLeft',9]]){
     await pg.keyboard.press(key);const buttons=pg.locator('[data-t52-tab]');const selected=buttons.nth(index);
     check('měsíce: '+key,await selected.getAttribute('aria-selected')==='true' && await selected.evaluate(e=>e===document.activeElement));
    }
    const focus=await pg.evaluate(()=>{const s=getComputedStyle(document.activeElement);return {style:s.outlineStyle,width:parseFloat(s.outlineWidth)}});
    check('viditelný fokus kalendáře',focus.style!=='none'&&focus.width>=2);
    check('jeden panel měsíce',await pg.locator('[data-t52-panel]:visible').count()===1);
   }
   if(id==='t-priprava'){
    await pg.goto('http://127.0.0.1:8090/teacher.php?tab=priprava79&class=class_3a&krok=1',{waitUntil:'networkidle'});
    for(let step=2;step<=4;step++){
     const next=pg.getByRole('button',{name:/Další:/}).or(pg.getByRole('link',{name:/Další:/}));
     await next.click();await pg.waitForLoadState('networkidle');
     check('příprava krok '+step,new URL(pg.url()).searchParams.get('krok')===String(step));
    }
    const p=await pg.evaluate(probe,true);check('tisk a projektor bez posunu',!p.hscroll);
   }
  }
 }
 await pg.close();}await s.close();await t.close();}

 if(phase!=='before'){
  const fixture=spawnSync('/usr/bin/php',['-r',`require 'bootstrap.php';require 'src/lessons/session_v53.php';sess53_open('class_3a',date('Y-m-d'),$modules,['title'=>'Testovací hodina','steps'=>[]],'work',['lesson_number'=>7]);`],{cwd:temp+'/app',env,encoding:'utf8'});
  if(fixture.status!==0)throw Error('Fixture hodiny: '+fixture.stderr);
  for(const scheme of ['light','dark']){
   const ctx=await b.newContext({colorScheme:scheme,reducedMotion:'reduce'}),pg=await ctx.newPage();
   await pg.goto('http://127.0.0.1:8090/?class=class_3a&student=Vizuální%20test');
   pg.on('pageerror',e=>out.errors.push({at:'aktivni-hodina',message:e.message}));
   pg.on('console',m=>{if(m.type()==='error')out.console.push({at:'aktivni-hodina',message:m.text()})});
   pg.on('response',r=>{if(r.status()>=400)out.http.push({at:'aktivni-hodina',url:r.url(),status:r.status()})});
   for(const w of [1440,390]){
    await pg.setViewportSize({width:w,height:900});await pg.goto('http://127.0.0.1:8090/?view=hodina',{waitUntil:'networkidle'});
    if(new URL(pg.url()).searchParams.get('view')!=='hodina')throw Error('Otevřená hodina není dostupná studentovi');
    out.rows.push({id:'s-dnes-aktivni',scheme,w,url:pg.url(),...await pg.evaluate(probe,true)});
    await pg.screenshot({path:root+'/docs/screens-2026-10-29/'+(phase.startsWith('structure-')?phase:(phase==='before-active'?'before':'after'))+'-s-dnes-aktivni-'+scheme+'-'+w+'.jpg',type:'jpeg',quality:65,fullPage:true});
   }await ctx.close();
  }
 }
 }catch(e){out.fatal=e.stack;process.exitCode=1}finally{if(b)await b.close();php.kill();out.protectedDataUnchanged=protectedPaths.every((p,i)=>fingerprint(root+'/'+p)===originalHashes[i]);if(!out.protectedDataUnchanged){out.fatal='Protected data changed';process.exitCode=1;}fs.writeFileSync(temp+'/php.log',log);out.phpErrors=log.split('\n').filter(x=>/PHP (Warning|Notice|Fatal|Deprecated|Parse)|Uncaught/.test(x));fs.writeFileSync(root+'/docs/screens-2026-10-29/'+phase+'-results.json',JSON.stringify(out,null,2));if(out.errors.length||out.http.length||out.console.length||out.phpErrors.length||(phase.startsWith('after')&&out.rows.some(r=>r.hscroll||r.lowCount||r.tinyCount||r.smallCount)))process.exitCode=1;console.log(JSON.stringify({phase,rows:out.rows.length,interactions:out.interactions.length,protectedDataUnchanged:out.protectedDataUnchanged,issues:out.rows.filter(r=>r.hscroll||r.lowCount||r.tinyCount||r.smallCount).map(r=>({id:r.id,w:r.w,scheme:r.scheme,wide:r.wide,low:r.lowC.slice(0,5),count:r.lowCount,tiny:r.tiny,small:r.small})),errors:out.errors,http:out.http,php:out.phpErrors,fatal:out.fatal,temp},null,2))}})();
