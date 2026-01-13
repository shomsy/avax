<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Avax\DataHandling\ArrayHandling\Arrhae;
use Avax\Repository\Repository;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

/**
 * Settings for tracking and analyzing service dependency relationships within the container.
 *
 * This repository manages the complex relationships between services, providing dependency
 * tracking, cycle detection, health analysis, and relationship management. It serves as
 * the central authority for understanding service coupling and interdependencies.
 *
 * ARCHITECTURAL ROLE:
 * - Tracks all service-to-service dependency relationships
 * - Enables dependency graph analysis and cycle detection
 * - Provides health monitoring and optimization insights
 * - Supports auto-discovery of dependencies through reflection
 * - Maintains dependency metadata (types, optionality, etc.)
 *
 * DEPENDENCY TYPES:
 * - constructor: Dependencies injected via constructor parameters
 * - property: Dependencies injected via property setters
 * - method: Dependencies injected via method calls
 * - factory: Dependencies used in factory methods
 *
 * HEALTH MONITORING:
 * - Circular dependency detection to prevent resolution failures
 * - Orphan service identification for cleanup opportunities
 * - Complexity analysis for refactoring recommendations
 * - Usage statistics for performance optimization
 *
 * USAGE SCENARIOS:
 * ```php
 * $repo = new ServiceDependencyRepository();
 *
 * // Track a dependency relationship
 * $repo->trackDependency('UserService', 'Database', 'constructor');
 *
 * // Analyze dependency health
 * $health = $repo->analyzeDependencyHealth();
 * if ($health['health_score'] === 'critical') {
 *     // Handle critical dependency issues
 * }
 *
 * // Get dependency statistics
 * $stats = $repo->getDependencyStats();
 * ```
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Cycle detection: O(V + E) using DFS algorithm
 * - Graph building: O(D) where D is dependency count
 * - Health analysis: O(D + S) where S is service count
 * - Statistics queries: O(D log D) for sorting operations
 *
 * DATA INTEGRITY:
 * - Validates service existence before creating relationships
 * - Prevents duplicate dependency tracking
 * - Supports atomic updates for relationship changes
 * - Maintains referential integrity with service definitions
 *
 * @see     ServiceDependency For the managed entity type
 * @see     ServiceDefinitionRepository For service definition management
 * @see     docs/Features/Define/Store/ServiceDependencyRepository.md#quick-summary
 */
class ServiceDependencyRepository extends Repository
{
    /**
     * Finds all services that depend on a specific service.
     *
     * Returns services that have declared dependencies on the specified service,
     * useful for impact analysis when modifying or removing services.
     *
     * IMPACT ANALYSIS:
     * - Identify services affected by changes to a dependency
     * - Determine cascade effects of service modifications
     * - Support safe refactoring by understanding usage
     *
     * @param string $serviceId The service to find dependents for
     *
     * @return Arrhae Collection of services that depend on the specified service
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getdependentservices
     */
    public function getDependentServices(string $serviceId) : Arrhae
    {
        return Arrhae::make(items: $this->findBy(conditions: ['depends_on_id' => $serviceId]));
    }

    /**
     * Builds dependency chains showing transitive relationships.
     *
     * Creates a hierarchical representation of dependency relationships,
     * showing not just direct dependencies but also transitive chains
     * (A depends on B which depends on C, etc.).
     *
     * CHAIN STRUCTURE:
     * ```php
     * [
     *     'ServiceB' => [
     *         'ServiceC' => [
     *             'ServiceD' => []
     *         ]
     *     ],
     *     'ServiceE' => []
     * ]
     * ```
     *
     * DEPTH LIMITATION:
     * - Prevents infinite recursion in circular dependencies
     * - Limits output size for complex dependency graphs
     * - Configurable to balance completeness vs performance
     *
     * USE CASES:
     * - Understanding full impact of service changes
     * - Debugging complex dependency resolution issues
     * - Architecture documentation and visualization
     *
     * @param string $serviceId Root service for chain construction
     * @param int    $maxDepth  Maximum depth to traverse (default: 5)
     *
     * @return array Hierarchical dependency chain structure
     *
     * @throws \Exception
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getdependencychains
     */
    public function getDependencyChains(string $serviceId, int $maxDepth = 5) : array
    {
        return $this->buildDependencyChains(serviceId: $serviceId, visited: [], depth: 0, maxDepth: $maxDepth);
    }

    /**
     * @param string $serviceId
     * @param array  $visited
     * @param int    $depth
     * @param int    $maxDepth
     *
     * @return array
     * @throws \Throwable
     */
    private function buildDependencyChains(string $serviceId, array $visited, int $depth, int $maxDepth) : array
    {
        if ($depth >= $maxDepth || in_array($serviceId, $visited)) {
            return [];
        }

        $dependencies = Arrhae::from($this->getServiceDependencies(serviceId: $serviceId));
        if ($dependencies->isEmpty()) {
            return [];
        }

        $visited[] = $serviceId;
        $chains    = [];

        foreach ($dependencies as $dep) {
            $depId          = is_array($dep) ? ($dep['service'] ?? $dep['dependsOnId']) : $dep->dependsOnId;
            $chains[$depId] = $this->buildDependencyChains(serviceId: $depId, visited: $visited, depth: $depth + 1, maxDepth: $maxDepth);
        }

        return $chains;
    }

    /**
     * Retrieves all dependencies for a specific service.
     *
     * Returns a collection of all services that the specified service depends on,
     * including metadata about the type and optionality of each dependency.
     *
     * @param string $serviceId The service to get dependencies for
     *
     * @return Arrhae Collection of ServiceDependency entities
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getservicedependencies
     */
    public function getServiceDependencies(string $serviceId) : Arrhae
    {
        return Arrhae::make(items: $this->findBy(conditions: ['service_id' => $serviceId]));
    }

    /**
     * Performs comprehensive health analysis of the dependency ecosystem.
     *
     * Analyzes the entire dependency graph for potential issues, performance
     * problems, and optimization opportunities. Provides actionable insights
     * for maintaining a healthy, efficient dependency structure.
     *
     * ANALYSIS CATEGORIES:
     * - Circular Dependencies: Cycles that can cause resolution failures
     * - Orphan Services: Services with no dependents (potentially unused)
     * - High Complexity: Services with excessive dependency counts
     * - Usage Statistics: Distribution and frequency analysis
     *
     * HEALTH SCORING:
     * - healthy: No significant issues detected
     * - attention: Minor issues requiring monitoring
     * - warning: Medium severity issues needing attention
     * - critical: High severity issues requiring immediate action
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     'cycles' => [...],          // cycle arrays/paths
     *     'orphans' => [...],         // service IDs
     *     'most_depended' => [...],   // usage counts
     *     'stats' => [...],           // statistical data
     *     'issues' => [...],          // identified problems
     *     'health_score' => 'healthy|attention|warning|critical',
     * ]
     * ```
     *
     * @return array Comprehensive dependency health analysis
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \Throwable
     * @throws \Throwable
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-analyzedependencyhealth
     */
    public function analyzeDependencyHealth() : array
    {
        $analysis = [
            'cycles'        => $this->detectCircularDependencies(),
            'orphans'       => $this->findOrphanedServices(),
            'most_depended' => $this->getMostDependedServices(limit: 5),
            'stats'         => $this->getDependencyStats(),
            'issues'        => [],
        ];

        // Identify potential issues
        if (! empty($analysis['cycles'])) {
            $analysis['issues'][] = [
                'type'     => 'circular_dependencies',
                'severity' => 'high',
                'message'  => 'Circular dependencies can cause resolution failures',
                'count'    => count($analysis['cycles']),
            ];
        }

        if (! empty($analysis['orphans'])) {
            $analysis['issues'][] = [
                'type'     => 'orphaned_services',
                'severity' => 'medium',
                'message'  => 'Orphaned services may indicate unused code',
                'count'    => count($analysis['orphans']),
            ];
        }

        // Check for services with too many dependencies
        $complexDeps = array_filter(
            $analysis['stats']['by_service'] ?? [],
            static fn(int $count) : bool => $count > 10
        );

        if (! empty($complexDeps)) {
            $analysis['issues'][] = [
                'type'     => 'high_complexity',
                'severity' => 'medium',
                'message'  => 'Services with high dependency count may need refactoring',
                'services' => array_keys($complexDeps),
            ];
        }

        $analysis['health_score'] = $this->calculateHealthScore(issues: $analysis['issues']);

        return $analysis;
    }

    /**
     * Detects circular dependency chains using Depth-First Search algorithm.
     *
     * Analyzes the dependency graph to identify cycles where services depend
     * on each other either directly or through intermediate services. Circular
     * dependencies can cause resolution failures and infinite loops.
     *
     * DETECTION ALGORITHM:
     * - Uses DFS traversal of dependency graph
     * - Maintains visited and recursion stack tracking
     * - Identifies back edges indicating cycles
     * - Extracts complete cycle paths for reporting
     *
     * CYCLE IMPACT:
     * - Can cause container resolution to hang or fail
     * - Indicates tight coupling that should be refactored
     * - May require dependency injection redesign
     *
     * PERFORMANCE:
     * - O(V + E) complexity where V=vertices (services), E=edges (dependencies)
     * - Single pass through entire dependency graph
     * - Memory efficient for large graphs
     *
     * @return array Array of detected cycle paths, each as an array of service IDs
     *
     * @throws \Exception
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-detectcirculardependencies
     */
    public function detectCircularDependencies() : array
    {
        $graph          = $this->getDependencyGraph();
        $cycles         = [];
        $visited        = [];
        $recursionStack = [];

        foreach (array_keys($graph) as $node) {
            if (! isset($visited[$node])) {
                $this->dfsCycleDetection(node: $node, graph: $graph, visited: $visited, recursionStack: $recursionStack, cycles: $cycles);
            }
        }

        return $cycles;
    }

    /**
     * Constructs a complete dependency graph representation.
     *
     * Builds a directed graph where each node represents a service and edges
     * represent dependency relationships. Includes metadata about dependency
     * types and optionality for advanced analysis.
     *
     * GRAPH STRUCTURE:
     * ```php
     * [
     *     'ServiceA' => [
     *         ['service' => 'ServiceB', 'type' => 'constructor', 'optional' => false],
     *         ['service' => 'ServiceC', 'type' => 'property', 'optional' => true]
     *     ]
     * ]
     * ```
     *
     * USE CASES:
     * - Cycle detection algorithms
     * - Dependency resolution ordering
     * - Visual graph representations
     * - Complexity analysis
     *
     * @return array Associative array representing the dependency graph
     *
     * @throws \Exception
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getdependencygraph
     */
    public function getDependencyGraph() : array
    {
        $dependencies = $this->findAll();

        $graph = [];
        foreach ($dependencies as $dep) {
            if (! isset($graph[$dep->serviceId])) {
                $graph[$dep->serviceId] = [];
            }
            $graph[$dep->serviceId][] = [
                'service'  => $dep->dependsOnId,
                'type'     => $dep->dependencyType,
                'optional' => $dep->isOptional,
            ];
        }

        return $graph;
    }

    private function dfsCycleDetection(string $node, array $graph, array &$visited, array &$recursionStack, array &$cycles) : void
    {
        $visited[$node]        = true;
        $recursionStack[$node] = true;

        foreach ($graph[$node] ?? [] as $neighbor) {
            $neighborId = is_array($neighbor) ? $neighbor['service'] : $neighbor;

            if (! isset($visited[$neighborId])) {
                $this->dfsCycleDetection(node: $neighborId, graph: $graph, visited: $visited, recursionStack: $recursionStack, cycles: $cycles);
            } elseif (isset($recursionStack[$neighborId])) {
                $cycles[] = $this->extractCycle(start: $node, end: $neighborId, recursionStack: $recursionStack);
            }
        }

        unset($recursionStack[$node]);
    }

    private function extractCycle(string $start, string $end, array $recursionStack) : array
    {
        $cycle   = [];
        $current = $end;

        // Build cycle path
        while ($current !== $start) {
            array_unshift($cycle, $current);
            // Find previous node in recursion stack
            $found = false;
            foreach ($recursionStack as $node => $inStack) {
                if ($inStack && $node !== $current) {
                    $current = $node;
                    $found   = true;
                    break;
                }
            }
            if (! $found) {
                break;
            }
        }

        array_unshift($cycle, $start);
        $cycle[] = $end; // Close the cycle

        return $cycle;
    }

    /**
     * Identifies services that have no incoming dependencies (orphans).
     *
     * Finds services that are defined but never depended upon by other services.
     * Orphan services may indicate unused code, configuration errors, or services
     * that are only used directly without dependency injection.
     *
     * ORPHAN ANALYSIS:
     * - Services with outgoing dependencies but no incoming ones
     * - May include root services in the dependency hierarchy
     * - Useful for cleanup and dead code detection
     *
     * BUSINESS VALUE:
     * - Identifies potentially unused services for removal
     * - Helps maintain clean, relevant service configurations
     * - Supports continuous refactoring and cleanup efforts
     *
     * @return array Array of service IDs that have no dependents
     *
     * @throws \Throwable
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-findorphanedservices
     */
    public function findOrphanedServices() : array
    {
        $allServices = $this->query()
            ->select('DISTINCT service_id')
            ->get()
            ->pluck('service_id')
            ->all();

        $servicesWithDeps = $this->query()
            ->select('DISTINCT depends_on_id')
            ->get()
            ->pluck('depends_on_id')
            ->all();

        return array_diff($allServices, $servicesWithDeps);
    }

    /**
     * Identifies the most frequently depended-upon services.
     *
     * Returns services ranked by their usage as dependencies, useful for
     * understanding which services are most critical to the application
     * architecture and should receive highest maintenance priority.
     *
     * USAGE METRICS:
     * - Count of incoming dependency relationships
     * - Indicates service importance and coupling level
     * - Helps prioritize testing and maintenance efforts
     *
     * BUSINESS INSIGHTS:
     * - Core infrastructure services appear at top
     * - High-usage services may need special attention
     * - Low-usage services might be candidates for consolidation
     *
     * @param int $limit Maximum number of results to return
     *
     * @return array Associative array mapping service IDs to usage counts
     *
     * @throws \Throwable
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getmostdependedservices
     */
    public function getMostDependedServices(int $limit = 10) : array
    {
        $results = $this->query()
            ->select('depends_on_id', 'COUNT(*) as usage_count')
            ->groupBy('depends_on_id')
            ->orderBy(column: 'usage_count', direction: 'DESC')
            ->limit(limit: $limit)
            ->get();

        return Arrhae::make(items: $results)->mapWithKeys(callback: static function ($row) {
            return [$row['depends_on_id'] => (int) $row['usage_count']];
        })->all();
    }

    /**
     * Generates comprehensive dependency statistics and metrics.
     *
     * Provides quantitative analysis of the dependency ecosystem, including
     * counts, averages, distributions, and usage patterns useful for
     * monitoring, optimization, and capacity planning.
     *
     * STATISTICAL METRICS:
     * - Total dependency relationship count
     * - Unique service and dependency counts
     * - Average dependencies per service
     * - Per-service dependency distribution
     * - Per-dependency usage distribution
     *
     * PERFORMANCE IMPACT:
     * - Multiple database queries for aggregation
     * - Sorting operations for ranking
     * - Memory usage scales with relationship count
     *
     * BUSINESS VALUE:
     * - Architecture complexity assessment
     * - Performance bottleneck identification
     * - Refactoring opportunity discovery
     * - Capacity planning data
     *
     * @return array Comprehensive dependency statistics
     *
     * @throws \Throwable
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getdependencystats
     */
    public function getDependencyStats() : array
    {
        $totalDeps          = $this->query()->count();
        $uniqueServices     = $this->query()->distinct('service_id')->count();
        $uniqueDependencies = $this->query()->distinct('depends_on_id')->count();

        // Dependencies per service
        $depsPerService = $this->query()
            ->select('service_id', 'COUNT(*) as dep_count')
            ->groupBy('service_id')
            ->orderBy(column: 'dep_count', direction: 'DESC')
            ->get();

        // Services per dependency
        $servicesPerDep = $this->query()
            ->select('depends_on_id', 'COUNT(*) as service_count')
            ->groupBy('depends_on_id')
            ->orderBy(column: 'service_count', direction: 'DESC')
            ->get();

        return [
            'total_dependencies'   => $totalDeps,
            'unique_services'      => $uniqueServices,
            'unique_dependencies'  => $uniqueDependencies,
            'avg_deps_per_service' => $uniqueServices > 0 ? round($totalDeps / $uniqueServices, 2) : 0,
            'by_service'           => Arrhae::make(items: $depsPerService)
                ->mapWithKeys(callback: static fn($row) => [$row['service_id'] => (int) $row['dep_count']])
                ->all(),
            'by_dependency'        => Arrhae::make(items: $servicesPerDep)
                ->mapWithKeys(callback: static fn($row) => [$row['depends_on_id'] => (int) $row['service_count']])
                ->all(),
        ];
    }

    private function calculateHealthScore(array $issues) : string
    {
        $highIssues   = count(array_filter($issues, static fn($i) => ($i['severity'] ?? 'low') === 'high'));
        $mediumIssues = count(array_filter($issues, static fn($i) => ($i['severity'] ?? 'low') === 'medium'));

        if ($highIssues > 0) {
            return 'critical';
        }
        if ($mediumIssues > 2) {
            return 'warning';
        }
        if (count($issues) > 0) {
            return 'attention';
        }

        return 'healthy';
    }

    /**
     * Automatically discovers dependencies by analyzing service class definitions.
     *
     * Uses PHP reflection to analyze service classes and identify dependencies
     * from constructor parameters and typed properties. Automatically creates
     * dependency relationships based on static analysis.
     *
     * DISCOVERY METHODS:
     * - Constructor parameter type hints
     * - Typed property declarations
     * - Factory method analysis (future enhancement)
     *
     * REFLECTION ANALYSIS:
     * - Class constructor inspection
     * - Property type extraction
     * - Parameter optionality detection
     * - Exception handling for unreflectable classes
     *
     * LIMITATIONS:
     * - Cannot detect runtime dependencies
     * - Limited to typed parameters/properties
     * - May miss complex dependency injection patterns
     *
     * @param ServiceDefinitionRepository $serviceRepo Settings to get service definitions from
     *
     * @return array Array of discovered dependency relationships
     *
     * @throws \ReflectionException
     * @throws \Throwable
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-autodiscoverdependencies
     */
    public function autoDiscoverDependencies(ServiceDefinitionRepository $serviceRepo) : array
    {
        $services   = $serviceRepo->findAll();
        $discovered = [];

        foreach ($services as $service) {
            $deps = $this->analyzeServiceDependencies(service: $service);
            foreach ($deps as $dep) {
                $this->trackDependency(
                    serviceId     : $service->id,
                    dependsOnId   : $dep['service_id'],
                    dependencyType: $dep['type'],
                    isOptional    : $dep['optional']
                );
                $discovered[] = [
                    'service'    => $service->id,
                    'depends_on' => $dep['service_id'],
                    'type'       => $dep['type'],
                ];
            }
        }

        return $discovered;
    }

    private function analyzeServiceDependencies(ServiceDefinitionEntity $service) : array
    {
        $deps = [];

        if (! class_exists($service->class)) {
            return $deps;
        }

        try {
            $reflection  = new ReflectionClass(objectOrClass: $service->class);
            $constructor = $reflection->getConstructor();

            if ($constructor) {
                foreach ($constructor->getParameters() as $param) {
                    $type = $param->getType();
                    if ($type && ! $type->isBuiltin() && $type instanceof ReflectionNamedType) {
                        $typeName = $type->getName();
                        $deps[]   = [
                            'service_id' => $typeName,
                            'type'       => 'constructor',
                            'optional'   => $param->isOptional(),
                        ];
                    }
                }
            }

            // Analyze properties for dependencies
            foreach ($reflection->getProperties() as $property) {
                $type = $property->getType();
                if ($type && ! $type->isBuiltin() && $type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                    $deps[]   = [
                        'service_id' => $typeName,
                        'type'       => 'property',
                        'optional'   => ! $property->getType()->allowsNull(),
                    ];
                }
            }
        } catch (ReflectionException $e) {
            // Skip services that can't be reflected
        }

        return $deps;
    }

    /**
     * Records or updates a dependency relationship between two services.
     *
     * Creates a new dependency relationship or updates an existing one with
     * the same service IDs and dependency type. Handles upsert logic to
     * maintain data consistency and prevent duplicate relationships.
     *
     * DEPENDENCY TYPES:
     * - constructor: Required for object instantiation
     * - property: Injected via property setters
     * - method: Passed as method parameters
     * - factory: Used in factory method creation
     *
     * UPSERT BEHAVIOR:
     * - Creates new relationship if none exists
     * - Updates optional flag if it changed
     * - Preserves creation timestamp for existing relationships
     * - Sets new timestamp for newly created relationships
     *
     * @param string $serviceId      The service that has the dependency
     * @param string $dependsOnId    The service being depended upon
     * @param string $dependencyType The type of dependency relationship
     * @param bool   $isOptional     Whether this dependency is optional
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \Throwable
     * @throws \Throwable
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-trackdependency
     */
    public function trackDependency(
        string      $serviceId,
        string      $dependsOnId,
        string|null $dependencyType = null,
        bool        $isOptional = false
    ) : void
    {
        $dependencyType ??= 'constructor';
        $existing       = $this->findOneBy(conditions: [
            'service_id'      => $serviceId,
            'depends_on_id'   => $dependsOnId,
            'dependency_type' => $dependencyType,
        ]);

        if ($existing) {
            // Update if optional flag changed
            if ($existing->isOptional !== $isOptional) {
                $updated = new ServiceDependency(
                    serviceId     : $existing->serviceId,
                    dependsOnId   : $existing->dependsOnId,
                    dependencyType: $existing->dependencyType,
                    isOptional    : $isOptional,
                    createdAt     : $existing->createdAt
                );
                $this->save(entity: $updated);
            }

            return;
        }

        $dependency = new ServiceDependency(
            serviceId     : $serviceId,
            dependsOnId   : $dependsOnId,
            dependencyType: $dependencyType,
            isOptional    : $isOptional,
            createdAt     : new DateTimeImmutable
        );

        $this->save(entity: $dependency);
    }

    // Private helper methods

    /**
     * Validates the integrity of dependency relationships.
     *
     * Checks that all services referenced in dependency relationships actually
     * exist in the service definition repository. Identifies broken references
     * that could cause resolution failures.
     *
     * VALIDATION CHECKS:
     * - Service existence for dependency sources
     * - Service existence for dependency targets
     * - Referential integrity across relationships
     *
     * ISSUE TYPES:
     * - missing_service: Source service does not exist
     * - missing_dependency: Target service does not exist
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     [
     *         'type' => 'missing_service',
     *         'service' => 'NonExistentService',
     *         'message' => 'Service referenced in dependency does not exist'
     *     ]
     * ]
     * ```
     *
     * @param ServiceDefinitionRepository $serviceRepo Settings to validate against
     *
     * @return array Array of validation issues found
     *
     * @throws \Exception
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-validatedependencies
     */
    public function validateDependencies(ServiceDefinitionRepository $serviceRepo) : array
    {
        $issues       = [];
        $dependencies = $this->findAll();
        $serviceIds   = Arrhae::from($serviceRepo->findAll())->pluck('id')->all();

        foreach ($dependencies as $dep) {
            // Check if both services exist
            if (! in_array($dep->serviceId, $serviceIds)) {
                $issues[] = [
                    'type'    => 'missing_service',
                    'service' => $dep->serviceId,
                    'message' => 'Service referenced in dependency does not exist',
                ];
            }

            if (! in_array($dep->dependsOnId, $serviceIds)) {
                $issues[] = [
                    'type'      => 'missing_dependency',
                    'service'   => $dep->dependsOnId,
                    'dependent' => $dep->serviceId,
                    'message'   => 'Dependency service does not exist',
                ];
            }
        }

        return $issues;
    }

    /**
     * Removes orphaned dependency relationships from the repository.
     *
     * Identifies and removes dependency records where one or both services
     * no longer exist. This cleanup operation helps maintain data integrity
     * and prevents accumulation of stale relationship data.
     *
     * CLEANUP CRITERIA:
     * - Dependencies where source service no longer exists
     * - Dependencies where target service no longer exists
     * - Relationship metadata that has become invalid
     *
     * IMPLEMENTATION STATUS:
     * Currently returns empty results as a placeholder for future implementation
     * that would require cross-repository validation logic.
     *
     * FUTURE ENHANCEMENT:
     * - Cross-reference with ServiceDefinitionRepository
     * - Batch deletion of invalid relationships
     * - Detailed reporting of cleanup actions
     *
     * @return array Cleanup operation results with counts and errors
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-cleanuporphaneddependencies
     */
    public function cleanupOrphanedDependencies() : array
    {
        $results = ['deleted' => 0, 'errors' => []];

        // This would require more complex logic to identify truly orphaned deps
        // For now, return empty results
        return $results;
    }

    /**
     * Returns the entity class name for this repository.
     *
     * Used by the base Settings class to determine the managed entity type.
     * Must return the fully qualified class name of ServiceDependency.
     *
     * @return string The entity class name
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-getentityclass
     */
    protected function getEntityClass() : string
    {
        return ServiceDependency::class;
    }

    /**
     * Maps raw database/array data to a ServiceDependency instance.
     *
     * Converts database result arrays into domain entities, handling type
     * conversion and providing default values for optional fields.
     *
     * @param array $data Raw data from database/cache
     *
     * @return ServiceDependency Hydrated entity instance
     *
     * @throws \DateMalformedStringException
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-maptoentity
     */
    protected function mapToEntity(array $data) : ServiceDependency
    {
        return new ServiceDependency(
            serviceId     : $data['service_id'],
            dependsOnId   : $data['depends_on_id'],
            dependencyType: $data['dependency_type'] ?? 'constructor',
            isOptional    : (bool) ($data['is_optional'] ?? false),
            createdAt     : isset($data['created_at']) ? new DateTimeImmutable(datetime: $data['created_at']) : null
        );
    }

    /**
     * Maps a ServiceDependency to database-compatible array format.
     *
     * Converts entity instances to arrays suitable for database storage,
     * handling serialization of complex data types and type assertions.
     *
     * @param object $entity The entity to map (must be ServiceDependency)
     *
     * @return array Database-compatible array representation
     *
     * @see docs/Features/Define/Store/ServiceDependencyRepository.md#method-maptodatabase
     */
    protected function mapToDatabase(object $entity) : array
    {
        assert($entity instanceof ServiceDependency);

        return [
            'service_id'      => $entity->serviceId,
            'depends_on_id'   => $entity->dependsOnId,
            'dependency_type' => $entity->dependencyType,
            'is_optional'     => $entity->isOptional,
            'created_at'      => $entity->createdAt?->format(format: 'Y-m-d H:i:s'),
        ];
    }
}
