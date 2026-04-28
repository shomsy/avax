<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeNotOpen;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RequestScopeStore::class)]
#[CoversClass(RequestScope::class)]
final class RequestScopeStoreTest extends TestCase
{
    #[Test]
    public function it_has_no_current_scope_when_created(): void
    {
        $store = new RequestScopeStore();

        self::assertFalse($store->hasCurrent());
    }

    #[Test]
    public function it_opens_new_scope_and_sets_current(): void
    {
        $store = new RequestScopeStore();

        $scope = $store->open();

        self::assertTrue($store->hasCurrent());
        self::assertSame($scope, $store->current());
        self::assertTrue($scope->isOpen());
    }

    #[Test]
    public function it_throws_when_opening_scope_while_another_is_active(): void
    {
        $store = new RequestScopeStore();
        $store->open();

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Cannot open a new request scope while another scope is active.');

        $store->open();
    }

    #[Test]
    public function it_closes_current_scope_and_clears_reference(): void
    {
        $store = new RequestScopeStore();
        $scope = $store->open();

        $store->closeCurrent();

        self::assertFalse($store->hasCurrent());
        self::assertFalse($scope->isOpen());
    }

    #[Test]
    public function it_throws_when_getting_current_when_no_scope_exists(): void
    {
        $store = new RequestScopeStore();

        $this->expectException(RequestScopeNotOpen::class);

        $store->current();
    }

    #[Test]
    public function it_resets_state_and_closes_scope(): void
    {
        $store = new RequestScopeStore();
        $scope = $store->open();
        $scope->write(key: 'data', value: 'value');

        $store->resetState();

        self::assertFalse($store->hasCurrent());
        self::assertFalse($scope->isOpen());
    }

    #[Test]
    public function it_can_open_new_scope_after_reset(): void
    {
        $store = new RequestScopeStore();
        $store->open();
        $store->resetState();

        $newScope = $store->open();

        self::assertTrue($store->hasCurrent());
        self::assertNotSame($store->current()->id()->toString(), $newScope->id()->toString());
    }

    #[Test]
    public function it_can_open_new_scope_after_close(): void
    {
        $store = new RequestScopeStore();
        $firstScope = $store->open();
        $store->closeCurrent();

        $secondScope = $store->open();

        self::assertTrue($store->hasCurrent());
        self::assertNotSame($firstScope->id()->toString(), $secondScope->id()->toString());
    }

    #[Test]
    public function it_generates_unique_scope_ids(): void
    {
        $store = new RequestScopeStore();

        $scope1 = $store->open();
        $store->closeCurrent();
        $scope2 = $store->open();

        self::assertNotSame($scope1->id()->toString(), $scope2->id()->toString());
    }

    #[Test]
    public function it_does_not_leak_value_between_two_scopes(): void
    {
        $store = new RequestScopeStore();

        $store->open();
        $store->current()->write(key: 'secret', value: 'from-first');

        $store->closeCurrent();
        $store->open();

        self::assertNull($store->current()->read(key: 'secret'));
    }
}