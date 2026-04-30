<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\StartWorker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;

final class StartWorker
{
    public function __construct(
        private readonly RuntimeContext $context,
    ) {
    }

    public function start(callable $bootstrap): void
    {
        $this->bootWorkerApplication($bootstrap);

        $this->startWorkerLoop();
    }

    private function bootWorkerApplication(callable $bootstrap): void
    {
        $bootstrap();
    }

    private function startWorkerLoop(): void
    {
        $this->context->getRuntime()->run();
    }
}
