<?php
declare(strict_types=1);
if (basename((string)($_SERVER["SCRIPT_FILENAME"] ?? "")) === basename(__FILE__)) { http_response_code(403); exit; }
return array (
  'branches' => 
  array (
    'networking' => 
    array (
      'slug' => 'networking',
      'icon' => '🌐',
      'name' => 'Networking',
      'description' => 'Sítě, protokoly, konfigurace a diagnostika.',
    ),
    'linux' => 
    array (
      'slug' => 'linux',
      'icon' => '🐧',
      'name' => 'Linux',
      'description' => 'CLI, služby, administrace a automatizace.',
    ),
    'security' => 
    array (
      'slug' => 'security',
      'icon' => '🛡',
      'name' => 'Security',
      'description' => 'Bezpečnost, hardening, monitoring a incident response.',
    ),
    'design' => 
    array (
      'slug' => 'design',
      'icon' => '🎨',
      'name' => 'Design',
      'description' => 'Vizuální komunikace, kompozice, barva a systémy.',
    ),
    'typography' => 
    array (
      'slug' => 'typography',
      'icon' => 'Aa',
      'name' => 'Typography',
      'description' => 'Typografie, sazba, hierarchie a čitelnost.',
    ),
    'ui-ux' => 
    array (
      'slug' => 'ui-ux',
      'icon' => '◫',
      'name' => 'UI/UX',
      'description' => 'Rozhraní, výzkum, prototypování a design systémy.',
    ),
  ),
  'curricula' => 
  array (
    'class_1a' => 
    array (
      'branches' => 
      array (
        'design' => 0.4,
        'typography' => 0.3,
        'ui-ux' => 0.3,
      ),
      'primary' => 
      array (
        0 => 'design',
        1 => 'typography',
        2 => 'ui-ux',
      ),
    ),
    'class_2a' => 
    array (
      'branches' => 
      array (
        'design' => 0.4,
        'typography' => 0.3,
        'ui-ux' => 0.3,
      ),
      'primary' => 
      array (
        0 => 'design',
        1 => 'typography',
        2 => 'ui-ux',
      ),
    ),
    'class_3a' => 
    array (
      'branches' => 
      array (
        'networking' => 0.4,
        'linux' => 0.35,
        'security' => 0.25,
      ),
      'primary' => 
      array (
        0 => 'networking',
        1 => 'linux',
        2 => 'security',
      ),
    ),
    'class_4a' => 
    array (
      'branches' => 
      array (
        'networking' => 0.35,
        'linux' => 0.35,
        'security' => 0.3,
      ),
      'primary' => 
      array (
        0 => 'networking',
        1 => 'linux',
        2 => 'security',
      ),
    ),
  ),
  'skills' => 
  array (
    'networking' => 
    array (
      0 => 
      array (
        'slug' => 'network-fundamentals',
        'name' => 'Network Fundamentals',
        'description' => 'Základní principy počítačových sítí.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
        ),
        'mastery_node' => false,
      ),
      1 => 
      array (
        'slug' => 'osi-tcpip',
        'name' => 'OSI & TCP/IP',
        'description' => 'Vrstvy, zapouzdření a tok dat.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'network-fundamentals',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      2 => 
      array (
        'slug' => 'network-hardware',
        'name' => 'Network Hardware',
        'description' => 'Switch, router, AP, firewall a NIC.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'network-fundamentals',
            'mastery' => 40,
          ),
        ),
        'mastery_node' => false,
      ),
      3 => 
      array (
        'slug' => 'ports-protocols',
        'name' => 'Ports & Protocols',
        'description' => 'TCP/UDP a běžné aplikační protokoly.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'osi-tcpip',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      4 => 
      array (
        'slug' => 'ipv4',
        'name' => 'IPv4 Addressing',
        'description' => 'Adresace, maska, gateway.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'osi-tcpip',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      5 => 
      array (
        'slug' => 'subnetting',
        'name' => 'Subnetting',
        'description' => 'CIDR, network, broadcast a dělení sítí.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'ipv4',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      6 => 
      array (
        'slug' => 'switching',
        'name' => 'Switching',
        'description' => 'MAC tabulka a ethernet switching.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'network-hardware',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      7 => 
      array (
        'slug' => 'vlans',
        'name' => 'VLANs',
        'description' => 'Access/trunk porty a segmentace.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'switching',
            'mastery' => 60,
          ),
          1 => 
          array (
            'skill' => 'subnetting',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      8 => 
      array (
        'slug' => 'dns-dhcp',
        'name' => 'DNS & DHCP',
        'description' => 'Name resolution, leases a konfigurace.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'ports-protocols',
            'mastery' => 60,
          ),
          1 => 
          array (
            'skill' => 'ipv4',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      9 => 
      array (
        'slug' => 'routing',
        'name' => 'Routing',
        'description' => 'Směrování mezi sítěmi a gateway.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'subnetting',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'vlans',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      10 => 
      array (
        'slug' => 'wireshark',
        'name' => 'Wireshark Analysis',
        'description' => 'Packet capture a interpretace provozu.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'ports-protocols',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      11 => 
      array (
        'slug' => 'network-troubleshooting',
        'name' => 'Network Troubleshooting',
        'description' => 'Systematická diagnostika síťových závad.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'routing',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'dns-dhcp',
            'mastery' => 65,
          ),
          2 => 
          array (
            'skill' => 'wireshark',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      12 => 
      array (
        'slug' => 'network-mastery',
        'name' => 'Network Mastery',
        'description' => 'Komplexní návrh a diagnostika infrastruktury.',
        'tier' => 5,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'network-troubleshooting',
            'mastery' => 80,
          ),
          1 => 
          array (
            'skill' => 'vlans',
            'mastery' => 75,
          ),
        ),
        'mastery_node' => true,
      ),
    ),
    'linux' => 
    array (
      0 => 
      array (
        'slug' => 'linux-fundamentals',
        'name' => 'Linux Fundamentals',
        'description' => 'Distribuce, kernel, shell a základní orientace.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
        ),
        'mastery_node' => false,
      ),
      1 => 
      array (
        'slug' => 'cli-navigation',
        'name' => 'CLI Navigation',
        'description' => 'Práce v terminálu a navigace.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'linux-fundamentals',
            'mastery' => 40,
          ),
        ),
        'mastery_node' => false,
      ),
      2 => 
      array (
        'slug' => 'filesystem',
        'name' => 'Filesystem',
        'description' => 'Hierarchie souborového systému.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'cli-navigation',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      3 => 
      array (
        'slug' => 'files-permissions',
        'name' => 'Files & Permissions',
        'description' => 'Vlastnictví, chmod a bezpečná oprávnění.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'filesystem',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      4 => 
      array (
        'slug' => 'users-groups',
        'name' => 'Users & Groups',
        'description' => 'Účty, skupiny a sudo.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'files-permissions',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      5 => 
      array (
        'slug' => 'processes',
        'name' => 'Processes',
        'description' => 'Procesy, signály a monitoring.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'cli-navigation',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      6 => 
      array (
        'slug' => 'packages',
        'name' => 'Packages',
        'description' => 'Instalace, aktualizace a repozitáře.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'linux-fundamentals',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      7 => 
      array (
        'slug' => 'pipes-shell',
        'name' => 'Pipes & Shell',
        'description' => 'Přesměrování, pipes a textové nástroje.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'cli-navigation',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      8 => 
      array (
        'slug' => 'systemd',
        'name' => 'systemd Services',
        'description' => 'Správa služeb a bootu.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'processes',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'packages',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      9 => 
      array (
        'slug' => 'ssh-linux',
        'name' => 'SSH Administration',
        'description' => 'Bezpečný vzdálený přístup.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'users-groups',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'systemd',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      10 => 
      array (
        'slug' => 'logs-troubleshooting',
        'name' => 'Logs & Troubleshooting',
        'description' => 'journalctl, logy a hledání příčin.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'systemd',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'pipes-shell',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      11 => 
      array (
        'slug' => 'shell-automation',
        'name' => 'Shell Automation',
        'description' => 'Skripty, cron a automatizace.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'pipes-shell',
            'mastery' => 75,
          ),
          1 => 
          array (
            'skill' => 'files-permissions',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      12 => 
      array (
        'slug' => 'linux-mastery',
        'name' => 'Linux Mastery',
        'description' => 'Obnova a správa simulovaného serveru.',
        'tier' => 5,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'logs-troubleshooting',
            'mastery' => 80,
          ),
          1 => 
          array (
            'skill' => 'ssh-linux',
            'mastery' => 75,
          ),
          2 => 
          array (
            'skill' => 'shell-automation',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => true,
      ),
    ),
    'security' => 
    array (
      0 => 
      array (
        'slug' => 'security-fundamentals',
        'name' => 'Security Fundamentals',
        'description' => 'CIA triáda, hrozby a rizika.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
        ),
        'mastery_node' => false,
      ),
      1 => 
      array (
        'slug' => 'authentication',
        'name' => 'Authentication & 2FA',
        'description' => 'Hesla, MFA a identity.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'security-fundamentals',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      2 => 
      array (
        'slug' => 'social-engineering',
        'name' => 'Social Engineering',
        'description' => 'Phishing a bezpečné chování.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'security-fundamentals',
            'mastery' => 40,
          ),
        ),
        'mastery_node' => false,
      ),
      3 => 
      array (
        'slug' => 'access-control',
        'name' => 'Access Control',
        'description' => 'Least privilege a autorizace.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'authentication',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      4 => 
      array (
        'slug' => 'encryption',
        'name' => 'Encryption Fundamentals',
        'description' => 'Hashing, symetrické a asymetrické šifrování.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'security-fundamentals',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      5 => 
      array (
        'slug' => 'backups-patching',
        'name' => 'Backups & Patching',
        'description' => 'Obnova, aktualizace a provozní hygiena.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'security-fundamentals',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      6 => 
      array (
        'slug' => 'firewall-security',
        'name' => 'Firewall Management',
        'description' => 'Pravidla, segmentace a bezpečné služby.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'access-control',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      7 => 
      array (
        'slug' => 'secure-ssh',
        'name' => 'Secure SSH',
        'description' => 'Klíče, omezení přístupu a hardening.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'authentication',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'access-control',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      8 => 
      array (
        'slug' => 'logs-monitoring',
        'name' => 'Logs & Monitoring',
        'description' => 'Audit, logy a indikátory incidentu.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'backups-patching',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      9 => 
      array (
        'slug' => 'web-security',
        'name' => 'Web Security Fundamentals',
        'description' => 'Běžné webové hrozby a obrana.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'encryption',
            'mastery' => 55,
          ),
          1 => 
          array (
            'skill' => 'authentication',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      10 => 
      array (
        'slug' => 'threat-modeling',
        'name' => 'Threat Modeling',
        'description' => 'Aktiva, hrozby, mitigace.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'access-control',
            'mastery' => 75,
          ),
          1 => 
          array (
            'skill' => 'web-security',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      11 => 
      array (
        'slug' => 'incident-response',
        'name' => 'Incident Response',
        'description' => 'Triage, containment a recovery.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'logs-monitoring',
            'mastery' => 75,
          ),
          1 => 
          array (
            'skill' => 'threat-modeling',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      12 => 
      array (
        'slug' => 'security-mastery',
        'name' => 'Security Mastery',
        'description' => 'Komplexní obranná incident challenge.',
        'tier' => 5,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'incident-response',
            'mastery' => 80,
          ),
          1 => 
          array (
            'skill' => 'firewall-security',
            'mastery' => 75,
          ),
          2 => 
          array (
            'skill' => 'secure-ssh',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => true,
      ),
    ),
    'design' => 
    array (
      0 => 
      array (
        'slug' => 'design-principles',
        'name' => 'Design Principles',
        'description' => 'Kontrast, opakování, alignment a proximity.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
        ),
        'mastery_node' => false,
      ),
      1 => 
      array (
        'slug' => 'composition',
        'name' => 'Composition',
        'description' => 'Kompozice, rytmus a rovnováha.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'design-principles',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      2 => 
      array (
        'slug' => 'visual-hierarchy',
        'name' => 'Visual Hierarchy',
        'description' => 'Řízení pozornosti a důležitosti.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'design-principles',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      3 => 
      array (
        'slug' => 'color-theory',
        'name' => 'Color Theory',
        'description' => 'Barva, kontrast a palety.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'design-principles',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      4 => 
      array (
        'slug' => 'grid-systems',
        'name' => 'Grid Systems',
        'description' => 'Mřížky a strukturace layoutu.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'composition',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      5 => 
      array (
        'slug' => 'image-composition',
        'name' => 'Image Composition',
        'description' => 'Práce s obrazem a ořezem.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'composition',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      6 => 
      array (
        'slug' => 'brand-consistency',
        'name' => 'Brand Consistency',
        'description' => 'Vizuální konzistence značky.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'color-theory',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'visual-hierarchy',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      7 => 
      array (
        'slug' => 'poster-design',
        'name' => 'Poster Design',
        'description' => 'Plakát a vizuální sdělení.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'grid-systems',
            'mastery' => 60,
          ),
          1 => 
          array (
            'skill' => 'visual-hierarchy',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      8 => 
      array (
        'slug' => 'digital-graphics',
        'name' => 'Digital Graphics',
        'description' => 'Grafika pro digitální kanály.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'image-composition',
            'mastery' => 60,
          ),
          1 => 
          array (
            'skill' => 'color-theory',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      9 => 
      array (
        'slug' => 'design-critique',
        'name' => 'Design Critique',
        'description' => 'Argumentace a konstruktivní kritika.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'poster-design',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'brand-consistency',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      10 => 
      array (
        'slug' => 'visual-systems',
        'name' => 'Visual Systems',
        'description' => 'Systémy komponent a pravidel.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'brand-consistency',
            'mastery' => 75,
          ),
          1 => 
          array (
            'skill' => 'grid-systems',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      11 => 
      array (
        'slug' => 'art-direction',
        'name' => 'Art Direction',
        'description' => 'Koncept a vedení vizuálního směru.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'design-critique',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'visual-systems',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      12 => 
      array (
        'slug' => 'design-mastery',
        'name' => 'Design Mastery',
        'description' => 'Komplexní brief od konceptu po obhajobu.',
        'tier' => 5,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'art-direction',
            'mastery' => 80,
          ),
          1 => 
          array (
            'skill' => 'visual-systems',
            'mastery' => 80,
          ),
        ),
        'mastery_node' => true,
      ),
    ),
    'typography' => 
    array (
      0 => 
      array (
        'slug' => 'type-fundamentals',
        'name' => 'Typography Fundamentals',
        'description' => 'Typeface, font a základní kategorie.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
        ),
        'mastery_node' => false,
      ),
      1 => 
      array (
        'slug' => 'type-anatomy',
        'name' => 'Type Anatomy',
        'description' => 'Anatomie písma a základní termíny.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'type-fundamentals',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      2 => 
      array (
        'slug' => 'readability',
        'name' => 'Readability',
        'description' => 'Čitelnost a volba písma.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'type-fundamentals',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      3 => 
      array (
        'slug' => 'type-hierarchy',
        'name' => 'Type Hierarchy',
        'description' => 'Hierarchie nadpisů a textu.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'readability',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      4 => 
      array (
        'slug' => 'font-pairing',
        'name' => 'Font Pairing',
        'description' => 'Kombinace rodin a kontrast.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'type-anatomy',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      5 => 
      array (
        'slug' => 'spacing-type',
        'name' => 'Kerning, Tracking & Leading',
        'description' => 'Mikrotypografie a rytmus.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'type-anatomy',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      6 => 
      array (
        'slug' => 'editorial-type',
        'name' => 'Editorial Typography',
        'description' => 'Sazba delšího obsahu.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'type-hierarchy',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'spacing-type',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      7 => 
      array (
        'slug' => 'web-type',
        'name' => 'Web Typography',
        'description' => 'Typografie v responzivním webu.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'readability',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'type-hierarchy',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      8 => 
      array (
        'slug' => 'poster-type',
        'name' => 'Poster Typography',
        'description' => 'Expresivní práce s písmem.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'font-pairing',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'type-hierarchy',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      9 => 
      array (
        'slug' => 'type-grid',
        'name' => 'Typography Grid',
        'description' => 'Mřížka a baseline systém.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'editorial-type',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      10 => 
      array (
        'slug' => 'variable-fonts',
        'name' => 'Variable Fonts',
        'description' => 'Osy, variace a moderní font technologie.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'web-type',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'type-anatomy',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      11 => 
      array (
        'slug' => 'type-accessibility',
        'name' => 'Typography Accessibility',
        'description' => 'Čitelnost a přístupnost.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'web-type',
            'mastery' => 75,
          ),
          1 => 
          array (
            'skill' => 'readability',
            'mastery' => 80,
          ),
        ),
        'mastery_node' => false,
      ),
      12 => 
      array (
        'slug' => 'typography-mastery',
        'name' => 'Typography Mastery',
        'description' => 'Kompletní redesign špatné sazby.',
        'tier' => 5,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'type-grid',
            'mastery' => 75,
          ),
          1 => 
          array (
            'skill' => 'type-accessibility',
            'mastery' => 80,
          ),
          2 => 
          array (
            'skill' => 'poster-type',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => true,
      ),
    ),
    'ui-ux' => 
    array (
      0 => 
      array (
        'slug' => 'ux-fundamentals',
        'name' => 'UI vs UX',
        'description' => 'Role rozhraní, použitelnosti a potřeb.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
        ),
        'mastery_node' => false,
      ),
      1 => 
      array (
        'slug' => 'user-needs',
        'name' => 'User Needs',
        'description' => 'Cíle uživatelů a kontext použití.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'ux-fundamentals',
            'mastery' => 50,
          ),
        ),
        'mastery_node' => false,
      ),
      2 => 
      array (
        'slug' => 'accessibility-ui',
        'name' => 'Accessibility Fundamentals',
        'description' => 'Přístupnost rozhraní.',
        'tier' => 1,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'ux-fundamentals',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      3 => 
      array (
        'slug' => 'wireframes',
        'name' => 'Wireframes',
        'description' => 'Nízkofidelitní návrh rozhraní.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'user-needs',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      4 => 
      array (
        'slug' => 'user-flows',
        'name' => 'User Flows',
        'description' => 'Toky, scénáře a navigace.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'user-needs',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      5 => 
      array (
        'slug' => 'information-architecture',
        'name' => 'Information Architecture',
        'description' => 'Organizace obsahu a struktura.',
        'tier' => 2,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'user-flows',
            'mastery' => 55,
          ),
        ),
        'mastery_node' => false,
      ),
      6 => 
      array (
        'slug' => 'components-ui',
        'name' => 'UI Components',
        'description' => 'Komponenty a konzistence.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'wireframes',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      7 => 
      array (
        'slug' => 'responsive-ui',
        'name' => 'Responsive Layout',
        'description' => 'Adaptivní layout a breakpointy.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'components-ui',
            'mastery' => 60,
          ),
          1 => 
          array (
            'skill' => 'accessibility-ui',
            'mastery' => 60,
          ),
        ),
        'mastery_node' => false,
      ),
      8 => 
      array (
        'slug' => 'forms-states',
        'name' => 'Forms & States',
        'description' => 'Formuláře, chyby, empty/loading stavy.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'user-flows',
            'mastery' => 65,
          ),
          1 => 
          array (
            'skill' => 'components-ui',
            'mastery' => 65,
          ),
        ),
        'mastery_node' => false,
      ),
      9 => 
      array (
        'slug' => 'prototyping',
        'name' => 'Prototyping',
        'description' => 'Interakce a testovatelný prototyp.',
        'tier' => 3,
        'weight' => 1.0,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'wireframes',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'user-flows',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => false,
      ),
      10 => 
      array (
        'slug' => 'usability-testing',
        'name' => 'Usability Testing',
        'description' => 'Testování použitelnosti a evidence.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'prototyping',
            'mastery' => 70,
          ),
          1 => 
          array (
            'skill' => 'user-needs',
            'mastery' => 75,
          ),
        ),
        'mastery_node' => false,
      ),
      11 => 
      array (
        'slug' => 'design-systems',
        'name' => 'Design Systems',
        'description' => 'Tokeny, komponenty a pravidla.',
        'tier' => 4,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'components-ui',
            'mastery' => 80,
          ),
          1 => 
          array (
            'skill' => 'responsive-ui',
            'mastery' => 75,
          ),
        ),
        'mastery_node' => false,
      ),
      12 => 
      array (
        'slug' => 'uiux-mastery',
        'name' => 'UI/UX Mastery',
        'description' => 'Produktová challenge od problému po iteraci.',
        'tier' => 5,
        'weight' => 1.5,
        'requires' => 
        array (
          0 => 
          array (
            'skill' => 'usability-testing',
            'mastery' => 80,
          ),
          1 => 
          array (
            'skill' => 'design-systems',
            'mastery' => 80,
          ),
          2 => 
          array (
            'skill' => 'forms-states',
            'mastery' => 70,
          ),
        ),
        'mastery_node' => true,
      ),
    ),
  ),
  'challenges' => 
  array (
    'broken-office' => 
    array (
      'id' => 'broken-office',
      'branch' => 'networking',
      'skill' => 'network-mastery',
      'title' => 'The Broken Office',
      'summary' => 'Komplexní praktická mastery challenge ověřující několik navazujících kompetencí.',
      'pass_score' => 75,
      'max_hints' => 3,
      'questions' => 
      array (
        0 => 
        array (
          'q' => 'Stanice má 192.168.10.42/24, gateway 192.168.10.1. Ping na gateway selže. Co má nejvyšší prioritu?',
          'options' => 
          array (
            0 => 'Ověřit lokální linku/VLAN, IP konfiguraci a dosažitelnost gateway.',
            1 => 'Měnit veřejný DNS resolver.',
            2 => 'Obnovit TLS certifikát webu.',
            3 => 'Přidat default route na serveru.',
          ),
          'correct' => 0,
        ),
        1 => 
        array (
          'q' => 'Ping na 1.1.1.1 funguje, ale portal.example.cz se nepřeloží. Která vrstva je nejsilnější hypotéza?',
          'options' => 
          array (
            0 => 'DNS resolver/záznam nebo DNS cesta klienta.',
            1 => 'Ethernet kabel serveru.',
            2 => 'Velikost MTU na tiskárně.',
            3 => 'Nefunkční CSS.',
          ),
          'correct' => 0,
        ),
        2 => 
        array (
          'q' => 'USERS VLAN potřebuje jen HTTPS na WEB. Jaké firewall pravidlo odpovídá least privilege?',
          'options' => 
          array (
            0 => 'ALLOW USERS → WEB TCP/443 před obecným deny.',
            1 => 'ALLOW USERS → SERVERS ANY.',
            2 => 'Vypnout firewall mezi VLAN.',
            3 => 'ALLOW ANY → ANY.',
          ),
          'correct' => 0,
        ),
        3 => 
        array (
          'q' => 'Po opravě konektivity co nejlépe potvrzuje uzavření incidentu?',
          'options' => 
          array (
            0 => 'Pozitivní i negativní testy z relevantních VLAN a zapsaná root cause.',
            1 => 'Jeden úspěšný ping z admin sítě.',
            2 => 'Restart všech zařízení.',
            3 => 'Smazání packet capture.',
          ),
          'correct' => 0,
        ),
      ),
    ),
    'server-down' => 
    array (
      'id' => 'server-down',
      'branch' => 'linux',
      'skill' => 'linux-mastery',
      'title' => 'Server Down',
      'summary' => 'Komplexní praktická mastery challenge ověřující několik navazujících kompetencí.',
      'pass_score' => 75,
      'max_hints' => 3,
      'questions' => 
      array (
        0 => 
        array (
          'q' => 'Web po deployi vrací 502 a lokální health check upstreamu na 127.0.0.1:9000 hlásí connection refused. Co ověřit jako první?',
          'options' => 
          array (
            0 => 'Stav služby, naslouchající port a systemd/logy upstream aplikace.',
            1 => 'DNS klienta.',
            2 => 'Fonty webu.',
            3 => 'DHCP pool kanceláře.',
          ),
          'correct' => 0,
        ),
        1 => 
        array (
          'q' => 'Disk je 100 % plný. Který postup je nejbezpečnější?',
          'options' => 
          array (
            0 => 'Zjistit, co prostor spotřebovalo, uvolnit bezpečně místo a ověřit službu.',
            1 => 'Smazat náhodně /var.',
            2 => 'Formátovat disk.',
            3 => 'Restartovat opakovaně bez diagnostiky.',
          ),
          'correct' => 0,
        ),
        2 => 
        array (
          'q' => 'Služba běží, ale aplikace nemůže číst konfiguraci. Co je relevantní důkaz?',
          'options' => 
          array (
            0 => 'Vlastník, skupina, mode/ACL souboru a identita procesu.',
            1 => 'Barva terminálu.',
            2 => 'Hostname notebooku studenta.',
            3 => 'Počet otevřených karet v browseru.',
          ),
          'correct' => 0,
        ),
        3 => 
        array (
          'q' => 'Jak změnu SSH konfigurace aplikovat bezpečně na vzdáleném serveru?',
          'options' => 
          array (
            0 => 'Nejprve validovat config, ponechat existující session, reloadnout a ověřit nové spojení.',
            1 => 'Zavřít všechny session před testem.',
            2 => 'Smazat authorized_keys.',
            3 => 'Vypnout firewall.',
          ),
          'correct' => 0,
        ),
      ),
    ),
    'incident-0317' => 
    array (
      'id' => 'incident-0317',
      'branch' => 'security',
      'skill' => 'security-mastery',
      'title' => 'Incident 03:17',
      'summary' => 'Komplexní praktická mastery challenge ověřující několik navazujících kompetencí.',
      'pass_score' => 75,
      'max_hints' => 3,
      'questions' => 
      array (
        0 => 
        array (
          'q' => 'Monitoring hlásí neobvyklé přihlášení privilegovaného účtu. Co udělat nejdřív?',
          'options' => 
          array (
            0 => 'Zachovat relevantní logy/důkazy, ověřit rozsah a bezpečně omezit riziko.',
            1 => 'Smazat logy.',
            2 => 'Veřejně zveřejnit heslo.',
            3 => 'Ignorovat incident, dokud služba běží.',
          ),
          'correct' => 0,
        ),
        1 => 
        array (
          'q' => 'Co znamená containment v incident response?',
          'options' => 
          array (
            0 => 'Omezit šíření a dopad incidentu při zachování důkazů a provozních priorit.',
            1 => 'Smazat všechny servery.',
            2 => 'Pouze napsat postmortem.',
            3 => 'Přejmenovat incident.',
          ),
          'correct' => 0,
        ),
        2 => 
        array (
          'q' => 'Jaký zdroj je nejvhodnější pro rekonstrukci podezřelého přihlášení a následných akcí?',
          'options' => 
          array (
            0 => 'Autentizační/auditní logy, časová osa událostí a relevantní systémové logy.',
            1 => 'Pouze screenshot homepage.',
            2 => 'Barva alertu.',
            3 => 'Název Wi-Fi.',
          ),
          'correct' => 0,
        ),
        3 => 
        array (
          'q' => 'Co patří po eradikaci a obnově?',
          'options' => 
          array (
            0 => 'Ověřit čistý stav, obnovit monitoring, zdokumentovat root cause a preventivní opatření.',
            1 => 'Vypnout logování.',
            2 => 'Odstranit zálohy.',
            3 => 'Vrátit stejné kompromitované credentials.',
          ),
          'correct' => 0,
        ),
      ),
    ),
    'brand-rescue' => 
    array (
      'id' => 'brand-rescue',
      'branch' => 'design',
      'skill' => 'design-mastery',
      'title' => 'Brand Rescue',
      'summary' => 'Komplexní praktická mastery challenge ověřující několik navazujících kompetencí.',
      'pass_score' => 75,
      'max_hints' => 3,
      'questions' => 
      array (
        0 => 
        array (
          'q' => 'Brief požaduje důvěryhodnost a rychlou orientaci. Co má vzniknout před finálním vizuálem?',
          'options' => 
          array (
            0 => 'Jasný koncept, obsahová hierarchie a několik cílených variant.',
            1 => 'Náhodná sada efektů.',
            2 => 'Finální export bez konceptu.',
            3 => 'Co nejvíc fontů.',
          ),
          'correct' => 0,
        ),
        1 => 
        array (
          'q' => 'Jak nejlépe ověřit, že vizuální hierarchie funguje?',
          'options' => 
          array (
            0 => 'Thumbnail/3-second test a kontrola pořadí, v jakém člověk čte hlavní sdělení.',
            1 => 'Počítáním počtu vrstev.',
            2 => 'Jen zvětšením loga.',
            3 => 'Kontrolou názvu souboru.',
          ),
          'correct' => 0,
        ),
        2 => 
        array (
          'q' => 'Co je znakem konzistentního brand systému?',
          'options' => 
          array (
            0 => 'Opakovatelné role typografie, barev, spacingu a komponent napříč formáty.',
            1 => 'Každý formát používá jiné principy.',
            2 => 'Náhodné lokální barvy.',
            3 => 'Stejné pixelové souřadnice ve všech poměrech stran.',
          ),
          'correct' => 0,
        ),
        3 => 
        array (
          'q' => 'Co má obsahovat kvalitní obhajoba návrhu?',
          'options' => 
          array (
            0 => 'Vazbu rozhodnutí na brief, cílové publikum, hierarchii a ověřitelné důvody.',
            1 => 'Pouze „líbí se mi to“.',
            2 => 'Seznam použitých klávesových zkratek.',
            3 => 'Počet exportů.',
          ),
          'correct' => 0,
        ),
      ),
    ),
    'type-rescue' => 
    array (
      'id' => 'type-rescue',
      'branch' => 'typography',
      'skill' => 'typography-mastery',
      'title' => 'Typography Rescue',
      'summary' => 'Komplexní praktická mastery challenge ověřující několik navazujících kompetencí.',
      'pass_score' => 75,
      'max_hints' => 3,
      'questions' => 
      array (
        0 => 
        array (
          'q' => 'Dlouhý odstavec je obtížně čitelný. Co kontrolovat jako první?',
          'options' => 
          array (
            0 => 'Velikost textu, délku řádku, line-height, kontrast a strukturu odstavců.',
            1 => 'Přidat třetí dekorativní font.',
            2 => 'Zmenšit line-height na minimum.',
            3 => 'Vycentrovat všechny odstavce.',
          ),
          'correct' => 0,
        ),
        1 => 
        array (
          'q' => 'Kdy je kerning nejvíc relevantní?',
          'options' => 
          array (
            0 => 'Při úpravě konkrétních dvojic znaků, zejména ve výrazných nadpisech/logotypech.',
            1 => 'Pro nastavení mezery mezi odstavci.',
            2 => 'Pro změnu line-height.',
            3 => 'Pro export obrázku.',
          ),
          'correct' => 0,
        ),
        2 => 
        array (
          'q' => 'Co nejlépe drží typografii konzistentní napříč dokumentem?',
          'options' => 
          array (
            0 => 'Definované textové role, omezená škála, grid/baseline a opakované spacing vztahy.',
            1 => 'Ruční náhodná velikost každého textu.',
            2 => 'Jeden font ve stejné velikosti pro vše.',
            3 => 'Maximální počet řezů.',
          ),
          'correct' => 0,
        ),
        3 => 
        array (
          'q' => 'Jak ověřit webovou typografii?',
          'options' => 
          array (
            0 => 'Na reálných viewportech ověřit čitelnost, wrap, kontrast, zoom a hierarchii.',
            1 => 'Pouze na jednom desktop screenshotu.',
            2 => 'Jen v editoru bez prohlížeče.',
            3 => 'Podle počtu CSS pravidel.',
          ),
          'correct' => 0,
        ),
      ),
    ),
    'fix-this-product' => 
    array (
      'id' => 'fix-this-product',
      'branch' => 'ui-ux',
      'skill' => 'uiux-mastery',
      'title' => 'Fix This Product',
      'summary' => 'Komplexní praktická mastery challenge ověřující několik navazujících kompetencí.',
      'pass_score' => 75,
      'max_hints' => 3,
      'questions' => 
      array (
        0 => 
        array (
          'q' => 'Co má předcházet detailnímu wireframu produktu?',
          'options' => 
          array (
            0 => 'Pochopení problému, uživatelů, hlavních úloh a informačního/tokového modelu.',
            1 => 'Výběr gradientu.',
            2 => 'Finální mikroanimace.',
            3 => 'Export marketingového banneru.',
          ),
          'correct' => 0,
        ),
        1 => 
        array (
          'q' => 'Které stavy formuláře je nutné navrhnout, ne jen happy path?',
          'options' => 
          array (
            0 => 'Default, focus, loading/submitting, success, validation/error a případně disabled.',
            1 => 'Pouze default.',
            2 => 'Pouze hover.',
            3 => 'Jen dark mode.',
          ),
          'correct' => 0,
        ),
        2 => 
        array (
          'q' => 'Co ověřuje usability test?',
          'options' => 
          array (
            0 => 'Zda cílový uživatel zvládne klíčový scénář, kde váhá/chybuje a proč.',
            1 => 'Jestli se design líbí autorovi.',
            2 => 'Počet komponent ve Figmě.',
            3 => 'Rychlost exportu PNG.',
          ),
          'correct' => 0,
        ),
        3 => 
        array (
          'q' => 'Co patří do funkčního design systému?',
          'options' => 
          array (
            0 => 'Tokeny, komponenty, varianty/stavy, pravidla použití a dokumentace.',
            1 => 'Jen logo.',
            2 => 'Sada náhodných screenshotů.',
            3 => 'Pouze seznam barev bez sémantiky.',
          ),
          'correct' => 0,
        ),
      ),
    ),
  ),
  'specializations' => 
  array (
    'infrastructure-specialist' => 
    array (
      'name' => 'Infrastructure Specialist',
      'requirements' => 
      array (
        'networking' => 75,
        'linux' => 70,
        'security' => 55,
      ),
    ),
    'infrastructure-architect' => 
    array (
      'name' => 'Infrastructure Architect',
      'requirements' => 
      array (
        'networking' => 90,
        'linux' => 85,
        'security' => 80,
      ),
    ),
    'digital-product-designer' => 
    array (
      'name' => 'Digital Product Designer',
      'requirements' => 
      array (
        'design' => 70,
        'typography' => 65,
        'ui-ux' => 75,
      ),
    ),
    'creative-director' => 
    array (
      'name' => 'Creative Systems Specialist',
      'requirements' => 
      array (
        'design' => 88,
        'typography' => 82,
        'ui-ux' => 70,
      ),
    ),
  ),
);
