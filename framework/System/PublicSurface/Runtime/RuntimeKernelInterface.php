<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;

interface RuntimeKernelInterface
{
    public function state(): RuntimeState;

    public function runWorker(WorkerRuntimeInterface $workerRuntime): WorkerLifecycle;
}
