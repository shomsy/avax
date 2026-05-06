<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Exception;

use RuntimeException;

final class InvalidTarget extends RuntimeException
{
    public function __construct(string $targetClass)
    {
        parent::__construct(message: sprintf(
            'Cache read target "%s" is not supported. Use RuntimeCacheTarget or CompiledCacheTarget.',
            $targetClass,
        ));
    }
}
