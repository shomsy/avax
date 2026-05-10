<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\InMemoryFailedJobsStore;
use Avax\Components\Operations\Queue\System\Foundation\JobInterface;
use Throwable;

final class Queue
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private static array $queues = [];

    private static ?FailedJobsStore $failedJobsStore = null;

    private const int DEFAULT_MAX_ATTEMPTS = 3;

    /**
     * Set the explicit failed-jobs store.
     * If not set, Queue uses an in-memory store.
     */
    public static function useFailedJobsStore(FailedJobsStore $store) : void
    {
        self::$failedJobsStore = $store;
    }

    /**
     * Get the active failed-jobs store.
     */
    private static function failedJobsStore() : FailedJobsStore
    {
        return self::$failedJobsStore ??= new InMemoryFailedJobsStore();
    }

    public static function push(callable|JobInterface|string $job, array $data = [], string $queue = 'default', int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS): string
    {
        $jobId = uniqid(prefix: 'job_', more_entropy: true);

        self::$queues[$queue][$jobId] = [
            'id' => $jobId,
            'job' => $job,
            'data' => $data,
            'attempts' => 0,
            'max_attempts' => $maxAttempts,
            'run_at' => time(),
            'created_at' => time(),
        ];

        return $jobId;
    }

    public static function later(int $delay, callable|JobInterface|string $job, array $data = [], string $queue = 'default'): string
    {
        $jobId = self::push(job: $job, data: $data, queue: $queue);
        self::$queues[$queue][$jobId]['run_at'] = time() + $delay;

        return $jobId;
    }

    public static function pop(string $queue = 'default'): ?array
    {
        foreach (self::$queues[$queue] ?? [] as $jobId => $job) {
            if (($job['run_at'] ?? 0) > time()) {
                continue;
            }

            unset(self::$queues[$queue][$jobId]);

            return $job;
        }

        return null;
    }

    public static function process(string $queue = 'default'): int
    {
        $processed = 0;

        while (($job = self::pop(queue: $queue)) !== null) {
            try {
                self::execute(job: $job);
                $processed++;
            } catch (Throwable) {
                self::release(queue: $queue, job: $job, delay: 5);
            }
        }

        return $processed;
    }

    public static function release(string $queue, array $job, int $delay = 0): void
    {
        $jobId = $job['id'] ?? uniqid(prefix: 'job_', more_entropy: true);
        $job['attempts'] = ($job['attempts'] ?? 0) + 1;
        $maxAttempts = $job['max_attempts'] ?? self::DEFAULT_MAX_ATTEMPTS;

        if ($job['attempts'] >= $maxAttempts) {
            // Move to dead letter store
            self::failedJobsStore()->record(
                queue   : $queue,
                payload : [
                              'job'      => $job['job'] ?? null,
                              'id'       => $jobId,
                              'data'     => $job['data'] ?? [],
                              'attempts' => $job['attempts'],
                          ],
                reason  : 'Max attempts exceeded',
                failedAt: date('Y-m-d H:i:s'),
            );

            return;
        }

        $job['run_at'] = time() + $delay;
        self::$queues[$queue][$jobId] = $job;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function deadLetters(string $queue = ''): array
    {
        return self::failedJobsStore()->list($queue);
    }

    public static function deadLetterCount(string $queue = ''): int
    {
        return self::failedJobsStore()->count($queue);
    }

    public static function clearDeadLetters(string $queue = ''): void
    {
        self::failedJobsStore()->clear($queue);
    }

    public static function size(string $queue = 'default'): int
    {
        return count(self::$queues[$queue] ?? []);
    }

    public static function clear(string $queue = 'default'): void
    {
        unset(self::$queues[$queue]);
    }

    /**
     * Reset all static queue state for long-lived worker safety.
     *
     * Clears all queues so subsequent requests start from a clean slate.
     * Must be called during worker warmup or between isolated test runs.
     */
    public static function reset(): void
    {
        self::$queues = [];
        self::failedJobsStore()->clear();
        self::$failedJobsStore = null;
    }

    public static function bulk(array $jobs, string $queue = 'default'): array
    {
        $ids = [];

        foreach ($jobs as $job) {
            $ids[] = self::push(
                job  : $job['job'] ?? $job,
                data : $job['data'] ?? [],
                queue: $queue,
            );
        }

        return $ids;
    }

    private static function execute(array $job): void
    {
        $handler = $job['job'];

        if ($handler instanceof JobInterface) {
            $handler->handle(data: $job['data'] ?? []);

            return;
        }

        if (is_callable(value: $handler)) {
            $handler($job['data'] ?? []);
        }
    }
}
