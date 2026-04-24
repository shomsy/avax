<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Foundation\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a route exists but the HTTP method is not allowed.
 */
final class MethodNotAllowedException extends RuntimeException
{
    /** @var list<string> */
    public readonly array $allowedMethods;

    public function __construct(string $method, string $path, array $allowedMethods)
    {
        parent::__construct(message: "Method {$method} not allowed for {$path}");

        $this->allowedMethods = $allowedMethods;
    }

    public static function for(string $method, string $path, array $allowedMethods) : self
    {
        return new self(method: $method, path: $path, allowedMethods: $allowedMethods);
    }

}
