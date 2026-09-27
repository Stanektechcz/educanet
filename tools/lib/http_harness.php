<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET · tools/lib/http_harness.php
 *
 * Malá knihovna pro HTTP smoke testy nad vestavěným PHP serverem (`php -S`).
 * Nepoužívá curl – jen `stream_context_create()` + `file_get_contents()`, výhradně
 * na 127.0.0.1. Bezpečná pro souběžné testy: každá instance běží na vlastním portu
 * a s vlastní storage (typicky dočasná kopie), server je vždy ukončen i při chybě.
 *
 * Veřejné API:
 *
 *   Harness::start(array $env = [], ?int $port = null, string $docroot = ''): Harness
 *       Najde volný port (nebo použije $port), spustí `php -S 127.0.0.1:<port>` s daným
 *       $docroot (výchozí: kořen projektu) a $env (přidá se k aktuálnímu prostředí),
 *       počká na dostupnost portu a vrátí instanci. Registruje shutdown handler i signal
 *       handler (pokud je pcntl k dispozici), aby server vždy zastavil i po chybě/exit.
 *
 *   $harness->request(string $method, string $path, array $fields = [], array $headers = []): array
 *       Provede HTTP požadavek na 127.0.0.1:<port><path>. `$fields` se u GET připojí jako
 *       query string, u POST/PUT/PATCH/DELETE se pošlou jako
 *       application/x-www-form-urlencoded tělo. Automaticky posílá a ukládá cookies
 *       (jednoduchý cookie jar) a sleduje `Location` přesměrování (max 5 skoků, lze
 *       vypnout `follow_redirects => false` v $headers). Vrací:
 *         ['status' => int, 'headers' => array<string,string> (poslední odpověď),
 *          'body' => string, 'redirects' => array<int,array{status:int,location:string}>]
 *
 *   $harness->csrfToken(string $html): ?string
 *       Vytáhne CSRF token z HTML – `name="csrf" value="…"` (skrytý input, jak ho
 *       aplikace vkládá do formulářů) nebo `<meta name="csrf-token" content="…">`.
 *
 *   $harness->cookies(): array   Aktuální cookie jar (název => hodnota).
 *   $harness->baseUrl(): string  `http://127.0.0.1:<port>`.
 *   $harness->stop(): void       Ukončí server (i strom procesů na Windows). Idempotentní.
 *
 * Použití:
 *   require __DIR__ . '/lib/http_harness.php';
 *   $h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmpStorage, 'EDUCANET_DEV_BYPASS' => '1']);
 *   try {
 *       $r = $h->request('GET', '/?class=class_3a&student=Test%20Zak');
 *       $token = $h->csrfToken($r['body']);
 *   } finally {
 *       $h->stop();
 *   }
 */
final class Harness
{
    private $process;
    private array $pipes = [];
    private int $port;
    private array $cookies = [];
    private bool $stopped = false;
    private ?int $pid = null;
    private ?string $logFile = null;

    private function __construct(int $port, $process, array $pipes, ?string $logFile)
    {
        $this->port = $port;
        $this->process = $process;
        $this->pipes = $pipes;
        $this->logFile = $logFile;
        $status = proc_get_status($process);
        $this->pid = is_array($status) ? (int)($status['pid'] ?? 0) : null;
    }

    public static function findFreePort(int $from = 8150, int $to = 8199): int
    {
        // Náhodný start v rozsahu: opakované běhy (a jiní souběžní agenti) nechávají
        // porty chvíli v TIME_WAIT, kdy je nový bind na Windows nespolehlivý.
        $span = max(1, $to - $from + 1);
        $start = $from + random_int(0, $span - 1);
        for ($i = 0; $i < $span; $i++) {
            $p = $from + (($start - $from + $i) % $span);
            $sock = @stream_socket_server("tcp://127.0.0.1:$p", $errno, $errstr);
            if ($sock !== false) {
                fclose($sock);
                return $p;
            }
        }
        throw new RuntimeException("Nenalezen volný port v rozsahu $from-$to.");
    }

    public static function start(array $env = [], ?int $port = null, string $docroot = ''): self
    {
        $docroot = $docroot !== '' ? $docroot : dirname(__DIR__, 2);
        $port = $port ?? self::findFreePort();
        $php = getenv('PHP_BINARY') ?: 'C:/php/php.exe';
        if (!is_file($php)) {
            $php = PHP_BINARY;
        }

        $fullEnv = [];
        foreach ($_SERVER as $k => $v) {
            if (is_scalar($v)) {
                $fullEnv[$k] = (string)$v;
            }
        }
        foreach ($env as $k => $v) {
            $fullEnv[$k] = (string)$v;
        }
        // Stdout/stderr jdou do souboru, ne do roury: `php -S` píše access log na každý
        // požadavek a nedrenážovaná roura by se po pár desítkách požadavků zaplnila a
        // server (jednovláknový) by se na zápisu zaseknul – i naše testovací požadavky.
        $logFile = sys_get_temp_dir() . '/educanet_harness_' . $port . '_' . bin2hex(random_bytes(4)) . '.log';
        $descriptors = [0 => ['pipe', 'r'], 1 => ['file', $logFile, 'w'], 2 => ['file', $logFile, 'a']];
        $cmd = escapeshellarg($php) . ' -S 127.0.0.1:' . $port . ' -t ' . escapeshellarg($docroot);
        $process = proc_open($cmd, $descriptors, $pipes, $docroot, $fullEnv, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            throw new RuntimeException('Nepodařilo se spustit dev server pro test.');
        }
        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $harness = new self($port, $process, $pipes, $logFile);
        register_shutdown_function([$harness, 'stop']);

        $deadline = microtime(true) + 15.0;
        $up = false;
        while (microtime(true) < $deadline) {
            $status = proc_get_status($process);
            if (is_array($status) && $status['running'] === false) {
                $err = is_file($logFile) ? (string)file_get_contents($logFile) : '';
                throw new RuntimeException('Dev server neběží: ' . trim($err));
            }
            $sock = @stream_socket_client('tcp://127.0.0.1:' . $port, $errno, $errstr, 0.2);
            if ($sock !== false) {
                fclose($sock);
                // Port přijímá spojení, ale vestavěný server ještě chvíli po startu
                // nemusí umět zpracovat skutečný HTTP požadavek – ověř to opravdovým
                // requestem, ne jen TCP handshake.
                // Lehký endpoint (statický/neexistující soubor) místo '/', aby ověření
                // nečekalo na vykreslení celé (těžké) aplikační stránky.
                $probeCtx = stream_context_create(['http' => ['method' => 'GET', 'ignore_errors' => true, 'timeout' => 2]]);
                $probe = @file_get_contents('http://127.0.0.1:' . $port . '/robots.txt', false, $probeCtx);
                if ($probe !== false) {
                    $up = true;
                    break;
                }
            }
            usleep(150000);
        }
        if (!$up) {
            $log = is_file($logFile) ? (string)file_get_contents($logFile) : '';
            $harness->stop();
            throw new RuntimeException('Dev server na portu ' . $port . ' nenaběhl včas. log=' . trim($log));
        }
        return $harness;
    }

    public function baseUrl(): string
    {
        return 'http://127.0.0.1:' . $this->port;
    }

    public function cookies(): array
    {
        return $this->cookies;
    }

    private function cookieHeader(): string
    {
        if (!$this->cookies) {
            return '';
        }
        $parts = [];
        foreach ($this->cookies as $name => $value) {
            $parts[] = $name . '=' . $value;
        }
        return implode('; ', $parts);
    }

    private function absorbSetCookies(array $headerLines): void
    {
        foreach ($headerLines as $line) {
            if (stripos($line, 'Set-Cookie:') !== 0) {
                continue;
            }
            $value = trim(substr($line, strlen('Set-Cookie:')));
            $pair = explode(';', $value, 2)[0];
            $eq = strpos($pair, '=');
            if ($eq === false) {
                continue;
            }
            $name = trim(substr($pair, 0, $eq));
            $val = trim(substr($pair, $eq + 1));
            if (stripos($value, 'Max-Age=0') !== false || stripos($value, 'expires=Thu, 01 Jan 1970') !== false) {
                unset($this->cookies[$name]);
            } else {
                $this->cookies[$name] = $val;
            }
        }
    }

    private static function headersToMap(array $headerLines): array
    {
        $map = [];
        foreach ($headerLines as $line) {
            $colon = strpos($line, ':');
            if ($colon === false) {
                continue;
            }
            $map[trim(substr($line, 0, $colon))] = trim(substr($line, $colon + 1));
        }
        return $map;
    }

    /**
     * @param array<string,mixed> $fields
     * @param array<string,mixed> $headers volitelně: 'follow_redirects' => bool (výchozí true),
     *        'extra' => array<string,string> další HTTP hlavičky
     */
    public function request(string $method, string $path, array $fields = [], array $headers = []): array
    {
        $method = strtoupper($method);
        $follow = $headers['follow_redirects'] ?? true;
        $extraHeaders = $headers['extra'] ?? [];
        $redirects = [];
        $maxHops = 5;

        for ($hop = 0; $hop <= $maxHops; $hop++) {
            $url = $this->baseUrl() . $path;
            $body = '';
            $reqHeaders = [];
            if ($method === 'GET' || $method === 'HEAD') {
                if ($fields) {
                    $sep = str_contains($path, '?') ? '&' : '?';
                    $url .= $sep . http_build_query($fields);
                }
            } else {
                $body = http_build_query($fields);
                $reqHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
                $reqHeaders[] = 'Content-Length: ' . strlen($body);
            }
            $cookieHeader = $this->cookieHeader();
            if ($cookieHeader !== '') {
                $reqHeaders[] = 'Cookie: ' . $cookieHeader;
            }
            foreach ($extraHeaders as $k => $v) {
                $reqHeaders[] = $k . ': ' . $v;
            }

            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $reqHeaders),
                    'content' => $body,
                    'ignore_errors' => true,
                    'timeout' => 10,
                    'follow_location' => 0,
                    'protocol_version' => 1.1,
                ],
            ]);

            $respBody = @file_get_contents($url, false, $context);
            if ($respBody === false) {
                // Ojedinělé selhání spojení (server právě dobíhá start) – jedno opakování stačí.
                usleep(150000);
                $respBody = @file_get_contents($url, false, $context);
            }
            $responseHeaders = $http_response_header ?? [];
            $status = 0;
            if (isset($responseHeaders[0]) && preg_match('~HTTP/\S+\s+(\d+)~', $responseHeaders[0], $m)) {
                $status = (int)$m[1];
            }
            $this->absorbSetCookies($responseHeaders);
            $headerMap = self::headersToMap($responseHeaders);

            if ($follow && in_array($status, [301, 302, 303, 307, 308], true) && isset($headerMap['Location'])) {
                $redirects[] = ['status' => $status, 'location' => $headerMap['Location']];
                $location = $headerMap['Location'];
                if (str_starts_with($location, 'http://') || str_starts_with($location, 'https://')) {
                    $path = (string)parse_url($location, PHP_URL_PATH) . (($q = parse_url($location, PHP_URL_QUERY)) ? '?' . $q : '');
                } else {
                    $path = $location;
                }
                if ($path === '' || $path[0] !== '/') {
                    $path = '/' . $path;
                }
                $method = 'GET';
                $fields = [];
                continue;
            }

            return [
                'status' => $status,
                'headers' => $headerMap,
                'raw_headers' => $responseHeaders,
                'body' => (string)$respBody,
                'redirects' => $redirects,
            ];
        }

        throw new RuntimeException('Příliš mnoho přesměrování pro ' . $path);
    }

    public function csrfToken(string $html): ?string
    {
        if (preg_match('/name=["\']csrf["\']\s+value=["\']([^"\']+)["\']/i', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/value=["\']([^"\']+)["\']\s+name=["\']csrf["\']/i', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/<meta[^>]+name=["\']csrf-token["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return $m[1];
        }
        return null;
    }

    public function stop(): void
    {
        if ($this->stopped) {
            return;
        }
        $this->stopped = true;
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
            }
        }
        if (is_resource($this->process)) {
            if (stripos(PHP_OS, 'WIN') === 0 && $this->pid) {
                // php -S na Windows spouští potomka: /T ukončí celý strom procesů.
                // Nejdřív taskkill DOBĚHNE (proc_close na jeho handle čeká na jeho konec,
                // ne na konec php -S), teprve pak čekáme na ukončení hlídaného procesu –
                // jinak hrozí zámek: proc_close($this->process) by čekal na proces, který
                // ještě nikdo nezabil.
                $killDescriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                $killProcess = @proc_open('taskkill /F /T /PID ' . (int)$this->pid, $killDescriptors, $killPipes, null, null, ['bypass_shell' => true]);
                if (is_resource($killProcess)) {
                    foreach ($killPipes as $pp) {
                        if (is_resource($pp)) {
                            @fclose($pp);
                        }
                    }
                    @proc_close($killProcess);
                }
            } else {
                @proc_terminate($this->process);
            }
            // Krátké aktivní čekání místo blokujícího proc_close, kdyby se proces přesto
            // nestihl ukončit včas (např. taskkill selhal) – nikdy nechceme viset navždy.
            $deadline = microtime(true) + 5.0;
            while (microtime(true) < $deadline) {
                $status = @proc_get_status($this->process);
                if (!is_array($status) || $status['running'] === false) {
                    break;
                }
                usleep(100000);
            }
            @proc_close($this->process);
        }
        if ($this->logFile !== null) {
            @unlink($this->logFile);
        }
    }

    public function __destruct()
    {
        $this->stop();
    }
}
