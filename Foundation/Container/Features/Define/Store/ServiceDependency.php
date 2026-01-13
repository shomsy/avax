<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Avax\Entity\Entity;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Domain Entity representing a directed dependency relationship between two services.
 *
 * This immutable entity captures the fundamental relationship between services in the
 * dependency injection container, defining how one service depends on another. It serves
 * as the core data structure for dependency graph analysis, cycle detection, and
 * relationship management throughout the container ecosystem.
 *
 * ARCHITECTURAL ROLE:
 * - Models directed service-to-service relationships
 * - Enables dependency graph construction and analysis
 * - Supports different injection patterns and strengths
 * - Provides metadata for optimization and monitoring
 * - Maintains audit trail with creation timestamps
 *
 * DEPENDENCY TYPES:
 * - constructor: Dependencies injected during object instantiation (strongest)
 * - property: Dependencies injected via public property access
 * - method/setter: Dependencies injected via method calls
 * - interface: Contractual dependencies without specific implementation
 *
 * DEPENDENCY CHARACTERISTICS:
 * - Directionality: Always from dependent to dependency
 * - Optionality: Whether the dependency is required or optional
 * - Strength: Relative importance based on injection timing
 * - Immutability: Once created, relationships cannot be modified
 *
 * USAGE SCENARIOS:
 * ```php
 * // Constructor dependency (required)
 * $dep = new ServiceDependency('UserService', 'Database', 'constructor', false);
 *
 * // Optional property dependency
 * $dep = new ServiceDependency('CacheService', 'Logger', 'property', true);
 *
 * // Interface contract dependency
 * $dep = new ServiceDependency('PaymentProcessor', 'PaymentInterface', 'interface', false);
 * ```
 *
 * RELATIONSHIP MODELING:
 * - serviceId â†’ dependsOnId (direction of dependency)
 * - dependencyType defines injection mechanism
 * - isOptional affects resolution strategy
 * - createdAt provides temporal context
 *
 * BUSINESS RULES:
 * - No self-dependencies (service cannot depend on itself)
 * - Valid dependency types enforced at construction
 * - Non-empty service identifiers required
 * - Referential integrity maintained with service definitions
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Immutable design allows safe sharing across threads
 * - Minimal memory footprint for relationship storage
 * - Efficient serialization for persistence operations
 * - Fast validation during construction
 *
 * @see     ServiceDependencyRepository For persistence and querying operations
 * @see     ServiceDefinitionEntity For the services being related
 * @see     docs/Features/Define/Store/ServiceDependency.md#quick-summary
 */
class ServiceDependency extends Entity
{
    /**
     * Creates a new ServiceDependency instance with relationship validation.
     *
     * Initializes an immutable dependency relationship between two services,
     * validating all business rules and data integrity constraints. The
     * dependency represents a directed edge in the service dependency graph.
     *
     * VALIDATION PERFORMED:
     * - Non-empty service and dependency identifiers
     * - No self-dependency relationships
     * - Valid dependency type from allowed values
     * - Proper data types for all parameters
     *
     * RELATIONSHIP SEMANTICS:
     * - serviceId: The service that has the dependency (dependent)
     * - dependsOnId: The service being depended upon (dependency)
     * - dependencyType: How the dependency is injected
     * - isOptional: Whether dependency failure should be tolerated
     *
     * @param string                  $serviceId      The identifier of the dependent service
     * @param string                  $dependsOnId    The identifier of the service being depended upon
     * @param string                  $dependencyType The mechanism of dependency injection
     * @param bool                    $isOptional     Whether this dependency is optional (can fail gracefully)
     * @param \DateTimeImmutable|null $createdAt      Timestamp when this dependency was established
     *
     * @throws \InvalidArgumentException When validation rules are violated
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-__construct
     */
    public function __construct(
        public readonly string                 $serviceId,
        public readonly string                 $dependsOnId,
        public readonly string                 $dependencyType = 'constructor',
        public readonly bool                   $isOptional = false,
        public readonly DateTimeImmutable|null $createdAt = null,
    )
    {
        $this->validate();
    }

    /**
     * Validates all dependency relationship data against business rules.
     *
     * Performs comprehensive validation of the dependency relationship to ensure
     * data integrity, prevent invalid relationships, and maintain graph consistency.
     * Called automatically during construction to enforce invariants.
     *
     * VALIDATION RULES:
     * - Service ID must be non-empty after trimming
     * - Dependency service ID must be non-empty after trimming
     * - Service cannot depend on itself (prevents cycles of length 1)
     * - Dependency type must be from allowed values
     * - All string fields must be properly formatted
     *
     * ALLOWED DEPENDENCY TYPES:
     * - constructor: Injected during instantiation
     * - property: Injected via property access
     * - method: Injected via method call
     * - setter: Injected via setter method
     * - interface: Contractual dependency
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
        if (empty(trim($this->serviceId))) {
            throw new InvalidArgumentException(message: 'Service ID cannot be empty');
        }

        if (empty(trim($this->dependsOnId))) {
            throw new InvalidArgumentException(message: 'Dependency service ID cannot be empty');
        }

        if ($this->serviceId === $this->dependsOnId) {
            throw new InvalidArgumentException(message: 'Service cannot depend on itself');
        }

        $validTypes = ['constructor', 'property', 'method', 'setter', 'interface'];
        if (! in_array($this->dependencyType, $validTypes)) {
            throw new InvalidArgumentException(
                message: "Invalid dependency type '{$this->dependencyType}'. Valid types: " . implode(', ', $validTypes)
            );
        }
    }

    /**
     * Reconstructs a ServiceDependency from serialized array data.
     *
     * Factory method for hydrating entities from persistent storage,
     * handling type conversion and default value assignment for
     * optional fields during deserialization.
     *
     * DESERIALIZATION PROCESS:
     * - Maps database column names to constructor parameters
     * - Converts boolean values from database representation
     * - Parses timestamp strings into DateTimeImmutable objects
     * - Applies default values for missing optional data
     *
     * STORAGE FORMAT:
     * ```php
     * [
     *     'service_id' => 'dependent.service',
     *     'depends_on_id' => 'dependency.service',
     *     'dependency_type' => 'constructor',
     *     'is_optional' => false,
     *     'created_at' => '2024-01-01 12:00:00'
     * ]
     * ```
     *
     * @param array $data Serialized entity data from storage
     *
     * @return self Reconstructed dependency entity
     *
     * @throws \InvalidArgumentException When required fields are missing
     * @throws \DateMalformedStringException
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            serviceId     : $data['service_id'],
            dependsOnId   : $data['depends_on_id'],
            dependencyType: $data['dependency_type'] ?? 'constructor',
            isOptional    : (bool) ($data['is_optional'] ?? false),
            createdAt     : isset($data['created_at']) ? new DateTimeImmutable(datetime: $data['created_at']) : null,
        );
    }

    /**
     * Returns the database table name for this entity type.
     *
     * Provides the canonical table name used for persistence operations,
     * following the container-related database table naming convention.
     *
     * TABLE NAMING CONVENTION:
     * Uses snake_case with 'container_' prefix for namespace isolation
     * and consistent table identification across the system.
     *
     * @return string The database table name for service dependencies
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-gettablename
     */
    public static function getTableName() : string
    {
        return 'container_service_dependencies';
    }

    /**
     * Serializes the dependency entity to an array for storage or transmission.
     *
     * Converts the immutable entity into a flat array representation suitable
     * for database storage, caching, or API responses. Handles type conversion
     * for database compatibility and preserves all relationship metadata.
     *
     * SERIALIZATION FEATURES:
     * - Converts DateTimeImmutable to database timestamp format
     * - Ensures boolean values are properly represented
     * - Maintains all relationship metadata
     * - Produces consistent output format
     *
     * OUTPUT FORMAT:
     * ```php
     * [
     *     'service_id' => 'dependent.service',
     *     'depends_on_id' => 'dependency.service',
     *     'dependency_type' => 'constructor',
     *     'is_optional' => false,
     *     'created_at' => '2024-01-01 12:00:00'
     * ]
     * ```
     *
     * @return array Serialized entity data for storage or transmission
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-toarray
     */
    public function toArray() : array
    {
        return [
            'service_id'      => $this->serviceId,
            'depends_on_id'   => $this->dependsOnId,
            'dependency_type' => $this->dependencyType,
            'is_optional'     => $this->isOptional,
            'created_at'      => $this->createdAt?->format(format: 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Determines if this dependency is injected via property access.
     *
     * Property dependencies are injected directly into public properties,
     * allowing for more flexible injection timing but requiring accessible
     * properties on the dependent service.
     *
     * PROPERTY DEPENDENCY CHARACTERISTICS:
     * - Injected after object construction
     * - Requires public property access
     * - Can be lazy-loaded if optional
     * - Moderate coupling strength
     *
     * @return bool True if this is a property dependency
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-ispropertydependency
     */
    public function isPropertyDependency() : bool
    {
        return $this->dependencyType === 'property';
    }

    /**
     * Determines if this dependency is injected via method calls.
     *
     * Method dependencies are injected through method invocations, providing
     * the most flexible injection mechanism with potential for complex
     * initialization logic.
     *
     * METHOD DEPENDENCY TYPES:
     * - method: Generic method injection
     * - setter: Property setter method injection
     *
     * CHARACTERISTICS:
     * - Most flexible injection timing
     * - Allows complex initialization logic
     * - Can be lazy-loaded
     * - Lower coupling strength
     *
     * @return bool True if this is a method or setter dependency
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-ismethoddependency
     */
    public function isMethodDependency() : bool
    {
        return in_array($this->dependencyType, ['method', 'setter']);
    }

    /**
     * Calculates a strength score indicating dependency importance and coupling.
     *
     * Provides a numerical score representing the relative strength and importance
     * of this dependency relationship. Higher scores indicate stronger coupling
     * and greater impact on the dependent service.
     *
     * SCORING SCALE:
     * - 10: Constructor dependencies (highest coupling)
     * - 7: Property dependencies (direct injection)
     * - 5: Method/setter dependencies (deferred injection)
     * - 3: Interface dependencies (contractual only)
     * - 1: Unknown/other dependency types
     *
     * USAGE IN ANALYSIS:
     * ```php
     * // Sort dependencies by coupling strength
     * usort($dependencies, fn($a, $b) =>
     *     $b->getStrengthScore() <=> $a->getStrengthScore()
     * );
     * ```
     *
     * BUSINESS VALUE:
     * - Identifies critical dependencies for monitoring
     * - Supports refactoring priority decisions
     * - Enables coupling analysis and architecture assessment
     *
     * @return int Strength score from 1-10 (higher = stronger coupling)
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-getstrengthscore
     */
    public function getStrengthScore() : int
    {
        return match ($this->dependencyType) {
            'constructor'      => 10,  // Strongest - required at creation
            'property'         => 7,      // Strong - injected directly
            'method', 'setter' => 5, // Medium - can be called later
            'interface'        => 3,     // Weak - just a contract
            default            => 1
        };
    }

    /**
     * Determines if this dependency can be resolved lazily without blocking service creation.
     *
     * Lazy loading is possible when the dependency is optional or not required
     * during the initial service construction phase. This allows for more
     * efficient service initialization and reduced startup coupling.
     *
     * LAZY LOADING CONDITIONS:
     * - Optional dependencies (isOptional = true)
     * - Non-constructor dependencies (can be injected later)
     *
     * BENEFITS:
     * - Faster service initialization
     * - Reduced startup time coupling
     * - More flexible dependency resolution
     * - Better support for circular dependency resolution
     *
     * @return bool True if this dependency can be lazy-loaded
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-canbelazyloaded
     */
    public function canBeLazyLoaded() : bool
    {
        return $this->isOptional || ! $this->isConstructorDependency();
    }

    /**
     * Determines if this dependency is injected during object construction.
     *
     * Constructor dependencies are the strongest type of dependency as they
     * are required for object instantiation and cannot be injected later.
     * These dependencies have the highest impact on service lifecycle.
     *
     * CONSTRUCTOR DEPENDENCY IMPACT:
     * - Required for service instantiation
     * - Cannot be lazy-loaded or deferred
     * - Failure prevents service creation
     * - Highest coupling strength
     *
     * @return bool True if this is a constructor dependency
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-isconstructordependency
     */
    public function isConstructorDependency() : bool
    {
        return $this->dependencyType === 'constructor';
    }

    /**
     * Creates the inverse dependency relationship for graph analysis.
     *
     * Generates a new dependency entity representing the reverse relationship,
     * useful for certain types of dependency graph analysis and queries.
     * The inverse always marks itself as optional since reverse dependencies
     * are inherently optional from the perspective of the original dependency.
     *
     * INVERSE RELATIONSHIP:
     * Original: A â†’ B (A depends on B)
     * Inverse: B â†’ A (B has dependent A)
     *
     * USE CASES:
     * - Impact analysis (what depends on this service?)
     * - Graph traversal algorithms
     * - Dependency inversion analysis
     *
     * @return self New dependency entity representing the inverse relationship
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-getinverse
     */
    public function getInverse() : self
    {
        return new self(
            serviceId     : $this->dependsOnId,
            dependsOnId   : $this->serviceId,
            dependencyType: 'inverse_' . $this->dependencyType,
            isOptional    : true, // Inverse dependencies are always optional
            createdAt     : $this->createdAt
        );
    }

    /**
     * Generates a human-readable description of the dependency relationship.
     *
     * Creates a natural language description of the dependency for logging,
     * debugging, and user interface display purposes. Includes all relevant
     * metadata about the relationship type and characteristics.
     *
     * DESCRIPTION FORMAT:
     * "{serviceId} depends on {dependsOnId} via {dependencyType} [(optional)]"
     *
     * EXAMPLES:
     * - "UserService depends on Database via constructor"
     * - "CacheService depends on Logger via property (optional)"
     * - "PaymentProcessor depends on PaymentInterface via interface"
     *
     * @return string Human-readable dependency description
     *
     * @see docs/Features/Define/Store/ServiceDependency.md#method-getdescription
     */
    public function getDescription() : string
    {
        $optional = $this->isOptional ? ' (optional)' : '';

        return "{$this->serviceId} depends on {$this->dependsOnId} via {$this->dependencyType}{$optional}";
    }
}
