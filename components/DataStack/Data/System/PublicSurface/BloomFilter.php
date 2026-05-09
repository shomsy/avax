<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic\BloomFilter as StructureBloomFilter;

final class BloomFilter
{
    private function __construct() {}

    public static function empty(int $bits = 128, int $hashCount = 3) : StructureBloomFilter
    {
        return StructureBloomFilter::empty(bits: $bits, hashCount: $hashCount);
    }
}
