<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Foundation\Failure;

use RuntimeException;
use Throwable;

class SecureRequestAuthorizationFailed extends RuntimeException
{
    public function __construct(
        string     $message = 'SecureRequest authorization failed.',
        int $code = 403, Throwable|null $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
