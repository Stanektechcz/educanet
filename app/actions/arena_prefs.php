<?php

declare(strict_types=1);

/**
 * POST arena58_rank_toggle – v58 · ARN-06: žákovský přepínač „Nechci vidět pořadí“ pro daný závod.
 * CSRF už ověřil index.php. Identita jen ze session (adaptive_student_key); race id z POST se ověří
 * uvnitř arena57_toggle_hide_rank() proti $classId, takže cizí race_id nic nezmění.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'arena58_rank_toggle') {
    $arenaClass = current_class_id($modules);
    if ($arenaClass === null) { $_SESSION['flash'] = tr('Nejdřív se přihlas.'); redirect_to('?view=home'); }
    $arenaKey = function_exists('adaptive_student_key') ? adaptive_student_key((string)$arenaClass) : '';
    if ($arenaKey === '') { $_SESSION['flash'] = tr('Nepodařilo se určit studentský profil.'); redirect_to('?view=lab'); }
    $arenaRaceId = (string)($_POST['race'] ?? '');
    if (function_exists('arena57_toggle_hide_rank')) arena57_toggle_hide_rank($arenaRaceId, (string)$arenaClass, $arenaKey);
    redirect_to('?view=lab&zavod=' . rawurlencode($arenaRaceId));
}
