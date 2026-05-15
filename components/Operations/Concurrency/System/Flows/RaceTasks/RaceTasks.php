<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Flows\RaceTasks;

use Avax\Components\Operations\Concurrency\System\Capabilities\RunWithFibers\FiberTaskRuntime;
use Closure;

final readonly class RaceTasks
{
    public function __construct(
        private FiberTaskRuntime $runtime,
    ) {}

    /**
     * @param list<Closure(): mixed> $tasks
     */
    public function race(array $tasks) : mixed
    {
        if (empty($tasks)) {
            return null;
        }

        return $this->runtime->race($tasks);
    }
}
