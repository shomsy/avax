<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

final readonly class ChooseReadProjectionResult
{
    public function __construct(
        public array $columns,
        public bool  $aggregated,
        public bool  $coverable
    ) {}

    public static function full() : self
    {
        return new self(['*'], false, false);
    }

    public static function minimal(array $columns, array $availableCovering) : self
    {
        $isCoverable = count(array_diff($columns, $availableCovering)) === 0;

        return new self($columns, false, $isCoverable);
    }

    public static function aggregate() : self
    {
        return new self([], true, true);
    }

    public function estimateReduction(int $totalColumns) : float
    {
        if (empty($this->columns) || $this->columns[0] === '*') {
            return 1.0;
        }
        if ($this->aggregated) {
            return 0.1;
        }

        return count($this->columns) / max(1, $totalColumns);
    }

    public function toMetadata() : array
    {
        return [
            'columns'    => $this->columns,
            'aggregated' => $this->aggregated,
            'coverable'  => $this->coverable,
        ];
    }
}

final readonly class ChooseReadProjection
{
    public function describeResponsibility() : string
    {
        return 'chooses a read projection to minimize data transfer for queries.';
    }

    public function choose(array $queryContext) : ChooseReadProjectionResult
    {
        $requestedColumns  = $queryContext['columns'] ?? ['*'];
        $isAggregated      = $queryContext['is_aggregated'] ?? false;
        $availableCovering = $queryContext['covering_columns'] ?? [];

        if ($requestedColumns[0] === '*') {
            return ChooseReadProjectionResult::full();
        }

        if ($isAggregated) {
            return ChooseReadProjectionResult::aggregate();
        }

        return ChooseReadProjectionResult::minimal($requestedColumns, $availableCovering);
    }
}