<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Foundation\Values;

use Throwable;

/**
 * Result of a reliability execution.
 */
final readonly class ReliabilityResult
{
    public function __construct(
        public bool $success,
        public mixed $result = null,
        public int $attempts = 1,
        public Throwable|null $lastException = null,
        public bool $fallbackUsed = false,
        public float $elapsedMs = 0.0,
    ) {}

    public static function success(mixed $result, int $attempts = 1, float $elapsedMs = 0.0) : self
    {
        return new self(success: true, result: $result, attempts: $attempts, elapsedMs: $elapsedMs);
    }

    public static function failure(Throwable $exception, int $attempts = 1, bool $fallbackUsed = false, mixed $fallbackResult = null, float $elapsedMs = 0.0) : self
    {
        return new self(
            success: false,
            result: $fallbackResult,
            attempts: $attempts,
            lastException: $exception,
            fallbackUsed: $fallbackUsed,
            elapsedMs: $elapsedMs,
        );
    }
}
