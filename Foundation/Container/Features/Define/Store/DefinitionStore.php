<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Closure;

/**
 * Registry interface for service definitions and injection rules.
 *
 * This class acts as the centralized data store for all container configurations,
 * including service blueprints (ServiceDefinition), tags, extenders, and
 * contextual injection rules. It provides optimized lookup mechanisms and
 * memoization caches for complex matching logic.
 *
 * @package Avax\Container\Define\Store
 * @see docs/Features/Define/Store/DefinitionStore.md
 */
class DefinitionStore
{
    /** @var array<string, ServiceDefinition> */
    private array $definitions = [];

    /** @var array<string, string[]> */
    private array $tags = [];

    /** @var array<string, array<string, mixed>> */
    private array $contextual = [];

    /** @var array<string, array<string, mixed>> */
    private array $wildcardContextual = [];

    /** @var array<string, mixed> */
    private array $resolvedCache = [];

    /** @var array<string, array{parents: string[], interfaces: string[]}> */
    private array $classHierarchyCache = [];

    /** @var array<string, Closure[]> */
    private array $extenders = [];

    /**
     * Appends or updates a service definition in the store.
     *
     * @param ServiceDefinition $definition The blueprint to store.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-add
     */
    public function add(ServiceDefinition $definition): void
    {
        if (isset($this->definitions[$definition->abstract])) {
            $existing = $this->definitions[$definition->abstract];
            foreach ($existing->tags as $tag) {
                if (isset($this->tags[$tag])) {
                    $this->tags[$tag] = array_diff($this->tags[$tag], [$definition->abstract]);
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
     * Checks if a definition exists for the given abstract.
     *
     * @param string $abstract The identifier to check.
     * @return bool True if registered.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-has
     */
    public function has(string $abstract): bool
    {
        return isset($this->definitions[$abstract]);
    }

    /**
     * Retrieves a service definition.
     *
     * @param string $abstract The identifier to retrieve.
     * @return ServiceDefinition|null The definition or null if not found.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-get
     */
    public function get(string $abstract): ?ServiceDefinition
    {
        return $this->definitions[$abstract] ?? null;
    }

    /**
     * Resolve unique service IDs associated with a specific tag.
     *
     * @param string $tag The tag identifier.
     * @return array<int, string> List of unique service IDs.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-gettaggedids
     */
    public function getTaggedIds(string $tag): array
    {
        return isset($this->tags[$tag]) ? array_values(array_unique($this->tags[$tag])) : [];
    }

    /**
     * Retrieve the best contextual match for a specific consumer and requirement.
     *
     * @param string $consumer The consuming class name.
     * @param string $needs    The dependency ID being requested.
     * @return mixed The configured implementation/value override.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-getcontextualmatch
     */
    public function getContextualMatch(string $consumer, string $needs): mixed
    {
        $cacheKey = $consumer . '@' . $needs;
        if (array_key_exists($cacheKey, $this->resolvedCache)) {
            return $this->resolvedCache[$cacheKey];
        }

        // 1. Direct Match
        if (isset($this->contextual[$consumer][$needs])) {
            return $this->resolvedCache[$cacheKey] = $this->contextual[$consumer][$needs];
        }

        // 2. Wildcard Match
        foreach ($this->wildcardContextual as $pattern => $rules) {
            if (isset($rules[$needs]) && fnmatch($pattern, $consumer)) {
                return $this->resolvedCache[$cacheKey] = $rules[$needs];
            }
        }

        // 3. Hierarchy Match (Parents & Interfaces)
        $hierarchy = $this->getClassHierarchy($consumer);
        foreach ($hierarchy['parents'] as $parent) {
            if (isset($this->contextual[$parent][$needs])) {
                return $this->resolvedCache[$cacheKey] = $this->contextual[$parent][$needs];
            }
        }
        foreach ($hierarchy['interfaces'] as $interface) {
            if (isset($this->contextual[$interface][$needs])) {
                return $this->resolvedCache[$cacheKey] = $this->contextual[$interface][$needs];
            }
        }

        return $this->resolvedCache[$cacheKey] = null;
    }

    /**
     * Add a contextual injection rule.
     *
     * @param string $consumer The class name or wildcard pattern.
     * @param string $needs    The dependency ID to override.
     * @param mixed  $give     The override value or implementation.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-addcontextual
     */
    public function addContextual(string $consumer, string $needs, mixed $give): void
    {
        if (str_contains($consumer, '*')) {
            $this->wildcardContextual[$consumer][$needs] = $give;
        } else {
            $this->contextual[$consumer][$needs] = $give;
        }

        $this->resolvedCache = [];
    }

    /**
     * Add a post-resolution service extender.
     *
     * @param string  $abstract The service identifier.
     * @param Closure $extender Callback receiving the instance and container.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-addextender
     */
    public function addExtender(string $abstract, Closure $extender): void
    {
        $this->extenders[$abstract][] = $extender;
    }

    /**
     * Retrieve all extenders applicable to a service.
     *
     * @param string $abstract The service identifier.
     * @return array<int, Closure> List of extender callbacks.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-getextenders
     */
    public function getExtenders(string $abstract): array
    {
        return $this->extenders[$abstract] ?? [];
    }

    /**
     * Batch add tags to an existing abstract.
     *
     * @param string          $abstract The service identifier.
     * @param string|string[] $tags     Single tag or list of tags.
     * @see docs/Features/Define/Store/DefinitionStore.md#method-addtags
     */
    public function addTags(string $abstract, string|array $tags): void
    {
        if (! isset($this->definitions[$abstract])) {
            return;
        }

        $tags = (array) $tags;
        foreach ($tags as $tag) {
            $this->definitions[$abstract]->tags[] = $tag;
            $this->tags[$tag][]                   = $abstract;
        }
    }

    /**
     * Retrieve the internal map of all registered definitions.
     *
     * @return array<string, ServiceDefinition>
     * @see docs/Features/Define/Store/DefinitionStore.md#method-getalldefinitions
     */
    public function getAllDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Internal helper to cache and retrieve class hierarchy.
     *
     * @param string $class The class name to reflect.
     * @return array{parents: string[], interfaces: string[]}
     */
    private function getClassHierarchy(string $class): array
    {
        if (isset($this->classHierarchyCache[$class])) {
            return $this->classHierarchyCache[$class];
        }

        return $this->classHierarchyCache[$class] = [
            'parents'    => class_exists($class) ? array_values(class_parents($class)) : [],
            'interfaces' => class_exists($class) || interface_exists($class) ? array_values(class_implements($class)) : []
        ];
    }
}
