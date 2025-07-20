<?php

declare(strict_types=1);

namespace Gemini\Container\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when a requested service cannot be found in the container.
 */
class ServiceNotFoundException extends RuntimeException
{
    public function __construct(string $serviceId, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(
            message : sprintf("Action '%s' not found in the container.", $serviceId),
            code    : $code,
            previous: $previous
        );
    }
}