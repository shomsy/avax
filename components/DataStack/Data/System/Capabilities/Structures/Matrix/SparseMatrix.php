<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\MatrixStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

final readonly class SparseMatrix implements Countable, MatrixStructure
{
    /**
     * @param array<string, mixed> $cells
     */
    public function __construct(private int $rows, private int $columns, private array $cells = [])
    {
        if ($rows < 0 || $columns < 0) {
            throw InvalidCapacity::because(reason: 'Matrix dimensions cannot be negative.');
        }
    }

    #[Override]
    public function rows() : int
    {
        return $this->rows;
    }

    #[Override]
    public function columns() : int
    {
        return $this->columns;
    }

    #[Override]
    public function get(int $row, int $column, mixed $default = null) : mixed
    {
        $this->ensureCell(row: $row, column: $column);

        return $this->cells[$this->key(row: $row, column: $column)] ?? $default;
    }

    private function ensureCell(int $row, int $column) : void
    {
        if ($row < 0 || $column < 0 || $row >= $this->rows || $column >= $this->columns) {
            throw IndexOutOfBounds::at(index: $row > $column ? $row : $column);
        }
    }

    private function key(int $row, int $column) : string
    {
        return $row . ':' . $column;
    }

    #[Override]
    public function put(int $row, int $column, mixed $value) : self
    {
        $this->ensureCell(row: $row, column: $column);

        $cells = $this->cells;
        $key   = $this->key(row: $row, column: $column);

        if ($value === null) {
            unset($cells[$key]);
        } else {
            $cells[$key] = $value;
        }

        return new self(rows: $this->rows, columns: $this->columns, cells: $cells);
    }

    /**
     * @return array<string, mixed>
     */
    public function storedCells() : array
    {
        return $this->cells;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->rows === 0 || $this->columns === 0;
    }

    #[Override]
    public function count() : int
    {
        return $this->rows * $this->columns;
    }
}
