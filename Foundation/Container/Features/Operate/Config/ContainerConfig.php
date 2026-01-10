<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Config;

use Avax\Cache\CacheManager;
use Avax\Logging\LoggerFactory;

/**
 * Immutable configuration container for Avax Dependency Injection runtime settings.
 *
 * This Data Transfer Object (DTO) encapsulates all configurable aspects of the container's
 * operational behavior, providing a centralized and type-safe way to control caching,
 * debugging, security, and performance limits.
 *
 * @package Avax\Container\Features\Operate\Config
 * @see docs/Features/Operate/Config/ContainerConfig.md
 */
class ContainerConfig
{
    /**
     * Initializes the container configuration.
     *
     * @param string|null         $cacheDir           Absolute path to cache directory.
     * @param bool                $debug              Enable verbose debugging.
     * @param bool                $strict             Enable strict security mode.
     * @param int                 $maxResolutionDepth Maximum recursion depth for resolution.
     * @param CacheManager|null   $cacheManager       Custom cache manager implementation.
     * @param LoggerFactory|null  $loggerFactory      Custom logger factory implementation.
     *
     * @see docs/Features/Operate/Config/ContainerConfig.md#method-__construct
     */
    public function __construct(
        public readonly string|null         $cacheDir = null,
        public readonly bool                $debug = false,
        public readonly bool                $strict = true,
        public readonly int                 $maxResolutionDepth = 50,
        public readonly CacheManager|null   $cacheManager = null,
        public readonly LoggerFactory|null  $loggerFactory = null
    ) {}

    /**
     * Create a default production configuration.
     *
     * @return self Optimized for performance and security.
     * @see docs/Features/Operate/Config/ContainerConfig.md#method-production
     */
    public static function production(): self
    {
        return new self(
            debug: false,
            strict: true,
            maxResolutionDepth: 50
        );
    }

    /**
     * Create a default development configuration.
     *
     * @return self Optimized for debugging and flexibility.
     * @see docs/Features/Operate/Config/ContainerConfig.md#method-development
     */
    public static function development(): self
    {
        return new self(
            debug: true,
            strict: false,
            maxResolutionDepth: 100
        );
    }

    /**
     * Create a default testing configuration.
     *
     * @return self Optimized for deterministic test runs.
     * @see docs/Features/Operate/Config/ContainerConfig.md#method-testing
     */
    public static function testing(): self
    {
        return new self(
            cacheDir: null, // Disable file cache in testing by default
            debug: true,
            strict: true,
            maxResolutionDepth: 25
        );
    }

    /**
     * Create a configuration instance from a raw array.
     *
     * @param array<string, mixed> $data Configuration data.
     * @return self Hydrated configuration instance.
     * @see docs/Features/Operate/Config/ContainerConfig.md#method-fromarray
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cacheDir: $data['cache_dir'] ?? null,
            debug: $data['debug'] ?? false,
            strict: $data['strict'] ?? true,
            maxResolutionDepth: $data['max_depth'] ?? 50,
            cacheManager: $data['cache_manager'] ?? null,
            loggerFactory: $data['logger_factory'] ?? null
        );
    }

    /**
     * Clone the configuration with specific cache and logging instances.
     *
     * @param CacheManager|null  $cacheManager  The cache manager to inject.
     * @param LoggerFactory|null $loggerFactory The logger factory to inject.
     *
     * @return self Modified configuration clone.
     * @see docs/Features/Operate/Config/ContainerConfig.md#method-withcacheandlogging
     */
    public function withCacheAndLogging(CacheManager|null $cacheManager, LoggerFactory|null $loggerFactory): self
    {
        return new self(
            cacheDir: $this->cacheDir,
            debug: $this->debug,
            strict: $this->strict,
            maxResolutionDepth: $this->maxResolutionDepth,
            cacheManager: $cacheManager,
            loggerFactory: $loggerFactory
        );
    }
}
