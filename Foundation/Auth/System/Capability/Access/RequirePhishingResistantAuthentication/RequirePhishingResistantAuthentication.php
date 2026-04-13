<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class RequirePhishingResistantAuthentication
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication
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
