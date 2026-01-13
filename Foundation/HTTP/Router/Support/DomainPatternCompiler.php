<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

/**
 * Class DomainPatternCompiler
 *
 * Provides methods for compiling dynamic domain patterns into regular expressions
 * and matching incoming host strings against compiled patterns.
 *
 * This utility is often used in routing systems to handle dynamic subdomains
 * or structured domain hierarchies.
 */
final class DomainPatternCompiler
{
    /**
     * Compiles a domain pattern string into a corresponding regular expression
     * to facilitate dynamic domain matching.
     *
     * Dynamic patterns are denoted using placeholders, e.g., `{account}.example.com`,
     * where `{account}` can match any subdomain name.
     *
     * @param string $pattern    The domain pattern string to compile.
     *                           Placeholders should be wrapped in curly braces, e.g., `{name}`.
     *
     * @return string The compiled regular expression string, ready for use in pattern matching.
     *                Example: `{account}.example.com` becomes `/^(?P<account>[\w\-.]+)\.example\.com$/i`.
     */
    public static function compile(string $pattern) : string
    {
        // Escape all special characters in the input domain pattern to ensure regex safety.
        $escaped = preg_quote(str: $pattern, delimiter: '/');

        // Transform placeholders (e.g., `{account}`) into named capturing groups in the regex pattern.
        // - \w matches word characters (a-z, A-Z, 0-9, and underscore).
        // - \- and \. Allow hyphen or dot in the subdomain portion.
        // Example: `{account}` becomes `(?P<account>[\w\-\.]+)`.
        $regex = preg_replace_callback(
            pattern : '/\\{(\w+)}/',
            // Matches `{placeholder_name}` where placeholders are word characters.
            callback: static fn(array $match) : string => '(?P<' . $match[1] . '>[\w\-\.]+)',
            // Replace it with a named group.
            subject : $escaped // Apply on the escaped string.
        );

        // Wrap the generated regex in delimiters, enforce case-insensitivity, and ensure it matches the full string.
        return '/^' . $regex . '$/i';
    }

    /**
     * Matches a host string against a precompiled domain regular expression.
     *
     * This method is used to determine if an incoming host (e.g., `x.example.com`) aligns
     * with the compiled domain pattern (e.g., `/^(?P<account>[\w\-\.]+)\.example\.com$/i`).
     *
     * @param string $host     The incoming host string to evaluate (e.g., `foo.example.com`).
     * @param string $compiled The precompiled domain regex (result from `compile`).
     *
     * @return bool Returns `true` if the host matches the regex, `false` otherwise.
     */
    public static function match(string $host, string $compiled) : bool
    {
        // Use preg_match to check if the host matches the compiled domain pattern.
        // Casting to boolean simplifies the return value to true/false.
        return (bool) preg_match(pattern: $compiled, subject: $host);
    }
}
