<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Foundation\Exceptions;

/**
 * Thrown when a route matches the path but not the HTTP method.
 */
final class MethodNotAllowedException extends RouterException
{
    public function __construct(string $method, array $allowedMethods = [])
    {
        $message = "Method {$method} not allowed.";
        if (! empty($allowedMethods)) {
            $message .= ' Allowed methods: ' . implode(', ', $allowedMethods);
        }
        parent::__construct($message, 405);
    }
}
