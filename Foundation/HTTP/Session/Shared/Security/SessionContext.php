<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security;

use Avax\HTTP\Context\HttpContextInterface;
use Avax\HTTP\Session\Shared\Contracts\Security\SessionIdProviderInterface;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;

/**
 * Default session context implementation.
 */
final readonly class SessionContext implements SessionContextInterface
{
    public function __construct(
        private StoreInterface             $store,
        private SessionIdProviderInterface $idProvider,
        private HttpContextInterface       $httpContext
    ) {}

    public function sessionId() : string
    {
        return $this->idProvider->current();
    }

    public function userId() : string|int|null
    {
        $value = $this->store->get(key: 'user_id');
        if (is_string($value) || is_int($value)) {
            return $value;
        }

        return null;
    }

    public function clientIp() : string|null
    {
        $stored = $this->store->get(key: 'ip_address');
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return $this->httpContext->clientIp();
    }

    public function userAgent() : string|null
    {
        $stored = $this->store->get(key: 'user_agent');
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return $this->httpContext->userAgent();
    }

    public function isSecure() : bool
    {
        return $this->httpContext->isSecure();
    }
}
