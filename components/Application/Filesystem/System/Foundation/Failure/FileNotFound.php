<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Failure;

use RuntimeException;

final class FileNotFound extends RuntimeException
{
    public function __construct(
        public readonly string $path,
    )
    {
        parent::__construct("File not found: {$path}");
    }
}