<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Failure;

use RuntimeException;

final class PermissionDenied extends RuntimeException
{
    public function __construct(
        public readonly string $path,
        public readonly string $operation,
    )
    {
        parent::__construct("Permission denied for {$operation}: {$path}");
    }
}