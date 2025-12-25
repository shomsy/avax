<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Policies;

use RuntimeException;

/**
 * MaxIdlePolicy - Session Idle Timeout Policy
 *
 * Enforces maximum idle time for sessions. If session is inactive
 * longer than the configured period, policy violation is triggered.
 *
 * @package Avax\HTTP\Session\Policies
 */
final class MaxIdlePolicy implements PolicyInterface
{
    /**
     * MaxIdlePolicy Constructor.
     *
     * @param int $maxIdleSeconds Maximum idle time in seconds (default: 30 minutes).
     */
    public function __construct(
        private int $maxIdleSeconds = 1800
    ) {}

    /**
     * Enforce max idle policy.
     *
     * @param array<string, mixed> $data Current session data.
     *
     * @return void
     * @throws \RuntimeException If session is idle too long.
     */
    public function enforce(array $data) : void
    {
        $lastActivity = $data['_last_activity'] ?? null;

        if ($lastActivity === null) {
            return;
        }

        $idleTime = time() - $lastActivity;

        if ($idleTime > $this->maxIdleSeconds) {
            throw new RuntimeException(
                message: sprintf(
                    'Session expired due to inactivity. Idle for %d seconds (max: %d).',
                    $idleTime,
                    $this->maxIdleSeconds
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
        return 'max_idle';
    }
}
