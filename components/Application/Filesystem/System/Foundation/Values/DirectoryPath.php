<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Values;

final readonly class DirectoryPath
{
    public function __construct(
        public string $path,
    ) {}

    public function exists() : bool
    {
        return is_dir($this->path);
    }

    public function isDirectory() : bool
    {
        return is_dir($this->path);
    }
}