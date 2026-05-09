<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Values;

final readonly class StoragePath
{
    public function __construct(
        public string $path,
    ) {}

    public function isEmpty() : bool
    {
        return $this->path === '';
    }

    public function withoutLeadingSlash() : string
    {
        return ltrim($this->path, '/');
    }

    public function withLeadingSlash() : string
    {
        return '/' . ltrim($this->path, '/');
    }
}