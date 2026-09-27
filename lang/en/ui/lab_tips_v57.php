<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „lab_tips_v57“ (angličtina).
 * Nápovědy simulátoru Linuxu ($w->tip() volané z linux_v57_world.php, linux_v57_cmd_files.php,
 * linux_v57_cmd_net.php, linux_v57_cmd_shell.php, linux_v57_cmd_sys.php, linux_v57_cmd_text.php).
 * Tipy jsou UI nápověda a překládají se; výstup simulovaných programů (stdout/stderr) zůstává anglicky.
 * Klíč = přesně český text ze zdroje. Vlastník: i18n builder TIP1.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // linux_v57_world.php
    'Soubor by byl větší než 1 MB – na tvém cvičném disku pro něj už není místo.' => "The file would be larger than 1 MB – there's no space left for it on your practice disk.",
    // linux_v57_cmd_files.php
    'Neznámá volba. Seznam voleb ukáže man {prikaz} nebo {prikaz} --help.' => 'Unknown option. List the options with man {prikaz} or {prikaz} --help.',
    '{prikaz} bez jména souboru čeká na text z klávesnice. V simulaci mu ho pošli rourou (např. cat soubor | {prikaz}) nebo napiš jméno souboru.' => "{prikaz} without a filename waits for keyboard input. In the simulator, pipe it in instead (e.g. cat file | {prikaz}) or give it a filename.",
    '{prikaz} v simulaci ukáže celý soubor najednou. Ve skutečném terminálu listuješ mezerníkem a končíš klávesou q.' => 'In the simulator, {prikaz} shows the whole file at once. In a real terminal you scroll with space and quit with q.',
    'tail -f by čekal na nové řádky. Simulace vypíše konec souboru a hned skončí.' => 'tail -f would wait for new lines. The simulator prints the end of the file and exits right away.',
    'Nadřazená složka neexistuje. Celou cestu najednou vytvoří mkdir -p {cesta}' => 'The parent folder does not exist. mkdir -p {cesta} creates the whole path at once.',
    'rmdir maže jen prázdné složky. Složku i s obsahem smaže rm -r {cesta} (opatrně!).' => 'rmdir only removes empty folders. rm -r {cesta} removes a folder and its contents (careful!).',
    'Smazání celého systému laboratoř nedovolí ani v simulaci. Úroveň vrátíš do původního stavu příkazem reset.' => "The lab won't let you delete the whole system, even in the simulator. Use the reset command to return the level to its starting state.",
    'Složku smažeš rm -r {cil}, prázdnou také rmdir {cil}.' => 'Remove a folder with rm -r {cil}; an empty one also with rmdir {cil}.',
    'Složku zkopíruješ s volbou -r: cp -r {zdroj} {cil}' => 'Copy a folder with the -r option: cp -r {zdroj} {cil}',
    'Práva může měnit jen vlastník souboru nebo správce (sudo chmod …).' => 'Only the file owner or an admin can change permissions (sudo chmod …).',
    'Vlastníka souboru mění jen správce systému: sudo chown {spec} {soubor}' => 'Only a system admin can change a file owner: sudo chown {spec} {soubor}',
    // linux_v57_cmd_net.php
    'Jméno „{jmeno}“ nešlo přeložit na IP adresu – žádný DNS server neodpověděl. Zkus ping na IP adresu (např. 1.1.1.1) a podívej se do /etc/resolv.conf.' => 'The name "{jmeno}" could not be resolved to an IP address – no DNS server answered. Try pinging an IP address (e.g. 1.1.1.1) and check /etc/resolv.conf.',
    'DNS server odpověděl, že jméno „{jmeno}“ neexistuje. Zkontroluj překlep.' => 'The DNS server replied that the name "{jmeno}" does not exist. Check for a typo.',
    'Síťovou konfiguraci mění správce: sudo ip addr …' => 'Only an admin can change the network configuration: sudo ip addr …',
    'Rozhraní zapíná a vypíná správce: sudo ip link set {rozhrani} {stav}' => 'Only an admin turns interfaces on and off: sudo ip link set {rozhrani} {stav}',
    'Směrovací tabulku mění správce: sudo ip route {argumenty}' => 'Only an admin changes the routing table: sudo ip route {argumenty}',
    'Tahle trasa už existuje. Zobrazíš je ip route; nahradit ji jde přes ip route replace.' => 'This route already exists. See routes with ip route; replace it with ip route replace.',
    'Brána musí ležet ve stejné síti jako tvoje rozhraní (ip a ukáže adresu a masku).' => 'The gateway must be in the same network as your interface (ip a shows the address and mask).',
    'ifconfig je starší nástroj z balíčku net-tools. Dnes se používá ip a (adresy) a ip route (trasy).' => 'ifconfig is an older tool from the net-tools package. Today ip a (addresses) and ip route (routes) are used instead.',
    'Skutečný ping běží, dokud ho nezastavíš Ctrl+C. Simulace pošle 4 pakety (jako ping -c 4).' => 'A real ping keeps running until you stop it with Ctrl+C. The simulator sends 4 packets (like ping -c 4).',
    'Počítač nezná cestu do cílové sítě. Zkontroluj ip route – chybí výchozí brána (default via …)? Nebo je rozhraní vypnuté (ip link)?' => "The computer doesn't know the route to the target network. Check ip route – is the default gateway (default via …) missing? Or is the interface down (ip link)?",
    'Cíl (nebo brána) v místní síti neodpovídá. Je zapojený kabel a zapnuté rozhraní? Zkus ip link a ip neigh.' => 'The target (or gateway) on the local network is not responding. Is the cable connected and the interface up? Try ip link and ip neigh.',
    'Na ping neodpovídá, ale to ještě neznamená, že nefunguje – některé servery ICMP blokují. Zkus curl nebo nc na jejich port.' => "It doesn't answer ping, but that doesn't mean it's down – some servers block ICMP. Try curl or nc on their port.",
    'Pakety se někde po cestě ztrácí. Kde přesně, ukáže traceroute {cil}.' => 'Packets are getting lost somewhere along the way. traceroute {cil} shows exactly where.',
    'Hvězdičky * * * znamenají, že od tohoto skoku už nepřišla odpověď. Problém je mezi posledním odpovídajícím routerem a dalším. (Skutečný traceroute by zkoušel až 30 skoků.)' => 'The stars * * * mean no reply came back from this hop onward. The problem is between the last responding router and the next one. (A real traceroute would try up to 30 hops.)',
    'Žádný DNS server z /etc/resolv.conf neodpověděl. Je adresa serveru správná a dostupná (ping)?' => 'No DNS server from /etc/resolv.conf answered. Is the server address correct and reachable (ping)?',
    'Počítač odpověděl, ale na portu {port} nic neposlouchá (Connection refused). Běží služba? Poslouchá na jiném portu? Zkus ss -tlnp nebo systemctl status.' => "The computer replied, but nothing is listening on port {port} (Connection refused). Is the service running? Is it listening on another port? Try ss -tlnp or systemctl status.",
    'Síť je nedosažitelná – zkontroluj ip route a ip link.' => 'The network is unreachable – check ip route and ip link.',
    'Spojení vypršelo – odpověď nepřišla. Pomůže ping a traceroute na {cil}.' => 'The connection timed out – no reply came. ping and traceroute to {cil} can help.',
    'Které procesy porty drží, uvidíš jen jako správce: sudo ss -tulpn' => 'You can only see which processes hold ports as an admin: sudo ss -tulpn',
    'netstat je starší nástroj z balíčku net-tools, dnes se používá ss -tulpn.' => 'netstat is an older tool from the net-tools package; today ss -tulpn is used instead.',
    'Služba čeká na tvůj vstup. Pošli ho rourou: echo "text" | nc {hostitel} {port}' => 'The service is waiting for your input. Send it via a pipe: echo "text" | nc {hostitel} {port}',
    'Spojení je otevřené, ale služba nic neposlala. Zkus jí něco poslat rourou: echo "ahoj" | nc {hostitel} {port}' => "The connection is open, but the service sent nothing. Try sending it something via a pipe: echo \"hi\" | nc {hostitel} {port}",
    // linux_v57_cmd_shell.php
    'Nemyslel(a) jsi man {navrh}?' => 'Did you mean man {navrh}?',
    'Napiš sudo před konkrétní příkaz, např. sudo systemctl restart nginx. Tak je vždy vidět, co děláš jako správce.' => 'Put sudo before a specific command, e.g. sudo systemctl restart nginx. This way it is always clear what you are doing as an admin.',
    'V této úrovni nejsi správce počítače, takže sudo nesmíš použít. Úkol jde vyřešit bez něj.' => "You are not the computer's admin in this level, so you may not use sudo. The task can be solved without it.",
    'Napiš sudo před konkrétní příkaz, např. sudo nano /etc/hosts.' => 'Put sudo before a specific command, e.g. sudo nano /etc/hosts.',
    'V laboratoři terminál zůstává otevřený – můžeš psát dál.' => 'In the lab the terminal stays open – you can keep typing.',
    'Nový interaktivní shell simulace nespouští – už v jednom jsi. Skript spustíš: bash soubor.sh' => "The simulator doesn't launch a new interactive shell – you're already in one. Run a script with: bash file.sh",
    // linux_v57_cmd_sys.php
    'Nejčastěji se používá ps aux (všechny procesy) nebo ps -ef.' => 'The most common options are ps aux (all processes) or ps -ef.',
    'top v simulaci ukáže jeden snímek. Ve skutečném terminálu se obnovuje a ukončíš ho klávesou q.' => 'In the simulator, top shows a single snapshot. In a real terminal it refreshes and you quit with the q key.',
    'Cizí proces (jiného uživatele) může ukončit jen správce: sudo kill {pid}' => "Only an admin can end another user's process: sudo kill {pid}",
    'Proces s tímto PID neexistuje. Aktuální čísla procesů ukáže ps aux.' => "There's no process with this PID. Current process numbers are shown by ps aux.",
    'Seznam služeb ukáže systemctl list-units --type=service.' => 'The list of services is shown by systemctl list-units --type=service.',
    'Službu spouští a zastavuje správce systému: sudo systemctl {akce} {jednotka}' => 'Only a system admin starts and stops services: sudo systemctl {akce} {jednotka}',
    'Služba nenaběhla. Důvod najdeš v logu: journalctl -u {jednotka} -n 20{extra}' => "The service failed to start. You'll find the reason in the log: journalctl -u {jednotka} -n 20{extra}",
    ' – nebo zkontroluj konfiguraci: sudo nginx -t' => ' – or check the configuration: sudo nginx -t',
    'journalctl -f by čekal na nové záznamy; simulace vypíše poslední a skončí.' => 'journalctl -f would wait for new entries; the simulator prints the latest ones and exits.',
    'nginx ukazuje soubor a číslo řádku s chybou. Otevři ho (sudo nano …) a oprav přesně ten řádek.' => 'nginx shows the file and line number with the error. Open it (sudo nano …) and fix exactly that line.',
    'Webový server běží jako služba. Ovládej ho přes sudo systemctl start|stop|restart nginx.' => 'The web server runs as a service. Control it with sudo systemctl start|stop|restart nginx.',
    'Instalaci softwaru dělá správce systému: sudo apt {argumenty}' => 'Only a system admin installs software: sudo apt {argumenty}',
    'Balíčky se stahují z internetu. Nejdřív oprav připojení (ping, ip route, /etc/resolv.conf).' => 'Packages are downloaded from the internet. Fix the connection first (ping, ip route, /etc/resolv.conf).',
    'Balíček s tímto jménem neexistuje. Hledat můžeš: apt search {balicek}' => 'There is no package with that name. You can search with: apt search {balicek}',
    // linux_v57_cmd_text.php
    'tr čte jen ze vstupu (roury). Použij třeba: cat soubor | tr a-z A-Z' => 'tr reads only from input (a pipe). Try something like: cat file | tr a-z A-Z',
    'Simulace awk zná pole ($1, $NF), NR, NF, vzory /text/ a porovnání, print/printf, proměnné a bloky BEGIN/END. Pole a cykly zatím ne.' => 'The awk simulator understands fields ($1, $NF), NR, NF, /text/ patterns and comparisons, print/printf, variables and BEGIN/END blocks. Arrays and loops are not supported yet.',
];
