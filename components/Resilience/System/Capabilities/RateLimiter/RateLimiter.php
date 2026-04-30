<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Capabilities\RateLimiter;

use Closure;
use Redis;

final class RedisRateLimiter
{
    private Redis  $redis;
    private string $prefix;

    public function __construct(
        private array $config = [],
    )
    {
        $this->prefix = $config['prefix'] ?? 'ratelimit:';

        $this->redis = new Redis();
        $this->redis->connect(
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 6379
        );
    }

    public function attempt(string $key, int $maxAttempts, int $decaySeconds) : bool
    {
        $attempts = $this->attempts($key);

        if ($attempts >= $maxAttempts) {
            return false;
        }

        $this->hit($key, $decaySeconds);

        return true;
    }

    private function attempts(string $key) : int
    {
        $attempts = $this->redis->get($this->prefix . $key);

        return (int) ($attempts ?? 0);
    }

    private function hit(string $key, int $decaySeconds) : void
    {
        $keyExists = $this->redis->exists($this->prefix . $key);

        if (! $keyExists) {
            $this->redis->setex($this->prefix . $key, $decaySeconds, 1);
            $this->redis->setex($this->prefix . $key . ':timer', $decaySeconds, time() + $decaySeconds);
        } else {
            $this->redis->incr($this->prefix . $key);
        }
    }

    public function retriesLeft(string $key, int $maxAttempts) : int
    {
        return $this->remaining($key, $maxAttempts);
    }

    public function remaining(string $key, int $maxAttempts) : int
    {
        $attempts = $this->attempts($key);

        return max(0, $maxAttempts - $attempts);
    }

    public function clear(string $key) : void
    {
        $this->redis->del($this->prefix . $key);
    }

    public function availableIn(string $key) : int
    {
        $time = $this->redis->get($this->prefix . $key . ':timer');

        if (! $time) {
            return 0;
        }

        return max(0, (int) $time - time());
    }
}

final class RateLimit
{
    private static RedisRateLimiter|null $limiter = null;

    public static function attempt(string $key, int $maxAttempts, int $decaySeconds = 60) : bool
    {
        return self::getLimiter()->attempt($key, $maxAttempts, $decaySeconds);
    }

    public static function getLimiter() : RedisRateLimiter
    {
        if (self::$limiter === null) {
            self::$limiter = new RedisRateLimiter();
        }

        return self::$limiter;
    }

    public static function setLimiter(RedisRateLimiter $limiter) : void
    {
        self::$limiter = $limiter;
    }

    public static function clear(string $key) : void
    {
        self::getLimiter()->clear($key);
    }

    public static function tooManyAttempts(string $key, int $maxAttempts) : bool
    {
        return self::getLimiter()->remaining($key, $maxAttempts) === 0;
    }

    public static function remaining(string $key, int $maxAttempts) : int
    {
        return self::getLimiter()->remaining($key, $maxAttempts);
    }
}

final class RateLimitMiddleware
{
    public function __construct(
        private array $config = [],
    ) {}

    public function handle($request, Closure $next)
    {
        $key          = $this->resolveKey($request);
        $maxAttempts  = $this->config['max_attempts'] ?? 60;
        $decaySeconds = $this->config['decay_seconds'] ?? 60;

        if (RateLimit::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimit::availableIn($key);

            return response()->json([
                                        'error' => 'Too many attempts',
                                                                                                                                                                                                                                                                            'retry_after' => $retryAfter,
                                                                                                                                                                                                                                                                                                                                                                                                 'message' => 'Rate limit exceeded. Try again later.',
                                    ], 429)->withHeaders([
                                                             'Retry-After'           => (string) $retryAfter,
                                                             'X-RateLimit-Limit'     => (string) $maxAttempts,
                                                             'X-RateLimit-Remaining' => '0',
                                                         ]);
        }

        $response = $next($request);

        $remaining = RateLimit::remaining($key, $maxAttempts);

        return $response->withHeaders([
                                          'X-RateLimit-Limit'     => (string) $maxAttempts,
                                          'X-RateLimit-Remaining' => (string) $remaining,
                                      ]);
    }

    private function resolveKey($request) : string
    {
        $ip = $request->getAttribute('client_ip') ??
            $request->getServerParams()['REMOTE_ADDR'] ??
            'unknown';

        $endpoint = $request->getUri()->getPath();

        return $ip . ':' . $endpoint;
    }
}