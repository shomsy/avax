<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

final readonly class RuntimeStatusSnapshot
{
    public function __construct(
        public HealthStatus $status,
        public int $uptime = 0,
        public int $memoryBytes = 0,
        /** @var list<HealthFinding> $findings */
        public array $findings = [],
    ) {
    }
}
