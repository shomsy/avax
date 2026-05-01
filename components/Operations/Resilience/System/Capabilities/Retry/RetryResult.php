<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

use Throwable;

final readonly class RetryResult
{
    public function __construct(
        public bool           $success,
        public mixed          $result,
        public int            $attempts,
        public Throwable|null $lastException,
    ) {}
}
