<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Serialization;

use RuntimeException;
use Throwable;

final class CachePayloadCouldNotBeSerialized extends RuntimeException
{
    public function __construct(
        string     $message,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }
}