<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireRole;

use Exception;
use Avax\Auth\System\Capabilities\User\UserRole;

/**
 * Exception for insufficient role within the Auth System.
 */
class RoleDenied extends Exception
{
    public function __construct(
        UserRole $requirement,
        string $message = 'Access denied.',
        int $code = 403
    ) {
        parent::__construct(
            message: $message . " (Missing role: $requirement->value)",
            code: $code
        );
    }
}
