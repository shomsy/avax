<?php

declare(strict_types=1);

namespace components\HTTP\Router\System\Foundation\Exceptions;

use Exception;

/**
 * Thrown when attempting to register a duplicate route.
 */
final class DuplicateRouteException extends Exception
{
    public function __construct(string $method, string $path, string|null $domain = null, string|null $name = null)
    {
        $key = $this->buildKey(method: $method, path: $path, domain: $domain, name: $name);
        parent::__construct(message: "Duplicate route registration: {$key}");
    }

    private function buildKey(string $method, string $path, string|null $domain, string|null $name) : string
    {
        $parts = [$method, $domain ?? '', $path];
        if ($name !== null) {
            $parts[] = "name:{$name}";
        }

        return implode(separator: '|', array: $parts);
    }
}