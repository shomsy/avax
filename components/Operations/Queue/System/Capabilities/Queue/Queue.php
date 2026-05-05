<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

use Avax\Components\Operations\Queue\System\Foundation\JobInterface;
use Throwable;

final class Queue
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private static array $queues = [];

    public static function push(callable|JobInterface|string $job, array $data = [], string $queue = 'default') : string
    {
        $jobId = uniqid(prefix: 'job_', more_entropy: true);

        self::$queues[$queue][$jobId] = [
            'id'       => $jobId,
            'job'      => $job,
            'data'     => $data,
            'attempts' => 0,
            'run_at'   => time(),
            'created_at' => time(),
        ];

        return $jobId;
    }

    public static function later(int $delay, callable|JobInterface|string $job, array $data = [], string $queue = 'default') : string
    {
        $jobId = self::push(job: $job, data: $data, queue: $queue);
        self::$queues[$queue][$jobId]['run_at'] = time() + $delay;

        return $jobId;
    }

    public static function pop(string $queue = 'default') : ?array
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

    public static function process(string $queue = 'default') : int
    {
        $processed = 0;

        while ( ($job = self::pop(queue: $queue)) !== null ) {
            try {
                self::execute(job: $job);
                $processed++;
            } catch (Throwable) {
                self::release(queue: $queue, job: $job, delay: 5);
            }
        }

        return $processed;
    }

    public static function release(string $queue, array $job, int $delay = 0) : void
    {
        $jobId           = $job['id'] ?? uniqid(prefix: 'job_', more_entropy: true);
        $job['attempts'] = ($job['attempts'] ?? 0) + 1;
        $job['run_at']   = time() + $delay;
        self::$queues[$queue][$jobId] = $job;
    }

    public static function size(string $queue = 'default') : int
    {
        return count(self::$queues[$queue] ?? []);
    }

    public static function clear(string $queue = 'default') : void
    {
        unset(self::$queues[$queue]);
    }

    public static function bulk(array $jobs, string $queue = 'default') : array
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

    private static function execute(array $job) : void
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
