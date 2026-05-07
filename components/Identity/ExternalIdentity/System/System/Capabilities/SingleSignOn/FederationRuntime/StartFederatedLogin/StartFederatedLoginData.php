<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin;

final readonly class StartFederatedLoginData
{
    public function __construct(public string $connectionId, public string $redirectUri, public ?string $state = null) {}
}
