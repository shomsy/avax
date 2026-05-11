<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class WhereNode
{
    public function __construct(
        public string $column,
        public ComparisonOperator $operator,
        public mixed $value,
        public string $boolean = 'AND',
        public string|null $connector = null,
    ) {
    }

    public function getSql(GrammarInterface $grammar): string
    {
        $column = $grammar->wrap(value: $this->column);
        $operator = $this->operator->value;
        $boolean = strtoupper(string: $this->boolean);

        if ($this->operator === ComparisonOperator::IS_NULL || $this->operator === ComparisonOperator::IS_NOT_NULL) {
            return sprintf('%s %s %s', $boolean, $column, $operator);
        }

        if ($this->operator === ComparisonOperator::IN || $this->operator === ComparisonOperator::NOT_IN) {
            $values = is_array(value: $this->value) ? $this->value : [$this->value];
            $placeholders = implode(separator: ', ', array: array_fill(start_index: 0, count: count(value: $values), value: '?'));

            return sprintf('%s %s %s (%s)', $boolean, $column, $operator, $placeholders);
        }

        if ($this->operator === ComparisonOperator::BETWEEN) {
            return sprintf('%s %s BETWEEN ? AND ?', $boolean, $column);
        }

        return sprintf('%s %s %s ?', $boolean, $column, $operator);
    }
}
