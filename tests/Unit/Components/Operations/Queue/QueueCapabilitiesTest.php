<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\SyncDriver;
use Avax\Components\Operations\Queue\System\Capabilities\TaskBus;
use Avax\Components\Operations\Queue\System\Capabilities\TaskHandlerInterface;
use DateInterval;
use PHPUnit\Framework\TestCase;

final class QueueCapabilitiesTest extends TestCase
{
    public function test_sync_driver_dispatches_invokable_task() : void
    {
        $driver = new SyncDriver();
        $called = false;
        $task   = new class($called) {
            public function __construct(private bool &$called) {}

            public function __invoke() : void { $this->called = true; }
        };

        $driver->dispatch($task);
        $this->assertTrue($called);
    }

    public function test_task_bus_dispatches_to_registered_handler() : void
    {
        $bus    = new TaskBus();
        $called = false;

        $handler = new class($called) implements TaskHandlerInterface {
            public function __construct(private bool &$called) {}

            public function handle(object $task) : void { $this->called = true; }
        };

        $task = new class {};
        $bus->register($task::class, $handler);

        $bus->dispatch($task);
        $this->assertTrue($called);
    }

    public function test_task_bus_falls_back_to_sync_driver() : void
    {
        $bus    = new TaskBus();
        $called = false;
        $task   = new class($called) {
            public function __construct(private bool &$called) {}

            public function __invoke() : void { $this->called = true; }
        };

        $bus->dispatch($task);
        $this->assertTrue($called);
    }

    public function test_task_bus_dispatch_later() : void
    {
        $bus    = new TaskBus();
        $called = false;
        $task   = new class($called) {
            public function __construct(private bool &$called) {}

            public function __invoke() : void { $this->called = true; }
        };

        // 0 interval for testing speed
        $bus->dispatchlater($task, new DateInterval('PT0S'));
        $this->assertTrue($called);
    }
}
