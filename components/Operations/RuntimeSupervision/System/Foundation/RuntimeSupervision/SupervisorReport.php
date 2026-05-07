<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Foundation\RuntimeSupervision;

final readonly class SupervisorReport
{
    /**
     * @param array<string, mixed> $processes
     */
    public function __construct(
        public string $name,
        public string $status,
        public array  $processes,
        public int    $uptime,
        public int    $restartCount = 0,
    ) {}

    public function toArray() : array
    {
        return [
            'name'          => $this->name,
            'status'        => $this->status,
            'processes'     => $this->processes,
            'uptime'        => $this->uptime,
            'restart_count' => $this->restartCount,
        ];
    }
}
