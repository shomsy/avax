<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Engines\FrankenPhp;

use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLoop;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;

final readonly class FrankenPhpWorkerLoop
{
    public function __construct(
        private WorkerLoop $workerLoop,
        private WorkerRuntimeInterface $workerRuntime,
    ) {
    }

    public function run(): WorkerLifecycle
    {
        return $this->workerLoop->run(workerRuntime: $this->workerRuntime);
    }
}
