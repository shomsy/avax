<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCompiledCache;

use InvalidArgumentException;

final class CompiledCacheWasMissing extends InvalidArgumentException
{
    public function __construct(
        string $name
    )
    {
        parent::__construct(
            sprintf('Compiled cache "%s" was missing', $name)
        );
    }
}