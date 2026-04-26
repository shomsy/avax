<?php

declare(strict_types=1);

namespace components\Cache\System\PublicSurface\Exception;

use RuntimeException;

final class NotFound extends RuntimeException
{
    public function __construct(string $name, array $available)
    {
        parent::__construct(message: sprintf(
                                         'Cache "%s" not found in registry. Available: %s',
                                         $name,
                                         implode(', ', $available)
                                     ));
    }
}