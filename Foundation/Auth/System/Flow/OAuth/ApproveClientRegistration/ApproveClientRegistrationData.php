<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ApproveClientRegistration;

final readonly class ApproveClientRegistrationData
{
    public function __construct(
        public string $clientId,
        public string $approvedBy
    ) {}
}
