<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequireRole;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for specific roles within the Auth System.
 *
 * Banal: Stops anyone without the required job title.
 */
final readonly class RequireRole
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication
    ) {}

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
