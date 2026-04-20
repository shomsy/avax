<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireResourceOwner;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Stops access when the current user is not the expected resource owner.
 */
final readonly class RequireResourceOwner
{
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication
    )
    {
        $this->currentAuthentication = $currentAuthentication;
    }

    /**
     * @throws ResourceOwnerDenied
     * @throws Unauthenticated
     */
    public function execute(int $ownerUserId) : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($user->id !== $ownerUserId) {
            throw new ResourceOwnerDenied(ownerUserId: $ownerUserId);
        }
    }
}
