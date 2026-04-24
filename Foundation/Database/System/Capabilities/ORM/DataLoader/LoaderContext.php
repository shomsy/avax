<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\DataLoader;

final class LoaderContext
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function set(string $key, mixed $value) : self
    {
        $this->values[$key] = $value;

        return $this;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function all() : array
    {
        return $this->values;
    }
}
