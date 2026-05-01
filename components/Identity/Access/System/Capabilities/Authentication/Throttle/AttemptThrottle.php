<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use InvalidArgumentException;

/**
 * Shared attempt throttling for auth-sensitive entry points.
 */
final readonly class AttemptThrottle
{
    private int $maxAttempts;

    public function __construct(
        private AttemptThrottleStoreInterface $attemptThrottleStore,
        private Clock $clock,
        ?int                                  $maxAttempts = null,
        private int $decaySeconds = 900,
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
     * @throws AttemptThrottleExceeded
     */
    public function check(string $key): void
    {
        $attempts = $this->attemptThrottleStore->get(key: $key);

        if ($attempts < $this->maxAttempts) {
            return;
        }

        $elapsed = $this->clock->now()->getTimestamp() - $this->attemptThrottleStore->getLastAttemptTime(key: $key);

        if ($elapsed >= $this->decaySeconds) {
            $this->attemptThrottleStore->reset(key: $key);

            return;
        }

        throw new AttemptThrottleExceeded(retryAfter: max(0, $this->decaySeconds - $elapsed));
    }

    public function reset(string $key): void
    {
        $this->attemptThrottleStore->reset(key: $key);
    }

    public function recordAttempt(string $key): void
    {
        $this->attemptThrottleStore->increment(key: $key, timestamp: $this->clock->now()->getTimestamp());
    }
}
