<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Capabilities\RouteDefinition;

/**
 * Value object representing a unique route identifier.
 */
final readonly class RouteKey
{
    public string $path;
    public string $domain;
    public string $method;

    public function __construct(string $method, string $domain, string $path)
    {
        $this->method = $method;
        $this->domain = $domain;
        $this->path   = $path;
    }

    public static function fromRoute(RouteDefinition $route) : self
    {
        return new self(
            method: strtoupper($route->method),
            domain: $route->domain ?? '',
            path  : $route->path
        );
    }

    public function toString() : string
    {
        return "{$this->method}|{$this->domain}|{$this->path}";
    }

    public function conflictsWith(self $other) : bool
    {
        return $this->method === $other->method &&
            $this->domain === $other->domain &&
            $this->path === $other->path;
    }

    public function conflictsWithAnyMethod(self $anyMethodKey) : bool
    {
        return $anyMethodKey->isAnyMethod() &&
            $this->domain === $anyMethodKey->domain &&
            $this->path === $anyMethodKey->path;
    }

    public function isAnyMethod() : bool { return $this->method === 'ANY'; }

    public function describe() : string
    {
        $domain = $this->domain ?: '(no domain)';

        return "[{$this->method}] {$this->path} @ {$domain}";
    }
}
