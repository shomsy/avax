<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\ConfigLoader;

use Override;
use RuntimeException;

/**
 * Capability to load configuration from PHP array files.
 */
final class PHPArrayFileLoader implements ConfigLoaderInterface
{
    #[Override]
    public function loadConfigFile(string $filePath) : array
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException('Configuration file not found: ' . $filePath);
        }

        $config = require $filePath;

        if (! is_array($config)) {
            throw new RuntimeException('Configuration file must return an array: ' . $filePath);
        }

        return $config;
    }
}
