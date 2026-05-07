<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Scheduler;

use Avax\Components\Operations\Scheduler\System\PublicSurface\ScheduledTask;
use Avax\Components\Operations\Scheduler\System\PublicSurface\Scheduler;
use PHPUnit\Framework\TestCase;

final class SchedulerCapabilitiesTest extends TestCase
{
    public function test_it_schedules_task_with_cron_expression() : void
    {
        $task = Scheduler::schedule('* * * * *', static function () : void {});
        $this->assertInstanceOf(ScheduledTask::class, $task);
        $this->assertSame('* * * * *', $task->expression);
    }

    public function test_it_returns_all_scheduled_tasks() : void
    {
        Scheduler::schedule('0 * * * *', static function () : void {});
        Scheduler::schedule('0 0 * * *', static function () : void {});
        $this->assertCount(2, Scheduler::scheduled());
    }

    public function test_it_clears_all_scheduled_tasks() : void
    {
        Scheduler::schedule('* * * * *', static function () : void {});
        $this->assertCount(1, Scheduler::scheduled());
        Scheduler::clear();
        $this->assertCount(0, Scheduler::scheduled());
    }

    public function test_it_registers_independent_task() : void
    {
        $task = Scheduler::register('*/5 * * * *', static function () : void {});
        $this->assertInstanceOf(ScheduledTask::class, $task);
        $this->assertSame('*/5 * * * *', $task->expression);
    }

    protected function setUp() : void
    {
        Scheduler::clear();
    }

    protected function tearDown() : void
    {
        Scheduler::clear();
    }
}
