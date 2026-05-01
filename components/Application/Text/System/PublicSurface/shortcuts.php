<?php

declare(strict_types=1);

/**
 * Text processing shortcuts for global access.
 */

use Avax\Components\Application\Text\MatchResult;
use Avax\Components\Application\Text\Pattern;
use Avax\Components\Application\Text\Text;

if (! function_exists('text')) {
    /**
     * Create Text instance from string.
     *
     *
     * @return Text
     */
    function text(string $value) : Text
    {
        return Text::of($value);
    }
}

if (! function_exists('t')) {
    /**
     * Create Text instance from nullable string.
     *
     *
     * @return Text
     */
    function t(string|null $value, string $default = '') : Text
    {
        return Text::fromNullable($value, $default);
    }
}

if (! function_exists('pipe')) {
    /**
     * Functional pipe for string transformations.
     *
     *
     */
    function pipe(string $value, callable $fn) : string
    {
        return $fn(Text::of($value))->toString();
    }
}

if (! function_exists('trimmed')) {
    /**
     * Trim whitespace from string.
     *
     *
     */
    function trimmed(string $value) : string
    {
        return Text::of($value)->trim()->toString();
    }
}

if (! function_exists('slug')) {
    /**
     * Create URL slug from string.
     *
     *
     */
    function slug(string $value, string $separator = '-') : string
    {
        return Text::of($value)->slug($separator)->toString();
    }
}

if (! function_exists('camel')) {
    /**
     * Convert to camelCase.
     *
     *
     */
    function camel(string $value) : string
    {
        return Text::of($value)->camel()->toString();
    }
}

if (! function_exists('snake')) {
    /**
     * Convert to snake_case.
     *
     *
     */
    function snake(string $value) : string
    {
        return Text::of($value)->snake()->toString();
    }
}

if (! function_exists('limit')) {
    /**
     * Limit string length with suffix.
     *
     *
     */
    function limit(string $value, int $max, string $suffix = '…') : string
    {
        return Text::of($value)->limit($max, $suffix)->toString();
    }
}

if (! function_exists('before')) {
    /**
     * Get text before delimiter.
     *
     *
     */
    function before(string $value, string $needle) : string
    {
        return Text::of($value)->before($needle)->toString();
    }
}

if (! function_exists('after')) {
    /**
     * Get text after delimiter.
     *
     *
     */
    function after(string $value, string $needle) : string
    {
        return Text::of($value)->after($needle)->toString();
    }
}

if (! function_exists('between')) {
    /**
     * Get text between delimiters.
     *
     *
     */
    function between(string $value, string $left, string $right) : string
    {
        return Text::of($value)->between($left, $right)->toString();
    }
}

if (! function_exists('ensure_prefix')) {
    /**
     * Ensure string starts with prefix.
     *
     *
     */
    function ensure_prefix(string $value, string $prefix) : string
    {
        return Text::of($value)->ensurePrefix($prefix)->toString();
    }
}

if (! function_exists('ensure_suffix')) {
    /**
     * Ensure string ends with suffix.
     *
     *
     */
    function ensure_suffix(string $value, string $suffix) : string
    {
        return Text::of($value)->ensureSuffix($suffix)->toString();
    }
}

if (! function_exists('rx')) {
    /**
     * Create Pattern instance.
     *
     *
     * @return Pattern
     */
    function rx(string $pattern, string $flags = '') : Pattern
    {
        return Pattern::of($pattern, $flags);
    }
}

if (! function_exists('rx_test')) {
    /**
     * Test regex pattern against string.
     *
     *
     */
    function rx_test(string $pattern, string $subject, string $flags = '') : bool
    {
        return Pattern::of($pattern, $flags)->test($subject);
    }
}

if (! function_exists('rx_match')) {
    /**
     * Match regex pattern against string.
     *
     *
     */
    function rx_match(string $pattern, string $subject, string $flags = '') : MatchResult
    {
        return Pattern::of($pattern, $flags)->match($subject);
    }
}

if (! function_exists('rx_replace')) {
    /**
     * Replace with regex pattern.
     *
     *
     */
    function rx_replace(string $pattern, string $replacement, string $subject, string $flags = '') : string
    {
        return Pattern::of($pattern, $flags)->replace($subject, $replacement);
    }
}

if (! function_exists('rx_replace_callback')) {
    /**
     * Replace with regex pattern using callback.
     *
     *
     */
    function rx_replace_callback(string $pattern, string $subject, callable $fn, string $flags = '') : string
    {
        return Pattern::of($pattern, $flags)->replaceCallback($subject, $fn);
    }
}

if (! function_exists('rx_split')) {
    /**
     * Split string by regex pattern.
     *
     *
     */
    function rx_split(string $pattern, string $subject, string $flags = '') : array
    {
        return Pattern::of($pattern, $flags)->split($subject);
    }
}

if (! function_exists('preview_text')) {
    /**
     * Shortens the given text for preview purposes.
     *
     *
     */
    function preview_text(string $text, int $limit = 80) : string
    {
        $text = strip_tags($text);

        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit - 3) . '...'
            : $text;
    }
}
