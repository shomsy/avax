<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Tests\Framework\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RuntimeState::class)]
final class RuntimeStateTest extends TestCase
{
    #[Test]
    public function it_is_not_booted_when_created(): void
    {
        $state = new RuntimeState(runtimeName: 'test');

        self::assertFalse($state->isBooted());
        self::assertSame(0, $state->bootCount());
        self::assertNull($state->bootedAt());
        self::assertNull($state->shutdownAt());
        self::assertSame('test', $state->runtimeName());
    }

    #[Test]
    public function it_marks_as_booted_and_increments_boot_count(): void
    {
        $state = new RuntimeState(runtimeName: 'avax');
        $bootedAt = new DateTimeImmutable();

        $state->markBooted(bootedAt: $bootedAt);

        self::assertTrue($state->isBooted());
        self::assertSame(1, $state->bootCount());
        self::assertSame($bootedAt, $state->bootedAt());
        self::assertNull($state->shutdownAt());
    }

    #[Test]
    public function it_increments_boot_count_on_each_boot(): void
    {
        $state = new RuntimeState(runtimeName: 'avax');
        $bootedAt = new DateTimeImmutable();

        $state->markBooted(bootedAt: $bootedAt);
        $state->markBooted(bootedAt: $bootedAt);
        $state->markBooted(bootedAt: $bootedAt);

        self::assertSame(3, $state->bootCount());
    }

    #[Test]
    public function it_marks_shutdown_and_clears_booted_state(): void
    {
        $state    = new RuntimeState(runtimeName: 'avax');
        $bootedAt = new DateTimeImmutable();
        $shutdownAt = new DateTimeImmutable();

        $state->markBooted(bootedAt: $bootedAt);
        $state->markShutdown(shutdownAt: $shutdownAt);

        self::assertFalse($state->isBooted());
        self::assertSame($shutdownAt, $state->shutdownAt());
        self::assertNull($state->bootedAt());
    }

    #[Test]
    public function it_resets_booted_at_when_boot_called_after_shutdown(): void
    {
        $state     = new RuntimeState(runtimeName: 'avax');
        $firstBoot = new DateTimeImmutable('2026-01-01 10:00:00');
        $secondBoot = new DateTimeImmutable('2026-01-01 12:00:00');
        $shutdown  = new DateTimeImmutable('2026-01-01 11:00:00');

        $state->markBooted(bootedAt: $firstBoot);
        $state->markShutdown(shutdownAt: $shutdown);
        $state->markBooted(bootedAt: $secondBoot);

        self::assertTrue($state->isBooted());
        self::assertSame($secondBoot, $state->bootedAt());
        self::assertNull($state->shutdownAt());
        self::assertSame(2, $state->bootCount());
    }
}
