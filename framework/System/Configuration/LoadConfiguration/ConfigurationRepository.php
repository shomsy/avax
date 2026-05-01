<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\LoadConfiguration;

final class ConfigurationRepository
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $keys    = explode('.', $key);
        $current = $this->config;

        foreach ($keys as $k) {
            if (! isset($current[$k])) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    public function set(string $key, mixed $value): void
    {
        $keys    = explode('.', $key);
        $current = &$this->config;

        foreach ($keys as $k) {
            if (! isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function load(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
}
