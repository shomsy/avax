<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Federation\VerifyDomain;

use SensitiveParameter;

final readonly class VerifyFederationDomainData
{
    public string $verificationToken;
    public string $connectionId;

    public function __construct(
        string                       $connectionId,
        #[SensitiveParameter] string $verificationToken
    )
    {
        $this->connectionId      = $connectionId;
        $this->verificationToken = $verificationToken;
    }
}
