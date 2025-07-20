<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Support;

use Gemini\DataHandling\ArrayHandling\Arrhae;
use Gemini\HTTP\Session\Contracts\BagRegistryInterface;
use Gemini\HTTP\Session\Contracts\FlashBagInterface;
use Gemini\HTTP\Session\Contracts\SessionBagInterface;
use InvalidArgumentException;

/**
 * ArrayBagRegistry
 *
 * A mutable registry for managing named session bags in a type-safe,
 * extensible, and runtime-configurable manner.
 *
 * This class adheres strictly to Clean Architecture and DDD practices,
 * allowing for dynamic registration, safe retrieval, and logical filtering.
 *
 * @package Gemini\HTTP\Session\Support
 */
final class ArrayBagRegistry implements BagRegistryInterface
{
    /**
     * Internal map of session bags using the `Arrhae` data structure
     * for advanced array manipulation and type safety.
     *
     * @var Arrhae<string, SessionBagInterface>
     */
    private Arrhae $map;

    /**
     * Constructor.
     *
     * @param array<string, SessionBagInterface> $bags
     *        Initial session bags provided during instantiation.
     */
    public function __construct(array $bags = [])
    {
        $this->map = new Arrhae(items: $bags);
    }

    /**
     * Dynamically register a session bag at runtime.
     *
     * Allows middleware, modules, or packages to extend the registry without
     * needing to recreate it from scratch.
     *
     * @param string $name             Unique bag identifier.
     * @param SessionBagInterface $bag The session bag instance.
     *
     * @return void
     */
    public function register(string $name, SessionBagInterface $bag) : void
    {
        $this->map->set(key: $name, value: $bag);
    }

    /**
     * Retrieve a registered session bag by its name.
     *
     * @param string $name
     *        The unique identifier for the session bag to retrieve.
     *
     * @return SessionBagInterface
     *
     * @throws InvalidArgumentException
     *         If the specified bag name is not found in the registry.
     */
    public function get(string $name) : SessionBagInterface
    {
        if (! $this->map->has(key: $name)) {
            logger()->error(
                'Session bag could not be found in the registry.',
                ['name' => $name]
            );

            throw new InvalidArgumentException(
                message: "Session bag [{$name}] not found in registry."
            );
        }

        return $this->map->get(key: $name);
    }

    /**
     * Determine whether a session bag exists by name.
     *
     * @param string $name The bag key to check.
     *
     * @return bool True if the bag exists; false otherwise.
     */
    public function has(string $name) : bool
    {
        return $this->map->has(key: $name);
    }

    /**
     * Returns a filtered registry instance with only the specified bag keys.
     *
     * This enables scoped registries for specific purposes (e.g., flash-only or validation-only).
     *
     * @param array<string> $keys Keys to include.
     *
     * @return BagRegistryInterface A new filtered registry.
     */
    public function only(array $keys) : BagRegistryInterface
    {
        return new self(bags: $this->map->only(keys: $keys)->toArray());
    }

    /**
     * Returns a filtered registry excluding specified bag keys.
     *
     * Useful for removing internal or reserved bags from DX exposure.
     *
     * @param array<string> $keys Keys to exclude.
     *
     * @return BagRegistryInterface A new filtered registry.
     */
    public function except(array $keys) : BagRegistryInterface
    {
        return new self(bags: $this->map->except(keys: $keys)->toArray());
    }

    /**
     * Retrieve all available bag keys currently registered.
     *
     * @return array<string> List of all registered bag names.
     */
    public function keys() : array
    {
        return $this->map->keys();
    }

    /**
     * Retrieve all registered session bags in the registry.
     *
     * This method returns the complete mapping of bag names to their respective
     * SessionBagInterface instances.
     * It is useful for introspection, debugging,
     * or batch operations on all session bags.
     *
     * @return array<string, SessionBagInterface>
     *         An associative array where the key is the bag name, and the value is the bag instance.
     */
    public function all() : array
    {
        return $this->map->toArray();
    }

    /**
     * Retrieves the flash message session bag.
     *
     * The `FlashBag` session bag is designed to store temporary messages or
     * data, persisting only until the next request by default. It simplifies
     * the handling of transient application states like success notifications,
     * validation alerts, or session-based one-time flags.
     *
     * Example usage:
     * ```php
     * $flashBag = $bagRegistry->flash();
     * $flashBag->put('success', 'Your account has been updated.');
     * ```
     *
     * Dependency on `FlashBagInterface`:
     * - `FlashBagInterface` extends `SessionBagInterface`, ensuring robust
     *    session-management capabilities with added functionality for
     *    managing flash-specific use cases, like `keep()` or `reflash()`.
     *
     * @return FlashBagInterface
     *   A flash message session bag adhering to FlashBagInterface, providing
     *   encapsulated methods tailored for transient data persistence.
     */
    public function flash() : FlashBagInterface
    {
        return app(abstract: FlashBagInterface::class);
    }

    /**
     * Retrieves the error message session bag.
     *
     * The `ErrorBag` is a generic session bag used to store validation
     * errors, user feedback, or any application state that needs to persist
     * across multiple requests. By handling errors via a dedicated bag,
     * developers can centralize error management into a structured container.
     *
     * Example usage:
     * ```php
     * $errorBag = $bagRegistry->errors();
     * $errorBag->put('email', 'The email address is invalid.');
     * ```
     *
     * Dependency on `SessionBagInterface`:
     * - The `ErrorBag` follows the contract defined by `SessionBagInterface`,
     *   guaranteeing functionality such as value retrieval, storage, and
     *   clearing, while allowing customization for error-related use cases.
     *
     * @return SessionBagInterface
     *   A generic session bag adhering to SessionBagInterface, providing
     *   flexible storage capabilities for error messages or other keyed data.
     */
    public function errors() : SessionBagInterface
    {
        return app(abstract: SessionBagInterface::class);
    }
}