<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition;

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

    public function set(string $key, mixed $value) : void
    {
        if ($key === '') {
            return;
        }

        $segments = explode(separator: '.', string: $key);
        $target   = &$this->items;

        foreach ($segments as $segment) {
            if (! isset($target[$segment]) || ! is_array(value: $target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;
    }

    public function env(string $key, mixed $default = null) : mixed
    {
        if ($key === '') {
            return $default;
        }

        $value = getenv(name: $key);
        if ($value !== false) {
            return $value;
        }

        if ($this->has(key: 'env.' . $key)) {
            return $this->get(key: 'env.' . $key, default: $default);
        }

        return $default;
    }

    public function has(string $key) : bool
    {
        if ($key === '') {
            return false;
        }

        $segments = explode(separator: '.', string: $key);
        $value    = $this->items;

        foreach ($segments as $segment) {
            if (! is_array(value: $value) || ! array_key_exists(key: $segment, array: $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        if ($key === '') {
            return $default;
        }

        $segments = explode(separator: '.', string: $key);
        $value    = $this->items;

        foreach ($segments as $segment) {
            if (! is_array(value: $value) || ! array_key_exists(key: $segment, array: $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->items;
    }
}
