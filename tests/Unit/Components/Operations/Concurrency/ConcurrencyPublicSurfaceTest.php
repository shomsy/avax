<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Concurrency;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentFailure;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;
use Avax\Components\Operations\Concurrency\System\Foundation\Failure\ConcurrencyException;
use Avax\Components\Operations\Concurrency\System\PublicSurface\Concurrency;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConcurrencyPublicSurfaceTest extends TestCase
{
    public function test_run_returns_successful_results() : void
    {
        $result = Concurrency::run([
                                       'a' => static fn () => 1,
                                       'b' => static fn () => 2,
                                       'c' => static fn () => 3,
                                   ]);

        $this->assertInstanceOf(ConcurrentResult::class, $result);
        $this->assertSame(1, $result->value('a'));
        $this->assertSame(2, $result->value('b'));
        $this->assertSame(3, $result->value('c'));
        $this->assertTrue($result->successful());
        $this->assertFalse($result->hasFailures());
    }

    public function test_run_preserves_task_keys() : void
    {
        $result = Concurrency::run([
                                       'first'  => static fn () => 'hello',
                                       'second' => static fn () => 'world',
                                   ]);

        $values = $result->values();
        $this->assertArrayHasKey('first', $values);
        $this->assertArrayHasKey('second', $values);
        $this->assertSame('hello', $values['first']);
        $this->assertSame('world', $values['second']);
    }

    public function test_run_captures_exceptions() : void
    {
        $result = Concurrency::run([
                                       'success' => static fn () => 'ok',
                                       'failing' => static fn () => throw new RuntimeException('Task failed'),
                                   ]);

        $this->assertFalse($result->successful());
        $this->assertTrue($result->hasFailures());
        $this->assertSame('ok', $result->value('success'));
        $this->assertNull($result->value('failing'));

        $failures = $result->failures();
        $this->assertCount(1, $failures);
        $this->assertInstanceOf(ConcurrentFailure::class, $failures[0]);
        $this->assertSame('failing', $failures[0]->getName());
        $this->assertSame('Task failed', $failures[0]->getMessage());
    }

    public function test_run_captures_multiple_failures() : void
    {
        $result = Concurrency::run([
                                       'first'  => static fn () => throw new RuntimeException('First error'),
                                       'second' => static fn () => throw new InvalidArgumentException('Second error'),
                                       'third'  => static fn () => 'ok',
                                   ]);

        $this->assertFalse($result->successful());
        $this->assertSame(2, $result->failedTasks);
        $this->assertSame(3, $result->startedTasks);
        $this->assertCount(2, $result->failures());
    }

    public function test_run_with_empty_tasks_returns_empty_result() : void
    {
        $result = Concurrency::run([]);

        $this->assertSame([], $result->values());
        $this->assertSame([], $result->failures());
        $this->assertSame(0, $result->startedTasks);
        $this->assertSame(0, $result->finishedTasks);
        $this->assertSame(0, $result->failedTasks);
        $this->assertTrue($result->successful());
    }

    public function test_run_respects_max_concurrent() : void
    {
        $executed = [];
        $result   = Concurrency::run([
                                         'a' => static function () use (&$executed) {
                                             $executed[] = 'a';

                                             return 'a';
                                         },
                                         'b' => static function () use (&$executed) {
                                             $executed[] = 'b';

                                             return 'b';
                                         },
                                         'c' => static function () use (&$executed) {
                                             $executed[] = 'c';

                                             return 'c';
                                         },
                                     ], maxConcurrent: 2);

        $this->assertTrue($result->successful());
        $this->assertCount(3, $executed);
    }

    public function test_run_throw_if_failed_throws_exception() : void
    {
        $result = Concurrency::run([
                                       'failing' => static fn () => throw new RuntimeException('Boom'),
                                   ]);

        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('Boom');

        $result->throwIfFailed();
    }

    public function test_run_throw_if_failed_no_exception_when_successful() : void
    {
        $result = Concurrency::run([
                                       'ok' => static fn () => 'success',
                                   ]);

        $result->throwIfFailed();
        $this->assertTrue($result->successful());
    }

    public function test_all_is_alias_for_run() : void
    {
        $result = Concurrency::all([
                                       'a' => static fn () => 1,
                                       'b' => static fn () => 2,
                                   ]);

        $this->assertTrue($result->successful());
        $this->assertSame(1, $result->value('a'));
        $this->assertSame(2, $result->value('b'));
    }

    public function test_race_returns_first_successful_result() : void
    {
        $result = Concurrency::race([
                                        static fn () => null,
                                        static fn () => 'found',
                                        static fn () => 'not this',
                                    ]);

        $this->assertSame('found', $result);
    }

    public function test_race_returns_null_when_all_fail() : void
    {
        $result = Concurrency::race([
                                        static fn () => null,
                                        static fn () => null,
                                    ]);

        $this->assertNull($result);
    }

    public function test_race_with_empty_tasks() : void
    {
        $result = Concurrency::race([]);

        $this->assertNull($result);
    }

    public function test_start_creates_task() : void
    {
        $task = Concurrency::start(static fn () => 'delayed');

        $this->assertInstanceOf(ConcurrentTask::class, $task);
        $this->assertFalse($task->isStarted());
        $this->assertFalse($task->isFinished());
    }

    public function test_await_completes_task() : void
    {
        $task = Concurrency::start(static fn () => 'result');

        $result = Concurrency::await($task);

        $this->assertSame('result', $result);
        $this->assertTrue($task->isStarted());
        $this->assertTrue($task->isFinished());
    }

    public function test_await_throws_on_failure() : void
    {
        $task = Concurrency::start(static fn () => throw new RuntimeException('Failed'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed');

        Concurrency::await($task);
    }

    public function test_run_with_integer_keys() : void
    {
        $result = Concurrency::run([
                                       0 => static fn () => 'zero',
                                       1 => static fn () => 'one',
                                   ]);

        $this->assertSame('zero', $result->value(0));
        $this->assertSame('one', $result->value(1));
    }

    public function test_run_failure_preserves_exception_chain() : void
    {
        $previous = new LogicException('Previous error', 99);
        $result   = Concurrency::run([
                                         'chained' => static fn () => throw new RuntimeException('New error', 1, $previous),
                                     ]);

        $failure = $result->failures()[0];
        $this->assertSame('New error', $failure->getMessage());
        $this->assertNotNull($failure->getPrevious());
        $this->assertSame('Previous error', $failure->getPrevious()->getMessage());
    }

    public function test_run_throw_if_failed_multiple_shows_count() : void
    {
        $result = Concurrency::run([
                                       'a' => static fn () => throw new RuntimeException('A'),
                                       'b' => static fn () => throw new RuntimeException('B'),
                                   ]);

        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('2 tasks failed');

        $result->throwIfFailed();
    }

    public function test_result_value_returns_null_for_nonexistent_key() : void
    {
        $result = Concurrency::run([
                                       'existing' => static fn () => 'value',
                                   ]);

        $this->assertNull($result->value('nonexistent'));
    }

    public function test_run_all_tasks_fail() : void
    {
        $result = Concurrency::run([
                                       'a' => static fn () => throw new RuntimeException('A'),
                                       'b' => static fn () => throw new RuntimeException('B'),
                                   ]);

        $this->assertFalse($result->successful());
        $this->assertSame(2, $result->failedTasks);
    }
}
