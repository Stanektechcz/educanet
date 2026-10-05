<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v64 audit · dávka 3: role týmových her, přínos, retrospektiva. Proměnné: $check, $tmp, $root. */
foreach (['teamgames_v58_registry.php', 'teamgames_v58_game_netadmin.php', 'teamgames_v58_game_escape.php', 'teacher_scope_v59.php'] as $lib) require_once $root . '/' . $lib;

$cid = 'class_3a';
$m = ['class_3a:s1', 'class_3a:s2', 'class_3a:s3', 'class_3a:s4'];
$r0 = tg64_assign_roles($m, 0);
$r1 = tg64_assign_roles($m, 1);
$r2 = tg64_assign_roles($m, 2);
$check('role: navigátor/operátor/kontrolor, v kole 0 po řadě, další kolo se otočí', $r0['class_3a:s1'] === 'navigator' && $r0['class_3a:s2'] === 'operator' && $r0['class_3a:s3'] === 'checker' && $r1['class_3a:s1'] === 'operator' && $r2['class_3a:s1'] === 'checker');
$seen = [];
for ($round = 0; $round < 3; $round++) $seen[tg64_assign_roles($m, $round)['class_3a:s1']] = true;
$check('role: za 3 kola si každý vyzkouší všechny 3 role (rotace)', count($seen) === 3);
$check('role: alias funkčních rolí úniku (sitar→operátor, detektiv→navigátor, dokumentátor→kontrolor) a neznámá role = null', tg64_role_alias('sitar') === 'operator' && tg64_role_alias('detektiv') === 'navigator' && tg64_role_alias('dokumentator') === 'checker' && tg64_role_alias('xx') === null);
$allAlias = true;
foreach (array_merge(TG58_ESCAPE_ROLES_NET, TG58_ESCAPE_ROLES_GFX) as $er) $allAlias = $allAlias && tg64_role_alias($er) !== null;
$check('role: všechny funkční role úniku (síť i grafika) mají alias', $allAlias);

// --- Přínos ---------------------------------------------------------------------------------------
$session = ['id' => 'abc12345', 'class_id' => $cid, 'type' => 'escape', 'teams' => [['id' => 't1', 'name' => 'Tým', 'members' => $m]], 'contrib' => []];
$fair = $session;
foreach ($m as $key) { for ($i = 0; $i < 3; $i++) $fair = tg64_actor_note($fair, $key, 'ok'); }
$fairC = array_map(static fn(string $k): float => tg64_contribution($fair, 't1', $k), $m);
$check('přínos: vyrovnaný tým má všem 1,0', $fairC === [1.0, 1.0, 1.0, 1.0]);
$free = $session;
for ($i = 0; $i < 8; $i++) $free = tg64_actor_note($free, 'class_3a:s1', 'ok');
$free = tg64_actor_note($free, 'class_3a:s2', 'ok');
$check('přínos: kdo nic neudělal, má 0 (nelze jet na černo); nositel týmu max 1,0', tg64_contribution($free, 't1', 'class_3a:s4') === 0.0 && tg64_contribution($free, 't1', 'class_3a:s3') === 0.0 && tg64_contribution($free, 't1', 'class_3a:s1') === 1.0);
$weak = tg64_contribution($free, 't1', 'class_3a:s2');
$check('přínos: slabý přispěvatel má menší přínos než nositel (0 < s2 < s1)', $weak > 0.0 && $weak < 1.0);
$noisy = tg64_actor_note(tg64_actor_note(tg64_actor_note($session, 'class_3a:s1', 'ok'), 'class_3a:s1', 'wrong'), 'class_3a:s1', 'wrong');
$check('přínos: chybné pokusy snižují přesnost (1 správně / 2 špatně < 1,0)', tg64_contribution($noisy, 't1', 'class_3a:s1') < 1.0);
$sig = tg64_actor_note($session, 'class_3a:s2', 'signal');
$check('přínos: signál navigátora se počítá, neznámý druh/cizí klíč se ignoruje', tg64_contribution($sig, 't1', 'class_3a:s2') > 0.0 && tg64_actor_note($session, 'x', 'hack') === $session && tg64_contribution($sig, 't1', 'neexistuje') === 0.0);
$check('přínos: odpověď s correct=true se zapíše, bez klíče correct ne', tg64_actor_from_response($session, 'class_3a:s1', ['ok' => true, 'correct' => true])['contrib']['class_3a:s1']['ok'] === 1 && tg64_actor_from_response($session, 'class_3a:s1', ['ok' => true]) === $session);
$check('skóre týmové hry = 0,3 + 0,5 × přínos, max 0,8 (nulový přínos 0,3)', tg64_evidence_score(1.0) === 0.8 && tg64_evidence_score(0.0) === 0.3 && tg64_evidence_score(5.0) === 0.8 && tg64_evidence_score(0.5) === 0.55);

// signál v reálné relaci
tg58_update(tg58_session_path('abc12345'), static fn(array $d): array => $session + ['status' => 'running', 'signals' => []]);
tg58_send_signal('abc12345', 't1', 'class_3a:s2', 'help', time());
$saved = tg58_get('abc12345');
$check('přínos: tg58_send_signal zapíše actor (signal=1) do uložené relace', (int)($saved['contrib']['class_3a:s2']['signal'] ?? 0) === 1);

// --- Retrospektiva --------------------------------------------------------------------------------
$check('retrospektiva: přesně 3 otázky', count(tg64_retro_questions()) === 3);
tg64_retro_save($cid, 'abc12345', 't1', 'class_3a:s1', ['Dobře komunikace', str_repeat('x', 400), 'Víc kontrolovat'], 1_790_000_000);
$raw = (string)file_get_contents(tg64_retro_path());
$stored = storage_read(tg64_retro_path())['abc12345']['teams']['t1'][sha1($cid . '|class_3a:s1')]['a'] ?? [];
$check('retrospektiva: odpověď se ořízne na 280 znaků a uloží pod sha1 (žádný klíč žáka v souboru)', mb_strlen($stored[1] ?? '') === 280 && !str_contains($raw, 'class_3a:s1') && str_starts_with($raw, '<?php http_response_code(403)'));
$threw = false;
try { tg64_retro_save($cid, 'abc12345', 't1', 'class_3a:s2', ['', '  ', ''], 1); } catch (RuntimeException $e) { $threw = true; }
$check('retrospektiva: prázdná odevzdávka se odmítne', $threw && tg64_retro_done($cid, 'abc12345', 't1', 'class_3a:s1') && !tg64_retro_done($cid, 'abc12345', 't1', 'class_3a:s2'));
tg64_retro_save($cid, 'abc12345', 't1', 'class_3a:s1', ['Podruhé', '', ''], 1_790_000_100);
$check('retrospektiva: opětovné odeslání přepíše vlastní odpověď (nepřibývá řádek)', count(storage_read(tg64_retro_path())['abc12345']['teams']['t1']) === 1);
$badId = false;
try { tg64_retro_save($cid, '../x', 't1', 'class_3a:s1', ['a', 'b', 'c']); } catch (RuntimeException $e) { $badId = true; }
$check('retrospektiva: neplatné id hry se odmítne', $badId);
$check('retrospektiva: učitel bez oprávnění (bez relace v režimu s účty) nevidí nic', tg64_retro_for_teacher('abc12345') === [] || teacher59_mode() === 'legacy');
$check('retence: smazání starších než cutoff (dry-run nic nemění)', tg64_retro_purge(1_800_000_000, true) === 1 && isset(storage_read(tg64_retro_path())['abc12345']) && tg64_retro_purge(1_800_000_000) === 1 && storage_read(tg64_retro_path()) === []);
