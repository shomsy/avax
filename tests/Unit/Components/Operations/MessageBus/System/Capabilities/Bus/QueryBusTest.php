<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus\System\Capabilities\Bus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class QueryBusTest extends TestCase
{
    #[Test]
    public function it_dispatches_query_to_registered_handler() : void
    {
        $bus           = new QueryBus();
        $query         = new class {
            public string $filter = 'active';
        };
        $handlerCalled = false;

        $bus->register($query::class, static function (object $q) use (&$handlerCalled) : array {
            $handlerCalled = true;

            return ['filter' => $q->filter];
        });

        $result = $bus->dispatch($query);

        self::assertTrue($handlerCalled);
        self::assertSame(['filter' => 'active'], $result);
    }

    #[Test]
    public function it_returns_handler_result() : void
    {
        $bus   = new QueryBus();
        $query = new class {
            public int $id = 42;
        };

        $bus->register($query::class, static fn (object $q) : string => "user-{$q->id}");

        $result = $bus->dispatch($query);

        self::assertSame('user-42', $result);
    }

    #[Test]
    public function it_throws_when_no_handler_registered() : void
    {
        $bus   = new QueryBus();
        $query = new class {};

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No handler registered for query: ');

        $bus->dispatch($query);
    }

    #[Test]
    public function it_replaces_handler_when_registered_twice_for_same_query() : void
    {
        $bus   = new QueryBus();
        $query = new class {};

        $bus->register($query::class, static fn () : string => 'first');
        $bus->register($query::class, static fn () : string => 'second');

        $result = $bus->dispatch($query);

        self::assertSame('second', $result);
    }

    #[Test]
    public function it_handles_multiple_queries_independently() : void
    {
        $bus    = new QueryBus();
        $queryA = new class {};
        $queryB = new class {};

        $bus->register($queryA::class, static fn () : string => 'A');
        $bus->register($queryB::class, static fn () : string => 'B');

        self::assertSame('A', $bus->dispatch($queryA));
        self::assertSame('B', $bus->dispatch($queryB));
    }

    #[Test]
    public function it_handles_query_returning_null() : void
    {
        $bus   = new QueryBus();
        $query = new class {};

        $bus->register($query::class, static fn () : ?string => null);

        $result = $bus->dispatch($query);

        self::assertNull($result);
    }

    #[Test]
    public function it_handles_query_returning_scalar() : void
    {
        $bus   = new QueryBus();
        $query = new class {};

        $bus->register($query::class, static fn () : int => 100);

        $result = $bus->dispatch($query);

        self::assertSame(100, $result);
    }

    #[Test]
    public function it_handles_query_returning_object() : void
    {
        $bus                = new QueryBus();
        $query              = new class {};
        $resultObject       = new stdClass();
        $resultObject->name = 'test';

        $bus->register($query::class, static fn () => $resultObject);

        $result = $bus->dispatch($query);

        self::assertSame($resultObject, $result);
        self::assertSame('test', $result->name);
    }

    #[Test]
    public function it_distinguishes_queries_by_class_name() : void
    {
        $bus    = new QueryBus();
        $query1 = new class { public string $key = 'a'; };
        $query2 = new class { public string $key = 'b'; };

        $bus->register($query1::class, static fn () : string => 'first');
        $bus->register($query2::class, static fn () : string => 'second');

        self::assertSame('first', $bus->dispatch($query1));
        self::assertSame('second', $bus->dispatch($query2));
    }

    #[Test]
    public function it_propagates_exception_from_handler() : void
    {
        $bus   = new QueryBus();
        $query = new class {};

        $bus->register($query::class, static function () : never {
            throw new RuntimeException('query handler failure');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('query handler failure');

        $bus->dispatch($query);
    }

    #[Test]
    public function it_has_no_middleware_pipeline() : void
    {
        $bus       = new QueryBus();
        $query     = new class {};
        $callCount = 0;

        $bus->register($query::class, static function () use (&$callCount) : string {
            $callCount++;

            return 'ok';
        });

        $bus->dispatch($query);
        $bus->dispatch($query);

        self::assertSame(2, $callCount);
    }
}
