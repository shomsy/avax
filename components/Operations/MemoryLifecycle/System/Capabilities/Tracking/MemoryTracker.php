<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Tracking;

class MemoryTracker
{
    /**
     * @var array<int, array{timestamp: float, operation: string, bytes: int}>
     */
    private array $allocations = [];

    private bool $enabled = false;

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function track(string $operation, int $bytes): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->allocations[] = [
            'timestamp' => hrtime(true) / 1e9,
            'operation' => $operation,
            'bytes' => $bytes,
        ];
    }

    /**
     * @return array<int, array{timestamp: float, operation: string, bytes: int}>
     */
    public function getAllocations(): array
    {
        return $this->allocations;
    }

    public function clear(): void
    {
        $this->allocations = [];
    }
}