<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security\Policies;

use Avax\HTTP\Session\Exceptions\PolicyViolationException;

/**
 * SessionIpPolicy - IP Address Binding Policy
 *
 * OWASP ASVS 3.4.1 Compliant
 * 
 * Binds sessions to client IP address to detect session hijacking.
 * Supports both strict mode (exact match) and relaxed mode (subnet match).
 * 
 * Security Trade-offs:
 * - Strict: More secure, but breaks with mobile networks (IP changes)
 * - Relaxed: Less secure, but handles legitimate IP changes
 * 
 * @package Avax\HTTP\Session\Security\Policies
 */
final class SessionIpPolicy implements PolicyInterface
{
    /**
     * SessionIpPolicy Constructor.
     *
     * @param bool $strictMode If true, require exact IP match. If false, allow /24 subnet.
     */
    public function __construct(
        private bool $strictMode = false
    ) {}

    /**
     * {@inheritdoc}
     */
    public function enforce(array $data): void
    {
        $storedIp = $data['_client_ip'] ?? null;
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';

        // First time - no stored IP yet
        if ($storedIp === null) {
            return;
        }

        if ($this->strictMode) {
            // Strict: Exact match required
            if ($storedIp !== $currentIp) {
                throw PolicyViolationException::forPolicy(
                    'ip_binding_strict',
                    'IP address mismatch (strict) - possible session hijacking'
                );
            }
        } else {
            // Relaxed: Same /24 subnet
            if (!$this->isSameSubnet($storedIp, $currentIp)) {
                throw PolicyViolationException::forPolicy(
                    'ip_binding_relaxed',
                    'IP subnet mismatch - possible session hijacking'
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->strictMode ? 'ip_binding_strict' : 'ip_binding_relaxed';
    }

    /**
     * Check if two IPs are in the same /24 subnet.
     *
     * @param string $ip1 First IP.
     * @param string $ip2 Second IP.
     *
     * @return bool True if same subnet.
     */
    private function isSameSubnet(string $ip1, string $ip2): bool
    {
        $parts1 = explode('.', $ip1);
        $parts2 = explode('.', $ip2);

        // Compare first 3 octets (class C subnet)
        return $parts1[0] === $parts2[0]
            && $parts1[1] === $parts2[1]
            && $parts1[2] === $parts2[2];
    }
}
