<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process;

class ProcessRecord
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $status,
        public readonly int $pid,
        public readonly float $startedAt,
        public readonly ?float $endedAt = null,
    ) {
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    public function duration(): float
    {
        $end = $this->endedAt ?? hrtime(true) / 1e9;

        return $end - $this->startedAt;
    }
}
