<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireRole;

use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Exception;

/**
 * Exception for insufficient role within the Auth System.
 */
class RoleDenied extends Exception
{
    public function __construct(
        private readonly UserRole $requirement,
        string   $message = 'Access denied.',
        int      $code = 403
    )
    {
        parent::__construct(message: $message, code: $code);
    }

    public function requirement() : UserRole
    {
        return $this->requirement;
    }
}
