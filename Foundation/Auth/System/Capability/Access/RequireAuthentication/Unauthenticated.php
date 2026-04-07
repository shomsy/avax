<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequireAuthentication;

use Exception;

/**
 * Exception for missing authentication within the Auth System.
 */
class Unauthenticated extends Exception
{
    public function __construct(string $message = 'Authentication required.', int $code = 401)
    {
        parent::__construct(message: $message, code: $code);
    }
}
