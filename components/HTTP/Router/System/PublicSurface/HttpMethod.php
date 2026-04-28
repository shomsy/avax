<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

/**
 * Valid HTTP methods supported by the Avax Router.
 */
enum HttpMethod: string
{
    case GET     = 'GET';
    case POST    = 'POST';
    case PUT     = 'PUT';
    case PATCH   = 'PATCH';
    case DELETE  = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case HEAD    = 'HEAD';
    case ANY     = 'ANY';

    /**
     * Checks if a method string is valid.
     */
    public static function isValid(string $method) : bool
    {
        $method = strtoupper(string: $method);

        foreach (self::cases() as $case) {
            if ($case->value === $method) {
                return true;
            }
        }

        return false;
    }
}
