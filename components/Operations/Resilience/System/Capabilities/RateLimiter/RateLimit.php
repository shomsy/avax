<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter;

final class RateLimit
{
    private static RedisRateLimiter|null $limiter = null;

    public static function setLimiter(RedisRateLimiter $limiter) : void
    {
        self::useLimiter(limiter: $limiter);
    }

    public static function useLimiter(RedisRateLimiter $limiter) : void
    {
        self::$limiter = $limiter;
    }

    public static function attempt(string $key, int $maxAttempts, int $decaySeconds = 60) : bool
    {
        return self::limiter()->attempt(
            key         : $key,
            maxAttempts : $maxAttempts,
            decaySeconds: $decaySeconds,
        );
    }

    private static function limiter() : RedisRateLimiter
    {
        if (self::$limiter === null) {
            self::$limiter = new RedisRateLimiter(config: ['driver' => 'auto']);
        }

        return self::$limiter;
    }

    public static function remaining(string $key, int $maxAttempts, int $decaySeconds = 60) : int
    {
        return self::limiter()->remaining(
            key         : $key,
            maxAttempts : $maxAttempts,
            decaySeconds: $decaySeconds,
        );
    }

    public static function clear(string $key) : void
    {
        self::limiter()->clear(key: $key);
    }

    public static function availableIn(string $key, int $decaySeconds = 60) : int
    {
        return self::limiter()->availableIn(key: $key, decaySeconds: $decaySeconds);
    }

    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds = 60) : bool
    {
        return self::limiter()->tooManyAttempts(
            key         : $key,
            maxAttempts : $maxAttempts,
            decaySeconds: $decaySeconds,
        );
    }
}
