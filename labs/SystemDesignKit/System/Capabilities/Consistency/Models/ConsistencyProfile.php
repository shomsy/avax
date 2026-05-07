<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Models;

/**
 * Consistency profile for a single data path.
 *
 * @experimental V3 labs
 *
 * Combines a consistency model with its latency overhead
 * and stale-read risk assessment.
 */
final readonly class ConsistencyProfile
{
    public function __construct(
        public string           $path,
        public ConsistencyModel $model,
        public int              $stalenessToleranceMs,
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

        if ($this->stalenessToleranceMs < 0) {
            $errors[] = 'staleness_tolerance_ms must be non-negative.';
        }

        if ($this->model === ConsistencyModel::Strong && $this->stalenessToleranceMs > 0) {
            $errors[] = 'strong consistency implies zero staleness tolerance.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Estimated latency overhead for this consistency model.
     */
    public function estimatedLatencyOverheadMs() : int
    {
        return $this->model->estimatedOverheadMs();
    }

    /**
     * Whether the configured staleness tolerance is compatible with the model.
     */
    public function isStalenessCompatible() : bool
    {
        if ($this->model === ConsistencyModel::Strong) {
            return $this->stalenessToleranceMs === 0;
        }

        return true;
    }
}
