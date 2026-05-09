<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface ConcurrentStructure extends DataStructure
{
    public function runtimeBoundary() : string;
}
