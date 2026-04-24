<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

use InvalidArgumentException;

enum RecoveryGuaranteeLevel: string
{
    case NONE        = 'none';
    case BEST_EFFORT = 'best_effort';
    case DURABLE     = 'durable';
    case STRICT      = 'strict';

    public function guaranteesDurability() : bool
    {
        return in_array($this, [self::DURABLE, self::STRICT], true);
    }

    public function requiresWriteAheadLog() : bool
    {
        return $this === self::STRICT;
    }
}

final readonly class RecoveryGuarantee
{
    public function __construct(
        public RecoveryGuaranteeLevel $level,
        public int                    $recoveryPointMinutes,
        public bool                   $allowDataLossTolerance
    )
    {
        if ($this->recoveryPointMinutes < 0) {
            throw new InvalidArgumentException('Recovery point minutes cannot be negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'specifies recovery guarantee level, RPO in minutes, and data-loss tolerance.';
    }

    public static function strict() : self
    {
        return new self(RecoveryGuaranteeLevel::STRICT, 0, false);
    }

    public static function durable() : self
    {
        return new self(RecoveryGuaranteeLevel::DURABLE, 5, false);
    }

    public static function bestEffort() : self
    {
        return new self(RecoveryGuaranteeLevel::BEST_EFFORT, 60, true);
    }

    public function toMetadata() : array
    {
        return [
            'level'                  => $this->level->value,
            'recovery_point_minutes' => $this->recoveryPointMinutes,
            'allow_data_loss'        => $this->allowData - lossTolerance,
        ];
    }
}