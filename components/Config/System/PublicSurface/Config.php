<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\PublicSurface;

final class Config implements ConfigInterface
{
    private array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $current = $this->data;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function set(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }
}