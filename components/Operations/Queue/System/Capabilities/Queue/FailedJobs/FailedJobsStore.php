<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs;

/**
 * Explicit store for failed/dead-letter queue jobs.
 *
 * Queue owns operational failed-job/dead-letter behavior.
 * This interface defines the reset-safe storage contract.
 */
interface FailedJobsStore
{
    /**
     * Record a failed job.
     *
     * @param array<string, mixed> $payload
     */
    public function record(string $queue, array $payload, string $reason, string $failedAt) : void;

    /**
     * List all failed jobs for a queue, or all queues if empty.
     *
     * @param string $queue Empty string means all queues.
     *
     * @return list<array<string, mixed>>
     */
    public function list(string $queue = '') : array;

    /**
     * Find a failed job by ID.
     *
     * @return array{id: string, queue: string, payload: string, exception: string, failed_at: string}|null
     */
    public function find(string $id) : array|null;

    /**
     * Remove a failed job by ID.
     */
    public function remove(string $id) : void;

    /**
     * Count failed jobs for a queue, or all queues if empty.
     */
    public function count(string $queue = ''): int;

    /**
     * Clear failed jobs for a queue, or all queues if empty.
     */
    public function clear(string $queue = ''): void;
}
