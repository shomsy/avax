<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\Network;

/**
 * TrustedProxyPolicy
 *
 * Policy owner for deciding whether a remote address belongs to a trusted proxy.
 *
 * Supported rules:
 * - exact IPv4/IPv6 match
 * - IPv4/IPv6 CIDR match
 * - wildcard trust via "*"
 *
 * Notes:
 * - invalid IPs or invalid CIDR rules are treated as non-matching
 */
final readonly class TrustedProxyPolicy
{
    /**
     * @param list<string> $trustedProxies
     */
    public function __construct(private array $trustedProxies = []) {}

    public function isTrusted(string $ip) : bool
    {
        if ($ip === '') {
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

        if ($rule === '*') {
            return true;
        }

        if (str_contains(haystack: $rule, needle: '/')) {
            return $this->matchesCidr(ip: $ip, cidr: $rule);
        }

        return $ip === $rule;
    }

    private function matchesCidr(string $ip, string $cidr) : bool
    {
        if (str_contains(haystack: $ip, needle: ':')) {
            return $this->matchesIpv6Cidr(ip: $ip, cidr: $cidr);
        }

        return $this->matchesIpv4Cidr(ip: $ip, cidr: $cidr);
    }

    private function matchesIpv6Cidr(string $ip, string $cidr) : bool
    {
        if (! str_contains(haystack: $cidr, needle: '/') || ! str_contains(haystack: $cidr, needle: ':')) {
            return false;
        }

        [$subnet, $mask] = explode(separator: '/', string: $cidr, limit: 2) + [1 => '128'];

        if (
            filter_var(value: $ip, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV6) === false ||
            filter_var(value: $subnet, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV6) === false ||
            ! ctype_digit(text: $mask)
        ) {
            return false;
        }

        $maskInt = (int) $mask;
        if ($maskInt < 0 || $maskInt > 128) {
            return false;
        }

        $ipBin     = inet_pton(ip: $ip);
        $subnetBin = inet_pton(ip: $subnet);

        $maskBin = str_repeat(string: "\xff", times: $maskInt >> 3);
        if ($maskInt % 8 !== 0) {
            $maskBin .= chr(codepoint: 0xff << (8 - ($maskInt % 8)));
        }
        $maskBin = str_pad(string: $maskBin, length: 16, pad_string: "\x00");

        return ($ipBin & $maskBin) === ($subnetBin & $maskBin);
    }

    private function matchesIpv4Cidr(string $ip, string $cidr) : bool
    {
        if (! str_contains(haystack: $cidr, needle: '/') || str_contains(haystack: $cidr, needle: ':')) {
            return false;
        }

        [$subnet, $mask] = explode(separator: '/', string: $cidr, limit: 2) + [1 => '32'];

        if (
            filter_var(value: $ip, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV4) === false ||
            filter_var(value: $subnet, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV4) === false ||
            ! ctype_digit(text: $mask)
        ) {
            return false;
        }

        $maskInt = (int) $mask;
        if ($maskInt < 0 || $maskInt > 32) {
            return false;
        }

        $ipLong     = ip2long(ip: $ip);
        $subnetLong = ip2long(ip: $subnet);

        if ($maskInt === 0) {
            return true;
        }

        $maskBits = -1 << (32 - $maskInt);

        return ($ipLong & $maskBits) === ($subnetLong & $maskBits);
    }
}
