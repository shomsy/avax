<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Foundation\Failure;

final class CsrfTokenMismatch extends SecurityException
{
    public function __construct(string $message = 'CSRF token mismatch.')
    {
        parent::__construct($message, 419);
    }
}
