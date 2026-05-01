<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker;

use RuntimeException;
use Throwable;

final class CircuitBreaker
{
    private CircuitBreakerState $state = CircuitBreakerState::Closed;

    private int $failures = 0;

    private ?int $openedAt = null;

    public function __construct(
        private readonly int $failureThreshold = 3,
        private readonly int $cooldownSeconds = 30,
    ) {}

    /**
     * @template TResult
     *
     * @param callable(): TResult $operation
     *
     * @return TResult
     *
     * @throws Throwable
     */
    public function run(callable $operation): mixed
    {
        if (! $this->canRun()) {
            throw new RuntimeException('Circuit breaker is open.');
        }

        try {
            $result = $operation();
            $this->recordSuccess();

            return $result;
        } catch (Throwable $throwable) {
            $this->recordFailure();

            throw $throwable;
        }
    }

    private function canRun(): bool
    {
        return $this->state() !== CircuitBreakerState::Open;
    }

    public function state() : CircuitBreakerState
    {
        if ($this->state === CircuitBreakerState::Open && $this->cooldownExpired()) {
            $this->state = CircuitBreakerState::HalfOpen;
        }

        return $this->state;
    }

    private function cooldownExpired(): bool
    {
        return $this->openedAt !== null && (time() - $this->openedAt) >= $this->cooldownSeconds;
    }

    private function recordSuccess(): void
    {
        $this->failures = 0;
        $this->openedAt = null;
        $this->state = CircuitBreakerState::Closed;
    }

    private function recordFailure(): void
    {
        $this->failures++;

        if ($this->failures >= $this->failureThreshold) {
            $this->state = CircuitBreakerState::Open;
            $this->openedAt = time();
        }
    }
}
