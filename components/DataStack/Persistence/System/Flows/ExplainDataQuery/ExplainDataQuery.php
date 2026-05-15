<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\ExplainDataQuery;

use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQueryPlan;
use Avax\Components\DataStack\Persistence\System\Flows\CompileDataQuery\CompileDataQuery;
use function count;
use function in_array;
use function is_array;

/**
 * ExplainDataQuery flow.
 *
 * Returns query plan explanation with optimization suggestions.
 */
final readonly class ExplainDataQuery
{
    public function __construct(private CompileDataQuery $compileDataQuery)
    {
    }

    /**
     * Explains the query and returns an optimized plan with suggestions.
     */
    public function explain(DataQuery $dataQuery): DataQueryPlan
    {
        $dataQueryPlan = $this->compileDataQuery->compile($dataQuery);

        $suggestions = $this->analyzeQuery($dataQuery);

        if ($suggestions !== []) {
            return $dataQueryPlan->withSuggestions($suggestions);
        }

        return $dataQueryPlan;
    }

    /**
     * Analyzes the query and provides optimization suggestions.
     *
     * @return array<string>
     */
    private function analyzeQuery(DataQuery $dataQuery): array
    {
        $suggestions = [];

        // Check for missing WHERE clause with LIMIT
        if ($dataQuery->conditions === [] && $dataQuery->limit === null) {
            $suggestions[] = 'Consider adding a WHERE clause or LIMIT to avoid full table scans';
        }

        // Check for SELECT *
        if ($dataQuery->select === ['*']) {
            $suggestions[] = 'Avoid SELECT * - specify only needed columns to reduce I/O';
        }

        // Check for missing ORDER BY with LIMIT
        if ($dataQuery->limit !== null && $dataQuery->orderBy === []) {
            $suggestions[] = 'Consider adding ORDER BY with LIMIT for deterministic results';
        }

        // Check for large OFFSET
        if ($dataQuery->offset !== null && $dataQuery->offset > 1000) {
            $suggestions[] = 'Large OFFSET detected - consider using keyset pagination instead';
        }

        // Check for multiple JOINs
        if (count($dataQuery->joins) > 3) {
            $suggestions[] = 'Multiple JOINs detected - consider denormalizing or using cached views';
        }

        // Check for missing indexes on WHERE conditions
        if ($dataQuery->conditions !== []) {
            $indexedFields = $this->extractIndexedFields();
            foreach ($dataQuery->conditions as $condition) {
                if (! is_array($condition)) {
                    continue;
                }

                if (! isset($condition['field'])) {
                    continue;
                }

                if (in_array($condition['field'], $indexedFields, true)) {
                    continue;
                }

                $suggestions[] = sprintf('Consider adding an index on column "%s"', $condition['field']);
            }
        }

        // Estimate cost based on complexity
        $estimatedCost = $this->estimateCost($dataQuery);
        if ($estimatedCost > 100.0) {
            $suggestions[] = 'High estimated query cost - consider query restructuring';
        }

        return $suggestions;
    }

    /**
     * Extracts fields that are likely indexed from the plan.
     *
     * @return array<string>
     */
    private function extractIndexedFields(): array
    {
        // In a real implementation, this would query the database metadata
        // For now, we assume primary key and common fields are indexed
        return ['id', 'created_at', 'updated_at'];
    }

    /**
     * Estimates the query cost.
     */
    private function estimateCost(DataQuery $dataQuery): float
    {
        $cost = 1.0;

        // Base cost for table scan
        $cost += 10.0;

        // JOINs are expensive
        $cost += count($dataQuery->joins) * 20.0;

        // WHERE clauses reduce cost if indexed
        $cost -= count($dataQuery->conditions) * 2.0;

        // LIMIT reduces cost
        if ($dataQuery->limit !== null) {
            $cost -= 5.0;
        }

        // ORDER BY adds cost
        $cost += count($dataQuery->orderBy) * 5.0;

        // OFFSET adds cost
        if ($dataQuery->offset !== null) {
            $cost += $dataQuery->offset * 0.01;
        }

        return max(1.0, $cost);
    }
}
