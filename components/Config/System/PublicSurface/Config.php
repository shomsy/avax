<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\PublicSurface;

use Avax\Components\Config\System\Capabilities\Architecture\AppPath;
use Avax\Components\Config\System\Capabilities\ConfigLoader\ConfigLoaderInterface;
use Avax\Components\Data\System\PublicSurface\Data;
use RuntimeException;

/**
 * Configuration Public Surface.
 *
 * Provides access to all application configuration via dot-notation.
 */
final class Config
{
    private array $configuration = [];
    private bool  $loaded        = false;

    public function __construct(
        private readonly ConfigLoaderInterface $loader,
        private readonly Data                  $dataFacade
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureLoaded();

        $value = $this->dataFacade->get($this->configuration, $key, $default);

        if ($value === $default && $default === null) {
            throw new RuntimeException("Configuration key [{$key}] does not exist.");
        }

        return $value;
    }

    public function has(string $key): bool
    {
        $this->ensureLoaded();

        return $this->dataFacade->get($this->configuration, $key) !== null;
    }

    public function all() : array
    {
        $this->ensureLoaded();

        return $this->configuration;
    }

    private function ensureLoaded() : void
    {
        if ($this->loaded) {
            return;
        }

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
                $this->configuration[$namespace] = $this->loader->loadConfigFile($filePath);
            }
        }

        $this->loaded = true;
    }
}