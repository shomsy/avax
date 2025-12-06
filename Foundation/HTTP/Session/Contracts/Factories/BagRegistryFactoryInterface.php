<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts\Factories;

use Avax\HTTP\Session\Contracts\BagRegistryInterface;
use Avax\HTTP\Session\Contracts\SessionInterface;

/**
 * Interface BagRegistryFactoryInterface
 *
 * This interface defines a factory contract for creating instances of a BagRegistry.
 *
 * Why use this factory design:
 * - Decoupling: The specific implementation of the BagRegistryInterface can
 *   vary. The factory provides an abstraction, allowing for flexible and
 *   interchangeable implementations.
 * - Encapsulation: Encapsulates the logic for constructing and configuring
 *   the BagRegistry instance, ensuring clients do not need to handle this.
 * - Testability: Factories enable easier mocking and testing by resolving
 *   dependencies for BagRegistry.
 *
 * Implementations of this interface should adhere to DDD principles,
 * following concepts like Dependency Injection for more modular, clean code.
 *
 * @package Avax\HTTP\Session\Contracts\Factories
 */
interface BagRegistryFactoryInterface
{
    /**
     * Create a new instance of BagRegistryInterface.
     *
     * This method is the central point for creating and resolving a BagRegistry.
     * The BagRegistry is a container for session bags, which organizes
     * data into logical namespaces for session management (e.g., FlashBag, ErrorBag).
     *
     * Design Notes:
     * - Dependency Injection: The factory may use DI to resolve instances.
     * - Extensibility: By returning `BagRegistryInterface`, the factory
     *   allows implementations to vary while adhering to the contract.
     *
     * Example usage:
     * ```php
     * $factory = new ConcreteBagRegistryFactory();
     * $bagRegistry = $factory->create();
     * $flashBag = $bagRegistry->flash();
     * ```
     *
     * @return BagRegistryInterface
     *   A new instance of BagRegistryInterface, fully initialized and ready to use.
     */
    public function create(SessionInterface $session) : BagRegistryInterface;
}