<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Serialization;

use RuntimeException;
use Throwable;

final class CachePayloadCouldNotBeSerialized extends RuntimeException
{
    public function __construct(string $message, Throwable $throwable = null)
    {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }
}
