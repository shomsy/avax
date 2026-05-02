<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\EventLoopAdapters;

final readonly class SynchronousEventLoop
{
    /**
     * @param list<callable(): mixed> $tasks
     * @return list<mixed>
     */
    public function run(array $tasks): array
    {
        $results = [];

        foreach ($tasks as $task) {
            $results[] = $task();
        }

        return $results;
    }
}
