<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain;

use SensitiveParameter;

final readonly class VerifyFederationDomainData
{
    public function __construct(
        public string $connectionId,
        #[SensitiveParameter]
        public string $verificationToken,
    ) {}
}
