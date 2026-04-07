<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequirePermission;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capability\User\UserPermission;

/**
 * Requirement for specific permissions within the Auth System.
 * 
 * Banal: Stops anyone without the exact permission.
 */
final readonly class RequirePermission
{
    public function __construct(
        private ReadCurrentUser $readCurrentUser
    ) {}

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     */
    public function execute(UserPermission $permission) : void
    {
        $user = $this->readCurrentUser->execute();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->hasPermission(permission: $permission)) {
            throw new PermissionDenied(requirement: $permission);
        }
    }
}
