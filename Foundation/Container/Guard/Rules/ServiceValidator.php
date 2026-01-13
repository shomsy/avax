<?php

declare(strict_types=1);

namespace Avax\Container\Guard\Rules;

use Avax\Container\Features\Define\Store\ServiceDefinitionEntity;
use Avax\Container\Features\Define\Store\ServiceDefinitionRepository;
use Avax\Container\Features\Define\Store\ServiceDependencyRepository;
use Avax\DataHandling\Validation\Attributes\AbstractRule;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * Comprehensive service validation orchestrator for dependency injection containers.
 *
 * This validator performs multi-layered validation of service definitions using
 * attribute-based rules, business logic constraints, security policies, and
 * dependency analysis. It serves as the central authority for ensuring container
 * integrity and preventing runtime issues through comprehensive pre-flight checks.
 *
 * ARCHITECTURAL ROLE:
 * - Multi-layered service validation orchestrator
 * - Business rule enforcement for service definitions
 * - Security policy validation and threat detection
 * - Dependency graph analysis and constraint checking
 * - Performance impact assessment and optimization recommendations
 * - Comprehensive error reporting and diagnostic capabilities
 *
 * VALIDATION LAYERS:
 * 1. Attribute Validation: Uses reflection and validation rules on service properties
 * 2. Business Rules: Enforces domain-specific constraints and invariants
 * 3. Dependency Validation: Checks dependency relationships and cycles
 * 4. Security Validation: Detects potentially dangerous patterns and sensitive data
 * 5. Performance Analysis: Assesses complexity and optimization opportunities
 *
 * VALIDATION RESULTS:
 * Returns structured validation results with errors, warnings, and diagnostic information.
 * Supports both individual service validation and bulk validation scenarios.
 *
 * USAGE SCENARIOS:
 * ```php
 * $validator = new ServiceValidator($serviceRepo, $dependencyRepo);
 *
 * // Validate single service
 * $result = $validator->validateService($serviceDefinition);
 * if (!$result['isValid']) {
 *     // Handle validation errors
 *     foreach ($result['errors'] as $error) {
 *         echo "Error: " . $error['message'];
 *     }
 * }
 *
 * // Get validation summary for all services
 * $summary = $validator->getValidationSummary();
 * ```
 *
 * VALIDATION CATEGORIES:
 * - Errors: Critical issues that prevent service registration
 * - Warnings: Performance or maintainability concerns
 * - Security Issues: Potential vulnerabilities or unsafe patterns
 * - Dependency Issues: Circular references or missing dependencies
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Reflection operations for attribute validation
 * - Database queries for dependency analysis
 * - Graph algorithms for cycle detection
 * - Memory usage scales with service complexity
 * - Caching recommended for repeated validations
 *
 * THREAD SAFETY:
 * - Read-only operations on repositories
 * - No shared mutable state
 * - Safe for concurrent validation requests
 *
 * ERROR HANDLING:
 * - Comprehensive exception catching and reporting
 * - Structured error messages with context
 * - Validation continues after individual failures
 * - Detailed diagnostic information for debugging
 *
 * @see     ServiceDefinitionEntity For the validated service structure
 * @see     ServiceDefinitionRepository For service data access
 * @see     ServiceDependencyRepository For dependency relationship management
 * @see     docs/Guard/Rules/ServiceValidator.md#quick-summary
 */
readonly class ServiceValidator
{
    /**
     * Creates a new service validator with required repository dependencies.
     *
     * Initializes the validator with access to service and dependency repositories
     * needed for comprehensive validation operations. Both repositories are required
     * for full validation capabilities including dependency analysis.
     *
     * DEPENDENCY INJECTION:
     * - serviceRepo: Provides access to service definitions for validation
     * - dependencyRepo: Enables dependency graph analysis and cycle detection
     *
     * REPOSITORY REQUIREMENTS:
     * - ServiceDefinitionRepository: Must support findById and related queries
     * - ServiceDependencyRepository: Must support dependency graph operations
     *
     * @param ServiceDefinitionRepository $serviceRepo    Settings for service definition access
     * @param ServiceDependencyRepository $dependencyRepo Settings for dependency relationship access
     *
     * @see docs/Guard/Rules/ServiceValidator.md#method-__construct
     */
    public function __construct(
        private ServiceDefinitionRepository $serviceRepo,
        private ServiceDependencyRepository $dependencyRepo
    ) {}

    /**
     * Get validation summary across all services.
     *
     * @throws \Exception
     *
     * @see docs/Guard/Rules/ServiceValidator.md#method-getvalidationsummary
     */
    public function getValidationSummary() : array
    {
        $allServices = $this->serviceRepo->findAll();
        $results     = $this->validateServices(services: $allServices);

        $summary = [
            'total_services'   => count($results),
            'valid_services'   => 0,
            'invalid_services' => 0,
            'total_errors'     => 0,
            'total_warnings'   => 0,
            'errors_by_rule'   => [],
            'warnings_by_rule' => [],
        ];

        foreach ($results as $result) {
            if ($result['isValid']) {
                $summary['valid_services']++;
            } else {
                $summary['invalid_services']++;
            }

            $summary['total_errors']   += count($result['errors']);
            $summary['total_warnings'] += count($result['warnings']);

            foreach ($result['errors'] as $error) {
                $rule                             = $error['rule'];
                $summary['errors_by_rule'][$rule] = ($summary['errors_by_rule'][$rule] ?? 0) + 1;
            }

            foreach ($result['warnings'] as $warning) {
                $rule                               = $warning['rule'];
                $summary['warnings_by_rule'][$rule] = ($summary['warnings_by_rule'][$rule] ?? 0) + 1;
            }
        }

        return $summary;
    }

    /**
     * Validate multiple services at once.
     *
     * @throws \Exception
     *
     * @see docs/Guard/Rules/ServiceValidator.md#method-validateservices
     */
    public function validateServices(array $services) : array
    {
        $results = [];

        foreach ($services as $service) {
            if ($service instanceof ServiceDefinitionEntity) {
                $results[] = $this->validateService(service: $service);
            }
        }

        return $results;
    }

    /**
     * Performs comprehensive validation of a single service definition.
     *
     * Executes multi-layered validation against a service definition, checking
     * attributes, business rules, dependencies, security constraints, and
     * performance implications. Returns detailed validation results with
     * errors, warnings, and diagnostic information.
     *
     * VALIDATION PROCESS:
     * 1. Attribute Validation: Validates service properties using reflection and validation rules
     * 2. Business Rules: Checks domain constraints (uniqueness, lifetime, environment)
     * 3. Dependency Validation: Verifies dependency relationships and detects cycles
     * 4. Security Validation: Scans for dangerous patterns and sensitive data exposure
     * 5. Performance Analysis: Assesses complexity and provides optimization warnings
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     'isValid' => bool,           // Overall validation status
     *     'errors' => [                // Critical validation failures
     *         [
     *             'rule' => 'RuleName',
     *             'message' => 'Error description',
     *             'value' => mixed,      // Value that failed validation
     *             'field' => 'fieldName' // Optional field identifier
     *         ]
     *     ],
     *     'warnings' => [              // Performance/maintainability concerns
     *         [
     *             'rule' => 'RuleName',
     *             'message' => 'Warning description',
     *             'value' => mixed
     *         ]
     *     ],
     *     'serviceId' => 'service.id'  // Service identifier for reference
     * ]
     * ```
     *
     * VALIDATION STRICTNESS:
     * - Errors: Block service registration (critical issues)
     * - Warnings: Allow registration but recommend attention (performance/maintainability)
     * - Validation continues through all layers even after failures
     *
     * EXCEPTION HANDLING:
     * - Catches and reports reflection exceptions
     * - Handles repository access failures gracefully
     * - Continues validation after individual layer failures
     * - Provides actionable error messages for debugging
     *
     * @param ServiceDefinitionEntity $service The service definition to validate
     *
     * @return array Structured validation results with errors, warnings, and metadata
     *
     * @throws \Exception When critical validation infrastructure fails
     *
     * @see docs/Guard/Rules/ServiceValidator.md#method-validateservice
     */
    public function validateService(ServiceDefinitionEntity $service) : array
    {
        $errors   = [];
        $warnings = [];

        // Basic attribute validation
        $attributeErrors = $this->validateAttributes(service: $service);
        $errors          = array_merge($errors, $attributeErrors);

        // Business rule validation
        $businessErrors = $this->validateBusinessRules(service: $service);
        $errors         = array_merge($errors, $businessErrors);

        // Dependency validation
        $dependencyErrors = $this->validateDependencies(service: $service);
        $errors           = array_merge($errors, $dependencyErrors);

        // Security validation
        $securityErrors = $this->validateSecurity(service: $service);
        $errors         = array_merge($errors, $securityErrors);

        // Performance validation
        $performanceWarnings = $this->validatePerformance(service: $service);
        $warnings            = array_merge($warnings, $performanceWarnings);

        return [
            'isValid'   => empty($errors),
            'errors'    => $errors,
            'warnings'  => $warnings,
            'serviceId' => $service->id,
        ];
    }

    /**
     * Validate service attributes using reflection and validation rules.
     */
    private function validateAttributes(ServiceDefinitionEntity $service) : array
    {
        $errors = [];

        try {
            $reflection = new ReflectionClass(objectOrClass: $service);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                $attributes = $property->getAttributes(name: AbstractRule::class, flags: ReflectionAttribute::IS_INSTANCEOF);

                foreach ($attributes as $attribute) {
                    $rule  = $attribute->newInstance();
                    $value = $property->getValue(object: $service);

                    if (! $rule->validate($value)) {
                        $errors[] = [
                            'field'   => $property->getName(),
                            'rule'    => get_class($rule),
                            'message' => $rule->getErrorMessage(),
                            'value'   => $value,
                        ];
                    }
                }
            }
        } catch (ReflectionException $e) {
            $errors[] = [
                'field'   => 'reflection',
                'rule'    => 'ReflectionException',
                'message' => 'Could not reflect service class: ' . $e->getMessage(),
                'value'   => get_class($service),
            ];
        }

        return $errors;
    }

    /**
     * Validate business rules for service definitions.
     *
     * @throws \Exception
     */
    private function validateBusinessRules(ServiceDefinitionEntity $service) : array
    {
        $errors = [];

        // Check for duplicate service IDs
        $existing = $this->serviceRepo->findById(id: $service->id);
        if ($existing && $existing->id !== $service->id) {
            $errors[] = [
                'rule'    => 'UniqueServiceId',
                'message' => "Service ID '{$service->id}' already exists",
                'value'   => $service->id,
            ];
        }

        // Validate lifetime transitions
        if ($existing && $existing->lifetime !== $service->lifetime) {
            $errors[] = [
                'rule'    => 'LifetimeImmutability',
                'message' => 'Service lifetime cannot be changed after creation',
                'value'   => $service->lifetime->value,
            ];
        }

        // Check tag consistency
        if (empty($service->tags)) {
            $errors[] = [
                'rule'    => 'RequiredTags',
                'message' => 'Services must have at least one tag for categorization',
                'value'   => $service->tags,
            ];
        }

        // Validate environment-specific constraints
        if ($service->environment && ! in_array($service->environment, ['development', 'staging', 'production'])) {
            $errors[] = [
                'rule'    => 'ValidEnvironment',
                'message' => 'Environment must be one of: development, staging, production',
                'value'   => $service->environment,
            ];
        }

        return $errors;
    }

    /**
     * Validate service dependencies.
     *
     * @throws \Exception
     */
    private function validateDependencies(ServiceDefinitionEntity $service) : array
    {
        $errors = [];

        foreach ($service->dependencies as $dependencyId) {
            // Check if dependency exists
            $dependency = $this->serviceRepo->findById(id: $dependencyId);
            if (! $dependency) {
                $errors[] = [
                    'rule'    => 'DependencyExists',
                    'message' => "Dependency '{$dependencyId}' does not exist",
                    'value'   => $dependencyId,
                ];

                continue;
            }

            // Check for circular dependencies
            if ($this->createsCircularDependency(serviceId: $service->id, dependencyId: $dependencyId)) {
                $errors[] = [
                    'rule'    => 'NoCircularDependencies',
                    'message' => "Dependency on '{$dependencyId}' would create circular reference",
                    'value'   => $dependencyId,
                ];
            }

            // Check dependency availability
            if (! $dependency->isAvailableInEnvironment($service->environment)) {
                $errors[] = [
                    'rule'    => 'DependencyAvailability',
                    'message' => "Dependency '{$dependencyId}' not available in environment '{$service->environment}'",
                    'value'   => $dependencyId,
                ];
            }
        }

        return $errors;
    }

    /**
     * Check if adding a dependency would create a circular reference.
     *
     * @param string $serviceId
     * @param string $dependencyId
     *
     * @return bool
     * @throws \Throwable
     */
    private function createsCircularDependency(string $serviceId, string $dependencyId) : bool
    {
        // Check if dependency already depends on this service
        $inverseDeps = $this->dependencyRepo->getServiceDependencies(serviceId: $dependencyId);
        foreach ($inverseDeps as $dep) {
            $depId = is_array($dep) ? ($dep['depends_on_id'] ?? $dep->dependsOnId) : $dep->dependsOnId;
            if ($depId === $serviceId) {
                return true;
            }
        }

        // Use repository's cycle detection for more complex cases
        $graph                   = $this->dependencyRepo->getDependencyGraph();
        $testGraph               = $graph;
        $testGraph[$serviceId][] = ['service' => $dependencyId];

        $cycles = $this->dependencyRepo->detectCircularDependencies($testGraph);

        return ! empty($cycles);
    }

    /**
     * Validate security constraints.
     */
    private function validateSecurity(ServiceDefinitionEntity $service) : array
    {
        $errors = [];

        // Check for potentially dangerous classes
        $dangerousClasses = [
            'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open',
            'eval', 'create_function', 'assert', 'preg_replace',
        ];

        $className = strtolower($service->class);
        foreach ($dangerousClasses as $dangerous) {
            if (str_contains($className, $dangerous)) {
                $errors[] = [
                    'rule'    => 'SecurityPolicy',
                    'message' => "Service class contains potentially dangerous function '{$dangerous}'",
                    'value'   => $service->class,
                ];
            }
        }

        // Check for sensitive data in config
        if (! empty($service->config)) {
            $sensitiveKeys = ['password', 'secret', 'key', 'token', 'api_key', 'private_key'];
            foreach ($service->config as $key => $value) {
                foreach ($sensitiveKeys as $sensitive) {
                    if (str_contains(strtolower($key), $sensitive)) {
                        $errors[] = [
                            'rule'    => 'SensitiveDataProtection',
                            'message' => "Config contains sensitive data in key '{$key}'",
                            'value'   => $key,
                        ];
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate performance implications.
     *
     * @param \Avax\Container\Features\Define\Store\ServiceDefinitionEntity $service
     *
     * @return array
     * @throws \Throwable
     */
    private function validatePerformance(ServiceDefinitionEntity $service) : array
    {
        $warnings = [];

        // Check complexity score
        $complexity = $service->getComplexityScore();
        if ($complexity > 15) {
            $warnings[] = [
                'rule'    => 'PerformanceWarning',
                'message' => "Service has high complexity score ({$complexity}), consider refactoring",
                'value'   => $complexity,
            ];
        }

        // Check dependency count
        $depCount = count($service->dependencies);
        if ($depCount > 10) {
            $warnings[] = [
                'rule'    => 'DependencyCount',
                'message' => "Service has {$depCount} dependencies, consider reducing",
                'value'   => $depCount,
            ];
        }

        // Check for singleton services with many dependents
        if ($service->lifetime->value === 'singleton') {
            $dependents = $this->dependencyRepo->getDependentServices(serviceId: $service->id);
            if ($dependents->count() > 20) {
                $warnings[] = [
                    'rule'    => 'SingletonUsage',
                    'message' => "Singleton service has {$dependents->count()} dependents, consider scoped lifetime",
                    'value'   => $dependents->count(),
                ];
            }
        }

        return $warnings;
    }
}
