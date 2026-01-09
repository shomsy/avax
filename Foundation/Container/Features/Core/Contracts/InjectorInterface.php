<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Contracts;

/**
 * @package Avax\Container\Core\Contracts
 *
 * Contract for dependency injection operations.
 *
 * InjectorInterface defines the capabilities for performing dependency injection
 * on existing objects. It focuses on property injection, method injection, and
 * post-construction setup, separating these concerns from service resolution
 * and registration.
 *
 * WHY IT EXISTS:
 * - To provide a focused contract for dependency injection operations
 * - To enable injection on objects not managed by the container
 * - To support complex injection scenarios (property, setter, interface injection)
 * - To facilitate testing and mocking of injection behavior
 *
 * INJECTION SCENARIOS:
 * - Property injection via #[Inject] attributes
 * - Method injection via #[Inject] attributes
 * - Interface-based injection (Initializable, Terminable)
 * - Post-construction setup and configuration
 *
 * INJECTION STRATEGIES:
 * - Attribute-driven injection (#[Inject])
 * - Type-hint driven injection
 * - Named parameter injection
 * - Custom injection strategies
 *
 * THREAD SAFETY:
 * Implementations should be thread-safe for concurrent injection operations.
 *
 * @see     ContainerInterface For the full container contract
 * @see     ResolverInterface For service resolution operations
 */
interface InjectorInterface
{
    /**
     * Performs dependency injection on an existing object instance.
     *
     * Analyzes the target object for injection points (properties and methods
     * marked with #[Inject] attributes) and injects the required dependencies.
     * This method works on objects that were instantiated outside the container.
     *
     * INJECTION PROCESS:
     * 1. Scan object for #[Inject] attributes on properties and methods
     * 2. Resolve dependencies for each injection point
     * 3. Inject resolved dependencies into the target object
     * 4. Handle injection failures gracefully with detailed error reporting
     *
     * SUPPORTED INJECTION TYPES:
     * - Property injection: `#[Inject] private Logger $logger;`
     * - Method injection: `#[Inject] public function setConfig(Config $config)`
     * - Parameter injection with type hints and names
     *
     * @param object $target The object instance to inject dependencies into
     *
     * @return object The same object instance after injection (fluent interface)
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface If injection fails
     */
    public function injectInto(object $target) : object;

    /**
     * Checks if an object can be injected with dependencies.
     *
     * Performs a dry-run analysis to determine if the given object has
     * valid injection points and if all required dependencies can be resolved.
     * This method does not modify the target object.
     *
     * VALIDATION CHECKS:
     * - Object has accessible injection points
     * - All injection dependencies can be resolved
     * - Injection points are properly configured
     * - No circular dependencies in injection graph
     *
     * @param object $target The object to validate for injection
     *
     * @return bool True if the object can be injected, false otherwise
     */
    public function canInject(object $target) : bool;

    /**
     * Gets information about injection points on an object.
     *
     * Returns detailed information about all injection points found on the target object,
     * including property and method injection specifications. This is useful for
     * debugging and introspection.
     *
     * RETURNED INFORMATION:
     * - Property injection points with types and names
     * - Method injection points with parameter specifications
     * - Required vs optional dependencies
     * - Injection constraints and validation rules
     *
     * @param object $target The object to analyze
     *
     * @return array Information about injection points
     */
    public function getInjectionInfo(object $target) : array;
}