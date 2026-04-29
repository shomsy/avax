<?php
declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Flows\LoadConfig;

use Avax\Components\Application\Config\System\Capabilities\Loader\ConfigLoader;
use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;

final readonly class LoadConfig
{
    public function __construct(
        private ConfigLoader $loader,
        private ConfigurationRepository $repository
    ) {}

    public function fromDirectory(string $directory): void
    {
        $configs = $this->loader->load($directory);
        foreach ($configs as $namespace => $data) {
            $this->repository->set($namespace, $data);
        }
    }
}
