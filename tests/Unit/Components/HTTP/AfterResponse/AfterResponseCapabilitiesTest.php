<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\AfterResponse;

use Avax\Components\HTTP\AfterResponse\System\System\Capabilities\Tasks\AfterResponseQueue;
use Avax\Components\HTTP\AfterResponse\System\System\Capabilities\Tasks\AfterResponseTask;
use PHPUnit\Framework\TestCase;

final class AfterResponseCapabilitiesTest extends TestCase
{
    public function test_after_response_queue_executes_tasks() : void
    {
        $queue    = new AfterResponseQueue();
        $executed = false;

        $task = new AfterResponseTask(
            task: function () use (&$executed) {
                $executed = true;
            }
        );

        $queue->enqueue($task);
        $queue->execute();

        $this->assertTrue($executed);
        $this->assertTrue($queue->isEmpty());
    }
}
