<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface SetStructure extends DataStructure
{
    public function contains(mixed $value) : bool;

    public function add(mixed $value) : static;

    public function remove(mixed $value) : static;

    /**
     * @return list<mixed>
     */
    public function toArray() : array;
}
