<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\Queue;
use PHPUnit\Framework\TestCase;

final class QueueResetTest extends TestCase
{
    protected function setUp() : void
    {
        Queue::reset();
    }

    protected function tearDown() : void
    {
        Queue::reset();
    }

    public function testPushAndPop() : void
    {
        $id = Queue::push(fn() => null, queue: 'test_reset');

        self::assertSame(1, Queue::size('test_reset'));

        $job = Queue::pop('test_reset');
        self::assertNotNull($job);
        self::assertSame($id, $job['id']);
    }

    public function testResetClearsAllQueues() : void
    {
        Queue::push(fn() => null, queue: 'reset_test_a');
        Queue::push(fn() => null, queue: 'reset_test_b');

        self::assertSame(1, Queue::size('reset_test_a'));
        self::assertSame(1, Queue::size('reset_test_b'));

        Queue::reset();

        self::assertSame(0, Queue::size('reset_test_a'));
        self::assertSame(0, Queue::size('reset_test_b'));
    }

    public function testResetIsolatesTestState() : void
    {
        // Simulate previous test leaving state
        Queue::push(fn() => null, queue: 'isolation_test');
        self::assertSame(1, Queue::size('isolation_test'));

        // Reset simulates fresh worker cycle
        Queue::reset();

        self::assertSame(0, Queue::size('isolation_test'));
    }

    public function testClearOnlyClearsSpecifiedQueue() : void
    {
        Queue::push(fn() => null, queue: 'keep_this');
        Queue::push(fn() => null, queue: 'clear_this');

        Queue::clear('clear_this');

        self::assertSame(1, Queue::size('keep_this'));
        self::assertSame(0, Queue::size('clear_this'));

        // Cleanup
        Queue::clear('keep_this');
    }

    public function testDeadLetterAfterMaxAttemptsExceeded() : void
    {
        $id = Queue::push(fn() => null, queue: 'dlq_test', maxAttempts: 2);

        // Pop and release once (attempt 1)
        $job = Queue::pop('dlq_test');
        self::assertNotNull($job);
        Queue::release('dlq_test', $job);

        // Job should still be in queue (1 attempt < 2 max)
        self::assertSame(1, Queue::size('dlq_test'));
        self::assertSame(0, Queue::deadLetterCount('dlq_test'));

        // Pop and release again (attempt 2 >= 2 max) -> dead letter
        $job = Queue::pop('dlq_test');
        self::assertNotNull($job);
        Queue::release('dlq_test', $job);

        self::assertSame(0, Queue::size('dlq_test'));
        self::assertSame(1, Queue::deadLetterCount('dlq_test'));

        $deadLetters = Queue::deadLetters('dlq_test');
        self::assertSame('Max attempts exceeded', $deadLetters[0]['reason']);
    }

    public function testClearDeadLetters() : void
    {
        $id = Queue::push(fn() => null, queue: 'clear_dlq_test', maxAttempts: 1);
        $job = Queue::pop('clear_dlq_test');
        Queue::release('clear_dlq_test', $job);

        self::assertSame(1, Queue::deadLetterCount('clear_dlq_test'));

        Queue::clearDeadLetters('clear_dlq_test');
        self::assertSame(0, Queue::deadLetterCount('clear_dlq_test'));
    }

    public function testResetClearsDeadLetters() : void
    {
        $id = Queue::push(fn() => null, queue: 'reset_dlq_test', maxAttempts: 1);
        $job = Queue::pop('reset_dlq_test');
        Queue::release('reset_dlq_test', $job);

        self::assertSame(1, Queue::deadLetterCount('reset_dlq_test'));

        Queue::reset();

        self::assertSame(0, Queue::deadLetterCount('reset_dlq_test'));
    }
}
