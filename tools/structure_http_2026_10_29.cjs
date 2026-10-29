// Porovnání veřejných URL na izolované kopii; POST formuláře pouze objevujeme.
// rtk proxy node tools/structure_http_2026_10_29.cjs before|after
const fs = require('fs'), path = require('path'), os = require('os'), crypto = require('crypto');
const {spawn} = require('child_process');
const {chromium} = require(path.join(os.homedir(), 'Projects/prezentace/node_modules/playwright'));
const root = process.cwd(), phase = process.argv[2] || 'after';
const temp = fs.mkdtempSync(path.join(os.tmpdir(), 'educanet-structure-'));
const output = path.join(root, 'docs/structure-2026-10-29');
const protectedPaths = ['storage', 'private', 'uploads', 'cache'].filter(p => fs.existsSync(p));
function fingerprint(dir) {
  const hash = crypto.createHash('sha256');
  function visit(p) {
    for (const e of fs.readdirSync(p, {withFileTypes:true}).sort((a,b) => a.name.localeCompare(b.name))) {
      const f = path.join(p,e.name); hash.update(path.relative(dir,f));
      if(e.isDirectory()) visit(f); else hash.update(e.isSymbolicLink() ? fs.readlinkSync(f) : fs.readFileSync(f));
    }
  }
  visit(dir); return hash.digest('hex');
}
const hashes = protectedPaths.map(fingerprint);
fs.cpSync(root, temp+'/app', {recursive:true, filter:p => !['.git','docs'].includes(path.relative(root,p).split(path.sep)[0])});
const key = crypto.randomBytes(32).toString('hex');
const server = spawn('/usr/bin/php', ['-S','127.0.0.1:8090'], {cwd:temp+'/app',env:{...process.env,
  PHP_INI_SCAN_DIR:':'+os.homedir()+'/.config/php/conf.d', EDUCANET_TEACHER_EXPORT_KEY:key,
  EDUCANET_STORAGE_DIR:temp+'/app/storage', EDUCANET_DEV_BYPASS:'1', EDUCANET_LM71_CACHE_DIR:temp+'/cache'}});
let log='';server.stderr.on('data',d=>log+=d);server.stdout.on('data',d=>log+=d);
const origin='http://127.0.0.1:8090';
(async()=>{
 let browser; const rows=[]; let phpErrors=[], unchanged=false;
 try {
  for(let i=0;i<100;i++){try{await fetch(origin);break}catch{await new Promise(r=>setTimeout(r,100))}}
  browser=await chromium.launch({executablePath:'/usr/bin/chromium',headless:true});
  for(const role of ['anonymous','student','teacher']) {
   const ctx=await browser.newContext(), page=await ctx.newPage();
   if(role==='student') await page.goto(origin+'/?class=class_3a&student=Struktura%20test');
   if(role==='teacher') {
    await page.goto(origin+'/teacher.php');
    await page.locator('[name=teacher_name]').fill('Struktura test');await page.locator('[name=teacher_key]').fill(key);
    await Promise.all([page.waitForNavigation(),page.locator('form button[type=submit]').click()]);
   }
   const seeds=['/','/index.php','/teacher.php','/calendar.ics.php','/practice.php','/progress.php',
    '/export_extra_csv.php','/lab_v57_api.php','/teamgames_v58_api.php','/robots_v58_api.php','/arena_v58_events_api.php',
    '/lab-offline.html','/sw.js','/manifest.webmanifest'];
   const routes=fs.readFileSync(root+'/app/routes.php','utf8');
   for(const match of routes.matchAll(/'match'\s*=>\s*\[([^\]]+)\]/g))
    for(const name of match[1].matchAll(/'([a-zA-Z0-9_]+)'/g)) seeds.push('/?view='+name[1]);
   const old=phase==='after' ? JSON.parse(fs.readFileSync(output+'/before.json','utf8')).rows.filter(r=>r.role===role).map(r=>r.url) : [];
   const queue=[...new Set(seeds)].map(url=>({url,depth:0})), seen=new Set();
   for(const url of old) if(!seeds.includes(url)) queue.push({url,depth:1});
   for(let i=0;i<queue.length;i++) {
    const {url,depth}=queue[i];if(seen.has(url))continue;seen.add(url);
    const response=await ctx.request.get(origin+url,{maxRedirects:0,timeout:30000});
    rows.push({role,url,status:response.status(),location:response.headers().location||''});
    if(depth>0 || !(response.headers()['content-type']||'').includes('text/html'))continue;
    const html=await response.text();
    for(const m of html.matchAll(/(?:href|action)\s*=\s*["']([^"'<>]*)["']/gi)) {
     const value=m[1].replace(/&amp;/g,'&');if(!value || value.includes('<?') || value.startsWith('#'))continue;
     let next;try{next=new URL(value,origin+url)}catch{continue}
     if(next.origin!==origin || /logout|delete|remove|purge|reset|recompute/.test(next.search))continue;
     if(!/\.(php|html)$/.test(next.pathname) && next.pathname!=='/')continue;
     queue.push({url:next.pathname+next.search,depth:depth+1});
    }
   }
   await ctx.close();
  }
 } finally {
  if(browser)await browser.close();server.kill();
  await new Promise(resolve=>server.once('close',resolve));
  phpErrors=log.split('\n').filter(l=>/PHP (Warning|Notice|Fatal|Deprecated|Parse)|Uncaught/.test(l)).map(l=>l.replace(/^\[[^\]]+\] /,'').replaceAll(temp+'/app','APP'));
  unchanged=protectedPaths.every((p,i)=>fingerprint(p)===hashes[i]);
  fs.mkdirSync(output,{recursive:true});fs.writeFileSync(output+'/'+phase+'.json',JSON.stringify({rows,phpErrors,protectedDataUnchanged:unchanged},null,2)+'\n');
  fs.rmSync(temp,{recursive:true,force:true});
 }
 if(!unchanged)throw Error('Změnila se chráněná data');
 if(phase==='after') {
  const before=JSON.parse(fs.readFileSync(output+'/before.json','utf8'));
  const sort=a=>a.map(r=>JSON.stringify(r)).sort();
  if(JSON.stringify(sort(before.rows))!==JSON.stringify(sort(rows)))throw Error('HTTP výsledky se liší');
  // Cesty interních souborů se změnily; počet a druh existujících chyb zůstává kontrolovatelný.
  if(before.phpErrors.length!==phpErrors.length)throw Error('Změnil se počet PHP chyb');
 }
 console.log(JSON.stringify({phase,requests:rows.length,phpErrors:phpErrors.length,protectedDataUnchanged:unchanged}));
})().catch(e=>{console.error(e);process.exitCode=1});
