<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use RuntimeException;
use Throwable;

final class StoreCachedValueFailed extends RuntimeException
{
    public function __construct(
        string        $message,
        public string $key,
        ?Throwable    $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }
}