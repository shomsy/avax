<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter;

use Redis;
use Throwable;

final class RedisRateLimiter
{
    /** @var array<string, list<float>> */
    private array $windows = [];

    private ?Redis $redis = null;

    public function __construct(
        private readonly array $config = [],
    ) {
        $this->connectWhenAvailable();
    }

    private function connectWhenAvailable(): void
    {
        if (($this->config['driver'] ?? 'auto') === 'array') {
            return;
        }

        if (! class_exists(Redis::class)) {
            return;
        }

        try {
            $redis = new Redis();
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

    public function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        if ($this->tooManyAttempts(key: $key, maxAttempts: $maxAttempts, decaySeconds: $decaySeconds)) {
            return false;
        }

        $this->hit(key: $key, decaySeconds: $decaySeconds);

        return true;
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds = 60): bool
    {
        return $this->attempts(key: $key, decaySeconds: $decaySeconds) >= $maxAttempts;
    }

    private function attempts(string $key, int $decaySeconds): int
    {
        if ($this->redis instanceof Redis) {
            $redisKey = $this->redisKey(key: $key);
            $this->redis->zRemRangeByScore($redisKey, '-inf', (string) (microtime(true) - $decaySeconds));

            return (int) $this->redis->zCard($redisKey);
        }

        $this->windows[$key] = $this->activeWindow(key: $key, decaySeconds: $decaySeconds);

        return count($this->windows[$key]);
    }

    private function redisKey(string $key): string
    {
        return ($this->config['prefix'] ?? 'avax:rate-limit:').$key;
    }

    /**
     * @return list<float>
     */
    private function activeWindow(string $key, int $decaySeconds): array
    {
        $cutoff = microtime(true) - $decaySeconds;

        return array_values(array_filter(
            $this->windows[$key] ?? [],
            static fn (float $hitAt): bool => $hitAt >= $cutoff,
        ));
    }

    private function hit(string $key, int $decaySeconds): void
    {
        $now = microtime(true);

        if ($this->redis instanceof Redis) {
            $redisKey = $this->redisKey(key: $key);
            $this->redis->zAdd($redisKey, $now, $now.':'.bin2hex(random_bytes(4)));
            $this->redis->expire($redisKey, $decaySeconds);

            return;
        }

        $this->windows[$key] = $this->activeWindow(key: $key, decaySeconds: $decaySeconds);
        $this->windows[$key][] = $now;
    }

    public function remaining(string $key, int $maxAttempts, int $decaySeconds = 60): int
    {
        return max(0, $maxAttempts - $this->attempts(key: $key, decaySeconds: $decaySeconds));
    }

    public function clear(string $key): void
    {
        if ($this->redis instanceof Redis) {
            $this->redis->del($this->redisKey(key: $key));
        }

        unset($this->windows[$key]);
    }

    public function availableIn(string $key, int $decaySeconds = 60): int
    {
        $oldest = $this->oldestHit(key: $key, decaySeconds: $decaySeconds);

        if ($oldest === null) {
            return 0;
        }

        return max(0, (int) ceil(($oldest + $decaySeconds) - microtime(true)));
    }

    private function oldestHit(string $key, int $decaySeconds): ?float
    {
        if ($this->redis instanceof Redis) {
            $redisKey = $this->redisKey(key: $key);
            $this->redis->zRemRangeByScore($redisKey, '-inf', (string) (microtime(true) - $decaySeconds));
            $entries = $this->redis->zRange($redisKey, 0, 0, true);

            if ($entries === [] || $entries === false) {
                return null;
            }

            return (float) array_values($entries)[0];
        }

        $window = $this->activeWindow(key: $key, decaySeconds: $decaySeconds);

        return $window[0] ?? null;
    }
}
