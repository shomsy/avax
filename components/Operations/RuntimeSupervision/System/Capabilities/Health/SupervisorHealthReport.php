<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Health;

final readonly class SupervisorHealthReport
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

    /**
     * @return array<string, mixed>
     */
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
