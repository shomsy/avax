<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface HeapStructure extends DataStructure
{
    public function peek(mixed $default = null) : mixed;

    public function insert(mixed $value, int|float $priority) : static;

    public function extract() : mixed;
}
