<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Arrays;

use Avax\Components\Data\System\Capabilities\Collections\Collection;

/**
 * Arrhae - Raw array facade for functional array manipulation.
 * This is a recovered capability that provides the same power as old Arrhae,
 * but backed by the new Collection implementation.
 */
final readonly class Arrhae
{
    private Collection $collection;

    public function __construct(array $items = [])
    {
        $this->collection = new Collection($items);
    }

    public static function from(array $items) : self
    {
        return new self($items);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->collection->get($key, $default);
    }

    public function map(callable $callback) : self
    {
        return new self($this->collection->map($callback)->all());
    }

    public function all() : array
    {
        return $this->collection->all();
    }

    public function filter(callable $callback) : self
    {
        return new self($this->collection->filter($callback)->all());
    }

    public function merge(array $items) : self
    {
        return new self($this->collection->merge($items)->all());
    }

    public function sort(callable $callback) : self
    {
        return new self($this->collection->sortBy($callback)->all());
    }

    public function unique() : self
    {
        return new self($this->collection->unique()->all());
    }

    public function pluck(string $key) : array
    {
        return $this->collection->pluck($key);
    }

    public function where(string $key, mixed $value) : self
    {
        return new self($this->collection->firstWhere($key, $value) ? [$this->collection->firstWhere($key, $value)] : []);
    }

    public function toJson(int $flags = 0) : string
    {
        return $this->collection->toJson($flags);
    }

    public function toXml(string $root = 'root') : string
    {
        return $this->collection->toXml($root);
    }

    public function collect() : Collection
    {
        return $this->collection;
    }
}
