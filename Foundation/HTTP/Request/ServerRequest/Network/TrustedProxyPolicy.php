<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\Network;

/**
 * TrustedProxyPolicy - Policy owner for identifying trusted network sources.
 */
final readonly class TrustedProxyPolicy
{
    /** @var string[] */
    private array $trustedProxies;

    public function __construct(array $trustedProxies = [])
    {
        $this->trustedProxies = $trustedProxies;
    }

    public function isTrusted(string $ip) : bool
    {
        return in_array($ip, $this->trustedProxies, true);
    }
}
