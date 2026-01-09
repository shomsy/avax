<?php

declare(strict_types=1);

namespace Avax\Auth\Adapters;


use Avax\HTTP\Session\Session;

/**
 * Class RateLimiterService
 *
 * Implements rate limiting logic using session storage.
 */
class RateLimiter
{
    /**
     * The default maximum number of attempts allowed.
     */
    private int $defaultMaxAttempts = 5;

    /**
     * The default duration (in seconds) for which a lockout is enforced.
     */
    private int $defaultLockoutDuration = 300; // 5 minutes lockout duration

    /**
     * @param Session $session The session management implementation.
     */
    public function __construct(private readonly Session $session) {}

    /**
     * Determines if a user or identifier can attempt an action.
     */
    public function canAttempt(string $identifier, int|null $maxAttempts = null, int|null $timeWindow = null): bool
    {
        $maxAttempts ??= $this->defaultMaxAttempts;
        $attempts = $this->getAttempts(identifier: $identifier);

        return $attempts < $maxAttempts && ! $this->isLockedOut(identifier: $identifier);
    }

    private function getAttempts(string $identifier): int
    {
        $attemptsKey = $this->getSessionKey(identifier: $identifier, property: 'attempts');

        return $this->session->get(key: $attemptsKey, default: 0);
    }

    private function getSessionKey(string $identifier, string $property): string
    {
        return hash('sha256', sprintf('rate_limiter_%s_%s', $identifier, $property));
    }

    private function isLockedOut(string $identifier): bool
    {
        $lockoutKey   = $this->getSessionKey(identifier: $identifier, property: 'lockout_until');
        $lockoutUntil = $this->session->get(key: $lockoutKey);

        return $lockoutUntil && time() < strtotime($lockoutUntil);
    }

    public function recordFailedAttempt(
        string   $identifier,
        int|null $maxAttempts = null,
        int|null $timeWindow = null
    ): void {
        $maxAttempts ??= $this->defaultMaxAttempts;
        $timeWindow  ??= $this->defaultLockoutDuration;

        $attemptsKey = $this->getSessionKey(identifier: $identifier, property: 'attempts');
        $attempts    = $this->getAttempts(identifier: $identifier) + 1;

        $this->session->put(key: $attemptsKey, value: $attempts);

        if ($attempts >= $maxAttempts) {
            $this->lockOut(identifier: $identifier, duration: $timeWindow);
        }
    }

    private function lockOut(string $identifier, int $duration): void
    {
        $lockoutKey   = $this->getSessionKey(identifier: $identifier, property: 'lockout_until');
        $lockoutUntil = date('Y-m-d H:i:s', time() + $duration);

        $this->session->put(key: $lockoutKey, value: $lockoutUntil);
    }

    public function resetAttempts(string $identifier): void
    {
        $attemptsKey = $this->getSessionKey(identifier: $identifier, property: 'attempts');
        $lockoutKey  = $this->getSessionKey(identifier: $identifier, property: 'lockout_until');

        $this->session->forget(key: $attemptsKey);
        $this->session->forget(key: $lockoutKey);
    }
}
