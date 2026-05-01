<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Operations\Tasks\System\Capabilities\Queue\Queue;
use Avax\Tests\TestCase;

final class QueueTest extends TestCase
{
    public function test_queue_processes_callable_jobs() : void
    {
        $handled = false;
        Queue::push(job  : static function (array $data = []) use (&$handled) : void {
            $handled = true;
        },          queue: 'integration');

        $processed = Queue::process(queue: 'integration');

        self::assertSame(expected: 1, actual: $processed);
        self::assertTrue(condition: $handled);
    }

    protected function setUp() : void
    {
        parent::setUp();
        Queue::clear(queue: 'integration');
    }
}
