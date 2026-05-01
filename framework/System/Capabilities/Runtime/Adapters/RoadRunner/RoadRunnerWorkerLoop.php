<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Adapters\RoadRunner;

use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLoop;

final readonly class RoadRunnerWorkerLoop
{
    public function __construct(private WorkerLoop $workerLoop) {}

    public function run() : WorkerLifecycle
    {
        return $this->workerLoop->run();
    }
}
