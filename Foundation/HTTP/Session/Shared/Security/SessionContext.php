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
    private HttpContextInterface       $httpContext;
    private SessionIdProviderInterface $idProvider;
    private StoreInterface             $store;

    public function __construct(
        StoreInterface             $store,
        SessionIdProviderInterface $idProvider,
        HttpContextInterface       $httpContext
    )
    {
        $this->store       = $store;
        $this->idProvider  = $idProvider;
        $this->httpContext = $httpContext;
    }

    public function sessionId() : string
    {
        return $this->idProvider->current();
    }

    public function userId() : string|int|null
    {
        $value = $this->store->get(key: 'user_id');
        if (is_string(value: $value) || is_int(value: $value)) {
            return $value;
        }

        return null;
    }

    public function clientIp() : string|null
    {
        $stored = $this->store->get(key: 'ip_address');
        if (is_string(value: $stored) && $stored !== '') {
            return $stored;
        }

        return $this->httpContext->clientIp();
    }

    public function userAgent() : string|null
    {
        $stored = $this->store->get(key: 'user_agent');
        if (is_string(value: $stored) && $stored !== '') {
            return $stored;
        }

        return $this->httpContext->userAgent();
    }

    public function isSecure() : bool
    {
        return $this->httpContext->isSecure();
    }
}
