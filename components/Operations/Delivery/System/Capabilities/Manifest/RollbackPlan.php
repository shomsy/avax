<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Manifest;

class RollbackPlan
{
    public function __construct(
        private readonly ReleaseManifest $releaseManifest,
    )
    {
    }

    /**
     * @return array{rollback_possible: bool, steps: array<int, array{name: string, action: callable}>}
     */
    public function toArray(): array
    {
        return [
            'rollback_possible' => $this->canRollback(),
            'steps' => array_reverse($this->releaseManifest->getSteps()),
        ];
    }

    public function canRollback(): bool
    {
        return $this->releaseManifest->getSteps() !== [];
    }
}
