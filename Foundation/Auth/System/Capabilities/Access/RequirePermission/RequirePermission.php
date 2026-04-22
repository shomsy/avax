<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequirePermission;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for specific permissions within the Auth System.
 *
 * Banal: Stops anyone without the exact permission.
 */
final readonly class RequirePermission
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication
    )
    {
    }

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     */
    public function execute(UserPermission $permission) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->hasPermission(permission: $permission)) {
            throw new PermissionDenied(requirement: $permission);
        }
    }
}
