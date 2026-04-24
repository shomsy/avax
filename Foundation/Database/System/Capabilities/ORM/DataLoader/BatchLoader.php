<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\DataLoader;

use RuntimeException;

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
        $cacheKey = $name . ':' . md5(string: json_encode(value: array_values(array: $keys), flags: JSON_THROW_ON_ERROR));

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $callback = $this->loaders[$name] ?? null;

        if ($callback === null) {
            throw new RuntimeException(message: "No loader registered for: {$name}");
        }

        $results                = $callback($keys);
        $this->cache[$cacheKey] = $results;

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
        return isset($this->loaders[$name]);
    }
}
