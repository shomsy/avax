<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login\RateLimit;

use Avax\Auth\System\Foundation\Clock;
use InvalidArgumentException;

/**
 * Handle login rate limiting logic within the Auth System.
 * 
 * Banal: The gatekeeper for login attempts.
 */
final readonly class LoginRateLimit
{
    public function __construct(
        private LoginRateLimitStorageInterface $storage,
        private Clock $clock,
        private int $maxAttempts = 5,
        private int $decaySeconds = 60
    ) {
        if ($this->maxAttempts < 1) {
            throw new InvalidArgumentException('Max attempts must be at least 1.');
        }

        if ($this->decaySeconds < 0) {
            throw new InvalidArgumentException('Decay seconds cannot be negative.');
        }
    }

    /**
     * @throws \Avax\Auth\System\Flows\Login\RateLimit\RateLimitException
     */
    public function check(string $identifier) : void
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $attempts = $this->storage->get(identifier: $identifier);

        if ($attempts >= $this->maxAttempts) {
            $lastAttemptTime = $this->storage->getLastAttemptTime(identifier: $identifier);
            $elapsed = $this->clock->now()->getTimestamp() - $lastAttemptTime;

            if ($elapsed < $this->decaySeconds) {
                $retryAfter = max(0, $this->decaySeconds - $elapsed);

                throw new RateLimitException(
                    message: "Too many login attempts. Please try again in {$retryAfter} seconds.",
                    retryAfter: $retryAfter
                );
            }

            $this->storage->reset(identifier: $identifier);
        }
    }

    public function recordFailed(string $identifier) : void
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $this->storage->increment(identifier: $identifier);
    }

    public function reset(string $identifier) : void
    {
        $identifier = $this->normalizeIdentifier($identifier);
        $this->storage->reset(identifier: $identifier);
    }

    private function normalizeIdentifier(string $identifier) : string
    {
        return strtolower(trim($identifier));
    }
}
