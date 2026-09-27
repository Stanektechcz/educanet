<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – katalog msgid domény „core_ui“ (angličtina).
 * Jádro (i18n_v58.php, i18n_v59.php) zatím nepoužívá tr()/trn() – přepínač jazyka má trojjazyčný
 * popisek napevno (Jazyk · Language · Мова) a tlačítko/poznámka běží přes starší t()
 * (lang/en/core.php, zachováno kvůli zpětné kompatibilitě). Prázdný katalog je platný stav:
 * pokrytí domény s nulou msgid je triviálně splněné (tools/v59_i18n_audit.php).
 * Vlastník: integrátor (i18n_core) – žádost o nové msgid v této doméně směřuj sem.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
];
