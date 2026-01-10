<?php

declare(strict_types=1);
namespace Avax\Container\Features\Define\Store;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\DataHandling\ArrayHandling\Arrhae;

/**
 * Intelligent service discovery and analysis engine for the dependency injection container.
 *
 * This class provides advanced service discovery capabilities beyond basic CRUD operations,
 * offering intelligent filtering, health analysis, conflict detection, and optimization
 * recommendations. It serves as the high-level query interface for service ecosystems,
 * enabling developers and tools to understand and manage complex service relationships.
 *
 * ARCHITECTURAL ROLE:
 * - High-level service query interface beyond basic repository operations
 * - Intelligent service filtering and discovery based on capabilities and relationships
 * - Health monitoring and optimization analysis for service ecosystems
 * - Recommendation engine for service configuration improvements
 * - Dependency analysis and conflict resolution support
 *
 * DISCOVERY CAPABILITIES:
 * - Interface-based service lookup with inheritance support
 * - Tag-based capability filtering with AND/OR logic
 * - Environment-aware service availability checking
 * - Alternative service discovery for failover scenarios
 * - Advanced multi-criteria search with complex filtering
 *
 * ANALYTICS FEATURES:
 * - Service health scoring and issue detection
 * - Dependency cycle analysis and orphan service identification
 * - Complexity analysis and optimization recommendations
 * - Usage pattern analysis and service relationship insights
 * - Migration suggestions for service configuration improvements
 *
 * USAGE SCENARIOS:
 * ```php
 * $discovery = new ServiceDiscovery($repository);
 *
 * // Find all cache implementations
 * $caches = $discovery->findServicesByInterface(CacheInterface::class);
 *
 * // Find services with logging and monitoring capabilities
 * $instrumentedServices = $discovery->findServicesWithCapabilities(['logging', 'metrics'], 'AND');
 *
 * // Analyze service ecosystem health
 * $health = $discovery->analyzeServiceHealth();
 * if ($health['overall_health'] === 'critical') {
 *     // Handle critical issues
 * }
 *
 * // Get optimization suggestions
 * $suggestions = $discovery->suggestMigrations();
 * ```
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Interface/type queries use PHP reflection (expensive, cache results)
 * - Health analysis loads all services into memory
 * - Advanced search supports complex filtering with good performance
 * - Dependency tree building is recursive with configurable depth limits
 * - Results are cached at repository level where possible
 *
 * ALGORITHM COMPLEXITY:
 * - Interface matching: O(n) where n is number of services
 * - Health analysis: O(s * d) where s=services, d=avg dependencies per service
 * - Dependency tree: O(d^depth) with exponential complexity (use depth limits)
 * - Advanced search: O(n * f) where f is number of applied filters
 *
 * THREAD SAFETY:
 * - Read-only operations are thread-safe
 * - No internal state modification during queries
 * - Depends on underlying repository thread safety
 *
 * @package Avax\Container\Define\Store
 * @see     ServiceDefinitionRepository For underlying data access
 * @see     ServiceDefinitionEntity For service data structure
 * @see docs/Features/Define/Store/ServiceDiscovery.md#quick-summary
 */
readonly class ServiceDiscovery
{
    /**
     * Creates a new ServiceDiscovery instance with a service repository.
     *
     * Initializes the discovery engine with access to the service definition
     * repository, enabling all discovery and analysis operations.
     *
     * @param ServiceDefinitionRepository $repository The repository providing service data access
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-__construct
     */
    public function __construct(
        private ServiceDefinitionRepository $repository
    ) {}

    /**
     * Finds services that are eligible for caching based on their lifetime.
     *
     * Identifies services that can be safely cached because they have singleton
     * or scoped lifetimes, meaning their instances can be reused across multiple
     * resolutions within the appropriate scope boundaries.
     *
     * CACHEABLE LIFETIMES:
     * - Singleton: Single shared instance for entire application
     * - Scoped: Per-scope instance (request, session, etc.)
     * - Transient: Not cacheable (new instance each time)
     *
     * USAGE FOR OPTIMIZATION:
     * ```php
     * // Pre-warm cache with cacheable services in production
     * $cacheable = $discovery->findCacheableServices('production');
     * foreach ($cacheable as $service) {
     *     $container->get($service->id); // Force instantiation
     * }
     * ```
     *
     * @param string|null $environment Target environment, or null for all environments
     *
     * @return Arrhae Collection of cacheable ServiceDefinitionEntity instances
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-findcacheableservices
     */
    public function findCacheableServices(string|null $environment = null) : Arrhae
    {
        $services = $this->repository->findActiveServices(environment: $environment);

        return $services->filter(callback: static function ($service) {
            return $service->lifetime === ServiceLifetime::Singleton ||
                $service->lifetime === ServiceLifetime::Scoped;
        });
    }

    /**
     * Finds services that implement multiple interfaces simultaneously (AND logic).
     *
     * Performs strict multi-interface matching where services must implement ALL
     * specified interfaces. Useful for finding services with compound capabilities
     * or multiple contractual obligations.
     *
     * MATCHING REQUIREMENTS:
     * - Service must implement every interface in the array
     * - Order of interfaces in array is irrelevant
     * - Partial matches are rejected (all or nothing)
     *
     * USE CASES:
     * ```php
     * // Find services that are both loggable and serializable
     * $services = $discovery->findServicesByInterfaces([
     *     LoggableInterface::class,
     *     SerializableInterface::class
     * ]);
     * ```
     *
     * @param array       $interfaces  Array of interface names that must all be implemented
     * @param string|null $environment Environment filter, null for all environments
     *
     * @return Arrhae Collection of services implementing all specified interfaces
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-findservicesbyinterfaces
     */
    public function findServicesByInterfaces(array $interfaces, string|null $environment = null) : Arrhae
    {
        $services = $this->repository->findActiveServices(environment: $environment);

        return $services->filter(callback: static function ($service) use ($interfaces) {
            foreach ($interfaces as $interface) {
                if (! is_subclass_of($service->class, $interface) &&
                    ! in_array($interface, class_implements($service->class))) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Finds services tagged with specific capabilities (delegates to repository).
     *
     * Provides a semantic wrapper around tag-based service discovery, framing
     * tags as "capabilities" for more intuitive API usage. Supports both AND
     * and OR logical operators for flexible capability matching.
     *
     * CAPABILITY CONCEPT:
     * Tags represent capabilities or characteristics that services provide,
     * enabling discovery based on functionality rather than implementation.
     *
     * EXAMPLES:
     * ```php
     * // Find services with caching capability
     * $caches = $discovery->findServicesWithCapabilities(['cache']);
     *
     * // Find services that are both cacheable AND serializable
     * $specialized = $discovery->findServicesWithCapabilities(['cache', 'serializable'], 'AND');
     * ```
     *
     * @param array  $capabilities Array of capability tags to match
     * @param string $operator     Logical operator: 'AND' or 'OR' (default: 'AND')
     *
     * @return Arrhae Collection of services with the specified capabilities
     * @throws \Exception
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-findserviceswithcapabilities
     */
    public function findServicesWithCapabilities(array $capabilities, string $operator = 'AND') : Arrhae
    {
        return $this->repository->findByTags(tags: $capabilities, operator: $operator);
    }

    /**
     * Finds alternative services that could replace a given service.
     *
     * Discovers services that implement the same interfaces as the specified
     * service, enabling failover scenarios, A/B testing, or service replacement
     * without changing dependent code.
     *
     * ALTERNATIVE DISCOVERY:
     * 1. Identifies all interfaces implemented by the original service
     * 2. Finds all other services implementing those interfaces
     * 3. Returns alternatives filtered by environment and excluding original
     *
     * USE CASES:
     * - Failover service selection
     * - Load balancing across multiple implementations
     * - A/B testing with different service implementations
     * - Graceful service migration
     *
     * @param string      $serviceId   ID of the service to find alternatives for
     * @param string|null $environment Environment filter for alternatives
     *
     * @return Arrhae Collection of alternative ServiceDefinitionEntity instances
     * @throws \Exception
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-findalternativeservices
     */
    public function findAlternativeServices(string $serviceId, string|null $environment = null) : Arrhae
    {
        $service = $this->repository->findById(id: $serviceId);
        if (! $service) {
            return Arrhae::make(items: []);
        }

        // Find all services implementing the same interfaces
        $interfaces = class_implements($service->class) ?: [];
        if (interface_exists($service->class)) {
            $interfaces[] = $service->class;
        }

        $alternatives = Arrhae::make(items: []);
        foreach ($interfaces as $interface) {
            $implementations = $this->findServicesByInterface(interface: $interface, environment: $environment);
            $alternatives    = $alternatives->merge(collection: $implementations);
        }

        // Remove the original service
        return $alternatives->filter(callback: static fn($s) => $s->id !== $serviceId);
    }

    /**
     * Finds all services that implement a specific interface or extend a base class.
     *
     * Performs type-based service discovery, identifying all services whose classes
     * implement the given interface or inherit from the given base class. This enables
     * polymorphic service resolution and capability-based discovery.
     *
     * TYPE MATCHING LOGIC:
     * - Interface implementation: in_array($interface, class_implements($service->class))
     * - Inheritance: is_subclass_of($service->class, $interface)
     * - Exact match: $service->class === $interface (for interface-to-interface)
     *
     * PERFORMANCE NOTES:
     * - Uses PHP reflection functions which are relatively expensive
     * - Consider caching results for frequently queried interfaces
     * - Results include environment filtering for active services only
     *
     * EXAMPLES:
     * ```php
     * // Find all logger implementations
     * $loggers = $discovery->findServicesByInterface(LoggerInterface::class);
     *
     * // Find all HTTP clients
     * $clients = $discovery->findServicesByInterface(HttpClientInterface::class, 'production');
     * ```
     *
     * @param string      $interface   Fully qualified interface or base class name
     * @param string|null $environment Environment filter, null for all environments
     *
     * @return Arrhae Collection of services implementing the specified type
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-findservicesbyinterface
     */
    public function findServicesByInterface(string $interface, string|null $environment = null) : Arrhae
    {
        $services = $this->repository->findActiveServices(environment: $environment);

        return $services->filter(callback: static function ($service) use ($interface) {
            return is_subclass_of($service->class, $interface) ||
                in_array($interface, class_implements($service->class));
        });
    }

    /**
     * Generates service recommendations based on usage patterns and relationships.
     *
     * Analyzes the service ecosystem to provide recommendations for services that
     * are likely related or commonly used together. This is a discovery feature:
     * it’s meant to help you navigate a large service catalog, not to be “always correct”.
     *
     * RECOMMENDATION STRATEGIES:
     * 1. Common Dependencies: services that share dependency sets
     * 2. Similar Tags: services that share tags/capabilities
     * 3. Same Domain: services that live in the same namespace “neighborhood”
     *
     * RECOMMENDATION STRUCTURE:
     * ```php
     * [
     *     'common_dependencies' => ServiceDefinitionEntity[],
     *     'similar_tags' => ServiceDefinitionEntity[],
     *     'same_domain' => ServiceDefinitionEntity[],
     * ]
     * ```
     *
     * @param string $currentServiceId The service to generate recommendations for
     *
     * @return array Associative array with recommendation categories and service lists
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-getservicerecommendations
     */
    public function getServiceRecommendations(string $currentServiceId) : array
    {
        $service = $this->repository->findById($currentServiceId);
        if (!$service) {
            return [];
        }

        $recommendations = [];

        // 1. Services that commonly depend on the same dependencies
        $commonDependencies = $this->findServicesWithCommonDependencies($service);

        // 2. Services with similar tags
        $similarTagged = $this->findServicesWithSimilarTags($service);

        // 3. Services in the same "domain" (by namespace)
        $domainServices = $this->findServicesInSameDomain($service);

        $recommendations['common_dependencies'] = $commonDependencies->take(5)->all();
        $recommendations['similar_tags'] = $similarTagged->take(5)->all();
        $recommendations['same_domain'] = $domainServices->take(5)->all();

        return $recommendations;
    }

    /**
     * Performs comprehensive health analysis of the service ecosystem.
     *
     * Analyzes the entire service graph for potential issues, performance problems,
     * and optimization opportunities. Provides actionable insights for maintaining
     * a healthy, efficient service ecosystem.
     *
     * ANALYSIS CATEGORIES:
     * - Circular Dependencies: Services with circular reference chains
     * - Orphan Services: Services that are never depended upon
     * - Complex Services: Services with high instantiation complexity
     * - Unused Services: Services not appearing in dependency chains
     *
     * HEALTH SCORING:
     * - healthy: No issues detected
     * - attention: Minor issues present
     * - warning: Medium severity issues
     * - critical: High severity issues requiring immediate action
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     'overall_health' => 'healthy|attention|warning|critical',
     *     'issues' => [/* issue entries */],
     *     'suggestions' => [/* optimization suggestions */],
     *     'stats' => [/* repository stats */],
     * ]
     * ```
     *
     * PERFORMANCE IMPACT:
     * - Loads all services and performs complete dependency analysis
     * - Complex algorithms: O(s^2) for cycle detection, O(s*d) for usage analysis
     * - Consider caching results for monitoring dashboards
     *
     * @return array Comprehensive health analysis with issues, suggestions, and statistics
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-analyzeservicehealth
     */
    public function analyzeServiceHealth(): array
    {
        $stats = $this->repository->getServiceStats();
        $dependencyAnalysis = $this->repository->analyzeDependencies();

        $issues = [];
        $suggestions = [];

        // Check for circular dependencies
        if (!empty($dependencyAnalysis['cycles'])) {
            $issues[] = [
                'type' => 'circular_dependencies',
                'severity' => 'high',
                'message' => 'Circular dependencies detected',
                'data' => $dependencyAnalysis['cycles']
            ];
        }

        // Check for orphan services
        if (!empty($dependencyAnalysis['orphans'])) {
            $issues[] = [
                'type' => 'orphan_services',
                'severity' => 'medium',
                'message' => 'Services with no dependencies found',
                'data' => $dependencyAnalysis['orphans']
            ];
        }

        // Check for over-complex services
        $complexServices = Arrhae::make($dependencyAnalysis['complexity'])
            ->filter(fn($s) => $s->getComplexityScore() > 10);

        if ($complexServices->isNotEmpty()) {
            $suggestions[] = [
                'type' => 'complexity_reduction',
                'message' => 'Consider breaking down complex services',
                'services' => $complexServices->pluck('id')->all()
            ];
        }

        // Check for unused services
        $usage = $stats['most_used'] ?? [];
        $allServices = $this->repository->findAll()->pluck('id')->all();
        $unused = array_diff($allServices, array_keys($usage));

        if (!empty($unused)) {
            $issues[] = [
                'type' => 'unused_services',
                'severity' => 'low',
                'message' => 'Potentially unused services detected',
                'data' => array_slice($unused, 0, 10) // Limit to first 10
            ];
        }

        return [
            'overall_health' => $this->calculateHealthScore($issues),
            'issues' => $issues,
            'suggestions' => $suggestions,
            'stats' => $stats
        ];
    }

    /**
     * Identifies services that might cause conflicts due to multiple implementations.
     *
     * Finds services that implement the same interfaces as other services,
     * potentially causing ambiguity in interface-based resolution or indicating
     * areas where service disambiguation might be needed.
     *
     * CONFLICT ANALYSIS:
     * - Groups services by implemented interfaces
     * - Identifies interfaces with multiple implementations
     * - Returns all services implementing multi-implementation interfaces
     *
     * CONFLICT RESOLUTION STRATEGIES:
     * - Use explicit service IDs instead of interface resolution
     * - Implement service qualification/selection logic
     * - Use different interfaces for different use cases
     * - Combine services using composite patterns
     *
     * @return Arrhae Collection of services that participate in interface conflicts
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-findpotentialconflicts
     */
    public function findPotentialConflicts(): Arrhae
    {
        $services = $this->repository->findAll();
        $conflicts = [];

        // Group by interfaces
        $byInterface = [];
        foreach ($services as $service) {
            $interfaces = class_implements($service->class) ?: [];
            foreach ($interfaces as $interface) {
                $byInterface[$interface][] = $service;
            }
        }

        // Find interfaces with multiple implementations
        foreach ($byInterface as $interface => $implementations) {
            if (count($implementations) > 1) {
                $conflicts = array_merge($conflicts, $implementations);
            }
        }

        return Arrhae::make($conflicts);
    }

    /**
     * Builds a hierarchical dependency tree for a specific service.
     *
     * Creates a nested representation of a service's dependency graph,
     * showing direct and transitive dependencies up to a specified depth.
     * Useful for understanding service coupling and debugging resolution issues.
     *
     * TREE STRUCTURE:
     * ```php
     * [
     *     'service' => 'service.id',
     *     'class' => 'App\\Services\\MyService',
     *     'dependencies' => [
     *         'dep1' => [/* subtree */],
     *         'dep2' => [/* subtree */],
     *     ],
     * ]
     * ```
     *
     * DEPTH LIMITATION:
     * - Prevents infinite recursion in circular dependencies
     * - Limits output size for large dependency graphs
     * - Default depth of 5 is suitable for most analysis
     *
     * PERFORMANCE NOTES:
     * - Recursive algorithm with exponential complexity potential
     * - Memory usage scales with dependency graph size
     * - Cycle detection prevents infinite loops
     *
     * @param string $serviceId Root service for tree construction
     * @param int $maxDepth Maximum depth to traverse (default: 5)
     * @return array Hierarchical dependency tree structure
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-getdependencytree
     */
    public function getDependencyTree(string $serviceId, int $maxDepth = 5): array
    {
        return $this->buildDependencyTree($serviceId, [], 0, $maxDepth);
    }

    /**
     * Performs advanced multi-criteria service search with complex filtering.
     *
     * Provides sophisticated search capabilities with support for multiple
     * filter types and criteria combinations. Enables precise service discovery
     * for administrative tools, monitoring systems, and development utilities.
     *
     * SUPPORTED FILTERS:
     * - tags: Services containing any of the specified tags (array)
     * - lifetime: Services with specific lifetime scope (string)
     * - interface: Services implementing specific interface (string)
     * - namespace: Services in specific namespace (string)
     * - complexity_min: Services above complexity threshold (int)
     * - has_dependencies: Services with/without dependencies (bool)
     *
     * FILTER APPLICATION:
     * - Filters are applied sequentially in the order provided
     * - Each filter narrows the result set further
     * - Invalid filter types are ignored with type coercion where appropriate
     *
     * TYPE COERCION:
     * - complexity_min values are cast to int for comparison
     * - has_dependencies values are cast to bool for comparison
     * - Other filters expect appropriate string/array types
     *
     * PERFORMANCE:
     * - Loads all services initially, then filters in memory
     * - Multiple filters compound performance impact
     * - Consider database-level filtering for production use
     *
     * @param array $filters Associative array of filter criteria
     * @return Arrhae Collection of services matching all filter criteria
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-advancedsearch
     */
    public function advancedSearch(array $filters): Arrhae
    {
        $services = $this->repository->findAll();

        foreach ($filters as $filterType => $filterValue) {
            switch ($filterType) {
                case 'tags':
                    $services = $services->filter(fn($s) =>
                        !empty(array_intersect($filterValue, $s->tags))
                    );
                    break;

                case 'lifetime':
                    $services = $services->filter(fn($s) =>
                        $s->lifetime->value === $filterValue
                    );
                    break;

                case 'interface':
                    $services = $services->filter(fn($s) =>
                        is_subclass_of($s->class, $filterValue) ||
                        in_array($filterValue, class_implements($s->class))
                    );
                    break;

                case 'namespace':
                    $services = $services->filter(fn($s) =>
                        str_starts_with($s->class, $filterValue)
                    );
                    break;

                case 'complexity_min':
                    $minComplexity = (int) $filterValue;
                    $services = $services->filter(fn($s) =>
                        $s->getComplexityScore() >= $minComplexity
                    );
                    break;

                case 'has_dependencies':
                    $hasDeps = (bool) $filterValue;
                    $services = $services->filter(fn($s) =>
                        !empty($s->dependencies) === $hasDeps
                    );
                    break;
            }
        }

        return $services;
    }

    /**
     * Generates intelligent migration suggestions for service configuration improvements.
     *
     * Analyzes the current service configuration and provides automated suggestions
     * for optimizations, corrections, and best practice improvements. Helps maintain
     * service ecosystem health and performance through proactive recommendations.
     *
     * SUGGESTION TYPES:
     * - change_lifetime: Services that should use different lifetime scopes
     * - add_tags: Services missing appropriate capability tags
     * - add_dependencies: Services with missing dependency declarations
     *
     * ANALYSIS HEURISTICS:
     * - Request-scoped detection based on class naming patterns
     * - Tag inference from class names and functionality
     * - Dependency analysis using reflection on constructor parameters
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     'service.id' => [
     *         'change_lifetime' => 'scoped',
     *         'add_tags' => ['logging', 'cache'],
     *         'add_dependencies' => ['dep.id'],
     *     ],
     * ]
     * ```
     *
     * IMPLEMENTATION NOTES:
     * - Uses heuristics and may produce false positives
     * - Suggestions should be reviewed by developers
     * - Automatic application requires careful validation
     *
     * @return array Associative array mapping service IDs to suggested changes
     * @see docs/Features/Define/Store/ServiceDiscovery.md#method-suggestmigrations
     */
    public function suggestMigrations(): array
    {
        $services = $this->repository->findAll();
        $suggestions = [];

        foreach ($services as $service) {
            $suggestion = [];

            // Check if service should be scoped instead of singleton
            if ($service->lifetime === ServiceLifetime::Singleton &&
                $this->isRequestScoped($service)) {
                $suggestion['change_lifetime'] = 'scoped';
            }

            // Check for missing tags
            if (empty($service->tags)) {
                $inferredTags = $this->inferTags($service);
                if (!empty($inferredTags)) {
                    $suggestion['add_tags'] = $inferredTags;
                }
            }

            // Check for missing dependencies
            $missingDeps = $this->findMissingDependencies($service);
            if (!empty($missingDeps)) {
                $suggestion['add_dependencies'] = $missingDeps;
            }

            if (!empty($suggestion)) {
                $suggestions[$service->id] = $suggestion;
            }
        }

        return $suggestions;
    }

    // Private helper methods

    private function findServicesWithCommonDependencies(ServiceDefinitionEntity $service): Arrhae
    {
        if (empty($service->dependencies)) {
            return Arrhae::make([]);
        }

        $services = $this->repository->findAll();

        return $services->filter(function($s) use ($service) {
            if ($s->id === $service->id) return false;

            $commonDeps = array_intersect($s->dependencies, $service->dependencies);
            return count($commonDeps) >= 2; // At least 2 common dependencies
        });
    }

    private function findServicesWithSimilarTags(ServiceDefinitionEntity $service): Arrhae
    {
        if (empty($service->tags)) {
            return Arrhae::make([]);
        }

        return $this->repository->findByTags($service->tags, 'OR')
            ->filter(fn($s) => $s->id !== $service->id);
    }

    private function findServicesInSameDomain(ServiceDefinitionEntity $service): Arrhae
    {
        $namespace = $this->extractNamespace($service->class);
        $services = $this->repository->findAll();

        return $services->filter(function($s) use ($namespace, $service) {
            if ($s->id === $service->id) return false;
            return str_starts_with($s->class, $namespace);
        });
    }

    private function buildDependencyTree(string $serviceId, array $visited, int $depth, int $maxDepth): array
    {
        if ($depth >= $maxDepth || in_array($serviceId, $visited)) {
            return [];
        }

        $service = $this->repository->findById($serviceId);
        if (!$service) {
            return [];
        }

        $visited[] = $serviceId;
        $tree = [
            'service' => $service->id,
            'class' => $service->class,
            'dependencies' => []
        ];

        foreach ($service->dependencies as $depId) {
            $tree['dependencies'][$depId] = $this->buildDependencyTree(
                $depId, $visited, $depth + 1, $maxDepth
            );
        }

        return $tree;
    }

    private function calculateHealthScore(array $issues): string
    {
        $highSeverity = count(array_filter($issues, fn($i) => ($i['severity'] ?? 'low') === 'high'));
        $mediumSeverity = count(array_filter($issues, fn($i) => ($i['severity'] ?? 'low') === 'medium'));

        if ($highSeverity > 0) return 'critical';
        if ($mediumSeverity > 2) return 'warning';
        if (count($issues) > 0) return 'attention';
        return 'healthy';
    }

    private function isRequestScoped(ServiceDefinitionEntity $service): bool
    {
        // Simple heuristic: if class name contains 'Request', 'Controller', etc.
        $requestIndicators = ['Request', 'Controller', 'Handler', 'Middleware'];
        $className = strtolower($service->class);

        foreach ($requestIndicators as $indicator) {
            if (str_contains($className, strtolower($indicator))) {
                return true;
            }
        }

        return false;
    }

    private function inferTags(ServiceDefinitionEntity $service): array
    {
        $tags = [];
        $className = strtolower($service->class);

        // Infer tags based on class name patterns
        if (str_contains($className, 'repository')) $tags[] = 'data';
        if (str_contains($className, 'service')) $tags[] = 'business';
        if (str_contains($className, 'cache')) $tags[] = 'cache';
        if (str_contains($className, 'logger') || str_contains($className, 'log')) $tags[] = 'logging';
        if (str_contains($className, 'http') || str_contains($className, 'client')) $tags[] = 'http';

        return array_unique($tags);
    }

    private function findMissingDependencies(ServiceDefinitionEntity $service): array
    {
        $missing = [];

        // Analyze constructor for dependencies
        if (class_exists($service->class)) {
            $reflection = new \ReflectionClass($service->class);
            $constructor = $reflection->getConstructor();

            if ($constructor) {
                foreach ($constructor->getParameters() as $param) {
                    $type = $param->getType();
                    if ($type && !$type->isBuiltin()) {
                        $typeName = $type->getName();
                        if (!in_array($typeName, $service->dependencies)) {
                            $missing[] = $typeName;
                        }
                    }
                }
            }
        }

        return $missing;
    }

    private function extractNamespace(string $className): string
    {
        $parts = explode('\\', $className);
        array_pop($parts); // Remove class name
        return implode('\\', $parts);
    }
}
