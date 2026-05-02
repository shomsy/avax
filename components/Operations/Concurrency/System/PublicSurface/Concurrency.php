<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\PublicSurface;

use Avax\Components\Operations\Concurrency\System\Capabilities\Tasks\TaskRunner;

final readonly class Concurrency
{
    /**
     * Run multiple tasks concurrently and return the first result.
     *
     * @param list<callable(): mixed> $tasks
     */
    public static function race(array $tasks): mixed
    {
        $taskRunner = new TaskRunner();

        return $taskRunner->race($tasks);
    }

    /**
     * Alias for all() with maxConcurrent parameter.
     *
     * @param list<callable(): mixed> $tasks
     *
     * @return list<mixed>
     */
    public static function run(array $tasks) : array
    {
        return self::all($tasks);
    }

    /**
     * Run multiple tasks concurrently and wait for all to complete.
     *
     * @param list<callable(): mixed> $tasks
     *
     * @return list<mixed>
     */
    public static function all(array $tasks) : array
    {
        $taskRunner = new TaskRunner();

        return $taskRunner->runAll($tasks);
    }
}
