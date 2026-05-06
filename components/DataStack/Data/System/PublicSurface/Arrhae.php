<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

/**
 * Arrhae - Enterprise-grade raw array facade for high-performance manipulation.
 * Aligns with refactor.md 1:1. Sourced from avax.txt.
 */
final readonly class Arrhae
{
    public function __construct(private array $items = [])
    {
    }

    public static function from(array $items): self
    {
        return new self($items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }
}
