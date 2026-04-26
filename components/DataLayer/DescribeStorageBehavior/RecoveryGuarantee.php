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
            throw new InvalidArgumentException(message: 'Recovery point minutes cannot be negative.');
        }
    }

    public static function strict() : self
    {
        return new self(level: RecoveryGuaranteeLevel::STRICT, recoveryPointMinutes: 0, allowDataLossTolerance: false);
    }

    public static function durable() : self
    {
        return new self(level: RecoveryGuaranteeLevel::DURABLE, recoveryPointMinutes: 5, allowDataLossTolerance: false);
    }

    public static function bestEffort() : self
    {
        return new self(level: RecoveryGuaranteeLevel::BEST_EFFORT, recoveryPointMinutes: 60, allowDataLossTolerance: true);
    }

    public function describeResponsibility() : string
    {
        return 'specifies recovery guarantee level, RPO in minutes, and data-loss tolerance.';
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