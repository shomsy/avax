<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\Repository;

/**
 * Repository for application configuration with dot-notation support.
 */
final class ConfigurationRepository
{
    private array $items = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        if (! str_contains($key, '.')) {
            return $default;
        }

        return $this->getDot($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->items[$key] = $value;

            return;
        }

        $this->setDot($key, $value);
    }

    public function has(string $key) : bool
    {
        return $this->get($key, $this) !== $this;
    }

    public function all() : array
    {
        return $this->items;
    }

    public function merge(array $config) : void
    {
        $this->items = array_replace_recursive($this->items, $config);
    }

    private function getDot(string $key, mixed $default) : mixed
    {
        $array = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    private function setDot(string $key, mixed $value) : void
    {
        $array = &$this->items;
        $keys  = explode('.', $key);

        while ( count($keys) > 1 ) {
            $key = array_shift($keys);

            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }
}