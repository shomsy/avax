<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeAlreadyClosed;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Avax\Tests\Framework\TestCase;

#[CoversClass(RequestScope::class)]
#[CoversClass(RequestScopeId::class)]
final class RequestScopeTest extends TestCase
{
    #[Test]
    public function it_is_open_when_created(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        self::assertTrue($scope->isOpen());
        self::assertInstanceOf(RequestScopeId::class, $scope->id());
    }

    #[Test]
    public function it_stores_and_reads_value(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        $scope->write(key: 'user_id', value: 123);
        $scope->write(key: 'user_name', value: 'Milos');

        self::assertSame(123, $scope->read(key: 'user_id'));
        self::assertSame('Milos', $scope->read(key: 'user_name'));
    }

    #[Test]
    public function it_returns_null_for_missing_key(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        self::assertNull($scope->read(key: 'missing'));
    }

    #[Test]
    public function it_checks_key_existence(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        $scope->write(key: 'exists', value: 'value');

        self::assertTrue($scope->has(key: 'exists'));
        self::assertFalse($scope->has(key: 'missing'));
    }

    #[Test]
    public function it_removes_value(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        $scope->write(key: 'temp', value: 'data');
        self::assertTrue($scope->has(key: 'temp'));

        $scope->remove(key: 'temp');

        self::assertFalse($scope->has(key: 'temp'));
    }

    #[Test]
    public function it_returns_all_values(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        $scope->write(key: 'a', value: 1);
        $scope->write(key: 'b', value: 2);

        $all = $scope->all();

        self::assertCount(2, $all);
        self::assertSame(1, $all['a']);
        self::assertSame(2, $all['b']);
    }

    #[Test]
    public function it_closes_scope_and_clears_values(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());

        $scope->write(key: 'data', value: 'secret');
        $scope->close();

        self::assertFalse($scope->isOpen());
        self::assertSame([], $scope->all());
    }

    #[Test]
    public function it_throws_when_accessing_closed_scope(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());
        $scope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);
        $this->expectExceptionMessage('Request scope is already closed.');

        $scope->read(key: 'any');
    }

    #[Test]
    public function it_throws_when_writing_to_closed_scope(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());
        $scope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);

        $scope->write(key: 'key', value: 'value');
    }

    #[Test]
    public function it_throws_when_checking_closed_scope(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());
        $scope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);

        $scope->has(key: 'key');
    }

    #[Test]
    public function it_throws_when_closing_already_closed_scope(): void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());
        $scope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);

        $scope->close();
    }
}