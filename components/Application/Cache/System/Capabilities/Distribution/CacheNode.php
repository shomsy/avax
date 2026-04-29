<?php
declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

final readonly class CacheNode
{
    public function __construct(
        public string $id,
        public string $host,
        public int $port,
        public int $weight = 100
    ) {}
}
