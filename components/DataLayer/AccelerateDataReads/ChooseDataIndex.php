<?php

declare(strict_types=1);

namespace components\DataLayer\AccelerateDataReads;

final readonly class ChooseDataIndexResult
{
    public function __construct(
        public array       $indexColumns,
        public string      $type,
        public float       $selectivity,
        public float       $estimatedSpeedup,
        public string|null $indexName
    ) {}

    public static function none() : self
    {
        return new self(indexColumns: [], type: 'none', selectivity: 1.0, estimatedSpeedup: 1.0, indexName: null);
    }

    public function isUseful() : bool
    {
        return $this->estimatedSpeedup > 1.0;
    }
}

final readonly class ChooseDataIndex
{
    public function __construct(
        private float $indexSelectivityThreshold = 0.01
    ) {}

    public function describeResponsibility() : string
    {
        return 'chooses an appropriate data index based on query predicates and selectivity.';
    }

    public function choose(array $queryContext) : ChooseDataIndexResult
    {
        $whereColumns = $queryContext['where_columns'] ?? [];
        $orderColumns = $queryContext['order_columns'] ?? [];
        $selectivity  = $queryContext['selectivity'] ?? 1.0;

        if (empty($whereColumns) && empty($orderColumns)) {
            return ChooseDataIndexResult::none();
        }

        if ($selectivity > $this->indexSelectivityThreshold) {
            return ChooseDataIndexResult::none();
        }

        $indexColumns     = array_merge($whereColumns, $orderColumns);
        $type             = ! empty($whereColumns) ? 'eq' : 'range';
        $estimatedSpeedup = $this->calculateSpeedup(selectivity: $selectivity);

        return new ChooseDataIndexResult(
            indexColumns    : $indexColumns,
            type            : $type,
            selectivity     : $selectivity,
            estimatedSpeedup: $estimatedSpeedup,
            indexName       : $queryContext['preferred_index'] ?? null
        );
    }

    private function calculateSpeedup(float $selectivity) : float
    {
        if ($selectivity <= 0.001) {
            return 100.0;
        }
        if ($selectivity <= 0.01) {
            return 50.0;
        }
        if ($selectivity <= 0.1) {
            return 10.0;
        }

        return 5.0;
    }

    public function toMetadata() : array
    {
        return ['index_selectivity_threshold' => $this->indexSelectivityThreshold];
    }
}