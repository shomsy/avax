<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Closure;

/**
 * @package Avax\Container\Define\Store
 *
 * Central Settings for Service Definitions and Bindings.
 *
 * The DefinitionStore is the authoritative source of truth for how the container should
 * behave when resolving specific identifiers. It stores service lifetimes, factory closures,
 * contextual overrides, and extensibility hooks. It serves as the "Knowledge Base" for
 * the EngineInterface.
 *
 * WHY IT EXISTS:
 * - To provide a consistent storage for all service-related metadata.
 * - To support advanced features like contextual binding and service tagging.
 * - To allow lazy resolution of complex rules through hierarchical lookup (wildcards, interfaces).
 * - To enable post-resolution customization through extenders.
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Uses an internal cache ($resolvedCache) for complex contextual lookups (e.g. interfaces, parents).
 * - Definition lookups via dictionary key ($definitions[$abstract]) are O(1).
 * - Tag lookups return arrays of service identifiers, which may require deduplication.
 *
 * SECURITY CONSIDERATIONS:
 * - Does not perform direct logic; it only stores metadata used by the engine.
 * - Input validation should be performed by builders (BindingBuilder, ContextBuilder).
 *
 * THREAD SAFETY:
 * - This class is mutable and not inherently thread-safe.
 * - In multi-threaded environments, access should be synchronized during registration phase.
 *
 * @see     ServiceDefinition The blueprint stored within this class.
 * @see docs_md/Features/Define/Store/DefinitionStore.md#quick-summary
 */
class DefinitionStore
{
    /**
     * @var array<string, ServiceDefinition> Map of abstract identifier to its definition object.
     */
    private array $definitions = [];

    /**
     * @var array<string, string[]> Reverse index of tags to service identifiers.
     */
    private array $tags = [];

    /**
     * @var array<string, array<string, mixed>> Specific contextual rules (Consumer -> Needs -> Give).
     */
    private array $contextual = [];

    /**
     * @var array<string, array<string, mixed>> Pattern-based contextual rules (Wildcard -> Needs -> Give).
     */
    private array $wildcardContextual = [];

    /**
     * @var array<string, mixed> Cache for memoized contextual matches to avoid recursive reflection.
     */
    private array $resolvedCache = [];

    /**
     * @var array<string, array{parents: string[], interfaces: string[]}> Cache for class hierarchy information.
     */
    private array $classHierarchyCache = [];

    /**
     * @var array<string, \Closure[]> Custom callbacks to run after resolution for specific abstract types.
     */
    private array $extenders = [];

    /**
     * Appends or updates a service definition in the store.
     *
     * @param ServiceDefinition $definition The blueprint to store.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-add
     */
    public function add(ServiceDefinition $definition): void
    {
        if (isset($this->definitions[$definition->abstract])) {
            $existing = $this->definitions[$definition->abstract];
            foreach ($existing->tags as $tag) {
                if (! isset($this->tags[$tag])) {
                    continue;
                }
                $this->tags[$tag] = array_values(array_diff($this->tags[$tag], [$definition->abstract]));
                if ($this->tags[$tag] === []) {
                    unset($this->tags[$tag]);
                }
            }
        }

        $this->definitions[$definition->abstract] = $definition;
        foreach ($definition->tags as $tag) {
            $this->tags[$tag][] = $definition->abstract;
        }
        $this->resolvedCache = [];
    }

    /**
     * Retrieves a service definition by its identifier.
     *
     * @param string $abstract The identifier to look up.
     *
     * @return ServiceDefinition|null The definition if found, null otherwise.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-get
     */
    public function get(string $abstract): ServiceDefinition|null
    {
        return $this->definitions[$abstract] ?? null;
    }

    /**
     * Checks if a definition exists for the given abstract identifier.
     *
     * @param string $abstract The identifier to check.
     *
     * @return bool True if registered, false otherwise.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-has
     */
    public function has(string $abstract): bool
    {
        return isset($this->definitions[$abstract]);
    }

    /**
     * Retrieves all service identifiers associated with a given tag.
     *
     * @param string $tag The tag name.
     *
     * @return string[] Array of unique abstract identifiers.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-gettaggedids
     */
    public function getTaggedIds(string $tag): array
    {
        return array_unique($this->tags[$tag] ?? []);
    }

    /**
     * Finds the best contextual binding match for a given consumer and dependency.
     *
     * Uses memoization to optimize repeated lookups.
     *
     * @param string $consumer The class name of the object being resolved.
     * @param string $needs    The dependency identifier required by the consumer.
     *
     * @return mixed The configured implementation (closure/string/object) or null.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-getcontextualmatch
     */
    public function getContextualMatch(string $consumer, string $needs): mixed
    {
        $cacheKey = $consumer . ':' . $needs;
        if (array_key_exists($cacheKey, $this->resolvedCache)) {
            return $this->resolvedCache[$cacheKey];
        }

        return $this->resolvedCache[$cacheKey] = $this->resolveContextual(consumer: $consumer, needs: $needs);
    }

    /**
     * Performs a hierarchical search for contextual rules.
     *
     * Search priority:
     * 1. Direct class match.
     * 2. Wildcard (fnmatch) pattern match.
     * 3. Parent class rules.
     * 4. Interface rules.
     *
     * @param string $consumer The consuming class name.
     * @param string $needs    The dependency being requested.
     *
     * @return mixed Match if found, null otherwise.
     */
    private function resolveContextual(string $consumer, string $needs): mixed
    {
        if (isset($this->contextual[$consumer][$needs])) {
            return $this->contextual[$consumer][$needs];
        }

        foreach ($this->wildcardContextual as $pattern => $rules) {
            if (isset($rules[$needs]) && fnmatch($pattern, $consumer)) {
                return $rules[$needs];
            }
        }

        if (class_exists($consumer)) {
            $hierarchy = $this->getClassHierarchy(consumer: $consumer);

            foreach ($hierarchy['parents'] as $parent) {
                if (isset($this->contextual[$parent][$needs])) {
                    return $this->contextual[$parent][$needs];
                }
            }
            foreach ($hierarchy['interfaces'] as $interface) {
                if (isset($this->contextual[$interface][$needs])) {
                    return $this->contextual[$interface][$needs];
                }
            }
        }

        return null;
    }

    /**
     * Gets cached class hierarchy information for a consumer class.
     *
     * @param string $consumer The class name to analyze.
     *
     * @return array{parents: string[], interfaces: string[]} Cached hierarchy info.
     */
    private function getClassHierarchy(string $consumer): array
    {
        if (! isset($this->classHierarchyCache[$consumer])) {
            $this->classHierarchyCache[$consumer] = [
                'parents'    => class_parents($consumer),
                'interfaces' => class_implements($consumer),
            ];
        }

        return $this->classHierarchyCache[$consumer];
    }

    /**
     * Adds a contextual rule to the store.
     *
     * @param string $consumer The target class or pattern (*).
     * @param string $needs    The dependency identifier.
     * @param mixed  $give     The value or instruction to fulfill the dependency.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-addcontextual
     */
    public function addContextual(string $consumer, string $needs, mixed $give): void
    {
        $this->resolvedCache = [];
        if (str_contains($consumer, '*')) {
            $this->wildcardContextual[$consumer][$needs] = $give;
        } else {
            $this->contextual[$consumer][$needs] = $give;
        }
    }

    /**
     * Registers an extender for a specific service.
     *
     * @param string  $abstract The target service.
     * @param Closure $extender Callback receiving the instance.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-addextender
     */
    public function addExtender(string $abstract, Closure $extender): void
    {
        $this->extenders[$abstract][] = $extender;
        unset($this->resolvedCache[$abstract]);
    }

    /**
     * Retrieves all registered extenders for a given service.
     *
     * @param string $abstract The target service.
     *
     * @return \Closure[]
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-getextenders
     */
    public function getExtenders(string $abstract): array
    {
        return array_merge(
            $this->extenders['*'] ?? [],
            $this->extenders[$abstract] ?? []
        );
    }

    /**
     * Updates the tags for an existing service definition.
     *
     * This method ensures that tag indexes are properly maintained when tags
     * are added to existing definitions (e.g., through BindingBuilder::tag()).
     *
     * @param string          $abstract The service identifier.
     * @param string|string[] $tags     The tags to add.
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-addtags
     */
    public function addTags(string $abstract, string|array $tags): void
    {
        if (! isset($this->definitions[$abstract])) {
            return;
        }

        $definition       = $this->definitions[$abstract];
        $definition->tags = array_unique(array_merge($definition->tags, (array) $tags));

        foreach ((array) $tags as $tag) {
            $this->tags[$tag][] = $abstract;
            $this->tags[$tag]   = array_unique($this->tags[$tag]);
        }
    }

    /**
     * Returns an array of all registered definitions.
     *
     * @return array<string, ServiceDefinition>
     * @see docs_md/Features/Define/Store/DefinitionStore.md#method-getalldefinitions
     */
    public function getAllDefinitions(): array
    {
        return $this->definitions;
    }
}
