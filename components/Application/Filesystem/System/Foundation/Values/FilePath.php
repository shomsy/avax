<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Values;

final readonly class FilePath
{
    public function __construct(
        public string $path,
    ) {}

    public function exists() : bool
    {
        return file_exists($this->path);
    }

    public function isFile() : bool
    {
        return is_file($this->path);
    }
}