<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Support\Factories;

use Avax\HTTP\Session\Contracts\BagRegistryInterface;
use Avax\HTTP\Session\Contracts\Factories\BagRegistryFactoryInterface;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Enums\SessionBag;
use Avax\HTTP\Session\Support\ArrayBagRegistry;
use Avax\HTTP\Session\Support\Bags\FlashBag;
use Avax\HTTP\Session\Support\Bags\InputBag;
use Avax\HTTP\Session\Support\Bags\ValidationBag;

/**
 * BagRegistryFactory
 *
 * Factory responsible for creating a default BagRegistry instance, containing core session bags.
 * These session bags include:
 * - **FlashBag**: For temporary session data (e.g., notifications).
 * - **InputBag**: For preserving user input (e.g., forms).
 * - **ValidationBag**: For maintaining validation errors.
 *
 * **Design Goals**:
 * - Ensure all core session bags are registered via enum-based identifiers.
 * - Maintain extensibility and strong type safety without sacrificing simplicity.
 *
 * @final
 */
final class BagRegistryFactory implements BagRegistryFactoryInterface
{
    /**
     * Creates and returns an instance of BagRegistry containing default session bags.
     *
     * This factory initializes the following session bags:
     * - `FlashBag`: For handling flash session data, persists for a single request.
     * - `InputBag`: Depends on `FlashBag` for preserving form input data.
     * - `ValidationBag`: Depends on `FlashBag` for managing validation errors.
     *
     * **Key Design Objectives**:
     * - Dependency Injection is utilized to inject the `SessionInterface`.
     * - Enum identifiers ensure clear, extensible mapping of session bags.
     *
     * @return BagRegistryInterface The created BagRegistry initialized with default session bags.
     */
    public function create(SessionInterface $session) : BagRegistryInterface
    {
        // Instantiate the FlashBag, passing the session dependency.
        // FlashBag is used to manage data that persists for only one request lifecycle.
        $flashBag = new FlashBag(session: $session);

        // Return an ArrayBagRegistry containing predefined session bags.
        // Each bag is registered using a unique key derived from the SessionBag enum.
        return new ArrayBagRegistry(
            bags: [
                      // Register the FlashBag using the SessionBag::Flash identifier.
                      SessionBag::Flash->value      => $flashBag,

                      // Register the InputBag instance; it relies on FlashBag for retaining user-submitted forms.
                      SessionBag::Input->value      => new InputBag(flash: $flashBag),

                      // Register the ValidationBag instance; it also depends on FlashBag to persist validation errors.
                      SessionBag::Validation->value => new ValidationBag(flash: $flashBag),
                  ]
        );
    }
}