<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks;

use Closure;

final readonly class AfterResponseTask
{
    public function __construct(private Closure $task) {}

    public function execute() : void
    {
        ($this->task)();
    }
}
