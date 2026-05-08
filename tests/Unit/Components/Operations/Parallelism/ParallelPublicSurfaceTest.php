<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Parallelism;

use Avax\Components\Operations\Parallelism\System\Foundation\Failure\ParallelException;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelFailure;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Avax\Components\Operations\Parallelism\System\PublicSurface\Parallel;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ParallelPublicSurfaceTest extends TestCase
{
    public function test_run_returns_successful_results() : void
    {
        $result = Parallel::run([
                                    'a' => static fn () => 1,
                                    'b' => static fn () => 2,
                                    'c' => static fn () => 3,
                                ]);

        $this->assertInstanceOf(ParallelResult::class, $result);
        $this->assertSame(1, $result->value('a'));
        $this->assertSame(2, $result->value('b'));
        $this->assertSame(3, $result->value('c'));
        $this->assertTrue($result->successful());
        $this->assertFalse($result->hasFailures());
    }

    public function test_run_preserves_task_keys() : void
    {
        $result = Parallel::run([
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
        $result = Parallel::run([
                                    'success' => static fn () => 'ok',
                                    'failing' => static fn () => throw new RuntimeException('Task failed'),
                                ]);

        $this->assertFalse($result->successful());
        $this->assertTrue($result->hasFailures());
        $this->assertSame('ok', $result->value('success'));
        $this->assertNull($result->value('failing'));

        $failures = $result->failures();
        $this->assertCount(1, $failures);
        $this->assertInstanceOf(ParallelFailure::class, $failures[0]);
        $this->assertSame('failing', $failures[0]->getName());
        $this->assertSame('Task failed', $failures[0]->getMessage());
        $this->assertSame(0, $failures[0]->getCode());
    }

    public function test_run_captures_multiple_failures() : void
    {
        $result = Parallel::run([
                                    'first'  => static fn () => throw new RuntimeException('First error'),
                                    'second' => static fn () => throw new InvalidArgumentException('Second error'),
                                    'third'  => static fn () => 'ok',
                                ]);

        $this->assertFalse($result->successful());
        $this->assertSame(2, $result->failedWorkers);
        $this->assertSame(3, $result->startedWorkers);
        $this->assertCount(2, $result->failures());
    }

    public function test_run_with_empty_work_returns_empty_result() : void
    {
        $result = Parallel::run([]);

        $this->assertSame([], $result->values());
        $this->assertSame([], $result->failures());
        $this->assertSame(0, $result->startedWorkers);
        $this->assertSame(0, $result->finishedWorkers);
        $this->assertSame(0, $result->failedWorkers);
        $this->assertTrue($result->successful());
    }

    public function test_run_respects_max_workers() : void
    {
        $executed = [];
        $result   = Parallel::run([
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
                                  ], maxWorkers: 2);

        $this->assertTrue($result->successful());
        $this->assertCount(3, $executed);
    }

    public function test_run_throw_if_failed_throws_exception() : void
    {
        $result = Parallel::run([
                                    'failing' => static fn () => throw new RuntimeException('Boom'),
                                ]);

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('Boom');

        $result->throwIfFailed();
    }

    public function test_run_throw_if_failed_single_error_uses_message() : void
    {
        $result = Parallel::run([
                                    'failing' => static fn () => throw new RuntimeException('Specific error message'),
                                ]);

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('Specific error message');

        $result->throwIfFailed();
    }

    public function test_run_throw_if_failed_no_exception_when_successful() : void
    {
        $result = Parallel::run([
                                    'ok' => static fn () => 'success',
                                ]);

        $result->throwIfFailed();
        $this->assertTrue(true);
    }

    public function test_map_returns_ordered_results() : void
    {
        $items  = ['a', 'b', 'c'];
        $result = Parallel::map($items, static fn ($item) => strtoupper($item));

        $this->assertTrue($result->successful());
        $this->assertSame('A', $result->value('item_0'));
        $this->assertSame('B', $result->value('item_1'));
        $this->assertSame('C', $result->value('item_2'));
    }

    public function test_map_preserves_order_regardless_of_execution() : void
    {
        $items  = [1, 2, 3, 4, 5];
        $result = Parallel::map($items, static fn ($item) => $item * 2);

        $values = $result->values();
        $this->assertSame(2, $values['item_0']);
        $this->assertSame(4, $values['item_1']);
        $this->assertSame(6, $values['item_2']);
        $this->assertSame(8, $values['item_3']);
        $this->assertSame(10, $values['item_4']);
    }

    public function test_map_captures_failures() : void
    {
        $items  = ['good', 'bad', 'also_bad'];
        $result = Parallel::map($items, static function ($item) {
            if ($item === 'bad' || $item === 'also_bad') {
                throw new RuntimeException('Bad item: ' . $item);
            }

            return $item;
        });

        $this->assertFalse($result->successful());
        $this->assertSame('good', $result->value('item_0'));
        $this->assertNull($result->value('item_1'));
        $this->assertNull($result->value('item_2'));
        $this->assertSame(2, $result->failedWorkers);
    }

    public function test_map_with_empty_items() : void
    {
        $result = Parallel::map([], static fn ($item) => $item);

        $this->assertTrue($result->successful());
        $this->assertSame([], $result->values());
    }

    public function test_run_with_integer_keys() : void
    {
        $result = Parallel::run([
                                    0 => static fn () => 'zero',
                                    1 => static fn () => 'one',
                                    2 => static fn () => 'two',
                                ]);

        $this->assertSame('zero', $result->value(0));
        $this->assertSame('one', $result->value(1));
        $this->assertSame('two', $result->value(2));
    }

    public function test_run_task_can_return_null() : void
    {
        $result = Parallel::run([
                                    'nullable' => static fn () : null => null,
                                ]);

        $this->assertTrue($result->successful());
        $this->assertNull($result->value('nullable'));
    }

    public function test_run_task_can_return_array() : void
    {
        $result = Parallel::run([
                                    'array_result' => static fn () : array => ['key' => 'value'],
                                ]);

        $this->assertTrue($result->successful());
        $this->assertSame(['key' => 'value'], $result->value('array_result'));
    }

    public function test_run_task_can_throw_with_custom_code() : void
    {
        $result = Parallel::run([
                                    'custom_code' => static fn () => throw new RuntimeException('Error', 42),
                                ]);

        $failure = $result->failures()[0];
        $this->assertSame(42, $failure->getCode());
    }

    public function test_run_failure_preserves_exception_chain() : void
    {
        $previous = new LogicException('Previous error', 99);
        $result   = Parallel::run([
                                      'chained' => static fn () => throw new RuntimeException('New error', 1, $previous),
                                  ]);

        $failure = $result->failures()[0];
        $this->assertSame('New error', $failure->getMessage());
        $this->assertSame(1, $failure->getCode());
        $this->assertNotNull($failure->getPrevious());
        $this->assertSame('Previous error', $failure->getPrevious()->getMessage());
        $this->assertSame(99, $failure->getPrevious()->getCode());
    }

    public function test_run_handles_fatal_errors() : void
    {
        $result = Parallel::run([
                                    'notice' => static fn () : mixed => trigger_error('Notice test', E_USER_NOTICE),
                                ]);

        $this->assertTrue($result->successful());
    }

    public function test_run_all_tasks_fail() : void
    {
        $result = Parallel::run([
                                    'a' => static fn () => throw new RuntimeException('A'),
                                    'b' => static fn () => throw new RuntimeException('B'),
                                    'c' => static fn () => throw new RuntimeException('C'),
                                ]);

        $this->assertFalse($result->successful());
        $this->assertSame(3, $result->failedWorkers);
        $this->assertSame(3, $result->startedWorkers);
        $this->assertSame(3, $result->finishedWorkers);
        $this->assertCount(3, $result->failures());
    }

    public function test_run_throw_if_failed_multiple_shows_count() : void
    {
        $result = Parallel::run([
                                    'a' => static fn () => throw new RuntimeException('A'),
                                    'b' => static fn () => throw new RuntimeException('B'),
                                ]);

        $this->expectException(ParallelException::class);
        $this->expectExceptionMessage('2 workers failed');

        $result->throwIfFailed();
    }

    public function test_result_value_returns_null_for_nonexistent_key() : void
    {
        $result = Parallel::run([
                                    'existing' => static fn () => 'value',
                                ]);

        $this->assertNull($result->value('nonexistent'));
        $this->assertNull($result->value(999));
    }

    public function test_map_with_generator() : void
    {
        $gen = static function () : iterable {
            yield 'a';
            yield 'b';
            yield 'c';
        };

        $result = Parallel::map($gen(), static fn ($item) => strtoupper($item));

        $this->assertTrue($result->successful());
        $this->assertSame('A', $result->value('item_0'));
        $this->assertSame('B', $result->value('item_1'));
        $this->assertSame('C', $result->value('item_2'));
    }

    public function test_run_with_boolean_return_values() : void
    {
        $result = Parallel::run([
                                    'true'  => static fn () : bool => true,
                                    'false' => static fn () : bool => false,
                                ]);

        $this->assertTrue($result->successful());
        $this->assertTrue($result->value('true'));
        $this->assertFalse($result->value('false'));
    }

    public function test_run_with_numeric_return_values() : void
    {
        $result = Parallel::run([
                                    'int'   => static fn () : int => 42,
                                    'float' => static fn () : float => 3.14,
                                ]);

        $this->assertTrue($result->successful());
        $this->assertSame(42, $result->value('int'));
        $this->assertSame(3.14, $result->value('float'));
    }
}
