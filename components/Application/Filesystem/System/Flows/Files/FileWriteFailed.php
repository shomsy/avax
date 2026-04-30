<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Files;

use RuntimeException;

class FileWriteFailed extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(message: 'Failed to write file: ' . $path);
    }
}
