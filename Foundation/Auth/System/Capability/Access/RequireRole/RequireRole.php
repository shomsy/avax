<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequireRole;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capability\User\UserRole;

/**
 * Requirement for specific roles within the Auth System.
 * 
 * Banal: Stops anyone without the required job title.
 */
final readonly class RequireRole
{
    public function __construct(
        private ReadCurrentUser $readCurrentUser
    ) {}

    /**
     * @throws Unauthenticated
     * @throws RoleDenied
     */
    public function execute(UserRole $requiredRole) : void
    {
        $user = $this->readCurrentUser->execute();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->canAccessRole(requiredRole: $requiredRole)) {
            throw new RoleDenied(requirement: $requiredRole);
        }
    }
}
