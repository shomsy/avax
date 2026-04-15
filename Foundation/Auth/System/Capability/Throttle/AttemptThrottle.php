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
    private int                           $decaySeconds;
    private int                           $maxAttempts;
    private Clock                         $clock;
    private AttemptThrottleStoreInterface $store;

    public function __construct(
        AttemptThrottleStoreInterface $store,
        Clock                         $clock,
        int|null                      $maxAttempts = null,
        int                           $decaySeconds = 900
    )
    {
        $maxAttempts        ??= 5;
        $this->store        = $store;
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
     * @throws AttemptThrottleExceeded
     */
    public function check(string $key) : void
    {
        $attempts = $this->store->get(key: $key);

        if ($attempts < $this->maxAttempts) {
            return;
        }

        $elapsed = $this->clock->now()->getTimestamp() - $this->store->getLastAttemptTime(key: $key);

        if ($elapsed >= $this->decaySeconds) {
            $this->store->reset(key: $key);

            return;
        }

        throw new AttemptThrottleExceeded(retryAfter: max(0, $this->decaySeconds - $elapsed));
    }

    public function reset(string $key) : void
    {
        $this->store->reset(key: $key);
    }

    public function recordAttempt(string $key) : void
    {
        $this->store->increment(key: $key, timestamp: $this->clock->now()->getTimestamp());
    }
}
