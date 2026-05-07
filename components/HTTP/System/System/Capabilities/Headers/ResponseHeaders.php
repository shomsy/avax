<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Capabilities\Headers;

final class ResponseHeaders
{
    /** @var array<string, list<string>> */
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

    /**
     * @return list<string>|null
     */
    public function get(string $name) : ?array
    {
        return $this->headers[$name] ?? null;
    }

    public function has(string $name) : bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function all() : array
    {
        return $this->headers;
    }
}
