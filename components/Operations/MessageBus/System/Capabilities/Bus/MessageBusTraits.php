<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Bus;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Closure;
use RuntimeException;

final class MessageBusTraits
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

    /**
 * @throws RuntimeException
 */
public function dispatch(Command $command): mixed
    {
        $class = $command::class;

        if (! isset($this->handlers[$class])) {
            throw new RuntimeException('No handler registered for command: '.$class);
        }

        $handler = $this->handlers[$class];
        $pipeline = $this->buildPipeline($handler);

        return $pipeline($command);
    }

    private function buildPipeline(object $handler): Closure
    {
        $middlewares = array_reverse($this->middleware);
        $final = fn (Command $command): mixed => $this->execute($handler, $command);

        foreach ($middlewares as $middleware) {
            $next = $final;
            $final = static fn (Command $command) => $middleware->process($command, $next);
        }

        return $final;
    }

    private function execute(object $handler, Command $command): mixed
    {
        return $handler($command);
    }
}
