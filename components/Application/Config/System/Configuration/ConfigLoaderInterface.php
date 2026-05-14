<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Configuration;

interface ConfigLoaderInterface
{
    public function loadConfigFile(string $filePath): array;
}
