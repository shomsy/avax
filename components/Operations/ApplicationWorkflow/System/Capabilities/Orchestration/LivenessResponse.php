<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration;

final readonly class LivenessResponse
{
    public function __construct(
        public string $status,
    ) {
    }

    public function toArray(): array
    {
        return ['status' => $this->status];
    }
}
