<?php

declare(strict_types=1);

namespace Infrastructure\Config\Service;

use Avax\Config\Architecture\DDD\AppPath;
use Avax\Config\Configurator\AppConfigurator;
use Avax\Config\Configurator\ConfiguratorInterface;

final class Config extends AppConfigurator implements ConfiguratorInterface
{
    /**
     * Retrieves the paths to configuration files.
     *
     * By using a unified method for accessing configuration paths, the system can dynamically
     * load and manage configuration data based on varying contexts or environments.
     *
     * @return array<string, string> Associative array where the key is the configuration namespace
     *                               and the value is the path to the configuration file.
     */
    public function configurationFilePaths() : array
    {
        return $this->getConfigurationPaths();
    }

    /**
     * Defines the paths to each configuration file.
     *
     * This method provides an associative array mapping configuration namespaces
     * to their respective file paths. This helps in maintaining organized and modular
     * configuration management.
     *
     * @return array<string, string> Associative array of configuration namespaces to their file paths.
     */
    protected function getConfigurationPaths() : array
    {
        return [
            'app'         => AppPath::CONFIG->get() . '/app.php',
            'database'    => AppPath::CONFIG->get() . '/database.php',
            'logging'     => AppPath::CONFIG->get() . '/logging.php',
            'middleware'  => AppPath::CONFIG->get() . '/middleware.php',
            'rtgapi'      => AppPath::CONFIG->get() . '/rtgapi.php',
            'serverpy'    => AppPath::CONFIG->get() . '/serverpy.php',
            'views'       => AppPath::CONFIG->get() . '/views.php',
            'filesystems' => AppPath::CONFIG->get() . '/filesystems.php',
        ];
    }
}
