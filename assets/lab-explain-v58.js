/* EDUCANET v58 · Linux Lab – „Vysvětli výstup“ (EDU-05).
 * Samostatný modul: neupravuje linux-v57.js, jen čte DOM ([data-lab57-out]) a poslouchá kliky/klávesy.
 * Žádné innerHTML s daty – vysvětlení se staví přes textContent/createElement (XSS).
 * V režimu vysvětlování je roving tabindex nad *bloky* výstupu (jeden příkaz/výstup = jeden blok),
 * ne nad každým fyzickým řádkem – proto pár desítek zastávek na úroveň, ne stovky. */
(function () {
  'use strict';

  var EduI18n = window.EduI18n || {locale:'cs', tr:function(s,p){return String(s).replace(/\{([a-z0-9_]+)\}/gi,function(m,k){return p&&Object.prototype.hasOwnProperty.call(p,k)?String(p[k]):m;});}, trn:function(f,n,p){p=p||{};if(!('n' in p))p.n=n;var a=Math.abs(parseInt(n,10)||0);var s=a===1?f.one:(a>=2&&a<=4?(f.few||f.other):f.other);return this.tr(s||f.other,p);}};

  // ------------------------------------------------------------------
  // Pravidla: příkaz → { title, explain(cmd, out) → list<string> } – VÝUKOVÝ OBSAH, nepřekládá se
  // (kontejner panelu dostává lang="cs" v linux_v57_views.php).
  // ------------------------------------------------------------------

  function firstWord(text) {
    var tokens = String(text || '').trim().split(/\s+/).filter(function (t) { return t !== ''; });
    var i = 0;
    while (i < tokens.length && (tokens[i] === 'sudo' || /^[A-Za-z_][A-Za-z0-9_]*=/.test(tokens[i]))) i++;
    return tokens[i] || '';
  }

  function explainLs() {
    var paragraphs = ['ls -l vypíše obsah složky s podrobnostmi: typ a práva, počet odkazů, vlastník, skupina, velikost v bajtech, datum poslední změny a jméno.'];
    return paragraphs;
  }
  function explainLsDynamic(out) {
    var m = /([dl-])([r-][w-][xsS-])([r-][w-][xsS-])([r-][w-][xtT-])/.exec(out || '');
    if (!m) return null;
    var type = m[1] === 'd' ? 'složka' : (m[1] === 'l' ? 'symbolický odkaz' : 'běžný soubor');
    var describe = function (bits) {
      var list = [];
      if (bits.charAt(0) !== '-') list.push('čtení');
      if (bits.charAt(1) !== '-') list.push('zápis');
      var x = bits.charAt(2);
      if (x !== '-' && x !== 'S' && x !== 'T') list.push('spouštění');
      return list.length ? list.join(', ') : 'žádná práva';
    };
    return 'První nalezený záznam v tomto výstupu: ' + type + '. Vlastník smí: ' + describe(m[2]) +
      '. Skupina smí: ' + describe(m[3]) + '. Ostatní smí: ' + describe(m[4]) + '.';
  }
  function explainLsAll(cmd, out) {
    var p = explainLs();
    var dyn = explainLsDynamic(out);
    if (dyn) p.push(dyn);
    p.push('Pořadí sloupců za právy: počet odkazů, vlastník, skupina, velikost, datum a jméno souboru.');
    return p;
  }

  function explainPs() {
    return [
      'ps vypíše aktuální seznam procesů, top ukazuje totéž průběžně (v simulaci jako jeden snímek).',
      'Časté sloupce: PID (číslo procesu), uživatel, %CPU, %MEM a příkaz. PID se hodí třeba pro kill <pid>.'
    ];
  }

  function explainDf(cmd) {
    var word = firstWord(cmd);
    if (word === 'free') return ['free ukazuje paměť RAM: total (celkem), used (obsazeno), free (volno) a available (reálně dostupné pro nové programy – Linux volnou paměť dočasně využívá jako cache).'];
    if (word === 'du') return ['du spočítá velikost souborů a složek. Přepínač -h zobrazí čitelné jednotky (K/M/G), -s jen součet za celou složku bez výpisu podsložek.'];
    return ['df ukazuje místo na připojených discích: velikost, použito, volno a Use% (procento zaplnění). Mounted on je místo, kam je disk připojený.'];
  }

  function explainIp(cmd) {
    var parts = String(cmd || '').trim().split(/\s+/);
    var sub = parts[1] || '';
    if (sub.indexOf('r') === 0) return ['ip route vypisuje směrovací tabulku – kam mají jít pakety pro danou cílovou síť. Řádek „default via …“ je výchozí brána pro vše, co nesedí na konkrétnější trasu.'];
    return [
      'ip addr (zkráceně ip a) vypisuje síťová rozhraní: stav UP/DOWN, adresu inet (IPv4 s maskou za lomítkem) a inet6 (IPv6).',
      'Rozhraní lo je vždy „loopback“ (počítač sám se sebou, 127.0.0.1). Ostatní (např. eth0) jsou skutečná rozhraní.'
    ];
  }

  function explainPing() {
    return [
      'ping posílá pakety ICMP a měří dobu odpovědi (time=… ms) a TTL (počet skoků, které paket ještě smí udělat).',
      'icmp_seq je pořadové číslo paketu. Souhrn na konci (packets transmitted/received, % packet loss) říká, kolik paketů se ztratilo.',
      'Bez odpovědi to nemusí být závada – některé servery ping (ICMP) záměrně blokují.'
    ];
  }

  function explainTraceroute() {
    return [
      'traceroute posílá pakety s postupně rostoucím TTL a vypisuje, který router odpověděl v každém kroku (hopu) – tak uvidíš celou cestu k cíli.',
      'Na řádku bývají tři časy (tři pokusy na stejný hop). Hvězdičky * * * znamenají, že daný hop neodpověděl.'
    ];
  }

  function explainDns() {
    return [
      'DNS dotaz převádí jméno na IP adresu. V odpovědi hledej záznam s adresou (sekce ANSWER).',
      'Stav NOERROR znamená, že server odpověděl v pořádku (i bez záznamu), NXDOMAIN že jméno neexistuje. Žádná odpověď = server neodpovídá (zkontroluj /etc/resolv.conf).'
    ];
  }

  var HTTP_CODES = {
    '200': 'OK – požadavek se povedl, server posílá obsah.',
    '201': 'Created – server podle požadavku něco vytvořil.',
    '301': 'Moved Permanently – trvalé přesměrování (viz hlavička Location).',
    '302': 'Found – dočasné přesměrování na jinou adresu.',
    '304': 'Not Modified – obsah se od minula nezměnil.',
    '400': 'Bad Request – požadavek byl špatně sestavený.',
    '401': 'Unauthorized – je potřeba se přihlásit.',
    '403': 'Forbidden – přístup byl odmítnut.',
    '404': 'Not Found – na dané adrese nic není.',
    '500': 'Internal Server Error – chyba na straně serveru.',
    '502': 'Bad Gateway – server za bránou/proxy neodpověděl správně.',
    '503': 'Service Unavailable – server je dočasně nedostupný.'
  };
  function explainHttp(cmd, out) {
    var paragraphs = ['Odpověď HTTP začíná stavovým řádkem (verze protokolu a třímístný kód) a pokračuje hlavičkami (jméno: hodnota), např. Content-Type, Content-Length, Server nebo Location.'];
    var m = /HTTP\/\d(?:\.\d)?\s+(\d{3})/.exec(out || '');
    if (m) paragraphs.push('Nalezený stavový kód ' + m[1] + (HTTP_CODES[m[1]] ? ': ' + HTTP_CODES[m[1]] : ' – neznámý/neobvyklý kód.'));
    return paragraphs;
  }

  function explainSs() {
    return [
      'ss (nebo starší netstat) vypisuje síťová spojení. State ukazuje stav (LISTEN = naslouchá na portu, ESTABLISHED = navázané spojení).',
      'Local Address:Port je adresa a port na tomto počítači, Peer Address:Port protistrana. Proto je protokol (tcp/udp).'
    ];
  }

  function explainSystemctl(cmd) {
    var sub = String(cmd || '').trim().split(/\s+/)[1] || '';
    if (sub !== 'status') return ['Tenhle podpříkaz systemctl (' + (sub || '?') + ') zatím rychlé vysvětlení nemá – zkus příručku.'];
    return [
      'systemctl status <služba> ukáže stav jedné služby (jednotky systemd): Active říká, jestli běží (active (running)), selhala (failed), nebo je vypnutá (inactive/dead).',
      'Loaded ukazuje, jestli je služba povolená při startu (enabled) nebo ne (disabled). Main PID je hlavní proces.',
      'Pod tím bývá pár posledních řádků logu – stejné zprávy jako journalctl -u <služba>.'
    ];
  }

  function explainJournalctl() {
    return [
      'journalctl čte systémový žurnál. Řádek má čas, jméno počítače, jednotku/proces a zprávu.',
      'journalctl -u <služba> ukáže log jedné služby, -n <počet> omezí počet vypsaných řádků.'
    ];
  }

  function explainGit(cmd) {
    var sub = String(cmd || '').trim().split(/\s+/)[1] || '';
    if (sub === 'status') return ['git status ukáže, co se změnilo: soubory připravené ke commitu (staged), změněné nepřipravené a nesledované nové (untracked) soubory.'];
    if (sub === 'log') return ['git log vypisuje historii commitů od nejnovějšího: hash, autora, datum a zprávu. Každý commit je jeden uložený stav projektu.'];
    if (sub === 'show' || sub === 'diff') return ['git diff/show ukazuje konkrétní změny řádků: řádky se znaménkem − zmizely, řádky se znaménkem + přibyly.'];
    return ['git pracuje s historií verzí – zkus git status (co se změnilo) nebo git log (historie commitů).'];
  }

  function explainTar() {
    return [
      'Přepínač -t vypíše obsah archivu bez rozbalení, -v přidá podrobnosti – podobně jako ls -l: práva, vlastník/skupina, velikost, datum a cesta souboru uvnitř archivu.',
      'Podle přípony poznáš kompresi: .tar (bez komprese), .tar.gz/.tgz (gzip), .tar.bz2 (bzip2).'
    ];
  }

  function explainCrontab() {
    return [
      'Řádek v crontabu má tvar: minuta hodina den-v-měsíci měsíc den-v-týdnu příkaz. Hvězdička * znamená „kdykoli“.',
      'Např. "0 3 * * *" spustí příkaz každý den ve 3:00. Dny v týdnu jsou 0–6 (0 = neděle).',
      'crontab -l vypíše naplánované úlohy, crontab -e je upraví.'
    ];
  }

  function explainId(cmd, out) {
    var paragraphs = ['Identita uživatele: uid (číslo a jméno účtu), gid (hlavní skupina) a groups (všechny skupiny). Členství ve skupině často rozhoduje o přístupu k souborům.'];
    var m = /uid=(\d+)\(([^)]+)\)/.exec(out || '');
    if (m) paragraphs.push('V tomto výstupu jsi přihlášen/a jako „' + m[2] + '“ (uid ' + m[1] + ').');
    return paragraphs;
  }

  var RULES = {
    ls: { title: 'ls -l — výpis souborů', explain: explainLsAll },
    ps: { title: 'ps / top — procesy', explain: explainPs },
    top: { title: 'ps / top — procesy', explain: explainPs },
    df: { title: 'df / du / free — místo a paměť', explain: explainDf },
    du: { title: 'df / du / free — místo a paměť', explain: explainDf },
    free: { title: 'df / du / free — místo a paměť', explain: explainDf },
    ip: { title: 'ip — rozhraní a trasy', explain: explainIp },
    ping: { title: 'ping — dostupnost přes ICMP', explain: explainPing },
    traceroute: { title: 'traceroute — cesta k cíli', explain: explainTraceroute },
    tracepath: { title: 'traceroute — cesta k cíli', explain: explainTraceroute },
    dig: { title: 'DNS dotaz', explain: explainDns },
    nslookup: { title: 'DNS dotaz', explain: explainDns },
    host: { title: 'DNS dotaz', explain: explainDns },
    curl: { title: 'HTTP odpověď', explain: explainHttp },
    wget: { title: 'HTTP odpověď', explain: explainHttp },
    ss: { title: 'ss / netstat — spojení', explain: explainSs },
    netstat: { title: 'ss / netstat — spojení', explain: explainSs },
    systemctl: { title: 'systemctl status', explain: explainSystemctl },
    journalctl: { title: 'journalctl — systémový log', explain: explainJournalctl },
    git: { title: 'git log / status', explain: explainGit },
    tar: { title: 'tar -tv — obsah archivu', explain: explainTar },
    crontab: { title: 'crontab — plánované úlohy', explain: explainCrontab },
    id: { title: 'id / groups — identita', explain: explainId },
    groups: { title: 'id / groups — identita', explain: explainId }
  };

  // ------------------------------------------------------------------
  // Přiřazení bloku výstupu → příkaz, který ho vytvořil
  // ------------------------------------------------------------------

  function isCmdline(el) { return !!(el && el.classList && el.classList.contains('lab57-cmdline')); }

  function commandTextOf(el) {
    if (!el || !el.lastChild) return '';
    return el.lastChild.textContent || '';
  }

  function findCommandFor(block) {
    if (isCmdline(block)) return commandTextOf(block);
    var node = block.previousElementSibling;
    while (node) {
      if (isCmdline(node)) return commandTextOf(node);
      node = node.previousElementSibling;
    }
    return '';
  }

  function explainBlock(block) {
    if (block.classList.contains('lab57-tipline') || block.classList.contains('lab57-sysline')) {
      return { title: EduI18n.tr('Zpráva simulátoru'), paragraphs: [EduI18n.tr('Tohle je nápověda nebo systémová zpráva simulátoru, ne přímý výstup příkazu.')], manualCmd: null };
    }
    var cmdText = findCommandFor(block);
    var word = firstWord(cmdText);
    var outText = isCmdline(block) ? '' : (block.textContent || '');
    var rule = word ? RULES[word] : null;
    if (!rule) {
      var paragraphs = word
        ? [EduI18n.tr('Pro příkaz „{prikaz}“ zatím nemáme rychlé vysvětlení přímo tady – zkus příručku příkazů.', {prikaz: word})]
        : [EduI18n.tr('K tomuhle řádku se nepodařilo najít příkaz, ke kterému patří.')];
      return { title: word ? EduI18n.tr('Příkaz {prikaz}', {prikaz: word}) : EduI18n.tr('Výstup'), paragraphs: paragraphs, manualCmd: word || null };
    }
    return { title: rule.title, paragraphs: rule.explain(cmdText, outText), manualCmd: word };
  }

  // ------------------------------------------------------------------
  // Roving tabindex + panel
  // ------------------------------------------------------------------

  function initExplain(root) {
    var toggle = root.querySelector('[data-lab57-explain-toggle]');
    var out = root.querySelector('[data-lab57-out]');
    var panel = root.querySelector('[data-lab57-explain-panel]');
    var titleEl = root.querySelector('[data-lab57-explain-title]');
    var bodyEl = root.querySelector('[data-lab57-explain-body]');
    var manualLink = root.querySelector('[data-lab57-explain-manual]');
    var closeBtn = root.querySelector('[data-lab57-explain-close]');
    if (!toggle || !out || !panel || !titleEl || !bodyEl || !closeBtn) return;

    var active = false;
    var current = -1;
    var lastFocused = null;

    function blocks() {
      return Array.prototype.filter.call(out.children, function (el) { return el.nodeType === 1; });
    }

    function decorate(el) {
      if (el.hasAttribute('data-lab57-explain-decorated')) return;
      el.setAttribute('data-lab57-explain-decorated', '1');
      if (!el.hasAttribute('role')) { el.setAttribute('role', 'button'); el.setAttribute('data-lab57-explain-role', '1'); }
      if (!el.hasAttribute('aria-label')) {
        var t = (el.textContent || '').replace(/\s+/g, ' ').trim();
        el.setAttribute('aria-label', EduI18n.tr('Vysvětlit řádek:') + ' ' + (t.length > 60 ? t.slice(0, 60) + '…' : (t || EduI18n.tr('(prázdné)'))));
        el.setAttribute('data-lab57-explain-label', '1');
      }
    }
    function undecorate(el) {
      el.removeAttribute('tabindex');
      el.classList.remove('lab57-explain-current');
      if (el.getAttribute('data-lab57-explain-role') === '1') el.removeAttribute('role');
      if (el.getAttribute('data-lab57-explain-label') === '1') el.removeAttribute('aria-label');
      el.removeAttribute('data-lab57-explain-decorated');
      el.removeAttribute('data-lab57-explain-role');
      el.removeAttribute('data-lab57-explain-label');
    }

    function setRoving(index) {
      var list = blocks();
      if (list.length === 0) { current = -1; return; }
      index = Math.max(0, Math.min(list.length - 1, index));
      list.forEach(function (el, i) {
        decorate(el);
        el.tabIndex = i === index ? 0 : -1;
        el.classList.toggle('lab57-explain-current', i === index);
      });
      current = index;
    }

    function activate() {
      active = true;
      toggle.setAttribute('aria-pressed', 'true');
      root.classList.add('lab57-explain-active');
      out.tabIndex = -1;
      setRoving(blocks().length - 1);
      var list = blocks();
      if (list[current]) list[current].focus();
    }
    function deactivate() {
      active = false;
      toggle.setAttribute('aria-pressed', 'false');
      root.classList.remove('lab57-explain-active');
      out.tabIndex = 0;
      blocks().forEach(undecorate);
      closePanel();
    }
    toggle.addEventListener('click', function () { if (active) deactivate(); else activate(); });

    function openFor(block) {
      lastFocused = block;
      var info = explainBlock(block);
      titleEl.textContent = info.title;
      while (bodyEl.firstChild) bodyEl.removeChild(bodyEl.firstChild);
      info.paragraphs.forEach(function (text) {
        var p = document.createElement('p');
        p.textContent = text;
        bodyEl.appendChild(p);
      });
      if (manualLink) {
        if (info.manualCmd) {
          manualLink.hidden = false;
          manualLink.href = '?view=prikazy&c=' + encodeURIComponent(info.manualCmd);
          manualLink.textContent = EduI18n.tr('Otevřít „{prikaz}“ v příručce →', {prikaz: info.manualCmd});
        } else {
          manualLink.hidden = true;
        }
      }
      panel.hidden = false;
      closeBtn.focus();
    }
    function closePanel() {
      panel.hidden = true;
      if (lastFocused && document.contains(lastFocused) && active) lastFocused.focus();
    }
    closeBtn.addEventListener('click', closePanel);
    panel.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { e.preventDefault(); closePanel(); }
    });

    out.addEventListener('keydown', function (e) {
      if (!active) return;
      var list = blocks();
      if (list.length === 0) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); setRoving(current + 1); list = blocks(); if (list[current]) list[current].focus(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setRoving(current - 1); list = blocks(); if (list[current]) list[current].focus(); }
      else if (e.key === 'Home') { e.preventDefault(); setRoving(0); list = blocks(); if (list[current]) list[current].focus(); }
      else if (e.key === 'End') { e.preventDefault(); setRoving(list.length - 1); list = blocks(); if (list[current]) list[current].focus(); }
      else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (list[current]) openFor(list[current]); }
      else if (e.key === 'Escape') { e.preventDefault(); deactivate(); }
    });

    // Kapturovaná fáze doběhne dřív než bublající posluchač linux-v57.js, který by při
    // kliknutí do výstupu přesunul fokus zpět na vstupní řádek (out.addEventListener('click', …)).
    out.addEventListener('click', function (e) {
      if (!active) return;
      var el = e.target;
      while (el && el.parentNode !== out) el = el.parentNode;
      if (!el || el.parentNode !== out) return;
      e.stopPropagation();
      setRoving(blocks().indexOf(el));
      openFor(el);
    }, true);

    if (window.MutationObserver) {
      new MutationObserver(function () { if (active) setRoving(blocks().length - 1); }).observe(out, { childList: true });
    }
  }

  function ready() {
    document.querySelectorAll('[data-lab57]').forEach(initExplain);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();
})();
