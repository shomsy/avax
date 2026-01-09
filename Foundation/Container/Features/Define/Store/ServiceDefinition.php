<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Avax\Container\Features\Core\Enum\ServiceLifetime;

/**
 * @package Avax\Container\Define\Store
 *
 * Service Definition Data Transfer Object (DTO).
 *
 * The ServiceDefinition is the blueprint of a service. it stores all the necessary
 * information for the EngineInterface to determine how to instantiate, configure,
 * and persist a service. It is the central piece of metadata exchanged between the
 * "Finalize" builders and the "Store".
 *
 * WHY IT EXISTS:
 * - To provide a structured, type-safe representation of service configuration.
 * - To support serialization for performance-critical caching (Think phase).
 * - To separate the fluent DSL logic (Builders) from the storage logic (Store).
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Simple DTO with public properties for fast access.
 * - Implements __set_state and static factory for efficient cache hydration.
 *
 * THREAD SAFETY:
 * - Instances should be considered mutable during the registration phase but
 *   immutable once stored in a production-ready DefinitionStore.
 *
 * @see docs_md/Features/Define/Store/ServiceDefinition.md#quick-summary
 */
class ServiceDefinition
{
    /** @var string The unique identifier (class or alias) for the service. */
    public string $abstract;

    /** @var mixed The concrete implementation, factory closure, or specific instance. */
    public mixed $concrete = null;

    /** @var ServiceLifetime The persistence policy (Singleton, Scoped, Transient). */
    public ServiceLifetime $lifetime = ServiceLifetime::Transient;

    /** @var array<int, string> List of categories/tags associated with this service. */
    public array $tags = [];

    /** @var array<string, mixed> Associative array of constructor arguments to override. */
    public array $arguments = [];

    /**
     * Initializes a new definition for the given abstract.
     *
     * @param string $abstract
     * @see docs_md/Features/Define/Store/ServiceDefinition.md#method-__construct
     */
    public function __construct(string $abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Supports PHP's var_export for native evaluation during cache loading.
     *
     * @param array $array
     *
     * @return self
     * @see docs_md/Features/Define/Store/ServiceDefinition.md#method-__set_state
     */
    public static function __set_state(array $array): self
    {
        return self::fromArray(data: $array);
    }

    /**
     * Hydrates a definition instance from a raw array.
     *
     * Primarily used for restoring definitions from persistent file cache.
     *
     * @param array $data
     *
     * @return self
     * @see docs_md/Features/Define/Store/ServiceDefinition.md#method-fromarray
     */
    public static function fromArray(array $data): self
    {
        $obj           = new self(abstract: $data['abstract']);
        $obj->concrete = $data['concrete'] ?? null;

        $lifetime = $data['lifetime'] ?? ServiceLifetime::Transient;
        if (is_string($lifetime)) {
            $obj->lifetime = ServiceLifetime::from(value: $lifetime);
        } elseif ($lifetime instanceof ServiceLifetime) {
            $obj->lifetime = $lifetime;
        } else {
            $obj->lifetime = ServiceLifetime::Transient;
        }

        $obj->tags      = $data['tags'] ?? [];
        $obj->arguments = $data['arguments'] ?? [];

        return $obj;
    }

    /**
     * Flattens the definition into a serializable array.
     *
     * @return array
     * @see docs_md/Features/Define/Store/ServiceDefinition.md#method-toarray
     */
    public function toArray(): array
    {
        return [
            'abstract'  => $this->abstract,
            'concrete'  => $this->concrete,
            'lifetime'  => $this->lifetime->value,
            'tags'      => $this->tags,
            'arguments' => $this->arguments,
        ];
    }
}
