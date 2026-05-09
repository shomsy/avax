<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

use Countable;

interface DataStructure extends Countable
{
    public function isEmpty() : bool;
}
