<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface AssociativeStructure extends DataStructure
{
    public function has(int|string $key) : bool;

    public function get(int|string $key, mixed $default = null) : mixed;

    /**
     * @return list<array-key>
     */
    public function keys() : array;
}
