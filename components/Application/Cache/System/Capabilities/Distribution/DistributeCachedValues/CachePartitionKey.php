<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Stringable;

final readonly class CachePartitionKey implements Stringable
{
    public function __construct(
        public string $partition,
        public string $key
    ) {}

    public static function create(string $partition, string $key) : self
    {
        return new self(partition: $partition, key: $key);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return sprintf('%s:%s', $this->partition, $this->key);
    }
}