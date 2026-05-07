<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Flows\MonitorBackgroundProcess;

use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\HealthPolicy\HealthPolicy;
use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\ProcessRegistry\ProcessRegistry;

final readonly class MonitorBackgroundProcess
{
    public function __construct(
        private HealthPolicy $healthPolicy = new HealthPolicy(),
    ) {}

    /**
     * @return array{healthy:int,unhealthy:int,total:int,processes:list<array{id:string,name:string,healthy:bool}>}
     */
    public function execute(ProcessRegistry $registry) : array
    {
        return $this->healthPolicy->checkAll(processes: $registry->all());
    }
}
