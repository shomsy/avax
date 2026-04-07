<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Prototypes\Factory;

use Avax\Container\DependencyInjection\Capability\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\DependencyInjection\Capability\Prototypes\Cache\PrototypeCache;
use Avax\Container\DependencyInjection\Capability\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;

/**
 * The central manager for the creation and lifecycle of service prototypes.
 *
 * This factory acts as the primary entry point for obtaining a {@see ServicePrototype}.
 * It coordinates with the {@see PrototypeCache} for "Read-Once" performance and
 * delegates the heavy lifting of code reflection to the {@see PrototypeAnalyzer}.
 * It ensures that the rest of the container always has access to verified,
 * pre-analyzed metadata about any class.
 *
 */
final readonly class ServicePrototypeFactory implements ServicePrototypeFactoryInterface
{
    /**
     * Initializes the factory with its caching and analysis collaborators.
     *
     * @param PrototypeCache    $cache    The persistence/memory layer for prototypes.
     * @param PrototypeAnalyzer $analyzer The reflection specialist for class discovery.
     */
    public function __construct(
        private PrototypeCache    $cache,
        private PrototypeAnalyzer $analyzer
    ) {}

    /**
     * Get the underlying cache instance.
     *
     */
    public function getCache() : PrototypeCache
    {
        return $this->cache;
    }

    /**
     * Get the underlying reflection analyzer.
     *
     */
    public function getAnalyzer() : PrototypeAnalyzer
    {
        return $this->analyzer;
    }

    /**
     * Retrieves or creates a prototype for a specific class.
     *
     * This method implements a "Cache-First" strategy:
     * 1. Check if the blueprint is already in the cache.
     * 2. If missing, perform full reflection analysis.
     * 3. Store the new blueprint in the cache.
     * 4. Return the blueprint.
     *
     * @param string $class The fully qualified class name.
     *
     * @return ServicePrototype The analyzed and ready-to-use blueprint.
     *
     * @throws \RuntimeException If the class cannot be analyzed.
     *
     */
    public function createFor(string $class) : ServicePrototype
    {
        // 1. Check cache first for high-performance retrieval
        $cached = $this->cache->get(class: $class);
        if ($cached !== null) {
            return $cached;
        }

        // 2. Perform deep reflection analysis if cache is empty
        $prototype = $this->analyzer->analyze(class: $class);

        // 3. Persist the blueprint for future requests
        $this->cache->set(class: $class, prototype: $prototype);

        return $prototype;
    }

    /**
     * Determine if a blueprint already exists in the cache for a class.
     *
     * @param string $class The class name.
     *
     * @return bool True if a prototype is already cached.
     *
     */
    public function hasPrototype(string $class) : bool
    {
        return $this->cache->has(class: $class);
    }
}
