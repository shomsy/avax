<?php

declare(strict_types=1);

namespace components\Cache\System\Foundation\Serialization;

use RuntimeException;
use Throwable;

final class CachePayloadCouldNotBeSerialized extends RuntimeException
{
    public function __construct(string $message, Throwable|null $previous = null)
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }
}