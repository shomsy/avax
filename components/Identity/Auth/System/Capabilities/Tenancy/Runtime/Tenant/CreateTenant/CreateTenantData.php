<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant;

final readonly class CreateTenantData
{
    public function __construct(public string $slug, public string $name, public int $ownerUserId) {}
}
