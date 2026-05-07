<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\RuntimeException;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use PHPUnit\Framework\TestCase;

final class MessageBusCapabilitiesTest extends TestCase
{
    // --- CommandBus ---

    public function test_it_dispatches_command_to_registered_handler() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};
        $handler = new class {
            public function __invoke(object $cmd) : string
            {
                return 'handled';
            }
        };

        $bus->register($command::class, $handler);
        $result = $bus->dispatch($command);
        $this->assertSame('handled', $result);
    }

    public function test_it_throws_when_no_handler_registered_for_command() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};

        $this->expectException(RuntimeException::class);
        $bus->dispatch($command);
    }

    // --- QueryBus ---

    public function test_it_dispatches_query_to_registered_handler() : void
    {
        $bus     = new QueryBus();
        $query   = new class {};
        $handler = new class {
            /** @return array<string, bool> */
            public function __invoke(object $q) : array
            {
                return ['result' => true];
            }
        };

        $bus->register($query::class, $handler);
        $result = $bus->dispatch($query);
        $this->assertSame(['result' => true], $result);
    }

    public function test_it_throws_when_no_handler_registered_for_query() : void
    {
        $bus   = new QueryBus();
        $query = new class {};

        $this->expectException(RuntimeException::class);
        $bus->dispatch($query);
    }

    // --- EventBus ---

    public function test_it_dispatches_event_to_all_registered_handlers() : void
    {
        $bus   = new EventBus();
        $event = new class {};
        $calls = 0;

        $handler1 = new class {
            public int $called = 0;

            public function __invoke(object $e) : void
            {
                $this->called++;
            }
        };
        $handler2 = new class {
            public int $called = 0;

            public function __invoke(object $e) : void
            {
                $this->called++;
            }
        };

        $bus->register($event::class, $handler1);
        $bus->register($event::class, $handler2);
        $bus->dispatch($event);

        $this->assertSame(1, $handler1->called);
        $this->assertSame(1, $handler2->called);
    }

    public function test_it_does_nothing_when_event_has_no_handlers() : void
    {
        $bus   = new EventBus();
        $event = new class {};

        // Should not throw
        $this->expectNotToPerformAssertions();
        $bus->dispatch($event);
    }

    public function test_it_returns_handlers_for_registered_event() : void
    {
        $bus        = new EventBus();
        $eventClass = 'TestEvent';
        $handler    = new class {
            public function __invoke(object $e) : void {}
        };

        $bus->register($eventClass, $handler);
        $handlers = $bus->HandlersFor($eventClass);
        $this->assertCount(1, $handlers);
    }

    public function test_it_returns_empty_array_for_unregistered_event_handlers() : void
    {
        $bus = new EventBus();
        $this->assertSame([], $bus->HandlersFor('UnknownEvent'));
    }
}
