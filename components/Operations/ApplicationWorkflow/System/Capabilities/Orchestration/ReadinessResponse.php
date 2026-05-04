<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration\Kubernetes\GracefulStop;

final readonly class ReadinessResponse
{
    public function __construct(
        public string $status,
        /** @var array<string, ReadinessCheck> */
        public array  $checks = []
    )
    {
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checks' => array_map(
                static fn(ReadinessCheck $readinessCheck): array => $readinessCheck->toArray(),
                $this->checks,
            ),
        ];
    }
}
