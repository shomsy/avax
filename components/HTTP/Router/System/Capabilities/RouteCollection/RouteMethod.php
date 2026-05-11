<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteCollection;

use InvalidArgumentException;

/**
 * Canonical HTTP methods as a backed enum.
 *
 * Replaces bare string literals like 'GET', 'POST', etc.
 * with type-safe enum cases.
 */
enum RouteMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case HEAD = 'HEAD';

    /**
     * Create an HttpMethod from a string value.
     *
     * @throws InvalidArgumentException if the method is not a recognized HTTP method.
     */
    public static function fromString(string $method): self
    {
        $upper = strtoupper($method);

        return match ($upper) {
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'PATCH' => self::PATCH,
            'DELETE' => self::DELETE,
            'OPTIONS' => self::OPTIONS,
            'HEAD' => self::HEAD,
            default => throw new InvalidArgumentException("Unknown HTTP method: {$method}"),
        };
    }

    /**
     * Try to create a RouteMethod from a string, returning null if unknown.
     */
    public static function tryFromString(string $method) : self|null
    {
        $upper = strtoupper($method);

        return match ($upper) {
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'PATCH' => self::PATCH,
            'DELETE' => self::DELETE,
            'OPTIONS' => self::OPTIONS,
            'HEAD' => self::HEAD,
            default => null,
        };
    }
}
