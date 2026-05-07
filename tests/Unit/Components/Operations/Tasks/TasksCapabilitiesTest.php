<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Tasks;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskBus;
use PHPUnit\Framework\TestCase;

final class TasksCapabilitiesTest extends TestCase
{
    public function test_it_instantiates_task_bus() : void
    {
        $bus = new TaskBus();
        $this->assertInstanceOf(TaskBus::class, $bus);
    }
}
