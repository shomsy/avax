<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\Health;

final readonly class HealthCheckResult
{
    public function __construct(
        public string $status,
        public float $latencyMs = 0.0,
        public ?string $error = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'latency_ms' => $this->latencyMs,
            'error' => $this->error,
        ];
    }
}
