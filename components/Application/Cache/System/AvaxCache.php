<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
use Avax\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\DecideStaleValueCanBeServed;
use Avax\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\StaleValuePolicy;
use Avax\Cache\System\Capabilities\Lifecycle\RefreshCachedValues\RefreshPolicy;
use Avax\Cache\System\Capabilities\Lifecycle\RefreshCachedValues\ShouldRefreshCachedValue;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockStore;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockWasNotAcquired;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Flows\Protection\ProtectCacheSource\AcquireCacheStampedeLock;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\SystemClock;
use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;
use Traversable;

final class AvaxCache implements CacheContract
{
    private CacheTtl                      $ttlCalculator;
    private DecideStaleValueCanBeServed   $stalePolicyDecider;
    private ShouldRefreshCachedValue      $refreshDecider;
    private AcquireCacheStampedeLock|null $stampedeLock;
    private bool                          $stampedeProtectionEnabled;

    public function __construct(
        private readonly CacheStore        $store,
        private readonly Clock             $clock = new SystemClock(),
        private readonly CacheMetrics|null $metrics = null,
        StaleValuePolicy|null              $stalePolicy = null,
        RefreshPolicy|null                 $refreshPolicy = null,
        CacheLockStore|null                $lockStore = null,
        bool                               $stampedeProtection = false,
        private readonly int               $lockWaitTimeoutSeconds = 5,
        private readonly int               $lockTtlSeconds = 30,
        private readonly int               $refreshAheadWindowSeconds = 60
    )
    {
        $this->ttlCalculator      = new CacheTtl(clock: $this->clock);
        $this->stalePolicyDecider = new DecideStaleValueCanBeServed(
            policy: $stalePolicy ?? StaleValuePolicy::DO_NOT_SERVE_STALE
        );
        $this->refreshDecider     = new ShouldRefreshCachedValue(
            clock                    : $this->clock,
            policy                   : $refreshPolicy ?? RefreshPolicy::DO_NOT_REFRESH,
            refreshAheadWindowSeconds: $this->refreshAheadWindowSeconds
        );

        if ($stampedeProtection && $lockStore !== null) {
            $this->stampedeLock              = new AcquireCacheStampedeLock(
                lockStore         : $lockStore,
                waitTimeoutSeconds: $this->lockWaitTimeoutSeconds,
                lockTtlSeconds    : $this->lockTtlSeconds
            );
            $this->stampedeProtectionEnabled = true;
        } else {
            $this->stampedeProtectionEnabled = $stampedeProtection;
        }
    }

    public function remember(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        $startTime = hrtime(true);
        $cacheKey  = CacheKey::create(key: $key);
        $result    = $this->store->read(key: $cacheKey, clock: $this->clock);

        if ($result instanceof CacheStoreRecordWasFound) {
            $record    = $result->record;
            $lifecycle = $record->lifecycle;

            if ($this->shouldRefreshValue(lifecycle: $lifecycle)) {
                return $this->loadWithStampedeProtection(key: $key, ttl: $ttl, loader: $loader, staleValue: $record->value);
            }

            if ($lifecycle->isExpired(clock: $this->clock)) {
                if ($this->stalePolicyDecider->canServeStale()) {
                    $this->recordMetrics(
                        startTime: $startTime,
                        operation: 'stale_served',
                        key      : $cacheKey
                    );

                    $this->loadInBackground(key: $key, ttl: $ttl, loader: $loader);

                    return $record->value;
                }

                return $this->loadWithStampedeProtection(key: $key, ttl: $ttl, loader: $loader);
            }

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'hit',
                key      : $cacheKey
            );

            return $record->value;
        }

        return $this->loadWithStampedeProtection(key: $key, ttl: $ttl, loader: $loader);
    }

    private function shouldRefreshValue(CachedValueLifecycle $lifecycle) : bool
    {
        return $this->refreshDecider->shouldRefresh(lifecycle: $lifecycle);
    }

    private function loadWithStampedeProtection(string $key, int|DateInterval|null $ttl, callable $loader, mixed $staleValue = null) : mixed
    {
        if ($this->stampedeProtectionEnabled && $this->stampedeLock !== null) {
            return $this->protectedLoad(key: $key, ttl: $ttl, loader: $loader);
        }

        return $this->directLoad(key: $key, ttl: $ttl, loader: $loader);
    }

    private function protectedLoad(string $key, int|DateInterval|null $ttl, callable $loader) : mixed
    {
        try {
            $guard = $this->stampedeLock->acquire(key: $key);

            try {
                return $this->directLoad(key: $key, ttl: $ttl, loader: $loader);
            } finally {
                $guard->release();
            }
        } catch (CacheLockWasNotAcquired) {
            $cacheKey = CacheKey::create(key: $key);
            $result   = $this->store->read(key: $cacheKey, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->recordMetrics(
                    startTime: hrtime(true),
                    operation: 'stale_served',
                    key      : $cacheKey
                );

                return $result->record->value;
            }

            $this->recordMetrics(
                startTime: hrtime(true),
                operation: 'miss',
                key      : $cacheKey
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

    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey  = CacheKey::create(key: $key);
            $expiresAt = $this->ttlCalculator->calculateExpiresAt(ttl: $ttl, clock: $this->clock);

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt ?? $this->clock->now()->add(duration: Duration::ofSeconds(seconds: 86400)),
                clock    : $this->clock
            );

            $record = new StoredCacheRecord(
                value    : $value,
                lifecycle: $lifecycle
            );

            $this->store->write(key: $cacheKey, record: $record);

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'write',
                key      : $cacheKey,
                ttl      : $ttl instanceof DateInterval ? (int) $ttl->s : $ttl
            );

            return true;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return false;
        }
    }

    private function recordMetrics(
        int      $startTime,
        string   $operation,
        CacheKey $key,
        int|null $ttl = null
    ) : void
    {
        if ($this->metrics === null) {
            return;
        }

        $endTime             = hrtime(true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        match ($operation) {
            'hit'          => $this->metrics->recordHit(),
            'miss'         => $this->metrics->recordMiss(),
            'write'        => $this->metrics->recordWrite(),
            'delete'       => $this->metrics->recordDelete(),
            'stale_served' => $this->metrics->recordStaleServed(),
            default        => null,
        };

        $this->metrics->recordLatency(microseconds: $latencyMicroseconds);
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

    public function clear() : bool
    {
        try {
            $this->store->clear();

            return true;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return false;
        }
    }

    public function has(string $key) : bool
    {
        try {
            $cacheKey = CacheKey::create(key: $key);

            return $this->store->exists(key: $cacheKey);
        } catch (Throwable) {
            return false;
        }
    }

    public function getMultiple(iterable $keys, mixed $default = null) : iterable
    {
        $result = [];
        $keys   = $keys instanceof Traversable ? iterator_to_array($keys) : (is_array($keys) ? $keys : []);

        foreach ($keys as $key) {
            $result[$key] = $this->get(key: $key, default: $default);
        }

        return $result;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $result   = $this->store->read(key: $cacheKey, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordMetrics(
                    startTime: $startTime,
                    operation: 'miss',
                    key      : $cacheKey
                );

                return $default;
            }

            if ($result->record->isExpired(clock: $this->clock)) {
                $this->recordMetrics(
                    startTime: $startTime,
                    operation: 'miss',
                    key      : $cacheKey
                );

                return $default;
            }

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'hit',
                key      : $cacheKey
            );

            return $result->record->value;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return $default;
        }
    }

    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null) : bool
    {
        $values = $values instanceof Traversable ? iterator_to_array($values) : (is_array($values) ? $values : []);

        foreach ($values as $key => $value) {
            $this->set(key: $key, value: $value, ttl: $ttl);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys) : bool
    {
        $keys = $keys instanceof Traversable ? iterator_to_array($keys) : (is_array($keys) ? $keys : []);

        foreach ($keys as $key) {
            $this->delete(key: $key);
        }

        return true;
    }

    public function delete(string $key) : bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create(key: $key);
            $this->store->forget(key: $cacheKey);

            $this->recordMetrics(
                startTime: $startTime,
                operation: 'delete',
                key      : $cacheKey
            );

            return true;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return false;
        }
    }
}