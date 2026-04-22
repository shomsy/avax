<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use RuntimeException;

class FileCopyFailed extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(message: "Failed to copy file: {$path}");
    }
}