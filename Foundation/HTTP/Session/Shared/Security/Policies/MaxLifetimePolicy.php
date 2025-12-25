<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

use RuntimeException;

/**
 * MaxLifetimePolicy - Session Maximum Lifetime Policy
 *
 * Enforces absolute maximum lifetime for sessions regardless of activity.
 * Once session reaches max lifetime, it must be terminated.
 *
 * @package Avax\HTTP\Session\Policies
 */
final class MaxLifetimePolicy implements PolicyInterface
{
    /**
     * MaxLifetimePolicy Constructor.
     *
     * @param int $maxLifetimeSeconds Maximum session lifetime in seconds (default: 1 hour).
     */
    public function __construct(
        private int $maxLifetimeSeconds = 3600
    ) {}

    /**
     * Enforce max lifetime policy.
     *
     * @param array<string, mixed> $data Current session data.
     *
     * @return void
     * @throws \RuntimeException If session exceeded max lifetime.
     */
    public function enforce(array $data) : void
    {
        $createdAt = $data['_created_at'] ?? null;

        if ($createdAt === null) {
            return;
        }

        $lifetime = time() - $createdAt;

        if ($lifetime > $this->maxLifetimeSeconds) {
            throw new RuntimeException(
                message: sprintf(
                    'Session expired (max lifetime). Active for %d seconds (max: %d).',
                    $lifetime,
                    $this->maxLifetimeSeconds
                )
            );
        }
    }

    /**
     * Get policy name.
     *
     * @return string Policy identifier.
     */
    public function getName() : string
    {
        return 'max_lifetime';
    }
}
