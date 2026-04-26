<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

use RuntimeException;

final class CacheReadTargetWasNotSupported extends RuntimeException
{
    public function __construct(string $targetClass)
    {
        parent::__construct(sprintf(
                                'Cache read target "%s" is not supported. Use RuntimeCacheTarget or CompiledCacheTarget.',
                                $targetClass
                            ));
    }
}