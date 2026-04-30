<?php

declare(strict_types=1);

namespace Avax\Components\Concurrency\System\PublicSurface;

use Avax\Components\Concurrency\System\Capabilities\Tasks\TaskRunner;

final readonly class Concurrency
{
    /**
     * Run multiple tasks concurrently and wait for all to complete.
     *
     * @param list<callable(): mixed> $tasks
     *
     * @return list<mixed>
     */
    public static function all(array $tasks) : array
    {
        $runner = new TaskRunner();

        return $runner->runAll($tasks);
    }

    /**
     * Run multiple tasks concurrently and return the first result.
     *
     * @param list<callable(): mixed> $tasks
     *
     * @return mixed
     */
    public static function race(array $tasks) : mixed
    {
        $runner = new TaskRunner();

        return $runner->race($tasks);
    }

    /**
     * Alias for all() with maxConcurrent parameter.
     *
     * @param list<callable(): mixed> $tasks
     * @param int                     $maxConcurrent
     *
     * @return list<mixed>
     */
    public static function run(array $tasks, int $maxConcurrent = 3) : array
    {
        return self::all($tasks);
    }
}