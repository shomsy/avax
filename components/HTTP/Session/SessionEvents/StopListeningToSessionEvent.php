<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionEvents;

final class StopListeningToSessionEvent
{
    private $bus;

    public function __construct($bus)
    {
        $this->bus = $bus;
    }

    public function handle(string $event, callable $callback) : void
    {
        $this->bus->removeListener($event, $callback);
    }
}