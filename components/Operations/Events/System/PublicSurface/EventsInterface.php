<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\PublicSurface;

interface EventsInterface
{
    public function dispatch(string|object $event, mixed $data = null): void;

    public function listen(string $event, callable $listener, int $priority = 0): void;

    public function flush(): void;

    public function forget(string $event) : void;

    public function hasListeners(string $event) : bool;

    public function listenerCount(string $event) : int;
}
