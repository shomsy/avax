<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a route exists but the HTTP method is not allowed.
 */
final class MethodNotAllowedException extends RuntimeException
{
    private array $allowedMethods;

    public function __construct(string $method, string $path, array $allowedMethods)
    {
        parent::__construct(message: "Method {$method} not allowed for {$path}");

        $this->allowedMethods = $allowedMethods;
    }

    public static function for(string $method, string $path, array $allowedMethods) : self
    {
        return new self(method: $method, path: $path, allowedMethods: $allowedMethods);
    }

    public function getAllowedMethods() : array
    {
        return $this->allowedMethods;
    }
}
