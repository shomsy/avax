<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login\RateLimit;

use Avax\Auth\System\Foundation\Clock;

/**
 * Handle login rate limiting logic within the Auth System.
 * 
 * Banal: The gatekeeper for login attempts.
 */
final readonly class LoginRateLimit
{
    private const int MAX_ATTEMPTS = 5;
    private const int DECAY_SECONDS = 60;

    public function __construct(
        private LoginRateLimitStorageInterface $storage,
        private Clock $clock
    ) {}

    /**
     * @throws \Avax\Auth\System\Flows\Login\RateLimit\RateLimitException
     */
    public function check(string $identifier) : void
    {
        $attempts = $this->storage->get(identifier: $identifier);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lastAttemptTime = $this->storage->getLastAttemptTime(identifier: $identifier);
            $elapsed = $this->clock->now()->getTimestamp() - $lastAttemptTime;

            if ($elapsed < self::DECAY_SECONDS) {
                throw new RateLimitException(
                    message: "Too many login attempts. Please try again in " . (self::DECAY_SECONDS - $elapsed) . " seconds.",
                    retryAfter: self::DECAY_SECONDS - $elapsed
                );
            }

            $this->storage->reset(identifier: $identifier);
        }
    }

    public function recordFailed(string $identifier) : void
    {
        $this->storage->increment(identifier: $identifier);
    }

    public function reset(string $identifier) : void
    {
        $this->storage->reset(identifier: $identifier);
    }
}
