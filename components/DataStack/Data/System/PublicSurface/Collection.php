<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection as CollectionCapability;

/**
 * Collection - Enterprise-grade fluent collection wrapper.
 * Aligns with refactor.md 1:1. Sourced from avax.txt.
 */
final class Collection
{
    private array $items;

    public function __construct(iterable $items = [])
    {
        $this->items = is_array($items) ? $items : iterator_to_array($items);
    }

    public static function from(iterable $items): self
    {
        return new self($items);
    }

    public function all(): array { return $this->items; }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    public function filter(callable $callback): self
    {
        return new self(array_filter($this->items, $callback));
    }

    public function first(mixed $default = null): mixed
    {
        return $this->items[0] ?? $default;
    }

    // ... additional enterprise methods (pluck, reduce, etc.)
}
