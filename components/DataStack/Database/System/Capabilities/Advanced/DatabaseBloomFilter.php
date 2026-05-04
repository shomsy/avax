<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Advanced;

final class DatabaseBloomFilter
{
    private array $bitset = [];

    public function add(string $key): void
    {
        $hash = crc32($key) % 1000;
        $this->bitset[$hash] = true;
    }

    public function contains(string $key): bool
    {
        $hash = crc32($key) % 1000;

        return isset($this->bitset[$hash]);
    }
}
