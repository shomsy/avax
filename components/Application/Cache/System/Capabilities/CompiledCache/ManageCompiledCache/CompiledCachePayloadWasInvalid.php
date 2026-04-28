<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use RuntimeException;
use Throwable;

final class CompiledCachePayloadWasInvalid extends RuntimeException
{
    public function __construct(
        string         $reason,
        Throwable|null $previous = null
    )
    {
        parent::__construct(
            message : sprintf('Compiled cache payload was invalid: %s', $reason),
            code    : 0,
            previous: $previous
        );
    }
}