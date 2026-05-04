<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

use DateInterval;

interface TaskDriverInterface
{
    public function dispatch(object $task): void;

    public function dispatchlater(object $task, DateInterval $dateInterval): void;
}
