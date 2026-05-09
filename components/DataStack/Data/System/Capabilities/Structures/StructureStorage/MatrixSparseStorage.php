<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidStructureOperation;

/**
 * MatrixSparseStorage — immutable sparse matrix storage using coordinate list (COO) format.
 *
 * Only non-default cells are stored. Suitable for matrices where most cells contain the default value.
 */
final readonly class MatrixSparseStorage
{
    /**
     * @var array<string, mixed> Key is "row,col" string
     */
    private array $cells;

    private int $rowCount;
    private int $columnCount;

    /**
     * @param array<string, mixed> $cells
     */
    public function __construct(
        int          $rows,
        int          $columns,
        array        $cells = [],
        public mixed $default = 0,
    )
    {
        if ($rows <= 0 || $columns <= 0) {
            throw new InvalidStructureOperation(message: 'Matrix dimensions must be positive.');
        }

        $this->rowCount    = $rows;
        $this->columnCount = $columns;
        $this->cells       = $cells;
    }

    /**
     * Create an empty sparse matrix.
     *
     * @phpstan-return self
     */
    public static function empty(int $rows, int $columns, mixed $default = 0) : self
    {
        return new self(rows: $rows, columns: $columns, default: $default);
    }

    /**
     * Return a new matrix with the given cell updated.
     * If the value equals the default, the cell is removed from storage.
     */
    public function set(int $row, int $column, mixed $value) : self
    {
        $cells = $this->cells;
        $key   = $this->key(row: $row, column: $column);

        if ($value === $this->default) {
            unset($cells[$key]);
        } else {
            $cells[$key] = $value;
        }

        return new self(rows: $this->rowCount, columns: $this->columnCount, cells: $cells, default: $this->default);
    }

    private function key(int $row, int $column) : string
    {
        return "{$row},{$column}";
    }

    public function rowCount() : int
    {
        return $this->rowCount;
    }

    public function columnCount() : int
    {
        return $this->columnCount;
    }

    public function sparsity() : float
    {
        $total = $this->rowCount * $this->columnCount;

        return 1.0 - ($this->nonZeroCount() / $total);
    }

    public function nonZeroCount() : int
    {
        return count(value: $this->cells);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return $this->cells;
    }

    /**
     * @return list<list<mixed>>
     */
    public function toDense() : array
    {
        $dense = [];
        for ($r = 0; $r < $this->rowCount; $r++) {
            $row = [];
            for ($c = 0; $c < $this->columnCount; $c++) {
                $row[] = $this->get(row: $r, column: $c);
            }
            $dense[] = $row;
        }

        return $dense;
    }

    public function get(int $row, int $column) : mixed
    {
        return $this->cells[$this->key(row: $row, column: $column)] ?? $this->default;
    }
}
