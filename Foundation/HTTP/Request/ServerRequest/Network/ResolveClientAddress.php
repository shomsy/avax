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

        // Traverse the chain from right to left, stripping trusted proxies.
        // The first non-trusted IP we encounter is the real client IP.
        $clientAddress = $remoteAddr;
        $ips           = array_reverse($forwarded);

        foreach ($ips as $ip) {
            if ($this->proxyPolicy->isTrusted(ip: $ip)) {
                $clientAddress = $ip; // Still a trusted proxy, keep moving left
                continue;
            }

            return $ip; // Found the real client
        }

        return $clientAddress;
    }
}
