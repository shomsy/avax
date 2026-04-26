<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

enum CapacityUnit: string
{
    case MB = 'MB';
    case GB = 'GB';
    case TB = 'TB';
}

final readonly class CapacityPlan
{
    public function __construct(
        public int $currentCapacityMb,
        public int $growthRateMb,
        public int $forecastMonths,
        public int $bufferPercent
    ) {}

    public static function standard(int $currentMb) : self
    {
        return new self(currentCapacityMb: $currentMb, growthRateMb: 1000, forecastMonths: 12, bufferPercent: 20);
    }

    public function describeResponsibility() : string
    {
        return 'plans data capacity growth and scaling requirements.';
    }

    public function planCapacity() : CapacityPlanResult
    {
        $projected = $this->currentCapacityMb + ($this->growthRateMb * $this->forecastMonths);
        $buffer    = (int) ($projected * ($this->bufferPercent / 100));
        $total     = $projected + $buffer;

        return new CapacityPlanResult(
            currentCapacityMb    : $this->currentCapacityMb,
            projectedCapacityMb  : $projected,
            requiredCapacityMb   : $total,
            recommendedShardCount: $this->calculateShardCount(capacityMb: $total)
        );
    }

    private function calculateShardCount(int $capacityMb) : int
    {
        if ($capacityMb < 10000) {
            return 1;
        }
        if ($capacityMb < 100000) {
            return 4;
        }
        if ($capacityMb < 500000) {
            return 8;
        }

        return 16;
    }

    public function toMetadata() : array
    {
        return [
            'current_capacity_mb' => $this->currentCapacityMb,
            'growth_rate_mb'      => $this->growthRateMb,
            'forecast_months'     => $this->forecastMonths,
            'buffer_percent'      => $this->bufferPercent,
        ];
    }
}

final readonly class CapacityPlanResult
{
    public function __construct(
        public int $currentCapacityMb,
        public int $projectedCapacityMb,
        public int $requiredCapacityMb,
        public int $recommendedShardCount
    ) {}
}