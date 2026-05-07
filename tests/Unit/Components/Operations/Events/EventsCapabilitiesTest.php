<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Events;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use PHPUnit\Framework\TestCase;

final class EventsCapabilitiesTest extends TestCase
{
    private ListenerRegistry $registry;

    private EventDispatcher $dispatcher;

    public function test_it_returns_empty_array_when_no_listeners_registered() : void
    {
        $this->assertSame([], $this->registry->getListenersFor('some.event'));
    }

    // --- ListenerRegistry ---

    public function test_it_registers_and_retrieves_listener_for_event() : void
    {
        $called   = false;
        $listener = static function () use (&$called) : void {
            $called = true;
        };

        $this->registry->subscribe('user.created', $listener);

        $listeners = $this->registry->getListenersFor('user.created');
        $this->assertCount(1, $listeners);

        $listeners[0]();
        $this->assertTrue($called);
    }

    public function test_it_sorts_listeners_by_priority_descending() : void
    {
        $order = [];
        $this->registry->subscribe('app.boot', static function () use (&$order) : void {
            $order[] = 'low';
        },                         0);
        $this->registry->subscribe('app.boot', static function () use (&$order) : void {
            $order[] = 'high';
        },                         10);
        $this->registry->subscribe('app.boot', static function () use (&$order) : void {
            $order[] = 'medium';
        },                         5);

        $listeners = $this->registry->getListenersFor('app.boot');
        foreach ($listeners as $listener) {
            $listener();
        }

        $this->assertSame(['high', 'medium', 'low'], $order);
    }

    public function test_it_caches_sorted_listeners_and_invalidates_on_new_subscription() : void
    {
        $this->registry->subscribe('cache.test', static function () : void {});

        $first = $this->registry->getListenersFor('cache.test');
        $this->assertCount(1, $first);

        $second = $this->registry->getListenersFor('cache.test');
        $this->assertCount(1, $second);

        $this->registry->subscribe('cache.test', static function () : void {});
        $third = $this->registry->getListenersFor('cache.test');
        $this->assertCount(2, $third);
    }

    public function test_it_clears_all_listeners() : void
    {
        $this->registry->subscribe('event.a', static function () : void {});
        $this->registry->subscribe('event.b', static function () : void {});

        $this->registry->clear();

        $this->assertSame([], $this->registry->getListenersFor('event.a'));
        $this->assertSame([], $this->registry->getListenersFor('event.b'));
    }

    public function test_it_checks_if_event_has_listeners() : void
    {
        $this->assertFalse($this->registry->hasListeners('app.start'));
        $this->registry->subscribe('app.start', static function () : void {});
        $this->assertTrue($this->registry->hasListeners('app.start'));
    }

    public function test_it_counts_listeners_for_event() : void
    {
        $this->assertSame(0, $this->registry->listenerCount('count.test'));
        $this->registry->subscribe('count.test', static function () : void {});
        $this->registry->subscribe('count.test', static function () : void {}, 5);
        $this->assertSame(2, $this->registry->listenerCount('count.test'));
    }

    public function test_it_removes_all_listeners_for_specific_event() : void
    {
        $this->registry->subscribe('remove.me', static function () : void {});
        $this->registry->subscribe('keep.me', static function () : void {});

        $this->registry->remove('remove.me');

        $this->assertFalse($this->registry->hasListeners('remove.me'));
        $this->assertTrue($this->registry->hasListeners('keep.me'));
    }

    public function test_it_dispatches_string_event_to_registered_listeners() : void
    {
        $received = null;
        $this->registry->subscribe('order.placed', static function (string $event, mixed $data) use (&$received) : void {
            $received = $data;
        });

        $this->dispatcher->dispatch('order.placed', ['id' => 42]);
        $this->assertSame(['id' => 42], $received);
    }

    // --- EventDispatcher ---

    public function test_it_returns_event_after_dispatch() : void
    {
        $result = $this->dispatcher->dispatch('no.listeners');
        $this->assertSame('no.listeners', $result);
    }

    public function test_it_stops_propagation_when_event_signals_stop() : void
    {
        $event = new class {
            public bool $stopped = false;

            public function isPropagationStopped() : bool
            {
                return $this->stopped;
            }
        };

        $calls = 0;
        $this->registry->subscribe($event::class, static function (object $e) use (&$calls) : void {
            if (property_exists($e, 'stopped')) {
                $calls++;
                $e->stopped = true;
            }
        });
        $this->registry->subscribe($event::class, static function () use (&$calls) : void {
            $calls++;
        });

        $this->dispatcher->dispatch($event);
        $this->assertSame(1, $calls);
    }

    protected function setUp() : void
    {
        $this->registry   = new ListenerRegistry();
        $this->dispatcher = new EventDispatcher($this->registry);
    }
}
