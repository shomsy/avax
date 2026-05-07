<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\CreateTenant;

final readonly class CreateTenantData
{
    public function __construct(public string $slug, public string $name, public int $ownerUserId) {}
}
