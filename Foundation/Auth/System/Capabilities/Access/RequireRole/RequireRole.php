<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireRole;

use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capabilities\User\UserRole;

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
     * @throws RoleDenied
     */
    public function execute(UserRole $requiredRole) : void
    {
        $user = $this->readCurrentUser->execute();

        if ($user === null || ! $user->hasRole(role: $requiredRole)) {
            throw new RoleDenied(requirement: $requiredRole);
        }
    }
}
