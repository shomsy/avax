<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Stops access when the current user is not the expected resource owner.
 */
final readonly class RequireResourceOwner
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
    ) {}

    /**
     * @throws ResourceOwnerDenied
     * @throws Unauthenticated
     */
    public function execute(int $ownerUserId) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if ($user->id !== $ownerUserId) {
            throw new ResourceOwnerDenied(ownerUserId: $ownerUserId);
        }
    }
}
