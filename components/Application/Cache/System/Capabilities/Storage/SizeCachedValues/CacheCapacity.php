<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues;

readonly class CacheCapacity
{
    public function __construct(
        public int $maxEntries = 10000,
        public int $maxSizeBytes = 104857600,
        public int $maxValueSizeBytes = 1048576
    ) {}

    public static function unlimited() : self
    {
        return new self(
            maxEntries       : PHP_INT_MAX,
            maxSizeBytes     : PHP_INT_MAX,
            maxValueSizeBytes: PHP_INT_MAX
        );
    }

    public static function fromMegabytes(int $megabytes) : self
    {
        return new self(
            maxEntries       : PHP_INT_MAX,
            maxSizeBytes     : $megabytes * 1048576,
            maxValueSizeBytes: $megabytes * 1048576
        );
    }

    public function canStore(int $currentCount, int $currentSizeBytes) : bool
    {
        return $currentCount < $this->maxEntries
            && $currentSizeBytes < $this->maxSizeBytes;
    }

    public function isValueTooLarge(int $valueSizeBytes) : bool
    {
        return $valueSizeBytes > $this->maxValueSizeBytes;
    }
}