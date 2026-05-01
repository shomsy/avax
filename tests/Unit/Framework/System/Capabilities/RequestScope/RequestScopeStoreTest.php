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
        $requestScopeStore = new RequestScopeStore();

        self::assertFalse($requestScopeStore->hasCurrent());
    }

    #[Test]
    public function it_opens_new_scope_and_sets_current(): void
    {
        $requestScopeStore = new RequestScopeStore();

        $requestScope = $requestScopeStore->open();

        self::assertTrue($requestScopeStore->hasCurrent());
        self::assertSame($requestScope, $requestScopeStore->current());
        self::assertTrue($requestScope->isOpen());
    }

    #[Test]
    public function it_throws_when_opening_scope_while_another_is_active(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $requestScopeStore->open();

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Cannot open a new request scope while another scope is active.');

        $requestScopeStore->open();
    }

    #[Test]
    public function it_closes_current_scope_and_clears_reference(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $requestScope      = $requestScopeStore->open();

        $requestScopeStore->closeCurrent();

        self::assertFalse($requestScopeStore->hasCurrent());
        self::assertFalse($requestScope->isOpen());
    }

    #[Test]
    public function it_throws_when_getting_current_when_no_scope_exists(): void
    {
        $requestScopeStore = new RequestScopeStore();

        $this->expectException(RequestScopeNotOpen::class);

        $requestScopeStore->current();
    }

    #[Test]
    public function it_resets_state_and_closes_scope(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $requestScope      = $requestScopeStore->open();
        $requestScope->write(key: 'data', value: 'value');

        $requestScopeStore->resetState();

        self::assertFalse($requestScopeStore->hasCurrent());
        self::assertFalse($requestScope->isOpen());
    }

    #[Test]
    public function it_can_open_new_scope_after_reset(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $requestScopeStore->open();
        $requestScopeStore->resetState();

        $requestScope = $requestScopeStore->open();

        self::assertTrue($requestScopeStore->hasCurrent());
        self::assertNotSame($requestScopeStore->current()->id()->toString(), $requestScope->id()->toString());
    }

    #[Test]
    public function it_can_open_new_scope_after_close(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $requestScope      = $requestScopeStore->open();
        $requestScopeStore->closeCurrent();

        $secondScope = $requestScopeStore->open();

        self::assertTrue($requestScopeStore->hasCurrent());
        self::assertNotSame($requestScope->id()->toString(), $secondScope->id()->toString());
    }

    #[Test]
    public function it_generates_unique_scope_ids(): void
    {
        $requestScopeStore = new RequestScopeStore();

        $requestScope = $requestScopeStore->open();
        $requestScopeStore->closeCurrent();
        $scope2 = $requestScopeStore->open();

        self::assertNotSame($requestScope->id()->toString(), $scope2->id()->toString());
    }

    #[Test]
    public function it_does_not_leak_value_between_two_scopes(): void
    {
        $requestScopeStore = new RequestScopeStore();

        $requestScopeStore->open();
        $requestScopeStore->current()->write(key: 'secret', value: 'from-first');

        $requestScopeStore->closeCurrent();
        $requestScopeStore->open();

        self::assertNull($requestScopeStore->current()->read(key: 'secret'));
    }
}
