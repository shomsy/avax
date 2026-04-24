<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

use InvalidArgumentException;

enum WriteAheadLogMode: string
{
    case SYNC         = 'sync';
    case ASYNC        = 'async';
    case GROUP_COMMIT = 'group_commit';
}

final readonly class WriteAheadLogBehavior
{
    public function __construct(
        public WriteAheadLogMode $mode,
        public int               $flushIntervalMs,
        public int               $maxBufferSize,
        public bool              $enabled
    )
    {
        if ($this->flushIntervalMs < 0) {
            throw new InvalidArgumentException('Flush interval must be non-negative.');
        }
        if ($this->maxBufferSize < 1) {
            throw new InvalidArgumentException('Max buffer size must be at least 1.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'configures write-ahead log mode, flush interval, and buffer size.';
    }

    public static function sync() : self
    {
        return new self(WriteAheadLogMode::SYNC, 0, 1, true);
    }

    public static function async(int $flushIntervalMs = 100) : self
    {
        return new self(WriteAheadLogMode::ASYNC, $flushIntervalMs, 10000, true);
    }

    public static function groupCommit(int|null $flushIntervalMs = null, int $maxBufferSize = 1000) : self
    {
        $flushIntervalMs ??= 50;

        return new self(WriteAheadLogMode::GROUP_COMMIT, $flushIntervalMs, $maxBufferSize, true);
    }

    public function shouldFlush(int $bufferedItems) : bool
    {
        return $this->enabled && ($bufferedItems >= $this->maxBufferSize);
    }

    public function toMetadata() : array
    {
        return [
            'mode'              => $this->mode->value,
            'flush_interval_ms' => $this->flushIntervalMs,
            'max_buffer_size'   => $this->maxBufferSize,
            'enabled'           => $this->enabled,
        ];
    }
}