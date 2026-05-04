<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Job;

use Throwable;

abstract class JobHandler
{
    abstract public function handle(array $payload): mixed;

    public function failed(Throwable $throwable, array $payload) : void
    {
    }
}
