<?php

declare(strict_types=1);
namespace Avax\Container\Guard\Rules;

/**
 * Container policy configuration for runtime behavior control.
 *
 * This class defines the policy settings that govern container behavior,
 * including strict mode, injection rules, debugging, and lazy loading defaults.
 * It provides a structured way to configure container security and performance
 * characteristics.
 *
 * ARCHITECTURAL ROLE:
 * - Runtime behavior configuration for dependency injection
 * - Security policy enforcement settings
 * - Debugging and monitoring controls
 * - Performance optimization toggles
 *
 * POLICY SETTINGS:
 * - strict: Enables strict validation of all operations
 * - strictInjection: Requires explicit type declarations for injection
 * - debug: Enables detailed debugging and error reporting
 * - lazyDefault: Sets lazy loading as the default binding behavior
 *
 * USAGE SCENARIOS:
 * ```php
 * $policy = new ContainerPolicy(
 *     strict: true,
 *     strictInjection: false,
 *     debug: true,
 *     lazyDefault: true
 * );
 *
 * $container->withPolicy($policy);
 * ```
 *
 * SECURITY IMPLICATIONS:
 * - Strict mode prevents unsafe operations
 * - Debug mode may expose sensitive information
 * - Lazy loading affects memory usage patterns
 *
 * PERFORMANCE IMPACT:
 * - Strict validation adds computational overhead
 * - Debug mode increases memory usage for error context
 * - Lazy loading reduces initial memory footprint
 *
 * @package Avax\Container\Guard\Rules
 * @see docs_md/Guard/Rules/ContainerPolicy.md#quick-summary
 */
final readonly class ContainerPolicy
{
    /**
     * Creates a new container policy with specified settings.
     *
     * @param bool $strict          Enable strict validation mode
     * @param bool $strictInjection Require strict type injection
     * @param bool $debug           Enable debug mode with detailed reporting
     * @param bool $lazyDefault     Set lazy loading as default behavior
     */
    public function __construct(
        public bool $strict = false,
        public bool $strictInjection = false,
        public bool $debug = false,
        public bool $lazyDefault = false
    ) {}
}