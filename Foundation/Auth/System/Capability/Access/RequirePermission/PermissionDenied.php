<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequirePermission;

use Exception;
use Avax\Auth\System\Capability\User\UserPermission;

/**
 * Exception for missing permission within the Auth System.
 */
class PermissionDenied extends Exception
{
    public function __construct(
        UserPermission $requirement,
        string $message = 'Access denied.',
        int $code = 403
    ) {
        parent::__construct(
            message: $message . " (Missing permission: $requirement->value)",
            code: $code
        );
    }
}
