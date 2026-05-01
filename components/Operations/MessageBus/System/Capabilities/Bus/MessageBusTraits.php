<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Bus;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Closure;
use RuntimeException;

final class CommandBus
{
    private array $handlers = [];

    private array $middleware = [];

    public function register(string $commandClass, object $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    public function use(object $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(Command $command): mixed
    {
        $class = $command::class;

        if (! isset($this->handlers[$class])) {
            throw new RuntimeException("No handler registered for command: {$class}");
        }

        $handler = $this->handlers[$class];
        $pipeline = $this->buildPipeline($command, $handler);

        return $pipeline($command);
    }

    private function buildPipeline(Command $command, object $handler): Closure
    {
        $middlewares = array_reverse($this->middleware);
        $final = fn (Command $cmd) => $this->execute($handler, $cmd);

        foreach ($middlewares as $middleware) {
            $next = $final;
            $final = static fn (Command $cmd) => $middleware->process($cmd, $next);
        }

        return $final;
    }

    private function execute(object $handler, Command $command): mixed
    {
        return $handler($command);
    }
}

final class QueryBus
{
    private array $handlers = [];

    private array $middleware = [];

    public function register(string $queryClass, object $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    public function use(object $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(object $query): mixed
    {
        $class = $query::class;

        if (! isset($this->handlers[$class])) {
            throw new RuntimeException("No handler registered for query: {$class}");
        }

        $handler = $this->handlers[$class];

        return $handler($query);
    }
}

final class EventBus
{
    /** @var array<string, list<object>> */
    private array $handlers = [];

    public function register(string $eventClass, object $handler): void
    {
        if (! isset($this->handlers[$eventClass])) {
            $this->handlers[$eventClass] = [];
        }

        $this->handlers[$eventClass][] = $handler;
    }

    public function dispatch(object $event): void
    {
        $class = $event::class;
        $handlers = $this->handlers[$class] ?? [];

        foreach ($handlers as $handler) {
            $handler($event);
        }
    }

    public function HandlersFor(string $eventClass): array
    {
        return $this->handlers[$eventClass] ?? [];
    }
}
