<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Adapters\Swoole;

use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLoop;

final readonly class SwooleHttpServerAdapter
{
    public function __construct(private WorkerLoop $workerLoop)
    {
    }

    public function run() : WorkerLifecycle
    {
        return $this->workerLoop->run();
    }
}
