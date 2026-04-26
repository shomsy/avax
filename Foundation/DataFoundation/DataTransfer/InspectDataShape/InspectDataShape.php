<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\InspectDataShape;

use Avax\DataFoundation\DataTransfer\Configuration\DataTransferConfig;

final readonly class InspectDataShape
{
    public function __construct(
        private DataTransferConfig|null $config = null,
        private CacheDataShape          $cache = new CacheDataShape(),
    ) {}

    /**
     * @param class-string $class
     */
    public function inspect(string $class) : DataShape
    {
        $config   = $this->config ?? DataTransferConfig::default();
        $cacheKey = $class . ':' . spl_object_id(object: $config);

        return $this->cache->remember(
            key    : $cacheKey,
            builder: fn () : DataShape => new ReadClassDataShape()->read(class: $class, config: $config),
        );
    }
}
