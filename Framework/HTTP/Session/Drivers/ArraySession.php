<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Drivers;

use Closure;
use Gemini\HTTP\Session\AbstractSession;
use Gemini\HTTP\Session\Contracts\BagRegistryInterface;
use Gemini\HTTP\Session\Contracts\SessionInterface;
use Gemini\HTTP\Session\Contracts\SessionStoreInterface;

/**
 * ArraySession
 *
 * In-memory session implementation. Primarily used for testing purposes
 * and non-persistent session storage. This should not be used in production
 * environments due to the volatile nature of in-memory storage.
 *
 * @package Gemini\HTTP\Session\Drivers
 */
final class ArraySession extends AbstractSession
{
    /**
     * Constructor for ArraySession.
     * Dependency Injection ensures this class adheres to the principle of Inversion of Control.
     *
     * @param SessionStoreInterface $store The store implementation for session handling.
     */
    public function __construct(
        SessionStoreInterface $store,
        Closure               $registryFactory
    ) {
        /** @var Closure(SessionInterface): BagRegistryInterface $registryFactory */
        $registry = $registryFactory($this);
        // TODO: Make this final logic for ArraySession when time comes
        parent::__construct(
            store   : $store,
            registry: $registry
        );
    }

    /**
     * Potential edge cases and usage scenarios:
     * - This session driver is intentionally in-memory and ephemeral.
     *   When the application terminates, session data will be lost.
     * - Suitable for unit or integration tests, and scenarios where persistence is not required.
     * - Ensure no reliance on long-lived session data to avoid unexpected behavior in production-like environments.
     *
     * Security warning:
     * - Do not use this in environments requiring persistent or distributed sessions.
     */
}