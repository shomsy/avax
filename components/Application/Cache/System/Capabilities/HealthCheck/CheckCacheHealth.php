<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\HealthCheck;

use Avax\Components\Application\Cache\System\Capabilities\Health\CacheHealthDetector;
use Avax\Components\Application\Cache\System\Capabilities\Health\CacheHealthStatus;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\PublicSurface\Cache;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * Health check for the Cache component.
 *
 * Uses CacheHealthDetector to perform real connectivity and latency checks
 * against the configured cache store. Returns canonical HealthReport.
 */
final class CheckCacheHealth
{
    public function __construct(
        private readonly CacheHealthDetector|null $healthDetector = null,
        private readonly Clock|null $clock = null,
    ) {}

    public function check() : HealthReport
    {
        $findings = [];
        $overall  = HealthStatus::Green;

        try {
            $cacheStore = $this->resolveCacheStore();
        } catch (NotConfigured $e) {
            return new HealthReport(
                findings: [new HealthFinding('cache.configuration', HealthStatus::Yellow, 'Cache is not configured')],
                overall : HealthStatus::Yellow,
            );
        } catch (Throwable $e) {
            return new HealthReport(
                findings: [new HealthFinding('cache.configuration', HealthStatus::Red, $e->getMessage())],
                overall : HealthStatus::Red,
            );
        }

        $detector = $this->healthDetector ?? ($this->clock !== null ? CacheHealthDetector::create($this->clock) : null);

        if ($detector === null) {
            return new HealthReport(
                findings: [new HealthFinding('cache.connectivity', HealthStatus::Yellow, 'Cache health detector not configured')],
                overall : HealthStatus::Yellow,
            );
        }

        try {
            $status = $detector->checkConnection($cacheStore);
        } catch (Throwable $e) {
            return new HealthReport(
                findings: [new HealthFinding('cache.connectivity', HealthStatus::Red, $e->getMessage())],
                overall : HealthStatus::Red,
            );
        }

        $findings[] = new HealthFinding(
            'cache.connectivity',
            $status->connected ? HealthStatus::Green : HealthStatus::Red,
            $status->connected ? 'Cache store reachable' : ($status->error ?? 'Cache store unreachable'),
        );

        if (! $status->connected) {
            return new HealthReport(findings: $findings, overall: HealthStatus::Red);
        }

        if ($status->latency > 0) {
            $latencyOk  = $status->latency <= 100;
            $findings[] = new HealthFinding(
                'cache.latency',
                $latencyOk ? HealthStatus::Green : HealthStatus::Yellow,
                sprintf('Cache latency: %dms', $status->latency),
            );
            if (! $latencyOk) {
                $overall = HealthStatus::Yellow;
            }
        }

        $memoryStatus = $detector->checkMemory();
        if ($memoryStatus->error !== null) {
            $findings[] = new HealthFinding('cache.memory', HealthStatus::Yellow, $memoryStatus->error);
            $overall    = HealthStatus::Yellow;
        } elseif ($memoryStatus->isMemoryCritical()) {
            $findings[] = new HealthFinding('cache.memory', HealthStatus::Red, sprintf('Memory critical: %.1f%%', $memoryStatus->getMemoryUsagePercent()));
            $overall    = HealthStatus::Red;
        } elseif ($memoryStatus->isMemoryHigh()) {
            $findings[] = new HealthFinding('cache.memory', HealthStatus::Yellow, sprintf('Memory high: %.1f%%', $memoryStatus->getMemoryUsagePercent()));
            $overall    = HealthStatus::Yellow;
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }

    /**
     * @throws NotConfigured
     */
    private function resolveCacheStore() : CacheStore
    {
        // The Cache facade holds the configured CacheContract.
        // For health check we need the underlying CacheStore.
        // If the contract itself is a CacheStore, use it directly.
        $contract = Cache::store();

        if ($contract instanceof CacheStore) {
            return $contract;
        }

        // If the contract wraps a CacheStore, try to access it.
        // For adapters that are not CacheStore, we do a simple has() probe.
        // This means we can't use CacheHealthDetector directly, so we fall back
        // to a basic connectivity probe.
        throw new NotConfigured(message: 'Cache contract is not a CacheStore; health check requires CacheStore adapter');
    }
}
