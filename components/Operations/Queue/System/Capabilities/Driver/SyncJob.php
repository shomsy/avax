<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Driver;

use Avax\Components\Operations\Queue\System\Capabilities\Job;

/**
 * Sync job implementation.
 */
final readonly class SyncJob implements Job
{
    public function __construct(
        private string $id,
        private string $job,
        private array $data,
        private int $attempts = 1,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPayload(): array
    {
        return [
            'job' => $this->job,
            'data' => $this->data,
        ];
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function release(int $delay = 0): void
    {
        // No-op for sync driver
    }

    public function delete(): void
    {
        // No-op for sync driver
    }
}
