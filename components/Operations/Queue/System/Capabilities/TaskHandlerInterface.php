<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

interface TaskHandlerInterface
{
    public function handle(object $task): void;
}
