<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionStore;

final class ArraySessionStore implements SessionStore
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->data[$key] = $value;
    }

    public function delete(string $key) : void
    {
        unset($this->data[$key]);
    }

    public function all() : array
    {
        return $this->data;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->data);
    }

    public function flush() : void
    {
        $this->data = [];
    }

    public function flushNamespace(string $prefix) : void
    {
        foreach (array_keys(array: $this->data) as $key) {
            if (str_starts_with(haystack: $key, needle: $prefix)) {
                unset($this->data[$key]);
            }
        }
    }
}
