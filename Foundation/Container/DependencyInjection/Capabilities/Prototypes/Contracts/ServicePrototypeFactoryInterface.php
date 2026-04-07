<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Prototypes\Contracts;

use Avax\Container\DependencyInjection\Capabilities\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\ServicePrototype;

/**
 * Interface for ServicePrototypeFactory.
 *
 */
interface ServicePrototypeFactoryInterface
{
    /**
     * Creates a prototype for a given class, analyzing it if not already cached.
     *
     * @param string $class The fully qualified class name to analyze
     *
     * @return ServicePrototype The analyzed service prototype
     *
     */
    public function createFor(string $class) : ServicePrototype;

    /**
     * Checks if a prototype exists for the given class in cache.
     *
     *
     */
    public function hasPrototype(string $class) : bool;

    /**
     * Get the underlying analyzer.
     *
     */
    public function getAnalyzer() : PrototypeAnalyzer;
}
