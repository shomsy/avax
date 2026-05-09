<?php

declare(strict_types=1);

namespace Avax;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;
use Avax\Components\Operations\Concurrency\System\PublicSurface\Concurrency;
use Closure;

/**
 * Lightweight async/await DX shortcuts for AvaX Concurrency.
 *
 * These are thin wrappers over the Concurrency public surface.
 * They do not add runtime magic, fake async I/O, or change Fiber behavior.
 *
 * Cooperative concurrency: tasks must explicitly yield/suspend at safe points
 * (Fiber::suspend) for interleaving to occur. CPU-bound or blocking work
 * belongs in Parallelism, not Concurrency.
 *
 * Usage:
 *   $task = async(fn () => $users->find($id));
 *   $result = await($task);
 *
 *   $user = await(fn () => $users->find($id));  // shortcut: start + await
 */

if (! function_exists('Avax\async')) {
    /**
     * Start a concurrent task without waiting for its result.
     *
     * Use async() when you want to launch multiple tasks first,
     * then await their results later for true concurrency.
     *
     * @throws \Throwable If the task fails to start.
     */
    function async(Closure $task) : ConcurrentTask
    {
        return Concurrency::start(task: $task);
    }
}

if (! function_exists('Avax\await')) {
    /**
     * Wait for a concurrent task to complete and return its result.
     *
     * Accepts either a ConcurrentTask (from async()) or a Closure
     * (starts and awaits immediately — convenience, not concurrency).
     *
     * @param  ConcurrentTask|Closure  $task
     * @return mixed
     *
     * @throws \Throwable If the task throws an exception.
     */
    function await(ConcurrentTask|Closure $task) : mixed
    {
        if ($task instanceof Closure) {
            return Concurrency::await(task: Concurrency::start(task: $task));
        }

        return Concurrency::await(task: $task);
    }
}
