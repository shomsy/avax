<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Storage;

/**
 * Storage growth model.
 *
 * @experimental V3 labs
 */
final readonly class StorageGrowth
{
    public function __construct(
        public int $growthPerDay,
        public int $averageRecordSizeBytes,
        public int $retentionDays,
    ) {}

    /**
     * Estimated total storage in human-readable format.
     */
    public function totalRetentionHumanReadable() : string
    {
        $bytes = $this->totalRetentionBytes();

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

    /**
     * Estimated total storage over retention period in bytes.
     */
    public function totalRetentionBytes() : int
    {
        return $this->dailyGrowthBytes() * $this->retentionDays;
    }

    /**
     * Estimated daily storage growth in bytes.
     */
    public function dailyGrowthBytes() : int
    {
        return $this->growthPerDay * $this->averageRecordSizeBytes;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->growthPerDay < 0) {
            $errors[] = 'growth_per_day must be non-negative.';
        }

        if ($this->averageRecordSizeBytes <= 0) {
            $errors[] = 'average_record_size_bytes must be positive.';
        }

        if ($this->retentionDays <= 0) {
            $errors[] = 'retention_days must be positive.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
