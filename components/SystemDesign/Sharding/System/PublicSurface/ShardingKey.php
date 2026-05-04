<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Sharding\System\PublicSurface;

final readonly class ShardingKey
{
    public function __construct(
        public string $field,
        public int    $shardCount,
    )
    {
    }
}
