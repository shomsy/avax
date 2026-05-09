<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Failure;

use RuntimeException;

final class TemporaryUrlNotSupported extends RuntimeException
{
    public function __construct(
        public readonly string $diskName,
    )
    {
        parent::__construct("Temporary URLs not supported for disk: {$diskName}");
    }
}