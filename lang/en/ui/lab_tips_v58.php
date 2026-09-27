<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „lab_tips_v58“ (angličtina).
 *
 * Nápovědy simulátoru Linuxu ($w->tip(...)) pro nové příkazy v58 (archivy, cron, extra nástroje,
 * ImageMagick/exiftool/rename, git, ssh/scp, uživatelé a práva). Výstup simulovaných programů
 * (stdout/stderr) zůstává anglicky beze změny – tento katalog překládá jen text nápovědy „💡“.
 * Příkazy, volby, cesty a jména proměnných zůstávají v {param} beze změny.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // linux_v58_cmd_git.php
    'Tady není žádný repozitář gitu. Přejdi do složky projektu (např. cd ~/web – podívej se ls), nebo založ nový: git init.' => 'There is no git repository here. Go to your project folder (e.g. cd ~/web – check with ls), or start a new one: git init.',
    'V laboratoři není síť ani vzdálený server. Historii zkoumej lokálně: git log, git show, git diff.' => 'The lab has no network or remote server. Explore the history locally: git log, git show, git diff.',
    'Simulace gitu umí: {seznam}.' => 'The git simulation supports: {seznam}.',
    'Překlep? Zkus git {similar}.' => 'Typo? Try git {similar}.',
    'Seznam podpříkazů vypíše git help, podrobnosti man git.' => 'git help lists the subcommands, man git gives the details.',
    'Git potřebuje vědět, kdo změnu dělá: git config --global user.name "Tvé Jméno" a git config --global user.email tvuj@email.cz' => 'Git needs to know who is making the change: git config --global user.name "Your Name" and git config --global user.email your@email.com',
    'Tuhle volbu git {sub} nezná (nebo ji simulace nepodporuje). Přehled: git {sub} -h nebo man git.' => 'git {sub} does not know this option (or the simulation does not support it). Overview: git {sub} -h or man git.',
    'Git nezná revizi ani soubor „{arg}“. Commity ukáže git log --oneline, větve git branch -a, značky git tag. Smazaný soubor zadej za --: git log -- {arg}' => 'Git does not know a revision or file called "{arg}". git log --oneline shows commits, git branch -a shows branches, git tag shows tags. For a deleted file, put it after --: git log -- {arg}',
    'Klíč má tvar sekce.jméno, např. user.name nebo user.email.' => 'A key looks like section.name, e.g. user.name or user.email.',
    'Skutečný git by teď otevřel editor pro zprávu commitu. V simulaci ji napiš rovnou: git commit -m "Co jsem změnil(a)"' => 'Real git would now open an editor for the commit message. In the simulation, type it directly: git commit -m "What I changed"',

    // linux_v58_cmd_git_more.php
    'Neuložené změny by přepnutí přepsalo. Ulož je commitem (git commit -am "…"), odlož (git stash), nebo zahoď (git restore <soubor>) – a přepni znovu.' => 'Switching would overwrite your unsaved changes. Save them with a commit (git commit -am "…"), stash them (git stash), or discard them (git restore filename) – then switch again.',
    'Větev „{name}“ neexistuje. Seznam větví: git branch -a; novou založíš git switch -c {name}.' => 'The branch "{name}" does not exist. git branch -a lists the branches; create a new one with git switch -c {name}.',
    'Skutečný git by otevřel editor pro popis značky. Zadej ho rovnou: git tag -a {name} -m "Popis vydání"' => 'Real git would open an editor for the tag message. Type it directly: git tag -a {name} -m "Release notes"',
    'Nejdřív ulož nebo zahoď rozdělané změny (git status), pak zkus git stash {sub} znovu.' => 'First save or discard your unfinished changes (git status), then try git stash {sub} again.',
    'Soubor se změnil v commitu i ve stashi. Simulace konflikt neslučuje – obsah stashe ukáže git stash show -p.' => 'The file changed both in the commit and in the stash. The simulation does not merge conflicts – git stash show -p shows the stash contents.',
    'V laboratoři není internet. Klonuj z místního repozitáře ve složce, např.: git clone /srv/git/web.git' => 'The lab has no internet. Clone from a local repository folder instead, e.g.: git clone /srv/git/web.git',
    'Na cestě „{url}“ repozitář není. Zkontroluj ji (ls /srv/git).' => 'There is no repository at the path "{url}". Check it (ls /srv/git).',

    // linux_v58_cmd_cron.php
    'crontab -e normálně otevře plán v $EDITOR a po uložení ho zkontroluje. V laboratoři uprav plán a ulož ho; nebo naplánuj z připraveného souboru: crontab muj-plan.txt.' => 'crontab -e normally opens the schedule in $EDITOR and checks it after saving. In the lab, edit the schedule and save it; or install one from a ready-made file: crontab muj-plan.txt.',
    'Chyba v plánu: {chyba}. Formát řádku: „minuta hodina den měsíc den_v_týdnu  příkaz".' => 'Error in the schedule: {chyba}. Line format: "minute hour day month day_of_week  command".',
    'crontab -l vypíše plán, crontab -e ho upraví, crontab soubor.txt ho nastaví z připraveného souboru.' => 'crontab -l lists the schedule, crontab -e edits it, crontab soubor.txt sets it from a ready-made file.',
    'Použij timewarp +30m, +2h, +1d nebo přesný čas timewarp 06:25 – posune simulované hodiny a spustí naplánované úlohy cronu.' => 'Use timewarp +30m, +2h, +1d, or an exact time timewarp 06:25 – it moves the simulated clock forward and runs any scheduled cron jobs.',
    'Zadej posun jako +30m, +2h, +90s, +1d, nebo cílový čas HH:MM (např. 06:25).' => 'Enter a shift like +30m, +2h, +90s, +1d, or a target time HH:MM (e.g. 06:25).',

    // linux_v58_cmd_extra_sys.php
    'whereis zná -b (jen programy), -m (jen manuálové stránky). → man whereis' => 'whereis knows -b (programs only), -m (manual pages only). → man whereis',
    'pgrep najde čísla procesů podle jména: pgrep ssh, s názvy pgrep -l ssh. → man pgrep' => 'pgrep finds process IDs by name: pgrep ssh, with names pgrep -l ssh. → man pgrep',
    'Napiš, co hledáš: pgrep nginx (čísla procesů), pgrep -a nginx (i s příkazem). → man pgrep' => 'Type what you are looking for: pgrep nginx (process IDs), pgrep -a nginx (with the command too). → man pgrep',
    'Připojování disků smí jen správce (sudo mount …). Seznam připojených svazků vypíše samotné mount nebo df -h.' => 'Only an administrator can mount disks (sudo mount …). Plain mount or df -h lists the mounted volumes.',
    'V laboratoři není žádný další disk ani flash disk k připojení – co existuje, ukáže lsblk.' => 'The lab has no other disk or flash drive to mount – lsblk shows what exists.',
    'watch -n 5 příkaz spouští příkaz každých 5 s. → man watch' => 'watch -n 5 command runs the command every 5 s. → man watch',
    'watch v simulaci proběhne jen jednou. Skutečný watch by příkaz spouštěl každých {sec} s a obnovoval obrazovku, dokud ho neukončíš Ctrl+C.' => 'In the simulation, watch runs only once. Real watch would run the command every {sec} s and refresh the screen until you stop it with Ctrl+C.',
    'Mezi čísly a operátory musí být mezery (expr 3 + 4). Hvězdičku a závorky chraň před shellem: expr 3 \\* 4, expr \\( 1 + 2 \\) \\* 3. → man expr' => 'There must be spaces between numbers and operators (expr 3 + 4). Protect the asterisk and brackets from the shell: expr 3 \\* 4, expr \\( 1 + 2 \\) \\* 3. → man expr',
    'expr počítá jen s celými čísly; na desetinná čísla použij bc. → man expr' => 'expr only works with whole numbers; for decimals use bc. → man expr',

    // linux_v58_cmd_extra.php
    'Příkaz dostal víc argumentů, než umí. Správné použití ukáže man {name}.' => 'The command got more arguments than it can handle. man {name} shows the correct usage.',
    '„{path}“ není symbolický odkaz (v laboratoři odkazy nejsou). Celou cestu vypíše readlink -f {path} nebo realpath {path}.' => '"{path}" is not a symbolic link (the lab has no links). readlink -f {path} or realpath {path} prints the full path.',
    'seq čeká čísla: seq 5, seq 2 10, seq 0 5 100 (od, krok, do). → man seq' => 'seq expects numbers: seq 5, seq 2 10, seq 0 5 100 (from, step, to). → man seq',
    'Simulace vypíše nejvýš {max} čísel.' => 'The simulation prints at most {max} numbers.',
    'Simulace zamíchá nejvýš {max} čísel.' => 'The simulation shuffles at most {max} numbers.',
    'shuf -r by bez -n psal donekonečna – simulace skončí po 1000 řádcích.' => 'shuf -r without -n would print forever – the simulation stops after 1000 lines.',
    '{name} porovnává dva seřazené soubory: {name} a.txt b.txt (seřadíš je příkazem sort). → man {name}' => '{name} compares two sorted files: {name} a.txt b.txt (sort them with the sort command). → man {name}',
    'comm potřebuje seřazené vstupy. Seřaď je: sort a.txt > a2.txt (nebo comm <(sort a) <(sort b) v bashi). → man comm' => 'comm needs sorted inputs. Sort them: sort a.txt > a2.txt (or comm <(sort a) <(sort b) in bash). → man comm',
    'join potřebuje oba soubory seřazené podle spojovacího pole (sort -k1). → man join' => 'join needs both files sorted by the join field (sort -k1). → man join',
    'Výstupních souborů by bylo moc (simulace jich vytvoří nejvýš {max}). Zvětši kusy: split -l 500 nebo -b 1M. → man split' => 'There would be too many output files (the simulation creates at most {max}). Make the chunks bigger: split -l 500 or -b 1M. → man split',
    'yes by psal donekonečna (do Ctrl+C) – simulace skončila po {n} řádcích. Typicky se posílá rourou: yes | příkaz.' => 'yes would print forever (until Ctrl+C) – the simulation stopped after {n} lines. It is typically piped: yes | command.',

    // linux_v58_cmd_extra_calc.php
    'bc zná hlavně -l (matematická knihovna, 20 desetinných míst). → man bc' => 'bc mainly knows -l (the maths library, 20 decimal places). → man bc',
    "bc v simulaci nečeká na klávesnici – pošli mu výraz rourou: echo '2+3' | bc, desetinná místa: echo 'scale=2; 10/3' | bc → man bc" => "In the simulation, bc does not wait for the keyboard – pipe it an expression: echo '2+3' | bc, decimal places: echo 'scale=2; 10/3' | bc → man bc",
    'bc zná čísla, + - * / % ^, proměnné (malými písmeny), scale=, sqrt(). Velká písmena jsou jen číslice A–F. → man bc' => 'bc knows numbers, + - * / % ^, variables (lowercase letters), scale=, sqrt(). Capital letters are only digits A–F. → man bc',
    "Zkontroluj výraz – např. chybějící číslo za operátorem nebo závorku. Příklad: echo 'scale=3; 22/7' | bc → man bc" => "Check the expression – e.g. a missing number after an operator, or a bracket. Example: echo 'scale=3; 22/7' | bc → man bc",

    // linux_v58_cmd_media_im.php
    '„{name}“ není obrázek (nebo je poškozený). Co v souboru doopravdy je, ukáže file {name}. → man identify' => '"{name}" is not an image (or it is corrupted). file {name} shows what the file actually is. → man identify',
    'identify zná hlavně -verbose a -format "%w x %h". → man identify' => 'identify mainly knows -verbose and -format "%w x %h". → man identify',
    'Tuhle volbu simulace ImageMagicku nezná (nebo má překlep). Časté volby: -resize, -quality, -strip, -crop, -rotate, -thumbnail. → man {tool}' => 'The ImageMagick simulation does not know this option (or it has a typo). Common options: -resize, -quality, -strip, -crop, -rotate, -thumbnail. → man {tool}',
    'Volba {raw} potřebuje hodnotu, např. {example}. → man {tool}' => 'The option {raw} needs a value, e.g. {example}. → man {tool}',
    'Geometrie se píše např. 50%, 800x600, 800x nebo x600.' => 'Geometry is written like 50%, 800x600, 800x, or x600.',
    'Okraj se píše v pixelech, např. {name} 10 nebo 10x5.' => 'The border is given in pixels, e.g. {name} 10 or 10x5.',
    'Úhel se píše ve stupních, např. -rotate 90.' => 'The angle is given in degrees, e.g. -rotate 90.',
    'Barevný prostor je např. Gray nebo sRGB.' => 'The colour space is e.g. Gray or sRGB.',
    'Typ obrázku je např. Grayscale, TrueColor nebo Palette.' => 'The image type is e.g. Grayscale, TrueColor, or Palette.',
    'Počet barev je celé číslo, např. -colors 16.' => 'The number of colours is a whole number, e.g. -colors 16.',
    'Bitová hloubka je obvykle 8 nebo 16.' => 'The bit depth is usually 8 or 16.',
    'Hodnota pro -alpha je např. off, remove nebo on.' => 'The value for -alpha is e.g. off, remove, or on.',
    'Barva se píše např. white, #ff8800 nebo "rgb(255,136,0)".' => 'A colour is written e.g. as white, #ff8800, or "rgb(255,136,0)".',
    '{tip} → man {tool}' => '{tip} → man {tool}',
    'Kvalita je číslo 1–100, např. -quality 80.' => 'Quality is a number from 1–100, e.g. -quality 80.',
    'Hustota se píše v DPI, např. -density 300.' => 'Density is given in DPI, e.g. -density 300.',
    'Velikost plátna se píše ŠÍŘKAxVÝŠKA, např. -size 800x600.' => 'The canvas size is written WIDTHxHEIGHT, e.g. -size 800x600.',
    'Jednotky jsou PixelsPerInch nebo PixelsPerCentimeter.' => 'The units are PixelsPerInch or PixelsPerCentimeter.',
    '-trim ořezává okraje podle barvy pixelů – simulace pixely nemá, rozměry proto zůstanou stejné.' => '-trim crops edges based on pixel colour – the simulation has no pixels, so the dimensions stay the same.',
    'Výřez se píše ŠÍŘKAxVÝŠKA+X+Y, např. -crop 800x600+100+50.' => 'A crop is written WIDTHxHEIGHT+X+Y, e.g. -crop 800x600+100+50.',
    'Bez +X+Y dělí -crop obrázek na dlaždice – tady by jich bylo moc. Nezapomněl(a) jsi posun, např. -crop 800x600+0+0?' => 'Without +X+Y, -crop splits the image into tiles – there would be too many here. Did you forget the offset, e.g. -crop 800x600+0+0?',
    'Debian ImageMagicku zápis PDF/PS z bezpečnostních důvodů zakazuje (/etc/ImageMagick-6/policy.xml). Na tisk ulož PNG nebo JPEG.' => 'Debian forbids ImageMagick from writing PDF/PS for security reasons (/etc/ImageMagick-6/policy.xml). For printing, save PNG or JPEG instead.',
    'Z rastru (pixelů) se vektor jen tak neudělá – vektorizaci dělá např. Inkscape (Trace Bitmap).' => 'You cannot just turn a raster (pixels) into a vector – vectorising is done e.g. by Inkscape (Trace Bitmap).',
    'Tenhle formát simulace neumí zapsat. Zkus .jpg, .png, .webp, .gif, .ico, .bmp nebo .tif.' => 'The simulation cannot write this format. Try .jpg, .png, .webp, .gif, .ico, .bmp, or .tif.',
    'Ikona smí mít nejvýš 256×256 px. Nejdřív ji zmenši: -resize 32x32' => 'An icon can be at most 256×256 px. Shrink it first: -resize 32x32',
    'Složku pro -path musíš nejdřív vytvořit: mkdir -p {path}' => 'You must create the folder for -path first: mkdir -p {path}',
    'Na konci příkazu musí být jméno výstupního souboru: convert vstup.jpg -resize 50% vystup.jpg → man convert' => 'The end of the command must have the output file name: convert vstup.jpg -resize 50% vystup.jpg → man convert',
    'convert potřebuje vstupní i výstupní soubor: convert vstup.png vystup.jpg → man convert' => 'convert needs both an input and an output file: convert vstup.png vystup.jpg → man convert',
    'mogrify potřebuje jména souborů, např. mogrify -resize 50% *.jpg → man mogrify' => 'mogrify needs file names, e.g. mogrify -resize 50% *.jpg → man mogrify',
    'Simulace zpracuje najednou nejvýš {max} souborů.' => 'The simulation processes at most {max} files at once.',
    'Příkaz magick {sub} simulace nemá. Umí: magick vstup [volby] výstup, magick identify, magick mogrify. → man magick' => 'The simulation has no magick {sub} command. It supports: magick input [options] output, magick identify, magick mogrify. → man magick',

    // linux_v58_cmd_media_tools.php
    'exiftool nechal zálohu původního souboru s příponou _original – i v ní jsou stará metadata. Bez zálohy: -overwrite_original.' => 'exiftool left a backup of the original file with the _original suffix – it still has the old metadata too. Without a backup: -overwrite_original.',
    'exiftool vypíše metadata: exiftool foto.jpg. Polohu GPS smažeš: exiftool -gps:all= foto.jpg → man exiftool' => 'exiftool prints the metadata: exiftool foto.jpg. Delete the GPS location: exiftool -gps:all= foto.jpg → man exiftool',
    'Značka se píše např. -Make, -GPSLatitude nebo -gps:all; zápis -Artist="Jméno". → man exiftool' => 'A tag is written e.g. -Make, -GPSLatitude, or -gps:all; to write a value use -Artist="Name". → man exiftool',
    'Na konec přidej soubor nebo složku, např. exiftool -gps:all fotky/ → man exiftool' => 'Add a file or folder at the end, e.g. exiftool -gps:all fotky/ → man exiftool',
    'rename zná hlavně -n (jen ukázat, co by udělal), -v (vypsat) a -f (přepsat existující). → man rename' => 'rename mainly knows -n (only show what it would do), -v (print) and -f (overwrite existing). → man rename',
    "Použití: rename 's/staré/nové/' soubory – nejdřív zkus s -n. → man rename" => "Usage: rename 's/old/new/' files – try it with -n first. → man rename",
    "Výraz pro rename se píše jako v Perlu: 's/co/čím/' (g = všechny výskyty, i = bez ohledu na velikost). Dej ho do apostrofů. → man rename" => "The expression for rename is written like in Perl: 's/what/with/' (g = all occurrences, i = ignore case). Put it in quotes. → man rename",
    'Regulární výraz má chybu (třeba neuzavřenou závorku). → man rename' => 'The regular expression has an error (perhaps an unclosed bracket). → man rename',

    // linux_v58_cmd_archive.php
    'tar používá kombinaci voleb: -c vytvoř, -x rozbal, -t vypiš, -z gzip, -j bzip2, -v ukaž, -f soubor.' => 'tar uses a combination of options: -c create, -x extract, -t list, -z gzip, -j bzip2, -v show, -f file.',
    'Zadej jméno archivu volbou -f, např. tar -czf zaloha.tar.gz slozka.' => 'Give the archive name with -f, e.g. tar -czf zaloha.tar.gz slozka.',
    'Soubor nevypadá jako tar. Zjisti formát příkazem file {file} a použij odpovídající nástroj.' => 'The file does not look like a tar. Find out its format with file {file} and use the matching tool.',
    '{name} potřebuje jméno souboru, např. {example}.' => '{name} needs a file name, e.g. {example}.',
    'Soubor není ve formátu gzip. Ověř ho příkazem file {file}.' => 'The file is not in gzip format. Check it with file {file}.',
    'Soubor není ve formátu bzip2. Ověř ho příkazem file {file}.' => 'The file is not in bzip2 format. Check it with file {file}.',
    'Použití: zip archiv.zip soubor… nebo zip -r archiv.zip slozka' => 'Usage: zip archiv.zip soubor… or zip -r archiv.zip slozka',
    'Soubor není ve formátu ZIP. Ověř ho příkazem file {archive}.' => 'The file is not in ZIP format. Check it with file {archive}.',
    'Terminál je neinteraktivní – přepis povol volbou -o: unzip -o {archive}.' => 'The terminal is non-interactive – allow overwriting with -o: unzip -o {archive}.',

    // linux_v58_cmd_ssh.php
    'Podporované typy klíčů: ed25519 (doporučeno), rsa, ecdsa. Např. ssh-keygen -t ed25519.' => 'Supported key types: ed25519 (recommended), rsa, ecdsa. E.g. ssh-keygen -t ed25519.',
    'Klíč už existuje. Zvol jiný název volbou -f, nebo starý nejdřív smaž (rm {file} {file}.pub).' => 'The key already exists. Pick a different name with -f, or delete the old one first (rm {file} {file}.pub).',
    'Přístupová fráze (-N) chrání soukromý klíč, kdyby ho někdo získal. V laboratoři ji nepotřebuješ – klíč je jen fiktivní.' => 'A passphrase (-N) protects the private key if someone gets hold of it. You do not need one in the lab – the key is only fictional.',
    'Na tenhle počítač se v laboratoři nedá přihlásit (nemá cvičný účet). Použij hosta ze zadání úlohy.' => 'You cannot log in to this computer in the lab (it has no practice account). Use the host from the task assignment.',
    "Terminál je neinteraktivní. Spusť příkaz rovnou: ssh {user}@{host} 'ls -la'  (nebo 'cat kod.txt')." => "The terminal is non-interactive. Run the command directly: ssh {user}@{host} 'ls -la'  (or 'cat kod.txt').",
    'Jméno „{host}“ nešlo přeložit na IP. Zkontroluj překlep nebo /etc/hosts.' => 'The name "{host}" could not be resolved to an IP. Check for a typo or /etc/hosts.',
    'Na portu {port} nikdo neposlouchá. Běží na cíli SSH server? Zkus nc -zv {host} {port}.' => 'Nobody is listening on port {port}. Is an SSH server running on the target? Try nc -zv {host} {port}.',
    'Spojení vypršelo. Ověř dostupnost: ping {host} a traceroute {host}.' => 'The connection timed out. Check reachability: ping {host} and traceroute {host}.',
    'Otisk serveru se změnil oproti tomu v known_hosts. V laboratoři to znamená, že úroveň server přeinstalovala. Když je to očekávané, starý záznam smaž: ssh-keygen -R {host} a přihlas se znovu.' => 'The server fingerprint has changed from the one in known_hosts. In the lab this means the level reinstalled the server. If this is expected, delete the old entry: ssh-keygen -R {host} and log in again.',
    'Soukromý klíč smí číst jen ty. Oprav práva: chmod 600 {path} (a chmod 700 {dir}).' => 'Only you may read the private key. Fix the permissions: chmod 600 {path} (and chmod 700 {dir}).',
    'Nemáš klíč, kterým by tě server přijal. Vygeneruj si ho (ssh-keygen -t ed25519) a nahraj na server (ssh-copy-id {user}@{host}).' => 'You do not have a key the server would accept. Generate one (ssh-keygen -t ed25519) and upload it to the server (ssh-copy-id {user}@{host}).',
    'Server tvůj klíč nezná a heslo v neinteraktivním terminálu zadat nejde. Nahraj svůj veřejný klíč: ssh-copy-id {user}@{host}.' => 'The server does not know your key, and a password cannot be entered in a non-interactive terminal. Upload your public key: ssh-copy-id {user}@{host}.',
    'Server přijímá jen klíče. Ověř, že tvůj veřejný klíč je v ~/.ssh/authorized_keys na serveru (ssh-copy-id {user}@{host}).' => 'The server only accepts keys. Check that your public key is in ~/.ssh/authorized_keys on the server (ssh-copy-id {user}@{host}).',
    'Na lokální kopírování použij cp. scp přenáší mezi počítači: scp soubor user@host:~/' => 'Use cp for local copying. scp transfers between computers: scp soubor user@host:~/',
    'Nemáš veřejný klíč. Nejdřív si ho vygeneruj: ssh-keygen -t ed25519.' => 'You do not have a public key. Generate one first: ssh-keygen -t ed25519.',
    'ssh-copy-id nahrává klíč po přihlášení heslem. Tenhle server ho v laboratoři nepřijímá – klíč přenes přes scp do ~/.ssh/authorized_keys.' => 'ssh-copy-id uploads the key after logging in with a password. This server does not accept that in the lab – transfer the key via scp into ~/.ssh/authorized_keys.',
    'Ve skutečnosti se ssh-copy-id jednou zeptá na heslo účtu. V laboratoři ho za tebe potvrdí, aby ses mohl(a) hned přihlásit klíčem.' => 'In reality, ssh-copy-id would ask for the account password once. In the lab it is confirmed for you, so you can log in with the key straight away.',

    // linux_v58_cmd_users.php
    'Účty a skupiny spravuje jen správce systému. Zkus příkaz zopakovat se sudo, např. sudo {name} …' => 'Only the system administrator manages accounts and groups. Try repeating the command with sudo, e.g. sudo {name} …',
    'Do skupiny přidáš člena: sudo gpasswd -a uzivatel skupina; odebereš: sudo gpasswd -d uzivatel skupina.' => 'To add a member to a group: sudo gpasswd -a uzivatel skupina; to remove one: sudo gpasswd -d uzivatel skupina.',
    'Heslo v laboratoři nastavíš neinteraktivně přes stdin: echo "{target}:NoveHeslo123" | sudo chpasswd. Skutečné heslo se nikde neukládá – jen příznak, že je nastavené.' => 'In the lab, you set a password non-interactively via stdin: echo "{target}:NoveHeslo123" | sudo chpasswd. No real password is stored anywhere – just a flag that one is set.',
    'chpasswd čte dvojice uzivatel:heslo ze standardního vstupu: echo "student:Tajne123" | sudo chpasswd.' => 'chpasswd reads user:password pairs from standard input: echo "student:Tajne123" | sudo chpasswd.',
    'Údaje o hesle jiného uživatele smí číst jen správce: sudo chage -l {user}.' => 'Only an administrator may read the password details of another user: sudo chage -l {user}.',
    'V laboratoři se na jiného uživatele nepřepneš heslem – terminál je neinteraktivní. Jako správce spusť konkrétní příkaz přes sudo a svá oprávnění si ověř příkazem sudo -l.' => 'In the lab you cannot switch to another user with a password – the terminal is non-interactive. As an administrator, run a specific command via sudo and check your permissions with sudo -l.',
    'getent umí v laboratoři databáze passwd a group, např. getent passwd student.' => 'In the lab, getent knows the passwd and group databases, e.g. getent passwd student.',
    'visudo -c ověří syntaxi souborů sudoers, aniž bys je otevíral(a).' => 'visudo -c checks the syntax of the sudoers files without you having to open them.',
    'Masku zadej osmičkově, např. umask 022 (nové soubory 644) nebo umask 077 (soukromé soubory).' => 'Enter the mask in octal, e.g. umask 022 (new files 644) or umask 077 (private files).',
];
