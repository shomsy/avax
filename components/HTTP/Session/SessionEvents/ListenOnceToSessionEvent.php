<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionEvents;

final class ListenOnceToSessionEvent
{
    private $bus;

    public function __construct($bus)
    {
        $this->bus = $bus;
    }

    public function handle(string $event, callable $callback) : void
    {
        $this->bus->once($event, $callback);
    }
}