<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Foundation\Failure;

use RuntimeException;
use Throwable;

class SecureRequestResolutionFailed extends RuntimeException
{
    public function __construct(
        string     $message = 'SecureRequest could not be resolved from current HTTP request.',
        int $code = 500, Throwable|null $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
