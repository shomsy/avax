<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequirePermission;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for specific permissions within the Auth System.
 *
 * Banal: Stops anyone without the exact permission.
 */
final readonly class RequirePermission
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
    ) {}

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     */
    public function execute(UserPermission $userPermission) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if (! $user->hasPermission(permission: $userPermission)) {
            throw new PermissionDenied(requirement: $userPermission);
        }
    }
}
