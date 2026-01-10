<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Config;

use Avax\Container\Features\Operate\Boot\ContainerBootstrap;

/**
 * Enterprise-grade immutable bootstrap profile for comprehensive container configuration management.
 *
 * This sophisticated configuration aggregator provides structured, environment-aware presets
 * for container bootstrapping, ensuring consistent and validated configuration across
 * development, testing, staging, and production environments.
 *
 * @package Avax\Container\Features\Operate\Config
 * @see docs/Features/Operate/Config/BootstrapProfile.md
 */
class BootstrapProfile
{
    /**
     * Initializes a new bootstrap profile.
     *
     * @param ContainerConfig $container Core container behavior settings.
     * @param TelemetryConfig $telemetry Monitoring and logging settings.
     *
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-__construct
     */
    public function __construct(
        public readonly ContainerConfig $container,
        public readonly TelemetryConfig $telemetry
    ) {}

    /**
     * Create a default development profile.
     *
     * @return self Profile with high debugging and extensive telemetry.
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-development
     */
    public static function development(): self
    {
        return new self(
            container: ContainerConfig::development(),
            telemetry: TelemetryConfig::development()
        );
    }

    /**
     * Create a default production profile.
     *
     * @return self Profile with optimized performance and strict security.
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-production
     */
    public static function production(): self
    {
        return new self(
            container: ContainerConfig::production(),
            telemetry: TelemetryConfig::production()
        );
    }

    /**
     * Create a default testing profile.
     *
     * @return self Profile with deterministic behavior for test suites.
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-testing
     */
    public static function testing(): self
    {
        return new self(
            container: ContainerConfig::testing(),
            telemetry: TelemetryConfig::testing()
        );
    }

    /**
     * Create a profile from raw configuration arrays.
     *
     * @param array<string, mixed> $container Raw container configuration.
     * @param array<string, mixed> $telemetry Raw telemetry configuration.
     *
     * @return self Hydrated profile instance.
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-fromarrays
     */
    public static function fromArrays(array $container = [], array $telemetry = []): self
    {
        return new self(
            container: ContainerConfig::fromArray(data: $container),
            telemetry: TelemetryConfig::fromArray(data: $telemetry)
        );
    }

    /**
     * Clone the profile with an updated container configuration.
     *
     * @param ContainerConfig $container New container configuration.
     * @return self Modified profile clone.
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-withcontainer
     */
    public function withContainer(ContainerConfig $container): self
    {
        return new self(container: $container, telemetry: $this->telemetry);
    }

    /**
     * Clone the profile with an updated telemetry configuration.
     *
     * @param TelemetryConfig $telemetry New telemetry configuration.
     * @return self Modified profile clone.
     * @see docs/Features/Operate/Config/BootstrapProfile.md#method-withtelemetry
     */
    public function withTelemetry(TelemetryConfig $telemetry): self
    {
        return new self(container: $this->container, telemetry: $telemetry);
    }
}
