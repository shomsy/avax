<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Failure;

use Throwable;

class FrameworkBootFailed extends FrameworkFailure
{
    public static function fromThrowable(Throwable $throwable) : self
    {
        return new self(
            message : sprintf('Framework boot failed: %s', $throwable->getMessage()),
            code    : (int) $throwable->getCode(),
            previous: $throwable,
        );
    }
}
