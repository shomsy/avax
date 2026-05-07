<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RequireAuthentication;

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
