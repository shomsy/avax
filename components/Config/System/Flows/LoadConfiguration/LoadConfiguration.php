<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\Flows\LoadConfiguration;

final class LoadConfiguration
{
    public function load(\Avax\Components\Config\System\PublicSurface\Config $config, array $data): void
    {
        $config->set($data);
    }
}