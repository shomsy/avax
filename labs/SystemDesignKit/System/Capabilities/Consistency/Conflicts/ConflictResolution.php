<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Conflicts;

/**
 * Conflict resolution profile for a data path.
 *
 * @experimental V3 labs
 *
 * Models the conflict resolution strategy, expected conflict rate,
 * and data loss risk for concurrent writes.
 */
final readonly class ConflictResolution
{
    public function __construct(
        public string           $path,
        public ConflictStrategy $strategy,
        public float            $estimatedConflictRate,
        public bool             $dataLossAccepted,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->path === '') {
            $errors[] = 'path must not be empty.';
        }

        if ($this->estimatedConflictRate < 0 || $this->estimatedConflictRate > 1) {
            $errors[] = 'estimated_conflict_rate must be between 0 and 1.';
        }

        if ($this->strategy->canLoseData() && ! $this->dataLossAccepted) {
            $errors[] = "strategy '{$this->strategy->value}' can lose data but data_loss_accepted is false.";
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Expected number of conflicts per 1000 writes.
     */
    public function conflictsPerThousandWrites() : float
    {
        return $this->estimatedConflictRate * 1000;
    }
}
