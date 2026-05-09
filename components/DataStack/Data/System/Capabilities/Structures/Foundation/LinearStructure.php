<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface LinearStructure extends DataStructure
{
    public function first(mixed $default = null) : mixed;

    public function last(mixed $default = null) : mixed;

    /**
     * @return list<mixed>
     */
    public function toArray() : array;
}
