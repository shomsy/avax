<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\DataLoader;

use RuntimeException;

interface DataLoaderInterface
{
    public function load(array $keys) : array;

    public function for(string $relation) : self;
}

interface LoaderCallback
{
    public function __invoke(array $keys) : array;
}

final class BatchLoader
{
    private array $loaders = [];

    private array $cache = [];

    public function register(string $name, LoaderCallback $callback) : self
    {
        $this->loaders[$name] = $callback;

        return $this;
    }

    public function load(string $name, array $keys) : array
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $callback = $this->loaders[$name] ?? null;

        if ($callback === null) {
            throw new RuntimeException(message: "No loader registered for: {$name}");
        }

        $results            = $callback($keys);
        $this->cache[$name] = $results;

        return $results;
    }

    public function prime(string $name, array $data) : self
    {
        $this->cache[$name] = $data;

        return $this;
    }

    public function clear(?string $name = null) : self
    {
        if ($name !== null) {
            unset($this->cache[$name]);
        } else {
            $this->cache = [];
        }

        return $this;
    }

    public function has(string $name) : bool
    {
        return isset($this->cache[$name]);
    }
}
