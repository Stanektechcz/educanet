<?php

declare(strict_types=1);

/** v59 · OPS-02 – překlady UI (uk), doména study_logic (GPS kroky v44, režimy v48.1). msgid = český text ze zdroje. */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Předpověz' => 'Передбач',
    'Změň' => 'Зміни',
    'Sestav model' => 'Побудуй модель',
    'Vysvětli' => 'Поясни',
    'Použij jinde' => 'Застосуй деінде',
    'Nejdřív vytvoř hypotézu.' => 'Спершу сформулюй гіпотезу.',
    'Manipuluj modelem a sleduj důsledek.' => 'Змінюй модель і стеж за наслідком.',
    'Seřaď části podle skutečného vztahu.' => 'Упорядкуй частини за справжнім звʼязком.',
    'Popiš příčinu, důkaz a závěr.' => 'Опиши причину, доказ і висновок.',
    'Ověř princip v novém kontextu.' => 'Перевір принцип у новому контексті.',
    'Guided · více opory' => 'Guided · більше підтримки',
    'Challenge · minimum opory' => 'Challenge · мінімум підтримки',
    'Practice · vyváženě' => 'Practice · збалансовано',
];
