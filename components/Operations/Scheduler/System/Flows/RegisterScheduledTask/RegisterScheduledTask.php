<?php

declare(strict_types=1);

namespace Avax\Components\Scheduler\System\Flows\RegisterScheduledTask;

use Avax\Components\Scheduler\System\PublicSurface\ScheduledTask;
use Avax\Components\Scheduler\System\PublicSurface\Scheduler;
use Closure;

final readonly class RegisterScheduledTask
{
    public function register(string $expression, Closure $task) : ScheduledTask
    {
        return Scheduler::schedule(expression: $expression, task: $task);
    }
}
