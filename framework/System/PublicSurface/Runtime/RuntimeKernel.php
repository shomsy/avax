<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;

final readonly class RuntimeKernel implements RuntimeKernelInterface
{
    public function __construct(private RuntimeInterface $runtime)
    {
    }

    public function state() : RuntimeState
    {
        return $this->runtime->state();
    }

    public function runWorker(WorkerRuntimeInterface $workerRuntime) : WorkerLifecycle
    {
        return $this->runtime->runWorker(workerRuntime: $workerRuntime);
    }
}
