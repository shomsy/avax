<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ShutdownRuntime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;

final class ShutdownRuntime
{
    public function __construct(
        private readonly RuntimeContext $context,
    ) {
    }

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
