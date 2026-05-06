<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Bus;

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
