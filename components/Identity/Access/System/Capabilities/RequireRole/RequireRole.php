<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequireRole;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for specific roles within the Auth System.
 *
 * Banal: Stops anyone without the required job title.
 */
final readonly class RequireRole
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
    ) {
    }

    /**
     * @throws Unauthenticated
     * @throws RoleDenied
     */
    public function execute(UserRole $userRole): void
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if (! $user->canAccessRole(requiredRole: $userRole)) {
            throw new RoleDenied(requirement: $userRole);
        }
    }
}
