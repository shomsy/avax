<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Config;

use Avax\Cache\CacheManager;
use Avax\Container\Features\Operate\Boot\ContainerBootstrapper;
use Avax\Logging\LoggerFactory;

/**
 * Immutable configuration container for Avax Dependency Injection runtime settings.
 *
 * This Data Transfer Object (DTO) encapsulates all configurable aspects of the container's
 * operational behavior, providing a centralized and type-safe way to control caching,
 * debugging, telemetry, performance limits, and security policies. The immutable design
 * ensures configuration integrity throughout the container lifecycle.
 *
 * CONFIGURATION CATEGORIES:
 * - Caching: Directories and strategies for performance optimization
 * - Debugging: Verbose logging and error reporting controls
 * - Telemetry: Metrics collection for monitoring and analytics
 * - Security: Strict mode and namespace whitelisting
 * - Performance: Resolution depth limits and compilation settings
 *
 * ARCHITECTURAL ROLE:
 * - Serves as single source of truth for container behavior
 * - Enables environment-specific configuration presets
 * - Supports programmatic configuration through fluent APIs
 * - Integrates with external caching and logging infrastructure
 *
 * USAGE PATTERNS:
 * ```php
 * // Programmatic configuration
 * $config = new ContainerConfig(
 *     cacheDir: '/var/cache/container',
 *     debug: true,
 *     strict: false,
 *     maxResolutionDepth: 15
 * );
 *
 * // Configuration file loading
 * $config = ContainerConfig::fromArray($configArray);
 *
 * // Environment presets
 * $config = ContainerConfig::production();
 * $config = ContainerConfig::development();
 * $config = ContainerConfig::testing();
 * ```
 *
 * PERFORMANCE IMPLICATIONS:
 * - Cache directories should be writable and performant (SSD recommended)
 * - Debug mode adds significant logging overhead
 * - Telemetry collection impacts memory usage
 * - Compilation improves runtime performance at the cost of startup time
 *
 * SECURITY CONSIDERATIONS:
 * - Strict mode prevents resolution of undefined services
 * - Namespace whitelisting limits autowiring scope
 * - Cache directories should have appropriate file permissions
 * - Debug mode should never be enabled in production
 *
 * THREAD SAFETY:
 * Immutable design makes all instances thread-safe for read operations.
 *
 * @package Avax\Container\Operate\Config
 * @see     ContainerBootstrapper For bootstrap integration
 * @see     CacheManager For caching infrastructure
 * @see     LoggerFactory For logging integration
 * @see docs_md/Features/Operate/Config/ContainerConfig.md#quick-summary
 */
final readonly class ContainerConfig
{
    /**
     * Creates a new ContainerConfig instance with specified settings.
     *
     * Initializes the configuration with all operational parameters for the
     * dependency injection container. All parameters are validated and stored
     * immutably to ensure consistent behavior throughout the container lifecycle.
     *
     * PARAMETER HIERARCHY:
     * - Cache directories can be derived from base cacheDir if not specified
     * - Default values provide sensible out-of-the-box behavior
     * - Boolean flags control feature toggles
     * - Arrays define whitelists and collections
     *
     * @param string $cacheDir           Base directory for all container caching operations
     * @param string $prototypeCacheDir  Directory for caching service analysis prototypes
     * @param bool   $debug              Enable debug mode with verbose logging and stack traces
     * @param bool   $strict             Enable strict mode that fails on undefined services
     * @param bool   $telemetry          Enable collection of performance and usage metrics
     * @param int    $maxResolutionDepth Maximum allowed dependency resolution depth
     * @param bool   $compile            Enable container compilation for production optimization
     * @param array  $allowedNamespaces  Whitelist of namespaces allowed for autowiring
     * @see docs_md/Features/Operate/Config/ContainerConfig.md#method-__construct
     */
    public function __construct(
        public string $cacheDir,
        public string $prototypeCacheDir,
        public bool   $debug = false,
        public bool   $strict = true,
        public bool   $telemetry = false,
        public int    $maxResolutionDepth = 10,
        public bool   $compile = false,
        public array  $allowedNamespaces = [],
    ) {}

    /**
     * Creates a ContainerConfig instance from an associative array.
     *
     * This factory method enables loading configuration from external sources
     * such as configuration files, environment variables, or database settings.
     * It provides sensible defaults for missing configuration keys while
     * allowing full customization of all parameters.
     *
     * CONFIGURATION SOURCES:
     * - PHP configuration files (config/container.php)
     * - Environment variables
     * - Database configuration tables
     * - Cloud configuration services
     *
     * DEFAULT STRATEGIES:
     * - Cache directories default to system temp directory
     * - Subdirectories are derived from base cache directory
     * - Boolean flags default to secure/safe values
     * - Arrays default to empty (no restrictions)
     *
     * @param array $config Associative array of configuration key-value pairs
     *
     * @return self Fully configured ContainerConfig instance
     * @see docs_md/Features/Operate/Config/ContainerConfig.md#method-fromarray
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            cacheDir          : $config['cacheDir'] ?? sys_get_temp_dir() . '/container',
            prototypeCacheDir : $config['prototypeCacheDir'] ?? ($config['cacheDir'] ?? sys_get_temp_dir() . '/container') . '/prototypes',
            debug             : $config['debug'] ?? false,
            strict            : $config['strict'] ?? true,
            telemetry         : $config['telemetry'] ?? false,
            maxResolutionDepth: $config['maxResolutionDepth'] ?? 10,
            compile           : $config['compile'] ?? false,
            allowedNamespaces : $config['allowedNamespaces'] ?? [],
        );
    }

    /**
     * Returns a pre-configured development environment configuration.
     *
     * This preset is optimized for development workflows with relaxed security,
     * enhanced debugging, and telemetry for monitoring. It sacrifices performance
     * for developer experience and troubleshooting capabilities.
     *
     * DEVELOPMENT FEATURES:
     * - Debug mode enabled for detailed error reporting
     * - Strict mode disabled to allow flexible development
     * - Telemetry enabled for performance monitoring
     * - Higher resolution depth for complex dependency graphs
     * - Compilation disabled for faster iteration
     *
     * @return self Development-optimized configuration preset
     * @see docs_md/Features/Operate/Config/ContainerConfig.md#method-development
     */
    public static function development() : self
    {
        return new self(
            cacheDir          : sys_get_temp_dir() . '/container-dev',
            prototypeCacheDir : sys_get_temp_dir() . '/container-dev/prototypes',
            debug             : true,
            strict            : false,
            telemetry         : true,
            maxResolutionDepth: 20,
            compile           : false,
            allowedNamespaces : ['App\\', 'Tests\\'],
        );
    }

    /**
     * Returns a pre-configured production environment configuration.
     *
     * This preset is optimized for production deployment with maximum security,
     * performance, and reliability. It enables all performance optimizations
     * while maintaining strict operational controls.
     *
     * PRODUCTION OPTIMIZATIONS:
     * - Compilation enabled for maximum performance
     * - Strict mode enforced for security
     * - Telemetry enabled for monitoring
     * - Optimized cache directories
     * - Conservative resolution depth limits
     *
     * @return self Production-optimized configuration preset
     * @see docs_md/Features/Operate/Config/ContainerConfig.md#method-production
     */
    public static function production() : self
    {
        return new self(
            cacheDir          : '/var/cache/container',
            prototypeCacheDir : '/var/cache/container/prototypes',
            debug             : false,
            strict            : true,
            telemetry         : true,
            maxResolutionDepth: 10,
            compile           : true,
            allowedNamespaces : ['App\\'],
        );
    }

    /**
     * Returns a pre-configured testing environment configuration.
     *
     * This preset is optimized for automated testing scenarios with balanced
     * performance and debugging capabilities. It provides isolation between
     * test runs while maintaining test execution speed.
     *
     * TESTING CONSIDERATIONS:
     * - Debug mode for test failure diagnostics
     * - Strict mode disabled for test flexibility
     * - Telemetry disabled to reduce test noise
     * - Isolated cache directories per test run
     * - Moderate resolution depth for complex mocks
     *
     * @return self Testing-optimized configuration preset
     * @see docs_md/Features/Operate/Config/ContainerConfig.md#method-testing
     */
    public static function testing() : self
    {
        return new self(
            cacheDir          : sys_get_temp_dir() . '/container-test',
            prototypeCacheDir : sys_get_temp_dir() . '/container-test/prototypes',
            debug             : true,
            strict            : false,
            telemetry         : false,
            maxResolutionDepth: 15,
            compile           : false,
            allowedNamespaces : ['App\\', 'Tests\\'],
        );
    }

    /**
     * Creates a configuration instance with integrated caching and logging infrastructure.
     *
     * This method extends the basic configuration with external dependencies for
     * advanced caching and logging capabilities. It returns a composite object
     * that combines configuration with operational infrastructure.
     *
     * INFRASTRUCTURE INTEGRATION:
     * - CacheManager provides distributed caching capabilities
     * - LoggerFactory enables structured logging with multiple sinks
     * - Composite object maintains immutability while adding functionality
     *
     * @param CacheManager  $cacheManager  Configured cache manager instance
     * @param LoggerFactory $loggerFactory Configured logger factory instance
     *
     * @return ContainerWithInfrastructure Configuration with infrastructure integration
     * @see docs_md/Features/Operate/Config/ContainerConfig.md#method-withcacheandlogging
     */
    public function withCacheAndLogging(
        CacheManager  $cacheManager,
        LoggerFactory $loggerFactory
    ) : ContainerWithInfrastructure
    {
        return new ContainerWithInfrastructure(
            config       : $this,
            cacheManager : $cacheManager,
            loggerFactory: $loggerFactory
        );
    }
}
