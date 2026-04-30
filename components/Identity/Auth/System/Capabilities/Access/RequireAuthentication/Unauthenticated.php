<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication;

use Exception;

/**
 * Exception for missing authentication within the Auth System.
 */
final class Unauthenticated extends Exception
{
    public function __construct(string $message = 'Authentication required.', int $code = 401)
    {
        parent::__construct(message: $message, code: $code);
    }
}
