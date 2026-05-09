<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Countable;

interface StructureStorage extends Countable
{
    public function isEmpty() : bool;
}
