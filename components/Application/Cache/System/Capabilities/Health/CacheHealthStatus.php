<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Health;

use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

/**
 * Value object with cache health metrics.
 *
 * Represents the health status of a cache instance including
 * connectivity, latency, memory usage, and hit rate information.
 */
final readonly class CacheHealthStatus
{
    public function __construct(public bool $connected, public int $latency, public float $memoryUsage, public float $hitRate, public Timestamp $timestamp, public ?string $error = null, public int $memoryLimit = 0, public int $keyCount = 0, public int $connectionCount = 0, public string $version = '') {
    }

    /**
     * Create a healthy status.
     */
    public static function healthy(
        int $latency = 0,
        float $memoryUsage = 0.0,
        float $hitRate = 1.0,
        ?Timestamp $lastCheck = null,
        ?Timestamp $timestamp = null,
        int $memoryLimit = 0,
        int $keyCount = 0,
        int $connectionCount = 0,
        string $version = '',
    ): self {
        return new self(
            connected      : true,
            latency        : $latency,
            memoryUsage    : $memoryUsage,
            hitRate        : $hitRate,
            error          : null,
            memoryLimit    : $memoryLimit,
            keyCount       : $keyCount,
            connectionCount: $connectionCount,
            version        : $version,
            lastCheck      : $lastCheck ?? $timestamp ?? Timestamp::now(),
        );
    }

    /**
     * Create an unhealthy status with an error.
     */
    public static function unhealthy(
        string $error,
        int $latency = 0,
        float $memoryUsage = 0.0,
        float $hitRate = 0.0,
        ?Timestamp $lastCheck = null,
        ?Timestamp $timestamp = null,
    ): self {
        return new self(
            connected  : false,
            latency    : $latency,
            memoryUsage: $memoryUsage,
            hitRate    : $hitRate,
            error      : $error,
            lastCheck  : $lastCheck ?? $timestamp ?? Timestamp::now(),
        );
    }

    /**
     * Create a degraded status (connected but with issues).
     */
    public static function degraded(
        string $error,
        int $latency = 0,
        float $memoryUsage = 0.0,
        float $hitRate = 0.5,
        ?Timestamp $lastCheck = null,
        ?Timestamp $timestamp = null,
        int $memoryLimit = 0,
        int $keyCount = 0,
        int $connectionCount = 0,
        string $version = '',
    ): self {
        return new self(
            connected  : true,
            latency    : $latency,
            memoryUsage: $memoryUsage,
            hitRate    : $hitRate,
            error      : $error,
            memoryLimit: $memoryLimit,
            keyCount   : $keyCount,
            connectionCount: $connectionCount,
            version: $version,
            lastCheck: $lastCheck ?? $timestamp ?? Timestamp::now(),
        );
    }

    /**
     * Check if the cache is degraded.
     *
     * Degraded means: connected but with warnings (high latency, high memory, or low hit rate).
     */
    public function isDegraded(
        int $maxLatencyMs = 100,
        float $maxMemoryUsagePercent = 90.0,
        float $minHitRate = 0.5,
    ): bool {
        if (! $this->connected) {
            return false;
        }

        return $this->latency     > $maxLatencyMs
            || $this->memoryUsage > $maxMemoryUsagePercent
            || $this->hitRate < $minHitRate;
    }

    /**
     * Check if memory usage is critically high (above 95%).
     */
    public function isMemoryCritical(): bool
    {
        return $this->getMemoryUsagePercent() > 95.0;
    }

    /**
     * Get memory usage as a percentage.
     */
    public function getMemoryUsagePercent(): float
    {
        if ($this->memoryLimit <= 0) {
            return $this->memoryUsage;
        }

        return ($this->memoryUsage / $this->memoryLimit) * 100;
    }

    /**
     * Check if memory usage is high (above 80%).
     */
    public function isMemoryHigh(): bool
    {
        return $this->getMemoryUsagePercent() > 80.0;
    }

    /**
     * Check if latency is high.
     */
    public function isLatencyHigh(int $thresholdMs = 100): bool
    {
        return $this->latency > $thresholdMs;
    }

    /**
     * Check if hit rate is low.
     */
    public function isHitRateLow(float $threshold = 0.5): bool
    {
        return $this->hitRate < $threshold;
    }

    /**
     * Convert to array representation.
     *
     * @return array{
     *     connected: bool,
     *     latency: int,
     *     memoryUsage: float,
     *     hitRate: float,
     *     lastCheck: int,
     *     error: string|null,
     *     memoryLimit: int,
     *     keyCount: int,
     *     connectionCount: int,
     *     version: string,
     *     memoryUsagePercent: float,
     *     healthLevel: string
     * }
     */
    public function toArray(): array
    {
        return [
            'connected'          => $this->connected,
            'latency'            => $this->latency,
            'memoryUsage'        => $this->memoryUsage,
            'hitRate'            => $this->hitRate,
            'lastCheck'          => $this->timestamp->seconds,
            'error'              => $this->error,
            'memoryLimit'        => $this->memoryLimit,
            'keyCount'           => $this->keyCount,
            'connectionCount'    => $this->connectionCount,
            'version'            => $this->version,
            'memoryUsagePercent' => $this->getMemoryUsagePercent(),
            'healthLevel'        => $this->getHealthLevel(),
        ];
    }

    /**
     * Get the health level as a string.
     */
    public function getHealthLevel(
        int $maxLatencyMs = 100,
        float $maxMemoryUsagePercent = 90.0,
        float $minHitRate = 0.5,
    ): string {
        if (! $this->connected) {
            return 'unhealthy';
        }

        if ($this->isHealthy($maxLatencyMs, $maxMemoryUsagePercent, $minHitRate)) {
            return 'healthy';
        }

        return 'degraded';
    }

    /**
     * Check if the cache is healthy.
     *
     * Healthy means: connected, latency under threshold, memory under limit, hit rate acceptable.
     */
    public function isHealthy(
        int $maxLatencyMs = 100,
        float $maxMemoryUsagePercent = 90.0,
        float $minHitRate = 0.5,
    ): bool {
        if (! $this->connected) {
            return false;
        }

        if ($this->latency > $maxLatencyMs) {
            return false;
        }

        if ($this->memoryUsage > $maxMemoryUsagePercent) {
            return false;
        }

        return $this->hitRate >= $minHitRate;
    }
}
