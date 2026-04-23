<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionEvents;

final class SessionEventBus
{
    private array $listeners = [];

    public function once(string $event, callable $callback) : void
    {
        $wrapper = function (...$args) use ($callback, $event, &$wrapper) {
            $callback(...$args);
            $this->removeListener($event, $wrapper);
        };

        $this->listen($event, $wrapper);
    }

    public function removeListener(string $event, callable $callback) : void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn ($cb) => $cb !== $callback
        );
    }

    public function listen(string $event, callable $callback) : void
    {
        $this->listeners[$event][] = $callback;
    }

    public function dispatch(string $event, array $data = []) : void
    {
        if (! isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $callback) {
            $callback(new SessionEvent($event, $data));
        }
    }
}