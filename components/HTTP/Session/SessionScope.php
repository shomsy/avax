<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use SensitiveParameter;

final class SessionScope
{
    private string           $prefix;
    private SessionInterface $session;

    public function __construct(string $namespace, #[SensitiveParameter] SessionInterface $session)
    {
        $this->prefix  = rtrim($namespace, '.') . '.';
        $this->session = $session;
    }

    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->session->put(key: $this->key(k: $key), value: $value, ttl: $ttl);
    }

    private function key(string $k) : string
    {
        return $this->prefix . $k;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->session->get(key: $this->key(k: $key), default: $default);
    }

    public function has(string $key) : bool
    {
        return $this->session->has(key: $this->key(k: $key));
    }

    public function forget(string $key) : void
    {
        $this->session->forget(key: $this->key(k: $key));
    }

    public function all() : array
    {
        $all = $this->session->all();
        $out = [];

        foreach ($all as $k => $v) {
            if (str_starts_with($k, $this->prefix)) {
                $short       = substr($k, strlen($this->prefix));
                $out[$short] = $v;
            }
        }

        return $out;
    }
}
