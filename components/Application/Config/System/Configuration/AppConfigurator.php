<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Configuration;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use RuntimeException;
use WeakMap;

abstract class AppConfigurator implements ConfiguratorInterface
{
    private static WeakMap $weakMap;

    protected Collection $configuration;

    protected ConfigLoaderInterface $configLoader;

    public function __construct(
        ConfigLoaderInterface $configLoader,
    ) {
        $this->configLoader = $configLoader;
        self::$weakMap ??= new WeakMap();
        $this->initializeConfiguration();
    }

    private function initializeConfiguration(): void
    {
        $this->configuration = self::$weakMap[$this] ?? $this->loadFreshConfigAndCache();
    }

    private function loadFreshConfigAndCache(): Collection
    {
        $collection = $this->loadConfigurationFiles();
        self::$weakMap[$this] = $collection;

        return $collection;
    }

    protected function loadConfigurationFiles(): Collection
    {
        $configData = [];
        foreach ($this->getConfigurationPaths() as $namespace => $filePath) {
            $configData[$namespace] = $this->configLoader->loadConfigFile(filePath: $filePath);
        }

        return Collection::make(items: $configData);
    }

    abstract /**
 * @throws RuntimeException
 */
protected function getConfigurationPaths(): array;

    public function get(string $key, mixed $default = null): mixed
    {
        $items = $this->configuration->all();

        $value = data_get(target: $items, key: $key, default: $default);

        if ($value === $default && $default === null) {
            throw new RuntimeException(message: 'Configuration key ['.$key.'] does not exist.');
        }

        return $value;
    }

    public function all(): Collection
    {
        return $this->configuration;
    }

    public function has(string $key): bool
    {
        return $this->configuration->contains(value: $key);
    }

    public function refresh(): Collection
    {
        return $this->configuration = $this->loadFreshConfigAndCache();
    }
}
