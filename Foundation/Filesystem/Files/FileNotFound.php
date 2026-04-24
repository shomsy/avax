<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use RuntimeException;

class FileNotFound extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(message: "File not found: {$path}");
    }
}