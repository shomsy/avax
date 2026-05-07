<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Flows\CheckSupervisorHealth;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Health\SupervisorHealthCheck;

final readonly class CheckSupervisorHealth
{
    /**
     * @return array{healthy: bool, status: string, issues: list<string>}
     */
    public function check() : array
    {
        return (new SupervisorHealthCheck())->check();
    }
}
