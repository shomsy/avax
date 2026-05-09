<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\EstimateStorageGrowth;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;

/**
 * Estimates storage growth over retention window.
 *
 * @experimental V3 labs
 */
final class EstimateStorageGrowth
{
    /**
     * @return array{
     *     daily_growth_bytes: int,
     *     daily_growth_human: string,
     *     retention_bytes: int,
     *     retention_human: string,
     *     yearly_growth_bytes: int,
     *     yearly_growth_human: string,
     * }
     */
    public function execute(CapacityModel $model) : array
    {
        $dailyGrowth = $model->estimatedDailyStorageGrowth();

        return [
            'daily_growth_bytes'  => $dailyGrowth,
            'daily_growth_human'  => $this->humanBytes($dailyGrowth),
            'retention_bytes'     => $model->storage->totalRetentionBytes(),
            'retention_human'     => $model->storage->totalRetentionHumanReadable(),
            'yearly_growth_bytes' => $dailyGrowth * 365,
            'yearly_growth_human' => $this->humanBytes($dailyGrowth * 365),
        ];
    }

    private function humanBytes(int $bytes) : string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i     = 0;
        $value = (float) $bytes;

        while ( $value >= 1024 && $i < count($units) - 1 ) {
            $value /= 1024;
            $i++;
        }

        return round($value, 2) . ' ' . $units[$i];
    }
}
