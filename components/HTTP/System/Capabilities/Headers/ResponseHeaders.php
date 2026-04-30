<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Headers;

final class ResponseHeaders
{
    private array $headers = [];

    public function set(string $name, string $value) : self
    {
        $this->headers[$name] = [$value];

        return $this;
    }

    public function add(string $name, string $value) : self
    {
        $this->headers[$name][] = $value;

        return $this;
    }

    public function get(string $name) : array|null
    {
        return $this->headers[$name] ?? null;
    }

    public function has(string $name) : bool
    {
        return isset($this->headers[$name]);
    }

    public function all() : array
    {
        return $this->headers;
    }
}
