<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Job;

use RuntimeException;

final class JobRegistry
{
    private array $handlers = [];

    public function register(string $name, callable $handler): void
    {
        $this->handlers[$name] = $handler;
    }

    /**
 * @throws RuntimeException
 */
public function resolve(string $name): callable
    {
        if (! isset($this->handlers[$name])) {
            throw new RuntimeException('No handler registered for job: '.$name);
        }

        return $this->handlers[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->handlers[$name]);
    }
}
