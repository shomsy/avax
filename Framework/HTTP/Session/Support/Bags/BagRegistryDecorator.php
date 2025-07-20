<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Support\Bags;

use Gemini\HTTP\Session\Contracts\BagRegistryInterface;
use Gemini\HTTP\Session\Contracts\FlashBagInterface;
use Gemini\HTTP\Session\Contracts\SessionBagInterface;
use Gemini\HTTP\Session\Enums\SessionBag;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class BagRegistryDecorator
 *
 * A strongly typed decorator for accessing and managing session bags through the BagRegistryInterface.
 * This provides a type-safe, expressive API over the generic bag registry, aligning with DDD principles.
 *
 * @package Gemini\HTTP\Session\Support\Bags
 */
final readonly class BagRegistryDecorator
{
    /**
     * @var BagRegistryInterface $delegate
     *
     * The wrapped instance of the bag registry that provides general-purpose session bag operations.
     */
    public function __construct(private BagRegistryInterface $delegate) {}

    /**
     * Retrieve the Flash Bag.
     *
     * This method resolves and returns the registered flash bag from the registry while
     * enforcing conformance to the FlashBagInterface. Throws an exception if the type mismatch occurs.
     *
     * @return FlashBagInterface
     *   The flash bag instance used for storing temporary session data.
     *
     * @throws InvalidArgumentException
     *   If the retrieved bag does not implement FlashBagInterface.
     */
    public function flash() : FlashBagInterface
    {
        // Retrieve the "flash" bag from the delegate registry.
        $bag = $this->delegate->get(name: 'flash');

        // Validate that the retrieved bag implements FlashBagInterface.
        if (! $bag instanceof FlashBagInterface) {
            throw new InvalidArgumentException(message: 'Registered bag "flash" must implement FlashBagInterface.');
        }

        // Return the strongly typed flash bag instance.
        return $bag;
    }

    /**
     * Retrieves a session bag by key.
     *
     * Delegates the retrieval logic to a lower-level abstraction, ensuring
     * that the session bag associated with the provided key is returned.
     * If the bag is not found, an exception will be propagated by the delegate.
     *
     * @param string $key The unique key identifying the session bag in the registry.
     *                    Must be a non-empty string that conforms to system key standards.
     *
     * @return SessionBagInterface The session bag associated with the specified key.
     *
     * @throws InvalidArgumentException If the session bag does not exist in the registry.
     * @throws RuntimeException If an unexpected error occurs during the retrieval process.
     */
    public function get(string $key) : SessionBagInterface
    {
        // Delegates the "get" call to the underlying session delegate, leveraging named arguments for clarity.
        return $this->delegate->get(name: $key);
    }

    /**
     * Retrieve the Input Bag.
     *
     * The input bag captures user inputs, allowing their persistence for redisplaying forms,
     * particularly on validation errors.
     *
     * @return SessionBagInterface
     *   The session bag instance for input data.
     *
     * @throws InvalidArgumentException
     *   If the bag cannot be resolved or registered correctly.
     */
    public function input() : SessionBagInterface
    {
        // Resolve the "input" bag using its enumeration value.
        return $this->delegate->get(name: SessionBag::Input->value);
    }

    /**
     * Retrieve the Validation Errors Bag.
     *
     * This bag contains validation error messages, useful for isolating error-related session
     * context and presenting it within user interfaces.
     *
     * @return SessionBagInterface
     *   The session bag instance for validation errors.
     *
     * @throws InvalidArgumentException
     *   If the bag cannot be accessed correctly from the registry.
     */
    public function errors() : SessionBagInterface
    {
        // Resolve the "validation" bag using its enumeration value.
        return $this->delegate->get(name: SessionBag::Validation->value);
    }
}