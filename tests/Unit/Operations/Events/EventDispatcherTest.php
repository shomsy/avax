<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Operations\Events;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Unit tests for the EventDispatcher with priority support.
 */
final class EventDispatcherTest extends TestCase
{
    private ListenerRegistry $registry;

    private EventDispatcher $dispatcher;

    #[Test]
    public function dispatch_calls_registered_listener(): void
    {
        $called = false;
        $this->registry->subscribe(
            event   : 'user.created',
            listener: static function () use (&$called): void {
                $called = true;
            },
        );

        $this->dispatcher->dispatch(event: 'user.created');
        $this->assertTrue($called);
    }

    #[Test]
    public function dispatch_passes_data_to_listener(): void
    {
        $receivedData  = null;
        $receivedEvent = null;

        $this->registry->subscribe(
            event   : 'user.created',
            listener: static function ($event, $data) use (&$receivedEvent, &$receivedData): void {
                $receivedEvent = $event;
                $receivedData  = $data;
            },
        );

        $this->dispatcher->dispatch(event: 'user.created', data: ['name' => 'John']);

        $this->assertEquals('user.created', $receivedEvent);
        $this->assertEquals(['name' => 'John'], $receivedData);
    }

    #[Test]
    public function dispatch_calls_multiple_listeners(): void
    {
        $callCount = 0;

        $this->registry->subscribe(
            event   : 'order.placed',
            listener: static function () use (&$callCount): void {
                $callCount++;
            },
        );

        $this->registry->subscribe(
            event   : 'order.placed',
            listener: static function () use (&$callCount): void {
                $callCount++;
            },
        );

        $this->dispatcher->dispatch(event: 'order.placed');
        $this->assertEquals(2, $callCount);
    }

    #[Test]
    public function dispatch_with_object_event(): void
    {
        $event       = new stdClass();
        $event->name = 'TestEvent';

        $receivedEvent = null;

        $this->registry->subscribe(
            event   : stdClass::class,
            listener: static function ($e) use (&$receivedEvent): void {
                $receivedEvent = $e;
            },
        );

        $this->dispatcher->dispatch(event: $event);
        $this->assertSame($event, $receivedEvent);
    }

    #[Test]
    public function dispatch_respects_propagation_stop(): void
    {
        $callCount = 0;

        $event = new class () {
            public bool $propagationStopped = false;

            public function isPropagationStopped(): bool
            {
                return $this->propagationStopped;
            }

            public function stopPropagation(): void
            {
                $this->propagationStopped = true;
            }
        };

        $this->registry->subscribe(
            event   : $event::class,
            listener: static function ($e) use (&$callCount): void {
                $callCount++;
                $e->stopPropagation();
            },
            priority: 10,
        );

        $this->registry->subscribe(
            event   : $event::class,
            listener: static function () use (&$callCount): void {
                $callCount++;
            },
            priority: 5,
        );

        $this->dispatcher->dispatch(event: $event);
        $this->assertEquals(1, $callCount);
    }

    #[Test]
    public function dispatch_listeners_execute_by_priority(): void
    {
        $executionOrder = [];

        $this->registry->subscribe(
            event   : 'test.event',
            listener: static function () use (&$executionOrder): void {
                $executionOrder[] = 'low';
            },
            priority: 1,
        );

        $this->registry->subscribe(
            event   : 'test.event',
            listener: static function () use (&$executionOrder): void {
                $executionOrder[] = 'high';
            },
            priority: 10,
        );

        $this->registry->subscribe(
            event   : 'test.event',
            listener: static function () use (&$executionOrder): void {
                $executionOrder[] = 'medium';
            },
            priority: 5,
        );

        $this->dispatcher->dispatch(event: 'test.event');

        // Higher priority executes first (krsort)
        $this->assertEquals(['high', 'medium', 'low'], $executionOrder);
    }

    #[Test]
    public function dispatch_returns_the_event(): void
    {
        $eventName = 'user.created';
        $result    = $this->dispatcher->dispatch(event: $eventName);

        $this->assertEquals($eventName, $result);
    }

    #[Test]
    public function dispatch_with_no_listeners_returns_event(): void
    {
        $result = $this->dispatcher->dispatch(event: 'no.listeners');
        $this->assertEquals('no.listeners', $result);
    }

    #[Test]
    public function registry_has_listeners_returns_true_when_listeners_exist(): void
    {
        $this->registry->subscribe(event: 'test.event', listener: static function (): void {
        });
        $this->assertTrue($this->registry->hasListeners('test.event'));
    }

    #[Test]
    public function registry_has_listeners_returns_false_when_no_listeners(): void
    {
        $this->assertFalse($this->registry->hasListeners('nonexistent'));
    }

    #[Test]
    public function registry_listener_count_returns_correct_count(): void
    {
        $this->registry->subscribe(event: 'test.event', listener: static function (): void {
        });
        $this->registry->subscribe(event: 'test.event', listener: static function (): void {
        });
        $this->registry->subscribe(event: 'test.event', listener: static function (): void {
        }, priority: 5);

        $this->assertEquals(3, $this->registry->listenerCount('test.event'));
    }

    #[Test]
    public function registry_remove_clears_all_listeners_for_event(): void
    {
        $this->registry->subscribe(event: 'test.event', listener: static function (): void {
        });
        $this->registry->subscribe(event: 'test.event', listener: static function (): void {
        });

        $this->registry->remove('test.event');
        $this->assertEquals(0, $this->registry->listenerCount('test.event'));
        $this->assertFalse($this->registry->hasListeners('test.event'));
    }

    #[Test]
    public function registry_clear_removes_all_listeners(): void
    {
        $this->registry->subscribe(event: 'event1', listener: static function (): void {
        });
        $this->registry->subscribe(event: 'event2', listener: static function (): void {
        });

        $this->registry->clear();

        $this->assertEquals(0, $this->registry->listenerCount('event1'));
        $this->assertEquals(0, $this->registry->listenerCount('event2'));
    }

    protected function setUp(): void
    {
        $this->registry   = new ListenerRegistry();
        $this->dispatcher = new EventDispatcher(registry: $this->registry);
    }
}
