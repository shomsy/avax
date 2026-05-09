<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Failure;

use RuntimeException;

final class InvalidStoragePath extends RuntimeException
{
    public function __construct(
        public readonly string $path,
    )
    {
        parent::__construct("Invalid storage path: {$path}");
    }
}