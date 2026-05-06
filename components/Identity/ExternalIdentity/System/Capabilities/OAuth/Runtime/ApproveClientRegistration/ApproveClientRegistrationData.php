<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration;

final readonly class ApproveClientRegistrationData
{
    public function __construct(public string $clientId, public string $approvedBy)
    {
    }
}
