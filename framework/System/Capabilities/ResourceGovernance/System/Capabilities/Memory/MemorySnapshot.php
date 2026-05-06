<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ResourceGovernance\System\Capabilities\Memory;

final readonly class MemorySnapshot
{
    public function __construct(
        public int $requestNumber,
        public int $memoryUsed,
        public int $workerMemory,
    ) {
    }

    /**
     * @return array{request_number: int, memory_used_mb: float, worker_memory_mb: float}
     */
    public function toArray(): array
    {
        return [
            'request_number' => $this->requestNumber,
            'memory_used_mb' => $this->memoryUsed / 1024 / 1024,
            'worker_memory_mb' => $this->workerMemory / 1024 / 1024,
        ];
    }
}
