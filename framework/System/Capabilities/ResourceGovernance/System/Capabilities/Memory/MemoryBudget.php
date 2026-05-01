<?php

declare(strict_types=1);

namespace Avax\Components\ResourceGovernor\System\Capabilities\Memory;

final class MemoryBudget
{
    public function __construct(
        public int $workerLimit,
        public int $requestLimit,
    ) {}

    public static function fromString(string $worker, string $request = '32M') : self
    {
        return new self(
            workerLimit : self::parseSize($worker),
            requestLimit: self::parseSize($request),
        );
    }

    private static function parseSize(string $size) : int
    {
        $unit  = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        return match ($unit) {
            'K'     => $value * 1024,
            'M'     => $value * 1024 * 1024,
            'G'     => $value * 1024 * 1024 * 1024,
            default => (int) $size,
        };
    }
}

final readonly class MemorySnapshot
{
    public function __construct(
        public int $requestNumber,
        public int $memoryUsed,
        public int $workerMemory,
    ) {}

    /**
     * @return array{request_number: int, memory_used_mb: float, worker_memory_mb: float}
     */
    public function toArray() : array
    {
        return [
            'request_number'   => $this->requestNumber,
            'memory_used_mb' => $this->memoryUsed / 1024 / 1024,
            'worker_memory_mb' => $this->workerMemory / 1024 / 1024,
        ];
    }
}
