<?php

declare(strict_types=1);

namespace Avax\Components\ResourceGovernor\System\Capabilities\Memory;

final readonly class MemorySnapshot
{
    public function __construct(
        public int $requestNumber,
        public int $memoryUsed,
        public int $workerMemory,
    ) {}
}
