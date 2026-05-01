<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\StartWorker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;

final readonly class StartWorker
{
    public function __construct(
        private RuntimeContext $runtimeContext,
    ) {}

    public function start(callable $bootstrap) : void
    {
        $this->bootWorkerApplication($bootstrap);

        $this->startWorkerLoop();
    }

    private function bootWorkerApplication(callable $bootstrap) : void
    {
        $bootstrap();
    }

    private function startWorkerLoop() : void
    {
        $this->runtimeContext->getRuntime()->run();
    }
}
