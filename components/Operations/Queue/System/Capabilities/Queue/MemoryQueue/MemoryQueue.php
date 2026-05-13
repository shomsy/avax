<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\MemoryQueue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\InMemoryFailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;

/**
 * In-memory queue broker for testing and lightweight runtimes.
 *
 * Stores jobs in a plain array. No persistence, no external dependencies.
 * Supports dead letter routing when max attempts are exceeded.
 * Dead-letter state uses an explicit FailedJobsStore for reset-safe behavior.
 */
final class MemoryQueue implements QueueBroker
{
    /** @var array<string, list<array<string, mixed>>> */
    private array $queues = [];

    public function __construct(
        private readonly int             $defaultMaxAttempts = 3,
        private readonly FailedJobsStore $failedJobsStore = new InMemoryFailedJobsStore(),
    )
    {
    }

    /**
     * @param array<string, mixed> $job
     */
    public function push(string $queue, array $job) : void
    {
        $entry = [
            'job' => $job,
            'id' => $job['id'] ?? uniqid('job_', true),
            'attempts' => 0,
            'max_attempts' => $job['max_attempts'] ?? $this->defaultMaxAttempts,
        ];

        $this->queues[$queue][] = $entry;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pop(string $queue) : ?array
    {
        if (!isset($this->queues[$queue]) || $this->queues[$queue] === []) {
            return null;
        }

        return array_shift($this->queues[$queue]);
    }

    public function size(string $queue) : int
    {
        return count($this->queues[$queue] ?? []);
    }

    public function remove(string $queue, string $jobId) : void
    {
        if (!isset($this->queues[$queue])) {
            return;
        }

        $this->queues[$queue] = array_values(array_filter(
            $this->queues[$queue],
            static fn (array $entry): bool => $entry['id'] !== $jobId,
        ));
    }

    public function clear(string $queue) : void
    {
        unset($this->queues[$queue]);
    }

    /**
     * Requeue a failed job or send to dead letter queue.
     *
     * @param array<string, mixed> $jobEntry
     */
    public function retry(string $queue, array $jobEntry, string $reason) : void
    {
        $attempts = ($jobEntry['attempts'] ?? 0) + 1;
        $maxAttempts = $jobEntry['max_attempts'] ?? $this->defaultMaxAttempts;

        if ($attempts >= $maxAttempts) {
            $this->failedJobsStore->record(
                queue   : $queue,
                payload : [
                              'job'      => $jobEntry['job'] ?? $jobEntry,
                              'id'       => $jobEntry['id'] ?? uniqid('dlq_', true),
                              'attempts' => $attempts,
                          ],
                reason  : $reason,
                failedAt: date('Y-m-d H:i:s'),
            );

            return;
        }

        $jobEntry['attempts'] = $attempts;
        $this->queues[$queue][] = $jobEntry;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function deadLetters(string $queue = '') : array
    {
        return $this->failedJobsStore->list($queue);
    }

    public function deadLetterCount(string $queue = '') : int
    {
        return $this->failedJobsStore->count($queue);
    }

    public function clearDeadLetters(string $queue = '') : void
    {
        $this->failedJobsStore->clear($queue);
    }

    public function clearAll() : void
    {
        $this->queues = [];
        $this->failedJobsStore->clear();
    }
}
