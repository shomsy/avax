<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Configuration;

use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;
use Avax\Components\Application\Config\System\PublicSurface\Config;

/**
 * Configuration unit to assemble Config component services.
 */
final class RegisterConfig
{
    public function build(): Config
    {
        return new Config(
            configurationRepository: new ConfigurationRepository(),
        );
    }
}
