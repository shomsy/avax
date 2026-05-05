<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\CaseConversion;

/**
 * Static string utility class providing common string manipulation methods.
 */
final class Str
{
    /**
     * Convert a string to a URL-friendly slug.
     */
    public static function slug(string $value, string $separator = '-') : string
    {
        if (function_exists('transliterator_transliterate')) {
            $value = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $value);
        } elseif (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = strtolower($converted);
            }
        } else {
            $value = strtolower($value);
        }

        $value = preg_replace('/[^a-z0-9]+/', $separator, $value);

        return trim((string) $value, $separator);
    }

    /**
     * Get the plural form of a word.
     */
    public static function plural(string $value, int $count = 2) : string
    {
        if ($count === 1) {
            return $value;
        }

        $irregular = [
            'person'   => 'people', 'man' => 'men', 'woman' => 'women',
            'child'    => 'children', 'foot' => 'feet', 'tooth' => 'teeth',
            'goose'    => 'geese', 'mouse' => 'mice', 'ox' => 'oxen',
            'datum'    => 'data', 'medium' => 'media', 'analysis' => 'analyses',
            'basis'    => 'bases', 'crisis' => 'crises', 'diagnosis' => 'diagnoses',
            'ellipsis' => 'ellipses', 'hypothesis' => 'hypotheses',
            'oasis'    => 'oases', 'parenthesis' => 'parentheses',
            'phenomenon' => 'phenomena', 'criterion' => 'criteria',
        ];

        $lower = strtolower($value);
        if (isset($irregular[$lower])) {
            $replacement = $irregular[$lower];

            return $value === ucfirst($value) ? ucfirst($replacement) : $replacement;
        }

        if (preg_match('/(?:s|x|z|ch|sh)$/i', $value)) {
            return $value . 'es';
        }

        if (preg_match('/[^aeiou]y$/i', $value)) {
            return substr($value, 0, -1) . 'ies';
        }

        if (preg_match('/(fe|f)$/i', $value)) {
            if (str_ends_with(strtolower($value), 'fe')) {
                return substr($value, 0, -2) . 'ves';
            }

            return substr($value, 0, -1) . 'ves';
        }

        return $value . 's';
    }

    /**
     * Get the singular form of a word.
     */
    public static function singular(string $value) : string
    {
        $irregular = [
            'people'   => 'person', 'men' => 'man', 'women' => 'woman',
            'children' => 'child', 'feet' => 'foot', 'teeth' => 'tooth',
            'geese'    => 'goose', 'mice' => 'mouse', 'oxen' => 'ox',
            'data'     => 'datum', 'media' => 'medium', 'analyses' => 'analysis',
            'bases'    => 'basis', 'crises' => 'crisis', 'diagnoses' => 'diagnosis',
            'ellipses' => 'ellipsis', 'hypotheses' => 'hypothesis',
            'oases'    => 'oasis', 'parentheses' => 'parenthesis',
            'phenomena' => 'phenomenon', 'criteria' => 'criterion',
        ];

        $lower = strtolower($value);
        if (isset($irregular[$lower])) {
            $replacement = $irregular[$lower];

            return $value === ucfirst($value) ? ucfirst($replacement) : $replacement;
        }

        if (preg_match('/([^aeiou])ies$/i', $value)) {
            return substr($value, 0, -3) . 'y';
        }

        if (preg_match('/(ves)$/i', $value) && str_ends_with(strtolower($value), 'ves')) {
            $base = substr($value, 0, -3);
            if (str_ends_with(strtolower($base), 'li')) {
                return $base . 'fe';
            }

            return $base . 'f';
        }

        if (preg_match('/([sxz])es$/i', $value)) {
            return substr($value, 0, -2);
        }

        if (preg_match('/([a-z])s$/i', $value) && in_array(preg_match('/(?:us|ss)$/i', $value), [0, false], true)) {
            return substr($value, 0, -1);
        }

        return $value;
    }

    /**
     * Convert a string to camelCase.
     */
    public static function camel(string $value) : string
    {
        return lcfirst(self::studly($value));
    }

    /**
     * Convert a string to lower case.
     */
    public static function lower(string $value) : string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }

    /**
     * Convert a string to UPPER CASE.
     */
    public static function upper(string $value) : string
    {
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($value, 'UTF-8');
        }

        return strtoupper($value);
    }

    /**
     * Convert a string to StudlyCase (PascalCase).
     */
    public static function studly(string $value) : string
    {
        $words = preg_split('/[\s\-_]+/', $value);
        if ($words === false) {
            $words = [$value];
        }

        return implode('', array_map(static fn (string $w) : string => ucfirst(strtolower($w)), $words));
    }

    /**
     * Convert a string to kebab-case.
     */
    public static function kebab(string $value) : string
    {
        return self::snake($value, '-');
    }

    /**
     * Convert a string to snake_case.
     */
    public static function snake(string $value, string $delimiter = '_') : string
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1' . $delimiter . '$2', $value);
        $value = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1' . $delimiter . '$2', (string) $value);
        $value = preg_replace('/[\s\-]+/', $delimiter, (string) $value);

        return strtolower((string) $value);
    }

    /**
     * Convert a string to Headline Case (Title Case with spaces).
     */
    public static function headline(string $value) : string
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $value);
        $value = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', (string) $value);
        $value = preg_replace('/[\s\-_]+/', ' ', (string) $value);

        return ucwords(strtolower(trim((string) $value)));
    }

    /**
     * Determine if a string contains a given value.
     */
    public static function contains(string $haystack, string|array $needles, bool $caseSensitive = true) : bool
    {
        $needles = is_array($needles) ? $needles : [$needles];

        foreach ($needles as $needle) {
            if ($caseSensitive) {
                if (str_contains($haystack, (string) $needle)) {
                    return true;
                }
            } elseif (stripos($haystack, (string) $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a string starts with a given value.
     */
    public static function startsWith(string $haystack, string|array $needles) : bool
    {
        $needles = is_array($needles) ? $needles : [$needles];

        return array_any($needles, fn ($needle) : bool => str_starts_with($haystack, (string) $needle));
    }

    /**
     * Determine if a string ends with a given value.
     */
    public static function endsWith(string $haystack, string|array $needles) : bool
    {
        $needles = is_array($needles) ? $needles : [$needles];

        return array_any($needles, fn ($needle) : bool => str_ends_with($haystack, (string) $needle));
    }

    /**
     * Limit the number of characters in a string.
     */
    public static function limit(string $value, int $limit = 100, string $end = '...') : string
    {
        if (mb_strlen($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')) . $end;
    }

    /**
     * Get a limited excerpt from a string, ensuring word boundaries.
     */
    public static function excerpt(string $value, int $length = 200, string $suffix = '...') : string
    {
        if (mb_strlen($value, 'UTF-8') <= $length) {
            return $value;
        }

        $truncated = mb_substr($value, 0, $length, 'UTF-8');
        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');

        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
        }

        return rtrim($truncated) . $suffix;
    }

    /**
     * Generate a random alphanumeric string.
     */
    public static function random(int $length = 16) : string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max   = strlen($chars) - 1;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    /**
     * Generate a UUID v4.
     */
    public static function uuid() : string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Check if string is a valid UUID.
     */
    public static function isUuid(string $value) : bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
