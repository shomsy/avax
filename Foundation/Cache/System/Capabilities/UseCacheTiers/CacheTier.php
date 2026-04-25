<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

final readonly class CacheTier
{
    public function __construct(
        public CacheTierName $name,
        public int           $priority,
        public int           $maxSize = 1000,
        public int           $ttlDefault = 3600
    ) {}

    public static function l1(string $name = 'L1', int $maxSize = 1000) : self
    {
        return new self(
            name      : CacheTierName::from($name),
            priority  : 1,
            maxSize   : $maxSize,
            ttlDefault: 3600
        );
    }

    public static function l2(string $name = 'L2', int $maxSize = 10000) : self
    {
        return new self(
            name      : CacheTierName::from($name),
            priority  : 2,
            maxSize   : $maxSize,
            ttlDefault: 7200
        );
    }

    public function isFasterThan(self $other) : bool
    {
        return $this->priority < $other->priority;
    }

    public function canStore(int $currentSize) : bool
    {
        return $currentSize < $this->maxSize;
    }
}