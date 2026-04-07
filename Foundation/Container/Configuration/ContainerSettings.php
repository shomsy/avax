<?php

declare(strict_types=1);

namespace Avax\Container\Configuration;

/**
 * Container-scoped settings with dot-notation access.
 */
final class ContainerSettings
{
    /** @var array<string, mixed> */
    private array $items;

    /**
     * @param array<string, mixed> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        if ($key === '') {
            return $default;
        }

        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value) : void
    {
        if ($key === '') {
            return;
        }

        $segments = explode('.', $key);
        $target = &$this->items;

        foreach ($segments as $segment) {
            if (! isset($target[$segment]) || ! is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;
    }

    public function has(string $key) : bool
    {
        if ($key === '') {
            return false;
        }

        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    public function env(string $key, mixed $default = null) : mixed
    {
        if ($key === '') {
            return $default;
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if ($this->has(key: 'env.' . $key)) {
            return $this->get(key: 'env.' . $key, default: $default);
        }

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->items;
    }
}
