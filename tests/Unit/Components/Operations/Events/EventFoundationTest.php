<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Events;

use Avax\Components\Operations\Events\System\Capabilities\ListenerProvider\ListenerProvider;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\CompiledListener;
use Avax\Components\Operations\Events\System\Foundation\ListenerExecutionMode;
use Avax\Components\Operations\Events\System\Foundation\ListenerRegistration;
use Avax\Components\Operations\Events\System\Foundation\ListenerSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventFoundationTest extends TestCase
{
    // --- ListenerRegistration ---

    #[Test]
    public function listener_registration_stores_event_listener_priority_source() : void
    {
        $called = false;
        $listener = static function () use (&$called) : void {
            $called = true;
        };

        $source = ListenerSource::Dsl;
        $mode = ListenerExecutionMode::Sync;

        $registration = new ListenerRegistration(
            eventClass: 'UserRegistered',
            listener: $listener,
            priority: 50,
            source: $source,
            mode: $mode,
            order: 1,
        );

        self::assertSame('UserRegistered', $registration->eventClass);
        self::assertSame(50, $registration->priority);
        self::assertSame('dsl', $registration->source->value); // @phpstan-ignore-line
        self::assertSame('sync', $registration->mode->value); // @phpstan-ignore-line
        self::assertSame(1, $registration->order);

        ($registration->listener)();
        self::assertTrue($called);
    }

    // --- CompiledListener ---

    #[Test]
    public function compiled_listener_stores_dispatch_metadata() : void
    {
        $listener = static function () : void {};

        $source = ListenerSource::Attribute;
        $mode = ListenerExecutionMode::Sync;

        $compiled = new CompiledListener(
            eventClass: 'OrderPaid',
            listener: $listener,
            priority: 100,
            source: $source,
            mode: $mode,
            order: 3,
        );

        self::assertSame('OrderPaid', $compiled->eventClass);
        self::assertSame(100, $compiled->priority);
        self::assertSame('attribute', $compiled->source->value); // @phpstan-ignore-line
        self::assertSame('sync', $compiled->mode->value); // @phpstan-ignore-line
        self::assertSame(3, $compiled->order);
    }

    // --- ListenerRegistry with ListenerRegistration ---

    private ListenerRegistry $registry;

    protected function setUp() : void
    {
        $this->registry = new ListenerRegistry();
    }

    #[Test]
    public function listener_registry_returns_empty_list_when_no_listener_exists() : void
    {
        self::assertSame([], $this->registry->getListenersFor('NoListeners'));
        self::assertSame([], $this->registry->listenersFor('NoListeners'));
    }

    #[Test]
    public function listener_registry_returns_registered_listeners_for_event() : void
    {
        $called = false;
        $listener = static function () use (&$called) : void {
            $called = true;
        };

        $registration = new ListenerRegistration(
            eventClass: 'UserRegistered',
            listener: $listener,
            source: ListenerSource::Dsl,
        );

        $this->registry->register($registration);

        $listeners = $this->registry->listenersFor('UserRegistered');
        self::assertCount(1, $listeners);

        $listeners[0]();
        self::assertTrue($called);
    }

    #[Test]
    public function listener_registry_sorts_by_priority_descending() : void
    {
        $order = [];
        $low = static function () use (&$order) : void {
            $order[] = 'low';
        };
        $high = static function () use (&$order) : void {
            $order[] = 'high';
        };
        $medium = static function () use (&$order) : void {
            $order[] = 'medium';
        };

        $this->registry->register(new ListenerRegistration(
            eventClass: 'AppBooted',
            listener: $low,
            priority: 0,
        ));
        $this->registry->register(new ListenerRegistration(
            eventClass: 'AppBooted',
            listener: $high,
            priority: 100,
        ));
        $this->registry->register(new ListenerRegistration(
            eventClass: 'AppBooted',
            listener: $medium,
            priority: 50,
        ));

        $listeners = $this->registry->listenersFor('AppBooted');
        foreach ($listeners as $listener) {
            $listener();
        }

        self::assertSame(['high', 'medium', 'low'], $order);
    }

    #[Test]
    public function listener_registry_preserves_registration_order_for_same_priority() : void
    {
        $order = [];
        $first = static function () use (&$order) : void {
            $order[] = 'first';
        };
        $second = static function () use (&$order) : void {
            $order[] = 'second';
        };
        $third = static function () use (&$order) : void {
            $order[] = 'third';
        };

        $this->registry->register(new ListenerRegistration(
            eventClass: 'OrderPlaced',
            listener: $first,
            priority: 0,
        ));
        $this->registry->register(new ListenerRegistration(
            eventClass: 'OrderPlaced',
            listener: $second,
            priority: 0,
        ));
        $this->registry->register(new ListenerRegistration(
            eventClass: 'OrderPlaced',
            listener: $third,
            priority: 0,
        ));

        $listeners = $this->registry->listenersFor('OrderPlaced');
        foreach ($listeners as $listener) {
            $listener();
        }

        self::assertSame(['first', 'second', 'third'], $order);
    }

    // --- ListenerProvider ---

    #[Test]
    public function listener_provider_returns_iterable_for_event() : void
    {
        $called = false;
        $listener = static function () use (&$called) : void {
            $called = true;
        };

        $this->registry->subscribe('UserRegistered', $listener);

        $provider = new ListenerProvider($this->registry);
        $event = new class {};

        $listeners = iterator_to_array($provider->listenersFor($event));
        self::assertCount(0, $listeners);
    }

    #[Test]
    public function listener_provider_returns_registered_listeners_via_object() : void
    {
        $called = false;
        $listener = static function () use (&$called) : void {
            $called = true;
        };

        $this->registry->subscribe('stdClass', $listener);

        $provider = new ListenerProvider($this->registry);
        $event = new \stdClass();

        $listeners = iterator_to_array($provider->listenersFor($event));
        self::assertCount(1, $listeners);

        $listeners[0]();
        self::assertTrue($called);
    }

    // --- No forced interfaces ---

    #[Test]
    public function event_contracts_do_not_require_event_interface() : void
    {
        $userEvent = new class {
            public function __construct(
                public readonly string $userId = 'test-user',
            ) {}
        };

        $received = null;
        $this->registry->subscribe($userEvent::class, static function ($event) use (&$received) : void {
            $received = $event;
        });

        $listeners = $this->registry->listenersFor($userEvent::class);
        self::assertCount(1, $listeners);

        $listeners[0]($userEvent);
        self::assertSame($userEvent, $received);
    }

    #[Test]
    public function listener_contracts_do_not_require_listener_interface() : void
    {
        $called = false;

        $plainListener = static function () use (&$called) : void {
            $called = true;
        };

        $registration = new ListenerRegistration(
            eventClass: 'UserRegistered',
            listener: $plainListener,
        );

        $this->registry->register($registration);

        $listeners = $this->registry->listenersFor('UserRegistered');
        self::assertCount(1, $listeners);

        $listeners[0]();
        self::assertTrue($called);
    }

    // --- Execution mode ---

    #[Test]
    public function sync_execution_mode_has_correct_value() : void
    {
        $mode = ListenerExecutionMode::Sync;
        self::assertSame('sync', $mode->value); // @phpstan-ignore-line
    }

    #[Test]
    public function listener_source_enum_values_are_valid() : void
    {
        $dsl = ListenerSource::Dsl;
        $attr = ListenerSource::Attribute;
        $config = ListenerSource::Configuration;

        self::assertSame('dsl', $dsl->value); // @phpstan-ignore-line
        self::assertSame('attribute', $attr->value); // @phpstan-ignore-line
        self::assertSame('configuration', $config->value); // @phpstan-ignore-line
        self::assertNotSame($dsl->value, $attr->value);
        self::assertNotSame($attr->value, $config->value);
    }
}
