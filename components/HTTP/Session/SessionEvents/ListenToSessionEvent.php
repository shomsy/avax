<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionEvents;

final class ListenToSessionEvent
{
    private $bus;

    public function __construct($bus)
    {
        $this->bus = $bus;
    }

    public function handle(string $event, callable $callback) : void
    {
        $this->bus->listen($event, $callback);
    }
}