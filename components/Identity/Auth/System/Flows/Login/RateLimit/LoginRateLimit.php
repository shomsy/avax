<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login\RateLimit;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use InvalidArgumentException;

/**
 * Handle login rate limiting logic within the Auth System.
 *
 * Banal: The gatekeeper for login attempts.
 */
final readonly class LoginRateLimit
{
    private int $maxAttempts;

    public function __construct(
        private LoginRateLimitStorageInterface $loginRateLimitStorage,
        private Clock $clock, int|null $maxAttempts = null,
        private int                            $decaySeconds = 60,
    )
    {
        $maxAttempts       ??= 5;
        $this->maxAttempts = $maxAttempts;
        if ($this->maxAttempts < 1) {
            throw new InvalidArgumentException(message: 'Max attempts must be at least 1.');
        }

        if ($this->decaySeconds < 0) {
            throw new InvalidArgumentException(message: 'Decay seconds cannot be negative.');
        }
    }

    /**
     * @throws RateLimitException
     */
    public function check(string $identifier) : void
    {
        $identifier = $this->normalizeIdentifier(identifier: $identifier);
        $attempts   = $this->loginRateLimitStorage->get(identifier: $identifier);

        if ($attempts >= $this->maxAttempts) {
            $lastAttemptTime = $this->loginRateLimitStorage->getLastAttemptTime(identifier: $identifier);
            $elapsed         = $this->clock->now()->getTimestamp() - $lastAttemptTime;

            if ($elapsed < $this->decaySeconds) {
                $retryAfter = max(0, $this->decaySeconds - $elapsed);

                throw new RateLimitException(
                    message   : sprintf('Too many login attempts. Please try again in %s seconds.', $retryAfter),
                    retryAfter: $retryAfter,
                );
            }

            $this->loginRateLimitStorage->reset(identifier: $identifier);
        }
    }

    private function normalizeIdentifier(string $identifier) : string
    {
        return strtolower(string: trim(string: $identifier));
    }

    public function reset(string $identifier) : void
    {
        $identifier = $this->normalizeIdentifier(identifier: $identifier);
        $this->loginRateLimitStorage->reset(identifier: $identifier);
    }

    public function recordFailed(string $identifier) : void
    {
        $identifier = $this->normalizeIdentifier(identifier: $identifier);
        $this->loginRateLimitStorage->increment(identifier: $identifier);
    }
}
