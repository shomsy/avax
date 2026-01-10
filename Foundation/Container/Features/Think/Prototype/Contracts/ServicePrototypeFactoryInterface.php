<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Prototype\Contracts;

use Avax\Container\Features\Think\Analyze\PrototypeAnalyzer;
use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * Interface for ServicePrototypeFactory.
 *
 * @see docs/Features/Think/Prototype/Contracts/ServicePrototypeFactoryInterface.md#quick-summary
 */
interface ServicePrototypeFactoryInterface
{
    /**
     * Creates a prototype for a given class, analyzing it if not already cached.
     *
     * @param string $class The fully qualified class name to analyze
     * @return ServicePrototype The analyzed service prototype
     * @see docs/Features/Think/Prototype/Contracts/ServicePrototypeFactoryInterface.md#method-createfor
     */
    public function createFor(string $class): ServicePrototype;

    /**
     * Checks if a prototype exists for the given class in cache.
     *
     * @param string $class
     * @return bool
     * @see docs/Features/Think/Prototype/Contracts/ServicePrototypeFactoryInterface.md#method-hasprototype
     */
    public function hasPrototype(string $class): bool;

    /**
     * Get the underlying analyzer.
     * 
     * @return PrototypeAnalyzer
     * @see docs/Features/Think/Prototype/Contracts/ServicePrototypeFactoryInterface.md#method-getanalyzer
     */
    public function getAnalyzer(): PrototypeAnalyzer;
}
