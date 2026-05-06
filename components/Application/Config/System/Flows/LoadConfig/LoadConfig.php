<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Flows\LoadConfig;

use Avax\Components\Application\Config\System\Capabilities\Loader\ConfigLoader;
use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;

final readonly class LoadConfig
{
    public function __construct(
        private ConfigLoader $configLoader,
        private ConfigurationRepository $configurationRepository,
    ) {
    }

    public function fromDirectory(string $directory): void
    {
        $configs = $this->configLoader->load($directory);
        foreach ($configs as $namespace => $data) {
            $this->configurationRepository->set($namespace, $data);
        }
    }
}
