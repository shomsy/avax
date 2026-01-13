<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Entity\Entity;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Domain Entity representing a service definition within the dependency injection container.
 *
 * This entity encapsulates all metadata and configuration for a service registration,
 * serving as the authoritative source of truth for service definitions in the container.
 * It implements Domain-Driven Design principles with rich business logic for validation,
 * querying, and service management operations.
 *
 * ARCHITECTURAL ROLE:
 * - Central data model for service definitions
 * - Immutable value object with validation
 * - Supports persistence through repository pattern
 * - Enables complex querying and filtering operations
 * - Provides service metadata for optimization and monitoring
 *
 * SERVICE LIFECYCLE:
 * ServiceDefinitionEntity tracks the complete lifecycle of service definitions from
 * registration through validation, storage, querying, and eventual resolution.
 *
 * KEY FEATURES:
 * - Comprehensive validation of service metadata
 * - Environment-aware service availability
 * - Tag-based service categorization
 * - Dependency relationship tracking
 * - Complexity scoring for optimization
 * - Immutable updates through builder pattern
 *
 * USAGE SCENARIOS:
 * ```php
 * // Creating a service definition
 * $definition = new ServiceDefinitionEntity(
 *     id: 'database.connection',
 *     class: DatabaseConnection::class,
 *     lifetime: ServiceLifetime::Singleton,
 *     config: ['host' => 'localhost', 'port' => 3306],
 *     tags: ['database', 'infrastructure'],
 *     dependencies: ['config.database'],
 *     environment: 'production',
 *     description: 'Main database connection service'
 * );
 *
 * // Querying service relationships
 * if ($definition->hasTag('database')) {
 *     // Handle database service
 * }
 *
 * if ($definition->dependsOn('config.database')) {
 *     // Handle dependency relationship
 * }
 * ```
 *
 * PERSISTENCE:
 * The entity supports full serialization/deserialization for storage in various
 * backends (database, cache, files) through the repository pattern.
 *
 * VALIDATION RULES:
 * - Service ID and class cannot be empty
 * - Target class/interface must exist
 * - Dependencies must be non-empty strings
 * - Environment constraints are optional
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Immutable design allows safe sharing
 * - Validation occurs at construction time
 * - Complexity scoring enables optimization decisions
 * - JSON serialization for storage efficiency
 *
 * THREAD SAFETY:
 * Immutable readonly properties ensure thread-safe access across concurrent operations.
 *
 * @see     ServiceDefinitionRepository For persistence operations
 * @see     ServiceDiscovery For querying operations
 * @see     ServiceLifetime For lifetime enumeration
 * @see     docs/Features/Define/Store/ServiceDefinitionEntity.md#quick-summary
 */
class ServiceDefinitionEntity extends Entity
{
    /**
     * Creates a new ServiceDefinitionEntity with comprehensive validation.
     *
     * Initializes a service definition with all required and optional metadata.
     * Performs immediate validation to ensure data integrity and consistency.
     * All properties are readonly to maintain immutability.
     *
     * VALIDATION PERFORMED:
     * - Non-empty service ID and class name
     * - Existence of target class or interface
     * - Valid dependency specifications
     * - Proper data types for all fields
     *
     * @param string                  $id           Unique identifier for the service within the container
     * @param string                  $class        Fully qualified class name or interface that this service provides
     * @param ServiceLifetime         $lifetime     Service lifetime scope (Singleton, Scoped, Transient)
     * @param array                   $config       Associative array of configuration parameters for service
     *                                              initialization
     * @param array                   $tags         String array of tags for service categorization and querying
     * @param array                   $dependencies String array of service IDs this service depends on
     * @param string|null             $environment  Environment name where this service is available (null = all
     *                                              environments)
     * @param string|null             $description  Human-readable description of the service's purpose
     * @param bool                    $isActive     Whether this service definition is currently active
     * @param \DateTimeImmutable|null $createdAt    Timestamp when this definition was created
     * @param \DateTimeImmutable|null $updatedAt    Timestamp when this definition was last updated
     *
     * @throws \InvalidArgumentException When validation fails for any required field
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-__construct
     */
    public function __construct(
        public readonly string                 $id,
        public readonly string                 $class,
        public readonly ServiceLifetime        $lifetime,
        public readonly array                  $config = [],
        public readonly array                  $tags = [],
        public readonly array                  $dependencies = [],
        public readonly string|null            $environment = null,
        public readonly string|null            $description = null,
        public readonly bool                   $isActive = true,
        public readonly DateTimeImmutable|null $createdAt = null,
        public readonly DateTimeImmutable|null $updatedAt = null,
    )
    {
        $this->validate();
    }

    /**
     * Validates all entity data according to business rules.
     *
     * Performs comprehensive validation of the service definition to ensure
     * data integrity and consistency. Called automatically during construction.
     * Throws exceptions for any validation failures to prevent invalid entities.
     *
     * VALIDATION RULES:
     * - Service ID must be non-empty after trimming
     * - Class name must be non-empty and exist as class or interface
     * - All dependencies must be non-empty strings
     * - Arrays must contain valid data types
     *
     * ERROR MESSAGES:
     * Provides specific, actionable error messages for debugging and user feedback.
     *
     * @throws \InvalidArgumentException When any validation rule is violated
     *
     * @private Called automatically during construction
     */
    private function validate() : void
    {
        if (empty(trim($this->id))) {
            throw new InvalidArgumentException(message: 'Service ID cannot be empty');
        }

        if (empty(trim($this->class))) {
            throw new InvalidArgumentException(message: 'Service class cannot be empty');
        }

        if (! class_exists($this->class) && ! interface_exists($this->class)) {
            throw new InvalidArgumentException(message: "Service class '{$this->class}' does not exist");
        }

        foreach ($this->dependencies as $dep) {
            if (! is_string($dep) || empty(trim($dep))) {
                throw new InvalidArgumentException(message: 'All dependencies must be non-empty strings');
            }
        }
    }

    /**
     * Returns the database table name for this entity type.
     *
     * Provides the canonical table name used for persistence operations.
     * Follows naming conventions for container-related database tables.
     *
     * TABLE NAMING:
     * Uses snake_case with 'container_' prefix for namespace isolation.
     *
     * @return string The database table name
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-gettablename
     */
    public static function getTableName() : string
    {
        return 'container_service_definitions';
    }

    /**
     * Checks if the service has been tagged with a specific tag.
     *
     * Performs exact string matching against the service's tag array.
     * Useful for filtering services by category or capability.
     *
     * TAG USAGE:
     * ```php
     * if ($service->hasTag('database')) {
     *     // Handle database-related service
     * }
     *
     * if ($service->hasTag('cache')) {
     *     // Handle caching service
     * }
     * ```
     *
     * @param string $tag The tag to check for
     *
     * @return bool True if the service has the specified tag
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-hastag
     */
    public function hasTag(string $tag) : bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * Checks if the service depends on a specific other service.
     *
     * Determines if the given service ID is listed as a dependency of this service.
     * Useful for building dependency graphs and detecting circular references.
     *
     * DEPENDENCY ANALYSIS:
     * ```php
     * if ($serviceA->dependsOn('serviceB')) {
     *     // serviceA requires serviceB to function
     *     // Check for circular dependencies
     * }
     * ```
     *
     * @param string $serviceId The service ID to check for as a dependency
     *
     * @return bool True if this service depends on the specified service
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-dependson
     */
    public function dependsOn(string $serviceId) : bool
    {
        return in_array($serviceId, $this->dependencies, true);
    }

    /**
     * Calculates a complexity score for the service based on its configuration.
     *
     * Provides a numerical score indicating the relative complexity of instantiating
     * and managing this service. Used by optimizers to prioritize caching and
     * resolution strategies.
     *
     * SCORING FACTORS:
     * - Base score: 1 (minimum complexity)
     * - Dependencies: +2 points each (resolution overhead)
     * - Scoped lifetime: +3 points (context management)
     * - Configuration: +1 point per config entry (initialization cost)
     *
     * OPTIMIZATION USE:
     * ```php
     * $complexServices = array_filter($services,
     *     fn($s) => $s->getComplexityScore() > 5
     * );
     * // Cache complex services more aggressively
     * ```
     *
     * @return int Complexity score (higher = more complex)
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-getcomplexityscore
     */
    public function getComplexityScore() : int
    {
        $score = 1; // Base score

        // More dependencies = higher complexity
        $score += count($this->dependencies) * 2;

        // Scoped services are more complex
        if ($this->lifetime === ServiceLifetime::Scoped) {
            $score += 3;
        }

        // Configuration adds complexity
        if (! empty($this->config)) {
            $score += count($this->config);
        }

        return $score;
    }

    /**
     * Determines if the service is available in the specified environment.
     *
     * Checks environment constraints to see if this service can be used in
     * the given runtime environment. Services without environment constraints
     * are available in all environments.
     *
     * ENVIRONMENT LOGIC:
     * - No environment constraint (null): Available everywhere
     * - Specific environment set: Only available in matching environment
     * - Environment mismatch: Not available
     *
     * USAGE SCENARIOS:
     * ```php
     * // Environment-specific services
     * $prodOnlyService = new ServiceDefinitionEntity(
     *     id: 'external.api',
     *     class: ExternalApi::class,
     *     environment: 'production'
     * );
     *
     * // Check availability
     * if ($service->isAvailableInEnvironment('production')) {
     *     // Safe to use in production
     * }
     * ```
     *
     * @param string|null $environment The environment to check availability for
     *
     * @return bool True if the service is available in the given environment
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-isavailableinenvironment
     */
    public function isAvailableInEnvironment(string|null $environment) : bool
    {
        if ($this->environment === null) {
            return true; // Available in all environments
        }

        return $this->environment === $environment;
    }

    /**
     * Creates an updated version of this service definition with modified fields.
     *
     * Implements the builder pattern for creating modified versions of immutable
     * entities. Only allows updates to fields that exist in the entity schema.
     * Automatically updates the modification timestamp.
     *
     * IMMUTABLE UPDATES:
     * ```php
     * $updatedService = $originalService->withUpdates([
     *     'description' => 'Updated description',
     *     'is_active' => false
     * ]);
     * // $originalService remains unchanged
     * ```
     *
     * VALIDATION:
     * Updates go through the same validation as new instances.
     *
     * AUDITING:
     * Automatically sets updated_at to current timestamp.
     *
     * @param array $updates Associative array of field => value updates
     *
     * @return self New entity instance with applied updates
     *
     * @throws \InvalidArgumentException When updates contain invalid data
     * @throws \DateMalformedStringException
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-withupdates
     */
    public function withUpdates(array $updates) : self
    {
        $data = $this->toArray();

        foreach ($updates as $key => $value) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        $data['updated_at'] = (new DateTimeImmutable)->format(format: 'Y-m-d H:i:s');

        return self::fromArray(data: $data);
    }

    /**
     * Serializes the entity to an array format suitable for storage.
     *
     * Converts the entity back to a flat array representation for persistence
     * in databases, caches, or configuration files. Handles type conversion
     * and JSON encoding for complex data structures.
     *
     * SERIALIZATION PROCESS:
     * - Converts enum to string value
     * - JSON encodes array properties
     * - Formats timestamps to database-compatible strings
     * - Preserves null values appropriately
     *
     * OUTPUT FORMAT:
     * ```php
     * [
     *     'id' => 'service.id',
     *     'class' => 'App\\Services\\MyService',
     *     'lifetime' => 'singleton',
     *     'config' => '{"host":"localhost"}',
     *     'tags' => '["database","infrastructure"]',
     *     'dependencies' => '["config.database"]',
     *     'environment' => 'production',
     *     'description' => 'Database connection service',
     *     'is_active' => true,
     *     'created_at' => '2024-01-01 12:00:00',
     *     'updated_at' => '2024-01-01 12:00:00'
     * ]
     * ```
     *
     * @return array Serialized entity data for storage
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-toarray
     */
    public function toArray() : array
    {
        return [
            'id'           => $this->id,
            'class'        => $this->class,
            'lifetime'     => $this->lifetime->value,
            'config'       => json_encode($this->config),
            'tags'         => json_encode($this->tags),
            'dependencies' => json_encode($this->dependencies),
            'environment'  => $this->environment,
            'description'  => $this->description,
            'is_active'    => $this->isActive,
            'created_at'   => $this->createdAt?->format(format: 'Y-m-d H:i:s'),
            'updated_at'   => $this->updatedAt?->format(format: 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Reconstructs a ServiceDefinitionEntity from serialized array data.
     *
     * Factory method for hydrating entities from persistent storage (database, cache, etc.).
     * Handles type conversion and default values for missing optional fields.
     * Used by repositories for loading service definitions from storage.
     *
     * DESERIALIZATION PROCESS:
     * - Maps array keys to constructor parameters
     * - Converts JSON strings back to arrays
     * - Parses enum values from stored strings
     * - Handles nullable timestamp fields
     * - Applies boolean conversion for active status
     *
     * STORAGE FORMAT:
     * ```php
     * [
     *     'id' => 'service.id',
     *     'class' => 'App\\Services\\MyService',
     *     'lifetime' => 'singleton',
     *     'config' => '{"key":"value"}',
     *     'tags' => '["tag1","tag2"]',
     *     'dependencies' => '["dep1","dep2"]',
     *     // ... other fields
     * ]
     * ```
     *
     * @param array $data Associative array of serialized entity data
     *
     * @return self Reconstructed entity instance
     *
     * @throws \DateMalformedStringException
     *
     * @see docs/Features/Define/Store/ServiceDefinitionEntity.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            id          : $data['id'],
            class       : $data['class'],
            lifetime    : ServiceLifetime::from(value: $data['lifetime']),
            config      : json_decode($data['config'] ?? '{}', true),
            tags        : json_decode($data['tags'] ?? '[]', true),
            dependencies: json_decode($data['dependencies'] ?? '[]', true),
            environment : $data['environment'] ?? null,
            description : $data['description'] ?? null,
            isActive    : (bool) ($data['is_active'] ?? true),
            createdAt   : isset($data['created_at']) ? new DateTimeImmutable(datetime: $data['created_at']) : null,
            updatedAt   : isset($data['updated_at']) ? new DateTimeImmutable(datetime: $data['updated_at']) : null,
        );
    }
}
