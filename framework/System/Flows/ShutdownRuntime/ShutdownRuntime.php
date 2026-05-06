<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ShutdownRuntime;

final readonly class ShutdownRuntime
{
    public function shutdown(): void
    {
        $this->flushTerminableWork();

        $this->closeRuntimeResources();
    }

    private function flushTerminableWork(): void
    {
    }

    private function closeRuntimeResources(): void
    {
    }
}
