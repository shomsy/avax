<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Snapshot;

class MemorySnapshot
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private readonly float $timestamp;

    public function __construct()
    {
        $this->timestamp = hrtime(true) / 1e9;
    }

    public function capture(): self
    {
        $snapshot = new self();
        $snapshot->data = [
            'memory_used' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'gc_enabled' => gc_enabled(),
            'gc_stats' => gc_status(),
        ];

        return $snapshot;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * @return array{memory_used: int, memory_peak: int, timestamp: string}
     */
    public function toArray(): array
    {
        return [
            'memory_used' => $this->getUsed(),
            'memory_peak' => $this->getPeak(),
            'timestamp' => date('c', (int) $this->timestamp),
        ];
    }

    public function getUsed(): int
    {
        return $this->data['memory_used'] ?? 0;
    }

    public function getPeak(): int
    {
        return $this->data['memory_peak'] ?? 0;
    }
}
