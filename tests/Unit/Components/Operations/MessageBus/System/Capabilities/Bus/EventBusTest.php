<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus\System\Capabilities\Bus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\DomainEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventBusTest extends TestCase
{
    #[Test]
    public function it_dispatches_event_to_single_handler() : void
    {
        $bus           = new EventBus();
        $event         = new class implements DomainEvent {
            public string $name = 'test';
        };
        $handlerCalled = false;

        $bus->register($event::class, static function (object $e) use (&$handlerCalled) : void {
            $handlerCalled = true;
            self::assertSame('test', $e->name);
        });

        $bus->dispatch($event);

        self::assertTrue($handlerCalled);
    }

    #[Test]
    public function it_dispatches_event_to_multiple_handlers() : void
    {
        $bus       = new EventBus();
        $event     = new class implements DomainEvent {};
        $callOrder = [];

        $bus->register($event::class, static function () use (&$callOrder) : void {
            $callOrder[] = 'handler1';
        });
        $bus->register($event::class, static function () use (&$callOrder) : void {
            $callOrder[] = 'handler2';
        });
        $bus->register($event::class, static function () use (&$callOrder) : void {
            $callOrder[] = 'handler3';
        });

        $bus->dispatch($event);

        self::assertSame(['handler1', 'handler2', 'handler3'], $callOrder);
    }

    #[Test]
    public function it_does_nothing_when_no_handlers_registered() : void
    {
        $bus   = new EventBus();
        $event = new class implements DomainEvent {};

        $bus->dispatch($event);

        self::assertTrue(true);
    }

    #[Test]
    public function it_dispatches_only_handlers_for_specific_event_type() : void
    {
        $bus    = new EventBus();
        $eventA = new class implements DomainEvent {};
        $eventB = new class implements DomainEvent {};

        $bus->register($eventA::class, static fn () : string => 'A called');
        $bus->register($eventB::class, static fn () : string => 'B called');

        $aCalled = false;
        $bCalled = false;

        $bus->register($eventA::class, static function () use (&$aCalled) : void {
            $aCalled = true;
        });
        $bus->register($eventB::class, static function () use (&$bCalled) : void {
            $bCalled = true;
        });

        $bus->dispatch($eventA);

        self::assertTrue($aCalled);
        self::assertFalse($bCalled);
    }

    #[Test]
    public function it_returns_handlers_for_event_class() : void
    {
        $bus     = new EventBus();
        $event   = new class implements DomainEvent {};
        $handler = static function () : void {};

        $bus->register($event::class, $handler);

        $handlers = $bus->handlersFor($event::class);

        self::assertCount(1, $handlers);
        self::assertSame($handler, $handlers[0]);
    }

    #[Test]
    public function it_returns_empty_array_for_unknown_event_class() : void
    {
        $bus = new EventBus();

        $handlers = $bus->handlersFor('UnknownEventClass');

        self::assertSame([], $handlers);
    }

    #[Test]
    public function it_returns_all_handlers_for_registered_event() : void
    {
        $bus   = new EventBus();
        $event = new class implements DomainEvent {};

        $bus->register($event::class, static function () : void {});
        $bus->register($event::class, static function () : void {});

        $handlers = $bus->handlersFor($event::class);

        self::assertCount(2, $handlers);
    }

    #[Test]
    public function it_handles_event_with_payload() : void
    {
        $bus   = new EventBus();
        $event = new class implements DomainEvent {
            public array $data = ['key' => 'value'];
        };

        $receivedData = null;

        $bus->register($event::class, static function (object $e) use (&$receivedData) : void {
            $receivedData = $e->data;
        });

        $bus->dispatch($event);

        self::assertSame(['key' => 'value'], $receivedData);
    }

    #[Test]
    public function it_dispatches_same_event_type_multiple_times() : void
    {
        $bus           = new EventBus();
        $event         = new class implements DomainEvent {
            public int $counter = 0;
        };
        $dispatchCount = 0;

        $bus->register($event::class, static function (object $e) use (&$dispatchCount) : void {
            $dispatchCount++;
        });

        $bus->dispatch($event);
        $bus->dispatch($event);
        $bus->dispatch($event);

        self::assertSame(3, $dispatchCount);
    }

    #[Test]
    public function it_handles_multiple_event_types_independently() : void
    {
        $bus    = new EventBus();
        $eventA = new class implements DomainEvent { public string $type = 'A'; };
        $eventB = new class implements DomainEvent { public string $type = 'B'; };

        $aCount = 0;
        $bCount = 0;

        $bus->register($eventA::class, static function () use (&$aCount) : void { $aCount++; });
        $bus->register($eventB::class, static function () use (&$bCount) : void { $bCount++; });

        $bus->dispatch($eventA);
        $bus->dispatch($eventB);
        $bus->dispatch($eventA);

        self::assertSame(2, $aCount);
        self::assertSame(1, $bCount);
    }

    #[Test]
    public function it_passes_exact_event_instance_to_handler() : void
    {
        $bus           = new EventBus();
        $event         = new class implements DomainEvent {
            public string $id = 'unique-123';
        };
        $receivedEvent = null;

        $bus->register($event::class, static function (object $e) use (&$receivedEvent) : void {
            $receivedEvent = $e;
        });

        $bus->dispatch($event);

        self::assertSame($event, $receivedEvent);
    }
}
