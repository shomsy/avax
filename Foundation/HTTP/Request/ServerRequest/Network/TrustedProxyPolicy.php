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

    public function isTrusted(string $ip): bool
    {
        foreach ($this->trustedProxies as $proxy) {
            if ($proxy === '*' || $proxy === '0.0.0.0/0') {
                return true;
            }

            if (str_contains($proxy, '/')) {
                if ($this->ipMatchesCidr(ip: $ip, cidr: $proxy)) {
                    return true;
                }
            } elseif ($ip === $proxy) {
                return true;
            }
        }

        return false;
    }

    private function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr, 2) + [1 => '32'];
        $mask = (int) $mask;

        if ($mask < 0 || $mask > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $shift = 32 - $mask;
        
        // Handle /0 case to avoid shifting by 32 which is undefined behavior in some architectures
        if ($shift === 32) {
            return true;
        }

        return ($ipLong >> $shift) === ($subnetLong >> $shift);
    }
}
