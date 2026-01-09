<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Prototype;

use Avax\Container\Features\Think\Analyze\PrototypeAnalyzer;
use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Features\Think\Model\ServicePrototype;

use Avax\Container\Features\Think\Prototype\Contracts\ServicePrototypeFactoryInterface;

/**
 * ServicePrototypeFactory - Factory for creating and managing dependency injection prototypes.
 * Orchestrates caching and delegates actual analysis to PrototypeAnalyzer.
 *
 * @see docs_md/Features/Think/Prototype/ServicePrototypeFactory.md#quick-summary
 */
final readonly class ServicePrototypeFactory implements ServicePrototypeFactoryInterface
{
    public function __construct(
        private PrototypeCache    $cache,
        private PrototypeAnalyzer $analyzer
    ) {}

    /**
     * @see docs_md/Features/Think/Prototype/ServicePrototypeFactory.md#method-getcache
     */
    public function getCache(): PrototypeCache
    {
        return $this->cache;
    }

    /**
     * @see docs_md/Features/Think/Prototype/ServicePrototypeFactory.md#method-getanalyzer
     */
    public function getAnalyzer(): PrototypeAnalyzer
    {
        return $this->analyzer;
    }

    /**
     * Creates a prototype for a given class, analyzing it if not already cached.
     *
     * @param string $class The fully qualified class name to analyze
     * @return ServicePrototype The analyzed service prototype
     * @see docs_md/Features/Think/Prototype/ServicePrototypeFactory.md#method-createfor
     */
    public function createFor(string $class): ServicePrototype
    {
        // Check cache first
        $cached = $this->cache->get(class: $class);
        if ($cached !== null) {
            return $cached;
        }

        // Perform reflection analysis
        $prototype = $this->analyzer->analyze(class: $class);

        // Cache the result
        $this->cache->set(class: $class, prototype: $prototype);

        return $prototype;
    }

    /**
     * @see docs_md/Features/Think/Prototype/ServicePrototypeFactory.md#method-hasprototype
     */
    public function hasPrototype(string $class): bool
    {
        return $this->cache->has(class: $class);
    }
}
