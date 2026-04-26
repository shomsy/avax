<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\UseCacheTiers;

enum CacheTierName: string
{
    case L1_MEMORY      = 'l1_memory';
    case L2_DISTRIBUTED = 'l2_distributed';
    case L3_PERSISTENT  = 'l3_persistent';

    public function label() : string
    {
        return match ($this) {
            self::L1_MEMORY      => 'L1 In-Memory',
            self::L2_DISTRIBUTED => 'L2 Distributed',
            self::L3_PERSISTENT  => 'L3 Persistent',
        };
    }

    public function priority() : int
    {
        return match ($this) {
            self::L1_MEMORY      => 1,
            self::L2_DISTRIBUTED => 2,
            self::L3_PERSISTENT  => 3,
        };
    }
}