<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\State\QueueState;
use Avax\Components\Operations\Queue\System\Foundation\JobInterface;
use Throwable;

/**
 * Queue — in-memory job queue.
 *
 * State is owned by an instance-scoped QueueState.
 * The default shared state is accessible statically for backward compatibility,
 * but reset() replaces the state instance for long-lived worker safety.
 */
final class Queue
{
    private static QueueState $state;

    private const int DEFAULT_MAX_ATTEMPTS = 3;

    private static function state() : QueueState
    {
        return self::$state ??= new QueueState();
    }

    /**
     * Set the explicit failed-jobs store.
     * If not set, Queue uses an in-memory store.
     */
    public static function useFailedJobsStore(FailedJobsStore $store) : void
    {
        self::state()->setFailedJobsStore($store);
    }

    public static function push(callable|JobInterface|string $job, array $data = [], string $queue = 'default', int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS): string
    {
        $jobId = uniqid(prefix: 'job_', more_entropy: true);

        self::state()->push(
            jobId  : $jobId,
            jobData: [
                         'id'           => $jobId,
                         'job'          => $job,
                         'data'         => $data,
                         'attempts'     => 0,
                         'max_attempts' => $maxAttempts,
                         'run_at'       => time(),
                         'created_at'   => time(),
                     ],
            queue  : $queue,
        );

        return $jobId;
    }

    public static function later(int $delay, callable|JobInterface|string $job, array $data = [], string $queue = 'default'): string
    {
        $jobId = self::push(job: $job, data: $data, queue: $queue);
        $state = self::state();
        $state->remove(queue: $queue, jobId: $jobId);
        $state->push(
            jobId  : $jobId,
            jobData: [
                         'id'           => $jobId,
                         'job'          => $job,
                         'data'         => $data,
                         'attempts'     => 0,
                         'max_attempts' => self::DEFAULT_MAX_ATTEMPTS,
                         'run_at'       => time() + $delay,
                         'created_at'   => time(),
                     ],
            queue  : $queue,
        );

        return $jobId;
    }

    public static function pop(string $queue = 'default'): ?array
    {
        return self::state()->pop(queue: $queue, now: time());
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
            self::state()->getFailedJobsStore()->record(
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
        self::state()->release(queue: $queue, jobId: $jobId, jobData: $job);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function deadLetters(string $queue = ''): array
    {
        return self::state()->getFailedJobsStore()->list($queue);
    }

    public static function deadLetterCount(string $queue = ''): int
    {
        return self::state()->getFailedJobsStore()->count($queue);
    }

    public static function clearDeadLetters(string $queue = ''): void
    {
        self::state()->getFailedJobsStore()->clear($queue);
    }

    public static function size(string $queue = 'default'): int
    {
        return self::state()->size(queue: $queue);
    }

    public static function clear(string $queue = 'default'): void
    {
        self::state()->clear(queue: $queue);
    }

    /**
     * Reset all queue state for long-lived worker safety.
     *
     * Replaces the shared state instance so subsequent requests
     * start from a clean slate. Must be called during worker warmup
     * or between isolated test runs.
     */
    public static function reset(): void
    {
        self::state()->reset();
        self::$state = new QueueState();
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
