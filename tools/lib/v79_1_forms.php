<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v79.1 · kontrola polí formulářů na vykreslené stránce (pro tools/v79_1_content_audit.php; čistá funkce nad HTML).
 * Pravidla: každé viditelné pole má popisek (label[for], obalující label, aria-label, aria-labelledby); e-mail = type=email + autocomplete;
 * heslo = autocomplete current-password|new-password; type=number = inputmode numeric|decimal; adresa (name/id url|odkaz|link) = type=url
 * nebo inputmode=url; jméno učitele = autocomplete name|username; kód (name=code) = autocomplete off|one-time-code + autocapitalize;
 * vyhledávání (type=search) = inputmode search nebo bez inputmode. Pole uvnitř <template> a skrytá pole se nekontrolují.
 * @return array{fields:int,list:list<string>}
 */
function v791_form_issues(string $html): array
{
    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $xp = new DOMXPath($doc);
    $labels = [];
    foreach ($xp->query('//label[@for]') ?: [] as $l) $labels[(string)$l->getAttribute('for')] = true;
    $issues = [];
    $n = 0;
    foreach ($xp->query('//input | //select | //textarea') ?: [] as $el) {
        /** @var DOMElement $el */
        $tag = strtolower($el->nodeName);
        $type = $tag === 'input' ? strtolower($el->getAttribute('type') ?: 'text') : $tag;
        if (in_array($type, ['hidden', 'submit', 'button', 'reset', 'image'], true)) continue;
        if ($xp->query('ancestor::template', $el)->length > 0) continue;
        $n++;
        $name = $el->getAttribute('name');
        $id = $el->getAttribute('id');
        $who = $tag . '[' . ($name !== '' ? 'name=' . $name : ($id !== '' ? 'id=' . $id : 'type=' . $type)) . ']';
        $ac = strtolower(trim($el->getAttribute('autocomplete')));
        $im = strtolower(trim($el->getAttribute('inputmode')));
        $labelled = ($id !== '' && isset($labels[$id])) || $xp->query('ancestor::label', $el)->length > 0 || $el->getAttribute('aria-label') !== '' || $el->getAttribute('aria-labelledby') !== '' || $el->getAttribute('title') !== '';
        if (!$labelled) $issues[] = $who . ': chybí popisek';
        if ($type === 'email' && $ac === '') $issues[] = $who . ': e-mail bez autocomplete';
        if ($type === 'password' && !in_array($ac, ['current-password', 'new-password', 'one-time-code'], true)) $issues[] = $who . ': heslo bez autocomplete current/new-password';
        if ($type === 'number' && !in_array($im, ['numeric', 'decimal'], true)) $issues[] = $who . ': číslo bez inputmode numeric/decimal';
        if (in_array($type, ['text', 'url'], true) && preg_match('/(^|_|-)(url|odkaz|link|href)(_|-|$)/i', $name . ' ' . $id) === 1 && $type !== 'url' && $im !== 'url') $issues[] = $who . ': adresa bez type=url / inputmode=url';
        if ($name === 'teacher_name' && !in_array($ac, ['name', 'username'], true)) $issues[] = $who . ': jméno bez autocomplete name/username';
        if ($name === 'code' && $type === 'text' && (!in_array($ac, ['off', 'one-time-code'], true) || $el->getAttribute('autocapitalize') === '')) $issues[] = $who . ': kód bez autocomplete off/one-time-code a autocapitalize';
    }
    return ['fields' => $n, 'list' => $issues];
}
