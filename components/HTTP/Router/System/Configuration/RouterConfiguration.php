<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Configuration;

/**
 * Immutable configuration object for the Router component.
 *
 * Holds routing settings such as cache enablement, strict mode,
 * URL generation defaults, and route prefixes.
 */
final readonly class RouterConfiguration
{
    /**
     * @param  string  $prefix  Global URL prefix for all routes
     * @param  bool  $cacheEnabled  Whether route matching cache is enabled
     * @param  bool  $strictMode  Whether to enforce strict matching (no trailing slashes)
     * @param  array<string, mixed>  $defaults  Default route parameters
     * @param  int  $maxRouteCount  Maximum number of routes allowed
     */
    public function __construct(
        private string $prefix = '',
        private bool $cacheEnabled = true,
        private bool $strictMode = false,
        private array $defaults = [],
        private int $maxRouteCount = 1000,
    ) {
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    public function isStrictMode(): bool
    {
        return $this->strictMode;
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return $this->defaults;
    }

    public function maxRouteCount(): int
    {
        return $this->maxRouteCount;
    }

    /**
     * Create a new configuration with merged overrides.
     *
     * @param  array<string, mixed>  $overrides
     */
    public function with(array $overrides): self
    {
        return new self(
            prefix       : $overrides['prefix'] ?? $this->prefix,
            cacheEnabled : $overrides['cache_enabled'] ?? $this->cacheEnabled,
            strictMode   : $overrides['strict_mode'] ?? $this->strictMode,
            defaults     : $overrides['defaults'] ?? $this->defaults,
            maxRouteCount: $overrides['max_route_count'] ?? $this->maxRouteCount,
        );
    }

    /**
     * Create configuration from an array.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            prefix       : $config['prefix'] ?? '',
            cacheEnabled : $config['cache_enabled'] ?? true,
            strictMode   : $config['strict_mode'] ?? false,
            defaults     : $config['defaults'] ?? [],
            maxRouteCount: $config['max_route_count'] ?? 1000,
        );
    }
}
