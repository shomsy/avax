<?php

declare(strict_types=1);

namespace Avax\Components\Tasks\System\Capabilities\Queue;

use Avax\Components\Tasks\System\Foundation\JobInterface;
use Redis;
use Throwable;

final class Queue
{
    private static array  $queues  = [];
    private static string $default = 'default';

    public static function later(int $delay, string $job, array $data = [], string $queue = 'default') : string
    {
        $jobId = uniqid('job_');

        self::$queues[$queue][$jobId] = [
            'job'        => $job,
            'data'       => $data,
            'attempts'   => 0,
            'run_at'     => time() + $delay,
            'created_at' => time(),
        ];

        return $jobId;
    }

    public static function process(string $queue = 'default') : int
    {
        $processed = 0;

        while ( $job = self::pop($queue) ) {
            try {
                if (is_callable($job['job'])) {
                    ($job['job'])($job['data']);
                } elseif (is_object($job['job']) && $job['job'] instanceof JobInterface) {
                    $job['job']->handle($job['data']);
                }

                $processed++;
            } catch (Throwable $e) {
                self::release($queue, $job['id'], $job['data']);
            }
        }

        return $processed;
    }

    public static function pop(string $queue = 'default') : array|null
    {
        $jobs = self::$queues[$queue] ?? [];

        foreach ($jobs as $jobId => $job) {
            if (isset($job['run_at']) && $job['run_at'] > time()) {
                continue;
            }

            unset(self::$queues[$queue][$jobId]);

            return [
                'id'       => $jobId,
                'job'      => $job['job'],
                'data'     => $job['data'],
                'attempts' => $job['attempts'],
            ];
        }

        return null;
    }

    public static function release(string $queue, string $jobId, array $data, int $delay = 0) : void
    {
        self::$queues[$queue][$jobId] = [
            'job'        => $data['job'] ?? null,
            'data'       => $data,
            'attempts'   => ($data['attempts'] ?? 0) + 1,
            'run_at'     => time() + $delay,
            'created_at' => time(),
        ];
    }

    public static function size(string $queue = 'default') : int
    {
        return count(self::$queues[$queue] ?? []);
    }

    public static function bulk(array $jobs, string $queue = 'default') : array
    {
        $ids = [];

        foreach ($jobs as $job) {
            $ids[] = self::push($job['job'] ?? $job, $job['data'] ?? [], $queue);
        }

        return $ids;
    }

    public static function push(string $job, array $data = [], string $queue = 'default') : string
    {
        $jobId = uniqid('job_');

        self::$queues[$queue][$jobId] = [
            'job'        => $job,
            'data'       => $data,
            'attempts'   => 0,
            'created_at' => time(),
        ];

        return $jobId;
    }
}

final class RedisQueue
{
    private Redis $redis;
    private string $prefix;

    public function __construct(
        private array $config = [],
    )
    {
        $this->prefix = $config['prefix'] ?? 'queue_';

        $this->redis = new Redis();
        $this->redis->connect(
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 6379
        );
    }

    public function push(string $queue, array $job) : string
    {
        $jobId     = uniqid();
        $job['id'] = $jobId;

        $this->redis->rPush($this->prefix . $queue, json_encode($job));

        return $jobId;
    }

    public function pop(string $queue, int $timeout = 0) : array|null
    {
        if ($timeout > 0) {
            $result = $this->redis->blPop($this->prefix . $queue, $timeout);
            if (! $result) return null;
            $data = $result[1] ?? $result[0];
        } else {
            $data = $this->redis->lPop($this->prefix . $queue);
        }

        return $data ? json_decode($data, true) : null;
    }

    public function size(string $queue) : int
    {
        return (int) $this->redis->lLen($this->prefix . $queue);
    }
}