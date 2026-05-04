<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ResourceGovernance\PublicSurface;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Stringable;
use Avax\Framework\System\Capabilities\ResourceGovernance\Capabilities\Memory\MemoryBudget;
use Avax\Framework\System\Capabilities\ResourceGovernance\Capabilities\Memory\MemorySnapshot;

final class ResourceGovernor
{
    private static ?MemoryBudget $memoryBudget = null;

    /**
     * @var list<MemorySnapshot>
     */
    private static array $snapshots = [];

    private static int $requestCount = 0;

    private static int $totalMemoryStart = 0;

    /**
     * @param array{worker_memory?: string, request_memory?: string} $config
     */
    public static function configure(array $config): void
    {
        self::$memoryBudget = MemoryBudget::fromString(
            worker: $config['worker_memory'] ?? '256M',
            request: $config['request_memory'] ?? '32M',
        );
    }

    public static function onRequestStart(): void
    {
        self::$requestCount++;
        self::$totalMemoryStart = memory_get_usage(true);
    }

    public static function onRequestEnd(): void
    {
        $endMemory = memory_get_usage(true);
        $delta = $endMemory - self::$totalMemoryStart;

        self::$snapshots[] = new MemorySnapshot(
            requestNumber: self::$requestCount,
            memoryUsed: $delta,
            workerMemory: memory_get_usage(true),
        );

        if (count(self::$snapshots) > 1000) {
            array_shift(self::$snapshots);
        }
    }

    public static function check(): bool
    {
        $budget = self::$memoryBudget;

        if (!$budget instanceof MemoryBudget) {
            return true;
        }

        if ($budget->requestLimit > 0) {
            $current = memory_get_usage(true);

            if ($current > $budget->requestLimit) {
                return false;
            }
        }

        return true;
    }

    public static function report(): ResourceReport
    {
        return new ResourceReport(
            requestCount: self::$requestCount,
            workerMemory: self::workerMemory(),
            workerLimit: self::workerLimit(),
            nearLimit: self::isNearLimit(),
            trend: self::calculateTrend(),
        );
    }

    public static function workerMemory(): int
    {
        return memory_get_usage(true);
    }

    public static function workerLimit(): int
    {
        return self::$memoryBudget->workerLimit ?? 0;
    }

    public static function isNearLimit(float $threshold = 0.8): bool
    {
        $current = self::workerMemory();
        $limit = self::workerLimit();

        if ($limit === 0) {
            return false;
        }

        return ($current / $limit) >= $threshold;
    }

    private static function calculateTrend(): float
    {
        if (count(self::$snapshots) < 10) {
            return 0.0;
        }

        $recent = array_slice(self::$snapshots, -10);
        $total = array_sum(array_map(static fn(MemorySnapshot $memorySnapshot): int => $memorySnapshot->memoryUsed, $recent));

        return $total / count($recent);
    }
}
