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
    public function __construct(
        private AttemptLimitStorageInterface $storage,
        private Clock                        $clock,
        private int                          $maxAttempts = 5,
        private int                          $decaySeconds = 300
    )
    {
        if ($this->maxAttempts < 1) {
            throw new InvalidArgumentException('Max attempts must be at least 1.');
        }

        if ($this->decaySeconds < 0) {
            throw new InvalidArgumentException('Decay seconds cannot be negative.');
        }
    }

    /**
     * @throws MfaAttemptLimitReached
     */
    public function check(string $key) : void
    {
        $attempts = $this->storage->get($key);

        if ($attempts < $this->maxAttempts) {
            return;
        }

        $elapsed = $this->clock->now()->getTimestamp() - $this->storage->getLastAttemptTime($key);

        if ($elapsed >= $this->decaySeconds) {
            $this->storage->reset($key);

            return;
        }

        throw new MfaAttemptLimitReached(max(0, $this->decaySeconds - $elapsed));
    }

    public function reset(string $key) : void
    {
        $this->storage->reset($key);
    }

    public function recordFailed(string $key) : void
    {
        $this->storage->increment($key, $this->clock->now()->getTimestamp());
    }
}
