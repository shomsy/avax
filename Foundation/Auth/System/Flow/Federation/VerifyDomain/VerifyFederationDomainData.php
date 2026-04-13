<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\VerifyDomain;

use SensitiveParameter;

final readonly class VerifyFederationDomainData
{
    public function __construct(
        public string                       $connectionId,
        #[SensitiveParameter] public string $verificationToken
    ) {}
}
