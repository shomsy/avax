<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Failure;

use Throwable;

class FrameworkMisconfigured extends FrameworkFailure
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(message: $message, previous: $previous);
    }
}
