<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Facade;

use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotConfigured;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\NotFound;

class CacheRegistry
{
    /** @var array<string, CacheContract> */
    private array $caches = [];

    private string|null $defaultName = null;

    public function register(string $name, CacheContract $cacheContract) : void
    {
        $this->caches[$name] = $cacheContract;

        if ($this->defaultName === null) {
            $this->defaultName = $name;
        }
    }

    public function default() : CacheContract
    {
        if ($this->defaultName === null) {
            throw new NotConfigured(
                message: 'No default cache configured. Use Cache::use() or CacheRegistry::register() first.',
            );
        }

        return $this->caches[$this->defaultName];
    }

    public function get(string $name) : CacheContract
    {
        if (! isset($this->caches[$name])) {
            throw new NotFound(
                name: sprintf('Cache "%s" not found in registry. Available: %s', $name, implode(', ', array_keys($this->caches))),
            );
        }

        return $this->caches[$name];
    }

    public function has(string $name) : bool
    {
        return isset($this->caches[$name]);
    }

    public function forget(string $name) : void
    {
        unset($this->caches[$name]);

        if ($this->defaultName === $name) {
            $this->defaultName = array_key_first($this->caches) ?? null;
        }
    }

    public function clear() : void
    {
        $this->caches      = [];
        $this->defaultName = null;
    }
}
