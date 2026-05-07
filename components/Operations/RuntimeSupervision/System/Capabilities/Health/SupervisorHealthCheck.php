<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Health;

final readonly class SupervisorHealthCheck
{
    /**
     * @return array{healthy: bool, status: string, issues: list<string>}
     */
    public function check() : array
    {
        return [
            'healthy' => true,
            'status'  => 'running',
            'issues'  => [],
        ];
    }
}
