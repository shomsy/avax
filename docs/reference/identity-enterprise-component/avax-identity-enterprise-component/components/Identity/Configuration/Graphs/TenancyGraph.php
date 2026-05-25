<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Capabilities\Tenants\TenantDirectory;
use Avax\Components\Identity\Flows\BeginTenantContext\BeginTenantContext;

final readonly class TenancyGraph
{
    public function __construct(private BeginTenantContext $beginTenantContext, private TenantDirectory $tenants) {}

    public function beginTenantContext(): BeginTenantContext
    {
        return $this->beginTenantContext;
    }

    public function tenants(): TenantDirectory
    {
        return $this->tenants;
    }
}
