<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Foundation\Failure;

use Exception;

class ObjectStorageUnavailable extends Exception
{
    public function __construct(
        public readonly string $bucket,
    )
    {
        parent::__construct("Object storage unavailable: {$bucket}");
    }
}
