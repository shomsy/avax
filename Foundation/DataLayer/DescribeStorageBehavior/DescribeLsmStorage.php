<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

use InvalidArgumentException;

enum LsmStorageStrategy: string
{
    case LEVEL  = 'level';
    case TWIG   = 'twig';
    case MATRIX = 'matrix';
}

enum LsmCompactionTrigger: string
{
    case SIZE       = 'size';
    case WALL_CLOCK = 'wall_clock';
    case INTERVAL   = 'interval';
}

final readonly class DescribeLsmStorage
{
    public function __construct(
        public LsmStorageStrategy   $strategy,
        public LsmCompactionTrigger $compactionTrigger,
        public int                  $maxLevel,
        public int                  $bloomFilterFalsePositiveRate,
        public bool                 $compactionEnabled
    )
    {
        if ($this->maxLevel < 1) {
            throw new InvalidArgumentException(message: 'Max level must be at least 1.');
        }
        if ($this->bloomFilterFalsePositiveRate < 0 || $this->bloomFilterFalsePositiveRate > 100) {
            throw new InvalidArgumentException(message: 'Bloom filter false positive rate must be between 0 and 100.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'describes LSM tree storage strategy, compaction trigger, levels, and bloom filter config.';
    }

    public static function levelBased(int|null $maxLevel = null, int $bloomFpr = 1) : self
    {
        $maxLevel ??= 7;

        return new self(
            strategy                    : LsmStorageStrategy::LEVEL,
            compactionTrigger           : LsmCompactionTrigger::SIZE,
            maxLevel                    : $maxLevel,
            bloomFilterFalsePositiveRate: $bloomFpr,
            compactionEnabled           : true
        );
    }

    public function toMetadata() : array
    {
        return [
            'strategy'           => $this->strategy->value,
            'compaction_trigger' => $this->compactionTrigger->value,
            'max_level'          => $this->maxLevel,
            'bloom_filter_fpr'   => $this->bloomFilterFalsePositiveRate,
            'compaction_enabled' => $this->compactionEnabled,
        ];
    }
}