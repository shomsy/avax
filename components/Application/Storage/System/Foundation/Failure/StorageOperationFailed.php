<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Failure;

use RuntimeException;
use Throwable;

final class StorageOperationFailed extends RuntimeException
{
    public function __construct(
        public readonly string $operation,
        public readonly string $path, string|null $message = null, int|null $code = null, Throwable|null $previous = null,
    )
    {
        $message ??= "Failed to {$operation}: {$path}";
        parent::__construct($message, $code ?? 0, $previous);
    }
}