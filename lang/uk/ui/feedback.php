<?php

declare(strict_types=1);

/**
 * v60 · katalog msgid domény „feedback“ (ukrajinsky) – nahlášení chyby / návrh vylepšení.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'Chyba' => 'Помилка',
    'Návrh vylepšení' => 'Пропозиція покращення',
    'Čeká na posouzení' => 'Очікує на розгляд',
    'Potvrzeno' => 'Підтверджено',
    'Zamítnuto' => 'Відхилено',
    'Duplicita' => 'Дублікат',
    'Vyřešeno' => 'Вирішено',
    'Vyber, jestli jde o chybu, nebo o návrh vylepšení.' => 'Обери, це помилка чи пропозиція покращення.',
    'Titulek je moc krátký (aspoň {n} znaků).' => 'Заголовок надто короткий (щонайменше {n} символів).',
    'Titulek je moc dlouhý (nejvýš {n} znaků).' => 'Заголовок надто довгий (щонайбільше {n} символів).',
    'Popiš to podrobněji (aspoň {n} znaků).' => 'Опиши детальніше (щонайменше {n} символів).',
    'Popis je moc dlouhý (nejvýš {n} znaků).' => 'Опис надто довгий (щонайбільше {n} символів).',
    'Dnes jsi poslal/a maximum hlášení. Zkus to zítra.' => 'Сьогодні ти вже надіслав/-ла максимум повідомлень. Спробуй завтра.',
    'Hlášení se stejným titulkem už jsi poslal/a.' => 'Ти вже надсилав/-ла повідомлення з таким самим заголовком.',
    'Hlášení se nepodařilo odeslat.' => 'Не вдалося надіслати повідомлення.',
    'Hlášení' => 'Повідомлення',
    'Nahlásit chybu nebo navrhnout vylepšení' => 'Повідомити про помилку або запропонувати покращення',
    'Pomoz nám zlepšit Educanet. Když učitel hlášení potvrdí, dostaneš body a XP (chyba obvykle {bp} b / {bx} XP, návrh {ip} b / {ix} XP).' => 'Допоможи нам покращити Educanet. Коли вчитель підтвердить повідомлення, ти отримаєш бали та XP (помилка зазвичай {bp} б / {bx} XP, пропозиція {ip} б / {ix} XP).',
    'Nové hlášení' => 'Нове повідомлення',
    'Co posíláš' => 'Що ти надсилаєш',
    'Stránka: {page}' => 'Сторінка: {page}',
    'Titulek' => 'Заголовок',
    'Popis' => 'Опис',
    'U chyby napiš, co jsi dělal/a a co se stalo. Nepiš hesla ani osobní údaje. Dnes můžeš poslat ještě {n} hlášení.' => 'Якщо це помилка, напиши, що ти робив/-ла і що сталося. Не пиши паролі та особисті дані. Сьогодні ти можеш надіслати ще {n} повідомлень.',
    'Odeslat hlášení' => 'Надіслати повідомлення',
    'Moje hlášení' => 'Мої повідомлення',
    'Zatím jsi nic neposlal/a.' => 'Ти ще нічого не надсилав/-ла.',
    'Odměna: {p} b a {x} XP' => 'Винагорода: {p} б і {x} XP',
    'Poznámka učitele' => 'Примітка вчителя',
    'Nahlášené chyby' => 'Повідомлені помилки',
    'Nahlášené chyby a návrhy' => 'Повідомлені помилки та пропозиції',
    'Nahlášeno: {t} · potvrzeno: {c}' => 'Повідомлено: {t} · підтверджено: {c}',
    'odměna celkem: {p} b a {x} XP' => 'загальна винагорода: {p} б і {x} XP',
    'Nahlásit chybu nebo návrh' => 'Повідомити про помилку чи ідею',
    'Nahlásit chybu' => 'Повідомити про помилку',
    'Díky! Hlášení je odeslané, učitel ho posoudí.' => 'Дякуємо! Повідомлення надіслано, вчитель його розгляне.',
];
