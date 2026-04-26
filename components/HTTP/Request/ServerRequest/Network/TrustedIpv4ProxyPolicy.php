<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\Network;

/**
 * TrustedProxyPolicy
 *
 * Policy owner for deciding whether a remote address belongs to a trusted proxy.
 *
 * Supported rules:
 * - exact IPv4 match
 * - IPv4 CIDR match
 * - wildcard trust via "*"
 * - full IPv4 wildcard via "0.0.0.0/0"
 *
 * Notes:
 * - current implementation is IPv4-focused
 * - invalid IPs or invalid CIDR rules are treated as non-matching
 */
final readonly class TrustedIpv4ProxyPolicy
{
    /**
     * @param list<string> $trustedProxies
     */
    public function __construct(private array $trustedProxies = []) {}

    public function isTrusted(string $ip) : bool
    {
        if ($ip === '' || filter_var(value: $ip, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV4) === false) {
            return false;
        }

        return array_any(array: $this->trustedProxies, callback: fn ($rule) => $this->matchesRule(ip: $ip, rule: $rule));
    }

    private function matchesRule(string $ip, string $rule) : bool
    {
        $rule = trim(string: $rule);

        if ($rule === '') {
            return false;
        }

        if ($rule === '*' || $rule === '0.0.0.0/0') {
            return true;
        }

        if (str_contains(haystack: $rule, needle: '/')) {
            return $this->matchesIpv4Cidr(ip: $ip, cidr: $rule);
        }

        return $ip === $rule;
    }

    private function matchesIpv4Cidr(string $ip, string $cidr) : bool
    {
        [$subnet, $mask] = explode(separator: '/', string: $cidr, limit: 2) + [1 => '32'];

        if (
            filter_var(value: $subnet, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV4) === false
            || ! ctype_digit(text: $mask)
        ) {
            return false;
        }

        $maskInt = (int) $mask;

        if ($maskInt < 0 || $maskInt > 32) {
            return false;
        }

        $ipLong     = ip2long(ip: $ip);
        $subnetLong = ip2long(ip: $subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        if ($maskInt === 0) {
            return true;
        }

        $maskBits = -1 << (32 - $maskInt);

        return ($ipLong & $maskBits) === ($subnetLong & $maskBits);
    }
}