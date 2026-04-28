<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Directories;

use RuntimeException;

class DirectoryDeleteFailed extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(message: "Failed to delete directory: {$path}");
    }
}