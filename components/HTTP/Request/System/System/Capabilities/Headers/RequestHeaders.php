<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\System\Capabilities\Headers;

final class RequestHeaders
{
    private array $headers = [];

    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $values) {
            $this->set($name, $values);
        }
    }

    public function set(string $name, string|array $values) : void
    {
        $this->headers[strtolower($name)] = new HeaderValue($values);
    }

    public function has(string $name) : bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function get(string $name) : ?HeaderValue
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function all() : array
    {
        $result = [];
        foreach ($this->headers as $name => $value) {
            $result[$name] = $value->all();
        }

        return $result;
    }
}
