<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

use RuntimeException;

/**
 * SecureOnlyPolicy - HTTPS-Only Session Policy
 *
 * Enforces that session operations only occur over HTTPS.
 * Prevents session hijacking over insecure connections.
 */
final class SecureOnlyPolicy implements PolicyInterface
{
    /**
     * Enforce HTTPS-only policy.
     *
     * @param  array<string, mixed>  $data  Current session data.
     *
     * @throws \RuntimeException If connection is not HTTPS.
     */
    public function enforce(array $data): void
    {
        $isSecure = (bool) ($data['is_secure'] ?? false);

        if (! $isSecure) {
            throw new RuntimeException(
                message: 'Session access requires HTTPS connection for security.'
            );
        }
    }

    /**
     * Get policy name.
     *
     * @return string Policy identifier.
     */
    public function getName(): string
    {
        return 'secure_only';
    }
}
