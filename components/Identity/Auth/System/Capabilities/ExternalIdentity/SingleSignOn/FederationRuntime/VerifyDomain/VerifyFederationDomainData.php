<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain;

use SensitiveParameter;

final readonly class VerifyFederationDomainData
{
    public function __construct(
        public string                       $connectionId,
        #[SensitiveParameter] public string $verificationToken
    ) {}
}
