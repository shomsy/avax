<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCompiledCache;

use RuntimeException;
use Throwable;

final class CompiledCachePayloadWasInvalid extends RuntimeException
{
    public function __construct(
        string     $reason,
        ?Throwable $previous = null
    )
    {
        parent::__construct(
            sprintf('Compiled cache payload was invalid: %s', $reason),
            0,
            $previous
        );
    }
}