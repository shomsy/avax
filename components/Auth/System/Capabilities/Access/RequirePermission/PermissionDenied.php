<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Access\RequirePermission;

use components\Auth\System\Capabilities\Identity\User\UserPermission;
use Exception;

/**
 * Exception for missing permission within the Auth System.
 */
class PermissionDenied extends Exception
{
    public function __construct(
        private readonly UserPermission $requirement,
        string                          $message = 'Access denied.',
        int                             $code = 403
    )
    {
        parent::__construct(message: $message, code: $code);
    }

    public function requirement() : UserPermission
    {
        return $this->requirement;
    }
}
