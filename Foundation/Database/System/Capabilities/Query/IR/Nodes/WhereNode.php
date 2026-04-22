<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\IR;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

enum ComparisonOperator: string
{
    case EQUAL                 = '=';
    case NOT_EQUAL             = '!=';
    case LESS_THAN             = '<';
    case LESS_THAN_OR_EQUAL    = '<=';
    case GREATER_THAN          = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LIKE                  = 'LIKE';
    case NOT_LIKE              = 'NOT LIKE';
    case ILIKE                 = 'ILIKE';
    case IN                    = 'IN';
    case NOT_IN                = 'NOT IN';
    case BETWEEN               = 'BETWEEN';
    case IS_NULL               = 'IS NULL';
    case IS_NOT_NULL           = 'IS NOT NULL';
}

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
        $boolean  = $this->boolean;

        if ($this->operator === ComparisonOperator::IS_NULL || $this->operator === ComparisonOperator::IS_NOT_NULL) {
            return "{$boolean} {$column} {$operator}";
        }

        if ($this->operator === ComparisonOperator::IN || $this->operator === ComparisonOperator::NOT_IN) {
            $values       = is_array(value: $this->value) ? $this->value : [$this->value];
            $placeholders = implode(separator: ', ', array_fill(start_index: 0, count: count($values), value: '?'));

            return "{$boolean} {$column} {$operator} ({$placeholders})";
        }

        if ($this->operator === ComparisonOperator::BETWEEN) {
            return "{$boolean} {$column} BETWEEN ? AND ?";
        }

        return "{$boolean} {$column} {$operator} ?";
    }
}
