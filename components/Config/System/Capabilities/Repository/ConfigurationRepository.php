<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\Capabilities\Repository;

final class ConfigurationRepository
{
    private array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }
}