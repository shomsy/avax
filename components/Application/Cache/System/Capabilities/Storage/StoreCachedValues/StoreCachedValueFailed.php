<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use RuntimeException;
use Throwable;

final class StoreCachedValueFailed extends RuntimeException
{
    public function __construct(
        string    $message,
        public string $key,
        ?Throwable $throwable = null,
    )
    {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }
}
