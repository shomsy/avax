<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Events;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Flows\RegisterEventListeners\EventListenerDsl;
use Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Avax\Components\Operations\Events\System\PublicSurface\onEvent;
use function Avax\Components\Operations\Events\System\PublicSurface\onEventSetRegistry;

final class EventDslTest extends TestCase
{
    private ListenerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ListenerRegistry();
        onEventSetRegistry($this->registry);
    }

    protected function tearDown(): void
    {
        GlobalEventListenerState::reset();
    }

    // --- EventListenerDsl ---

    #[Test]
    public function dsl_returns_self_for_chaining(): void
    {
        $dsl = new EventListenerDsl('TestEvent', $this->registry);
        $result = $dsl->do(static fn () => null);
        self::assertSame($dsl, $result);
    }

    #[Test]
    public function dsl_registers_listener_via_subscribe(): void
    {
        $called = false;
        $listener = static function () use (&$called): void {
            $called = true;
        };

        $dsl = new EventListenerDsl('UserRegistered', $this->registry);
        $dsl->do($listener);

        $listeners = $this->registry->listenersFor('UserRegistered');
        self::assertCount(1, $listeners);

        $listeners[0]();
        self::assertTrue($called);
    }

    #[Test]
    public function dsl_accepts_class_string_listener(): void
    {
        $dsl = new EventListenerDsl('OrderPaid', $this->registry);
        $result = $dsl->do(InvokableListener::class);

        self::assertSame($dsl, $result);
        self::assertCount(1, $this->registry->listenersFor('OrderPaid'));
    }

    #[Test]
    public function dsl_supports_priority(): void
    {
        $order = [];
        $dsl = new EventListenerDsl('AppBooted', $this->registry);

        $dsl->do(static function () use (&$order): void { $order[] = 'low'; }, priority: 0);
        $dsl->do(static function () use (&$order): void { $order[] = 'high'; }, priority: 100);

        foreach ($this->registry->listenersFor('AppBooted') as $listener) {
            $listener();
        }

        self::assertSame(['high', 'low'], $order);
    }

    #[Test]
    public function dsl_chains_multiple_listeners(): void
    {
        $order = [];
        onEvent('OrderCreated') // @phpstan-ignore-line
            ->do(static function () use (&$order): void { $order[] = 'first'; }, priority: 100)
            ->do(static function () use (&$order): void { $order[] = 'second'; }, priority: 50)
            ->do(static function () use (&$order): void { $order[] = 'third'; }, priority: 0);

        foreach ($this->registry->listenersFor('OrderCreated') as $listener) {
            $listener();
        }

        self::assertSame(['first', 'second', 'third'], $order);
    }

    // --- onEvent() global helper ---

    #[Test]
    public function onEvent_returns_dsl_instance(): void
    {
        $result = onEvent('TestEvent'); // @phpstan-ignore-line
        self::assertInstanceOf(EventListenerDsl::class, $result); // @phpstan-ignore-line
    }

    #[Test]
    public function onEvent_registers_listeners_through_global_registry(): void
    {
        $called = false;
        onEvent('UserCreated') // @phpstan-ignore-line
            ->do(static function () use (&$called): void {
                $called = true;
            });

        $listeners = $this->registry->listenersFor('UserCreated');
        self::assertCount(1, $listeners);

        $listeners[0]();
        self::assertTrue($called);
    }

    #[Test]
    public function onEvent_chains_multiple_do_calls(): void
    {
        $count = 0;
        onEvent('MultiListener') // @phpstan-ignore-line
            ->do(static function () use (&$count): void { $count++; })
            ->do(static function () use (&$count): void { $count++; })
            ->do(static function () use (&$count): void { $count++; });

        self::assertCount(3, $this->registry->listenersFor('MultiListener'));
    }

    #[Test]
    public function onEvent_respects_priority_ordering(): void
    {
        $order = [];
        onEvent('PriorityEvent') // @phpstan-ignore-line
            ->do(static function () use (&$order): void { $order[] = 'medium'; }, priority: 50)
            ->do(static function () use (&$order): void { $order[] = 'highest'; }, priority: 200)
            ->do(static function () use (&$order): void { $order[] = 'lowest'; }, priority: -10);

        foreach ($this->registry->listenersFor('PriorityEvent') as $listener) {
            $listener();
        }

        self::assertSame(['highest', 'medium', 'lowest'], $order);
    }

    #[Test]
    public function onEvent_preserves_registration_order_for_same_priority(): void
    {
        $order = [];
        onEvent('SamePriority') // @phpstan-ignore-line
            ->do(static function () use (&$order): void { $order[] = 'first'; })
            ->do(static function () use (&$order): void { $order[] = 'second'; })
            ->do(static function () use (&$order): void { $order[] = 'third'; });

        foreach ($this->registry->listenersFor('SamePriority') as $listener) {
            $listener();
        }

        self::assertSame(['first', 'second', 'third'], $order);
    }

    #[Test]
    public function onEvent_uses_shared_registry_across_calls(): void
    {
        onEvent('SharedEvent') // @phpstan-ignore-line
            ->do(static fn () => null);

        onEvent('SharedEvent') // @phpstan-ignore-line
            ->do(static fn () => null);

        self::assertCount(2, $this->registry->listenersFor('SharedEvent'));
    }

    #[Test]
    public function onEvent_default_priority_is_zero(): void
    {
        $order = [];
        onEvent('DefaultPriority') // @phpstan-ignore-line
            ->do(static function () use (&$order): void { $order[] = 'first'; })
            ->do(static function () use (&$order): void { $order[] = 'second'; });

        foreach ($this->registry->listenersFor('DefaultPriority') as $listener) {
            $listener();
        }

        self::assertSame(['first', 'second'], $order);
    }
}

/**
 * Simple invokable class used as a listener in tests.
 */
final class InvokableListener
{
    public function __invoke(): void
    {
    }
}
