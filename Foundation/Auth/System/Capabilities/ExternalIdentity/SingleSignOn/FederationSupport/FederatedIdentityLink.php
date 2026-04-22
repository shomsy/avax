<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

final readonly class FederatedIdentityLink
{
    public function __construct(public string $connectionId, public string $subject, public int $userId)
    {
    }
}
