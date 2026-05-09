<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Consistency\Lag;

/**
 * Replication lag — delay between primary and replica.
 *
 * @experimental V3 labs
 *
 * Models the lag between a write on the primary and
 * its visibility on a replica.
 */
final readonly class ReplicationLag
{
    public function __construct(
        public int    $expectedMs,
        public int    $p95Ms,
        public int    $p99Ms,
        public int    $replicaCount,
        public string $topology,
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

        if ($this->replicaCount < 0) {
            $errors[] = 'replica_count must be non-negative.';
        }

        if ($this->topology === '') {
            $errors[] = 'topology must not be empty.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Worst-case staleness across all replicas.
     */
    public function worstCaseStalenessMs() : int
    {
        return $this->p99Ms;
    }

    /**
     * Whether a read from a random replica is within staleness budget.
     */
    public function isWithinStalenessBudget(int $budgetMs) : bool
    {
        return $this->p99Ms <= $budgetMs;
    }
}
