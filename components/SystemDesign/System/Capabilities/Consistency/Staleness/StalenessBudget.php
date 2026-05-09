<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Consistency\Staleness;

/**
 * Staleness budget — how much data lag is acceptable.
 *
 * @experimental V3 labs
 *
 * Models the maximum acceptable staleness between
 * a write and its visibility to readers.
 */
final readonly class StalenessBudget
{
    public function __construct(
        public int    $toleranceMs,
        public string $path,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->toleranceMs < 0) {
            $errors[] = 'tolerance_ms must be non-negative.';
        }

        if ($this->path === '') {
            $errors[] = 'path must not be empty.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Whether measured staleness is within budget.
     */
    public function isWithinBudget(int $measuredStalenessMs) : bool
    {
        return $measuredStalenessMs <= $this->toleranceMs;
    }

    /**
     * Violation severity if budget is exceeded.
     */
    public function violationSeverity(int $measuredStalenessMs) : string
    {
        if ($measuredStalenessMs <= $this->toleranceMs) {
            return 'none';
        }

        $ratio = $measuredStalenessMs / max($this->toleranceMs, 1);

        return match (true) {
            $ratio > 10 => 'critical',
            $ratio > 5  => 'high',
            $ratio > 2  => 'medium',
            default     => 'low',
        };
    }

    /**
     * Human-readable budget description.
     */
    public function humanReadable() : string
    {
        if ($this->toleranceMs < 1000) {
            return "{$this->toleranceMs}ms for {$this->path}";
        }

        $seconds = round($this->toleranceMs / 1000, 1);

        return "{$seconds}s for {$this->path}";
    }
}
