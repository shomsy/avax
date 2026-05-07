<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RequirePhishingResistantAuthentication;

use Avax\Components\Identity\Access\System\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class RequirePhishingResistantAuthentication
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
    ) {}

    /**
     * @throws PhishingResistantAuthenticationRequired
     * @throws Unauthenticated
     */
    public function execute() : void
    {
        $authenticationContext = $this->currentAuthentication->read();

        if (! $authenticationContext->user() instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if (! $authenticationContext->isPhishingResistant()) {
            throw new PhishingResistantAuthenticationRequired();
        }
    }
}
