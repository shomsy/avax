<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\PublicSurface;

use Avax\Components\Operations\Scheduler\System\Capabilities\Cron\CronExpression;
use Closure;

final readonly class ScheduledTask
{
    public function __construct(
        public string $expression,
        public Closure $task,
    ) {}

    public function isDue() : bool
    {
        return CronExpression::matches($this->expression);
    }
}
