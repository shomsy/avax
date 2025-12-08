<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

use RuntimeException;
use Throwable;

class SessionStartException extends RuntimeException
{
    #[\Override]
    public function __construct(string $message = "Failed to start session", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
