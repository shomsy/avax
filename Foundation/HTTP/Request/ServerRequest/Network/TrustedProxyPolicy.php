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
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

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

        if (str_contains($trusted, '/')) {
            [$range, $netmask] = explode('/', $trusted, 2);
            
            $rangeLong = ip2long($range);
            $ipLong    = ip2long($ip);
            
            if ($rangeLong === false || $ipLong === false) {
                return false;
            }

            $mask = ~((1 << (32 - (int) $netmask)) - 1);

            return ($ipLong & $mask) === ($rangeLong & $mask);
        }

        return false;
    }
}
