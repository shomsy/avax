<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireAuthentication;

use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for authentication presence.
 *
 * Banal: Stops anyone not logged in.
 */
final readonly class RequireAuthentication
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
     */
    public function execute() : void
    {
        if (! $this->currentAuthentication->read()->isAuthenticated()) {
            throw new Unauthenticated();
        }
    }
}
