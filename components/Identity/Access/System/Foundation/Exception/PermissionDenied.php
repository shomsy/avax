<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Foundation\Exception;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use RuntimeException;

/**
 * Thrown when a permission check fails during authorization.
 *
 * This is the single canonical PermissionDenied exception for the Access component.
 * The former RequirePermission\PermissionDenied and PermissionDeniedException aliases
 * were removed during Slice 1 cleanup.
 */
final class PermissionDenied extends RuntimeException
{
    public function __construct(
        private readonly UserPermission|null $requirement = null,
        string $message = 'Access denied.',
        int $code = 403,
    ) {
        parent::__construct(message: $message, code: $code);
    }

    public function requirement() : UserPermission|null
    {
        return $this->requirement;
    }
}
