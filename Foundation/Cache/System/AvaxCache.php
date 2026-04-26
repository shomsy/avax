<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueState;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods\CacheTtl;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\StaleValuePolicies\DecideStaleValueCanBeServed;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\StaleValuePolicies\StaleValuePolicy;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\SystemClock;
use DateInterval;
use Throwable;
use Traversable;

final class AvaxCache implements CacheContract
{
    private CacheTtl                    $ttlCalculator;
    private DecideStaleValueCanBeServed $stalePolicyDecider;

    public function __construct(
        private readonly CacheStore        $store,
        private readonly Clock             $clock = new SystemClock(),
        private readonly ?CacheMetrics     $metrics = null,
        private readonly ?StaleValuePolicy $stalePolicy = null
    )
    {
        $this->ttlCalculator      = new CacheTtl($this->clock);
        $this->stalePolicyDecider = new DecideStaleValueCanBeServed(
            policy: $stalePolicy ?? StaleValuePolicy::DO_NOT_SERVE_STALE
        );
    }

    public function remember(string $key, null|int|DateInterval $ttl, callable $loader) : mixed
    {
        $cacheKey = CacheKey::create($key);
        $result   = $this->store->read($cacheKey, $this->clock);

        if ($result instanceof CacheStoreRecordWasFound && ! $result->record->lifecycle->isExpired($this->clock)) {
            $this->recordMetrics(
                startTime: hrtime(true),
                operation: 'hit',
                key      : $cacheKey
            );

            return $result->record->value;
        }

        $value = $loader();

        $this->set($key, $value, $ttl);

        return $value;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create($key);
            $result   = $this->store->read($cacheKey, $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordMetrics(
                    startTime: $startTime,
                    operation: 'miss',
                    key      : $cacheKey
                );

                return $default;
            }

            if ($result->record->isExpired($this->clock)) {
                $this->recordMetrics(
                    startTime: $startTime,
                    operation: 'miss',
                    key      : $cacheKey
                );

                return $default;
            }

            $state = $this->determineState($result->record->lifecycle);

            if ($state === CachedValueState::STALE
                && ! $this->stalePolicyDecider->canServeStale()) {
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

    private function recordMetrics(
        int      $startTime,
        string   $operation,
        CacheKey $key,
        ?int     $ttl = null
    ) : void
    {
        if ($this->metrics === null) {
            return;
        }

        $endTime = hrtime(true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        match ($operation) {
            'hit'    => $this->metrics->recordHit(),
            'miss'   => $this->metrics->recordMiss(),
            'write'  => $this->metrics->recordWrite(),
            'delete' => $this->metrics->recordDelete(),
            default  => null,
        };

        $this->metrics->recordLatency($latencyMicroseconds);
    }

    private function determineState(CachedValueLifecycle $lifecycle) : CachedValueState
    {
        if ($lifecycle->isExpired($this->clock)) {
            return CachedValueState::EXPIRED;
        }

        return CachedValueState::ACTIVE;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null) : bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey  = CacheKey::create($key);
            $expiresAt = $this->ttlCalculator->calculateExpiresAt($ttl, $this->clock);

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt ?? $this->clock->now()->add(
                Duration::ofSeconds(86400)
            ),
                clock    : $this->clock
            );

            $record = new StoredCacheRecord(
                value    : $value,
                lifecycle: $lifecycle
            );

            $this->store->write($cacheKey, $record);

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
            $cacheKey = CacheKey::create($key);

            return $this->store->exists($cacheKey);
        } catch (Throwable) {
            return false;
        }
    }

    public function getMultiple(iterable $keys, mixed $default = null) : iterable
    {
        $result = [];
        $keys   = $keys instanceof Traversable ? iterator_to_array($keys) : (is_array($keys) ? $keys : []);

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null) : bool
    {
        $values = $values instanceof Traversable ? iterator_to_array($values) : (is_array($values) ? $values : []);

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys) : bool
    {
        $keys = $keys instanceof Traversable ? iterator_to_array($keys) : (is_array($keys) ? $keys : []);

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function delete(string $key) : bool
    {
        $startTime = hrtime(true);

        try {
            $cacheKey = CacheKey::create($key);
            $this->store->forget($cacheKey);

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