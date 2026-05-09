<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface MapStructure extends AssociativeStructure
{
    public function put(int|string $key, mixed $value) : static;

    public function remove(int|string $key) : static;

    /**
     * @return list<object>
     */
    public function entries() : array;
}
