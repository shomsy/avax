<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface MatrixStructure extends DataStructure
{
    public function rows() : int;

    public function columns() : int;

    public function get(int $row, int $column, mixed $default = null) : mixed;

    public function put(int $row, int $column, mixed $value) : static;
}
