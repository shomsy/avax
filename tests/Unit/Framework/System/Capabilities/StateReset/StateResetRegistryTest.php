<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\StateReset;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

#[CoversClass(StateResetRegistry::class)]
#[CoversClass(StateResetReport::class)]
final class StateResetRegistryTest extends TestCase
{
    #[Test]
    public function it_resets_all_registered_states(): void
    {
        $registry = new StateResetRegistry();

        $resetCalled = false;

        $registry->register(
            name: 'state1',
            state: new class($resetCalled) implements ResettableState {
                public function __construct(private bool &$resetCalled)
                {
                }

                public function resetState(): void
                {
                    $this->resetCalled = true;
                }
            },
        );

        $report = $registry->resetAll();

        self::assertTrue($resetCalled);
        self::assertTrue($report->wasSuccessful());
        self::assertSame(['state1'], $report->resetComponents());
        self::assertSame([], $report->failures());
    }

    #[Test]
    public function it_resets_multiple_registered_states(): void
    {
        $registry = new StateResetRegistry();

        $called1 = false;
        $called2 = false;

        $registry->register(
            name: 'state1',
            state: new class($called1) implements ResettableState {
                public function __construct(private bool &$called)
                {
                }

                public function resetState(): void
                {
                    $this->called = true;
                }
            },
        );

        $registry->register(
            name: 'state2',
            state: new class($called2) implements ResettableState {
                public function __construct(private bool &$called)
                {
                }

                public function resetState(): void
                {
                    $this->called = true;
                }
            },
        );

        $report = $registry->resetAll();

        self::assertTrue($called1);
        self::assertTrue($called2);
        self::assertTrue($report->wasSuccessful());
        self::assertCount(2, $report->resetComponents());
    }

    #[Test]
    public function it_reports_failures_when_state_reset_throws(): void
    {
        $registry = new StateResetRegistry();

        $registry->register(
            name: 'failing',
            state: new class implements ResettableState {
                public function resetState(): void
                {
                    throw new RuntimeException(message: 'Cannot reset state');
                }
            },
        );

        $report = $registry->resetAll();

        self::assertFalse($report->wasSuccessful());
        self::assertSame([], $report->resetComponents());
        self::assertCount(1, $report->failures());
        self::assertArrayHasKey('failing', $report->failures());
        self::assertInstanceOf(RuntimeException::class, $report->failures()['failing']);
    }

    #[Test]
    public function it_continues_reset_after_one_state_fails(): void
    {
        $registry = new StateResetRegistry();

        $afterCalled = false;

        $registry->register(
            name: 'failing',
            state: new class implements ResettableState {
                public function resetState(): void
                {
                    throw new RuntimeException(message: 'Failed');
                }
            },
        );

        $registry->register(
            name: 'success',
            state: new class($afterCalled) implements ResettableState {
                public function __construct(private bool &$afterCalled)
                {
                }

                public function resetState(): void
                {
                    $this->afterCalled = true;
                }
            },
        );

        $report = $registry->resetAll();

        self::assertTrue($afterCalled);
        self::assertFalse($report->wasSuccessful());
        self::assertContains('success', $report->resetComponents());
        self::assertArrayHasKey('failing', $report->failures());
    }

    #[Test]
    public function it_returns_empty_report_when_nothing_registered(): void
    {
        $registry = new StateResetRegistry();

        $report = $registry->resetAll();

        self::assertTrue($report->wasSuccessful());
        self::assertSame([], $report->resetComponents());
        self::assertSame([], $report->failures());
    }
}