<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\DecideStaleValueCanBeServed;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\StaleValuePolicy;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\RefreshCachedValues\RefreshPolicy;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\RefreshCachedValues\ShouldRefreshCachedValue;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockStore;
use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockWasNotAcquired;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Flows\Protection\ProtectCacheSource\AcquireCacheStampedeLock;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use DateInterval;
use Override;
use Throwable;
use Traversable;

final class AvaxCache implements CacheContract
{
    private bool $stampedeProtectionEnabled;

    public function __construct(
        private readonly CacheStore                  $cacheStore,
        private readonly Clock                       $clock,
        private readonly CacheTtl                    $cacheTtl,
        private readonly DecideStaleValueCanBeServed $decideStaleValueCanBeServed,
        private readonly ShouldRefreshCachedValue    $shouldRefreshCachedValue,
        private readonly ?CacheMetrics               $cacheMetrics = null,
        private readonly ?AcquireCacheStampedeLock   $acquireCacheStampedeLock = null,
        bool                                         $stampedeProtection = false,
    ) {
        $this->stampedeProtectionEnabled = $stampedeProtection && $this->acquireCacheStampedeLock !== null;
    }

    #[Override]
    public function remember(string $key, int|DateInterval|null $ttl, callable $loader): mixed
    {
        $startTime = hrtime(true);
        $cacheKey = CacheKey::create(key: $key);
        $result = $this->cacheStore->read($cacheKey, $this->clock);

        if ($result instanceof CacheStoreRecordWasFound) {
            $record = $result->storedCacheRecord;
            $lifecycle = $record->cachedValueLifecycle;

            if ($this->shouldRefreshValue($lifecycle)) {
                return $this->loadWithStampedeProtection($key, $ttl, $loader);
            }

            if ($lifecycle->isExpired($this->clock)) {
                if ($this->decideStaleValueCanBeServed->canServeStale()) {
                    $this->recordMetrics($startTime, 'stale_served');

                    $this->loadInBackground($key, $ttl, $loader);

                    return $record->value;
                }

                return $this->loadWithStampedeProtection($key, $ttl, $loader);
            }

            $this->recordMetrics($startTime, 'hit');

            return $record->value;
        }

        return $this->loadWithStampedeProtection($key, $ttl, $loader);
    }

    private function shouldRefreshValue(CachedValueLifecycle $cachedValueLifecycle): bool
    {
        return $this->shouldRefreshCachedValue->shouldRefresh($cachedValueLifecycle);
    }

    private function loadWithStampedeProtection(string $key, int|DateInterval|null $ttl, callable $loader): mixed
    {
        if ($this->stampedeProtectionEnabled && $this->acquireCacheStampedeLock instanceof AcquireCacheStampedeLock) {
            return $this->protectedLoad($key, $ttl, $loader);
        }

        return $this->directLoad($key, $ttl, $loader);
    }

    private function protectedLoad(string $key, int|DateInterval|null $ttl, callable $loader): mixed
    {
        /** @var AcquireCacheStampedeLock $lock */
        $lock = $this->acquireCacheStampedeLock;
        try {
            $guard = $lock->acquire($key);

            try {
                return $this->directLoad(key: $key, ttl: $ttl, loader: $loader);
            } finally {
                $guard->release();
            }
        } catch (CacheLockWasNotAcquired) {
            $cacheKey = CacheKey::create(key: $key);
            $result = $this->cacheStore->read($cacheKey, $this->clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->recordMetrics(
                    startTime: hrtime(true),
                    operation: 'stale_served',
                );

                return $result->value();
            }

            $this->recordMetrics(
                startTime: hrtime(true),
                operation: 'miss',
            );

            return null;
        }
    }

    private function directLoad(string $key, int|DateInterval|null $ttl, callable $loader): mixed
    {
        $value = $loader();
        $this->set(key: $key, value: $value, ttl: $ttl);

        return $value;
    }

    #[Override]
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $expiresAt = $this->cacheTtl->calculateExpiresAt(ttl: $ttl, clock: $this->clock);

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt ?? $this->clock->now()->add(duration: Duration::ofSeconds(seconds: 86400)),
                clock    : $this->clock,
            );

            $storedCacheRecord = new StoredCacheRecord(
                $value,
                $lifecycle,
            );

            $this->cacheStore->write($cacheKey, $storedCacheRecord);

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'write',
            );

            return true;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return false;
        }
    }

    private function recordMetrics(
        int $startTime,
        string $operation,
    ): void {
        if (! $this->cacheMetrics instanceof CacheMetrics) {
            return;
        }

        $endTime = hrtime(true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        match ($operation) {
            'hit' => $this->cacheMetrics->recordHit(),
            'miss' => $this->cacheMetrics->recordMiss(),
            'write' => $this->cacheMetrics->recordWrite(),
            'delete' => $this->cacheMetrics->recordDelete(),
            'stale_served' => $this->cacheMetrics->recordStaleServed(),
            default => null,
        };

        $this->cacheMetrics->recordLatency(microseconds: $latencyMicroseconds);
    }

    private function loadInBackground(string $key, int|DateInterval|null $ttl, callable $loader): void
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        try {
            $this->set(key: $key, value: $loader(), ttl: $ttl);
        } catch (Throwable $e) {
            error_log(sprintf('Cache background refresh failed for key "%s": %s', $key, $e->getMessage()));
        }
    }

    #[Override]
    public function clear(): bool
    {
        try {
            $this->cacheStore->clear();

            return true;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return false;
        }
    }

    #[Override]
    public function has(string $key): bool
    {
        try {
            $cacheKey = CacheKey::create(key: $key);

            return $this->cacheStore->exists($cacheKey);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
     */
    #[Override]
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        $keys = $keys instanceof Traversable ? iterator_to_array($keys) : $keys;

        foreach ($keys as $key) {
            $result[$key] = $this->get(key: $key, default: $default);
        }

        return $result;
    }

    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $result = $this->cacheStore->read($cacheKey, $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordMetrics($startTime, 'miss');

                return $default;
            }

            if ($result->storedCacheRecord->isExpired($this->clock)) {
                $this->recordMetrics($startTime, 'miss');

                return $default;
            }

            $this->recordMetrics($startTime, 'hit');

            return $result->storedCacheRecord->value;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return $default;
        }
    }

    /**
     * @param  iterable<string, mixed>  $values
     */
    #[Override]
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        $values = $values instanceof Traversable ? iterator_to_array($values) : $values;

        foreach ($values as $key => $value) {
            $this->set(key: $key, value: $value, ttl: $ttl);
        }

        return true;
    }

    /**
     * @param  iterable<string>  $keys
     */
    #[Override]
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $keys instanceof Traversable ? iterator_to_array($keys) : $keys;

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    #[Override]
    public function delete(string $key): bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $this->cacheStore->forget($cacheKey);

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'delete',
            );

            return true;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return false;
        }
    }
}
