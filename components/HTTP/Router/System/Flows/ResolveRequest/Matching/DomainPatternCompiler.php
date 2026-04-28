<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching;

/**
 * Domain pattern compiler for dynamic domain matching.
 */
final class DomainPatternCompiler
{
    public static function compile(string $pattern) : string
    {
        $escaped = preg_quote(str: $pattern, delimiter: '/');
        $regex   = preg_replace_callback(
            pattern : '/\\{(\w+)}/',
            callback: static fn (array $match) : string => '(?P<' . $match[1] . '>[\w\-\.]+)',
            subject : $escaped
        );

        return '/^' . $regex . '$/i';
    }

    public static function match(string $host, string $compiled) : bool
    {
        return (bool) preg_match(pattern: $compiled, subject: $host);
    }
}
