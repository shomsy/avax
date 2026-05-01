<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequirePermission;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Exception;

/**
 * Exception for missing permission within the Auth System.
 */
final class PermissionDenied extends Exception
{
    public function __construct(
        private readonly UserPermission $userPermission,
        string $message = 'Access denied.',
        int $code = 403,
    ) {
        parent::__construct(message: $message, code: $code);
    }

    public function requirement(): UserPermission
    {
        return $this->userPermission;
    }
}
