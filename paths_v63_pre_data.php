<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · předpočítané výstupy kroků predict–run–explain (GENEROVANÝ soubor – needitovat).
 * Znovu vytvoří: php tools/v63_paths_build_pre.php --apply. Výstupy vznikly v simulátoru Linuxu (čisté PHP) s pevným semínkem a časem;
 * za běhu aplikace se nic nespouští. Soubor je datový (výjimka z limitu 800 řádků v BUILD_MANIFEST_V63.md).
 */

return array (
  'seed' => 'p63-fixed',
  'now' => 1790000000,
  'outputs' => 
  array (
    'lnx_chmod|predict|c1' => 
    array (
      'cmd' => 'stat -c \'%A %n\' report.txt',
      'out' => '-rw------- report.txt',
    ),
    'lnx_chmod|predict|c2' => 
    array (
      'cmd' => 'stat -c \'%A %n\' tajne.txt',
      'out' => '-rw-r----- tajne.txt',
    ),
    'lnx_chmod|predict|c3' => 
    array (
      'cmd' => 'chmod 755 skript.sh && stat -c \'%A %n\' skript.sh',
      'out' => '-rwxr-xr-x skript.sh',
    ),
    'lnx_services|predict|c1' => 
    array (
      'cmd' => 'systemctl is-active cron',
      'out' => 'active',
    ),
    'lnx_services|predict|c2' => 
    array (
      'cmd' => 'systemctl is-enabled ssh',
      'out' => 'enabled',
    ),
    'lnx_services|predict|c3' => 
    array (
      'cmd' => 'pgrep cron',
      'out' => '317',
    ),
    'net_dns|predict|c1' => 
    array (
      'cmd' => 'cat /etc/hosts',
      'out' => '127.0.0.1	localhost
127.0.1.1	lab-pc

# Školní síť
10.0.0.10	intranet.skola.test intranet

::1	localhost ip6-localhost ip6-loopback',
    ),
    'net_dns|predict|c2' => 
    array (
      'cmd' => 'cat /etc/resolv.conf',
      'out' => '# Vygeneroval DHCP klient
nameserver 1.1.1.1
nameserver 8.8.8.8
search skola.test',
    ),
    'net_dns|predict|c3' => 
    array (
      'cmd' => 'host intranet.skola.test',
      'out' => 'intranet.skola.test has address 10.0.0.10',
    ),
    'net_ports|predict|c1' => 
    array (
      'cmd' => 'ss -tln',
      'out' => 'Netid State  Recv-Q Send-Q Local Address:Port  Peer Address:Port Process
tcp   LISTEN 0      511            0.0.0.0:22         0.0.0.0:* 
tcp   LISTEN 0      511            0.0.0.0:80         0.0.0.0:* ',
    ),
    'net_ports|predict|c2' => 
    array (
      'cmd' => 'nc -zv 10.0.0.10 443',
      'out' => 'nc: connect to 10.0.0.10 (10.0.0.10) port 443 (tcp) failed: Connection refused',
    ),
    'net_ports|predict|c3' => 
    array (
      'cmd' => 'nc -zv 10.0.0.10 22',
      'out' => 'Connection to 10.0.0.10 (10.0.0.10) 22 port [tcp/ssh] succeeded!',
    ),
    'net_security|predict|c1' => 
    array (
      'cmd' => 'sudo grep PermitRootLogin /etc/ssh/sshd_config',
      'out' => 'PermitRootLogin no',
    ),
    'net_security|predict|c2' => 
    array (
      'cmd' => 'nc -zv 10.0.0.99 80',
      'out' => 'nc: connect to 10.0.0.99 (10.0.0.99) port 80 (tcp) failed: Connection timed out',
    ),
    'net_security|predict|c3' => 
    array (
      'cmd' => 'ss -tln',
      'out' => 'Netid State  Recv-Q Send-Q Local Address:Port  Peer Address:Port Process
tcp   LISTEN 0      511            0.0.0.0:22         0.0.0.0:* 
tcp   LISTEN 0      511            0.0.0.0:80         0.0.0.0:* ',
    ),
  ),
);
