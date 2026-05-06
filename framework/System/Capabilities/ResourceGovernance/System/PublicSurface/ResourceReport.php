<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ResourceGovernance\System\PublicSurface;

use Stringable;

final readonly class ResourceReport implements Stringable
{
    public function __construct(
        public int $requestCount,
        public int $workerMemory,
        public int $workerLimit,
        public bool $nearLimit,
        public float $trend,
    ) {
    }

    /**
     * @return array{request_count: int, worker_memory_mb: float, worker_limit_mb: float, near_limit: bool,
     *                              trend_mb_per_request: float}
     */
    public function toArray(): array
    {
        return [
            'request_count' => $this->requestCount,
            'worker_memory_mb' => $this->workerMemory / 1024 / 1024,
            'worker_limit_mb' => $this->workerLimit / 1024 / 1024,
            'near_limit' => $this->nearLimit,
            'trend_mb_per_request' => $this->trend / 1024 / 1024,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            'Worker memory: %.1fMB / %.1fMB | Requests: %d | Near limit: %s | Trend: %.2fMB/req',
            $this->workerMemory / 1024 / 1024,
            $this->workerLimit / 1024 / 1024,
            $this->requestCount,
            $this->nearLimit ? 'YES' : 'NO',
            $this->trend / 1024 / 1024,
        );
    }
}
