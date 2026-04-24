<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

use InvalidArgumentException;

enum WritePathStrategy: string
{
    case DIRECT   = 'direct';
    case BUFFERED = 'buffered';
    case ASYNC    = 'async';
    case BATCH    = 'batch';
}

final readonly class DescribeWritePath
{
    public function __construct(
        public WritePathStrategy $strategy,
        public int               $batchSize,
        public int               $flushIntervalMs,
        public bool              $durable
    )
    {
        if ($this->batchSize < 1) {
            throw new InvalidArgumentException('Batch size must be at least 1.');
        }
        if ($this->flushIntervalMs < 0) {
            throw new InvalidArgumentException('Flush interval must be non-negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'describes write path strategy, batch size, flush interval, and durability.';
    }

    public static function direct(bool $durable = true) : self
    {
        return new self(WritePathStrategy::DIRECT, 1, 0, $durable);
    }

    public static function batch(int $batchSize = 1000, int $flushIntervalMs = 100) : self
    {
        return new self(WritePathStrategy::BATCH, $batchSize, $flushIntervalMs, true);
    }

    public function shouldFlush(int $bufferedWrites) : bool
    {
        return $bufferedWrites >= $this->batchSize;
    }

    public function toMetadata() : array
    {
        return [
            'strategy'          => $this->strategy->value,
            'batch_size'        => $this->batchSize,
            'flush_interval_ms' => $this->flushIntervalMs,
            'durable'           => $this->durable,
        ];
    }
}