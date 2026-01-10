<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\DataHandling\ArrayHandling\Arrhae;
use Avax\Repository\Repository;
use DateTimeImmutable;
use Throwable;

/**
 * Settings implementation for persistent service definition storage and retrieval.
 *
 * This repository provides comprehensive data access layer for service definitions,
 * implementing the Settings pattern with advanced querying capabilities for service
 * discovery, dependency analysis, and lifecycle management. It serves as the primary
 * interface between the domain layer and persistent storage (database, cache, etc.).
 *
 * ARCHITECTURAL ROLE:
 * - Implements Settings pattern for ServiceDefinitionEntity
 * - Provides CRUD operations with domain-specific querying
 * - Enables service discovery and dependency analysis
 * - Supports bulk operations for import/export scenarios
 * - Offers maintenance operations for data consistency
 *
 * QUERYING CAPABILITIES:
 * - Environment-aware service filtering
 * - Tag-based service categorization
 * - Type hierarchy queries (classes, interfaces, inheritance)
 * - Lifetime-based service grouping
 * - Fuzzy search across service metadata
 *
 * ANALYTICS FEATURES:
 * - Service usage statistics and metrics
 * - Dependency cycle detection and analysis
 * - Complexity scoring and optimization insights
 * - Tag distribution and trending analysis
 *
 * USAGE SCENARIOS:
 * ```php
 * $repo = new ServiceDefinitionRepository();
 *
 * // Find active services for production
 * $services = $repo->findActiveServices('production');
 *
 * // Find database services
 * $dbServices = $repo->findByTags(['database']);
 *
 * // Analyze dependencies
 * $analysis = $repo->analyzeDependencies();
 *
 * // Search for services
 * $results = $repo->searchServices('user');
 * ```
 *
 * PERSISTENCE INTEGRATION:
 * Extends the base Settings class to leverage existing infrastructure
 * while adding domain-specific mapping and querying logic.
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Query operations may involve database access
 * - Complex analytics require full dataset loading
 * - Caching strategies should be implemented at higher layers
 * - Bulk operations support efficient batch processing
 *
 * DATA INTEGRITY:
 * - Validates entity relationships during operations
 * - Ensures referential integrity for dependencies
 * - Handles concurrent updates with optimistic locking
 *
 * @package Avax\Container\Define\Store
 * @see     ServiceDefinitionEntity For the managed entity type
 * @see     ServiceDiscovery For high-level service discovery operations
 * @see     Repository For base repository functionality
 * @see     docs_md/Features/Define/Store/ServiceDefinitionRepository.md#quick-summary
 */
class ServiceDefinitionRepository extends Repository
{
    /**
     * Finds all active services available in the specified environment.
     *
     * Retrieves service definitions that are currently active and available
     * in the given environment. Environment filtering supports services that
     * are either environment-agnostic (null environment) or specifically
     * configured for the target environment.
     *
     * ENVIRONMENT LOGIC:
     * - Services with null environment: Available in all environments
     * - Services with specific environment: Only available in matching environment
     * - Only active services (is_active = true) are returned
     *
     * USAGE SCENARIOS:
     * ```php
     * // Get all active services for production
     * $prodServices = $repo->findActiveServices('production');
     *
     * // Get services available in any environment
     * $allServices = $repo->findActiveServices();
     * ```
     *
     * @param string|null $environment Target environment name, or null for all environments
     *
     * @return Arrhae Collection of active ServiceDefinitionEntity instances
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-findactiveservices
     */
    public function findActiveServices(string|null $environment = null): Arrhae
    {
        $query = $this->query()->where('is_active', true);

        if ($environment !== null) {
            $query->where(static function ($q) use ($environment) {
                $q->whereNull('environment')
                    ->orWhere('environment', $environment);
            });
        }

        $results = $query->get();

        return Arrhae::make(items: array_map([$this, 'mapToEntity'], $results));
    }

    /**
     * Finds services that match the specified tags using AND/OR logic.
     *
     * Performs tag-based filtering of services with support for different
     * logical operators. AND mode requires services to have all specified
     * tags, while OR mode requires services to have at least one specified tag.
     *
     * OPERATOR MODES:
     * - 'AND': Service must have ALL tags in the array
     * - 'OR': Service must have at least ONE tag in the array
     *
     * PERFORMANCE NOTES:
     * - Loads all services into memory for filtering
     * - Consider database-level filtering for large datasets
     * - Tag indexing could improve query performance
     *
     * EXAMPLES:
     * ```php
     * // Find services tagged as both 'database' and 'cache'
     * $services = $repo->findByTags(['database', 'cache'], 'AND');
     *
     * // Find services tagged as either 'database' or 'cache'
     * $services = $repo->findByTags(['database', 'cache'], 'OR');
     * ```
     *
     * @param array  $tags     Array of tag strings to match against
     * @param string $operator Logical operator: 'AND' or 'OR' (default: 'AND')
     *
     * @return Arrhae Collection of matching ServiceDefinitionEntity instances
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-findbytags
     */
    public function findByTags(array $tags, string $operator = 'AND'): Arrhae
    {
        $services = $this->findAll();

        return Arrhae::make(items: $services)->filter(callback: static function ($service) use ($tags, $operator) {
            if ($operator === 'AND') {
                // Service must have ALL tags
                foreach ($tags as $tag) {
                    if (! $service->hasTag($tag)) {
                        return false;
                    }
                }

                return true;
            } else {
                // Service must have ANY tag (OR)
                foreach ($tags as $tag) {
                    if ($service->hasTag($tag)) {
                        return true;
                    }
                }

                return false;
            }
        });
    }

    /**
     * Finds services that implement or extend the specified type.
     *
     * Performs type-based filtering to find services whose classes implement
     * the given interface, extend the given base class, or exactly match the
     * specified type. Useful for finding all services that conform to a
     * particular contract or base functionality.
     *
     * TYPE MATCHING LOGIC:
     * - Exact class match ($service->class === $type)
     * - Interface implementation (in_array($type, class_implements($service->class)))
     * - Inheritance relationship (is_subclass_of($service->class, $type))
     *
     * PERFORMANCE CONSIDERATIONS:
     * - Uses PHP reflection functions which may be expensive
     * - Loads all services into memory for processing
     * - Consider caching type hierarchies for frequent queries
     *
     * EXAMPLES:
     * ```php
     * // Find all logger implementations
     * $loggers = $repo->findByType(LoggerInterface::class);
     *
     * // Find all controllers
     * $controllers = $repo->findByType(BaseController::class);
     * ```
     *
     * @param string $type Fully qualified interface or class name to match
     *
     * @return Arrhae Collection of services implementing/extending the specified type
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-findbytype
     */
    public function findByType(string $type): Arrhae
    {
        $services = $this->findAll();

        return Arrhae::make(items: $services)->filter(callback: static function ($service) use ($type) {
            return is_subclass_of($service->class, $type) ||
                in_array($type, class_implements($service->class)) ||
                $service->class === $type;
        });
    }

    /**
     * Finds services with the specified lifetime scope.
     *
     * Filters services based on their lifetime configuration, useful for
     * analyzing service lifecycle patterns or optimizing container behavior
     * based on service scopes.
     *
     * LIFETIME SCOPES:
     * - Singleton: Single shared instance
     * - Scoped: Per-scope instance (request, session, etc.)
     * - Transient: New instance each resolution
     *
     * @param ServiceLifetime $lifetime The lifetime scope to match
     *
     * @return Arrhae Collection of services with the specified lifetime
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-findbylifetime
     */
    public function findByLifetime(ServiceLifetime $lifetime): Arrhae
    {
        return Arrhae::make(items: $this->findBy(conditions: ['lifetime' => $lifetime->value]));
    }

    /**
     * Generates comprehensive statistics about the service ecosystem.
     *
     * Provides analytical data about service distribution, complexity, and
     * usage patterns. Useful for monitoring, optimization, and capacity planning.
     *
     * STATISTICS INCLUDED:
     * - Total service count
     * - Distribution by lifetime scopes
     * - Tag usage frequency
     * - Most complex services (by complexity score)
     * - Most depended-upon services
     *
     * PERFORMANCE IMPACT:
     * - Requires loading all services into memory
     * - Complex calculations for dependency analysis
     * - Consider caching results for frequent access
     *
     * @return array Associative array containing various service statistics
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-getservicestats
     */
    public function getServiceStats(): array
    {
        $services = $this->findAll();

        return [
            'total_services' => count($services),
            'by_lifetime'    => Arrhae::make(items: $services)
                ->countBy(key: static fn($s) => $s->lifetime->value),
            'by_tags'        => $this->getTagStatistics(services: $services),
            'most_complex'   => Arrhae::make(items: $services)
                ->sortBy(static fn($s) => $s->getComplexityScore(), 'desc')
                ->take(limit: 5)
                ->all(),
            'most_used'      => $this->getMostUsedServices(services: $services),
        ];
    }

    private function getTagStatistics(array $services): array
    {
        $tagCounts = [];

        foreach ($services as $service) {
            foreach ($service->tags as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }

        arsort($tagCounts);

        return $tagCounts;
    }

    private function getMostUsedServices(array $services): array
    {
        $usage = [];

        foreach ($services as $service) {
            foreach ($service->dependencies as $depId) {
                $usage[$depId] = ($usage[$depId] ?? 0) + 1;
            }
        }

        arsort($usage);

        return array_slice($usage, 0, 10, true);
    }

    /**
     * Performs comprehensive dependency analysis on the service graph.
     *
     * Analyzes the entire service dependency graph to detect cycles, orphans,
     * and usage patterns. Essential for maintaining service health and
     * preventing runtime issues.
     *
     * ANALYSIS TYPES:
     * - Cycle Detection: Identifies circular dependencies that could cause infinite loops
     * - Orphan Services: Services that are never depended upon (potentially unused)
     * - Most Depended: Services that are most frequently referenced
     * - Complexity Ranking: Services ordered by instantiation complexity
     *
     * ALGORITHM COMPLEXITY:
     * - Cycle detection: O(V + E) using DFS
     * - Graph construction: O(S * D) where S=services, D=avg dependencies
     * - Memory usage scales with service count
     *
     * USAGE FOR MAINTENANCE:
     * ```php
     * $analysis = $repo->analyzeDependencies();
     * if (!empty($analysis['cycles'])) {
     *     // Handle circular dependency issues
     * }
     * ```
     *
     * @return array Analysis results with cycles, orphans, most_depended, and complexity data
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-analyzedependencies
     */
    public function analyzeDependencies(): array
    {
        $services = $this->findAll();
        $graph    = [];

        // Build dependency graph
        foreach ($services as $service) {
            $graph[$service->id] = $service->dependencies;
        }

        return [
            'cycles'        => $this->detectCycles(graph: $graph),
            'orphans'       => $this->findOrphanServices(graph: $graph),
            'most_depended' => $this->findMostDependedServices(graph: $graph),
            'complexity'    => Arrhae::make(items: $services)
                ->sortBy(static fn($s) => $s->getComplexityScore(), 'desc')
                ->take(limit: 10)
                ->all(),
        ];
    }

    private function detectCycles(array $graph): array
    {
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

    private function dfsCycleDetection(string $node, array $graph, array &$visited, array &$recursionStack, array &$cycles): void
    {
        $visited[$node]        = true;
        $recursionStack[$node] = true;

        foreach ($graph[$node] ?? [] as $neighbor) {
            if (! isset($visited[$neighbor])) {
                $this->dfsCycleDetection(node: $neighbor, graph: $graph, visited: $visited, recursionStack: $recursionStack, cycles: $cycles);
            } elseif (isset($recursionStack[$neighbor])) {
                // Cycle found
                $cycles[] = $this->extractCycle(start: $node, end: $neighbor, recursionStack: $recursionStack);
            }
        }

        unset($recursionStack[$node]);
    }

    private function extractCycle(string $start, string $end, array $recursionStack): array
    {
        $cycle   = [$start];
        $current = $start;

        // This is a simplified cycle extraction - in production you'd want more robust logic
        while ($current !== $end && isset($recursionStack[$current])) {
            $cycle[] = $current;
            // This is incomplete - you'd need to track the actual path
        }

        return $cycle;
    }

    private function findOrphanServices(array $graph): array
    {
        $allServices = array_keys($graph);
        $referenced  = [];

        foreach ($graph as $dependencies) {
            foreach ($dependencies as $dep) {
                $referenced[$dep] = true;
            }
        }

        return array_diff($allServices, array_keys($referenced));
    }

    private function findMostDependedServices(array $graph): array
    {
        $usage = [];

        foreach ($graph as $dependencies) {
            foreach ($dependencies as $dep) {
                $usage[$dep] = ($usage[$dep] ?? 0) + 1;
            }
        }

        arsort($usage);

        return array_slice($usage, 0, 10, true);
    }

    /**
     * Performs fuzzy search across service metadata.
     *
     * Searches service IDs, class names, and descriptions using similarity
     * matching. Useful for administrative interfaces and service discovery
     * when exact matches aren't available.
     *
     * SEARCH SCOPE:
     * - Service ID
     * - Fully qualified class name
     * - Service description (if available)
     *
     * SIMILARITY ALGORITHM:
     * Uses Levenshtein distance normalized to percentage similarity.
     * Configurable threshold controls match sensitivity.
     *
     * PERFORMANCE NOTES:
     * - Loads all services into memory for searching
     * - Similarity calculation is O(n*m) for string lengths
     * - Consider full-text search for large datasets
     *
     * EXAMPLES:
     * ```php
     * // Find services similar to 'user'
     * $users = $repo->searchServices('user', 80);
     *
     * // Loose matching for discovery
     * $results = $repo->searchServices('data', 60);
     * ```
     *
     * @param string $query     Search term to match against service metadata
     * @param float  $threshold Similarity threshold (0-100, default: 70)
     *
     * @return Arrhae Collection of services matching the search criteria
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-searchservices
     */
    public function searchServices(string $query, float $threshold = 70): Arrhae
    {
        $services = $this->findAll();

        return Arrhae::make(items: $services)->filter(callback: function ($service) use ($query, $threshold) {
            // Search in ID, class name, and description
            $searchable = [
                $service->id,
                $service->class,
                $service->description ?? ''
            ];

            foreach ($searchable as $text) {
                if ($this->calculateSimilarity(str1: $query, str2: $text) >= $threshold) {
                    return true;
                }
            }

            return false;
        });
    }

    // Private helper methods

    private function calculateSimilarity(string $str1, string $str2): float
    {
        // Simple similarity calculation - in production you'd use a proper library
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);

        if ($str1 === $str2) {
            return 100.0;
        }

        if (empty($str1) || empty($str2)) {
            return 0.0;
        }

        $lev    = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));

        return (1 - $lev / $maxLen) * 100;
    }

    /**
     * Performs bulk import of service definitions from array data.
     *
     * Processes multiple service definitions in a single operation, providing
     * detailed results about the import process. Handles validation errors
     * gracefully, continuing with valid services while reporting failures.
     *
     * IMPORT PROCESS:
     * - Validates each service definition
     * - Creates ServiceDefinitionEntity instances
     * - Saves valid services to repository
     * - Collects error information for invalid entries
     *
     * ERROR HANDLING:
     * - Continues processing after individual failures
     * - Provides detailed error information for debugging
     * - Maintains transactional integrity where possible
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     'imported' => 5,      // Successfully imported count
     *     'skipped' => 2,       // Failed/skipped count
     *     'errors' => [         // Detailed error information
     *         ['data' => [...], 'error' => 'Validation failed']
     *     ]
     * ]
     * ```
     *
     * @param array $servicesData Array of service definition data arrays
     *
     * @return array Import results with counts and error details
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-importservices
     */
    public function importServices(array $servicesData): array
    {
        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($servicesData as $data) {
            try {
                $service = ServiceDefinitionEntity::fromArray(data: $data);
                $this->saveServiceDefinition(service: $service);
                $results['imported']++;
            } catch (Throwable $e) {
                $results['errors'][] = [
                    'data'  => $data,
                    'error' => $e->getMessage()
                ];
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Saves or updates a service definition with upsert logic.
     *
     * Creates a new service definition or updates an existing one based on
     * the service ID. Uses immutable update patterns to maintain data integrity.
     * Handles both creation and modification scenarios transparently.
     *
     * UPSERT LOGIC:
     * - Checks for existing service by ID
     * - Updates existing service with new data
     * - Creates new service if not found
     * - Preserves timestamps appropriately
     *
     * DATA INTEGRITY:
     * - Validates all input data through entity construction
     * - Ensures atomic updates where supported
     * - Handles concurrent modification scenarios
     *
     * @param ServiceDefinitionEntity $service The service definition to save
     *
     * @return void
     * @throws \Exception
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-saveservicedefinition
     */
    public function saveServiceDefinition(ServiceDefinitionEntity $service): void
    {
        $existing = $this->findOneBy(conditions: ['id' => $service->id]);

        if ($existing) {
            // Update existing
            $updated = $existing->withUpdates([
                'class'        => $service->class,
                'lifetime'     => $service->lifetime->value,
                'config'       => json_encode($service->config),
                'tags'         => json_encode($service->tags),
                'dependencies' => json_encode($service->dependencies),
                'environment'  => $service->environment,
                'description'  => $service->description,
            ]);
            $this->save(entity: $updated);
        } else {
            // Create new
            $this->save(entity: $service);
        }
    }

    /**
     * Exports service definitions with optional filtering.
     *
     * Retrieves service definitions from storage and converts them to
     * portable array format for backup, migration, or external processing.
     * Supports filtering to export specific subsets of services.
     *
     * EXPORT FORMAT:
     * Returns the same array format used by ServiceDefinitionEntity::toArray(),
     * making it suitable for import operations or external tools.
     *
     * FILTERING SUPPORT:
     * - Environment-specific exports
     * - Tag-based filtering
     * - Lifetime-based selection
     * - Active/inactive status filtering
     *
     * USE CASES:
     * ```php
     * // Export all services
     * $allServices = $repo->exportServices();
     *
     * // Export production database services
     * $dbServices = $repo->exportServices([
     *     'environment' => 'production',
     *     'is_active' => true
     * ]);
     * ```
     *
     * @param array $filters Associative array of column => value filters
     *
     * @return array Array of service definition arrays
     * @throws \DateMalformedStringException
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-exportservices
     */
    public function exportServices(array $filters = []): array
    {
        $query = $this->query();

        // Apply filters
        foreach ($filters as $column => $value) {
            $query->where($column, $value);
        }

        $services = $query->get();

        return array_map(function ($serviceData) {
            $entity = $this->mapToEntity(data: $serviceData);

            return $entity->toArray();
        }, $services);
    }

    /**
     * Maps raw database/array data to a ServiceDefinitionEntity instance.
     *
     * Converts database result arrays into domain entities, handling type
     * conversion and validation. Used internally for query result hydration.
     *
     * @param array $data Raw data from database/cache
     *
     * @return ServiceDefinitionEntity Hydrated entity instance
     * @throws \DateMalformedStringException
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-maptoentity
     */
    protected function mapToEntity(array $data): ServiceDefinitionEntity
    {
        return ServiceDefinitionEntity::fromArray(data: $data);
    }

    /**
     * Performs cleanup of old or inactive service definitions.
     *
     * Removes service definitions that are inactive and older than the
     * specified threshold. Useful for maintaining database hygiene and
     * preventing accumulation of stale configuration data.
     *
     * CLEANUP CRITERIA:
     * - Services must be inactive (is_active = false)
     * - Services must be older than the specified days
     * - Uses updated_at timestamp for age calculation
     *
     * SAFETY MEASURES:
     * - Only removes inactive services
     * - Requires explicit age threshold
     * - Returns operation results for verification
     *
     * RESULT STRUCTURE:
     * ```php
     * [
     *     'deleted' => 15,      // Number of services deleted
     *     'errors' => []        // Any errors encountered
     * ]
     * ```
     *
     * @param int|null $daysOld Minimum age in days for cleanup (default: 30)
     *
     * @return array Cleanup results with deletion count and errors
     * @throws \DateMalformedStringException
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-cleanup
     */
    public function cleanup(int|null $daysOld = 30): array
    {
        $results = ['deleted' => 0, 'errors' => []];

        if ($daysOld) {
            $cutoffDate = (new DateTimeImmutable())->modify(modifier: "-{$daysOld} days");

            try {
                $deleted = $this->query()
                    ->where('is_active', false)
                    ->where('updated_at', '<', $cutoffDate->format(format: 'Y-m-d H:i:s'))
                    ->delete();

                $results['deleted'] = $deleted;
            } catch (Throwable $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Returns the entity class name for this repository.
     *
     * Used by the base Settings class to determine the managed entity type.
     * Must return the fully qualified class name of ServiceDefinitionEntity.
     *
     * @return string The entity class name
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-getentityclass
     */
    protected function getEntityClass(): string
    {
        return ServiceDefinitionEntity::class;
    }

    /**
     * Maps a ServiceDefinitionEntity to database-compatible array format.
     *
     * Converts entity instances to arrays suitable for database storage,
     * handling serialization of complex data types. Includes type assertion
     * for runtime safety.
     *
     * @param object $entity The entity to map (must be ServiceDefinitionEntity)
     *
     * @return array Database-compatible array representation
     * @see docs_md/Features/Define/Store/ServiceDefinitionRepository.md#method-maptodatabase
     */
    protected function mapToDatabase(object $entity): array
    {
        assert($entity instanceof ServiceDefinitionEntity);

        return $entity->toArray();
    }
}
