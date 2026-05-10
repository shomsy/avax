<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;
use Avax\Components\DataStack\Data\System\Foundation\Mutability\MutationGuard;
use PHPUnit\Framework\TestCase;

final class MutationGuardTest extends TestCase
{
    private MutationGuard $guard;

    public function test_default_state_is_unlocked() : void
    {
        $this->assertFalse($this->guard->isLocked());
    }

    // -- Default State Tests --

    public function test_getLockedState_returns_false_by_default() : void
    {
        $this->assertFalse($this->guard->getLockedState());
    }

    public function test_assertMutable_does_not_throw_when_unlocked() : void
    {
        $this->guard->assertMutable();

        $this->assertFalse($this->guard->isLocked());
    }

    public function test_assertWritable_does_not_throw_when_unlocked() : void
    {
        $this->guard->assertWritable();

        $this->assertFalse($this->guard->isLocked());
    }

    public function test_lock_sets_locked_state() : void
    {
        $this->guard->lock();

        $this->assertTrue($this->guard->isLocked());
        $this->assertTrue($this->guard->getLockedState());
    }

    // -- Lock Tests --

    public function test_locking_already_locked_throws() : void
    {
        $this->guard->lock();

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Value is already locked.');

        $this->guard->lock();
    }

    public function test_assertMutable_throws_when_locked() : void
    {
        $this->guard->lock();

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Value is locked and cannot be modified.');

        $this->guard->assertMutable();
    }

    // -- Mutable Assertion Tests --

    public function test_assertWritable_throws_when_locked() : void
    {
        $this->guard->lock();

        $this->expectException(MutationException::class);
        $this->expectExceptionMessage('Value is locked and cannot be modified.');

        $this->guard->assertWritable();
    }

    public function test_valueIsLocked_exception() : void
    {
        $exception = MutationException::valueIsLocked();

        $this->assertInstanceOf(MutationException::class, $exception);
        $this->assertSame('Value is locked and cannot be modified.', $exception->getMessage());
    }

    // -- MutationException Factory Tests --

    public function test_valueIsAlreadyLocked_exception() : void
    {
        $exception = MutationException::valueIsAlreadyLocked();

        $this->assertInstanceOf(MutationException::class, $exception);
        $this->assertSame('Value is already locked.', $exception->getMessage());
    }

    public function test_collectionIsLocked_exception() : void
    {
        $exception = MutationException::collectionIsLocked();

        $this->assertInstanceOf(MutationException::class, $exception);
        $this->assertSame('Collection is locked and cannot be modified.', $exception->getMessage());
    }

    public function test_collectionIsAlreadyLocked_exception() : void
    {
        $exception = MutationException::collectionIsAlreadyLocked();

        $this->assertInstanceOf(MutationException::class, $exception);
        $this->assertSame('Collection is already locked.', $exception->getMessage());
    }

    public function test_arrayStyleMutationNotSupported_without_hint() : void
    {
        $exception = MutationException::arrayStyleMutationNotSupported();

        $this->assertInstanceOf(MutationException::class, $exception);
        $this->assertSame('Array-style mutation is not supported on immutable collections.', $exception->getMessage());
    }

    public function test_arrayStyleMutationNotSupported_with_hint() : void
    {
        $exception = MutationException::arrayStyleMutationNotSupported(hint: 'Use set() instead.');

        $this->assertInstanceOf(MutationException::class, $exception);
        $this->assertSame(
            'Array-style mutation is not supported on immutable collections. Use set() instead.',
            $exception->getMessage(),
        );
    }

    public function test_multiple_guards_are_independent() : void
    {
        $guard1 = new MutationGuard();
        $guard2 = new MutationGuard();

        $guard1->lock();

        $this->assertTrue($guard1->isLocked());
        $this->assertFalse($guard2->isLocked());
    }

    // -- Instance Isolation Tests --

    public function test_unlocked_guard_can_write() : void
    {
        $guard = new MutationGuard();

        $guard->assertMutable();
        $guard->assertWritable();

        $this->assertFalse($guard->isLocked());
    }

    public function test_lock_after_assertMutable_sequence() : void
    {
        $this->guard->assertMutable();
        $this->guard->assertWritable();
        $this->guard->lock();

        $this->assertTrue($this->guard->isLocked());
    }

    // -- Edge Cases --

    public function test_isLocked_returns_correct_type() : void
    {
        $this->assertIsBool($this->guard->isLocked());

        $this->guard->lock();

        $this->assertIsBool($this->guard->isLocked());
    }

    public function test_getLockedState_returns_correct_type() : void
    {
        $this->assertIsBool($this->guard->getLockedState());

        $this->guard->lock();

        $this->assertIsBool($this->guard->getLockedState());
    }

    protected function setUp() : void
    {
        $this->guard = new MutationGuard();
    }
}
