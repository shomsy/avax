<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RequireRole;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Exception;

/**
 * Exception for insufficient role within the Auth System.
 */
final class RoleDenied extends Exception
{
    public function __construct(
        private readonly UserRole $userRole,
        string                    $message = 'Access denied.',
        int                       $code = 403,
    )
    {
        parent::__construct(message: $message, code: $code);
    }

    public function requirement() : UserRole
    {
        return $this->userRole;
    }
}
