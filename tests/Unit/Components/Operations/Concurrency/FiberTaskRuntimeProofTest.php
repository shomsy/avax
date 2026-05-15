<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Concurrency;

use Avax\Components\Operations\Concurrency\System\Capabilities\RunWithFibers\FiberTaskRuntime;
use Avax\Components\Operations\Concurrency\System\Configuration\Builders\BuildConcurrencyRuntime;
use Avax\Components\Operations\Concurrency\System\Configuration\ConcurrencyConfig;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentFailure;
use Avax\Components\Operations\Concurrency\System\Foundation\Failure\ConcurrencyException;
use Fiber;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Proof tests for FiberTaskRuntime cooperative concurrency.
 *
 * These tests prove that:
 * - Real fiber interleaving occurs when tasks suspend
 * - maxConcurrent limits active fibers
 * - race() returns the first non-null completing task
 * - Failures are captured correctly
 * - Cooperative concurrency limitation is documented
 */
final class FiberTaskRuntimeProofTest extends TestCase
{
    private FiberTaskRuntime $runtime;

    protected function setUp() : void
    {
        $this->runtime = new FiberTaskRuntime();
    }

    // ============================================================
    // 1. REAL FIBER INTERLEAVING
    // ============================================================

    public function test_fiber_interleaving_proves_round_robin() : void
    {
        $trace = [];

        $result = $this->runtime->run([
            'A' => static function () use (&$trace) : string {
                $trace[] = 'A:start';
                Fiber::suspend();
                $trace[] = 'A:middle';
                Fiber::suspend();
                $trace[] = 'A:end';

                return 'A_done';
            },
            'B' => static function () use (&$trace) : string {
                $trace[] = 'B:start';
                Fiber::suspend();
                $trace[] = 'B:middle';
                Fiber::suspend();
                $trace[] = 'B:end';

                return 'B_done';
            },
        ]);

        $this->assertSame('A_done', $result->value('A'));
        $this->assertSame('B_done', $result->value('B'));
        $this->assertTrue($result->successful());

        $expected = [
            'A:start',
            'B:start',
            'A:middle',
            'B:middle',
            'A:end',
            'B:end',
        ];

        $this->assertSame($expected, $trace,
            'Trace proves round-robin interleaving, not sequential execution');
    }

    public function test_three_way_interleaving() : void
    {
        $trace = [];

        $result = $this->runtime->run([
            'A' => static function () use (&$trace) : string {
                $trace[] = 'A:1';
                Fiber::suspend();
                $trace[] = 'A:2';
                Fiber::suspend();
                $trace[] = 'A:3';

                return 'A';
            },
            'B' => static function () use (&$trace) : string {
                $trace[] = 'B:1';
                Fiber::suspend();
                $trace[] = 'B:2';
                Fiber::suspend();
                $trace[] = 'B:3';

                return 'B';
            },
            'C' => static function () use (&$trace) : string {
                $trace[] = 'C:1';
                Fiber::suspend();
                $trace[] = 'C:2';
                Fiber::suspend();
                $trace[] = 'C:3';

                return 'C';
            },
        ]);

        $this->assertTrue($result->successful());

        $expected = [
            'A:1', 'B:1', 'C:1',
            'A:2', 'B:2', 'C:2',
            'A:3', 'B:3', 'C:3',
        ];

        $this->assertSame($expected, $trace,
            'Three tasks interleave in round-robin order');
    }

    public function test_non_suspending_tasks_complete_still_work() : void
    {
        $trace = [];

        $result = $this->runtime->run([
            'A' => static function () use (&$trace) : string {
                $trace[] = 'A';

                return 'A_done';
            },
            'B' => static function () use (&$trace) : string {
                $trace[] = 'B';

                return 'B_done';
            },
        ]);

        $this->assertTrue($result->successful());
        $this->assertSame('A_done', $result->value('A'));
        $this->assertSame('B_done', $result->value('B'));

        // Non-suspending tasks run sequentially within their fiber
        // This is expected and documented behavior
        $this->assertSame(['A', 'B'], $trace,
            'Non-suspending tasks complete sequentially per fiber, which is expected');
    }

    // ============================================================
    // 2. MAXCONCURRENT PROOF
    // ============================================================

    public function test_maxConcurrent_limits_active_fibers() : void
    {
        $activeCount   = 0;
        $maxObserved   = 0;
        $trace         = [];
        $maxConcurrent = 2;

        $tasks = [];
        for ($i = 0; $i < 5; $i++) {
            $name = "task_$i";
            $tasks[$name] = static function () use (&$trace, &$activeCount, &$maxObserved, $name, $i) : string {
                $activeCount++;
                $maxObserved = max($maxObserved, $activeCount);
                $trace[] = "$name:start(active=$activeCount)";
                Fiber::suspend();
                $trace[] = "$name:middle";
                Fiber::suspend();
                $trace[] = "$name:end";
                $activeCount--;

                return "done_$i";
            };
        }

        $result = $this->runtime->run($tasks, $maxConcurrent);

        $this->assertTrue($result->successful());
        $this->assertLessThanOrEqual($maxConcurrent, $maxObserved,
            "Max observed active count ($maxObserved) exceeded maxConcurrent ($maxConcurrent)");
    }

    public function test_maxConcurrent_chunks_large_task_sets() : void
    {
        $taskCount     = 10;
        $maxConcurrent = 3;

        $tasks = [];
        for ($i = 0; $i < $taskCount; $i++) {
            $tasks["task_$i"] = static fn () => $i * 2;
        }

        $result = $this->runtime->run($tasks, $maxConcurrent);

        $this->assertTrue($result->successful());
        $this->assertSame($taskCount, $result->finishedTasks);
        $this->assertSame(0, $result->failedTasks);

        for ($i = 0; $i < $taskCount; $i++) {
            $this->assertSame($i * 2, $result->value("task_$i"));
        }
    }

    // ============================================================
    // 3. ALL() RESULT ORDER
    // ============================================================

    public function test_result_keys_are_preserved_regardless_of_completion_order() : void
    {
        $result = $this->runtime->run([
            'first'  => static fn () => 'result_1',
            'second' => static fn () => 'result_2',
            'third'  => static fn () => 'result_3',
        ]);

        $this->assertTrue($result->successful());
        $this->assertSame('result_1', $result->value('first'));
        $this->assertSame('result_2', $result->value('second'));
        $this->assertSame('result_3', $result->value('third'));

        $values = $result->values();
        $this->assertArrayHasKey('first', $values);
        $this->assertArrayHasKey('second', $values);
        $this->assertArrayHasKey('third', $values);
    }

    // ============================================================
    // 4. FAILURE HANDLING
    // ============================================================

    public function test_single_failure_is_captured_not_swallowed() : void
    {
        $result = $this->runtime->run([
            'good'   => static fn () => 'ok',
            'bad'    => static fn () => throw new RuntimeException('Task failed intentionally'),
            'also_good' => static fn () => 'also_ok',
        ]);

        $this->assertFalse($result->successful());
        $this->assertTrue($result->hasFailures());
        $this->assertSame(1, $result->failedTasks);
        $this->assertSame(2, $result->finishedTasks - $result->failedTasks);
        $this->assertSame('ok', $result->value('good'));
        $this->assertSame('also_ok', $result->value('also_good'));
        $this->assertNull($result->value('bad'));

        $failures = $result->failures();
        $this->assertCount(1, $failures);
        $this->assertInstanceOf(ConcurrentFailure::class, $failures[0]);
        $this->assertSame('bad', $failures[0]->getName());
        $this->assertSame('Task failed intentionally', $failures[0]->getMessage());
    }

    public function test_throwIfFailed_throws_with_clear_message() : void
    {
        $result = $this->runtime->run([
            'failing' => static fn () => throw new RuntimeException('Clear error message'),
        ]);

        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('Clear error message');

        $result->throwIfFailed();
    }

    public function test_all_tasks_fail() : void
    {
        $result = $this->runtime->run([
            'a' => static fn () => throw new RuntimeException('A fails'),
            'b' => static fn () => throw new RuntimeException('B fails'),
            'c' => static fn () => throw new RuntimeException('C fails'),
        ]);

        $this->assertFalse($result->successful());
        $this->assertSame(3, $result->failedTasks);
        $this->assertSame(0, count($result->values()));

        $failures = $result->failures();
        $this->assertCount(3, $failures);
    }

    // ============================================================
    // 5. RACE() IS REAL
    // ============================================================

    public function test_race_returns_fast_task_not_first_in_array() : void
    {
        // Slow task is first in array, fast task is second.
        // race() should return the fast task result.
        $result = $this->runtime->race([
            static function () : mixed {
                Fiber::suspend();
                Fiber::suspend();

                return 'slow';
            },
            static function () : string {
                return 'fast';
            },
        ]);

        $this->assertSame('fast', $result,
            'race() returns the fast task result, not the first array element');
    }

    public function test_race_returns_first_non_null_result() : void
    {
        $result = $this->runtime->race([
            static fn () => null,
            static fn () => 'found',
            static fn () => 'ignored',
        ]);

        $this->assertSame('found', $result);
    }

    public function test_race_returns_null_when_all_return_null() : void
    {
        $result = $this->runtime->race([
            static fn () => null,
            static fn () => null,
        ]);

        $this->assertNull($result);
    }

    public function test_race_with_empty_tasks_returns_null() : void
    {
        $result = $this->runtime->race([]);

        $this->assertNull($result);
    }

    public function test_race_with_suspending_tasks() : void
    {
        $trace = [];

        $result = $this->runtime->race([
            static function () use (&$trace) : mixed {
                $trace[] = 'slow:start';
                Fiber::suspend();
                $trace[] = 'slow:resume';
                Fiber::suspend();
                $trace[] = 'slow:end';

                return 'slow_result';
            },
            static function () use (&$trace) : string {
                $trace[] = 'fast:complete';

                return 'fast_result';
            },
        ]);

        $this->assertSame('fast_result', $result);
        // The slow task was started but race stopped waiting
        $this->assertContains('slow:start', $trace);
    }

    // ============================================================
    // 6. RACE() CANCELLATION BEHAVIOR
    // ============================================================

    public function test_race_does_not_cancel_remaining_tasks() : void
    {
        // race() stops waiting for results but does NOT cancel running fibers.
        // Remaining fibers continue to exist but are abandoned when the method returns.
        $executed = [];

        $result = $this->runtime->race([
            static function () use (&$executed) : string {
                $executed[] = 'fast';

                return 'fast';
            },
            static function () use (&$executed) : mixed {
                $executed[] = 'slow:start';
                Fiber::suspend();
                $executed[] = 'slow:completed';

                return 'slow';
            },
        ]);

        $this->assertSame('fast', $result);
        $this->assertContains('fast', $executed);
        // Slow task was started but not completed (race returned before it finished)
        $this->assertContains('slow:start', $executed);
        // Note: remaining fibers are abandoned, not cancelled.
        // This is documented cooperative concurrency behavior.
    }

    // ============================================================
    // 7. FIBER AVAILABILITY FALLBACK
    // ============================================================

    public function test_buildConcurrencyRuntime_selects_fiber_when_available() : void
    {
        $builder = new BuildConcurrencyRuntime();
        $runtimes = $builder->detectAvailableRuntimes();

        $this->assertArrayHasKey('current_process', $runtimes);

        if (class_exists(Fiber::class)) {
            $this->assertArrayHasKey('fiber', $runtimes);
            $this->assertSame('fiber', $builder->getDefaultRuntime());
        } else {
            $this->assertSame('current_process', $builder->getDefaultRuntime());
        }
    }

    public function test_buildConcurrencyRuntime_builds_fiber_runtime() : void
    {
        $builder = new BuildConcurrencyRuntime();

        if (class_exists(Fiber::class)) {
            $config  = new ConcurrencyConfig(runtime: 'fiber');
            $runtime = $builder->build($config);

            $this->assertInstanceOf(
                FiberTaskRuntime::class,
                $runtime,
                'BuildConcurrencyRuntime builds FiberTaskRuntime when fiber runtime is configured',
            );
        }
    }

    public function test_buildConcurrencyRuntime_fallback_to_current_process() : void
    {
        $builder = new BuildConcurrencyRuntime();
        $config  = new ConcurrencyConfig(runtime: 'current_process');
        $runtime = $builder->build($config);

        $this->assertNotInstanceOf(
            FiberTaskRuntime::class,
            $runtime,
            'BuildConcurrencyRuntime falls back to non-fiber runtime when current_process is configured',
        );
    }
}
