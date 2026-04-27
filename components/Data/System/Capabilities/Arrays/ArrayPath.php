<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Arrays;

final readonly class ArrayPath
{
    public function __construct(
        private string $path,
    ) {
    }

    public static function fromString(string $path): self
    {
        return new self(path: $path);
    }

    public function keys(): array
    {
        return explode(separator: '.', string: $this->path);
    }

    public function parent(): ArrayPath|null
    {
        $keys = $this->keys();

        if (count($keys) <= 1) {
            return null;
        }

        array_pop($keys);

        return new self(path: implode(separator: '.', array: $keys));
    }

    public function last(): string
    {
        $keys = $this->keys();

        return end($keys);
    }

    public function toString(): string
    {
        return $this->path;
    }
}