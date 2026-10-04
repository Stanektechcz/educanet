<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · předpočítání výstupů kroků „predict–run–explain“ (jen nástroje a audit, nikdy za běhu aplikace).
 *
 * Simulátor Linuxu je čisté PHP (nic nespouští, nikam se nepřipojuje). Pro každý případ kroku typu pre se v čerstvém
 * světě s pevným semínkem a pevným časem provedou přípravné příkazy (setup) a pak příkaz cmd; výstup se uloží do
 * paths_v63_pre_data.php. Aplikace výstup jen čte z datového souboru.
 */

const V63_PRE_SEED = 'p63-fixed';
const V63_PRE_NOW = 1790000000;

/** Výstup příkazu (stdout + stderr bez barevných escape sekvencí) v čerstvém světě po provedení setup příkazů. */
function v63_pre_compute(array $case): string
{
    $world = lab57_world_new(V63_PRE_SEED, V63_PRE_NOW);
    foreach ((array)($case['setup'] ?? []) as $line) lab57_run_line($world, (string)$line);
    $run = lab57_run_line($world, (string)$case['cmd']);
    $text = '';
    foreach ($run['chunks'] as $chunk) $text .= (string)$chunk[1];
    return rtrim((string)preg_replace('/\x1b\[[0-9;]*m/', '', $text), "\n");
}

/** Klíč případu v datovém souboru. */
function v63_pre_key(string $pathId, string $stepId, string $caseId): string
{
    return $pathId . '|' . $stepId . '|' . $caseId;
}

/** Všechny případy typu pre bez statického režimu: klíč => ['cmd' => …, 'out' => …]. @return array<string,array{cmd:string,out:string}> */
function v63_pre_all(): array
{
    $out = [];
    foreach (p63_paths() as $pathId => $path) {
        foreach ((array)$path['steps'] as $step) {
            if ((string)($step['type'] ?? '') !== 'pre') continue;
            foreach ((array)$step['cases'] as $case) {
                if (!empty($case['static'])) continue;
                $out[v63_pre_key((string)$pathId, (string)$step['id'], (string)$case['id'])] = ['cmd' => (string)$case['cmd'], 'out' => v63_pre_compute($case)];
            }
        }
    }
    ksort($out);
    return $out;
}

/** Obsah datového souboru paths_v63_pre_data.php. */
function v63_pre_render(array $outputs): string
{
    return "<?php\n\ndeclare(strict_types=1);\nif (basename((string)(\$_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }\n\n"
        . "/**\n * EDUCANET v63 · předpočítané výstupy kroků predict–run–explain (GENEROVANÝ soubor – needitovat).\n"
        . " * Znovu vytvoří: php tools/v63_paths_build_pre.php --apply. Výstupy vznikly v simulátoru Linuxu (čisté PHP) s pevným semínkem a časem;\n"
        . " * za běhu aplikace se nic nespouští. Soubor je datový (výjimka z limitu 800 řádků v BUILD_MANIFEST_V63.md).\n */\n\n"
        . 'return ' . var_export(['seed' => V63_PRE_SEED, 'now' => V63_PRE_NOW, 'outputs' => $outputs], true) . ";\n";
}
