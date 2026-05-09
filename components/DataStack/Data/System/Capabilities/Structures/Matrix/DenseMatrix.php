<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\MatrixStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

final readonly class DenseMatrix implements Countable, MatrixStructure
{
    /**
     * @param array<int, array<int, mixed>> $rows
     */
    public function __construct(private array $rows)
    {
        if ($rows === []) {
            return;
        }

        $columns = count(value: $rows[0]);

        foreach ($rows as $row) {
            if (count(value: $row) !== $columns) {
                throw InvalidCapacity::because(reason: 'Dense matrix rows must have the same number of columns.');
            }
        }
    }

    public static function filled(int $rows, int $columns, mixed $value = null) : self
    {
        if ($rows < 0 || $columns < 0) {
            throw InvalidCapacity::because(reason: 'Matrix dimensions cannot be negative.');
        }

        return new self(rows: array_fill(start_index: 0, count: $rows, value: array_fill(start_index: 0, count: $columns, value: $value)));
    }

    #[Override]
    public function get(int $row, int $column, mixed $default = null) : mixed
    {
        if ($row < 0 || $column < 0 || $row >= $this->rows() || $column >= $this->columns()) {
            return $default;
        }

        return $this->rows[$row][$column];
    }

    #[Override]
    public function rows() : int
    {
        return count(value: $this->rows);
    }

    #[Override]
    public function columns() : int
    {
        return $this->rows === [] ? 0 : count(value: $this->rows[0]);
    }

    #[Override]
    public function put(int $row, int $column, mixed $value) : self
    {
        $this->ensureCell(row: $row, column: $column);

        $rows                = $this->rows;
        $rows[$row][$column] = $value;

        return new self(rows: $rows);
    }

    private function ensureCell(int $row, int $column) : void
    {
        if ($row < 0 || $column < 0 || $row >= $this->rows() || $column >= $this->columns()) {
            throw IndexOutOfBounds::at(index: $row > $column ? $row : $column);
        }
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function toArray() : array
    {
        return $this->rows;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->rows === [];
    }

    #[Override]
    public function count() : int
    {
        return max(0, $this->rows() * $this->columns());
    }
}
