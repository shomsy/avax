<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

final readonly class StartedFederatedLogin
{
    public function __construct(public string $redirectUrl, public ?string $state = null) {}
}
