<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\Network;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use SensitiveParameter;

/**
 * ResolveClientAddress - Action owner for determining the actual client IP.
 */
final readonly class ResolveClientAddress
{
    public function __construct(
        private TrustedProxyPolicy|TrustedIpv4ProxyPolicy $proxyPolicy,
        private ParseForwardedAddresses $forwardedParser,
    ) {}

    public function execute(string $remoteAddr, #[SensitiveParameter] RequestHeaders $headers) : string|null
    {
        if (! $this->proxyPolicy->isTrusted(ip: $remoteAddr)) {
            return $remoteAddr;
        }

        $forwardedFor = $headers->getLine(name: 'X-Forwarded-For');
        $ips          = $this->forwardedParser->execute(headerLine: $forwardedFor);

        if ($ips === []) {
            return $remoteAddr;
        }

        return reset($ips);
    }
}
