<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Health;

class MemoryLifecycleHealthReport
{
    public function __construct(
        public readonly bool $healthy,
        public readonly string $message,
        public readonly array $details = [],
    ) {
    }
}
