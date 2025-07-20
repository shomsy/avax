<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Drivers;

use Closure;
use Gemini\HTTP\Session\AbstractSession;
use Gemini\HTTP\Session\Contracts\BagRegistryInterface;
use Gemini\HTTP\Session\Contracts\SessionInterface;
use Gemini\HTTP\Session\Contracts\SessionStoreInterface;

/**
 * Class NativeSession
 *
 * A concrete implementation of `AbstractSession` that utilizes PHP's native session handling mechanism.
 *
 * This class adheres to the principles of DDD by encapsulating the session handling behavior
 * and delegating responsibilities to specialized interfaces.
 */
final class NativeSession extends AbstractSession
{
    /**
     * NativeSession Constructor.
     *
     * This constructor uses PHP 8.3's constructor property promotion for concise and expressive initialization
     * while adhering to SRP (Single Responsibility Principle) by delegating storage and cryptographic logic to their
     * respective interfaces.
     *
     * @param SessionStoreInterface $store The storage mechanism the session will use to persist session data.
     * @param \Closure              $registryFactory
     */
    public function __construct(
        SessionStoreInterface $store,
        Closure               $registryFactory
    ) {
        /** @var Closure(SessionInterface): BagRegistryInterface $registryFactory */
        $registry = $registryFactory($this);

        parent::__construct(
            store   : $store,
            registry: $registry
        );
    }
}