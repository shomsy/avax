<?php

declare(strict_types=1);

namespace Avax\Components\ResourceGovernor\System\PublicSurface;

use Avax\Components\ResourceGovernor\System\Capabilities\Memory\MemoryBudget;
use Avax\Components\ResourceGovernor\System\Capabilities\Memory\MemorySnapshot;
use Stringable;

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
    public static function configure(array $config) : void
    {
        self::$memoryBudget = MemoryBudget::fromString(
            worker : $config['worker_memory'] ?? '256M',
            request: $config['request_memory'] ?? '32M',
        );
    }

    public static function onRequestStart() : void
    {
        self::$requestCount++;
        self::$totalMemoryStart = memory_get_usage(true);
    }

    public static function onRequestEnd() : void
    {
        $endMemory = memory_get_usage(true);
        $delta = $endMemory - self::$totalMemoryStart;

        self::$snapshots[] = new MemorySnapshot(
            requestNumber: self::$requestCount,
            memoryUsed   : $delta,
            workerMemory : memory_get_usage(true),
        );

        if (count(self::$snapshots) > 1000) {
            array_shift(self::$snapshots);
        }
    }

    public static function check() : bool
    {
        $budget = self::$memoryBudget;

        if (! $budget instanceof MemoryBudget) {
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

    public static function report() : ResourceReport
    {
        return new ResourceReport(
            requestCount: self::$requestCount,
            workerMemory: self::workerMemory(),
            workerLimit : self::workerLimit(),
            nearLimit   : self::isNearLimit(),
            trend       : self::calculateTrend(),
        );
    }

    public static function workerMemory() : int
    {
        return memory_get_usage(true);
    }

    public static function workerLimit() : int
    {
        return self::$memoryBudget->workerLimit ?? 0;
    }

    public static function isNearLimit(float $threshold = 0.8) : bool
    {
        $current = self::workerMemory();
        $limit = self::workerLimit();

        if ($limit === 0) {
            return false;
        }

        return ($current / $limit) >= $threshold;
    }

    private static function calculateTrend() : float
    {
        if (count(self::$snapshots) < 10) {
            return 0.0;
        }

        $recent = array_slice(self::$snapshots, -10);
        $total = array_sum(array_map(static fn (MemorySnapshot $memorySnapshot) : int => $memorySnapshot->memoryUsed, $recent));

        return $total / count($recent);
    }
}

final readonly class ResourceReport implements Stringable
{
    public function __construct(
        public int $requestCount,
        public int $workerMemory,
        public int $workerLimit,
        public bool $nearLimit,
        public float $trend,
    ) {}

    /**
     * @return array{request_count: int, worker_memory_mb: float, worker_limit_mb: float, near_limit: bool,
     *                              trend_mb_per_request: float}
     */
    public function toArray() : array
    {
        return [
            'request_count'    => $this->requestCount,
            'worker_memory_mb' => $this->workerMemory / 1024 / 1024,
            'worker_limit_mb'  => $this->workerLimit / 1024 / 1024,
            'near_limit'       => $this->nearLimit,
            'trend_mb_per_request' => $this->trend / 1024 / 1024,
        ];
    }

    public function __toString() : string
    {
        return sprintf(
            'Worker memory: %.1fMB / %.1fMB | Requests: %d | Near limit: %s | Trend: %.2fMB/req',
            $this->workerMemory / 1024 / 1024,
            $this->workerLimit / 1024 / 1024,
            $this->requestCount,
            $this->nearLimit ? 'YES' : 'NO',
            $this->trend / 1024 / 1024,
        );
    }
}
