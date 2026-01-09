<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

/**
 * Enum representing HTTP methods.
 *
 * This provides a type-safe representation of allowed HTTP methods
 * for routing and validation purposes.
 */
enum HttpMethod: string
{
    case GET = 'GET';

    case POST = 'POST';

    case PUT = 'PUT';

    case DELETE = 'DELETE';

    case PATCH = 'PATCH';

    case OPTIONS = 'OPTIONS';

    case HEAD = 'HEAD';
    case ANY = 'ANY';

    /**
     * Validates if a given string matches a valid HTTP method.
     *
     * @param string $method The HTTP method to validate.
     *
     * @return bool True if valid, false otherwise.
     */
    public static function isValid(string $method): bool
    {
        return in_array(needle: strtoupper(string: $method), haystack: array_column(array: self::cases(), column_key: 'value'), strict: true);
    }

    /**
     * Returns a list of all HTTP methods as strings.
     *
     * @return array<string>
     */
    public static function list(): array
    {
        return array_map(callback: static fn(HttpMethod $method) => $method->value, array: self::cases());
    }
}
