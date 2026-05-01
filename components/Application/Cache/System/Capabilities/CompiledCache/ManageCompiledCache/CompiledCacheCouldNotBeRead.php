<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use RuntimeException;
use Throwable;

final class CompiledCacheCouldNotBeRead extends RuntimeException
{
    public function __construct(
        string     $name,
        ?Throwable $throwable = null,
    )
    {
        parent::__construct(
            message : sprintf('Compiled cache "%s" could not be read', $name),
            code    : 0,
            previous: $throwable,
        );
    }
}
