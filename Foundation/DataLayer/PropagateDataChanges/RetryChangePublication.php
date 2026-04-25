<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use InvalidArgumentException;

enum RetryStrategy: string
{
    case IMMEDIATE   = 'immediate';
    case LINEAR      = 'linear';
    case EXPONENTIAL = 'exponential';
    case FIBONACCI   = 'fibonacci';
}

final readonly class RetryChangePublication
{
    private int           $maxRetries;
    private RetryStrategy $strategy;

    public function __construct(
        int|null $maxRetries = null,
        RetryStrategy $strategy = RetryStrategy::EXPONENTIAL
    )
    {
        $maxRetries ??= 3;
        if ($maxRetries < 0) {
            throw new InvalidArgumentException(message: 'Max retries cannot be negative.');
        }
        $this->maxRetries = $maxRetries;
        $this->strategy   = $strategy;
    }

    public function describeResponsibility() : string
    {
        return 'retries failed change publications with configurable backoff strategy.';
    }

    public function shouldRetry(int $attempt) : bool
    {
        return $attempt < $this->maxRetries;
    }

    public function calculateDelay(int $attempt) : int
    {
        $baseDelay = 1000;

        return match ($this->strategy) {
            RetryStrategy::IMMEDIATE   => 0,
            RetryStrategy::LINEAR      => $attempt * $baseDelay,
            RetryStrategy::EXPONENTIAL => $baseDelay * (2 ** $attempt),
            RetryStrategy::FIBONACCI => $baseDelay * $this->fibonacci(n: $attempt + 1),
        };
    }

    private function fibonacci(int $n) : int
    {
        if ($n <= 1) {
            return $n;
        }

        return $this->fibonacci(n: $n - 1) + $this->fibonacci(n: $n - 2);
    }

    public function toMetadata() : array
    {
        return [
            'max_retries' => $this->maxRetries,
            'strategy'    => $this->strategy->value,
        ];
    }
}