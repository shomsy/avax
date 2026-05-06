<?php

declare(strict_types=1);

namespace Avax\Labs\Integration\ObjectStorage\System\Foundation\Failure;

use Exception;

class ObjectNotFound extends Exception
{
    public function __construct(
        public readonly string $key,
    ) {
        parent::__construct("Object not found: {$key}");
    }
}
