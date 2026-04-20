<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireRole;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for specific roles within the Auth System.
 *
 * Banal: Stops anyone without the required job title.
 */
final readonly class RequireRole
{
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication
    )
    {
        $this->currentAuthentication = $currentAuthentication;
    }

    /**
     * @throws Unauthenticated
     * @throws RoleDenied
     */
    public function execute(UserRole $requiredRole) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->canAccessRole(requiredRole: $requiredRole)) {
            throw new RoleDenied(requirement: $requiredRole);
        }
    }
}
