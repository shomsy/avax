<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\Health;

final readonly class HealthReport
{
    /**
     * @param  array<string, HealthCheckResult>  $checks
     */
    public function __construct(
        public string $status,
        public array $checks,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checks' => array_map(
                callback: static fn (HealthCheckResult $result): array => $result->toArray(),
                array   : $this->checks,
            ),
        ];
    }
}
