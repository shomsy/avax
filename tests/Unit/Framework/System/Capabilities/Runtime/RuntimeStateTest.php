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
        $runtimeState = new RuntimeState(runtimeName: 'test');

        self::assertFalse($runtimeState->isBooted());
        self::assertSame(0, $runtimeState->bootCount());
        self::assertNull($runtimeState->bootedAt());
        self::assertNull($runtimeState->shutdownAt());
        self::assertSame('test', $runtimeState->runtimeName());
    }

    #[Test]
    public function it_marks_as_booted_and_increments_boot_count(): void
    {
        $runtimeState = new RuntimeState(runtimeName: 'avax');
        $bootedAt = new DateTimeImmutable();

        $runtimeState->markBooted(bootedAt: $bootedAt);

        self::assertTrue($runtimeState->isBooted());
        self::assertSame(1, $runtimeState->bootCount());
        self::assertSame($bootedAt, $runtimeState->bootedAt());
        self::assertNull($runtimeState->shutdownAt());
    }

    #[Test]
    public function it_increments_boot_count_on_each_boot(): void
    {
        $runtimeState = new RuntimeState(runtimeName: 'avax');
        $bootedAt = new DateTimeImmutable();

        $runtimeState->markBooted(bootedAt: $bootedAt);
        $runtimeState->markBooted(bootedAt: $bootedAt);
        $runtimeState->markBooted(bootedAt: $bootedAt);

        self::assertSame(3, $runtimeState->bootCount());
    }

    #[Test]
    public function it_marks_shutdown_and_clears_booted_state(): void
    {
        $runtimeState = new RuntimeState(runtimeName: 'avax');
        $bootedAt = new DateTimeImmutable();
        $shutdownAt = new DateTimeImmutable();

        $runtimeState->markBooted(bootedAt: $bootedAt);
        $runtimeState->markShutdown(shutdownAt: $shutdownAt);

        self::assertFalse($runtimeState->isBooted());
        self::assertSame($shutdownAt, $runtimeState->shutdownAt());
        self::assertNull($runtimeState->bootedAt());
    }

    #[Test]
    public function it_resets_booted_at_when_boot_called_after_shutdown(): void
    {
        $runtimeState = new RuntimeState(runtimeName: 'avax');
        $firstBoot = new DateTimeImmutable('2026-01-01 10:00:00');
        $secondBoot = new DateTimeImmutable('2026-01-01 12:00:00');
        $shutdown  = new DateTimeImmutable('2026-01-01 11:00:00');

        $runtimeState->markBooted(bootedAt: $firstBoot);
        $runtimeState->markShutdown(shutdownAt: $shutdown);
        $runtimeState->markBooted(bootedAt: $secondBoot);

        self::assertTrue($runtimeState->isBooted());
        self::assertSame($secondBoot, $runtimeState->bootedAt());
        self::assertNull($runtimeState->shutdownAt());
        self::assertSame(2, $runtimeState->bootCount());
    }
}
