<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Failure;

use RuntimeException;

final class PathTraversalAttempt extends RuntimeException
{
    public function __construct(
        public readonly string $path,
    )
    {
        parent::__construct("Path traversal attempt detected: {$path}");
    }
}