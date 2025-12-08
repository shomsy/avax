<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

class SessionStartException extends \RuntimeException
{
    public function __construct(string $message = "Failed to start session", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
