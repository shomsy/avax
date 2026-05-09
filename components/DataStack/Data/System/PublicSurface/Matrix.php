<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix\DenseMatrix;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix\SparseMatrix;

final class Matrix
{
    private function __construct() {}

    /**
     * @param list<list<mixed>> $rows
     */
    public static function dense(array $rows) : DenseMatrix
    {
        return new DenseMatrix(rows: $rows);
    }

    public static function filled(int $rows, int $columns, mixed $value = null) : DenseMatrix
    {
        return DenseMatrix::filled(rows: $rows, columns: $columns, value: $value);
    }

    public static function sparse(int $rows, int $columns) : SparseMatrix
    {
        return new SparseMatrix(rows: $rows, columns: $columns);
    }
}
