<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AdminElevationStoreTest extends TestCase
{
    #[Test]
    public function failsClosedByDefault(): void
    {
        $store = new AdminElevationStore();
        self::assertFalse($store->isActive());
    }

    #[Test]
    public function elevateChangesState(): void
    {
        $store = new AdminElevationStore();
        $store->elevate();
        self::assertTrue($store->isActive());
    }

    #[Test]
    public function resetRestoresFailClosed(): void
    {
        $store = new AdminElevationStore();
        $store->elevate();
        $store->reset();
        self::assertFalse($store->isActive());
    }

    #[Test]
    public function multipleElevationsRemainActive(): void
    {
        $store = new AdminElevationStore();
        $store->elevate();
        $store->elevate();
        self::assertTrue($store->isActive());
    }

    #[Test]
    public function instancesAreIsolated(): void
    {
        $a = new AdminElevationStore();
        $b = new AdminElevationStore();
        $a->elevate();
        self::assertTrue($a->isActive());
        self::assertFalse($b->isActive());
    }

    #[Test]
    public function idleResetDoesNotElevate(): void
    {
        $store = new AdminElevationStore();
        $store->reset();
        self::assertFalse($store->isActive());
    }
}
