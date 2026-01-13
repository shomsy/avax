<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Configuration object for Router behavior customization.
 *
 * Allows fine-tuning of router behavior through policy-based configuration,
 * enabling different deployment scenarios and use cases.
 */
final readonly class RouterConfig
{
    public function __construct(
        public DuplicatePolicy $duplicatePolicy = DuplicatePolicy::THROW,
        public bool $enableTracing = false,
        public bool $strictMode = true,
        public int $maxRoutes = 10000
    ) {}

    /**
     * Create a development-friendly configuration.
     *
     * More permissive settings suitable for development environments.
     */
    public static function development() : self
    {
        return new self(
            duplicatePolicy: DuplicatePolicy::THROW,
            enableTracing: true,
            strictMode: false,
            maxRoutes: 50000
        );
    }

    /**
     * Create a production-optimized configuration.
     *
     * Strict settings optimized for production stability.
     */
    public static function production() : self
    {
        return new self(
            duplicatePolicy: DuplicatePolicy::THROW,
            enableTracing: false,
            strictMode: true,
            maxRoutes: 10000
        );
    }

    /**
     * Create a testing configuration.
     *
     * Relaxed settings for testing scenarios.
     */
    public static function testing() : self
    {
        return new self(
            duplicatePolicy: DuplicatePolicy::REPLACE,
            enableTracing: true,
            strictMode: false,
            maxRoutes: 1000
        );
    }
}