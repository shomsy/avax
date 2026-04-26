<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

use InvalidArgumentException;

final readonly class DescribeBTreeStorage
{
    public function __construct(
        public int  $order,
        public int  $fanout,
        public bool $compressionEnabled,
        public int  $maxDepth
    )
    {
        if ($this->order < 2) {
            throw new InvalidArgumentException(message: 'B-tree order must be at least 2.');
        }
        if ($this->fanout < 2) {
            throw new InvalidArgumentException(message: 'Fanout must be at least 2.');
        }
        if ($this->maxDepth < 1) {
            throw new InvalidArgumentException(message: 'Max depth must be at least 1.');
        }
    }

    public static function standard(int $order = 1024) : self
    {
        return new self(order: $order, fanout: $order - 1, compressionEnabled: false, maxDepth: 4);
    }

    public static function compressed(int $order = 256) : self
    {
        return new self(order: $order, fanout: $order - 1, compressionEnabled: true, maxDepth: 4);
    }

    public function describeResponsibility() : string
    {
        return 'describes B-tree storage order, fanout, compression, and max depth.';
    }

    public function estimatePages(int $keys) : float
    {
        $keysPerPage = $this->fanout;

        return ceil($keys / $keysPerPage);
    }

    public function toMetadata() : array
    {
        return [
            'order'               => $this->order,
            'fanout'              => $this->fanout,
            'compression_enabled' => $this->compressionEnabled,
            'max_depth'           => $this->maxDepth,
        ];
    }
}