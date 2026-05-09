<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Concurrency;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;
use Avax\Components\Operations\Concurrency\System\PublicSurface\Concurrency;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Avax\async;
use function Avax\await;

/**
 * Proof tests for async/await DX shortcut functions.
 *
 * These tests prove that:
 * - async() returns a ConcurrentTask
 * - await(async(fn() => 'ok')) returns 'ok'
 * - await(fn() => 'ok') works as a shortcut
 * - Exception inside async task is thrown on await()
 * - Multiple async tasks can be started before await()
 * - Shortcuts delegate to Concurrency public surface
 */
final class AsyncAwaitShortcutsTest extends TestCase
{
    // ============================================================
    // 1. ASYNC RETURNS CONCURRENT TASK
    // ============================================================

    public function test_async_returns_concurrent_task() : void
    {
        $task = async(fn() => 'hello');

        $this->assertInstanceOf(ConcurrentTask::class, $task);
        $this->assertFalse($task->isStarted());
    }

    // ============================================================
    // 2. AWAIT WITH ASYNC TASK
    // ============================================================

    public function test_await_with_async_task_returns_result() : void
    {
        $task = async(fn() => 'hello');
        $result = await($task);

        $this->assertSame('hello', $result);
        $this->assertTrue($task->isFinished());
    }

    // ============================================================
    // 3. AWAIT WITH CLOSURE SHORTCUT
    // ============================================================

    public function test_await_with_closure_starts_and_awaits() : void
    {
        $result = await(fn() => 'direct');

        $this->assertSame('direct', $result);
    }

    // ============================================================
    // 4. EXCEPTION INSIDE ASYNC TASK
    // ============================================================

    public function test_exception_inside_async_thrown_on_await() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('async error');

        $shouldFail = true;
        $task = async(static fn(): string => $shouldFail ? throw new RuntimeException('async error') : 'ok');
        await($task);
    }

    // ============================================================
    // 5. MULTIPLE ASYNC TASKS BEFORE AWAIT
    // ============================================================

    public function test_multiple_async_tasks_started_before_await() : void
    {
        $a = async(fn() => 'first');
        $b = async(fn() => 'second');
        $c = async(fn() => 'third');

        // All tasks are created but not yet executed (cooperative concurrency)
        $resultA = await($a);
        $resultB = await($b);
        $resultC = await($c);

        $this->assertSame('first', $resultA);
        $this->assertSame('second', $resultB);
        $this->assertSame('third', $resultC);
    }

    // ============================================================
    // 6. DELEGATION TO CONCURRENCY PUBLIC SURFACE
    // ============================================================

    public function test_async_delegates_to_concurrency_start() : void
    {
        $shortcutTask = async(fn() => 'test');
        $directTask = Concurrency::start(fn() => 'test');

        $this->assertEquals(
            $shortcutTask->getResult(),
            $directTask->getResult(),
            'async() and Concurrency::start() produce equivalent tasks',
        );
    }

    public function test_await_delegates_to_concurrency_await() : void
    {
        $shortcutResult = await(fn() => 'delegated');
        $directResult = Concurrency::await(Concurrency::start(fn() => 'delegated'));

        $this->assertSame($directResult, $shortcutResult);
    }

    // ============================================================
    // 7. ASYNC WITH CAPTURED VARIABLES
    // ============================================================

    public function test_async_preserves_captured_variables() : void
    {
        $name = 'AvaX';
        $task = async(fn() => "Hello, {$name}!");

        $this->assertSame('Hello, AvaX!', await($task));
    }
}
