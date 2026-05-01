<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\Capabilities\Cron;

use Cron\CronExpression as CronLib;
use DateTimeImmutable;

final readonly class CronExpression
{
    public static function matches(string $expression) : bool
    {
        $cron = new CronLib($expression);

        return $cron->isDue();
    }

    public static function nextRun(string $expression) : DateTimeImmutable
    {
        $cron = new CronLib($expression);

        return $cron->getNextRunDate();
    }

    public static function isValid(string $expression) : bool
    {
        return CronLib::isValidExpression($expression);
    }
}