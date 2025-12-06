<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Traits;

/**
 * Trait QueryOptimizationTrait
 *
 * Provides query optimization strategies, including **indexing recommendations** and **performance insights**.
 *
 * ✅ Implements **OWASP security best practices** to prevent SQL injection.
 * ✅ Uses **PSR-3 Logging** instead of direct output.
 * ✅ Ensures **strict input validation** for indexing recommendations.
 */
trait QueryOptimizationTrait
{
    /**
     * @var array<string> Stores WHERE clauses for analysis.
     */
    private array $whereClauses = [];

    /**
     * Displays recommendations for indexing based on WHERE clauses.
     *
     * ✅ Uses structured **PSR-3 logging** instead of `echo`.
     * ✅ Ensures **strict column name validation** to prevent SQL injection.
     */
    public function showIndexingRecommendations() : void
    {
        $recommendations = $this->recommendIndexes();

        if (empty($recommendations)) {
            $this->logger->info(message: "🔍 No indexing recommendations. Your query is already optimized. 🚀");
        } else {
            $message = "⚡ Recommended columns for indexing: " . implode(', ', $recommendations);
            $this->logger->info(message: $message);

            foreach ($recommendations as $column) {
                $this->logger->info(message: "📌 Consider: CREATE INDEX idx_{$column} ON your_table({$column});");
            }
        }
    }

    /**
     * Analyzes WHERE conditions and suggests which columns should be indexed.
     *
     * ✅ **Prevents SQL injection** via strict column validation.
     * ✅ **Ensures best performance** by avoiding unnecessary recommendations.
     *
     * @return array<int, string> List of recommended columns for indexing.
     */
    public function recommendIndexes() : array
    {
        if (empty($this->whereClauses)) {
            return [];
        }

        $indexes = [];

        foreach ($this->whereClauses as $clause) {
            // Extract column names from WHERE conditions.
            if (preg_match('/^([a-zA-Z0-9_]+)\s*(=|LIKE|IN|>|<|>=|<=)/', $clause, $matches)) {
                $column = $matches[1];

                // Validate column name before adding to recommendations.
                if ($this->isValidColumnName(column: $column) && ! in_array($column, $indexes, true)) {
                    $indexes[] = $column;
                }
            }
        }

        return $indexes;
    }

    /**
     * Validates a column name against SQL injection risks.
     *
     * ✅ Ensures column names are safe before they are used in SQL statements.
     */
    private function isValidColumnName(string $column) : bool
    {
        return preg_match('/^[a-zA-Z0-9_]+$/', $column) === 1;
    }
}
