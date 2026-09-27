<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 migrace 0002 (Z2): každý profil žáka v learning_profiles dostane pole 'version' (výchozí 0),
 * podle kterého learning_save_profile() slučuje změny místo přepisu. Existující hodnoty (XP, odznaky…)
 * se nemění; profil, který už verzi má, zůstane beze změny → migrace je idempotentní.
 */

return [
    'id' => '0002_profile_versions',
    'description' => 'Profily žáků (learning_profiles) doplnit o pole version pro slučování zápisů (Z2).',
    'files' => ['learning_profiles.json.php'],
    'up' => static function (bool $dryRun): array {
        $path = STORAGE_DIR . '/learning_profiles.json.php';
        $missing = 0;
        foreach (storage_read($path) as $profile) {
            if (is_array($profile) && !array_key_exists('version', $profile)) $missing++;
        }
        $details = ['learning_profiles: profilů bez verze=' . $missing];
        if ($dryRun) return ['changed' => $missing > 0 ? 1 : 0, 'details' => $details];
        if ($missing > 0 && is_file($path)) {
            storage_update($path, static function (array $all): array {
                foreach ($all as $key => $profile) {
                    if (is_array($profile) && !array_key_exists('version', $profile)) $all[$key]['version'] = 0;
                }
                return $all;
            });
        }
        storage_schema_bump(['learning_profiles.json.php' => 2]);
        return ['changed' => $missing > 0 ? 1 : 0, 'details' => $details];
    },
];
