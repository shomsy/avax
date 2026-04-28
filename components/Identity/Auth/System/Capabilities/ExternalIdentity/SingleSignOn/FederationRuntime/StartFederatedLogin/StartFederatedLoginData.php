<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin;

final readonly class StartFederatedLoginData
{
    public function __construct(public string $connectionId, public string $redirectUri, public string|null $state = null) {}
}
