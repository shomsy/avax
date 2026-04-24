<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

use InvalidArgumentException;

enum InvalidationStrategy: string
{
    case IMMEDIATE  = 'immediate';
    case DELAYED    = 'delayed';
    case LAZY       = 'lazy';
    case GENERATION = 'generation';
}

final readonly class InvalidateReadCache
{
    public function __construct(
        public InvalidationStrategy $strategy,
        public int                  $delayMs,
        public bool                 $cascade
    )
    {
        if ($this->delayMs < 0) {
            throw new InvalidArgumentException('Delay must be non-negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'invalidates read cache using immediate, delayed, lazy, or generation-based invalidation.';
    }

    public static function immediate() : self
    {
        return new self(InvalidationStrategy::IMMEDIATE, 0, true);
    }

    public static function lazy() : self
    {
        return new self(InvalidationStrategy::LAZY, 0, false);
    }

    public static function delayed(int $delayMs = 1000) : self
    {
        return new self(InvalidationStrategy::DELAYED, $delayMs, false);
    }

    public function shouldInvalidate(string $cacheKey, array $recentWrites) : bool
    {
        if ($this->strategy === InvalidationStrategy::IMMEDIATE) {
            return true;
        }

        if ($this->strategy === InvalidationStrategy::GENERATION) {
            return true;
        }

        return false;
    }

    public function toMetadata() : array
    {
        return [
            'strategy' => $this->strategy->value,
            'delay_ms' => $this->delayMs,
            'cascade'  => $this->cascade,
        ];
    }
}