<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\CheckObservabilityHealth;

use Avax\Components\Operations\Observability\System\Capabilities\Health\ObservabilityHealthCheck;

final readonly class CheckObservabilityHealth
{
    /**
     * @return array{healthy: bool, drivers: array<string, bool>, issues: list<string>}
     */
    public function check() : array
    {
        return (new ObservabilityHealthCheck())->check();
    }
}
