<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

final class SessionScope
{
    private string $prefix;
    private SessionInterface $session;

    public function __construct(string $namespace, SessionInterface $session)
    {
        $this->prefix  = rtrim($namespace, '.') . '.';
        $this->session = $session;
    }

    private function key(string $k) : string
    {
        return $this->prefix . $k;
    }

    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $this->session->put($this->key($key), $value, $ttl);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->session->get($this->key($key), $default);
    }

    public function has(string $key) : bool
    {
        return $this->session->has($this->key($key));
    }

    public function forget(string $key) : void
    {
        $this->session->forget($this->key($key));
    }

    public function all() : array
    {
        $all = $this->session->all();
        $out = [];

        foreach ($all as $k => $v) {
            if (str_starts_with($k, $this->prefix)) {
                $short = substr($k, strlen($this->prefix));
                $out[$short] = $v;
            }
        }

        return $out;
    }
}
