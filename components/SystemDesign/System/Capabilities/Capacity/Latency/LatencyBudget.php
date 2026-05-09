<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Latency;

/**
 * Latency budget model — P50, P95, P99 targets.
 *
 * @experimental V3 labs
 */
final readonly class LatencyBudget
{
    public function __construct(
        public int $p50Ms,
        public int $p95Ms,
        public int $p99Ms,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->p50Ms <= 0) {
            $errors[] = 'p50_ms must be positive.';
        }

        if ($this->p50Ms > $this->p95Ms) {
            $errors[] = "p50 ({$this->p50Ms}ms) must be <= p95 ({$this->p95Ms}ms).";
        }

        if ($this->p95Ms > $this->p99Ms) {
            $errors[] = "p95 ({$this->p95Ms}ms) must be <= p99 ({$this->p99Ms}ms).";
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Check if measured latency is within budget.
     *
     * @return array{within_budget: bool, violations: list<string>}
     */
    public function isWithinBudget(int $measuredP50, int $measuredP95, int $measuredP99) : array
    {
        $violations = [];

        if ($measuredP50 > $this->p50Ms) {
            $violations[] = "p50 measured {$measuredP50}ms exceeds budget {$this->p50Ms}ms.";
        }

        if ($measuredP95 > $this->p95Ms) {
            $violations[] = "p95 measured {$measuredP95}ms exceeds budget {$this->p95Ms}ms.";
        }

        if ($measuredP99 > $this->p99Ms) {
            $violations[] = "p99 measured {$measuredP99}ms exceeds budget {$this->p99Ms}ms.";
        }

        return ['within_budget' => $violations === [], 'violations' => $violations];
    }
}
