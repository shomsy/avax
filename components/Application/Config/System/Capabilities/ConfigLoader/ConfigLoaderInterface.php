<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\ConfigLoader;

/**
 * Interface for loading configuration files.
 */
interface ConfigLoaderInterface
{
    public function loadConfigFile(string $filePath) : array;
}
