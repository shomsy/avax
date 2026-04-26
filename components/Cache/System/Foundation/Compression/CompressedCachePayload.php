<?php

declare(strict_types=1);

namespace components\Cache\System\Foundation\Compression;

use Stringable;

final readonly class CompressedCachePayload implements Stringable
{
    public function __construct(
        public string $data,
        public string $algorithm,
        public int    $originalSize,
        public int    $compressedSize
    ) {}

    public function spaceSavedPercentage() : float
    {
        if ($this->originalSize === 0) {
            return 0.0;
        }

        return ($this->spaceSaved() / $this->originalSize) * 100;
    }

    public function spaceSaved() : int
    {
        return $this->originalSize - $this->compressedSize;
    }

    public function isWorthCompressing(int $thresholdBytes = 1024) : bool
    {
        return $this->originalSize >= $thresholdBytes && $this->compressionRatio() < 0.9;
    }

    public function compressionRatio() : float
    {
        if ($this->originalSize === 0) {
            return 1.0;
        }

        return $this->compressedSize / $this->originalSize;
    }

    public function __toString() : string
    {
        return $this->data;
    }
}