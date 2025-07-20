<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Support\Factories;

use Gemini\HTTP\Session\Contracts\Factories\BagRegistryFactoryInterface;
use Gemini\HTTP\Session\Contracts\SessionInterface;
use Gemini\HTTP\Session\Drivers\ArraySession;
use Gemini\HTTP\Session\Drivers\NativeSession;
use Gemini\HTTP\Session\Enums\SessionDriver;
use Gemini\HTTP\Session\Exceptions\SessionException;
use Gemini\HTTP\Session\Stores\ArraySessionStore;
use Gemini\HTTP\Session\Stores\NativeSessionStore;

/**
 * Factory for creating session driver instances with DI-compliant store/registry configuration.
 *
 * This factory encapsulates the creation logic for various session drivers, ensuring
 * proper configuration of their dependencies such as stores and registries.
 * This approach adheres to the Dependency Inversion Principle (DIP),
 * promoting decoupling and testability.
 *
 * @final Ensures the integrity of the factory, disallowing inheritance as per DDD.
 */
final readonly class SessionDriverFactory
{
    /**
     * Factory interface to create session bag registry instances.
     *
     * @var BagRegistryFactoryInterface $registryFactory
     * A contract to abstract the creation of bag registries for session drivers.
     */
    public function __construct(
        private BagRegistryFactoryInterface $registryFactory // Constructor Promotion for clarity and efficiency.
    ) {}

    /**
     * Factory method to create and configure a new session driver instance.
     *
     * This method maps string-based driver types to their respective
     * session implementation, such as `NativeSession` or `ArraySession`.
     *
     * It uses a robust error-checking mechanism to validate supported drivers (e.g., Enum `SessionDriver`).
     *
     * @param string $driver
     *   The session driver's type, represented as a string (e.g., 'Native', 'Array').
     *
     * @return SessionInterface
     *   The fully configured session implementation.
     *
     * @throws SessionException
     *   Thrown if an unsupported or invalid session driver is passed to the factory.
     */
    public function create(string $driver) : SessionInterface
    {
        // Convert the provided driver type to an Enum instance; returns null if invalid.
        $enum = SessionDriver::tryFrom(value: $driver);

        // Check if the given driver is supported. If not, throw a custom SessionException.
        if ($enum === null) {
            throw new SessionException(
                message: sprintf(
                         // Compose a detailed error message, explicitly listing supported drivers.
                             "Invalid session driver: '%s'. Supported drivers are: %s.",
                             $driver,
                             implode(
                                 ', ',
                                 array_map(
                                     static fn(SessionDriver $d) : string => $d->value,
                                     SessionDriver::cases()
                                 )
                             )
                         )
            );
        }

        // Use `match` to delegate the driver creation logic to specific private methods.
        return match ($enum) {
            SessionDriver::Native => $this->createNativeDriver(),
            SessionDriver::Array  => $this->createArrayDriver(),
        };
    }

    /**
     * Private factory method for creating a `NativeSession` driver.
     *
     * - Encapsulates the instantiation of a native PHP session.
     * - Injects the session store (`NativeSessionStore`) with a DI-compliant registry factory.
     *
     * @return NativeSession
     *   A fully configured instance of the `NativeSession` driver.
     */
    private function createNativeDriver() : NativeSession
    {
        return new NativeSession(
        // NativeSession requires a specific storage implementation.
            store          : new NativeSessionStore(),
            // Passing in a lazily evaluated factory to enable bag registry resolution.
            registryFactory: fn(SessionInterface $s) => $this->registryFactory->create(session: $s)
        );
    }

    /**
     * Private factory method for creating an `ArraySession` driver.
     *
     * - Encapsulates the instantiation of an in-memory session.
     * - Leverages an `ArraySessionStore` for non-persistent data storage.
     *
     * @return ArraySession
     *   A fully configured instance of the `ArraySession` driver.
     */
    private function createArrayDriver() : ArraySession
    {
        return new ArraySession(
        // ArraySession uses an in-memory storage implementation.
            store          : new ArraySessionStore(),
            // A lazily evaluated factory creates a session bag registry for this session.
            registryFactory: fn(SessionInterface $s) => $this->registryFactory->create(session: $s)
        );
    }

    /**
     * Provides access to the BagRegistryFactoryInterface for testability and runtime overrides.
     *
     * This method promotes flexibility by exposing the registry factory instance,
     * allowing consumers to interact with the factory (e.g., mocking in tests).
     *
     * @return BagRegistryFactoryInterface
     *   The factory responsible for creating bag registries for sessions.
     */
    public function getRegistry() : BagRegistryFactoryInterface
    {
        return $this->registryFactory;
    }
}