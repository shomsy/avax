<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Foundation\Failure;

use Exception;

class ObjectStorageWriteFailed extends Exception
{
    public function __construct(
        public readonly string  $key,
        public readonly ?string $reason,
    )
    {
        parent::__construct("Failed to write object {$key}: {$reason}");
    }
}
