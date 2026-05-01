<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeAlreadyClosed;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RequestScope::class)]
#[CoversClass(RequestScopeId::class)]
final class RequestScopeTest extends TestCase
{
    #[Test]
    public function it_is_open_when_created(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        self::assertTrue($requestScope->isOpen());
        self::assertInstanceOf(RequestScopeId::class, $requestScope->id());
    }

    #[Test]
    public function it_stores_and_reads_value(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        $requestScope->write(key: 'user_id', value: 123);
        $requestScope->write(key: 'user_name', value: 'Milos');

        self::assertSame(123, $requestScope->read(key: 'user_id'));
        self::assertSame('Milos', $requestScope->read(key: 'user_name'));
    }

    #[Test]
    public function it_returns_null_for_missing_key(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        self::assertNull($requestScope->read(key: 'missing'));
    }

    #[Test]
    public function it_checks_key_existence(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        $requestScope->write(key: 'exists', value: 'value');

        self::assertTrue($requestScope->has(key: 'exists'));
        self::assertFalse($requestScope->has(key: 'missing'));
    }

    #[Test]
    public function it_removes_value(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        $requestScope->write(key: 'temp', value: 'data');
        self::assertTrue($requestScope->has(key: 'temp'));

        $requestScope->remove(key: 'temp');

        self::assertFalse($requestScope->has(key: 'temp'));
    }

    #[Test]
    public function it_returns_all_values(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        $requestScope->write(key: 'a', value: 1);
        $requestScope->write(key: 'b', value: 2);

        $all = $requestScope->all();

        self::assertCount(2, $all);
        self::assertSame(1, $all['a']);
        self::assertSame(2, $all['b']);
    }

    #[Test]
    public function it_closes_scope_and_clears_values(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());

        $requestScope->write(key: 'data', value: 'secret');
        $requestScope->close();

        self::assertFalse($requestScope->isOpen());
        self::assertSame([], $requestScope->all());
    }

    #[Test]
    public function it_throws_when_accessing_closed_scope(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());
        $requestScope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);
        $this->expectExceptionMessage('Request scope is already closed.');

        $requestScope->read(key: 'any');
    }

    #[Test]
    public function it_throws_when_writing_to_closed_scope(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());
        $requestScope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);

        $requestScope->write(key: 'key', value: 'value');
    }

    #[Test]
    public function it_throws_when_checking_closed_scope(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());
        $requestScope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);

        $requestScope->has(key: 'key');
    }

    #[Test]
    public function it_throws_when_closing_already_closed_scope(): void
    {
        $requestScope = new RequestScope(id: RequestScopeId::generate());
        $requestScope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);

        $requestScope->close();
    }
}
