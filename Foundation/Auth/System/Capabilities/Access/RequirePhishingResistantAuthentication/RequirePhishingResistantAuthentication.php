<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class RequirePhishingResistantAuthentication
{
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication
    )
    {
        $this->currentAuthentication = $currentAuthentication;
    }

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
