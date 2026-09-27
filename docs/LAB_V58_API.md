# Linux Lab v58 – API rozšiřitelného jádra

Stav: v58.0 jádra (agent „labcore“). Zpětně kompatibilní s v57: `tools/v57_linux_lab_audit.php` 330/330,
`tools/v57_arena_audit.php` 78/78. Vlastní audit: `tools/v58_lab_ext_audit.php` (konec `V58_LAB_EXT_AUDIT_OK`).

Soubory jádra:

| Soubor | Obsah |
|---|---|
| `linux_v58_ext.php` | registry (příkazy, apt, příručka, balíčky, zdroje úrovní, kontexty, události, filtry, rozšiřovače světa), načítání rozšíření, `lab58_try_solution()` |
| `linux_v57_levels.php` (sekce v58 na konci) | deklarativní úrovně: `lab58_register_generator/check`, `lab58_fill`, ukázkové generátory a kontroly, `lab58_validate_level()` |
| `lab_v58_log.php` | log příkazů, `lab58_classify_error()`, čtení a agregace pro analytiku |
| `linux_v57_lab.php` | `lab57_session()` – používá registry, vysílá události; na konci načte `linux_v58_ext.php` |
| `lab_v57_api.php` | JSON API; kontext validuje registr; `session_write_close()`; log se zapisuje až po odpovědi |

**Bezpečnostní invariant platí beze změny**: nic se nespouští, žádná síť. Token-sken v57 auditu teď pokrývá
i `linux_v58_*.php` a `lab_v58_*.php`. Rozšíření nesmí volat `exec`, `system`, `eval`, sockety, `curl_*`, DNS,
`mail`, zpětné apostrofy ani `file_get_contents`/`fopen` s URL.

---

## 0. Načítání a pořadí volání

1. Kdokoli načte `linux_v57_lab.php` (index, API, teacher, audity) → v57 soubory → na konci `linux_v58_ext.php`.
2. `linux_v58_ext.php` načte `lab_v58_log.php`, zaregistruje kontexty `practice` a `race`, ukázkové generátory
   a kontroly, posluchače logu, a pak **rozšíření**:
   - nejdřív všechny `linux_v58_cmd_*.php`, potom všechny `linux_v58_levels_*.php`, v každé skupině abecedně;
   - jen soubory z kořene projektu s názvem `^linux_v58_(cmd|levels)_[a-z0-9_]{1,40}\.php$`;
   - výjimka/ParseError v rozšíření se zachytí (`lab58_registry_errors()`), aplikace běží dál.
   - `$GLOBALS['lab58_skip_ext'] = true` před načtením jádra rozšíření vypne (izolované testy).
3. Registrace se provádí **při načtení souboru** (top-level kód), až po definici funkcí, na které odkazuje.
4. Cache (`lab57_levels()`, `lab57_command_registry()`, `lab58_manual()`, `lab58_packs()`) se zneplatní
   při každé registraci (`lab58_registry_version()`), úrovně a zdroje se vyhodnocují líně – jednou za požadavek.

Pořadí v jednom požadavku `lab57_session($ctx, $op, $input)`:

```
lab58_ctx_prepare(ctx)            prefix, id, state_key, shared, role, player
lab57_resolve_level → lab57_access_error   practice: třída balíčku + odemčení; jinak kontext levels()+access()
lab58_level_for_role              level['roles'][role] přepíše story/task/hints/commands/learn
lab58_context_info → seed → code  (kód i semínko z state_key, u žáka = v57 beze změny)
ZÁMEK stavového souboru (state_key):
  lab57_build_world: world_new → sandbox home → world extendery → build/generate → seal
  lab57_apply_state (+ ext, users/groups, hodiny) → u sdíleného světa stav hráče
  run/save/state/reset/complete … → export stavu
KONEC ZÁMKU → lab58_emit(...) pro všechny nasbírané události → lab57_event_add → XP → on_complete → emit complete
```

Události se vysílají **až po uvolnění zámku** – posluchač smí zapisovat do úložiště.

---

## 1. Příkazy

```php
lab58_register_command(string $name, string $handler, array $meta = []): bool
```

- `$name`: `^[a-z0-9][a-z0-9._+-]{0,31}$`. Kolize s v57 příkazem je chyba, pokud není `'override' => true`.
- `$handler`: jméno funkce `fn(Lab57Proc $p, array $argv): int` (jako v57: `$p->line()`, `$p->err()`, `$p->fail()`,
  `$p->stdin`, `$p->w` = `Lab57World`; `$argv[0]` je jméno příkazu; návratová hodnota = exit kód).
- `$meta`:
  | klíč | typ | výchozí | význam |
  |---|---|---|---|
  | `package` | ?string | `null` | balíček apt; příkaz existuje až po `sudo apt install <package>` |
  | `bin` | string | `/usr/bin/<name>` | cesta „binárky“ ve VFS (musí být v `$PATH`, aby fungoval `which`) |
  | `builtin` | bool | `false` | vestavěný příkaz shellu (nehledá se v `$PATH`, `type` → „shell builtin“) |
  | `help` | bool | `true` | `<cmd> --help` obslouží příručka; `false` = `--help` dostane handler |
  | `override` | bool | `false` | vědomé nahrazení v57 příkazu |

Registrovaný příkaz funguje v: dispatch, `$PATH`/`which`/`type`, `--help`, `man`, `help` (když má položku
v příručce s `in_lab`), `lab57_command_registry()`, doplňování Tab, tipy „command not found“ (překlepy).

Balíčky apt (volitelné – jinak se balíček z `meta.package` založí automaticky jako neinstalovaný):

```php
lab58_register_apt_package(string $name, array $info): bool
// $info: version, description, size, installed_size, bins (list příkazů), preinstalled (bool), removable (bool=true)
```

Minimální příklad (`linux_v58_cmd_archive.php`):

```php
<?php
declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

function lab58_cmd_unzip(Lab57Proc $p, array $argv): int
{
    $file = $argv[1] ?? '';
    if ($file === '') return $p->fail('missing archive operand');      // stderr „unzip: …“, exit 1
    $err = null;
    $data = $p->w->readFile($file, $err);
    if ($data === null) { $p->err("unzip: cannot find or open $file\n"); return 9; }
    $p->line("Archive:  $file");
    return 0;
}

lab58_register_command('unzip', 'lab58_cmd_unzip', ['package' => 'unzip']);
lab58_register_apt_package('unzip', ['version' => '6.0-28', 'description' => 'De-archiver for .zip files', 'bins' => ['unzip']]);
lab58_register_manual(['unzip' => [
    'cat' => 'soubory', 'summary' => 'Rozbalí archiv ZIP.', 'synopsis' => 'unzip archiv.zip', 'about' => 'Rozbalí …',
    'examples' => [['unzip --help', 'krátká nápověda']], 'tldr' => [['unzip data.zip', 'rozbalí do aktuální složky']],
    'see_also' => ['zip', 'tar'], 'in_lab' => true,
]]);
```

Simulovaný čas v příkazech: vždy `$p->w->now` (už obsahuje posun hodin). Deterministické: žádné `time()`, `rand()`.

---

## 2. Příručka

```php
lab58_register_manual(array $entries): bool
lab58_manual(): array            // sloučená příručka; v57_manual() vrací totéž, v57_manual_base() jen v57 data
```

`$entries` = `['commands' => [...], 'concepts' => [...], 'categories' => [...]]` nebo přímo mapa `jméno => položka`.
Položka příkazu jako v57 (`cat`, `summary`, `synopsis`, `about` povinné; `options`, `examples`, `related`,
`level`, `in_lab`, `tip`, `warn`) + nové `tldr` (list `[příkaz, popis]`, v `man` sekce „RYCHLÉ PŘÍKLADY“) a
`see_also` (list jmen, přidá se do „SOUVISEJÍCÍ“). `in_lab` výchozí = příkaz je v registru.
Existující příkaz se doplní přes `'extend' => true` (připojí `tldr`, `see_also`, `examples`, doplní prázdný `tip`).
Nová kategorie: `'categories' => ['archivy' => ['label' => 'Archivy', 'icon' => '🗜', 'lead' => '…']]`.

---

## 3. Balíčky a úrovně

```php
lab58_register_pack(array $pack, callable $levels): bool      // $levels(): list<array> – volá se líně
lab58_register_level_source(callable $provider): bool         // $provider(): list<level> | ['packs' => [...], 'levels' => [...]]
lab58_packs(): array                                          // v57 + v58 + zdroje, seřazené podle order
lab58_pack(string $id): ?array
lab58_packs_for_class(string $classId): array                 // jen povolené pro třídu a neprázdné (pro UI)
lab58_pack_allows_class(string $packId, string $classId): bool
lab57_levels(), lab57_level($id), lab57_pack_levels($pack)    // obsahují i v58 úrovně
```

Tvar balíčku (po normalizaci): `id` (`^[a-z0-9-]{2,24}$`), `title`, `description` (= `lead`), `order` (int, v57
balíčky mají 10–60), `classes` (`null` = všem, jinak neprázdný seznam ID tříd, např. `['class_1a','class_2a']`),
`unlock` (`'sequential'` = další úroveň po vyřešení předchozí, `'free'` = vše hned), `badge` (libovolná hodnota,
např. `['id' => 'archivar', 'label' => 'Archivář', 'icon' => '🗜']`), `icon`, `tone`, `inspired`, `source` (`v57|v58|store`).
`lab57_packs()` zůstává jen v57 (stávající UI); nové UI má používat `lab58_packs_for_class()`.
V procvičování `lab57_access_error()` odmítne úroveň balíčku, který třídě nepatří („není pro tvou třídu“).

Tvar úrovně jako v57: `id` (`^[a-z0-9-]{2,32}$`, globálně unikátní), `type` (`code|answer|check|golf`), `title`,
`story`, `task`, volitelně `difficulty`, `points`, `minutes`, `commands`, `hints`, `world`, `topology`, `learn`,
`answer_format`, `golf` (`reference`, `tests`), `v` (verze – zvýšení resetuje uložené instance), `build`, `checks`,
`answer`, `solution`, `programs`, `net`. `pack` se doplní z balíčku, `no` = pořadí v balíčku.
Nové: `roles` (viz kontexty) a deklarativní části:

| klíč | deklarativně | význam |
|---|---|---|
| `generate` | `[[jméno, params], …]` | místo/před `build`: generátory v pořadí, každý má vlastní RNG ze semínka |
| `checks` | `[[jméno, params], …]` | místo `[['label','fn'], …]`; `params['label']` je text v `check` |
| `answer` | string šablona | např. `'{f:secret}'` |
| `solution` | list šablon | např. `['cat {f:code_path}', 'submit {CODE}']` |

Šablony (`lab58_fill`): `{CODE}`, `{HOME}` (/home/student), `{USER}`, `{HOST}`, `{N}` (pořadí v `decoys`),
`{TOKEN}` (8 znaků z RNG generátoru), `{DECOY}` (falešný kód), `{f:jméno}` (fakt z generátoru, `$w->facts`).
Cesta začínající `~` = domov žáka.

```php
lab58_register_generator(string $name, callable $fn): bool   // $fn(Lab57World $w, Lab57Rng $rng, array $params): void
lab58_register_check(string $name, callable $fn): bool       // $fn(Lab57World $w, array $params): bool
lab58_validate_level(array $level): list<string>             // chyby tvaru + neznámé generátory/kontroly (editor)
lab58_try_solution(array $level, string $seed, int $now, string $code = 'EDU-TEST-0000'): array
                                                             // {solved, cmds, transcript} – bez úložiště (kontrola řešitelnosti)
```

Ukázkové generátory: `file` (`path`, `content`, `mode`, `owner`, `group`, `days`, `fact`),
`code_file` (`dirs`, `names`, `content`=`"{CODE}\n"`, `mode`, `owner`, `fact`=`code_path`),
`decoys` (`dir`, `count` 1–50, `name`=`zprava-{N}.txt`, `content`=`"Tady kód není: {DECOY}\n"`, `fact`=`decoys`).
Ukázkové kontroly: `file_contains` (`path`, `contains` | `equals`, `label`), `service_running` (`service`, `label`).
Generátor smí zapisovat jen do `$w` a `$w->facts` (fakta jsou string, neukládají se – staví se znovu).

Minimální balíček s jednou deklarativní úrovní jen pro 1.A/2.A (`linux_v58_levels_grafika.php`):

```php
lab58_register_pack(
    ['id' => 'grafika', 'title' => 'Terminál pro grafiky', 'description' => 'Soubory projektu a náhledy.',
     'order' => 70, 'classes' => ['class_1a', 'class_2a'], 'unlock' => 'sequential', 'badge' => ['id' => 'grafik', 'label' => 'Grafik v terminálu']],
    static fn(): array => [[
        'id' => 'grafika-1', 'type' => 'code', 'title' => 'Ztracený kód', 'difficulty' => 1, 'points' => 80,
        'story' => 'Do projektu se připletl soubor s kódem.', 'task' => 'Najdi kód a odevzdej ho: submit EDU-XXXX-XXXX',
        'hints' => ['Zkus ls -R nebo find ~ -name "*.txt".'],
        'generate' => [
            ['decoys', ['dir' => '~/web/obrazky', 'count' => 5, 'name' => 'popis-{N}.txt']],
            ['code_file', ['dirs' => ['~/web', '~/web/css', '~/.cache'], 'names' => ['kod.txt', 'poznamka-{TOKEN}.txt']]],
        ],
        'solution' => ['cat {f:code_path}', 'submit {CODE}'],
    ]]
);
```

Kontrolní úroveň: `'type' => 'check', 'checks' => [['file_contains', ['path' => '~/stav.txt', 'equals' => 'hotovo', 'label' => 'stav.txt obsahuje hotovo']], ['service_running', ['service' => 'nginx', 'label' => 'nginx běží']]]`.

Úrovně učitele z úložiště: `lab58_register_level_source(fn(): array => ['packs' => [...], 'levels' => [...]])` –
čti jen přes `lab57_store_read()`, úrovně musí být deklarativní (žádné closures z dat).

---

## 4. Kontexty (practice, race:<id>, …)

```php
lab58_register_context(string $prefix, array $spec): bool    // prefix ^[a-z]{2,12}$
lab58_context_valid(string $context): bool                    // practice | ^[a-z]{2,12}:[a-z0-9_-]{4,40}$ s registrovaným prefixem
lab58_context_parse(string $context): ?array{prefix, id}
```

API (`lab_v57_api.php`) přijme kontext jen přes `lab58_context_valid()`. Callbacky `$spec` (všechny volitelné,
výjimka v callbacku se zaloguje a použije se výchozí hodnota; u `access` se přístup odmítne):

| klíč | signatura | výchozí |
|---|---|---|
| `label` | string | prefix |
| `access` | `fn(array $level, array $ctx): ?string` (chyba pro žáka, `null` = smí) | **zamítnuto** – kontext bez `access` je z bezpečnostních důvodů zavřený pro všechny; vždy ho definuj (i jako `static fn(): ?string => null`) |
| `levels` | `fn(array $ctx): ?array` (seznam ID úrovní, `null` = libovolné) | `null` |
| `state_key` | `fn(array $ctx): string` | `$ctx['student']` |
| `seed` | `fn(array $ctx, array $level): string` | `lab57_seed(class, state_key, context, level)` |
| `points` | `fn(array $level, int $hints, array $ctx): int` | `lab57_level_points($level, $hints, $ctx['info'])` |
| `on_complete` | `fn(array $ctx, array $level, array $event): void` | – |
| `events_path` | `fn(string $context): string` | `storage/linux_v58/ctx_<prefix>_<id>.events.json.php` |
| `role` | `fn(array $ctx): ?string` (`^[a-z0-9_-]{1,24}$`) | `null` |
| `info` | `fn(array $ctx): ?array` – `{id,title,ends_at,settings:{hints:bool,hint_penalty:float}}`; v terminálu jako lišta `race` | `null` |
| `board` | `fn(array $ctx): array` – odpověď `op=board` | žádný (404) |
| `first_blood` | bool – první vyřešení úlohy v kontextu dostane ×1.25 | `false` |
| `reset` | bool – smí se úroveň resetovat | `true` |

`$ctx` v callbackech: `class`, `student`, `label`, `context`, `prefix`, `id`, `level`, `now`, `classmates`,
`state_key`, `shared` (state_key ≠ student), `role`, `player` (sha1 žáka, 12 znaků), v `points`/`on_complete` i `info`.
`race` je registrovaný stejným mechanismem (volá `arena57_race_access`, `arena57_race`, `arena57_board`).

**Sdílený svět týmu**: vrátí-li `state_key` klíč týmu, všichni hráči pracují v jednom stavovém souboru
`lab57_state_path(class, state_key)`; každý příkaz drží zámek přes čtení, běh i zápis → příkazy spoluhráčů se
serializují. Per hráč (`row['players'][player]`, max. 8) se ukládá `cwd`, `oldCwd`, `history`, `lastExit`, `env`,
`aliases`; limit příkazů je per hráč. Vyřešení (`solved_at`) a nápovědy jsou společné; log a události jsou per hráč
(`student`) s `state_key` týmu. Role: `lab58_level_for_role()` přepíše pro hráče `story/task/hints/commands/learn`
z `level['roles'][role]`; v příkazech `lab58_role($p->w)`. **Stavba světa (build/generate/extendery) nesmí záviset
na roli** – svět je sdílený; role mění jen pohled (zadání, výstup příkazů, kontroly).

Minimální týmový kontext:

```php
lab58_register_context('mise', [
    'label' => 'Týmová mise',
    'access' => static fn(array $level, array $ctx): ?string => my_team_of((string)$ctx['id'], (string)$ctx['student']) !== null ? null : 'Nejsi v tomhle týmu.',
    'levels' => static fn(array $ctx): array => ['mise-server-1'],
    'state_key' => static fn(array $ctx): string => 'team:' . $ctx['id'] . ':' . my_team_of((string)$ctx['id'], (string)$ctx['student']),
    'role' => static fn(array $ctx): ?string => my_role_of((string)$ctx['id'], (string)$ctx['student']),   // 'sitar' | 'spravce' | 'detektiv'
    'points' => static fn(array $level, int $hints, array $ctx): int => 50,
    'on_complete' => static function (array $ctx, array $level, array $event): void { /* zápis do vlastního úložiště přes lab57_store_update */ },
    'reset' => false,
]);
// úroveň: 'roles' => ['sitar' => ['task' => 'Zjisti, proč server nevidí bránu.'], 'spravce' => ['task' => 'Oprav konfiguraci nginx.']]
```

---

## 5. Události

```php
lab58_on(string $event, callable $fn): bool     // $fn(array $payload): void; '*' = všechny události
lab58_emit(string $event, array $payload): void // výjimka posluchače se zachytí a zaloguje (error_log)
```

Společné klíče payloadu: `event`, `ctx` (celý kontext), `prefix`, `class`, `student`, `state_key`, `level`, `pack`,
`role`, `ts` (čas požadavku).

| událost | kdy | navíc |
|---|---|---|
| `open` | `op=state` (terminál otevřen) | `solved` |
| `command` | každý `op=run` | `line` (≤400), `exit`, `error_class`, `out` (≤300, stdout+stderr), `hint` (číslo nápovědy nebo 0), `seq` (pořadí příkazu v instanci) |
| `hint` | příkaz `hint` spotřeboval nápovědu | `n` |
| `submit_fail` | `submit/answer/check` neprošlo (`error_class = wrong_answer`) | `line`, `foreign` (cizí kód) |
| `submit_ok` | úloha vyřešena (i uložením v editoru) | `line` |
| `complete` | po zápisu události `solve` | `points` (vč. first blood), `hints`, `secs`, `cmds`, `first`, `golf_len` |
| `reset` | příkaz `reset` nebo `op=reset` | `via` (`command`/`op`) |

```php
lab58_on('complete', static function (array $e): void {
    if ($e['pack'] === 'archivy') my_badges_award($e['class'], $e['student'], 'archivar');   // vlastní úložiště
});
```

---

## 6. Log příkazů a analytika (`lab_v58_log.php`)

Úložiště: `storage/linux_v58/log/<třída>__<sha1(žák)>/<úroveň>.jsonl.php` (1. řádek ochrana, pak JSON řádky) +
`meta.json.php` (třída, klíč žáka). Kruhový buffer: čtení vrací ≤ 150 záznamů na úroveň za posledních 30 dní;
zápis = připojení řádku; soubor se zhutní při > 160 KB nebo u každého 50. příkazu instance (retence 30 dní při
zápisu). `lab58_log_purge()` smaže soubory neměněné 30 dní (OPS-03). V API se zápis logu odkládá až po odeslání
odpovědi (`$GLOBALS['lab58_log_defer']`, `lab58_log_flush_after_response()`); v CLI/auditu se zapisuje hned.

```php
lab58_classify_error(string $stderr, int $exit, string $cmd): string
// ok | not_found | no_such_file | permission | bad_option | syntax | usage | wrong_answer | other
lab58_log_read(string $classId, string $studentKey, ?string $levelId = null, ?int $now = null): array
// list<{ts, ctx, level, kind: cmd|open|complete|reset, line, exit, error_class, out, hint, points, role}>
lab58_level_stats(string $classId, string $studentKey, string $levelId, ?int $now = null): array
// {level, attempts, errors: {třída => n}, failed_submits, hints, opened_at, last_ts, completed_at, points, secs_open}
lab58_class_log_summary(string $classId, int $sinceTs, ?int $now = null): array
// {class, since, students, total, commands: {cmd => {total, students, errors: {třída => {count, students}}}}, errors: {třída => {count, students}}}
// bez jmen: jen název příkazu (1. slovo bez sudo/VAR=), počty a počty žáků
lab58_class_activity(string $classId, int $sinceTs, int $recentMin = 5, ?int $now = null): array
// {studentKey => {student, level, ctx, last_ts, idle_secs, cmds_recent, errors_recent, solved}}
lab58_log_command_name(string $line): string
lab58_log_purge(?int $now = null): int
```

Pravidla soukromí: data jen v rámci třídy učitele, žádná jména v agregacích, retence 30 dní, nic do URL ani logů PHP.

---

## 7. Svět

`Lab57World` má nové vlastnosti: `ext` (array, **ukládá se**), `facts` (fakta generátorů, neukládá se),
`session` (`context`, `prefix`, `role`, `player`, `shared` – neukládá se), `clockBase`, `baseAccounts`.

- `ext` se uloží celé, při načtení `array_replace(ext z buildu, uložené ext)`.
- `users`/`groups` se uloží jen tehdy, když se liší od výchozího světa (po změně volej `lab57_world_write_accounts($w)`).
- Simulovaný čas: `lab58_now($w)` = čas požadavku + `ext['clock_offset']`; po načtení stavu je `$w->now = lab58_now($w)`,
  takže `date`, `uptime`, `ls -l`, `stat`, `touch`, `journalctl` i nové záznamy žurnálu jdou podle simulace.
  `lab58_clock_advance(Lab57World $w, int $seconds): int` posune hodiny (±10 let) – např. příkaz `timewarp`.
- `lab58_register_world_extender(callable $fn)`: `$fn(Lab57World $w, array $level, Lab57Rng $rng): void`, volá se
  po základním světě (a pískovišti) a před `build`/`generate` úrovně, pro **všechny** úrovně – kontroluj `$level['id']`/`pack`.

---

## 8. Filtry

```php
lab58_add_filter(string $name, callable $fn): bool       // $fn(mixed $value, array $args): mixed
lab58_filter(string $name, mixed $value, array $args = []): mixed   // výjimka filtru → zaloguje se, hodnota zůstane
```

| filtr | hodnota | `$args` |
|---|---|---|
| `state_payload` | JSON pro terminál (`op=state`): `level`, `checks`, `solved`, `tx`, `history`, `topology`, `race`, `motd`, `context` | `level`, `ctx`, `row`, `world` |
| `level_points` | int body za vyřešení | `level`, `hints`, `ctx` |
| `run_response` | JSON odpověď `op=run` (jen `ok`) | `level`, `ctx` |

```php
lab58_add_filter('state_payload', static fn(array $p, array $a): array => $p + ['a11y' => ['live' => true]]);
```

---

## 9. API a session (DAT-04)

`lab_v57_api.php` po ověření CSRF a identity žáka volá `session_write_close()`. Lab do session nepíše; jediná
výjimka – XP za první vyřešení v procvičování (`learning_award_once`) – session krátce znovu otevře přes
`lab58_with_session(callable)`. Nový kód, který potřebuje zapsat do session z API, musí použít `lab58_with_session()`.
`op=board` volá `board` callback kontextu.

---

## 10. Pravidla pro další agenty

- **Jména souborů**: příkazy `linux_v58_cmd_<téma>.php`, úrovně `linux_v58_levels_<balíček>.php` (malá písmena,
  číslice, `_`). Každý soubor: `declare(strict_types=1)`, guard knihovny, < 800 řádků. Funkce s prefixem `lab58_`
  + téma (např. `lab58_cmd_tar`, `lab58_arc_*`), ID úrovní s prefixem balíčku (`archivy-3`).
- **Nesahej** do `linux_v57_*`, `linux_v58_ext.php`, `lab_v58_log.php` – vše jde přes registry. Chybí-li
  rozšiřovací bod, napiš patch do INTEGRATION.md.
- **Stav**: vlastní data jen přes `lab57_store_update()` (zámek přes RMW) do `storage/linux_v58/<téma>/…json.php`
  s ochranným řádkem; nikdy do `storage/` přímo z posluchače bez zámku. Stav světa patří do `$w->ext`, `$w->mem`
  nebo VFS – ne do globálních proměnných.
- **Logování**: nepiš vlastní log příkazů – použij události (`lab58_on`) a čtecí funkce `lab58_log_*`.
- **Determinismus**: jen `Lab57Rng` ze semínka (`$rng`, `new Lab57Rng($w->seed . '|něco')`), čas `$w->now`.
- **Testy**: každý agent má audit `tools/v58_<téma>_audit.php` (CLI guard, `$GLOBALS['lab57_storage_override']`
  na dočasnou složku, `$GLOBALS['lab57_secret_override']`), který ověří přesný výstup, exit kódy, stderr,
  tipy, determinismus a řešitelnost (`lab58_try_solution` na ≥ 3 semínkách). Vždy spusť také
  `tools/v57_linux_lab_audit.php` (token-sken pokrývá i tvé soubory), `tools/v57_arena_audit.php`
  a `tools/v58_lab_ext_audit.php` – ten kontroluje, že `lab58_registry_errors()` je prázdné, že všechny v58
  položky příručky mají funkční příklady a že všechny v58 úrovně jsou řešitelné.
