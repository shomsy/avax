<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent;

/**
 * Compiled query plan with execution strategy.
 *
 * Represents the compiled version of a DataQuery, ready for execution.
 */
final readonly class DataQueryPlan
{
    /**
     * @param  array<string, mixed>  $bindings
     * @param  array<string>  $indexesUsed
     * @param  array<string>  $suggestions
     */
    public function __construct(
        public ?string $sql = null,
        public array $bindings = [],
        public ?float $estimatedCost = null,
        public array $indexesUsed = [],
        private array $suggestions = [],
    ) {
    }

    /**
     * Returns a human-readable explanation of the query plan.
     */
    public function explain(): string
    {
        $parts = [];

        if ($this->sql !== null) {
            $parts[] = 'SQL: '.$this->sql;
        }

        if ($this->bindings !== []) {
            $parts[] = 'Bindings: '.json_encode($this->bindings, JSON_THROW_ON_ERROR);
        }

        if ($this->estimatedCost !== null) {
            $parts[] = 'Estimated Cost: '.$this->estimatedCost;
        }

        if ($this->indexesUsed !== []) {
            $parts[] = 'Indexes Used: '.implode(', ', $this->indexesUsed);
        }

        if ($this->suggestions !== []) {
            $parts[] = 'Suggestions: '.implode('; ', $this->suggestions);
        }

        return implode("\n", $parts);
    }

    /**
     * Checks if the query plan is optimized.
     */
    public function isOptimized(): bool
    {
        return $this->suggestions === [];
    }

    /**
     * Returns optimization suggestions.
     *
     * @return array<string>
     */
    public function suggestions(): array
    {
        return $this->suggestions;
    }

    /**
     * Returns a new instance with added suggestions.
     *
     * @param  array<string>  $suggestions
     */
    public function withSuggestions(array $suggestions): DataQueryPlan
    {
        return new DataQueryPlan(
            sql          : $this->sql,
            bindings     : $this->bindings,
            estimatedCost: $this->estimatedCost,
            indexesUsed  : $this->indexesUsed,
            suggestions  : [...$this->suggestions, ...$suggestions],
        );
    }

    /**
     * Returns a new instance with the specified SQL.
     */
    public function withSql(string $sql): DataQueryPlan
    {
        return new DataQueryPlan(
            sql          : $sql,
            bindings     : $this->bindings,
            estimatedCost: $this->estimatedCost,
            indexesUsed  : $this->indexesUsed,
            suggestions  : $this->suggestions,
        );
    }

    /**
     * Returns a new instance with the specified bindings.
     *
     * @param  array<string, mixed>  $bindings
     */
    public function withBindings(array $bindings): DataQueryPlan
    {
        return new DataQueryPlan(
            sql          : $this->sql,
            bindings     : $bindings,
            estimatedCost: $this->estimatedCost,
            indexesUsed  : $this->indexesUsed,
            suggestions  : $this->suggestions,
        );
    }

    /**
     * Returns a new instance with the specified estimated cost.
     */
    public function withEstimatedCost(float $estimatedCost): DataQueryPlan
    {
        return new DataQueryPlan(
            sql          : $this->sql,
            bindings     : $this->bindings,
            estimatedCost: $estimatedCost,
            indexesUsed  : $this->indexesUsed,
            suggestions  : $this->suggestions,
        );
    }

    /**
     * Returns a new instance with the specified indexes used.
     *
     * @param  array<string>  $indexesUsed
     */
    public function withIndexesUsed(array $indexesUsed): DataQueryPlan
    {
        return new DataQueryPlan(
            sql          : $this->sql,
            bindings     : $this->bindings,
            estimatedCost: $this->estimatedCost,
            indexesUsed  : $indexesUsed,
            suggestions  : $this->suggestions,
        );
    }
}
