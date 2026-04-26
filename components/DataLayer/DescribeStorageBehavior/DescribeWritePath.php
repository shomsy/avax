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
            throw new InvalidArgumentException(message: 'Batch size must be at least 1.');
        }
        if ($this->flushIntervalMs < 0) {
            throw new InvalidArgumentException(message: 'Flush interval must be non-negative.');
        }
    }

    public static function direct(bool $durable = true) : self
    {
        return new self(strategy: WritePathStrategy::DIRECT, batchSize: 1, flushIntervalMs: 0, durable: $durable);
    }

    public static function batch(int|null $batchSize = null, int $flushIntervalMs = 100) : self
    {
        $batchSize ??= 1000;

        return new self(strategy: WritePathStrategy::BATCH, batchSize: $batchSize, flushIntervalMs: $flushIntervalMs, durable: true);
    }

    public function describeResponsibility() : string
    {
        return 'describes write path strategy, batch size, flush interval, and durability.';
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