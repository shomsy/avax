<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant;

final readonly class CreateTenantData
{
    public int    $ownerUserId;
    public string $name;
    public string $slug;

    public function __construct(
        string $slug,
        string $name,
        int    $ownerUserId
    )
    {
        $this->slug        = $slug;
        $this->name        = $name;
        $this->ownerUserId = $ownerUserId;
    }
}
