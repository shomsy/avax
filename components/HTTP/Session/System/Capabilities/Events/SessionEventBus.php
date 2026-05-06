<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Events;

use Avax\Components\HTTP\Session\System\Foundation\SessionEvent;

final class SessionEventBus
{
    private array $listeners = [];

    public function once(string $event, callable $callback): void
    {
        $wrapper = function (...$args) use ($callback, $event, &$wrapper): void {
            $callback(...$args);
            $this->removeListener($event, $wrapper);
        };
        $this->listen($event, $wrapper);
    }

    public function removeListener(string $event, callable $callback): void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            static fn ($cb): bool => $cb !== $callback,
        );
    }

    public function listen(string $event, callable $callback): void
    {
        $this->listeners[$event][] = $callback;
    }

    public function dispatch(string $event, array $data = []): void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        $sessionEvent = SessionEvent::create($event, $data);
        foreach ($this->listeners[$event] as $callback) {
            $callback($sessionEvent);
        }
    }
}
