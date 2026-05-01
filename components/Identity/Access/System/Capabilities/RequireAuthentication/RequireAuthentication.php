<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Requirement for authentication presence.
 *
 * Banal: Stops anyone not logged in.
 */
final readonly class RequireAuthentication
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute(): void
    {
        if (! $this->currentAuthentication->read()->isAuthenticated()) {
            throw new Unauthenticated();
        }
    }
}
