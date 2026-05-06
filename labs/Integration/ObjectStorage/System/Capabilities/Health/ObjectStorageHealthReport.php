<?php

declare(strict_types=1);

namespace Avax\Labs\Integration\ObjectStorage\System\Capabilities\Health;

class ObjectStorageHealthReport
{
    public function __construct(
        public readonly bool $healthy,
        public readonly string $message,
        public readonly int $latencyMs,
    ) {
    }
}
