<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use InvalidArgumentException;

/**
 * Throttles repeated MFA verification failures per user or challenge key.
 */
final readonly class LimitMfaAttempts
{
    private int $maxAttempts;

    public function __construct(
        private AttemptLimitStorageInterface $attemptLimitStorage,
        private Clock $clock,
        ?int                                 $maxAttempts = null,
        private int $decaySeconds = 300,
    ) {
        $maxAttempts ??= 5;
        $this->maxAttempts = $maxAttempts;
        if ($this->maxAttempts < 1) {
            throw new InvalidArgumentException(message: 'Max attempts must be at least 1.');
        }

        if ($this->decaySeconds < 0) {
            throw new InvalidArgumentException(message: 'Decay seconds cannot be negative.');
        }
    }

    /**
     * @throws MfaAttemptLimitReached
     */
    public function check(string $key): void
    {
        $attempts = $this->attemptLimitStorage->get(key: $key);

        if ($attempts < $this->maxAttempts) {
            return;
        }

        $elapsed = $this->clock->now()->getTimestamp() - $this->attemptLimitStorage->getLastAttemptTime(key: $key);

        if ($elapsed >= $this->decaySeconds) {
            $this->attemptLimitStorage->reset(key: $key);

            return;
        }

        throw new MfaAttemptLimitReached(retryAfter: max(0, $this->decaySeconds - $elapsed));
    }

    public function reset(string $key): void
    {
        $this->attemptLimitStorage->reset(key: $key);
    }

    public function recordFailed(string $key): void
    {
        $this->attemptLimitStorage->increment(key: $key, timestamp: $this->clock->now()->getTimestamp());
    }
}
