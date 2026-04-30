<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration;

use Avax\Components\Application\Filesystem\System\Capabilities\Storage\LocalStorage;
use Avax\Components\Application\Filesystem\System\PublicSurface\Storage;

/**
 * Configuration unit to register and assemble Storage services.
 */
final class RegisterStorage
{
    public function build() : Storage
    {
        return new Storage(
            storage: new LocalStorage(),
        );
    }
}
