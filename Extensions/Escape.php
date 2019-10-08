<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Regular expressions from Latte (https://latte.nette.org)
 * @author Lukáš Piják 2018
 */
class Escape
{
    public static function html($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }

    public static function js($s)
    {
        return str_replace(
            ["\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'],
            ['\u2028', '\u2029', ']]\x3E', '\x3C!'],
            json_encode($s, JSON_UNESCAPED_UNICODE)
        );
    }

    public static function url($s)
    {
        $s = (string) $s;
        return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|mailto:.+|[/?#].*|[^:]+)\z~i', $s) ?
            self::htmlAttr($s) : '';
    }

    public static function htmlAttr($s, $double = true)
    {
        $s = (string) $s;
        if (strpos($s, '`') !== false && strpbrk($s, ' <>"\'') === false) {
            $s .= ' '; // protection against innerHTML mXSS vulnerability nette/nette#1496
        }
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8', $double);
    }
}
