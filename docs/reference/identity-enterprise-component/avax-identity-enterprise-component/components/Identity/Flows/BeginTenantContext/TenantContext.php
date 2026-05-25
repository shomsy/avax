<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\BeginTenantContext;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class TenantContext
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
