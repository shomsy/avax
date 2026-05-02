<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\Flows\RegisterScheduledTask;

use Avax\Components\Operations\Scheduler\System\PublicSurface\ScheduledTask;
use Avax\Components\Operations\Scheduler\System\PublicSurface\Scheduler;
use Closure;

final readonly class RegisterScheduledTask
{
    public function register(string $expression, Closure $task): ScheduledTask
    {
        return Scheduler::schedule(expression: $expression, task: $task);
    }
}
