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
        $stateResetRegistry = new StateResetRegistry();

        $resetCalled = false;

        $stateResetRegistry->register(
            name: 'state1',
            resettableState: new class ($resetCalled) implements ResettableState {
                      public function __construct(private bool &$resetCalled) {}

                public function resetState(): void
                {
                    $this->resetCalled = true;
                }
            },
        );

        $stateResetReport = $stateResetRegistry->resetAll();

        self::assertTrue($resetCalled);
        self::assertTrue($stateResetReport->wasSuccessful());
        self::assertSame(['state1'], $stateResetReport->resetComponents());
        self::assertSame([], $stateResetReport->failures());
    }

    #[Test]
    public function it_resets_multiple_registered_states(): void
    {
        $stateResetRegistry = new StateResetRegistry();

        $called1 = false;
        $called2 = false;

        $stateResetRegistry->register(
            name: 'state1',
            resettableState: new class ($called1) implements ResettableState {
                      public function __construct(private bool &$called) {}

                public function resetState(): void
                {
                    $this->called = true;
                }
            },
        );

        $stateResetRegistry->register(
            name: 'state2',
            resettableState: new class ($called2) implements ResettableState {
                      public function __construct(private bool &$called) {}

                public function resetState(): void
                {
                    $this->called = true;
                }
            },
        );

        $stateResetReport = $stateResetRegistry->resetAll();

        self::assertTrue($called1);
        self::assertTrue($called2);
        self::assertTrue($stateResetReport->wasSuccessful());
        self::assertCount(2, $stateResetReport->resetComponents());
    }

    #[Test]
    public function it_reports_failures_when_state_reset_throws(): void
    {
        $stateResetRegistry = new StateResetRegistry();

        $stateResetRegistry->register(
            name: 'failing',
            resettableState: new class () implements ResettableState {
                public function resetState(): void
                {
                    throw new RuntimeException(message: 'Cannot reset state');
                }
            },
        );

        $stateResetReport = $stateResetRegistry->resetAll();

        self::assertFalse($stateResetReport->wasSuccessful());
        self::assertSame([], $stateResetReport->resetComponents());
        self::assertCount(1, $stateResetReport->failures());
        self::assertArrayHasKey('failing', $stateResetReport->failures());
        self::assertInstanceOf(RuntimeException::class, $stateResetReport->failures()['failing']);
    }

    #[Test]
    public function it_continues_reset_after_one_state_fails(): void
    {
        $stateResetRegistry = new StateResetRegistry();

        $afterCalled = false;

        $stateResetRegistry->register(
            name: 'failing',
            resettableState: new class () implements ResettableState {
                public function resetState(): void
                {
                    throw new RuntimeException(message: 'Failed');
                }
            },
        );

        $stateResetRegistry->register(
            name: 'success',
            resettableState: new class ($afterCalled) implements ResettableState {
                      public function __construct(private bool &$afterCalled) {}

                public function resetState(): void
                {
                    $this->afterCalled = true;
                }
            },
        );

        $stateResetReport = $stateResetRegistry->resetAll();

        self::assertTrue($afterCalled);
        self::assertFalse($stateResetReport->wasSuccessful());
        self::assertContains('success', $stateResetReport->resetComponents());
        self::assertArrayHasKey('failing', $stateResetReport->failures());
    }

    #[Test]
    public function it_returns_empty_report_when_nothing_registered(): void
    {
        $stateResetRegistry = new StateResetRegistry();

        $stateResetReport = $stateResetRegistry->resetAll();

        self::assertTrue($stateResetReport->wasSuccessful());
        self::assertSame([], $stateResetReport->resetComponents());
        self::assertSame([], $stateResetReport->failures());
    }
}
