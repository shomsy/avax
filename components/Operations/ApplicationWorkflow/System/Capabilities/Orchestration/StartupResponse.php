<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration\Kubernetes\GracefulStop;

final readonly class StartupResponse
{
    public function __construct(
        public string $status,
    )
    {
    }

    public function toArray(): array
    {
        return ['status' => $this->status];
    }
}
