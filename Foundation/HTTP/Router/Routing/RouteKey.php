<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Value object representing a unique route identifier.
 *
 * Encapsulates the logic for route deduplication by combining method, domain,
 * and path into a canonical key. Supports configurable duplicate handling policies.
 */
final readonly class RouteKey
{
    public function __construct(
        public string $method,
        public string $domain,
        public string $path
    ) {}

    /**
     * Create RouteKey from a RouteDefinition.
     */
    public static function fromRoute(RouteDefinition $route) : self
    {
        return new self(
            method: strtoupper($route->method),
            domain: $route->domain ?? '',
            path: $route->path
        );
    }

    /**
     * Get the canonical string representation of this route key.
     *
     * Format: "METHOD|DOMAIN|PATH"
     */
    public function toString() : string
    {
        return "{$this->method}|{$this->domain}|{$this->path}";
    }

    /**
     * Check if this key conflicts with another route key.
     *
     * Two keys conflict if they have the same method, domain, and path.
     */
    public function conflictsWith(self $other) : bool
    {
        return $this->method === $other->method &&
               $this->domain === $other->domain &&
               $this->path === $other->path;
    }

    /**
     * Check if this key represents an ANY method route.
     */
    public function isAnyMethod() : bool
    {
        return $this->method === 'ANY';
    }

    /**
     * Check if this key would conflict with an ANY method route for the same path/domain.
     */
    public function conflictsWithAnyMethod(self $anyMethodKey) : bool
    {
        return $anyMethodKey->isAnyMethod() &&
               $this->domain === $anyMethodKey->domain &&
               $this->path === $anyMethodKey->path;
    }

    /**
     * Get a human-readable description of this route key.
     */
    public function describe() : string
    {
        $domain = $this->domain ?: '(no domain)';
        return "[{$this->method}] {$this->path} @ {$domain}";
    }
}