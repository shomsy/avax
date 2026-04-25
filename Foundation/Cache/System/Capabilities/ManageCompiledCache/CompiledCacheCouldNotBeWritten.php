<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCompiledCache;

use RuntimeException;
use Throwable;

final class CompiledCacheCouldNotBeWritten extends RuntimeException
{
    public function __construct(
        string     $name,
        ?Throwable $previous = null
    )
    {
        parent::__construct(
            sprintf('Compiled cache "%s" could not be written', $name),
            0,
            $previous
        );
    }
}