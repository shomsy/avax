<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

final readonly class FederatedIdentityLink
{
    public function __construct(public string $connectionId, public string $subject, public int $userId) {}
}
