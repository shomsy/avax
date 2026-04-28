<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

interface FilesystemInterface
{
    public function disk(string $name = 'local') : Disk;

    public function exists(string $path) : bool;

    public function read(string $path) : string;

    public function write(string $path, string $contents) : void;

    public function delete(string $path) : void;

    public function mkdir(string $path, int $mode = 0755) : void;
}