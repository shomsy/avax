<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Tenancy;

use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContext;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TenancyCharacterizationTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        TenantContext::clear();
    }

    #[Test]
    public function getTenantIdReturnsNullByDefault(): void
    {
        self::assertNull(Tenancy::getTenantId());
    }

    #[Test]
    public function setTenantIdAndGetTenantIdRoundTrip(): void
    {
        Tenancy::setTenantId('tenant-1');
        self::assertSame('tenant-1', Tenancy::getTenantId());
    }

    #[Test]
    public function clearTenantResetsToNull(): void
    {
        Tenancy::setTenantId('tenant-1');
        Tenancy::clearTenant();
        self::assertNull(Tenancy::getTenantId());
    }

    #[Test]
    public function switchUpdatesCurrentTenant(): void
    {
        Tenancy::switch('tenant-2');
        self::assertSame('tenant-2', Tenancy::getTenantId());
    }

    #[Test]
    public function runExecutesOperationInScopeAndRestoresPrevious(): void
    {
        Tenancy::setTenantId('original');

        $result = Tenancy::run('scoped', function (): string {
            self::assertSame('scoped', Tenancy::getTenantId());
            return 'done';
        });

        self::assertSame('done', $result);
        self::assertSame('original', Tenancy::getTenantId());
    }

    #[Test]
    public function runRestoresPreviousEvenOnException(): void
    {
        Tenancy::setTenantId('original');

        try {
            Tenancy::run('scoped', function (): never {
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
        }

        self::assertSame('original', Tenancy::getTenantId());
    }

    #[Test]
    public function allMethodsAreStatic(): void
    {
        $reflection = new \ReflectionClass(Tenancy::class);
        foreach ($reflection->getMethods() as $method) {
            self::assertTrue(
                $method->isStatic(),
                "Tenancy::{$method->getName()}() should be static",
            );
        }
    }

    #[Test]
    public function staticStateIsIsolatedPerProcess(): void
    {
        TenantContext::clear();
        self::assertNull(Tenancy::getTenantId());
        Tenancy::setTenantId('isolated');
        self::assertSame('isolated', Tenancy::getTenantId());
    }
}
