<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch;

use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\AsyncDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\DeferredDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\SyncDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Resolution\DispatchStrategyResolver;
use Closure;
use DateInterval;

final class TaskDispatch
{
    private static DispatchStrategyResolver $dispatchStrategyResolver;

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
        if (! isset(self::$dispatchStrategyResolver)) {
            self::$dispatchStrategyResolver = new DispatchStrategyResolver();
        }

        return self::$dispatchStrategyResolver;
    }

    public static function sync(object $task): void
    {
        $syncDispatcher = new SyncDispatcher();
        $syncDispatcher->dispatch($task);
    }

    public static function async(object $task): void
    {
        $asyncDispatcher = new AsyncDispatcher();
        $asyncDispatcher->dispatch($task);
    }

    public static function later(object $task, DateInterval $dateInterval) : void
    {
        $deferredDispatcher = new DeferredDispatcher($dateInterval);
        $deferredDispatcher->dispatch($task);
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
    private ?Closure $then = null;

    public function __construct(
        /** @var list<object> */
        private readonly array $tasks
    )
    {
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

        if ($this->then instanceof Closure) {
            ($this->then)();
        }
    }
}
