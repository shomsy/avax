<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Capabilities\Queue;

use Redis;
use Throwable;

final class RedisQueue
{
    private ?Redis $redis = null;

    public function __construct(private readonly array $config = [])
    {
        $this->connectWhenAvailable();
    }

    private function connectWhenAvailable(): void
    {
        if (($this->config['driver'] ?? 'auto') === 'array' || ! class_exists(class: Redis::class)) {
            return;
        }

        try {
            $redis = new Redis;
            $redis->connect(
                host   : $this->config['host'] ?? '127.0.0.1',
                port   : $this->config['port'] ?? 6379,
                timeout: $this->config['timeout'] ?? 0.05,
            );
            $this->redis = $redis;
        } catch (Throwable) {
            $this->redis = null;
        }
    }

    public function push(string $queue, array $job): string
    {
        $jobId = uniqid(prefix: 'job_', more_entropy: true);
        $job['id'] = $jobId;

        if ($this->redis === null) {
            return Queue::push(job: static fn (): null => null, data: $job, queue: $queue);
        }

        $this->redis->rPush($this->key(queue: $queue), json_encode(value: $job, flags: JSON_THROW_ON_ERROR));

        return $jobId;
    }

    private function key(string $queue): string
    {
        return ($this->config['prefix'] ?? 'avax:queue:').$queue;
    }

    public function pop(string $queue, int $timeout = 0): ?array
    {
        if ($this->redis === null) {
            return Queue::pop(queue: $queue);
        }

        $payload = $timeout > 0
            ? $this->redis->blPop($this->key(queue: $queue), $timeout)
            : $this->redis->lPop($this->key(queue: $queue));

        if ($payload === false || $payload === null || $payload === []) {
            return null;
        }

        $json = is_array(value: $payload) ? (string) end(array: $payload) : (string) $payload;

        return json_decode(json: $json, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    public function size(string $queue): int
    {
        if ($this->redis === null) {
            return Queue::size(queue: $queue);
        }

        return (int) $this->redis->lLen($this->key(queue: $queue));
    }
}
