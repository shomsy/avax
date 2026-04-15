<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\Network;

/**
 * Action Owner: Determines if a given IP address is a trusted proxy.
 */
final readonly class TrustedProxyPolicy
{
    private array $trustedProxies;

    /**
     * @param string[] $trustedProxies List of IPs or CIDR ranges.
     */
    public function __construct(
        array $trustedProxies = []
    )
    {
        $this->trustedProxies = $trustedProxies;
    }

    public function isTrusted(string $ip) : bool
    {
        if ($this->trustedProxies === ['*']) {
            return true;
        }

        foreach ($this->trustedProxies as $trusted) {
            if ($this->checkIp(ip: $ip, trusted: $trusted)) {
                return true;
            }
        }

        return false;
    }

    private function checkIp(string $ip, string $trusted) : bool
    {
        if ($ip === $trusted) {
            return true;
        }

        // Simple CIDR check
        if (str_contains($trusted, '/')) {
            [$range, $netmask] = explode('/', $trusted, 2);
            $rangeInt = ip2long($range);
            $ipInt    = ip2long($ip);
            $mask     = ~((1 << (32 - (int) $netmask)) - 1);

            return ($ipInt & $mask) === ($rangeInt & $mask);
        }

        return false;
    }
}
