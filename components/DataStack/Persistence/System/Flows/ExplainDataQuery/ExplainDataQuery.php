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
final class ExplainDataQuery
{
    private CompileDataQuery $compiler;

    public function __construct(CompileDataQuery|null $compiler = null)
    {
        $this->compiler = $compiler ?? new CompileDataQuery();
    }

    /**
     * Explains the query and returns an optimized plan with suggestions.
     */
    public function explain(DataQuery $query) : DataQueryPlan
    {
        $plan = $this->compiler->compile($query);

        $suggestions = $this->analyzeQuery($query, $plan);

        if ($suggestions !== []) {
            $plan = $plan->withSuggestions($suggestions);
        }

        return $plan;
    }

    /**
     * Analyzes the query and provides optimization suggestions.
     *
     * @return array<string>
     */
    private function analyzeQuery(DataQuery $query, DataQueryPlan $plan) : array
    {
        $suggestions = [];

        // Check for missing WHERE clause with LIMIT
        if ($query->conditions === [] && $query->limit === null) {
            $suggestions[] = 'Consider adding a WHERE clause or LIMIT to avoid full table scans';
        }

        // Check for SELECT *
        if ($query->select === ['*']) {
            $suggestions[] = 'Avoid SELECT * - specify only needed columns to reduce I/O';
        }

        // Check for missing ORDER BY with LIMIT
        if ($query->limit !== null && $query->orderBy === []) {
            $suggestions[] = 'Consider adding ORDER BY with LIMIT for deterministic results';
        }

        // Check for large OFFSET
        if ($query->offset !== null && $query->offset > 1000) {
            $suggestions[] = 'Large OFFSET detected - consider using keyset pagination instead';
        }

        // Check for multiple JOINs
        if (count($query->joins) > 3) {
            $suggestions[] = 'Multiple JOINs detected - consider denormalizing or using cached views';
        }

        // Check for missing indexes on WHERE conditions
        if ($query->conditions !== []) {
            $indexedFields = $this->extractIndexedFields($plan);
            foreach ($query->conditions as $condition) {
                if (is_array($condition) && isset($condition['field'])) {
                    if (! in_array($condition['field'], $indexedFields, true)) {
                        $suggestions[] = "Consider adding an index on column \"{$condition['field']}\"";
                    }
                }
            }
        }

        // Estimate cost based on complexity
        $estimatedCost = $this->estimateCost($query);
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
    private function extractIndexedFields(DataQueryPlan $plan) : array
    {
        // In a real implementation, this would query the database metadata
        // For now, we assume primary key and common fields are indexed
        return ['id', 'created_at', 'updated_at'];
    }

    /**
     * Estimates the query cost.
     */
    private function estimateCost(DataQuery $query) : float
    {
        $cost = 1.0;

        // Base cost for table scan
        $cost += 10.0;

        // JOINs are expensive
        $cost += count($query->joins) * 20.0;

        // WHERE clauses reduce cost if indexed
        $cost -= count($query->conditions) * 2.0;

        // LIMIT reduces cost
        if ($query->limit !== null) {
            $cost -= 5.0;
        }

        // ORDER BY adds cost
        $cost += count($query->orderBy) * 5.0;

        // OFFSET adds cost
        if ($query->offset !== null) {
            $cost += $query->offset * 0.01;
        }

        return max(1.0, $cost);
    }
}
