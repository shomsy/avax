<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge;

use Avax\Auth\System\Foundation\Clock;
use InvalidArgumentException;

/**
 * Throttles repeated MFA verification failures per user or challenge key.
 */
final readonly class LimitMfaAttempts
{
    private int                          $decaySeconds;
    private int                          $maxAttempts;
    private Clock                        $clock;
    private AttemptLimitStorageInterface $storage;

    public function __construct(
        AttemptLimitStorageInterface $storage,
        Clock                        $clock,
        int|null                     $maxAttempts = null,
        int                          $decaySeconds = 300
    )
    {
        $maxAttempts        ??= 5;
        $this->storage      = $storage;
        $this->clock        = $clock;
        $this->maxAttempts  = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
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
    public function check(string $key) : void
    {
        $attempts = $this->storage->get(key: $key);

        if ($attempts < $this->maxAttempts) {
            return;
        }

        $elapsed = $this->clock->now()->getTimestamp() - $this->storage->getLastAttemptTime(key: $key);

        if ($elapsed >= $this->decaySeconds) {
            $this->storage->reset(key: $key);

            return;
        }

        throw new MfaAttemptLimitReached(retryAfter: max(0, $this->decaySeconds - $elapsed));
    }

    public function reset(string $key) : void
    {
        $this->storage->reset(key: $key);
    }

    public function recordFailed(string $key) : void
    {
        $this->storage->increment(key: $key, timestamp: $this->clock->now()->getTimestamp());
    }
}
