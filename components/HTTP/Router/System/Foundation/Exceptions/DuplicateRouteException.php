<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Foundation\Exceptions;

/**
 * Thrown when a duplicate route registration is attempted.
 */
final class DuplicateRouteException extends RouterException
{
    public function __construct(string $method, string $path, string|null $domain = null, string|null $name = null)
    {
        $message = "Duplicate route detected: [{$method}] {$path}";
        if ($domain !== null) $message .= " @ {$domain}";
        if ($name !== null) $message .= " (named: {$name})";
        parent::__construct($message);
    }
}
