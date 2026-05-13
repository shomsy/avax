<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs;

/**
 * In-memory failed jobs store for testing and lightweight runtimes.
 *
 * Instance-scoped — does not leak between tests when fresh instances are created.
 * Reset-safe via clear().
 */
final class InMemoryFailedJobsStore implements FailedJobsStore
{
    /** @var array<string, list<array<string, mixed>>> */
    private array $failedJobs = [];

    public function record(string $queue, array $payload, string $reason, string $failedAt) : void
    {
        $this->failedJobs[$queue][] = [
            ...$payload,
            'queue' => $queue,
            'reason'    => $reason,
            'failed_at' => $failedAt,
        ];
    }

    public function count(string $queue = '') : int
    {
        return count($this->list($queue));
    }

    public function list(string $queue = '') : array
    {
        if ($queue !== '') {
            return $this->failedJobs[$queue] ?? [];
        }

        $all = [];

        foreach ($this->failedJobs as $queueJobs) {
            array_push($all, ...$queueJobs);
        }

        return $all;
    }

    public function find(string $id) : array|null
    {
        foreach ($this->failedJobs as $queue => $queueJobs) {
            foreach ($queueJobs as $job) {
                if ((string) ($job['id'] ?? '') !== $id) {
                    continue;
                }

                return $this->normalizeFailedJob(queue: $queue, job: $job);
            }
        }

        return null;
    }

    public function remove(string $id) : void
    {
        foreach ($this->failedJobs as $queue => $queueJobs) {
            $this->failedJobs[$queue] = array_values(array_filter(
                                                         $queueJobs,
                                                         static fn (array $job) : bool => (string) ($job['id'] ?? '') !== $id,
                                                     ));
        }
    }

    public function clear(string $queue = '') : void
    {
        if ($queue !== '') {
            unset($this->failedJobs[$queue]);
        } else {
            $this->failedJobs = [];
        }
    }

    /**
     * @param array<string, mixed> $job
     *
     * @return array{id: string, queue: string, payload: string, exception: string, failed_at: string}
     */
    private function normalizeFailedJob(string $queue, array $job) : array
    {
        return [
            'id'        => (string) ($job['id'] ?? ''),
            'queue'     => $queue,
            'payload'   => json_encode($job['job'] ?? $job, JSON_THROW_ON_ERROR),
            'exception' => (string) ($job['exception'] ?? $job['reason'] ?? ''),
            'failed_at' => (string) ($job['failed_at'] ?? ''),
        ];
    }
}
