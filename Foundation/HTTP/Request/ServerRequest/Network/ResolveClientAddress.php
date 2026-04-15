<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\Network;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;

/**
 * Action Owner: Securely resolves the client IP address.
 */
final readonly class ResolveClientAddress
{
    private TrustedProxyPolicy $proxyPolicy;

    public function __construct(
        TrustedProxyPolicy $proxyPolicy = new TrustedProxyPolicy()
    )
    {
        $this->proxyPolicy = $proxyPolicy;
    }

    public function execute(string $remoteAddr, #[\SensitiveParameter] RequestHeaders $headers) : string
    {
        if (! $this->proxyPolicy->isTrusted(ip: $remoteAddr)) {
            return $remoteAddr;
        }

        $forwarded = (new ParseForwardedAddresses())->execute(headers: $headers);

        if ($forwarded === []) {
            return $remoteAddr;
        }

        // Return the first address in the chain (closest to the client)
        // Note: Production implementations might want to strip trusted proxies from the RIGHT.
        return $forwarded[0];
    }
}
