<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Failure;

use RuntimeException;

final class DirectoryNotEmpty extends RuntimeException
{
    public function __construct(
        public readonly string $path,
        public readonly int    $itemCount,
    )
    {
        parent::__construct("Directory not empty: {$path} ({$itemCount} items)");
    }
}