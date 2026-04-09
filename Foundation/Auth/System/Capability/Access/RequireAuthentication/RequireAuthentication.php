<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequireAuthentication;

use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;

/**
 * Requirement for authentication presence.
 *
 * Banal: Stops anyone not logged in.
 */
final readonly class RequireAuthentication
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication
    ) {}

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
