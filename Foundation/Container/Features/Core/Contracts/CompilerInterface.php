<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Avax\Container\Features\Think\Prototype\ServicePrototypeBuilder;

/**
 * @see     docs/Features/Core/Contracts/CompilerInterface.md
 * @see     ContainerInterface For the full container contract
 * @see     ResolverInterface For service resolution operations
 */
interface CompilerInterface
{
    /**
     * Analyzes dependencies for a service class and returns a prototype builder.
     *
     * Performs static analysis of the given class to understand its dependencies,
     * injection requirements, and instantiation strategy. Returns a builder that
     * can construct an optimized service prototype.
     *
     * ANALYSIS SCOPE:
     * - Constructor parameters and their types
     * - Property injection points (#[Inject] attributes)
     * - Method injection points (#[Inject] attributes)
     * - Lifecycle interfaces (Initializable, Terminable)
     * - Instantiability validation
     *
     * @param string $class The fully qualified class name to analyze
     *
     * @return \Avax\Container\Features\Think\Prototype\ServicePrototypeBuilder A builder for constructing the service
     *                                                                          prototype
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface If analysis fails
     */
    public function analyzeDependenciesFor(string $class) : ServicePrototypeBuilder;

    /**
     * Validates all registered service definitions and prototypes.
     *
     * Performs comprehensive validation of the container's service definitions,
     * checking for circular dependencies, invalid configurations, and other
     * potential runtime issues.
     *
     * VALIDATION CHECKS:
     * - Service definition completeness and correctness
     * - Circular dependency detection
     * - Type hint validation for injection points
     * - Accessibility validation for injection targets
     * - Lifecycle interface compliance
     *
     * @return self Returns $this for method chaining
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface If validation fails
     */
    public function validate() : self;

    /**
     * Compiles and caches service prototypes for all registered services.
     *
     * Performs full compilation of the container by analyzing all registered services,
     * generating optimized prototypes, and caching the results for production use.
     * This operation is expensive but results in significant runtime performance gains.
     *
     * COMPILATION STEPS:
     * 1. Analyze all registered service classes
     * 2. Generate optimized service prototypes
     * 3. Validate dependency graphs and injection points
     * 4. Cache prototypes using configured cache strategy
     * 5. Generate compilation statistics and reports
     *
     * PERFORMANCE IMPACT:
     * - High compilation cost (reflection, analysis, validation)
     * - Significant runtime performance improvement
     * - Reduced memory usage through optimized structures
     * - Faster startup times in production
     *
     * @return array{
     *     compiled_services: int,
     *     cache_size: int,
     *     compilation_time: float,
     *     validation_errors: int
     * } Compilation statistics and results
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface If compilation fails
     */
    public function compile() : array;

    /**
     * Clears all cached compilation artifacts.
     *
     * Removes all cached prototypes, analysis results, and compiled artifacts.
     * This forces fresh compilation on the next container operation.
     *
     * USE CASES:
     * - After modifying service class definitions
     * - When changing injection configurations
     * - During development iterations
     * - After deploying updated code
     *
     * @return self Returns $this for method chaining
     */
    public function clearCache() : self;

    /**
     * Gets compilation statistics and diagnostics.
     *
     * Returns detailed information about the container's compilation state,
     * including cache status, validation results, and performance metrics.
     *
     * STATISTICS INCLUDE:
     * - Number of compiled services
     * - Cache hit/miss ratios
     * - Compilation timestamps
     * - Validation error summaries
     * - Memory usage information
     *
     * @return array Compilation statistics and diagnostic information
     */
    public function getCompilationStats() : array;
}
