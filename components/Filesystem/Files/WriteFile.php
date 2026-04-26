<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

final class WriteFile
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path, string $content) : bool
    {
        return $this->disk->write(path: $path, content: $content, append: false);
    }
}
