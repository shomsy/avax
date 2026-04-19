<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequirePermission;

use Avax\Auth\System\Capability\User\UserPermission;
use Exception;

/**
 * Exception for missing permission within the Auth System.
 */
class PermissionDenied extends Exception
{
    private readonly UserPermission $requirement;

    #[\Override]
    public function __construct(
        UserPermission $requirement,
        string         $message = 'Access denied.',
        int            $code = 403
    )
    {
        $this->requirement = $requirement;
        parent::__construct(message: $message, code: $code);
    }

    public function requirement() : UserPermission
    {
        return $this->requirement;
    }
}
