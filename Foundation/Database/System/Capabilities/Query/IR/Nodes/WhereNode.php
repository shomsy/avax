<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class WhereNode
{
    public function __construct(
        public readonly string             $column,
        public readonly ComparisonOperator $operator,
        public readonly mixed              $value,
        public readonly string             $boolean = 'AND',
        public readonly ?string            $connector = null
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $column   = $grammar->wrap(value: $this->column);
        $operator = $this->operator->value;
        $boolean = strtoupper(string: $this->boolean);

        if ($this->operator === ComparisonOperator::IS_NULL || $this->operator === ComparisonOperator::IS_NOT_NULL) {
            return "{$boolean} {$column} {$operator}";
        }

        if ($this->operator === ComparisonOperator::IN || $this->operator === ComparisonOperator::NOT_IN) {
            $values       = is_array(value: $this->value) ? $this->value : [$this->value];
            $placeholders = implode(separator: ', ', array: array_fill(start_index: 0, count: count(value: $values), value: '?'));

            return "{$boolean} {$column} {$operator} ({$placeholders})";
        }

        if ($this->operator === ComparisonOperator::BETWEEN) {
            return "{$boolean} {$column} BETWEEN ? AND ?";
        }

        return "{$boolean} {$column} {$operator} ?";
    }
}
