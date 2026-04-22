<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\WindowFunctions;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

enum WindowFunction: string
{
    case ROW_NUMBER  = 'ROW_NUMBER()';
    case RANK        = 'RANK()';
    case DENSE_RANK  = 'DENSE_RANK()';
    case NTILE       = 'NTILE';
    case LAG         = 'LAG';
    case LEAD        = 'LEAD';
    case FIRST_VALUE = 'FIRST_VALUE';
    case LAST_VALUE  = 'LAST_VALUE';
    case NTH_VALUE   = 'NTH_VALUE';
    case COUNT       = 'COUNT';
    case SUM         = 'SUM';
    case AVG         = 'AVG';
    case MIN         = 'MIN';
    case MAX         = 'MAX';
}

final class WindowBuilder
{
    public function __construct(
        private GrammarInterface $grammar,
        private string           $function,
        private array            $partitionBy = [],
        private string           $orderBy = '',
        private string           $frame = 'ROWS UNBOUNDED PRECEDING'
    ) {}

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
        $sql = $this->function . '(';

        if (! empty($this->partitionBy)) {
            $partition = implode(separator: ', ', array_map(
                callback: fn ($col) => $this->grammar->wrap(value: $col),
                array   : $this->partitionBy
            ));
            $sql       .= 'PARTITION BY ' . $partition;
        }

        if ($this->orderBy !== '') {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }

        $sql .= ') ';

        if ($this->frame !== '') {
            $sql .= $this->frame . ' ';
        }

        $sql .= 'OVER';

        if (! empty($this->partitionBy) || $this->orderBy !== '') {
            $sql   .= ' (';
            $parts = [];

            if (! empty($this->partitionBy)) {
                $partition = implode(separator: ', ', array_map(
                    callback: fn ($col) => $this->grammar->wrap(value: $col),
                    array   : $this->partitionBy
                ));
                $parts[]   = 'PARTITION BY ' . $partition;
            }

            if ($this->orderBy !== '') {
                $parts[] = 'ORDER BY ' . $this->orderBy;
            }

            $parts[] = $this->frame;
            $sql     .= implode(separator: ' ', array: $parts);
            $sql     .= ')';
        }

        if ($alias !== '') {
            $sql .= ' AS ' . $this->grammar->wrap(value: $alias);
        }

        return $sql;
    }
}
