<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

use Traversable;

interface TreeStructure extends DataStructure
{
    public function rootValue(mixed $default = null) : mixed;

    /**
     * @return Traversable<mixed>
     */
    public function traverseDepthFirst() : Traversable;

    /**
     * @return Traversable<mixed>
     */
    public function traverseBreadthFirst() : Traversable;
}
