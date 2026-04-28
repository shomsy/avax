<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Flows\LoadConfiguration;

use Avax\Components\Application\Config\System\Capabilities\Architecture\AppPath;
use Avax\Components\Application\Config\System\Capabilities\ConfigLoader\ConfigLoaderInterface;
use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;

/**
 * Flow to coordinate the loading of configuration files into the repository.
 */
final readonly class LoadConfiguration
{
    public function __construct(
        private ConfigLoaderInterface   $loader,
        private ConfigurationRepository $repository
    ) {}

    /**
     * Load all standard configuration files.
     */
    public function load() : void
    {
        $paths = [
            'app'         => AppPath::CONFIG->get() . 'app.php',
            'database'    => AppPath::CONFIG->get() . 'database.php',
            'logging'     => AppPath::CONFIG->get() . 'logging.php',
            'middleware'  => AppPath::CONFIG->get() . 'middleware.php',
            'views'       => AppPath::CONFIG->get() . 'views.php',
            'filesystems' => AppPath::CONFIG->get() . 'filesystems.php',
        ];

        foreach ($paths as $namespace => $filePath) {
            if (file_exists($filePath)) {
                $data = $this->loader->loadConfigFile($filePath);
                $this->repository->set($namespace, $data);
            }
        }
    }
}