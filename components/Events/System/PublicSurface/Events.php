<?php

declare(strict_types=1);

namespace Avax\Components\Events\System\PublicSurface;

final class Events implements EventsInterface
{
    private array $listeners = [];

    public function dispatch(string $event, mixed $data = null): void
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener($data);
        }
    }

    public function listen(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }
}