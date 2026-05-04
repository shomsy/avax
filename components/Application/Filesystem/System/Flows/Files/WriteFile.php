<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Files;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;

final readonly class WriteFile
{
    public function __construct(private Disk $disk) {}

    public function execute(string $path, string $content) : bool
    {
        return $this->disk->write(path: $path, content: $content, append: false);
    }
}
