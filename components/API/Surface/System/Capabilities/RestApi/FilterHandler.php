<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\RestApi;

final readonly class FilterHandler
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private array $filters = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->filters;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->filters[$key] ?? $default;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->filters);
    }

    public function where(string $key, mixed $value) : self
    {
        $filters       = $this->filters;
        $filters[$key] = $value;

        return new self($filters);
    }

    /**
     * @param list<string> $keys
     */
    public function except(array $keys) : self
    {
        return new self(array_diff_key($this->filters, array_flip($keys)));
    }

    /**
     * @param list<string> $keys
     */
    public function only(array $keys) : self
    {
        return new self(array_intersect_key($this->filters, array_flip($keys)));
    }

    public function isEmpty() : bool
    {
        return $this->filters === [];
    }
}
