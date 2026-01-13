<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Boot;

/**
 * Environment loader interface for container bootstrap configuration.
 *
 * This interface defines the contract for loading environment-specific
 * configuration and settings during container initialization. Implementations
 * can load configuration from various sources like environment variables,
 * configuration files, or external services.
 *
 * ARCHITECTURAL ROLE:
 * - Provides environment-aware configuration loading
 * - Enables pluggable environment detection and loading
 * - Supports different configuration sources and formats
 * - Integrates with container bootstrap process
 *
 * CONFIGURATION SOURCES:
 * - Environment variables (.env files)
 * - Configuration files (JSON, YAML, PHP arrays)
 * - External services (configuration servers)
 * - Runtime parameters and flags
 *
 * USAGE SCENARIOS:
 * ```php
 * $loader = new DotEnvLoader('/path/to/.env');
 * $config = $loader->loadEnvironment();
 *
 * $container->setEnvironmentLoader($loader);
 * ```
 *
 * ENVIRONMENT DETECTION:
 * - Development, staging, production environments
 * - Feature flags and toggle configurations
 * - Runtime configuration overrides
 * - Environment-specific service bindings
 *
 * CONFIGURATION MERGING:
 * - Base configuration with environment overrides
 * - Hierarchical configuration loading
 * - Configuration validation and type checking
 * - Fallback values for missing configuration
 *
 * SECURITY CONSIDERATIONS:
 * - Sensitive configuration protection
 * - Environment-specific security settings
 * - Configuration validation and sanitization
 * - Access control for configuration sources
 *
 * PERFORMANCE IMPACT:
 * - Configuration loading overhead
 * - Caching of loaded configuration
 * - Lazy loading for expensive configuration sources
 * - Configuration pre-compilation for production
 *
 * ERROR HANDLING:
 * - Graceful handling of missing configuration files
 * - Validation errors for malformed configuration
 * - Fallback configurations for critical settings
 * - Diagnostic information for configuration issues
 *
 * @see     docs/Features/Operate/Boot/EnvironmentLoaderInterface.md#quick-summary
 */
interface EnvironmentLoaderInterface
{
    /**
     * Load environment configuration for container bootstrap.
     *
     * Loads and returns environment-specific configuration that will be
     * used during container initialization and service registration.
     * The returned configuration should be validated and ready for use.
     *
     * CONFIGURATION FORMAT:
     * Returns an associative array with configuration keys and values.
     * Nested configurations are supported using dot notation or array nesting.
     *
     * ```php
     * return [
     *     'app' => [
     *         'name' => 'MyApp',
     *         'debug' => true,
     *         'environment' => 'development'
     *     ],
     *     'database' => [
     *         'host' => 'localhost',
     *         'port' => 3306
     *     ]
     * ];
     * ```
     *
     * CONFIGURATION SOURCES:
     * - Environment variables (APP_NAME, DATABASE_HOST)
     * - Configuration files (.env, config.php, config.json)
     * - Runtime parameters and command line arguments
     * - External configuration services
     *
     * VALIDATION REQUIREMENTS:
     * - All required configuration keys must be present
     * - Configuration values must be of expected types
     * - Invalid configurations should throw exceptions
     * - Optional configurations should have sensible defaults
     *
     * PERFORMANCE OPTIMIZATION:
     * - Cache loaded configuration to avoid repeated I/O
     * - Lazy load expensive configuration sources
     * - Pre-compile configuration for production environments
     *
     * @return array<string, mixed> Environment configuration array
     *
     * @throws \RuntimeException When configuration cannot be loaded or is invalid
     *
     * @see docs/Features/Operate/Boot/EnvironmentLoaderInterface.md#method-loadenvironment
     */
    public function loadEnvironment() : array;
}
