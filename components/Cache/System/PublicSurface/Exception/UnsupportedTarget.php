<?php

declare(strict_types=1);

namespace components\Cache\System\PublicSurface\Exception;

use RuntimeException;

final class UnsupportedTarget extends RuntimeException
{
    public function __construct(string $targetClass)
    {
        parent::__construct(message: sprintf(
                                         'Cache read target "%s" is not supported. Use RuntimeCacheTarget or CompiledCacheTarget.',
                                         $targetClass
                                     ));
    }
}