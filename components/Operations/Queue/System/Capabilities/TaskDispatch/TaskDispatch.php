<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\AsyncDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\DeferredDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\SyncDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Resolution\DispatchStrategyResolver;
use Closure;
use DateInterval;

final class TaskDispatch
{
    private static DispatchStrategyResolver $resolver;

    public static function dispatch(object $task): void
    {
        $strategy = self::resolver()->resolve($task);

        if ($strategy === 'sync') {
            self::sync($task);
        } elseif ($strategy === 'async') {
            self::async($task);
        } else {
            self::later($task, new DateInterval('PT0S'));
        }
    }

    private static function resolver(): DispatchStrategyResolver
    {
        if (! isset(self::$resolver)) {
            self::$resolver = new DispatchStrategyResolver;
        }

        return self::$resolver;
    }

    public static function sync(object $task): void
    {
        $dispatcher = new SyncDispatcher;
        $dispatcher->dispatch($task);
    }

    public static function async(object $task): void
    {
        $dispatcher = new AsyncDispatcher;
        $dispatcher->dispatch($task);
    }

    public static function later(object $task, DateInterval $delay): void
    {
        $dispatcher = new DeferredDispatcher($delay);
        $dispatcher->dispatch($task);
    }

    public static function afterResponse(object $task): void
    {
        self::later($task, new DateInterval('PT0S'));
    }

    public static function batch(array $tasks): TaskBatch
    {
        return new TaskBatch($tasks);
    }
}

final class TaskBatch
{
    /** @var list<object> */
    private array $tasks;

    private ?Closure $then = null;

    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    public function then(Closure $callback): self
    {
        $this->then = $callback;

        return $this;
    }

    public function dispatch(): void
    {
        foreach ($this->tasks as $task) {
            TaskDispatch::dispatch($task);
        }

        if ($this->then) {
            ($this->then)();
        }
    }
}
