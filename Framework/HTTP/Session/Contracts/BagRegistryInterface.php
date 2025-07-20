<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Contracts;

/**
 * Interface BagRegistryInterface
 *
 * A service contract for managing session "bags", which group session
 * state by separate concerns (such as input data, error messages, or
 * flash data). This allows developers to organize session storage into
 * isolated namespaces for better scalability, modularity, and reuse.
 *
 * This registry acts as a central hub to store and retrieve such bags
 * during the lifecycle of the session.
 *
 * Usage Scenarios:
 * - Flash bag management for temporary data like notifications.
 * - Input bag management for preserving form state across requests.
 * - Error bag management for storing validation or runtime errors.
 */
interface BagRegistryInterface
{
    /**
     * Retrieve a specific session bag by its unique name.
     *
     * @template T of SessionBagInterface
     *
     * @param string $name
     *   The unique identifier for the session bag.
     *
     * @return \Gemini\HTTP\Session\Contracts\SessionBagInterface
     *   The resolved session bag instance implementing SessionBagInterface.
     *
     * @throws \InvalidArgumentException
     *   If no session bag with the given name is registered, an exception
     *   could be thrown to indicate the absence of the bag.
     *
     * Why use this method:
     * - Centralizes access to specific session sub-containers.
     * - Enables dependency injection or lazy initialization of session bags.
     */
    public function get(string $name) : SessionBagInterface;

    /**
     * Register a session bag into the registry under a specific name.
     *
     * @param string                                             $name
     *   The unique identifier for the session bag.
     *
     * @param \Gemini\HTTP\Session\Contracts\SessionBagInterface $bag
     *   The instance of the session bag to register. This could be a
     *   pre-configured reusable bag, such as a FlashBag or ErrorBag.
     *
     * @return void
     *
     * Why use this method:
     * - Extensibility: Register additional features that interact with
     *   the session, organizing them into separate namespaces (bags).
     * - Ease of use: Ensures that all session bags follow a consistent
     *   initialization pattern.
     * - Modularity: Each session bag can be registered independently,
     *   promoting a decoupled architecture.
     */
    public function register(string $name, SessionBagInterface $bag) : void;

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
    public function flash() : FlashBagInterface;

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
    public function errors() : SessionBagInterface;

}