<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tenants;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class TenantMembership
{
    public function __construct(private UserId $userId, private TenantId $tenantId) {}

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function tenantId(): TenantId
    {
        return $this->tenantId;
    }
}
