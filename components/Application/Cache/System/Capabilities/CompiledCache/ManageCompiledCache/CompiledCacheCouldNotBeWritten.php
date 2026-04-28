<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use RuntimeException;
use Throwable;

final class CompiledCacheCouldNotBeWritten extends RuntimeException
{
    public function __construct(
        string         $name,
        Throwable|null $previous = null
    )
    {
        parent::__construct(
            message : sprintf('Compiled cache "%s" could not be written', $name),
            code    : 0,
            previous: $previous
        );
    }
}