<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Transactions;

use PDOException;
use Throwable;

final class RetryPolicy
{
    public function __construct(
        public readonly int   $maxAttempts = 3,
        public readonly int   $baseDelayMs = 100,
        public readonly int   $maxDelayMs = 5000,
        public readonly float $multiplier = 2.0,
        public readonly array $retryOn = []
    ) {}

    public static function forDeadlocks(int $maxAttempts = 5) : self
    {
        return new self(
            maxAttempts: $maxAttempts,
            baseDelayMs: 100,
            maxDelayMs : 5000,
            multiplier : 2.0,
            retryOn    : [PDOException::class]
        );
    }

    public static function forNetworkErrors(int $maxAttempts = 3) : self
    {
        return new self(
            maxAttempts: $maxAttempts,
            baseDelayMs: 500,
            maxDelayMs : 10000,
            multiplier : 1.5,
            retryOn    : [PDOException::class]
        );
    }

    public function shouldRetry(Throwable $e, int $attempt) : bool
    {
        if ($attempt >= $this->maxAttempts) {
            return false;
        }

        foreach ($this->retryOn as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                return true;
            }
        }

        $message = $e->getMessage();

        if (stripos(haystack: $message, needle: 'deadlock') !== false) {
            return true;
        }

        if (stripos(haystack: $message, needle: 'connection refused') !== false) {
            return true;
        }

        if (stripos(haystack: $message, needle: 'timeout') !== false) {
            return true;
        }

        if (stripos(haystack: $message, needle: 'too many connections') !== false) {
            return true;
        }

        return false;
    }

    public function getDelayMs(int $attempt) : int
    {
        $delay = (int) ($this->baseDelayMs * pow(num: $this->multiplier, exponent: $attempt));

        return min($delay, $this->maxDelayMs);
    }
}
