<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus\System\PublicSurface;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Avax\Components\Operations\MessageBus\System\PublicSurface\DomainEvent;
use Avax\Components\Operations\MessageBus\System\PublicSurface\MessageBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Query;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MessageBusTest extends TestCase
{
    #[Test]
    public function it_dispatches_command() : void
    {
        $command = new class implements Command {
            public string $name = 'cmd-a';
        };

        MessageBus::listen($command::class, static fn (Command $c) : string => "handled: {$c->name}");

        $result = MessageBus::dispatch($command);

        self::assertSame('handled: cmd-a', $result);
    }

    #[Test]
    public function it_dispatches_query() : void
    {
        $query = new class implements Query {
            public int $id = 42;
        };

        MessageBus::listen($query::class, static fn (Query $q) : array => ['id' => $q->id]);

        $result = MessageBus::query($query);

        self::assertSame(['id' => 42], $result);
    }

    #[Test]
    public function it_publishes_event() : void
    {
        $event    = new class implements DomainEvent {
            public string $action = 'evt-a';
        };
        $received = null;

        MessageBus::listen($event::class, static function (DomainEvent $e) use (&$received) : void {
            $received = $e->action;
        });

        MessageBus::publish($event);

        self::assertSame('evt-a', $received);
    }

    #[Test]
    public function it_publishes_event_to_multiple_handlers() : void
    {
        $event = new class implements DomainEvent {};
        $count = 0;

        MessageBus::listen($event::class, static function () use (&$count) : void { $count++; });
        MessageBus::listen($event::class, static function () use (&$count) : void { $count++; });

        MessageBus::publish($event);

        self::assertSame(2, $count);
    }

    #[Test]
    public function it_throws_when_dispatching_unregistered_command() : void
    {
        $command = new class implements Command {
            public string $marker = 'unregistered-cmd-x';
        };

        $this->expectException(RuntimeException::class);

        MessageBus::dispatch($command);
    }

    #[Test]
    public function it_throws_when_querying_unregistered_query() : void
    {
        $query = new class implements Query {
            public string $marker = 'unregistered-qry-x';
        };

        $this->expectException(RuntimeException::class);

        MessageBus::query($query);
    }

    #[Test]
    public function it_does_nothing_when_publishing_unregistered_event() : void
    {
        $event = new class implements DomainEvent {
            public string $marker = 'unregistered-evt-x';
        };

        MessageBus::publish($event);

        self::assertTrue(true);
    }

    #[Test]
    public function it_routes_command_to_command_bus() : void
    {
        $command = new class implements Command {
            public int    $x   = 10;
            public string $tag = 'route-cmd';
        };

        MessageBus::listen($command::class, static fn (Command $c) : int => $c->x * 2);

        $result = MessageBus::dispatch($command);

        self::assertSame(20, $result);
    }

    #[Test]
    public function it_routes_query_to_query_bus() : void
    {
        $query = new class implements Query {
            public string $filter = 'active';
            public string $tag    = 'route-qry';
        };

        MessageBus::listen($query::class, static fn (Query $q) : string => strtoupper($q->filter));

        $result = MessageBus::query($query);

        self::assertSame('ACTIVE', $result);
    }

    #[Test]
    public function it_routes_event_to_event_bus() : void
    {
        $event    = new class implements DomainEvent {
            public string $type = 'notification';
            public string $tag  = 'route-evt';
        };
        $received = null;

        MessageBus::listen($event::class, static function (DomainEvent $e) use (&$received) : void {
            $received = $e->type;
        });

        MessageBus::publish($event);

        self::assertSame('notification', $received);
    }

    #[Test]
    public function it_handles_all_three_message_types_independently() : void
    {
        $command = new class implements Command { public string $tag = 'all-cmd'; };
        $query   = new class implements Query { public string $tag = 'all-qry'; };
        $event   = new class implements DomainEvent { public string $tag = 'all-evt'; };

        $commandResult = null;
        $queryResult   = null;
        $eventResult   = null;

        MessageBus::listen($command::class, static function () use (&$commandResult) : string {
            $commandResult = 'command';

            return 'cmd-done';
        });
        MessageBus::listen($query::class, static function () use (&$queryResult) : string {
            $queryResult = 'query';

            return 'qry-done';
        });
        MessageBus::listen($event::class, static function () use (&$eventResult) : void {
            $eventResult = 'event';
        });

        $cmdReturn = MessageBus::dispatch($command);
        $qryReturn = MessageBus::query($query);
        MessageBus::publish($event);

        self::assertSame('command', $commandResult);
        self::assertSame('query', $queryResult);
        self::assertSame('event', $eventResult);
        self::assertSame('cmd-done', $cmdReturn);
        self::assertSame('qry-done', $qryReturn);
    }
}
