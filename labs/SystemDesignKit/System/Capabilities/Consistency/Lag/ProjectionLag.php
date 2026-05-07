<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Lag;

/**
 * Projection lag — delay between event and read model update.
 *
 * @experimental V3 labs
 *
 * Models the lag between a write event and when the
 * projection (read model) reflects the change.
 */
final readonly class ProjectionLag
{
    public function __construct(
        public int    $expectedMs,
        public int    $p95Ms,
        public int    $p99Ms,
        public string $projection,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->expectedMs <= 0) {
            $errors[] = 'expected_ms must be positive.';
        }

        if ($this->p95Ms < $this->expectedMs) {
            $errors[] = 'p95_ms must be >= expected_ms.';
        }

        if ($this->p99Ms < $this->p95Ms) {
            $errors[] = 'p99_ms must be >= p95_ms.';
        }

        if ($this->projection === '') {
            $errors[] = 'projection must not be empty.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Whether measured lag is acceptable.
     */
    public function isWithinBudget(int $measuredMs) : bool
    {
        return $measuredMs <= $this->p99Ms;
    }

    /**
     * Whether the projection is considered fresh.
     */
    public function isFresh(int $measuredMs, int $freshnessThresholdMs) : bool
    {
        return $measuredMs <= $freshnessThresholdMs;
    }
}
