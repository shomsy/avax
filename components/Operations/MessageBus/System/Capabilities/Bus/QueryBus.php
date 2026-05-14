<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Bus;

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

    /**
 * @throws RuntimeException
 */
public function dispatch(object $query): mixed
    {
        $class = $query::class;

        if (! isset($this->handlers[$class])) {
            throw new RuntimeException('No handler registered for query: '.$class);
        }

        $handler = $this->handlers[$class];

        return $handler($query);
    }
}
