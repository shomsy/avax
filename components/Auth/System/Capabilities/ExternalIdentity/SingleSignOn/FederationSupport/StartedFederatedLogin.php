<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

final readonly class StartedFederatedLogin
{
    public function __construct(public string $redirectUrl, public string|null $state = null) {}
}
