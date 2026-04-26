<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\WindowFunctions;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class WindowBuilder
{
    public function __construct(
        private GrammarInterface $grammar,
        private string           $function,
        private array|null       $partitionBy = null,
        private string|null      $orderBy = null,
        private string           $frame = 'ROWS UNBOUNDED PRECEDING'
    )
    {
        $this->partitionBy ??= [];
        $this->orderBy     ??= '';
    }

    public static function rowNumber(GrammarInterface $grammar) : self
    {
        return new self(grammar: $grammar, function: 'ROW_NUMBER');
    }

    public static function rank(GrammarInterface $grammar) : self
    {
        return new self(grammar: $grammar, function: 'RANK');
    }

    public static function denseRank(GrammarInterface $grammar) : self
    {
        return new self(grammar: $grammar, function: 'DENSE_RANK');
    }

    public static function lag(GrammarInterface $grammar, string $column) : self
    {
        return new self(grammar: $grammar, function: "LAG({$column})");
    }

    public static function lead(GrammarInterface $grammar, string $column) : self
    {
        return new self(grammar: $grammar, function: "LEAD({$column})");
    }

    public function partitionBy(string ...$columns) : self
    {
        $this->partitionBy = $columns;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        $this->orderBy = "{$column} {$direction}";

        return $this;
    }

    public function frame(string $frame) : self
    {
        $this->frame = $frame;

        return $this;
    }

    public function rowsUnboundedPreceding() : self
    {
        $this->frame = 'ROWS UNBOUNDED PRECEDING';

        return $this;
    }

    public function rowsBetween(int $start, int $end) : self
    {
        $startExpr = $start < 0 ? "{$start} PRECEDING" : "{$start} FOLLOWING";
        $endExpr   = $end < 0 ? "{$end} PRECEDING" : "{$end} FOLLOWING";

        $this->frame = "ROWS BETWEEN {$startExpr} AND {$endExpr}";

        return $this;
    }

    public function getSql(string $alias = '') : string
    {
        $sql   = $this->function . ' OVER';
        $parts = [];

        if (! empty($this->partitionBy) || $this->orderBy !== '' || $this->frame !== '') {
            if (! empty($this->partitionBy)) {
                $partition = implode(separator: ', ', array: array_map(
                    callback: fn ($col) => $this->grammar->wrap(value: $col),
                    array   : $this->partitionBy
                ));
                $parts[]   = 'PARTITION BY ' . $partition;
            }

            if ($this->orderBy !== '') {
                $parts[] = 'ORDER BY ' . $this->orderBy;
            }

            if ($this->frame !== '') {
                $parts[] = $this->frame;
            }
        }

        $sql .= ' (' . implode(separator: ' ', array: $parts) . ')';

        if ($alias !== '') {
            $sql .= ' AS ' . $this->grammar->wrap(value: $alias);
        }

        return $sql;
    }
}
