<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
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
        $context = $this->currentAuthentication->read();

        if ($context->user() === null) {
            throw new Unauthenticated();
        }

        if (! $context->isPhishingResistant()) {
            throw new PhishingResistantAuthenticationRequired();
        }
    }
}
