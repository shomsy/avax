<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use InvalidArgumentException;

final class CompiledCacheWasMissing extends InvalidArgumentException
{
    public function __construct(
        string $name,
    ) {
        parent::__construct(
            message: sprintf('Compiled cache "%s" was missing', $name),
        );
    }
}
