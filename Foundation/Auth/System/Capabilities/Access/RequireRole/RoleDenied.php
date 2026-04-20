<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireRole;

use Avax\Auth\System\Capabilities\User\UserRole;
use Exception;

/**
 * Exception for insufficient role within the Auth System.
 */
class RoleDenied extends Exception
{
    private readonly UserRole $requirement;

    public function __construct(
        UserRole $requirement,
        string   $message = 'Access denied.',
        int      $code = 403
    )
    {
        $this->requirement = $requirement;
        parent::__construct(message: $message, code: $code);
    }

    public function requirement() : UserRole
    {
        return $this->requirement;
    }
}
