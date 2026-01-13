<?php

declare(strict_types=1);

use Avax\Text\MatchResult;
use Avax\Text\Pattern;
use Avax\Text\Text;

/**
 * Create Text instance from string.
 */
function text(string $value) : Text
{
    return Text::of(value: $value);
}

/**
 * Create Text instance from nullable string.
 */
function t(string|null $value, string $default = '') : Text
{
    return Text::fromNullable(value: $value, default: $default);
}

/**
 * Functional pipe for string transformations.
 */
function pipe(string $value, callable $fn) : string
{
    return $fn(Text::of(value: $value))->toString();
}

/**
 * Trim whitespace from string.
 */
function trimmed(string $value) : string
{
    return Text::of(value: $value)->trim()->toString();
}

/**
 * Create URL slug from string.
 */
function slug(string $value, string $separator = '-') : string
{
    return Text::of(value: $value)->slug(separator: $separator)->toString();
}

/**
 * Convert to camelCase.
 */
function camel(string $value) : string
{
    return Text::of(value: $value)->camel()->toString();
}

/**
 * Convert to snake_case.
 */
function snake(string $value) : string
{
    return Text::of(value: $value)->snake()->toString();
}

/**
 * Limit string length with suffix.
 */
function limit(string $value, int $max, string $suffix = '…') : string
{
    return Text::of(value: $value)->limit(max: $max, suffix: $suffix)->toString();
}

/**
 * Get text before delimiter.
 */
function before(string $value, string $needle) : string
{
    return Text::of(value: $value)->before(needle: $needle)->toString();
}

/**
 * Get text after delimiter.
 */
function after(string $value, string $needle) : string
{
    return Text::of(value: $value)->after(needle: $needle)->toString();
}

/**
 * Get text between delimiters.
 */
function between(string $value, string $left, string $right) : string
{
    return Text::of(value: $value)->between(left: $left, right: $right)->toString();
}

/**
 * Ensure string starts with prefix.
 */
function ensure_prefix(string $value, string $prefix) : string
{
    return Text::of(value: $value)->ensurePrefix(prefix: $prefix)->toString();
}

/**
 * Ensure string ends with suffix.
 */
function ensure_suffix(string $value, string $suffix) : string
{
    return Text::of(value: $value)->ensureSuffix(suffix: $suffix)->toString();
}

/**
 * Create Pattern instance.
 */
function rx(string $pattern, string $flags = '') : Pattern
{
    return Pattern::of(raw: $pattern, flags: $flags);
}

/**
 * Test regex pattern against string.
 *
 * @param string $pattern Raw regex pattern (without delimiters)
 * @param string $subject String to test against
 * @param string $flags   PCRE flags (e.g., 'i', 'u', 'm')
 */
function rx_test(string $pattern, string $subject, string $flags = '') : bool
{
    return Pattern::of(raw: $pattern, flags: $flags)->test(subject: $subject);
}

/**
 * Match regex pattern against string.
 *
 * @param string $pattern Raw regex pattern (without delimiters)
 * @param string $subject String to match against
 * @param string $flags   PCRE flags (e.g., 'i', 'u', 'm')
 */
function rx_match(string $pattern, string $subject, string $flags = '') : MatchResult
{
    return Pattern::of(raw: $pattern, flags: $flags)->match(subject: $subject);
}

/**
 * Replace with regex pattern.
 *
 * @param string $pattern     Raw regex pattern (without delimiters)
 * @param string $replacement Replacement string
 * @param string $subject     String to replace in
 * @param string $flags       PCRE flags (e.g., 'i', 'u', 'm')
 */
function rx_replace(string $pattern, string $replacement, string $subject, string $flags = '') : string
{
    return Pattern::of(raw: $pattern, flags: $flags)->replace(subject: $subject, replacement: $replacement);
}

/**
 * Replace with regex pattern using callback.
 *
 * @param string   $pattern Raw regex pattern (without delimiters)
 * @param string   $subject String to replace in
 * @param callable $fn      Callback function receiving matches
 * @param string   $flags   PCRE flags (e.g., 'i', 'u', 'm')
 */
function rx_replace_callback(string $pattern, string $subject, callable $fn, string $flags = '') : string
{
    return Pattern::of(raw: $pattern, flags: $flags)->replaceCallback(subject: $subject, fn: $fn);
}

/**
 * Split string by regex pattern.
 *
 * @param string $pattern Raw regex pattern (without delimiters)
 * @param string $subject String to split
 * @param string $flags   PCRE flags (e.g., 'i', 'u', 'm')
 */
function rx_split(string $pattern, string $subject, string $flags = '') : array
{
    return Pattern::of(raw: $pattern, flags: $flags)->split(subject: $subject);
}