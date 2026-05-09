<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Queue as StructureQueue;

final class Queue
{
    private function __construct() {}

    /**
     * @param iterable<mixed> $values
     */
    public static function make(iterable $values = []) : StructureQueue
    {
        return new StructureQueue(items: $values);
    }
}
