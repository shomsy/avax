<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidStructureOperation;

/**
 * MatrixDenseStorage — immutable dense (row-major) matrix storage.
 *
 * Every cell is materialized. Suitable for matrices where most cells contain non-default values.
 */
final readonly class MatrixDenseStorage
{
    /** @var list<list<mixed>> */
    private array $rows;

    /**
     * @param list<list<mixed>> $rows
     */
    public function __construct(array $rows)
    {
        if ($rows === []) {
            throw new InvalidStructureOperation(message: 'Matrix must have at least one row.');
        }

        $columns = count(value: $rows[0]);
        foreach ($rows as $i => $row) {
            if (count(value: $row) !== $columns) {
                throw new InvalidStructureOperation(message: "Row {$i} has " . count(value: $row) . " columns, expected {$columns}.");
            }
        }

        $this->rows = $rows;
    }

    /**
     * Create a matrix filled with a default value.
     */
    public static function fill(int $rows, int $columns, mixed $value = 0) : self
    {
        if ($rows <= 0 || $columns <= 0) {
            throw new InvalidStructureOperation(message: 'Matrix dimensions must be positive.');
        }

        return new self(rows: array_fill(0, $rows, array_fill(0, $columns, $value)));
    }

    /**
 * @throws IndexOutOfBounds
 */
public function get(int $row, int $column) : mixed
    {
        if ($row < 0 || $row >= $this->rowCount()) {
            throw new IndexOutOfBounds(message: "Row {$row} out of bounds [0, " . ($this->rowCount() - 1) . ']');
        }
        if ($column < 0 || $column >= $this->columnCount()) {
            throw new IndexOutOfBounds(message: "Column {$column} out of bounds [0, " . ($this->columnCount() - 1) . ']');
        }

        return $this->rows[$row][$column];
    }

    public function rowCount() : int
    {
        return count(value: $this->rows);
    }

    public function columnCount() : int
    {
        return count(value: $this->rows[0]);
    }

    /**
     * Return a new matrix with the given cell updated.
     */
    public function set(int $row, int $column, mixed $value) : self
    {
        if ($row < 0 || $row >= $this->rowCount()) {
            throw new IndexOutOfBounds(message: "Row {$row} out of bounds");
        }
        if ($column < 0 || $column >= $this->columnCount()) {
            throw new IndexOutOfBounds(message: "Column {$column} out of bounds");
        }

        $rows                = $this->rows;
        $rows[$row]          = [...$rows[$row]];
        $rows[$row][$column] = $value;

        return new self(rows: $rows);
    }

    /**
     * @return list<list<mixed>>
     */
    public function toArray() : array
    {
        return $this->rows;
    }

    /**
     * @return list<mixed>
     */
    public function flat() : array
    {
        $flat = [];
        foreach ($this->rows as $row) {
            $flat = [...$flat, ...$row];
        }

        return $flat;
    }
}
