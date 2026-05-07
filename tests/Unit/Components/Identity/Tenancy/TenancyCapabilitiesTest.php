<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tenancy;

use Avax\Components\Identity\Tenancy\System\Capabilities\Model\Tenant;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TenancyCapabilitiesTest extends TestCase
{
    public function test_tenant_identification() : void
    {
        $tenant = new Tenant('tenant-1', 'acme', 'Acme Corp', 1, new DateTimeImmutable());
        $this->assertSame('tenant-1', $tenant->tenantId);
        $this->assertSame('Acme Corp', $tenant->name);
    }
}
