<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

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
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;
use Traversable;

final class AvaxCache implements CacheContract
{
    private readonly CacheStore $cacheStore;

    private readonly Clock $clock;

    private readonly CacheMetrics|null $cacheMetrics;

    private readonly CacheTtl $cacheTtl;

    private readonly DecideStaleValueCanBeServed $decideStaleValueCanBeServed;

    private readonly ShouldRefreshCachedValue $shouldRefreshCachedValue;

    private AcquireCacheStampedeLock|null $acquireCacheStampedeLock = null;

    private bool $stampedeProtectionEnabled;

    public function __construct(
        CacheStore            $store,
        Clock                 $clock = new SystemClock(),
        CacheMetrics|null     $metrics = null,
        StaleValuePolicy|null $stalePolicy = null,
        RefreshPolicy|null    $refreshPolicy = null,
        CacheLockStore|null   $cacheLockStore = null,
        bool                  $stampedeProtection = false,
        private readonly int  $lockWaitTimeoutSeconds = 5,
        private readonly int  $lockTtlSeconds = 30,
        private readonly int  $refreshAheadWindowSeconds = 60,
    )
    {
        $this->cacheStore                  = $store;
        $this->clock                       = $clock;
        $this->cacheMetrics                = $metrics;
        $this->cacheTtl                    = new CacheTtl(clock: $this->clock);
        $this->decideStaleValueCanBeServed = new DecideStaleValueCanBeServed(
            staleValuePolicy: $stalePolicy ?? StaleValuePolicy::DO_NOT_SERVE_STALE,
        );
        $this->shouldRefreshCachedValue    = new ShouldRefreshCachedValue(
            clock                    : $this->clock,
            refreshPolicy            : $refreshPolicy ?? RefreshPolicy::DO_NOT_REFRESH,
            refreshAheadWindowSeconds: $this->refreshAheadWindowSeconds,
        );

        if ($stampedeProtection && $cacheLockStore instanceof CacheLockStore) {
            $this->acquireCacheStampedeLock = new AcquireCacheStampedeLock(
                cacheLockStore    : $cacheLockStore,
                waitTimeoutSeconds: $this->lockWaitTimeoutSeconds,
                lockTtlSeconds    : $this->lockTtlSeconds,
            );
            $this->stampedeProtectionEnabled = true;
        } else {
            $this->stampedeProtectionEnabled = $stampedeProtection;
        }
    }

    #[Override]
    public function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        $startTime = hrtime(true);
        $cacheKey  = CacheKey::create(key: $key);
        $result = $this->cacheStore->read(key: $cacheKey, clock: $this->clock);

        if ($result instanceof CacheStoreRecordWasFound) {
            $record    = $result->record;
            $lifecycle = $record->lifecycle;

            if ($this->shouldRefreshValue(lifecycle: $lifecycle)) {
                return $this->loadWithStampedeProtection(key: $key, ttl: $ttl, loader: $loader);
            }

            if ($lifecycle->isExpired(clock: $this->clock)) {
                if ($this->decideStaleValueCanBeServed->canServeStale()) {
                    $this->recordMetrics(
                        startTime: $startTime,
                        operation: 'stale_served',
                    );

                    $this->loadInBackground(key: $key, ttl: $ttl, loader: $loader);

                    return $record->value;
                }

                return $this->loadWithStampedeProtection(key: $key, ttl: $ttl, loader: $loader);
            }

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'hit',
            );

            return $record->value;
        }

        return $this->loadWithStampedeProtection(key: $key, ttl: $ttl, loader: $loader);
    }

    private function shouldRefreshValue(CachedValueLifecycle $lifecycle) : bool
    {
        return $this->shouldRefreshCachedValue->shouldRefresh(lifecycle: $lifecycle);
    }

    private function loadWithStampedeProtection(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        if ($this->stampedeProtectionEnabled && $this->acquireCacheStampedeLock !== null) {
            return $this->protectedLoad(key: $key, ttl: $ttl, loader: $loader);
        }

        return $this->directLoad(key: $key, ttl: $ttl, loader: $loader);
    }

    private function protectedLoad(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        try {
            $guard = $this->acquireCacheStampedeLock->acquire(key: $key);

            try {
                return $this->directLoad(key: $key, ttl: $ttl, loader: $loader);
            } finally {
                $guard->release();
            }
        } catch (CacheLockWasNotAcquired) {
            $cacheKey = CacheKey::create(key: $key);
            $result = $this->cacheStore->read(key: $cacheKey, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->recordMetrics(
                    startTime: hrtime(true),
                    operation: 'stale_served',
                );

                return $result->record->value;
            }

            $this->recordMetrics(
                startTime: hrtime(true),
                operation: 'miss',
            );

            return null;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function directLoad(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        $value = $loader();
        $this->set(key: $key, value: $value, ttl: $ttl);

        return $value;
    }

    /**
     * @throws InvalidArgumentException
     */

    #[Override]
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey  = CacheKey::create(key: $key);
            $expiresAt = $this->cacheTtl->calculateExpiresAt(ttl: $ttl, clock: $this->clock);

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt ?? $this->clock->now()->add(duration: Duration::ofSeconds(seconds: 86400)),
                clock    : $this->clock,
            );

            $storedCacheRecord = new StoredCacheRecord(
                value    : $value,
                lifecycle: $lifecycle,
            );

            $this->cacheStore->write(key: $cacheKey, record: $storedCacheRecord);

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
        int    $startTime,
        string $operation,
    ) : void
    {
        if ($this->cacheMetrics === null) {
            return;
        }

        $endTime             = hrtime(true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        match ($operation) {
            'hit'          => $this->cacheMetrics->recordHit(),
            'miss'         => $this->cacheMetrics->recordMiss(),
            'write'        => $this->cacheMetrics->recordWrite(),
            'delete'       => $this->cacheMetrics->recordDelete(),
            'stale_served' => $this->cacheMetrics->recordStaleServed(),
            default        => null,
        };

        $this->cacheMetrics->recordLatency(microseconds: $latencyMicroseconds);
    }

    private function loadInBackground(string $key, int|DateInterval|null $ttl, callable $loader) : void
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        try {
            $this->set(key: $key, value: $loader(), ttl: $ttl);
        } catch (Throwable) {
        }
    }

    #[Override]
    public function clear() : bool
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
    public function has(string $key) : bool
    {
        try {
            $cacheKey = CacheKey::create(key: $key);

            return $this->cacheStore->exists(key: $cacheKey);
        } catch (Throwable) {
            return false;
        }
    }

    #[Override]
    public function getMultiple(iterable $keys, mixed $default = null) : iterable
    {
        $result = [];
        $keys = $keys instanceof Traversable ? iterator_to_array($keys) : $keys;

        foreach ($keys as $key) {
            $result[$key] = $this->get(key: $key, default: $default);
        }

        return $result;
    }

    #[Override]
    public function get(string $key, mixed $default = null) : mixed
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $result = $this->cacheStore->read(key: $cacheKey, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordMetrics(
                    startTime: $startTime,
                    operation: 'miss',
                );

                return $default;
            }

            if ($result->record->isExpired(clock: $this->clock)) {
                $this->recordMetrics(
                    startTime: $startTime,
                    operation: 'miss',
                );

                return $default;
            }

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'hit',
            );

            return $result->record->value;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return $default;
        }
    }

    #[Override]
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null) : bool
    {
        $values = $values instanceof Traversable ? iterator_to_array($values) : $values;

        foreach ($values as $key => $value) {
            $this->set(key: $key, value: $value, ttl: $ttl);
        }

        return true;
    }

    #[Override]
    public function deleteMultiple(iterable $keys) : bool
    {
        $keys = $keys instanceof Traversable ? iterator_to_array($keys) : $keys;

        foreach ($keys as $key) {
            $this->delete(key: $key);
        }

        return true;
    }

    #[Override]
    public function delete(string $key) : bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $this->cacheStore->forget(key: $cacheKey);

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
