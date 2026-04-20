<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\ApproveClientRegistration;

final readonly class ApproveClientRegistrationData
{
    public string $approvedBy;
    public string $clientId;

    public function __construct(
        string $clientId,
        string $approvedBy
    )
    {
        $this->clientId   = $clientId;
        $this->approvedBy = $approvedBy;
    }
}
