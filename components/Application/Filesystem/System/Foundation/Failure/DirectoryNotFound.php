<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Failure;

use RuntimeException;

final class DirectoryNotFound extends RuntimeException
{
    public function __construct(
        public readonly string $path,
    )
    {
        parent::__construct("Directory not found: {$path}");
    }
}