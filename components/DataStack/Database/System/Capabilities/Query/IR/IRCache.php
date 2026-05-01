<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR;

final class IRCache
{
    /** @var array<string, mixed> */
    private array $items = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function put(string $key, mixed $value): self
    {
        $this->items[$key] = $value;

        return $this;
    }

    public function remember(string $key, callable $resolver): mixed
    {
        if (! array_key_exists(key: $key, array: $this->items)) {
            $this->items[$key] = $resolver();
        }

        return $this->items[$key];
    }

    public function forget(string $key): self
    {
        unset($this->items[$key]);

        return $this;
    }

    public function clear(): self
    {
        $this->items = [];

        return $this;
    }
}
