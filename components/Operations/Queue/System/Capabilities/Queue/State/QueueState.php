<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\State;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\InMemoryFailedJobsStore;

/**
 * Instance-scoped canonical queue state.
 *
 * Holds all in-memory queue data and failed jobs store.
 * Replaces static $queues array for long-lived worker safety.
 */
final class QueueState
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private array $queues = [];

    private ?FailedJobsStore $failedJobsStore = null;

    /**
     * Push a job onto the queue.
     *
     * @param array<string, mixed> $jobData
     */
    public function push(string $jobId, array $jobData, string $queue) : void
    {
        $this->queues[$queue][$jobId] = $jobData;
    }

    /**
     * Pop the next ready job from the queue.
     *
     * @return array<string, mixed>|null
     */
    public function pop(string $queue, int $now) : ?array
    {
        foreach ($this->queues[$queue] ?? [] as $jobId => $job) {
            if (($job['run_at'] ?? 0) > $now) {
                continue;
            }

            unset($this->queues[$queue][$jobId]);

            return $job;
        }

        return null;
    }

    /**
     * Release a job back onto the queue with a delay.
     *
     * @param array<string, mixed> $jobData
     */
    public function release(string $queue, string $jobId, array $jobData) : void
    {
        $this->queues[$queue][$jobId] = $jobData;
    }

    /**
     * Remove a job from the queue.
     */
    public function remove(string $queue, string $jobId) : void
    {
        unset($this->queues[$queue][$jobId]);
    }

    /**
     * Get queue size.
     */
    public function size(string $queue) : int
    {
        return count($this->queues[$queue] ?? []);
    }

    /**
     * Get the failed jobs store.
     */
    public function getFailedJobsStore() : FailedJobsStore
    {
        return $this->failedJobsStore ??= new InMemoryFailedJobsStore();
    }

    /**
     * Set an explicit failed-jobs store.
     */
    public function setFailedJobsStore(FailedJobsStore $store) : void
    {
        $this->failedJobsStore = $store;
    }

    /**
     * Reset all state for long-lived worker safety.
     */
    public function reset() : void
    {
        $this->queues = [];
        $this->failedJobsStore?->clear();
        $this->failedJobsStore = null;
    }

    /**
     * Clear a specific queue, or all queues if empty string.
     */
    public function clear(string $queue) : void
    {
        if ($queue === '') {
            $this->queues = [];
        } else {
            unset($this->queues[$queue]);
        }
    }
}
