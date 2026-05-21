<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tenancy;

use Avax\Components\Identity\Tenancy\System\Capabilities\Context\DefaultTenantContext;
use Avax\Components\Identity\Tenancy\System\Configuration\Assembly\TenancyGraph;
use Avax\Components\Identity\Tenancy\System\Foundation\Failure\TenantNotFoundException;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TenancyCharacterizationTest extends TestCase
{
    private Tenancy $tenancy;

    #[Override]
    protected function setUp(): void
    {
        $this->tenancy = $this->tenancy();
    }

    #[Test]
    public function getTenantIdReturnsNullByDefault(): void
    {
        self::assertNull($this->tenancy->getTenantId());
        self::assertNull($this->tenancy->currentTenant());
    }

    #[Test]
    public function requireTenantFailsClosedWhenNoTenantIsSet(): void
    {
        $this->expectException(TenantNotFoundException::class);
        $this->expectExceptionMessage('Tenant context is required.');

        $this->tenancy->requireTenant();
    }

    #[Test]
    public function setTenantIdAndGetTenantIdRoundTrip(): void
    {
        $this->tenancy->setTenantId('tenant-1');
        self::assertSame('tenant-1', $this->tenancy->getTenantId());
        self::assertSame('tenant-1', $this->tenancy->requireTenant());
    }

    #[Test]
    public function clearTenantResetsToNull(): void
    {
        $this->tenancy->setTenantId('tenant-1');
        $this->tenancy->clearTenant();
        self::assertNull($this->tenancy->getTenantId());
    }

    #[Test]
    public function switchUpdatesCurrentTenant(): void
    {
        $this->tenancy->switch('tenant-2');
        self::assertSame('tenant-2', $this->tenancy->getTenantId());
    }

    #[Test]
    public function runExecutesOperationInScopeAndRestoresPrevious(): void
    {
        $this->tenancy->setTenantId('original');

        $result = $this->tenancy->run('scoped', function (): string {
            self::assertSame('scoped', $this->tenancy->getTenantId());
            return 'done';
        });

        self::assertSame('done', $result);
        self::assertSame('original', $this->tenancy->getTenantId());
    }

    #[Test]
    public function runRestoresPreviousEvenOnException(): void
    {
        $this->tenancy->setTenantId('original');

        try {
            $this->tenancy->run('scoped', function (): never {
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
        }

        self::assertSame('original', $this->tenancy->getTenantId());
    }

    #[Test]
    public function separateRuntimesDoNotShareTenantContext(): void
    {
        $first = $this->tenancy();
        $second = $this->tenancy();

        $first->setTenantId('isolated');

        self::assertSame('isolated', $first->getTenantId());
        self::assertNull($second->getTenantId());
    }

    private function tenancy() : Tenancy
    {
        return TenancyGraph::fromContext(context: new DefaultTenantContext());
    }
}
