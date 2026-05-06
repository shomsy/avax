<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process;

class ProcessRegistry
{
    /**
     * @var array<string, ProcessRecord>
     */
    private array $processes = [];

    public function register(ProcessRecord $processRecord): void
    {
        $this->processes[$processRecord->id] = $processRecord;
    }

    public function find(string $id): ?ProcessRecord
    {
        return $this->processes[$id] ?? null;
    }

    public function all(): array
    {
        return $this->processes;
    }

    /**
     * @return array<string, ProcessRecord>
     */
    public function running(): array
    {
        return array_filter(
            $this->processes,
            fn (ProcessRecord $processRecord): bool => $processRecord->isRunning()
        );
    }
}
