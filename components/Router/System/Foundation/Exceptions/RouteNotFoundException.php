<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Foundation\Exceptions;

/**
 * Thrown when a route is not found for a given request.
 */
final class RouteNotFoundException extends RouterException
{
    public function __construct(string $method, string $path, string $domain = '')
    {
        $message = "Route not found for [{$method}] {$path}";
        if ($domain !== '') {
            $message .= " on domain {$domain}";
        }
        parent::__construct($message, 404);
    }
}
