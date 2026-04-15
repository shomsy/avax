<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Tenant;

use DateTimeImmutable;

final readonly class Tenant
{
    public DateTimeImmutable $createdAt;
    public int               $ownerUserId;
    public string            $name;
    public string            $slug;
    public string            $tenantId;

    public function __construct(
        string            $tenantId,
        string            $slug,
        string            $name,
        int               $ownerUserId,
        DateTimeImmutable $createdAt
    )
    {
        $this->tenantId    = $tenantId;
        $this->slug        = $slug;
        $this->name        = $name;
        $this->ownerUserId = $ownerUserId;
        $this->createdAt   = $createdAt;
    }
}
