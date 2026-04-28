<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\Configuration;

use Avax\Components\Config\System\Capabilities\ConfigLoader\PHPArrayFileLoader;
use Avax\Components\Config\System\Capabilities\Repository\ConfigurationRepository;
use Avax\Components\Config\System\PublicSurface\Config;
use Avax\Components\Data\System\PublicSurface\Data;

/**
 * Configuration unit to assemble Config component services.
 */
final class RegisterConfig
{
    public function build(Data $dataFacade) : Config
    {
        return new Config(
            repository: new ConfigurationRepository()
        );
    }
}
