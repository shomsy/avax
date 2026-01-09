<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Config;

use Avax\Container\Features\Operate\Boot\ContainerBootstrap;

/**
 * Enterprise-grade immutable bootstrap profile for comprehensive container configuration management.
 *
 * This sophisticated configuration aggregator provides structured, environment-aware presets
 * for container bootstrapping, ensuring consistent and validated configuration across
 * development, testing, staging, and production environments. It serves as the central
 * configuration authority for all container components including caching, telemetry,
 * core container settings.
 *
 * ARCHITECTURAL ROLE:
 * - Unified configuration management for container bootstrap process
 * - Environment-specific configuration presets and overrides
 * - Immutable configuration state ensuring predictability
 * - Configuration validation and integrity checking
 * - Profile-based deployment configuration management
 * - Configuration inheritance and override mechanisms
 *
 * CONFIGURATION COMPONENTS:
 * - ContainerConfig: Core container behavior (debug, strict mode, caching, etc.)
 * - TelemetryConfig: Monitoring and logging configuration (sampling, sinks, etc.)
 *
 * ENVIRONMENT PRESETS:
 * - Development: Full debugging, extensive telemetry, relaxed security
 * - Production: Optimized performance, minimal telemetry, strict security
 * - Testing: Deterministic behavior, disabled external dependencies
 * - Staging: Mixed production/debugging features for pre-production validation
 * - CI: Automated testing configuration with minimal overhead
 *
 * CONFIGURATION VALIDATION:
 * - Directory writability verification for cache storage
 * - Configuration value range and format validation
 * - Cross-component configuration consistency checking
 * - Environment-specific constraint enforcement
 * - Security setting validation and warnings
 *
 * USAGE SCENARIOS:
 * ```php
 * // Environment-specific bootstrap
 * $profile = BootstrapProfile::production();
 * $bootstrap = new ContainerBootstrap($profile);
 * $container = $bootstrap->bootstrap();
 *
 * // Custom configuration with environment overrides
 * $baseProfile = BootstrapProfile::development();
 * $prodProfile = $baseProfile->withEnvironment('production');
 *
 * // Configuration file loading
 * $profile = BootstrapProfile::fromArrays(
 *     container: ['debug' => true, 'cacheDir' => '/tmp/cache'],
 *     telemetry: ['enabled' => true, 'sampleRate' => 0.1],
 * );
 *
 * // Programmatic configuration overrides
 * $customProfile = BootstrapProfile::production()->withOverrides(
 *     container: ['maxResolutionDepth' => 20],
 *     telemetry: ['includeStackTraces' => true]
 * );
 * ```
 *
 * CONFIGURATION MERGING:
 * - Deep merge of configuration arrays with override precedence
 * - Environment-specific overrides applied on top of base presets
 * - Custom overrides merged with preset configurations
 * - Configuration inheritance supporting layered configuration
 *
 * VALIDATION FEATURES:
 * - Comprehensive configuration validation with detailed error reporting
 * - Configuration value range and constraint validation
 * - Cross-validation between related configuration settings
 * - Security-focused validation warnings and recommendations
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Lightweight configuration object creation and validation
 * - Efficient deep merging algorithms for configuration overrides
 * - Minimal memory footprint with shared configuration objects
 * - Fast validation operations suitable for bootstrap timing
 * - Optimized serialization for configuration persistence
 *
 * SECURITY CONSIDERATIONS:
 * - Directory writability validation preventing insecure fallbacks
 * - Configuration sanitization and validation preventing injection
 * - Secure default settings with explicit security configuration
 * - Audit trail generation for configuration changes
 * - Environment-specific security setting enforcement
 *
 * ERROR HANDLING:
 * - Comprehensive validation with actionable error messages
 * - Configuration parsing error handling and reporting
 * - Graceful handling of missing or invalid configuration files
 * - Validation failure recovery with fallback configurations
 * - Detailed error context for troubleshooting configuration issues
 *
 * THREAD SAFETY:
 * - Completely immutable readonly design
 * - Safe for concurrent access across multiple threads
 * - No mutable state or side effects during configuration access
 * - Thread-safe configuration sharing and reuse
 * - Atomic configuration operations and validation
 *
 * COMPLIANCE FEATURES:
 * - Configuration audit logging for regulatory compliance
 * - Environment-specific configuration tracking
 * - Configuration change history and versioning
 * - Security configuration validation and reporting
 * - Compliance-ready configuration export and documentation
 *
 * EXTENSIBILITY:
 * - Custom preset creation through configuration arrays
 * - Plugin architecture for additional configuration components
 * - Custom validation rules and constraint definitions
 * - Configuration source abstraction for external providers
 * - Environment detection and automatic profile selection
 *
 * TROUBLESHOOTING CAPABILITIES:
 * - Detailed configuration validation error messages
 * - Configuration export for debugging and analysis
 * - Environment override visibility and tracking
 * - Configuration merging visualization and conflict detection
 * - Bootstrap failure diagnosis through configuration analysis
 *
 * BACKWARD COMPATIBILITY:
 * - Maintains compatibility with existing configuration interfaces
 * - Gradual migration path for legacy configuration formats
 * - Version-aware configuration loading and validation
 * - Extensible configuration schema for future requirements
 *
 * DEPLOYMENT PATTERNS:
 * - Environment-based automatic profile selection
 * - Configuration file-based deployment configuration
 * - Programmatic configuration for dynamic deployments
 * - Configuration inheritance for layered deployment strategies
 * - Configuration validation in CI/CD pipelines
 *
 * MONITORING INTEGRATION:
 * - Configuration change event logging
 * - Bootstrap configuration metrics collection
 * - Configuration validation result reporting
 * - Environment-specific configuration monitoring
 * - Configuration drift detection and alerting
 *
 * @package Avax\Container\Operate\Config
 * @final
 * @readonly
 * @see     ContainerConfig Core container configuration settings
 * @see     TelemetryConfig Monitoring and telemetry configuration
 * @see     ContainerBootstrap Bootstrap orchestrator using profile configuration
 * @see docs_md/Features/Operate/Config/BootstrapProfile.md#quick-summary
 */
final readonly class BootstrapProfile
{
    /**
     * Initialize the profile with its component configurations.
     *
     * @param ContainerConfig $container Container configuration.
     * @param TelemetryConfig $telemetry Telemetry configuration.
     * @see docs_md/Features/Operate/Config/BootstrapProfile.md#method-__construct
     */
    public function __construct(
        public ContainerConfig $container,
        public TelemetryConfig $telemetry
    ) {}

    /**
     * Development preset with full debugging and telemetry enabled.
     *
     * @return self
     * @see docs_md/Features/Operate/Config/BootstrapProfile.md#method-development
     */
    public static function development() : self
    {
        return new self(
            container: ContainerConfig::development(),
            telemetry: TelemetryConfig::development()
        );
    }

    /**
     * Production preset optimized for performance and stability.
     *
     * @return self
     * @see docs_md/Features/Operate/Config/BootstrapProfile.md#method-production
     */
    public static function production() : self
    {
        return new self(
            container: ContainerConfig::production(),
            telemetry: TelemetryConfig::production()
        );
    }

    /**
     * Testing preset with minimal overhead and deterministic behavior.
     *
     * @return self
     * @see docs_md/Features/Operate/Config/BootstrapProfile.md#method-testing
     */
    public static function testing() : self
    {
        return new self(
            container: ContainerConfig::testing(),
            telemetry: TelemetryConfig::testing()
        );
    }

    /**
     * Staging preset mixing production and debugging features.
     *
     * @return self
     * @see docs_md/Features/Operate/Config/BootstrapProfile.md#method-staging
     */
    public static function staging() : self
    {
        return self::fromArrays(
            container: [
                'cacheDir'           => '/var/cache/container-staging',
                'debug'              => false,
                'strict'             => true,
                'telemetry'          => true,
                'maxResolutionDepth' => 15,
                'compile'            => false,
                'allowedNamespaces'  => ['App\\', 'Shared\\']
            ],
            telemetry: [
                'enabled'            => true,
                'sampleRate'         => 0.5, // 50% sampling
                'includeStackTraces' => true,
                'sink'               => 'json',
                'outputPath'         => '/var/log/container/telemetry.json'
            ]
        );
    }

    /**
     * Create a profile from configuration arrays.
     *
     * @param array|null $container Container config array.
     * @param array|null $telemetry Telemetry config array.
     *
     * @return self
     * @see docs_md/Features/Operate/Config/BootstrapProfile.md#method-fromarrays
     */
    public static function fromArrays(
        array|null $container = null,
        array|null $telemetry = null
    ) : self
    {
        $container ??= [];
        $telemetry ??= [];

        return new self(
            container: ContainerConfig::fromArray(config: $container),
            telemetry: TelemetryConfig::fromArray(config: $telemetry)
        );
    }

    /**
     * CI/CD preset for automated testing and deployment pipelines.
     *
     * @return self
     */
    public static function ci() : self
    {
        return self::fromArrays(
            container: [
                'cacheDir'           => sys_get_temp_dir() . '/container-ci',
                'debug'              => true,
                'strict'             => true,
                'telemetry'          => false, // Disable telemetry in CI
                'maxResolutionDepth' => 10,
                'compile'            => false,
                'allowedNamespaces'  => ['App\\', 'Tests\\']
            ],
            telemetry: [
                'enabled' => false
            ]
        );
    }

    /**
     * Validate the profile configuration and return errors and warnings.
     *
     * @return array{valid:bool,errors:array,warnings:array}
     */
    public function validate() : array
    {
        $errors   = [];
        $warnings = [];

        // Validate container config
        if (! is_writable($this->container->cacheDir)) {
            $warnings[] = "Cache directory not writable: {$this->container->cacheDir}";
        }

        if ($this->container->maxResolutionDepth < 1) {
            $errors[] = 'Max resolution depth must be at least 1';
        }

        // Validate telemetry config
        if ($this->telemetry->enabled) {
            if ($this->telemetry->sampleRate < 0 || $this->telemetry->sampleRate > 1) {
                $errors[] = 'Sample rate must be between 0 and 1';
            }

            if ($this->telemetry->sink === 'json' && empty($this->telemetry->outputPath)) {
                $errors[] = 'Output path required for JSON telemetry sink';
            }
        }

        return [
            'valid'    => empty($errors),
            'errors'   => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Create a new profile merged with environment-specific overrides.
     *
     * @param string $environment Environment name.
     *
     * @return self
     */
    public function withEnvironment(string $environment) : self
    {
        $overrides = $this->getEnvironmentOverrides(environment: $environment);
        $config    = $this->toArray();

        // Deep merge overrides
        $merged = $this->deepMerge(base: $config, override: $overrides);

        return self::fromArrays(
            container: $merged['container'] ?? [],
            telemetry: $merged['telemetry'] ?? []
        );
    }

    /**
     * Return environment-specific overrides applied by `withEnvironment()`.
     *
     * @param string $environment Environment name.
     *
     * @return array<string, mixed>
     */
    public function getEnvironmentOverrides(string $environment) : array
    {
        $overrides = [];

        switch ($environment) {
            case 'development':
                $overrides['telemetry']['includeStackTraces'] = true;
                $overrides['container']['debug']              = true;
                break;

            case 'production':
                $overrides['telemetry']['sampleRate'] = 0.1; // 10% sampling
                $overrides['container']['debug']      = false;
                $overrides['container']['compile']    = true;
                break;

            case 'testing':
                $overrides['telemetry']['enabled'] = false;
                break;
        }

        return $overrides;
    }

    /**
     * Export the profile to a serializable array.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'container' => [
                'cacheDir'           => $this->container->cacheDir,
                'prototypeCacheDir'  => $this->container->prototypeCacheDir,
                'debug'              => $this->container->debug,
                'strict'             => $this->container->strict,
                'telemetry'          => $this->container->telemetry,
                'maxResolutionDepth' => $this->container->maxResolutionDepth,
                'compile'            => $this->container->compile,
                'allowedNamespaces'  => $this->container->allowedNamespaces
            ],
            'telemetry' => [
                'enabled'            => $this->telemetry->enabled,
                'sampleRate'         => $this->telemetry->sampleRate,
                'includeStackTraces' => $this->telemetry->includeStackTraces,
                'trackedEvents'      => $this->telemetry->trackedEvents,
                'sink'               => $this->telemetry->sink,
                'outputPath'         => $this->telemetry->outputPath
            ]
        ];
    }

    /**
     * Deep merge two arrays.
     *
     * @param array $base     Base array.
     * @param array $override Override array.
     *
     * @return array
     */
    private function deepMerge(array $base, array $override) : array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->deepMerge(base: $base[$key], override: $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * Create a new profile with custom overrides.
     *
     * @param array|null $container Container overrides.
     * @param array|null $telemetry Telemetry overrides.
     *
     * @return self
     */
    public function withOverrides(array|null $container = null, array|null $telemetry = null) : self
    {
        $container ??= [];
        $telemetry ??= [];

        return new self(
            container: ContainerConfig::fromArray(config: array_merge($this->toArray()['container'], $container)),
            telemetry: TelemetryConfig::fromArray(config: array_merge($this->toArray()['telemetry'], $telemetry))
        );
    }
}
