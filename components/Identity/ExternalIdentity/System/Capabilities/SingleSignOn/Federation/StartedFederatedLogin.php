<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport;

final readonly class StartedFederatedLogin
{
    public function __construct(public string $redirectUrl, public string|null $state = null) {}
}
