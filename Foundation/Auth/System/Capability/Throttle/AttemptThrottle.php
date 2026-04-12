<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Throttle;

use Avax\Auth\System\Foundation\Clock;
use InvalidArgumentException;

/**
 * Shared attempt throttling for auth-sensitive entry points.
 */
final readonly class AttemptThrottle
{
    public function __construct(
        private AttemptThrottleStoreInterface $store,
        private Clock                         $clock,
        private int                           $maxAttempts = 5,
        private int                           $decaySeconds = 900
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
     * @throws AttemptThrottleExceeded
     */
    public function check(string $key) : void
    {
        $attempts = $this->store->get($key);

        if ($attempts < $this->maxAttempts) {
            return;
        }

        $elapsed = $this->clock->now()->getTimestamp() - $this->store->getLastAttemptTime($key);

        if ($elapsed >= $this->decaySeconds) {
            $this->store->reset($key);

            return;
        }

        throw new AttemptThrottleExceeded(max(0, $this->decaySeconds - $elapsed));
    }

    public function recordAttempt(string $key) : void
    {
        $this->store->increment($key, $this->clock->now()->getTimestamp());
    }

    public function reset(string $key) : void
    {
        $this->store->reset($key);
    }
}
