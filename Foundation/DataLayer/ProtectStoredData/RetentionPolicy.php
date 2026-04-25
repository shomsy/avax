<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;

enum RetentionPeriodUnit: string
{
    case DAYS   = 'days';
    case WEEKS  = 'weeks';
    case MONTHS = 'months';
    case YEARS  = 'years';
}

final readonly class RetentionPolicy
{
    public function __construct(
        public int                 $period,
        public RetentionPeriodUnit $unit,
        public bool                $archiveAfter,
        public bool                $permanent
    )
    {
        if ($this->period < 0) {
            throw new InvalidArgumentException(message: 'Retention period cannot be negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'records data retention period, archive behavior, and permanent retention flags.';
    }

    public static function standard() : self
    {
        return new self(period: 7, unit: RetentionPeriodUnit::YEARS, archiveAfter: true, permanent: false);
    }

    public static function compliant() : self
    {
        return new self(period: 10, unit: RetentionPeriodUnit::YEARS, archiveAfter: true, permanent: false);
    }

    public static function permanent() : self
    {
        return new self(period: 0, unit: RetentionPeriodUnit::YEARS, archiveAfter: false, permanent: true);
    }

    public function calculateRetentionDays() : int
    {
        if ($this->permanent) {
            return PHP_INT_MAX;
        }

        return match ($this->unit) {
            RetentionPeriodUnit::DAYS   => $this->period,
            RetentionPeriodUnit::WEEKS  => $this->period * 7,
            RetentionPeriodUnit::MONTHS => $this->period * 30,
            RetentionPeriodUnit::YEARS  => $this->period * 365,
        };
    }

    public function shouldArchive(int $ageDays) : bool
    {
        return $this->archiveAfter && $ageDays > $this->calculateRetentionDays();
    }

    public function toMetadata() : array
    {
        return [
            'period'         => $this->period,
            'unit'           => $this->unit->value,
            'archive_after'  => $this->archiveAfter,
            'permanent'      => $this->permanent,
            'retention_days' => $this->calculateRetentionDays(),
        ];
    }
}